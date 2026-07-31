<?php

namespace App\Http\Controllers;

use App\Models\RentInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentInvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = RentInvoice::with('user');

        if (Auth::user()->role === 'end_user') {
            $query->where('user_id', Auth::id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('warehouse_name', 'like', "%{$search}%")
                    ->orWhere('rent_month', 'like', "%{$search}%");
            });
        }

        $invoices = $query->latest()->paginate(10)->withQueryString();

        $totalRentSum = RentInvoice::when(Auth::user()->role === 'end_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->sum('total_amount');

        $unpaidCount = RentInvoice::when(Auth::user()->role === 'end_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('status', 'Unpaid')->count();

        $paidCount = RentInvoice::when(Auth::user()->role === 'end_user', function ($q) {
            $q->where('user_id', Auth::id());
        })->where('status', 'Paid')->count();

        return view('dashboards.rent_invoices.index', compact('invoices', 'totalRentSum', 'unpaidCount', 'paidCount'));
    }

    public function create()
    {
        $defaultInvoiceNum = 'RNT-'.date('Ym').'-'.rand(100, 999);
        $currentMonth = date('F Y'); // e.g., "July 2026"
        $defaultRent = 1200.00;

        return view('dashboards.rent_invoices.create', compact('defaultInvoiceNum', 'currentMonth', 'defaultRent'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'invoice_number' => 'required|string|unique:rent_invoices,invoice_number',
            'warehouse_name' => 'required|string|max:255',
            'rent_month' => 'required|string|max:255',
            'monthly_rent_amount' => 'required|numeric|min:0',
            'utility_charges' => 'nullable|numeric|min:0',
            'due_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);

        $rentAmount = (float) $request->monthly_rent_amount;
        $utilityCharges = (float) ($request->utility_charges ?? 0);
        $totalAmount = $rentAmount + $utilityCharges;

        $invoice = RentInvoice::create([
            'invoice_number' => $request->invoice_number,
            'user_id' => Auth::id(),
            'warehouse_name' => $request->warehouse_name,
            'rent_month' => $request->rent_month,
            'monthly_rent_amount' => $rentAmount,
            'utility_charges' => $utilityCharges,
            'total_amount' => $totalAmount,
            'due_date' => $request->due_date,
            'status' => 'Unpaid',
            'remarks' => $request->remarks,
        ]);

        return redirect()->route('rent-invoices.index')->with('success', "Rent Invoice {$invoice->invoice_number} created successfully.");
    }

    public function show($id)
    {
        $invoice = RentInvoice::with('user')->findOrFail($id);

        return view('dashboards.rent_invoices.show', compact('invoice'));
    }

    public function print($id)
    {
        $invoice = RentInvoice::with('user')->findOrFail($id);

        return view('dashboards.rent_invoices.print', compact('invoice'));
    }

    public function markPaid($id)
    {
        $invoice = RentInvoice::findOrFail($id);
        $invoice->update(['status' => 'Paid']);

        return redirect()->back()->with('success', "Rent Invoice {$invoice->invoice_number} marked as Paid.");
    }

    public function updateStatus(Request $request, $id)
    {
        $invoice = RentInvoice::findOrFail($id);

        $request->validate([
            'status' => 'required|string|in:Unpaid,Processing,Paid',
        ]);

        $invoice->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', "Rent Invoice status updated to '{$request->status}' successfully.");
    }

    public function destroy($id)
    {
        $invoice = RentInvoice::findOrFail($id);
        $num = $invoice->invoice_number;
        $invoice->delete();

        return redirect()->route('rent-invoices.index')->with('success', "Rent Invoice {$num} deleted successfully.");
    }
}
