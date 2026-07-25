<?php

namespace App\Http\Controllers;

use App\Models\ChequeCollection;
use App\Models\ChequeCollectionInvoice;
use App\Models\DeliveryInvoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChequeCollectionInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = ChequeCollectionInvoice::with(['items.chequeCollection.payments', 'user']);

        if (Auth::user()->role === 'end_user') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        $invoices = $query->latest()->paginate(10)->withQueryString();

        $totalFeeSum = ChequeCollectionInvoice::when(Auth::user()->role === 'end_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->sum('total_amount');

        $unpaidCount = ChequeCollectionInvoice::when(Auth::user()->role === 'end_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('status', 'Unpaid')->count();

        $paidCount = ChequeCollectionInvoice::when(Auth::user()->role === 'end_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('status', 'Paid')->count();

        $drivers = User::where('role', 'driver')->orderBy('name')->get();

        return view('dashboards.cheque_collection_invoices.index', compact('invoices', 'totalFeeSum', 'unpaidCount', 'paidCount', 'drivers'));
    }

    public function create()
    {
        $deliveryInvoicesQuery = DeliveryInvoice::whereHas('user', function ($q) {
            $q->where('role', 'end_user');
        })->with(['deliveryInstruction.deliveryNotes', 'user']);

        if (Auth::user()->role === 'end_user') {
            $deliveryInvoicesQuery->where('user_id', Auth::id());
        }
        $deliveryInvoices = $deliveryInvoicesQuery->latest()->get();

        // Ensure every DeliveryInvoice created by END User has a corresponding ChequeCollection record
        foreach ($deliveryInvoices as $delInv) {
            $exists = ChequeCollection::where('invoice_reference', $delInv->invoice_number)->exists();
            if (! $exists) {
                $driverName = null;
                if ($delInv->deliveryInstruction) {
                    $dn = $delInv->deliveryInstruction->deliveryNotes()->whereNotNull('driver')->first();
                    if ($dn) {
                        $driverName = $dn->driver;
                    }
                }

                ChequeCollection::create([
                    'collection_ref' => 'CHQ-'.date('Ymd').'-'.rand(1000, 9999),
                    'customer_name' => $delInv->customer_name,
                    'collection_location' => $delInv->deliveryInstruction->delivery_address ?? 'Customer Site',
                    'amount' => $delInv->total_amount,
                    'amount_usd' => $delInv->total_amount / 3.64,
                    'invoice_reference' => $delInv->invoice_number,
                    'so_reference' => $delInv->deliveryInstruction->di_number ?? null,
                    'status' => 'Pending Collection',
                    'user_id' => $delInv->user_id,
                    'driver' => $driverName ?: '-',
                ]);
            }
        }

        $chequesQuery = ChequeCollection::doesntHave('invoiceItem')
            ->whereHas('user', function ($q) {
                $q->where('role', 'end_user');
            });

        if (Auth::user()->role === 'end_user') {
            $chequesQuery->where('user_id', Auth::id());
        }

        $cheques = $chequesQuery->latest()->get();

        $defaultInvoiceNum = 'CHQ-INV-'.date('Ymd').'-'.rand(100, 999);

        return view('dashboards.cheque_collection_invoices.create', compact('cheques', 'deliveryInvoices', 'defaultInvoiceNum'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|unique:cheque_collection_invoices,invoice_number',
            'customer_name' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.cheque_collection_id' => 'required|exists:cheque_collections,id',
        ]);

        $totalInvoiceAmount = 0;
        $itemsData = [];

        foreach ($request->items as $item) {
            $cheque = ChequeCollection::findOrFail($item['cheque_collection_id']);
            $amount = (float) $cheque->amount;
            $totalInvoiceAmount += $amount;

            if ($cheque->payments()->count() === 0) {
                $cheque->update([
                    'status' => 'Pending Collection',
                    'paid_amount' => 0.00,
                ]);
            }

            $itemsData[] = [
                'cheque_collection_id' => $cheque->id,
                'collection_ref' => $cheque->collection_ref,
                'cheque_number' => $cheque->cheque_number ?: $cheque->collection_ref,
                'cheque_amount' => $amount,
                'collection_fee' => $amount,
            ];
        }

        $invoice = ChequeCollectionInvoice::create([
            'invoice_number' => $request->invoice_number,
            'user_id' => Auth::id(),
            'customer_name' => $request->customer_name,
            'total_amount' => $totalInvoiceAmount,
            'status' => 'Unpaid',
        ]);

        foreach ($itemsData as $data) {
            $invoice->items()->create($data);
        }

        return redirect()->route('cheque-collection-invoices.index')->with('success', "Cheque Collection Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show($id)
    {
        $invoice = ChequeCollectionInvoice::with(['items.chequeCollection.payments', 'user'])->findOrFail($id);

        return view('dashboards.cheque_collection_invoices.show', compact('invoice'));
    }

    public function print($id)
    {
        $invoice = ChequeCollectionInvoice::with(['items.chequeCollection.payments', 'user'])->findOrFail($id);

        return view('dashboards.cheque_collection_invoices.print', compact('invoice'));
    }

    public function markPaid($id)
    {
        $invoice = ChequeCollectionInvoice::findOrFail($id);
        $invoice->update(['status' => 'Paid']);

        return redirect()->back()->with('success', "Cheque Collection Invoice {$invoice->invoice_number} marked as Paid.");
    }

    public function assignDriver(Request $request, $id)
    {
        if (Auth::user()->role !== 'sfq_user') {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'driver' => 'required|string|max:255',
        ]);

        $invoice = ChequeCollectionInvoice::with('items.chequeCollection')->findOrFail($id);

        foreach ($invoice->items as $item) {
            if ($item->chequeCollection) {
                $item->chequeCollection->update([
                    'driver' => $request->driver,
                    'status' => 'Pending Collection',
                ]);
            }
        }

        return redirect()->back()->with('success', "Driver {$request->driver} assigned to Cheque Invoice {$invoice->invoice_number}.");
    }

    public function destroy($id)
    {
        $invoice = ChequeCollectionInvoice::findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('cheque-collection-invoices.index')->with('success', "Cheque Collection Invoice {$num} deleted successfully.");
    }
}
