<?php

namespace App\Http\Controllers;

use App\Models\AdvanceShippingNote;
use App\Models\Product;
use App\Models\SalesOrder;
use Illuminate\Http\Request;

class SfqController extends Controller
{
    public function grnIndex(Request $request)
    {
        $asns = AdvanceShippingNote::with('items')->latest()->paginate(10)->withQueryString();

        return view('dashboards.sfq.grns', compact('asns'));
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
        $asn->update(['status' => 'completed']);

        foreach ($request->input('received_qty', []) as $sku => $qty) {
            $item = \App\Models\AsnItem::where('asn_id', $asn->id)->where('sku_code', $sku)->first();
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

    public function locationIndex()
    {
        // Sample location inventory
        $locations = [
            ['warehouse' => 'WH-Main', 'zone' => 'A', 'rack' => '01', 'bin' => 'A1', 'level' => '1', 'sku' => 'SKU-001', 'qty' => 150, 'status' => 'Available'],
            ['warehouse' => 'WH-Main', 'zone' => 'A', 'rack' => '02', 'bin' => 'B3', 'level' => '2', 'sku' => 'SKU-002', 'qty' => 75, 'status' => 'Available'],
            ['warehouse' => 'WH-Main', 'zone' => 'B', 'rack' => '05', 'bin' => 'C2', 'level' => '1', 'sku' => 'SKU-STOCK', 'qty' => 300, 'status' => 'Reserved'],
        ];
        $products = Product::all();

        return view('dashboards.sfq.locations', compact('locations', 'products'));
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
