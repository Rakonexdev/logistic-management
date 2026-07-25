<?php

namespace App\Http\Controllers;

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\ChequeCollection;
use App\Models\DeliveryNote;
use App\Models\Location;
use App\Models\Product;
use App\Models\ReturnInstruction;
use App\Models\ReturnPickup;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\User;
use Illuminate\Http\Request;

class SfqController extends Controller
{
    public function grnIndex(Request $request)
    {
        $asns = AdvanceShippingNote::with('items')
            ->whereIn('status', ['processing', 'completed', 'discrepancy'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $pendingAsns = AdvanceShippingNote::whereIn('status', ['submitted', 'processing', 'discrepancy'])->latest()->get();

        return view('dashboards.sfq.grns', compact('asns', 'pendingAsns'));
    }

    public function grnConfirm(Request $request)
    {
        $request->validate([
            'asn_id' => 'required|exists:advance_shipping_notes,id',
            'received_qty' => 'required|array',
            'discrepancy_qty' => 'nullable|array',
            'discrepancy_reason' => 'nullable|array',
        ]);

        $asn = AdvanceShippingNote::findOrFail($request->asn_id);
        $newStatus = $request->input('action') === 'submit'
            ? 'completed'
            : ($request->input('action') === 'report' ? 'discrepancy' : 'processing');
        $asn->update(['status' => $newStatus]);

        foreach ($request->input('received_qty', []) as $sku => $qty) {
            $item = AsnItem::where('asn_id', $asn->id)->where('sku_code', $sku)->first();
            if ($item) {
                $discQty = isset($request->discrepancy_qty[$sku]) ? intval($request->discrepancy_qty[$sku]) : ($qty - $item->quantity);
                $missingStr = $request->missing_serials[$sku] ?? null;

                $item->update([
                    'received_qty' => $qty,
                    'discrepancy_qty' => $discQty,
                    'discrepancy_reason' => $request->discrepancy_reason[$sku] ?? null,
                    'missing_serials' => $missingStr,
                ]);

                if ($qty > 0 && ($newStatus === 'completed' || $request->input('action') === 'submit')) {
                    $product = Product::where('sku_code', $sku)->first();
                    if ($product) {
                        $product->increment('qty', $qty);
                        if ($item->serial_numbers) {
                            $existingSerials = $product->serial_number ? array_filter(array_map('trim', explode(',', $product->serial_number))) : [];
                            $allItemSerials = array_filter(array_map('trim', explode(',', $item->serial_numbers)));
                            $missingSerialsList = $missingStr ? array_filter(array_map('trim', explode(',', $missingStr))) : [];
                            $receivedSerials = array_diff($allItemSerials, $missingSerialsList);
                            $combinedSerials = array_unique(array_merge($existingSerials, $receivedSerials));
                            $product->update(['serial_number' => implode(', ', $combinedSerials)]);
                        }
                    }
                }
            }
        }

        return back()->with('success', 'GRN Inbound receipt confirmed successfully against ASN '.$asn->asn_reference);
    }

    public function locationIndex(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 20, 25, 50])) {
            $perPage = 10;
        }

        $products = $query->latest()->paginate($perPage)->withQueryString();

        foreach ($products as $product) {
            $inbound = $product->qty;

            $outbound = AsnItem::where('sku_code', $product->sku_code)
                ->whereHas('asn', function ($q) {
                    $q->whereIn('status', ['draft', 'submitted', 'processing', 'completed', 'discrepancy']);
                })->sum('quantity') + SalesOrderItem::where('sku_code', $product->sku_code)
                ->whereHas('salesOrder', function ($q) {
                    $q->whereIn('status', ['draft', 'submitted', 'processing', 'completed']);
                })->sum('quantity');

            $product->inbound_qty = $inbound;
            $product->outbound_qty = $outbound;
            $product->available_qty = max(0, $inbound - $outbound);

            $locationsList = Location::where('sku', $product->sku_code)->get();
            if ($locationsList->isEmpty()) {
                $product->location_info = 'WH-1';
            } else {
                $locationStrings = [];
                foreach ($locationsList as $loc) {
                    $locationStrings[] = "{$loc->warehouse} ({$loc->zone}-{$loc->rack}-{$loc->bin}-{$loc->level})";
                }
                $product->location_info = implode(', ', $locationStrings);
            }
        }

