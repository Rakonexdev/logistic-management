<?php

namespace App\Http\Controllers;

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;

class AsnController extends Controller
{
    public function index(Request $request)
    {
        $query = AdvanceShippingNote::where('user_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asn_reference', 'like', "%{$search}%")
                    ->orWhere('vendor_id', 'like', "%{$search}%")
                    ->orWhere('airway_bill', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $asns = $query->latest()->paginate(10)->withQueryString();

        return view('dashboards.asns.index', compact('asns'));
    }

    public function create()
    {
        $products = Product::orderBy('sku_code')->get();

        return view('dashboards.asns.create', compact('products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'asn_reference' => 'required|string|unique:advance_shipping_notes,asn_reference',
            'airway_bill' => 'required|string',
            'vendor_id' => 'required|string',
            'remarks' => 'nullable|string',
            'airway_bill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'additional_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,zip,doc,docx|max:10240',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string|exists:products,sku_code',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:draft,submitted',
        ]);

        foreach ($request->items as $index => $item) {
            $product = Product::where('sku_code', $item['sku_code'])->first();
            if ($product && $item['quantity'] > $product->qty) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(["items.{$index}.quantity" => "The quantity for SKU {$item['sku_code']} cannot exceed the actual product quantity ({$product->qty})."]);
            }
        }

        $asn = new AdvanceShippingNote;
        $asn->asn_reference = $request->asn_reference;
        $asn->airway_bill = $request->airway_bill;
        $asn->vendor_id = $request->vendor_id;
        $asn->remarks = $request->remarks;
        $asn->status = $request->status;
        $asn->user_id = Auth::id();

        if ($request->hasFile('airway_bill_file')) {
            $asn->airway_bill_path = $request->file('airway_bill_file')->store('airway_bills', 'public');
        }

        if ($request->hasFile('additional_file')) {
            $asn->additional_attachments_path = $request->file('additional_file')->store('additional_attachments', 'public');
        }

        $asn->save();

        foreach ($request->items as $item) {
            AsnItem::create([
                'asn_id' => $asn->id,
                'sku_code' => $item['sku_code'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('asns.index')->with('success', 'ASN '.ucfirst($asn->status).' successfully.');
    }

    public function show($id)
    {
        $asn = AdvanceShippingNote::with('items')->where('user_id', Auth::id())->findOrFail($id);

        return view('dashboards.asns.show', compact('asn'));
    }

    public function edit($id)
    {
        $asn = AdvanceShippingNote::with('items')->where('user_id', Auth::id())->findOrFail($id);
        $products = Product::orderBy('sku_code')->get();

        return view('dashboards.asns.edit', compact('asn', 'products'));
    }

    public function update(Request $request, $id)
    {
        $asn = AdvanceShippingNote::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'asn_reference' => 'required|string|unique:advance_shipping_notes,asn_reference,'.$asn->id,
            'airway_bill' => 'required|string',
            'vendor_id' => 'required|string',
            'remarks' => 'nullable|string',
            'airway_bill_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'additional_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,zip,doc,docx|max:10240',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string|exists:products,sku_code',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:draft,submitted',
        ]);

        foreach ($request->items as $index => $item) {
            $product = Product::where('sku_code', $item['sku_code'])->first();
            if ($product && $item['quantity'] > $product->qty) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(["items.{$index}.quantity" => "The quantity for SKU {$item['sku_code']} cannot exceed the actual product quantity ({$product->qty})."]);
            }
        }

        $asn->asn_reference = $request->asn_reference;
        $asn->airway_bill = $request->airway_bill;
        $asn->vendor_id = $request->vendor_id;
        $asn->remarks = $request->remarks;
        $asn->status = $request->status;

        if ($request->hasFile('airway_bill_file')) {
            if ($asn->airway_bill_path) {
                Storage::disk('public')->delete($asn->airway_bill_path);
            }
            $asn->airway_bill_path = $request->file('airway_bill_file')->store('airway_bills', 'public');
        }

        if ($request->hasFile('additional_file')) {
            if ($asn->additional_attachments_path) {
                Storage::disk('public')->delete($asn->additional_attachments_path);
            }
            $asn->additional_attachments_path = $request->file('additional_file')->store('additional_attachments', 'public');
        }

        $asn->save();

        // Sync items
        $asn->items()->delete();
        foreach ($request->items as $item) {
            AsnItem::create([
                'asn_id' => $asn->id,
                'sku_code' => $item['sku_code'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('asns.index')->with('success', 'ASN updated successfully.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=equipment_list_template.csv',
        ];
        $columns = ['sku_code', 'quantity'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $samples = [
                ['SKU-1001', '50'],
                ['SKU-1002', '100'],
                ['SKU-1003', '25'],
            ];

            foreach ($samples as $sample) {
                fputcsv($file, $sample);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
