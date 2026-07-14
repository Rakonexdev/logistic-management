<?php

namespace App\Http\Controllers;

use App\Models\ChequeCollection;
use App\Models\ReturnPickup;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function index()
    {
        $driverName = Auth::user()->name;

        // Fetch deliveries assigned to this driver
        $deliveries = SalesOrder::with('items')
            ->where('driver', $driverName)
            ->whereIn('delivery_status', ['Assigned', 'Arrived', 'Delivered', 'Issue Reported'])
            ->latest()
            ->get();

        // Fetch returns assigned to this driver
        $returns = ReturnPickup::where('driver', $driverName)
            ->latest()
            ->get();

        // Fetch cheques assigned to this driver
        $cheques = ChequeCollection::where('driver', $driverName)
            ->latest()
            ->get();

        return view('driver.dashboard', compact('deliveries', 'returns', 'cheques'));
    }

    public function markArrived(SalesOrder $order)
    {
        if ($order->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $order->update([
            'delivery_status' => 'Arrived',
            'arrived_at' => now(),
        ]);

        return back()->with('success', "Arrived at customer location for Delivery DEL-{$order->id}.");
    }

    public function markDelivered(Request $request, SalesOrder $order)
    {
        if ($order->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'recipient_name' => ['required', 'string', 'max:255'],
            'delivery_remarks' => ['nullable', 'string'],
            'signed_proof' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'delivery_photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $signedProofPath = null;
        $deliveryPhotoPath = null;

        if ($request->hasFile('signed_proof')) {
            $dir = public_path('uploads/proofs');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('signed_proof');
            $filename = time().'_sig_'.$order->id.'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $signedProofPath = 'uploads/proofs/'.$filename;
        }

        if ($request->hasFile('delivery_photo')) {
            $dir = public_path('uploads/photos');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('delivery_photo');
            $filename = time().'_photo_'.$order->id.'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $deliveryPhotoPath = 'uploads/photos/'.$filename;
        }

        $order->update([
            'delivery_status' => 'Delivered',
            'recipient_name' => $request->recipient_name,
            'signed_proof_path' => $signedProofPath,
            'delivery_photo_path' => $deliveryPhotoPath,
            'delivery_remarks' => $request->delivery_remarks,
            'delivery_completed_at' => now(),
        ]);

        return back()->with('success', "Delivery DEL-{$order->id} completed successfully.");
    }

    public function reportDeliveryIssue(Request $request, SalesOrder $order)
    {
        if ($order->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'delivery_issue' => ['required', 'string'],
        ]);

        $order->update([
            'delivery_status' => 'Issue Reported',
            'delivery_issue' => $request->delivery_issue,
        ]);

        return back()->with('success', "Issue reported for Delivery DEL-{$order->id}.");
    }

    public function startPickup(ReturnPickup $returnPickup)
    {
        if ($returnPickup->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $returnPickup->update([
            'status' => 'Pickup Started',
        ]);

        return back()->with('success', "Return pickup {$returnPickup->return_ref} started.");
    }

    public function completePickup(Request $request, ReturnPickup $returnPickup)
    {
        if ($returnPickup->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'quantity_picked_up' => ['required', 'integer', 'min:0'],
            'condition_data' => ['required', 'string'],
            'remarks' => ['nullable', 'string'],
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $dir = public_path('uploads/returns');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('photo');
            $filename = time().'_ret_'.$returnPickup->id.'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $photoPath = 'uploads/returns/'.$filename;
        }

        $returnPickup->update([
            'status' => 'Completed',
            'quantity_picked_up' => $request->quantity_picked_up,
            'condition_data' => $request->condition_data,
            'photo_path' => $photoPath,
            'remarks' => $request->remarks,
        ]);

        return back()->with('success', "Return pickup {$returnPickup->return_ref} confirmed completed.");
    }

    public function submitHandover(ReturnPickup $returnPickup)
    {
        if ($returnPickup->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $returnPickup->update([
            'status' => 'Returned to Warehouse',
        ]);

        return back()->with('success', "Handover submitted for return {$returnPickup->return_ref}.");
    }

    public function collectCheque(Request $request, ChequeCollection $chequeCollection)
    {
        if ($chequeCollection->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'photo' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
            'remarks' => ['nullable', 'string'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $dir = public_path('uploads/cheques');
            if (! file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            $file = $request->file('photo');
            $filename = time().'_chq_'.$chequeCollection->id.'.'.$file->getClientOriginalExtension();
            $file->move($dir, $filename);
            $photoPath = 'uploads/cheques/'.$filename;
        }

        $chequeCollection->update([
            'status' => 'Collected',
            'photo_path' => $photoPath,
            'remarks' => $request->remarks,
            'submission_time' => now(),
        ]);

        return back()->with('success', "Cheque {$chequeCollection->collection_ref} collected successfully.");
    }

    public function submitCheque(ChequeCollection $chequeCollection)
    {
        if ($chequeCollection->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $chequeCollection->update([
            'status' => 'Submitted',
        ]);

        return back()->with('success', "Cheque collection {$chequeCollection->collection_ref} submitted.");
    }

    public function reportChequeIssue(Request $request, ChequeCollection $chequeCollection)
    {
        if ($chequeCollection->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'remarks' => ['required', 'string'],
        ]);

        $chequeCollection->update([
            'status' => 'Issue Reported',
            'remarks' => $request->remarks,
        ]);

        return back()->with('success', "Issue reported for Cheque {$chequeCollection->collection_ref}.");
    }
}
