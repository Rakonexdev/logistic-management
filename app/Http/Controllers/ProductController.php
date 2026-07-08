<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::latest()->paginate(10);
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
}
