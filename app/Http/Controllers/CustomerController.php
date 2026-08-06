<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $perPage = $request->integer('per_page', 10);
        if (! in_array($perPage, [10, 25, 50])) {
            $perPage = 10;
        }

        $customers = $query->latest()->paginate($perPage)->withQueryString();

        return view('dashboards.customers.index', compact('customers'));
    }

    /**
     * Download CSV template for bulk customer upload.
     */
    public function downloadTemplate()
    {
        $headers = [
            'Content-type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename=customers_template.csv',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fwrite($file, "\xEF\xBB\xBF");
            fputcsv($file, ['Customer Name', 'Contact Number', 'Address']);

            $samples = [
                ['Al Maha Logistics & Services', '+974 44123456', 'Building 24, Zone 61, Street 810, Al Ring Road, Doha, Qatar'],
                ['Gulf Horizon Trading WLL', '+974 55234567', 'Office 301, Marina Tower, Lusail, Qatar'],
                ['Doha Supply Chain Solutions', '+974 66345678', 'Plot 45, Street 2, Industrial Area, Doha, Qatar'],
                ['Qatar National Energy Corp', '+974 77456789', 'West Bay Commercial Complex, Corniche, Doha, Qatar'],
                ['Pearl Oasis General Contracting', '+974 33567890', 'Tower B, Floor 14, The Pearl, Qatar'],
                ['Falcon Global Freight Systems', '+971 50123456', 'Warehouse 12, Jebel Ali Free Zone, Dubai, UAE'],
                ['Emirates Commercial Enterprises', '+971 52234567', 'Al Nadd Tower, Sheikh Zayed Road, Dubai, UAE'],
                ['Sharjah Industrial Logistics', '+971 54345678', 'Street 15, Industrial Area 4, Sharjah, UAE'],
                ['Apex Middle East Solutions', '+974 50456789', 'C-Ring Road, Near Salwa Flyover, Doha, Qatar'],
                ['United Gulf Distribution Co', '+974 66567890', 'Building 8, Ring Road, Al Wakra, Qatar'],
            ];

            foreach ($samples as $sample) {
                fputcsv($file, $sample);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Handle bulk CSV upload for customers.
     */
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

        if (isset($header[0]) && str_contains(strtolower($header[0]), 'name')) {
            // Header line skipped
        } else {
            rewind($handle);
        }

        $imported = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 1) {
                continue;
            }

            $name = trim($row[0] ?? '');
            if (empty($name)) {
                continue;
            }

            $rawContact = isset($row[1]) ? trim($row[1]) : null;
            $address = isset($row[2]) ? trim($row[2]) : null;

            $contactNumber = null;
            if (! empty($rawContact)) {
                if (preg_match('/^(\+971|\+974)/', $rawContact)) {
                    $contactNumber = $rawContact;
                } else {
                    $digits = preg_replace('/[^0-9]/', '', $rawContact);
                    if (strlen($digits) === 8) {
                        $contactNumber = '+974 '.$digits;
                    } else {
                        $contactNumber = $rawContact;
                    }
                }
            }

            Customer::create([
                'name' => $name,
                'contact_number' => $contactNumber,
                'address' => $address,
            ]);

            $imported++;
        }

        fclose($handle);

        return redirect()->route('customers.index')->with('success', "Successfully imported {$imported} customer(s).");
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('dashboards.customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $this->validateAndPrepareData($request);

        Customer::create($data);

        return redirect()->route('customers.index')->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Customer $customer)
    {
        return redirect()->route('customers.edit', $customer);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer)
    {
        return view('dashboards.customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $data = $this->validateAndPrepareData($request);

        $customer->update($data);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    /**
     * Validate and prepare customer input data.
     */
    private function validateAndPrepareData(Request $request): array
    {
        if ($request->has('contact_number_digits')) {
            $request->validate([
                'name' => 'required|string|max:255',
                'country_code' => 'nullable|in:+971,+974',
                'contact_number_digits' => 'nullable|string|regex:/^[0-9]{8}$/',
                'address' => 'nullable|string|max:1000',
            ], [
                'contact_number_digits.regex' => 'Contact number must be exactly 8 digits.',
            ]);

            $contactNumber = null;
            if ($request->filled('contact_number_digits')) {
                $code = $request->input('country_code', '+974');
                $contactNumber = $code.' '.trim($request->input('contact_number_digits'));
            }

            return [
                'name' => $request->input('name'),
                'contact_number' => $contactNumber,
                'address' => $request->input('address'),
            ];
        }

        return $request->validate([
            'name' => 'required|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'address' => 'nullable|string|max:1000',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }
}
