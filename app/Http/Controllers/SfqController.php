<?php

namespace App\Http\Controllers;

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\ChequeCollection;
use App\Models\Location;
use App\Models\Product;
use App\Models\ReturnPickup;
use App\Models\SalesOrder;
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
                $item->update([
                    'received_qty' => $qty,
                    'discrepancy_qty' => $discQty,
                    'discrepancy_reason' => $request->discrepancy_reason[$sku] ?? null,
                ]);
            }
        }

        return back()->with('success', 'GRN Inbound receipt confirmed successfully against ASN '.$asn->asn_reference);
    }

    public function locationIndex(Request $request)
    {
        $query = Location::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku', 'like', "%{$search}%")
                    ->orWhere('warehouse', 'like', "%{$search}%")
                    ->orWhere('zone', 'like', "%{$search}%")
                    ->orWhere('rack', 'like', "%{$search}%")
                    ->orWhere('bin', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 20, 50])) {
            $perPage = 10;
        }

        $locations = $query->latest()->paginate($perPage)->withQueryString();
        $products = Product::all();

        return view('dashboards.sfq.locations', compact('locations', 'products'));
    }

    public function locationCreate()
    {
        $products = Product::all();

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
        $products = Product::all();

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
        $orders = SalesOrder::with('items')->latest()->get();

        return view('dashboards.sfq.fulfillment', compact('orders'));
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

    public function deliveryIndex()
    {
        $orders = SalesOrder::where('status', 'completed')
            ->orWhereNotNull('delivery_status')
            ->latest()
            ->get();

        $deliveries = $orders->map(function ($order) {
            return [
                'ref' => 'DEL-'.$order->id,
                'so' => $order->so_number,
                'address' => $order->customer_name.' ('.($order->designation ?? 'N/A').')',
                'driver' => $order->driver ?? '-',
                'vehicle' => $order->vehicle ?? '-',
                'status' => $order->delivery_status ?: 'Pending Assignment',
            ];
        });

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

        $orderId = (int) str_replace('DEL-', '', $request->delivery_ref);
        $order = SalesOrder::findOrFail($orderId);

        $order->update([
            'driver' => $request->driver,
            'vehicle' => $request->vehicle,
            'delivery_status' => 'Assigned',
        ]);

        return back()->with('success', "Driver {$request->driver} and Vehicle {$request->vehicle} assigned to delivery {$request->delivery_ref}.");
    }

    public function deliveryComplete(Request $request)
    {
        $request->validate([
            'delivery_ref' => 'required|string',
        ]);

        $orderId = (int) str_replace('DEL-', '', $request->delivery_ref);
        $order = SalesOrder::findOrFail($orderId);

        $order->update([
            'delivery_status' => 'Delivered',
        ]);

        return back()->with('success', "Delivery {$request->delivery_ref} marked as Delivered.");
    }

    public function returnsIndex()
    {
        $returns = ReturnPickup::latest()->get()->map(function ($ret) {
            return [
                'ref' => $ret->return_ref,
                'driver' => $ret->driver ?? '-',
                'sku' => $ret->product_sku,
                'qty' => $ret->quantity,
                'classification' => $ret->classification ?? 'Re-stockable',
                'status' => $ret->status,
            ];
        });

        return view('dashboards.sfq.returns', compact('returns'));
    }

    public function returnsClassify(Request $request)
    {
        $request->validate([
            'return_ref' => 'required|string|exists:return_pickups,return_ref',
            'classification' => 'required|in:Defective,Re-stockable',
        ]);

        $return = ReturnPickup::where('return_ref', $request->return_ref)->firstOrFail();
        $return->update([
            'classification' => $request->classification,
            'status' => 'Returned to Warehouse',
        ]);

        return back()->with('success', "Return reference {$request->return_ref} classified as {$request->classification}.");
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
}