        return view('dashboards.sfq.locations', compact('products'));
    }

    public function locationCreate()
    {
        $products = $this->getVerifiedProducts();

        return view('dashboards.sfq.locations.create', compact('products'));
    }

    public function locationStore(Request $request)
    {
        $validated = $request->validate([
            'warehouse' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'rack' => 'required|string|max:255',
            'bin' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
        ]);

        Location::create($validated);

        return redirect()->route('sfq.locations.index')->with('success', 'Location created and added to Warehouse layout successfully.');
    }

    public function locationEdit($id)
    {
        $location = Location::findOrFail($id);
        $products = $this->getVerifiedProducts();

        return view('dashboards.sfq.locations.edit', compact('location', 'products'));
    }

    public function locationUpdate(Request $request, $id)
    {
        $location = Location::findOrFail($id);
        $validated = $request->validate([
            'warehouse' => 'required|string|max:255',
            'zone' => 'required|string|max:255',
            'rack' => 'required|string|max:255',
            'bin' => 'required|string|max:255',
            'level' => 'required|string|max:255',
            'sku' => 'required|string|max:255',
            'qty' => 'required|integer|min:0',
            'status' => 'required|string|max:255',
        ]);

        $location->update($validated);

        return redirect()->route('sfq.locations.index')->with('success', 'Location updated successfully.');
    }

    public function locationDestroy($id)
    {
        $location = Location::findOrFail($id);
        $location->delete();

        return redirect()->route('sfq.locations.index')->with('success', 'Location deleted successfully.');
    }

    public function locationTransfer(Request $request)
    {
        $request->validate([
            'sku' => 'required|string',
            'qty' => 'required|integer|min:1',
            'source' => 'required|string',
            'destination' => 'required|string',
        ]);

        return back()->with('success', "Successfully transferred {$request->qty} of {$request->sku} from {$request->source} to {$request->destination}.");
    }

    public function fulfillmentIndex()
    {
        $deliveryNotes = DeliveryNote::with('items', 'deliveryInstruction')
            ->whereIn('status', ['released', 'processing', 'completed'])
            ->latest()
            ->get();

        return view('dashboards.sfq.fulfillment', compact('deliveryNotes'));
    }

    public function fulfillmentUpdate(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:sales_orders,id',
            'status' => 'required|in:processing,completed,cancelled',
        ]);

        $order = SalesOrder::findOrFail($request->order_id);
        $updateData = ['status' => $request->status];
        if ($request->status === 'completed' && ! $order->delivery_status) {
            $updateData['delivery_status'] = 'Pending Assignment';
        }
        $order->update($updateData);

        return back()->with('success', "Sales Order {$order->so_number} status updated to ".ucfirst($request->status));
    }

    public function fulfillmentUpdateDeliveryNote(Request $request)
    {
        $request->validate([
            'note_id' => 'required|exists:delivery_notes,id',
            'status' => 'required|in:processing,completed',
        ]);

        $note = DeliveryNote::findOrFail($request->note_id);
        $note->update([
            'status' => $request->status,
            'delivery_status' => 'Pending Assignment',
        ]);

        return back()->with('success', "Delivery Note {$note->dn_number} status updated to ".ucfirst($request->status));
    }

    public function deliveryIndex()
    {
        $orders = SalesOrder::where('status', 'completed')
            ->orWhereNotNull('delivery_status')
            ->latest()
            ->get();

        $notes = DeliveryNote::with('deliveryInstruction')
            ->where('status', 'completed')
            ->latest()
            ->get();

        $deliveries = collect();

        foreach ($orders as $order) {
            $status = $order->delivery_status ?: 'Pending Assignment';
            if ($status === 'Arrived') {
                $status = 'In Transit';
            }
            $deliveries->push([
                'id' => null,
                'ref' => 'DEL-'.$order->id,
                'so' => $order->so_number,
                'address' => $order->customer_name.' ('.($order->customer_address ?? 'N/A').')',
                'driver' => $order->driver ?? '-',
                'vehicle' => $order->vehicle ?? '-',
                'status' => $status,
            ]);
        }

        foreach ($notes as $note) {
            $status = $note->delivery_status ?: 'Pending Assignment';
            if ($status === 'Arrived') {
                $status = 'In Transit';
            }
            $deliveries->push([
                'id' => $note->id,
                'ref' => $note->dn_number,
                'so' => $note->deliveryInstruction->di_number ?? $note->dn_number,
                'address' => ($note->deliveryInstruction->customer_name ?? 'N/A').' ('.($note->deliveryInstruction->delivery_address ?? 'N/A').')',
                'driver' => $note->driver ?? '-',
                'vehicle' => $note->vehicle ?? '-',
                'status' => $status,
            ]);
        }

        $drivers = User::where('role', 'driver')->get();

        return view('dashboards.sfq.deliveries', compact('deliveries', 'drivers'));
    }

    public function deliveryAssign(Request $request)
    {
        $request->validate([
            'delivery_ref' => 'required|string',
            'driver' => 'required|string',
            'vehicle' => 'required|string',
        ]);

        if (str_starts_with($request->delivery_ref, 'DN-')) {
            $dn = DeliveryNote::with('deliveryInstruction.invoice')->where('dn_number', $request->delivery_ref)->firstOrFail();
            $dn->update([
                'driver' => $request->driver,
                'vehicle' => $request->vehicle,
                'delivery_status' => 'Assigned',
            ]);

            if ($dn->deliveryInstruction) {
                $di = $dn->deliveryInstruction;
                ChequeCollection::where(function ($q) use ($di) {
                    $q->where('so_reference', $di->di_number)
                        ->orWhere('so_reference', $di->so_reference);
                    if ($di->invoice) {
                        $q->orWhere('invoice_reference', $di->invoice->invoice_number);
                    }
                })->update(['driver' => $request->driver]);
            }
        } else {
            $orderId = (int) str_replace('DEL-', '', $request->delivery_ref);
            $order = SalesOrder::findOrFail($orderId);
            $order->update([
                'driver' => $request->driver,
                'vehicle' => $request->vehicle,
                'delivery_status' => 'Assigned',
            ]);
        }

        return back()->with('success', "Driver {$request->driver} and Vehicle {$request->vehicle} assigned to delivery {$request->delivery_ref}.");
    }

    public function deliveryComplete(Request $request)
    {
        $request->validate([
            'delivery_ref' => 'required|string',
        ]);

        if (str_starts_with($request->delivery_ref, 'DN-')) {
            $dn = DeliveryNote::where('dn_number', $request->delivery_ref)->firstOrFail();
            $dn->update([
                'delivery_status' => 'Delivered',
            ]);
        } else {
            $orderId = (int) str_replace('DEL-', '', $request->delivery_ref);
            $order = SalesOrder::findOrFail($orderId);
            $order->update([
                'delivery_status' => 'Delivered',
            ]);
        }

        return back()->with('success', "Delivery {$request->delivery_ref} marked as Delivered.");
    }

    public function returnsIndex()
    {
        $instructions = ReturnInstruction::with('items')->latest()->get();
        $locations = Location::all();
        $drivers = User::where('role', 'driver')->orderBy('name')->get();
        $legacyReturns = ReturnPickup::latest()->get();

        return view('dashboards.sfq.returns', compact('instructions', 'locations', 'drivers', 'legacyReturns'));
    }

    public function returnsAssign(Request $request)
    {
        $request->validate([
            'return_ref' => 'required|string|exists:return_instructions,return_ref',
            'driver_name' => 'required|string|max:255',
            'storing_location' => 'required|string|max:255',
        ]);

        $instruction = ReturnInstruction::with('items')->where('return_ref', $request->return_ref)->firstOrFail();
        $instruction->update([
            'driver_name' => $request->driver_name,
            'storing_location' => $request->storing_location,
            'status' => 'Driver Assigned',
        ]);

        foreach ($instruction->items as $item) {
            ReturnPickup::updateOrCreate(
                ['return_ref' => $instruction->return_ref.'-'.$item->sku_code],
                [
                    'driver' => $request->driver_name,
                    'pickup_location' => $instruction->pickup_address,
                    'product_sku' => $item->sku_code,
                    'quantity' => $item->quantity,
                    'status' => 'Pending Pickup',
                    'remarks' => $instruction->remarks,
                ]
            );
        }

        return back()->with('success', "Driver {$request->driver_name} and location {$request->storing_location} assigned to Return {$instruction->return_ref}.");
    }

    public function returnsStatusUpdate(Request $request)
    {
        $request->validate([
            'return_ref' => 'required|string|exists:return_instructions,return_ref',
            'status' => 'required|in:Picked Up,Stored',
        ]);

        $instruction = ReturnInstruction::with('items')->where('return_ref', $request->return_ref)->firstOrFail();
        $updateData = ['status' => $request->status];

        if ($request->status === 'Picked Up') {
            $updateData['picking_date'] = now();
        } elseif ($request->status === 'Stored') {
            $updateData['storing_date'] = now();

            // Auto restock inventory if classification is Re-stockable or default
            if (empty($instruction->classification) || $instruction->classification === 'Re-stockable') {
                foreach ($instruction->items as $item) {
                    $product = Product::where('sku_code', $item->sku_code)->first();
                    if ($product) {
                        $product->increment('qty', $item->quantity);
                        if ($item->serial_numbers) {
                            $existingSerials = $product->serial_number ? array_filter(array_map('trim', explode(',', $product->serial_number))) : [];
                            $newSerials = array_filter(array_map('trim', explode(',', $item->serial_numbers)));
                            $mergedSerials = array_values(array_unique(array_merge($existingSerials, $newSerials)));
                            $product->update(['serial_number' => implode(', ', $mergedSerials)]);
                        }
                    }
                }
            }
        }

        $instruction->update($updateData);

        return back()->with('success', "Return {$instruction->return_ref} status updated to {$request->status}.");
    }

    public function returnsClassify(Request $request)
    {
        $request->validate([
            'return_ref' => 'required|string|exists:return_instructions,return_ref',
            'classification' => 'required|in:Defective,Re-stockable',
            'ship_back' => 'nullable|boolean',
            'tracking_number' => 'nullable|string|max:255',
            'courier_name' => 'nullable|string|max:255',
            'shipping_charges' => 'nullable|numeric|min:0',
        ]);

        $instruction = ReturnInstruction::with('items')->where('return_ref', $request->return_ref)->first();

        if (! $instruction) {
            // Legacy ReturnPickup fallback
            $return = ReturnPickup::where('return_ref', $request->return_ref)->firstOrFail();
            $return->update([
                'classification' => $request->classification,
                'status' => 'Returned to Warehouse',
            ]);

            return back()->with('success', "Return reference {$request->return_ref} classified as {$request->classification}.");
        }

        $updateData = [
            'classification' => $request->classification,
            'inspection_status' => $request->classification === 'Defective' ? 'Failed' : 'Passed',
        ];

        if ($request->filled('ship_back') && $request->ship_back) {
            $updateData['status'] = 'Shipped to END';
            $updateData['shipped_back_date'] = now();
            $updateData['tracking_number'] = $request->tracking_number;
            $updateData['courier_name'] = $request->courier_name;
            $updateData['shipping_charges'] = $request->shipping_charges;
        } else {
            $updateData['status'] = 'Completed';
        }

        // If classified as Re-stockable and not yet restocked
        if ($request->classification === 'Re-stockable' && $instruction->status !== 'Stored') {
            foreach ($instruction->items as $item) {
                $product = Product::where('sku_code', $item->sku_code)->first();
                if ($product) {
                    $product->increment('qty', $item->quantity);
                    if ($item->serial_numbers) {
                        $existingSerials = $product->serial_number ? array_filter(array_map('trim', explode(',', $product->serial_number))) : [];
                        $newSerials = array_filter(array_map('trim', explode(',', $item->serial_numbers)));
                        $mergedSerials = array_values(array_unique(array_merge($existingSerials, $newSerials)));
                        $product->update(['serial_number' => implode(', ', $mergedSerials)]);
                    }
                }
            }
        }

        $instruction->update($updateData);

        return back()->with('success', "Return {$instruction->return_ref} classified as {$request->classification}.");
    }

    public function chequesIndex()
    {
        $cheques = ChequeCollection::latest()->get()->map(function ($chq) {
            return [
                'ref' => $chq->collection_ref,
                'customer' => $chq->customer_name,
                'driver' => $chq->driver ?? '-',
                'amount' => $chq->amount,
                'status' => $chq->status,
                'invoice_reference' => $chq->invoice_reference,
                'so_reference' => $chq->so_reference,
            ];
        });

        return view('dashboards.sfq.cheques', compact('cheques'));
    }

    public function chequesSubmit(Request $request)
    {
        $request->validate([
            'cheque_ref' => 'required|string|exists:cheque_collections,collection_ref',
            'amount' => 'required|numeric|min:0.01',
        ]);

        $cheque = ChequeCollection::where('collection_ref', $request->cheque_ref)->firstOrFail();
        $cheque->update([
            'amount' => $request->amount,
            'status' => 'Submitted',
            'submission_time' => now(),
        ]);

        return back()->with('success', "Cheque collection record {$request->cheque_ref} submitted successfully.");
    }

    public function reconciliationIndex()
    {
        $charges = [
            ['ref' => 'CHG-001', 'type' => 'Inbound Handling', 'proposed' => 250.00, 'non_proposed' => 0.00, 'status' => 'Reconciled'],
            ['ref' => 'CHG-002', 'type' => 'Storage Rent', 'proposed' => 1200.00, 'non_proposed' => 150.00, 'status' => 'Pending'],
        ];

        return view('dashboards.sfq.reconciliation', compact('charges'));
    }

    public function reconciliationUpdate(Request $request)
    {
        $request->validate([
            'charge_ref' => 'required|string',
            'status' => 'required|in:Reconciled,Pending',
        ]);

        return back()->with('success', "Charge reference {$request->charge_ref} status updated to {$request->status}.");
    }

    public function invoicesIndex()
    {
        $invoices = [
            ['number' => 'INV-2026-001', 'type' => 'Warehouse Rent', 'period' => 'June 2026', 'amount' => 1500.00, 'status' => 'Issued', 'due' => '2026-07-31'],
            ['number' => 'INV-2026-002', 'type' => 'Delivery Charges', 'period' => 'July 2026', 'amount' => 450.00, 'status' => 'Draft', 'due' => '2026-08-15'],
        ];

        return view('dashboards.sfq.invoices', compact('invoices'));
    }

    public function invoicesCreate(Request $request)
    {
        $request->validate([
            'invoice_type' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'billing_period' => 'required|string',
        ]);

        return back()->with('success', "Invoice for {$request->invoice_type} created successfully.");
    }

    public function reportsIndex()
    {
        $metrics = [
            'total_stock' => 12450,
            'movement_ratio' => '85%',
            'delivered_orders' => 142,
            'grn_discrepancies' => 3,
        ];

        return view('dashboards.sfq.reports', compact('metrics'));
    }

    private function getVerifiedProducts()
    {
        $products = Product::whereIn('sku_code', function ($query) {
            $query->select('sku_code')
                ->from('asn_items')
                ->whereIn('asn_id', function ($subQuery) {
                    $subQuery->select('id')
                        ->from('advance_shipping_notes')
                        ->whereIn('status', ['completed', 'discrepancy']);
                });
        })->get();

        foreach ($products as $product) {
            $inboundAsn = AsnItem::where('sku_code', $product->sku_code)
                ->whereHas('asn', function ($q) {
                    $q->whereIn('status', ['completed', 'discrepancy']);
                })
                ->sum('received_qty');

            $outboundSo = SalesOrderItem::where('sku_code', $product->sku_code)
                ->whereHas('salesOrder', function ($q) {
                    $q->whereIn('status', ['submitted', 'processing', 'completed']);
                })
                ->sum('quantity');

            $product->available_qty = max(0, $inboundAsn - $outboundSo);
        }

        return $products;
    }
}
