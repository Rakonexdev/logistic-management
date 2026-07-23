<?php

namespace App\Http\Controllers;

use App\Models\AsnItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\SalesOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }

        $products = $query->latest()->paginate($perPage)->withQueryString();

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
            'qty' => 'required|integer|min:0',
            'vendor_id' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $serialNumbersArray = $request->input('serial_numbers', []);
        $enteredSerials = array_filter(array_map('trim', $serialNumbersArray));

        if (! empty($enteredSerials)) {
            $duplicates = array_diff_assoc($enteredSerials, array_unique($enteredSerials));
            if (! empty($duplicates)) {
                $dupVal = implode(', ', array_unique($duplicates));

                return back()->withErrors(['serial_number' => "Duplicate serial numbers found in your input: {$dupVal}."])->withInput();
            }

            foreach ($enteredSerials as $serial) {
                $exists = Product::where(function ($q) use ($serial) {
                    $q->where('serial_number', $serial)
                        ->orWhere('serial_number', 'like', "%,{$serial}")
                        ->orWhere('serial_number', 'like', "{$serial},%")
                        ->orWhere('serial_number', 'like', "%,{$serial},%");
                })->exists();

                if ($exists) {
                    return back()->withErrors(['serial_number' => "The serial number '{$serial}' has already been taken."])->withInput();
                }
            }
            $validated['serial_number'] = implode(', ', $enteredSerials);
        } else {
            $validated['serial_number'] = null;
        }

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
            'sku_code' => 'required|unique:products,sku_code,'.$product->id,
            'name' => 'required|string|max:255',
            'type' => 'required|in:physical,electronic',
            'qty' => 'required|integer|min:0',
            'vendor_id' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'status' => 'required|string',
        ]);

        $serialNumbersArray = $request->input('serial_numbers', []);
        $enteredSerials = array_filter(array_map('trim', $serialNumbersArray));

        if (! empty($enteredSerials)) {
            $duplicates = array_diff_assoc($enteredSerials, array_unique($enteredSerials));
            if (! empty($duplicates)) {
                $dupVal = implode(', ', array_unique($duplicates));

                return back()->withErrors(['serial_number' => "Duplicate serial numbers found in your input: {$dupVal}."])->withInput();
            }

            foreach ($enteredSerials as $serial) {
                $exists = Product::where('id', '!=', $product->id)
                    ->where(function ($q) use ($serial) {
                        $q->where('serial_number', $serial)
                            ->orWhere('serial_number', 'like', "%,{$serial}")
                            ->orWhere('serial_number', 'like', "{$serial},%")
                            ->orWhere('serial_number', 'like', "%,{$serial},%");
                    })->exists();

                if ($exists) {
                    return back()->withErrors(['serial_number' => "The serial number '{$serial}' has already been taken."])->withInput();
                }
            }
            $validated['serial_number'] = implode(', ', $enteredSerials);
        } else {
            $validated['serial_number'] = null;
        }

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
            'Content-Disposition' => 'attachment; filename=sample_products_20.csv',
        ];
        $columns = ['sku_code', 'name', 'type', 'qty', 'serial_number', 'status'];

        $callback = function () use ($columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            $samples = [
                ['SKU-SER-2001', 'Enterprise Rack Server R750', 'physical', 5, 'SN-R750-9001, SN-R750-9002, SN-R750-9003, SN-R750-9004, SN-R750-9005', 'active'],
                ['SKU-SER-2002', 'Blade Server Chassis B400', 'physical', 4, 'SN-B400-8001, SN-B400-8002, SN-B400-8003, SN-B400-8004', 'active'],
                ['SKU-MEM-2003', '128GB DDR5 ECC RAM Module', 'physical', 6, 'SN-DDR5-7001, SN-DDR5-7002, SN-DDR5-7003, SN-DDR5-7004, SN-DDR5-7005, SN-DDR5-7006', 'active'],
                ['SKU-SSD-2004', '15.36TB Enterprise NVMe SSD', 'physical', 4, 'SN-NVME-6001, SN-NVME-6002, SN-NVME-6003, SN-NVME-6004', 'active'],
                ['SKU-NIC-2005', 'Dual Port 100G PCIe NIC', 'physical', 5, 'SN-100G-5001, SN-100G-5002, SN-100G-5003, SN-100G-5004, SN-100G-5005', 'active'],
                ['SKU-SWI-2006', '48-Port Managed Core Switch', 'physical', 3, 'SN-SWI-4001, SN-SWI-4002, SN-SWI-4003', 'active'],
                ['SKU-PWR-2007', '2000W Hot-Swap Power Supply', 'physical', 6, 'SN-PWR-3001, SN-PWR-3002, SN-PWR-3003, SN-PWR-3004, SN-PWR-3005, SN-PWR-3006', 'active'],
                ['SKU-CPU-2008', '64-Core High Performance Processor', 'physical', 4, 'SN-CPU-2001, SN-CPU-2002, SN-CPU-2003, SN-CPU-2004', 'active'],
                ['SKU-GPU-2009', 'AI Workstation Accelerator GPU 80GB', 'physical', 3, 'SN-GPU-1001, SN-GPU-1002, SN-GPU-1003', 'active'],
                ['SKU-CAB-2010', '10G SFP+ Direct Attach Copper Cable', 'physical', 5, 'SN-CAB-0101, SN-CAB-0102, SN-CAB-0103, SN-CAB-0104, SN-CAB-0105', 'active'],
                ['SKU-ROU-2011', 'Industrial Edge Router', 'physical', 3, 'SN-ROU-0201, SN-ROU-0202, SN-ROU-0203', 'active'],
                ['SKU-PDU-2012', 'Smart Rack PDU 32A', 'physical', 4, 'SN-PDU-0301, SN-PDU-0302, SN-PDU-0303, SN-PDU-0304', 'active'],
                ['SKU-UPS-2013', '3000VA Online Tower UPS', 'physical', 2, 'SN-UPS-0401, SN-UPS-0402', 'active'],
                ['SKU-SFP-2014', '25G SFP28 Optical Transceiver', 'physical', 6, 'SN-SFP-0501, SN-SFP-0502, SN-SFP-0503, SN-SFP-0504, SN-SFP-0505, SN-SFP-0506', 'active'],
                ['SKU-FAN-2015', 'High CFM Server Cooling Fan Module', 'physical', 5, 'SN-FAN-0601, SN-FAN-0602, SN-FAN-0603, SN-FAN-0604, SN-FAN-0605', 'active'],
                ['SKU-RAI-2016', '2U Sliding Server Rail Kit', 'physical', 3, 'SN-RAI-0701, SN-RAI-0702, SN-RAI-0703', 'active'],
                ['SKU-STG-2017', 'SAN Storage Array Expansion Enclosure', 'physical', 2, 'SN-STG-0801, SN-STG-0802', 'active'],
                ['SKU-KVM-2018', '8-Port LCD Console KVM Switch', 'physical', 3, 'SN-KVM-0901, SN-KVM-0902, SN-KVM-0903', 'active'],
                ['SKU-MON-2019', '27-inch 4K Rack Mount Monitor', 'physical', 4, 'SN-MON-1001, SN-MON-1002, SN-MON-1003, SN-MON-1004', 'active'],
                ['SKU-SEC-2020', 'Hardware Security Module Appliance', 'physical', 2, 'SN-SEC-1101, SN-SEC-1102', 'active'],
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

        $callback = function () {
            $file = fopen('php://output', 'w');
            $columns = ['ID', 'SKU Code', 'Name', 'Type', 'QTY', 'Serial Number', 'Vendor ID', 'Category', 'Status', 'Created At'];
            fputcsv($file, $columns);

            Product::chunk(100, function ($products) use ($file) {
                foreach ($products as $product) {
                    fputcsv($file, [
                        $product->id,
                        $product->sku_code,
                        $product->name,
                        $product->type,
                        $product->qty,
                        $product->serial_number,
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

        $header = fgetcsv($handle);
        if (! $header) {
            fclose($handle);

            return back()->withErrors(['csv_file' => 'Uploaded CSV file is empty.']);
        }

        // Check if first row is header line
        if (isset($header[0]) && ! str_contains(strtolower($header[0]), 'sku')) {
            rewind($handle);
        }

        $count = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 2) {
                continue;
            }

            $sku = trim($row[0] ?? '');
            if (empty($sku)) {
                continue;
            }

            $name = trim($row[1] ?? 'Product '.$sku);
            $type = isset($row[2]) && strtolower(trim($row[2])) === 'electronic' ? 'electronic' : 'physical';
            $inputQty = isset($row[3]) ? intval(trim($row[3])) : 0;
            $serialsStr = isset($row[4]) ? trim($row[4]) : null;

            $serials = $serialsStr ? array_filter(array_map('trim', explode(',', $serialsStr))) : [];
            $serialsCount = count($serials);

            $qty = ($serialsCount > 0) ? max($inputQty, $serialsCount) : $inputQty;
            $finalSerialsStr = ! empty($serials) ? implode(', ', $serials) : null;

            $vendorId = isset($row[5]) ? trim($row[5]) : null;
            $category = isset($row[6]) ? trim($row[6]) : null;

            $status = 'active';
            foreach ($row as $colVal) {
                $val = strtolower(trim($colVal));
                if ($val === 'inactive') {
                    $status = 'inactive';
                    break;
                }
            }

            Product::updateOrCreate(
                ['sku_code' => $sku],
                [
                    'name' => $name,
                    'type' => $type,
                    'qty' => $qty,
                    'serial_number' => $finalSerialsStr,
                    'vendor_id' => $vendorId,
                    'category' => $category,
                    'status' => $status,
                ]
            );
            $count++;
        }

        fclose($handle);

        return redirect()->route('products.index')->with('success', "$count products imported successfully.");
    }

    public function stockVisibility(Request $request)
    {
        $query = Product::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('sku_code', 'like', "%{$search}%")
                    ->orWhere('name', 'like', "%{$search}%")
                    ->orWhere('serial_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }

        $products = $query->latest()->paginate($perPage)->withQueryString();

        foreach ($products as $product) {
            $inbound = $product->qty;

            $outbound = AsnItem::where('sku_code', $product->sku_code)
                ->whereHas('asn', function ($q) {
                    $q->whereIn('status', ['draft', 'submitted', 'processing', 'completed', 'discrepancy']);
                })->sum('quantity') + SalesOrderItem::where('sku_code', $product->sku_code)
                ->whereHas('salesOrder', function ($q) {
                    $q->whereIn('status', ['draft', 'submitted', 'processing', 'completed']);
                })->sum('quantity');

            $product->inbound_qty = $inbound;
            $product->outbound_qty = $outbound;
            $product->available_qty = max(0, $inbound - $outbound);

            // Fetch locations
            $locationsList = Location::where('sku', $product->sku_code)->get();
            if ($locationsList->isEmpty()) {
                $product->location_info = 'WH-1';
            } else {
                $locationStrings = [];
                foreach ($locationsList as $loc) {
                    $locationStrings[] = "{$loc->warehouse} ({$loc->zone}-{$loc->rack}-{$loc->bin}-{$loc->level})";
                }
                $product->location_info = implode(', ', $locationStrings);
            }
        }

        $categories = Product::whereNotNull('category')
            ->where('category', '<>', '')
            ->distinct()
            ->orderBy('category')
            ->pluck('category');

        return view('dashboards.products.stock_visibility', compact('products', 'categories'));
    }
}
