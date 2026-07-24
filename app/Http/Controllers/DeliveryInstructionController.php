<?php

namespace App\Http\Controllers;

use App\Models\DeliveryInstruction;
use App\Models\DeliveryInstructionItem;
use App\Models\DeliveryNote;
use App\Models\DeliveryNoteItem;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;

class DeliveryInstructionController extends Controller
{
    public function index()
    {
        $instructions = DeliveryInstruction::with(['items', 'deliveryNotes'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        foreach ($instructions as $di) {
            if ($di->deliveryNotes->isEmpty()) {
                $dn = DeliveryNote::create([
                    'dn_number' => 'DN-'.date('Ymd').'-'.rand(100, 999),
                    'delivery_instruction_id' => $di->id,
                    'user_id' => $di->user_id,
                    'status' => 'draft',
                ]);
                foreach ($di->items as $item) {
                    DeliveryNoteItem::create([
                        'delivery_note_id' => $dn->id,
                        'sku_code' => $item->sku_code,
                        'description' => $item->description ?? '',
                        'quantity' => $item->quantity,
                        'serial_numbers' => $item->serial_numbers,
                    ]);
                }
                $di->unsetRelation('deliveryNotes');
                $di->load('deliveryNotes');
            }
        }

        return view('dashboards.delivery_instructions.index', compact('instructions'));
    }

    public function deliveryNotesIndex()
    {
        $notes = DeliveryNote::with('items', 'deliveryInstruction')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('dashboards.delivery_notes.index', compact('notes'));
    }

    public function create()
    {
        $products = Product::orderBy('sku_code')->get();
        $remainingItems = [];
        $salesOrders = SalesOrder::with('items')->where('user_id', Auth::id())->latest()->get();

        return view('dashboards.delivery_instructions.create', compact('products', 'remainingItems', 'salesOrders'));
    }

    public function fulfillRemaining($id)
    {
        $di = DeliveryInstruction::with('items')->where('user_id', Auth::id())->findOrFail($id);
        $products = Product::orderBy('sku_code')->get();
        $salesOrders = SalesOrder::with('items')->where('user_id', Auth::id())->latest()->get();

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
            'salesOrders' => $salesOrders,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'di_number' => 'required|string',
            'customer_name' => 'required|string|max:255',
            'end_user_name' => 'nullable|string|max:255',
            'so_reference' => 'nullable|string|max:255',
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
            $availableSerials = $productSerial ? array_filter(array_map('trim', explode(',', $productSerial))) : [];

            $itemErrors = [];

            if ($availableQty <= 0) {
                $itemErrors[] = 'SKU is out of stock.';
            } elseif ($qty > $availableQty) {
                $itemErrors[] = "Requested quantity ({$qty}) exceeds available stock ({$availableQty}).";
            }

            if (! empty($availableSerials)) {
                if (empty($enteredSerials)) {
                    $itemErrors[] = 'Serial number is required for this SKU.';
                } else {
                    foreach ($enteredSerials as $serial) {
                        if (! in_array(strtolower($serial), array_map('strtolower', $availableSerials))) {
                            $itemErrors[] = "Serial number '{$serial}' does not match any of the available serial numbers: ".implode(', ', $availableSerials).'.';
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
                    if (! empty($availableSerials)) {
                        $enteredLower = array_map('strtolower', $enteredSerials);
                        foreach ($availableSerials as $availSer) {
                            if (in_array(strtolower($availSer), $enteredLower)) {
                                $matchedSerials[] = $availSer;
                            }
                        }
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
                'end_user_name' => $request->end_user_name,
                'so_reference' => $request->so_reference,
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

        $attachmentPath = null;
        if ($request->hasFile('delivery_note_attachment')) {
            $attachmentPath = $request->file('delivery_note_attachment')->store('delivery_notes', 'public');
        }

        // Create Delivery Instruction
        $di = DeliveryInstruction::create([
            'di_number' => $request->di_number,
            'customer_name' => $request->customer_name,
            'end_user_name' => $request->end_user_name,
            'so_reference' => $request->so_reference,
            'delivery_note_attachment' => $attachmentPath,
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
                    $newQty = max(0, $product->qty - $deliveredQty);
                    if (! empty($serials)) {
                        $deliveredSerials = array_filter(array_map('trim', explode(',', $serials)));
                        $existingSerials = $product->serial_number ? array_filter(array_map('trim', explode(',', $product->serial_number))) : [];
                        $remainingSerials = array_values(array_filter($existingSerials, function ($s) use ($deliveredSerials) {
                            return ! in_array(strtolower($s), array_map('strtolower', $deliveredSerials));
                        }));
                        $product->update([
                            'qty' => $newQty,
                            'serial_number' => implode(', ', $remainingSerials),
                        ]);
                    } else {
                        $product->update(['qty' => $newQty]);
                    }
                }
            }
        }

        $dnItems = ! empty($availableItems) ? $availableItems : array_map(function ($item) {
            $serialsStr = $item['serial_numbers'] ?? '';
            $serialsArr = is_array($serialsStr) ? $serialsStr : array_filter(array_map('trim', explode(',', $serialsStr)));

            return [
                'sku_code' => $item['sku_code'],
                'description' => $item['description'] ?? '',
                'quantity' => (int) $item['quantity'],
                'serial_numbers' => $serialsArr,
            ];
        }, $items);

        $dn = DeliveryNote::create([
            'dn_number' => 'DN-'.date('Ymd').'-'.rand(100, 999),
            'delivery_instruction_id' => $di->id,
            'user_id' => Auth::id(),
            'status' => 'draft',
            'delivery_note_attachment' => $attachmentPath,
        ]);

        foreach ($dnItems as $dnItem) {
            if ($dnItem['quantity'] > 0) {
                DeliveryNoteItem::create([
                    'delivery_note_id' => $dn->id,
                    'sku_code' => $dnItem['sku_code'],
                    'description' => $dnItem['description'] ?? '',
                    'quantity' => $dnItem['quantity'],
                    'serial_numbers' => is_array($dnItem['serial_numbers']) ? implode(', ', $dnItem['serial_numbers']) : $dnItem['serial_numbers'],
                ]);
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

    public function printDeliveryNote($id)
    {
        $note = DeliveryNote::with('items', 'deliveryInstruction')->findOrFail($id);

        return view('dashboards.delivery_instructions.print_dn', compact('note'));
    }

    public function releaseDeliveryNote($id)
    {
        $note = DeliveryNote::where('user_id', Auth::id())->findOrFail($id);
        $note->update(['status' => 'released']);

        return back()->with('success', "Delivery Note {$note->dn_number} released successfully.");
    }

    public function downloadAttachment($id)
    {
        $di = DeliveryInstruction::findOrFail($id);
        $attachment = $di->delivery_note_attachment;

        if (! $attachment) {
            $dn = DeliveryNote::where('delivery_instruction_id', $id)->whereNotNull('delivery_note_attachment')->first();
            if ($dn) {
                $attachment = $dn->delivery_note_attachment;
            }
        }

        if (! $attachment) {
            abort(404, 'No attachment uploaded for this Delivery Instruction.');
        }

        $fullPath = storage_path('app/public/'.$attachment);
        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/'.$attachment);
        }

        if (! file_exists($fullPath)) {
            abort(404, 'File not found on server.');
        }

        return response()->file($fullPath);
    }
}
