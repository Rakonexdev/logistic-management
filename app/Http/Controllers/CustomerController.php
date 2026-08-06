<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

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
