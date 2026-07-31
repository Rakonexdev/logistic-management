<?php

namespace App\Http\Controllers;

use App\Models\ChequeCollection;
use App\Models\DeliveryInstruction;
use App\Models\DeliveryInvoice;
use App\Models\DeliveryNote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DeliveryInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryInvoice::with(['deliveryInstruction.deliveryNotes', 'items', 'user']);

        if (Auth::user()->role === 'end_user') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('so_reference', 'like', "%{$search}%");
            });
        }

        $invoices = $query->latest()->paginate(10)->withQueryString();

        return view('dashboards.delivery_invoices.index', compact('invoices'));
    }

    public function create()
    {
        $instructionsQuery = DeliveryInstruction::with('items')->doesntHave('invoice');

        if (Auth::user()->role === 'end_user') {
            $instructionsQuery->where('user_id', Auth::id());
        }

        $instructions = $instructionsQuery->latest()->get();

        $defaultInvoiceNum = 'INV-'.date('Ymd').'-'.rand(100, 999);

        return view('dashboards.delivery_invoices.create', compact('instructions', 'defaultInvoiceNum'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|unique:delivery_invoices,invoice_number',
            'delivery_instruction_id' => 'required|exists:delivery_instructions,id',
            'lump_sum_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string',
            'items.*.serial_number' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.charge_amount' => 'nullable|numeric|min:0',
        ]);

        $di = DeliveryInstruction::findOrFail($request->delivery_instruction_id);

        $lumpSum = (float) $request->input('lump_sum_amount', 0);
        $totalInvoiceAmount = $lumpSum;
        $itemsData = [];

        foreach ($request->items as $item) {
            $charge = (float) ($item['charge_amount'] ?? 0);
            $qty = (int) $item['quantity'];
            $lineTotal = $charge * $qty;
            $totalInvoiceAmount += $lineTotal;

            $itemsData[] = [
                'sku_code' => $item['sku_code'],
                'serial_number' => $item['serial_number'] ?? null,
                'quantity' => $qty,
                'charge_amount' => $charge,
                'total_amount' => $lineTotal,
            ];
        }

        $invoice = DeliveryInvoice::create([
            'invoice_number' => $request->invoice_number,
            'delivery_instruction_id' => $di->id,
            'user_id' => Auth::id(),
            'customer_name' => $di->customer_name,
            'end_user_name' => $di->end_user_name,
            'so_reference' => $di->so_reference,
            'lump_sum_amount' => $lumpSum,
            'total_amount' => $totalInvoiceAmount,
            'status' => 'Unpaid',
        ]);

        foreach ($itemsData as $data) {
            $invoice->items()->create($data);
        }

        $assignedDriver = DeliveryNote::where('delivery_instruction_id', $di->id)
            ->whereNotNull('driver')
            ->value('driver');

        ChequeCollection::create([
            'user_id' => Auth::id(),
            'collection_ref' => 'CHQ-'.$invoice->invoice_number,
            'customer_name' => $di->customer_name,
            'collection_location' => $di->delivery_address ?? 'Customer Site',
            'amount' => $totalInvoiceAmount,
            'so_reference' => $di->so_reference ?: $di->di_number,
            'invoice_reference' => $invoice->invoice_number,
            'driver' => $assignedDriver ?: '-',
            'status' => 'Pending',
        ]);

        return redirect()->route('delivery-invoices.index')->with('success', "Delivery Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show($id)
    {
        $invoice = DeliveryInvoice::with(['deliveryInstruction', 'items', 'user'])->findOrFail($id);

        return view('dashboards.delivery_invoices.show', compact('invoice'));
    }

    public function print($id)
    {
        $invoice = DeliveryInvoice::with(['deliveryInstruction', 'items', 'user'])->findOrFail($id);

        return view('dashboards.delivery_invoices.print', compact('invoice'));
    }

    public function destroy($id)
    {
        $invoice = DeliveryInvoice::findOrFail($id);
        $num = $invoice->invoice_number;
        ChequeCollection::where('invoice_reference', $num)->delete();
        $invoice->delete();

        return redirect()->route('delivery-invoices.index')->with('success', "Delivery Invoice {$num} deleted successfully.");
    }

    public function updateRemarks(Request $request, $id)
    {
        $invoice = DeliveryInvoice::findOrFail($id);

        $request->validate([
            'remarks' => 'nullable|string',
        ]);

        $invoice->update([
            'remarks' => $request->remarks,
        ]);

        return redirect()->back()->with('success', 'Remarks updated successfully.');
    }

    public function updateStatus(Request $request, $id)
    {
        $invoice = DeliveryInvoice::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:Unpaid,Processing,Paid',
        ]);

        $invoice->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', "Delivery Invoice status updated to '{$request->status}' successfully.");
    }
}
