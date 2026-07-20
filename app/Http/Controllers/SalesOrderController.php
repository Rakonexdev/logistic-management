<?php

namespace App\Http\Controllers;

use App\Models\AsnItem;
use App\Models\Product;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SalesOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = SalesOrder::where('user_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('so_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(10)->withQueryString();

        return view('dashboards.sales_orders.index', compact('orders'));
    }

    public function create(): View
    {
        $products = Product::orderBy('sku_code')->get();

        return view('dashboards.sales_orders.create', compact('products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'so_number' => 'required|string|unique:sales_orders,so_number',
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'remarks' => 'nullable|string',
            'excel_file' => 'nullable|file|mimes:csv,txt,xlsx,xls|max:5120',
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:draft,submitted',
        ]);

        $order = new SalesOrder;
        $order->so_number = $request->so_number;
        $order->customer_name = $request->customer_name;
        $order->customer_address = $request->customer_address;
        $order->order_date = $request->order_date;
        $order->remarks = $request->remarks;
        $order->status = $request->status;
        $order->user_id = Auth::id();

        if ($request->hasFile('excel_file')) {
            $order->excel_file_path = $request->file('excel_file')->store('sales_order_excels', 'public');
        }

        if ($request->hasFile('pdf_file')) {
            $order->pdf_file_path = $request->file('pdf_file')->store('sales_order_pdfs', 'public');
        }

        $order->save();

        foreach ($request->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'sku_code' => $item['sku_code'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('sales-orders.index')->with('success', 'Sales Order '.ucfirst($order->status).' successfully.');
    }

    public function show(int $id): View
    {
        $order = SalesOrder::with('items')->where('user_id', Auth::id())->findOrFail($id);

        return view('dashboards.sales_orders.show', compact('order'));
    }

    public function edit(int $id): View
    {
        $order = SalesOrder::with('items')->where('user_id', Auth::id())->findOrFail($id);
        $products = Product::orderBy('sku_code')->get();

        return view('dashboards.sales_orders.edit', compact('order', 'products'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $order = SalesOrder::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'so_number' => 'required|string|unique:sales_orders,so_number,'.$order->id,
            'customer_name' => 'required|string|max:255',
            'customer_address' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'remarks' => 'nullable|string',
            'excel_file' => 'nullable|file|mimes:csv,txt,xlsx,xls|max:5120',
            'pdf_file' => 'nullable|file|mimes:pdf|max:5120',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'status' => 'required|in:draft,submitted',
        ]);

        $order->so_number = $request->so_number;
        $order->customer_name = $request->customer_name;
        $order->customer_address = $request->customer_address;
        $order->order_date = $request->order_date;
        $order->remarks = $request->remarks;
        $order->status = $request->status;

        if ($request->hasFile('excel_file')) {
            if ($order->excel_file_path) {
                Storage::disk('public')->delete($order->excel_file_path);
            }
            $order->excel_file_path = $request->file('excel_file')->store('sales_order_excels', 'public');
        }

        if ($request->hasFile('pdf_file')) {
            if ($order->pdf_file_path) {
                Storage::disk('public')->delete($order->pdf_file_path);
            }
            $order->pdf_file_path = $request->file('pdf_file')->store('sales_order_pdfs', 'public');
        }

        $order->save();

        // Sync items
        $order->items()->delete();
        foreach ($request->items as $item) {
            SalesOrderItem::create([
                'sales_order_id' => $order->id,
                'sku_code' => $item['sku_code'],
                'quantity' => $item['quantity'],
            ]);
        }

        return redirect()->route('sales-orders.index')->with('success', 'Sales Order updated successfully.');
    }

    public function checkStock(Request $request): JsonResponse
    {
        $items = $request->input('items', []);
        $results = [];

        foreach ($items as $item) {
            $sku = $item['sku_code'] ?? null;
            if (! $sku) {
                continue;
            }

            // Check if product exists
            $product = Product::where('sku_code', $sku)->first();
            if (! $product) {
                $results[$sku] = [
                    'available' => 0,
                    'status' => 'not_found',
                    'name' => 'Unknown Product',
                ];

                continue;
            }

            $inboundAsnSum = AsnItem::where('sku_code', $sku)
                ->whereHas('asn', function ($query) {
                    $query->whereIn('status', ['submitted', 'processing', 'completed', 'discrepancy']);
                })
                ->get()
                ->sum(function ($item) {
                    return $item->received_qty !== null ? $item->received_qty : $item->quantity;
                });

            $outboundSo = SalesOrderItem::where('sku_code', $sku)
                ->whereHas('salesOrder', function ($query) {
                    $query->whereIn('status', ['submitted', 'processing', 'completed']);
                })->sum('quantity');

            $available = max(0, $product->qty + $inboundAsnSum - $outboundSo);

            $results[$sku] = [
                'available' => (int) $available,
                'status' => $product->status, // active/inactive
                'name' => $product->name,
            ];
        }

        return response()->json(['stocks' => $results]);
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=sales_order_template.csv',
        ];
        $columns = ['sku_code', 'quantity'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $samples = [
                ['SKU-1001', '10'],
                ['SKU-1002', '5'],
                ['SKU-1003', '15'],
            ];

            foreach ($samples as $sample) {
                fputcsv($file, $sample);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    public function export(Request $request)
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=sales_orders_export.csv',
        ];

        $callback = function () use ($request) {
            $file = fopen('php://output', 'w');
            $columns = ['ID', 'SO Number', 'Customer Name', 'Customer Address', 'Order Date', 'Status', 'Created At'];
            fputcsv($file, $columns);

            $query = SalesOrder::where('user_id', Auth::id());

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('so_number', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_address', 'like', "%{$search}%");
                });
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            $query->chunk(100, function ($orders) use ($file) {
                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->id,
                        $order->so_number,
                        $order->customer_name,
                        $order->customer_address,
                        $order->order_date->format('Y-m-d'),
                        $order->status,
                        $order->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
