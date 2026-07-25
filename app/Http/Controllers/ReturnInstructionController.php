<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ReturnInstruction;
use App\Models\ReturnInstructionItem;
use App\Models\ReturnPickup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReturnInstructionController extends Controller
{
    public function index(Request $request)
    {
        $query = ReturnInstruction::with('items')
            ->where('user_id', Auth::id());

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('return_ref', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('pickup_address', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $instructions = $query->latest()->paginate(10)->withQueryString();

        return view('dashboards.return_instructions.index', compact('instructions'));
    }

    public function create()
    {
        $products = Product::orderBy('sku_code')->get();
        $defaultRef = 'RET-'.date('Ymd').'-'.rand(100, 999);

        return view('dashboards.return_instructions.create', compact('products', 'defaultRef'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'return_ref' => 'required|string|unique:return_instructions,return_ref',
            'customer_name' => 'required|string|max:255',
            'return_type' => 'nullable|string|in:Return to Warehouse,Shipping to Company Return',
            'pickup_address' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'attachment' => 'nullable|file|max:10240',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.sku_code' => 'required|string',
            'items.*.description' => 'nullable|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.serial_numbers' => 'nullable|string',
        ]);

        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('returns', 'public');
        }

        $instruction = ReturnInstruction::create([
            'user_id' => Auth::id(),
            'return_ref' => $request->return_ref,
            'customer_name' => $request->customer_name,
            'return_type' => $request->return_type ?? 'Return to Warehouse',
            'pickup_address' => $request->pickup_address,
            'contact_person' => $request->contact_person,
            'contact_phone' => $request->contact_phone,
            'attachment' => $attachmentPath,
            'status' => 'Created',
            'instruction_received_date' => now(),
            'remarks' => $request->remarks,
            'inspection_status' => 'Passed',
        ]);

        foreach ($request->items as $item) {
            ReturnInstructionItem::create([
                'return_instruction_id' => $instruction->id,
                'sku_code' => $item['sku_code'],
                'description' => $item['description'] ?? null,
                'quantity' => (int) $item['quantity'],
                'serial_numbers' => $item['serial_numbers'] ?? null,
            ]);

            // Sync to ReturnPickup model for SFQ operational compatibility
            ReturnPickup::create([
                'return_ref' => $request->return_ref.'-'.$item['sku_code'],
                'pickup_location' => $request->pickup_address,
                'product_sku' => $item['sku_code'],
                'quantity' => (int) $item['quantity'],
                'status' => 'Pending Pickup',
                'remarks' => $request->remarks,
            ]);
        }

        return redirect()->route('return-instructions.index')->with('success', "Return Instruction {$instruction->return_ref} created successfully.");
    }

    public function show($id)
    {
        $instruction = ReturnInstruction::with('items', 'user')
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('dashboards.return_instructions.show', compact('instruction'));
    }

    public function print($id)
    {
        $instruction = ReturnInstruction::with('items', 'user')
            ->findOrFail($id);

        return view('dashboards.return_instructions.print', compact('instruction'));
    }

    public function updateInspection(Request $request, $id)
    {
        $request->validate([
            'inspection_status' => 'required|in:Passed,Failed',
            'remarks' => 'nullable|string',
        ]);

        $instruction = ReturnInstruction::where('user_id', Auth::id())->findOrFail($id);

        $instruction->update([
            'inspection_status' => $request->inspection_status,
            'remarks' => $request->remarks ? $instruction->remarks."\nInspection: ".$request->remarks : $instruction->remarks,
        ]);

        return back()->with('success', "Quality Inspection updated to {$request->inspection_status}.");
    }

    public function downloadAttachment($id)
    {
        $instruction = ReturnInstruction::findOrFail($id);
        if (! $instruction->attachment) {
            abort(404, 'No attachment uploaded for this Return Instruction.');
        }

        $fullPath = storage_path('app/public/'.$instruction->attachment);
        if (! file_exists($fullPath)) {
            $fullPath = storage_path('app/'.$instruction->attachment);
        }

        if (! file_exists($fullPath)) {
            abort(404, 'File not found on server.');
        }

        return response()->file($fullPath);
    }
}
