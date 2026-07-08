<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $products = $query->latest()->paginate(10)->withQueryString();
        
        return view('dashboards.products.index', compact('products'));
    }

    public function create()
    {
        return view('dashboards.products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'sku_code' => 'required|unique:products,sku_code',
            'name' => 'required|string|max:255',
            'type' => 'required|in:physical,electronic',
            'vendor_id' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        Product::create($validated);

        return redirect()->route('products.index')->with('success', 'Product created successfully.');
    }

    public function edit(Product $product)
    {
        return view('dashboards.products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'sku_code' => 'required|unique:products,sku_code,' . $product->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:physical,electronic',
            'vendor_id' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $product->update($validated);

        return redirect()->route('products.index')->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return redirect()->route('products.index')->with('success', 'Product deleted successfully.');
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=products_template.csv',
        ];
        $columns = ['sku_code', 'name', 'type', 'vendor_id', 'category', 'status'];
        
        $callback = function() use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);
            
            $samples = [
                ['SKU-1001', 'Wireless Keyboard', 'electronic', 'V-001', 'Electronics', 'active'],
                ['SKU-1002', 'Ergonomic Mouse', 'electronic', 'V-001', 'Electronics', 'active'],
                ['SKU-1003', 'Desk Organizer', 'physical', 'V-002', 'Office Supplies', 'active'],
                ['SKU-1004', '24-inch Monitor', 'electronic', 'V-003', 'Electronics', 'active'],
                ['SKU-1005', 'Noise Cancelling Headphones', 'electronic', 'V-003', 'Audio', 'active'],
                ['SKU-1006', 'Standing Desk', 'physical', 'V-004', 'Furniture', 'active'],
                ['SKU-1007', 'Office Chair', 'physical', 'V-004', 'Furniture', 'inactive'],
                ['SKU-1008', 'USB-C Hub', 'electronic', 'V-001', 'Electronics', 'active'],
                ['SKU-1009', 'Webcam 1080p', 'electronic', 'V-002', 'Electronics', 'active'],
                ['SKU-1010', 'Notebook A4', 'physical', 'V-005', 'Office Supplies', 'active'],
            ];

            foreach ($samples as $sample) {
                fputcsv($file, $sample);
            }
            
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    public function export()
    {
        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename=products_export.csv',
        ];
        
        $callback = function() {
            $file = fopen('php://output', 'w');
            $columns = ['ID', 'SKU Code', 'Name', 'Type', 'Vendor ID', 'Category', 'Status', 'Created At'];
            fputcsv($file, $columns);
            
            Product::chunk(100, function ($products) use ($file) {
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->id,
                        $product->sku_code,
                        $product->name,
                        $product->type,
                        $product->vendor_id,
                        $product->category,
                        $product->status,
                        $product->created_at->format('Y-m-d H:i:s'),
                    ]);
                }
            });
            fclose($file);
        };
        
        return Response::stream($callback, 200, $headers);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $handle = fopen($file->path(), 'r');
        
        // Skip header
        fgetcsv($handle);
        
        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 6) continue; // Basic validation
            
            $sku = trim($row[0]);
            if (empty($sku)) continue;

            Product::updateOrCreate(
                ['sku_code' => $sku],
                [
                    'name' => trim($row[1]),
                    'type' => strtolower(trim($row[2])) == 'electronic' ? 'electronic' : 'physical',
                    'vendor_id' => trim($row[3]),
                    'category' => trim($row[4]),
                    'status' => strtolower(trim($row[5])) == 'inactive' ? 'inactive' : 'active',
                ]
            );
            $count++;
        }
        
        fclose($handle);

        return redirect()->route('products.index')->with('success', "$count products imported successfully.");
    }
}
