<?php

namespace App\Http\Controllers;

use App\Models\DeliveryInstruction;
use App\Models\DeliveryInstructionItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DeliveryInstructionController extends Controller
{
    public function index()
    {
        $instructions = DeliveryInstruction::with('items')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'instructions_page');

        $notes = DeliveryNote::with('items', 'deliveryInstruction')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10, ['*'], 'notes_page');

        return view('dashboards.delivery_instructions.index', compact('instructions', 'notes'));
    }

    public function create()
    {
        $products = Product::orderBy('sku_code')->get();
        $remainingItems = [];

        return view('dashboards.delivery_instructions.create', compact('products', 'remainingItems'));
    }

    public function fulfillRemaining($id)
    {
        $di = DeliveryInstruction::with('items')->where('user_id', Auth::id())->findOrFail($id);
        $products = Product::orderBy('sku_code')->get();

        $remainingItems = [];
        foreach ($di->items as $item) {
            $remQty = $item->quantity - $item->delivered_quantity;
            if ($remQty > 0) {
                $remainingItems[] = [
                    'sku_code' => $item->sku_code,
                    'quantity' => $remQty,
                    'serial_numbers' => $item->serial_numbers,
                ];
            }
        }

        return view('dashboards.delivery_instructions.create', [
            'products' => $products,
            'remainingItems' => $remainingItems,
            'parentDi' => $di,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'di_number' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'delivery_address' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serial_numbers' => 'nullable|string',
        ]);

        $items = $request->items;
        $mismatches = [];
        $availableItems = [];
        $hasMismatches = false;

        foreach ($items as $item) {
            $sku = $item['sku_code'];
            $qty = (int) $item['quantity'];
            $enteredSerialsStr = $item['serial_numbers'] ?? '';

            $enteredSerials = array_filter(array_map('trim', explode(',', $enteredSerialsStr)));

            $product = Product::where('sku_code', $sku)->first();

            if (! $product) {
                $hasMismatches = true;
                $mismatches[] = [
                    'sku_code' => $sku,
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'serial_numbers' => $enteredSerials,
                    'reason' => 'SKU does not exist in warehouse inventory.',
                    'available_qty' => 0,
                    'available_serials' => [],
                ];

                continue;
            }

            $availableQty = $product->qty;
            $productSerial = $product->serial_number;
            $availableSerials = $productSerial ? [$productSerial] : [];

            $itemErrors = [];

            if ($availableQty <= 0) {
                $itemErrors[] = 'SKU is out of stock.';
            } elseif ($qty > $availableQty) {
                $itemErrors[] = "Requested quantity ({$qty}) exceeds available stock ({$availableQty}).";
            }

            if ($productSerial) {
                if (empty($enteredSerials)) {
                    $itemErrors[] = 'Serial number is required for this SKU.';
                } else {
                    foreach ($enteredSerials as $serial) {
                        if (strtolower($serial) !== strtolower($productSerial)) {
                            $itemErrors[] = "Serial number '{$serial}' does not match available serial number '{$productSerial}'.";
                        }
                    }
                    if (count($enteredSerials) !== $qty) {
                        $itemErrors[] = 'Number of serial numbers ('.count($enteredSerials).") does not match requested quantity ({$qty}).";
                    }
                }
            }

            if (! empty($itemErrors)) {
                $hasMismatches = true;
                $mismatches[] = [
                    'sku_code' => $sku,
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'serial_numbers' => $enteredSerials,
                    'reason' => implode(' ', $itemErrors),
                    'available_qty' => $availableQty,
                    'available_serials' => $availableSerials,
                ];

                if ($availableQty > 0) {
                    $matchedSerials = [];
                    if ($productSerial && in_array(strtolower($productSerial), array_map('strtolower', $enteredSerials))) {
                        $matchedSerials[] = $productSerial;
                    }
                    $availableItems[] = [
                        'sku_code' => $sku,
                        'description' => $item['description'] ?? '',
                        'quantity' => min($qty, $availableQty),
                        'serial_numbers' => $matchedSerials,
                    ];
                }
            } else {
                $availableItems[] = [
                    'sku_code' => $sku,
                    'description' => $item['description'] ?? '',
                    'quantity' => $qty,
                    'serial_numbers' => $enteredSerials,
                ];
            }
        }

        if ($hasMismatches && ! $request->has('confirm_partial')) {
            return view('dashboards.delivery_instructions.warning', [
                'di_number' => $request->di_number,
                'customer_name' => $request->customer_name,
                'delivery_address' => $request->delivery_address,
                'mismatches' => $mismatches,
                'available_items' => $availableItems,
                'original_items' => $items,
            ]);
        }

        // Validate DI number uniqueness
        $existingDi = DeliveryInstruction::where('di_number', $request->di_number)->first();
        if ($existingDi && ! $request->has('confirm_partial')) {
            return back()->withInput()->withErrors(['di_number' => 'The Delivery Instruction number has already been taken.']);
        }

        // Create Delivery Instruction
        $di = DeliveryInstruction::create([
            'di_number' => $request->di_number,
            'customer_name' => $request->customer_name,
            'delivery_address' => $request->delivery_address,
            'status' => $hasMismatches ? 'partial' : 'completed',
            'user_id' => Auth::id(),
        ]);

        foreach ($items as $item) {
            $sku = $item['sku_code'];
            $qty = (int) $item['quantity'];
            $serials = $item['serial_numbers'] ?? '';

            $deliveredQty = 0;
            foreach ($availableItems as $avail) {
                if ($avail['sku_code'] === $sku) {
                    $deliveredQty = $avail['quantity'];
                    break;
                }
            }

            $itemStatus = 'pending';
            if ($deliveredQty >= $qty) {
                $itemStatus = 'completed';
            } elseif ($deliveredQty > 0) {
                $itemStatus = 'partial';
            }

            $desc = $item['description'] ?? '';

            DeliveryInstructionItem::create([
                'delivery_instruction_id' => $di->id,
                'sku_code' => $sku,
                'description' => $desc,
                'quantity' => $qty,
                'serial_numbers' => $serials,
                'delivered_quantity' => $deliveredQty,
                'status' => $itemStatus,
            ]);

            if ($deliveredQty > 0) {
                $product = Product::where('sku_code', $sku)->first();
                if ($product) {
                    $product->decrement('qty', $deliveredQty);
                }
            }
        }

        $totalDelivered = collect($availableItems)->sum('quantity');
        if ($totalDelivered > 0) {
            $dn = DeliveryNote::create([
                'dn_number' => 'DN-'.date('Ymd').'-'.rand(100, 999),
                'delivery_instruction_id' => $di->id,
                'user_id' => Auth::id(),
            ]);

            foreach ($availableItems as $avail) {
                if ($avail['quantity'] > 0) {
                    DeliveryNoteItem::create([
                        'delivery_note_id' => $dn->id,
                        'sku_code' => $avail['sku_code'],
                        'description' => $avail['description'] ?? '',
                        'quantity' => $avail['quantity'],
                        'serial_numbers' => implode(', ', $avail['serial_numbers']),
                    ]);
                }
            }
        }

        $message = $hasMismatches
            ? 'Partial Delivery processed. Delivery Note generated for available items.'
            : 'Delivery Instruction and Delivery Note created successfully.';

        return redirect()->route('delivery-instructions.index')->with('success', $message);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=delivery_instruction_template.csv',
        ];
        $columns = ['sku_code', 'description', 'quantity', 'serial_numbers'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $samples = [
                ['FG-100F', 'FortiGate-100F Firewall', '2', 'FG100FTK25011385, FG100FTK25011385'],
                ['FG-40F', 'FortiGate-40F Firewall', '5', 'FGT40FTK24083675'],
                ['LIC-FG100F-BDL', 'Unified Threat Protection License', '1', ''],
            ];

            foreach ($samples as $sample) {
                fputcsv($file, $sample);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
