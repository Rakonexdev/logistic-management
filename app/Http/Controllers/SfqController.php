<?php

namespace App\Http\Controllers;

use App\Models\AdvanceShippingNote;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SfqController extends Controller
{
    public function grnIndex()
    {
        $asns = AdvanceShippingNote::with('items')->latest()->get();

        return view('dashboards.sfq.grns', compact('asns'));
    }

    public function grnConfirm(Request $request)
    {
        $request->validate([
            'asn_id' => 'required|exists:advance_shipping_notes,id',
            'received_qty' => 'required|array',
            'discrepancy_reason' => 'nullable|array',
        ]);

        $asn = AdvanceShippingNote::findOrFail($request->asn_id);
        $asn->update(['status' => 'completed']);

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
        $order->update(['status' => $request->status]);

        return back()->with('success', "Sales Order {$order->so_number} status updated to ".ucfirst($request->status));
    }

    public function deliveryIndex()
    {
        $deliveries = [
            ['ref' => 'DEL-101', 'so' => 'SO-2026-0001', 'address' => '100 North Rd, NY', 'driver' => 'John Doe', 'vehicle' => 'Truck A', 'status' => 'Out for Delivery'],
            ['ref' => 'DEL-102', 'so' => 'SO-2026-0002', 'address' => '200 East Ave, CA', 'driver' => 'Jane Smith', 'vehicle' => 'Van B', 'status' => 'Pending Assignment'],
        ];

        return view('dashboards.sfq.deliveries', compact('deliveries'));
    }

    public function deliveryAssign(Request $request)
    {
        $request->validate([
            'delivery_ref' => 'required|string',
            'driver' => 'required|string',
            'vehicle' => 'required|string',
        ]);

        return back()->with('success', "Driver {$request->driver} and Vehicle {$request->vehicle} assigned to delivery {$request->delivery_ref}.");
    }

    public function returnsIndex()
    {
        $returns = [
            ['ref' => 'RET-001', 'driver' => 'John Doe', 'sku' => 'SKU-001', 'qty' => 5, 'classification' => 'Defective', 'status' => 'Returned to Warehouse'],
            ['ref' => 'RET-002', 'driver' => 'Jane Smith', 'sku' => 'SKU-002', 'qty' => 12, 'classification' => 'Re-stockable', 'status' => 'Pending Pickup'],
        ];

        return view('dashboards.sfq.returns', compact('returns'));
    }

    public function returnsClassify(Request $request)
    {
        $request->validate([
            'return_ref' => 'required|string',
            'classification' => 'required|in:Defective,Re-stockable',
        ]);

        return back()->with('success', "Return reference {$request->return_ref} classified as {$request->classification}.");
    }

    public function chequesIndex()
    {
        $cheques = [
            ['ref' => 'CHQ-201', 'customer' => 'Customer Alpha', 'driver' => 'John Doe', 'amount' => 1500.00, 'status' => 'Collected'],
            ['ref' => 'CHQ-202', 'customer' => 'Customer Beta', 'driver' => 'Jane Smith', 'amount' => 3200.50, 'status' => 'Pending Collection'],
        ];

        return view('dashboards.sfq.cheques', compact('cheques'));
    }

    public function chequesSubmit(Request $request)
    {
        $request->validate([
            'cheque_ref' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
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
