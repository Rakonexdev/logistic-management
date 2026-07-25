<?php

namespace App\Http\Controllers;

use App\Models\ChequeCollection;
use App\Models\DeliveryNote;
use App\Models\ReturnInstruction;
use App\Models\ReturnPickup;
use App\Models\SalesOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DriverDashboardController extends Controller
{
    public function index()
    {
        $driverName = Auth::user()->name;

        // Fetch deliveries assigned to this driver (Sales Orders)
        $soDeliveries = SalesOrder::with('items')
            ->where('driver', $driverName)
            ->whereIn('delivery_status', ['Assigned', 'Arrived', 'Delivered', 'Issue Reported'])
            ->latest()
            ->get()
            ->map(function ($so) {
                $so->is_delivery_note = false;
                $so->ref_number = 'DEL-'.$so->id;
                $so->display_so_number = $so->so_number;

                return $so;
            });

        // Fetch deliveries assigned to this driver (Delivery Notes)
        $dnDeliveries = DeliveryNote::with('items', 'deliveryInstruction')
            ->where('driver', $driverName)
            ->whereIn('delivery_status', ['Assigned', 'Arrived', 'Delivered', 'Issue Reported'])
            ->latest()
            ->get()
            ->map(function ($dn) {
                $dn->is_delivery_note = true;
                $dn->ref_number = $dn->dn_number;
                $dn->customer_name = $dn->deliveryInstruction->customer_name ?? 'N/A';
                $dn->customer_address = $dn->deliveryInstruction->delivery_address ?? 'N/A';
                $dn->display_so_number = $dn->deliveryInstruction->di_number ?? 'N/A';

                return $dn;
            });

        $deliveries = $soDeliveries->concat($dnDeliveries);

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

    public function markArrived($id)
    {
        if (str_starts_with($id, 'dn-')) {
            $realId = (int) str_replace('dn-', '', $id);
            $dn = DeliveryNote::findOrFail($realId);
            if ($dn->driver !== Auth::user()->name) {
                abort(403, 'Unauthorized.');
            }
            $dn->update([
                'delivery_status' => 'Arrived',
                'arrived_at' => now(),
            ]);

            return back()->with('success', "Arrived at customer location for Delivery {$dn->dn_number}.");
        }

        $realId = (int) str_replace('so-', '', $id);
        $order = SalesOrder::findOrFail($realId);
        if ($order->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $order->update([
            'delivery_status' => 'Arrived',
            'arrived_at' => now(),
        ]);

        return back()->with('success', "Arrived at customer location for Delivery DEL-{$order->id}.");
    }

    public function markDelivered(Request $request, $id)
    {
        $request->validate([
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'delivery_remarks' => ['nullable', 'string'],
            'signed_proof' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf,heic,svg', 'max:15360'],
            'delivery_photo' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf,heic,svg', 'max:15360'],
        ]);

        $isDn = str_starts_with($id, 'dn-');
        $realId = (int) str_replace(['dn-', 'so-'], '', $id);

        if ($isDn) {
            $model = DeliveryNote::findOrFail($realId);
        } else {
            $model = SalesOrder::findOrFail($realId);
        }

        if ($model->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $signedProofPath = null;
        $deliveryPhotoPath = null;

        if ($request->hasFile('signed_proof')) {
            $file = $request->file('signed_proof');
            $filename = time().'_sig_'.$realId.'.'.$file->getClientOriginalExtension();
            if (app()->environment('testing')) {
                $file->storeAs('uploads/proofs', $filename, 'public');
            } else {
                $dir = public_path('uploads/proofs');
                if (! file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file->move($dir, $filename);
            }
            $signedProofPath = 'uploads/proofs/'.$filename;
        }

        if ($request->hasFile('delivery_photo')) {
            $file = $request->file('delivery_photo');
            $filename = time().'_photo_'.$realId.'.'.$file->getClientOriginalExtension();
            if (app()->environment('testing')) {
                $file->storeAs('uploads/photos', $filename, 'public');
            } else {
                $dir = public_path('uploads/photos');
                if (! file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file->move($dir, $filename);
            }
            $deliveryPhotoPath = 'uploads/photos/'.$filename;
        }

        $model->update([
            'delivery_status' => 'Delivered',
            'recipient_name' => $request->recipient_name ?? 'N/A',
            'signed_proof_path' => $signedProofPath,
            'delivery_photo_path' => $deliveryPhotoPath,
            'delivery_remarks' => $request->delivery_remarks,
            'delivery_completed_at' => now(),
        ]);

        $ref = $isDn ? $model->dn_number : 'DEL-'.$model->id;

        return back()->with('success', "Delivery {$ref} completed successfully.");
    }

    public function reportDeliveryIssue(Request $request, $id)
    {
        $request->validate([
            'delivery_issue' => ['required', 'string'],
        ]);

        $isDn = str_starts_with($id, 'dn-');
        $realId = (int) str_replace(['dn-', 'so-'], '', $id);

        if ($isDn) {
            $model = DeliveryNote::findOrFail($realId);
        } else {
            $model = SalesOrder::findOrFail($realId);
        }

        if ($model->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $model->update([
            'delivery_status' => 'Issue Reported',
            'delivery_issue' => $request->delivery_issue,
        ]);

        $ref = $isDn ? $model->dn_number : 'DEL-'.$model->id;

        return back()->with('success', "Issue reported for Delivery {$ref}.");
    }

    public function startPickup(ReturnPickup $returnPickup)
    {
        if ($returnPickup->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $returnPickup->update([
            'status' => 'Pickup Started',
        ]);

        $refParts = explode('-', $returnPickup->return_ref);
        if (count($refParts) >= 3) {
            $mainRef = $refParts[0].'-'.$refParts[1].'-'.$refParts[2];
            ReturnInstruction::where('return_ref', $mainRef)->update(['status' => 'Pickup Started']);
        }

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
            'photo' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf,heic,svg', 'max:15360'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'_ret_'.$returnPickup->id.'.'.$file->getClientOriginalExtension();
            if (app()->environment('testing')) {
                $file->storeAs('uploads/returns', $filename, 'public');
            } else {
                $dir = public_path('uploads/returns');
                if (! file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file->move($dir, $filename);
            }
            $photoPath = 'uploads/returns/'.$filename;
        }

        $returnPickup->update([
            'status' => 'Completed',
            'quantity_picked_up' => $request->quantity_picked_up,
            'condition_data' => $request->condition_data,
            'photo_path' => $photoPath,
            'remarks' => $request->remarks,
        ]);

        $refParts = explode('-', $returnPickup->return_ref);
        if (count($refParts) >= 3) {
            $mainRef = $refParts[0].'-'.$refParts[1].'-'.$refParts[2];
            ReturnInstruction::where('return_ref', $mainRef)->update([
                'status' => 'Picked Up',
                'picking_date' => now(),
            ]);
        }

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

        $refParts = explode('-', $returnPickup->return_ref);
        if (count($refParts) >= 3) {
            $mainRef = $refParts[0].'-'.$refParts[1].'-'.$refParts[2];
            ReturnInstruction::where('return_ref', $mainRef)->update([
                'status' => 'Stored',
                'storing_date' => now(),
            ]);
        }

        return back()->with('success', "Handover submitted for return {$returnPickup->return_ref}.");
    }

    public function collectCheque(Request $request, ChequeCollection $chequeCollection)
    {
        if ($chequeCollection->driver !== Auth::user()->name) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'amount' => ['nullable', 'numeric', 'min:0.01'],
            'cheque_number' => ['nullable', 'string', 'max:255'],
            'cheque_date' => ['nullable', 'date'],
            'photo' => ['required', 'file', 'mimes:jpeg,png,jpg,gif,webp,pdf,heic,svg', 'max:15360'],
            'remarks' => ['nullable', 'string'],
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time().'_chq_'.$chequeCollection->id.'_'.rand(100, 999).'.'.$file->getClientOriginalExtension();
            if (app()->environment('testing')) {
                $file->storeAs('uploads/cheques', $filename, 'public');
            } else {
                $dir = public_path('uploads/cheques');
                if (! file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $file->move($dir, $filename);
            }
            $photoPath = 'uploads/cheques/'.$filename;
        }

        $totalAmount = (float) $chequeCollection->amount;
        $currentPaidAmount = (float) ($request->amount ?: $chequeCollection->amount);
        $previousPaid = (float) $chequeCollection->paid_amount;
        $newTotalPaid = $previousPaid + $currentPaidAmount;
        $remainingBalance = max(0, $totalAmount - $newTotalPaid);

        $existingPaymentCount = $chequeCollection->payments()->count();
        $nextPaymentNumber = $existingPaymentCount + 1;

        $chequeNumber = $request->cheque_number ?: ($chequeCollection->cheque_number ?: $chequeCollection->collection_ref.'-P'.$nextPaymentNumber);

        $chequeCollection->payments()->create([
            'payment_number' => $nextPaymentNumber,
            'paid_amount' => $currentPaidAmount,
            'remaining_balance' => $remainingBalance,
            'cheque_number' => $chequeNumber,
            'cheque_date' => $request->cheque_date ?: now(),
            'photo_path' => $photoPath,
            'driver' => Auth::user()->name,
            'remarks' => $request->remarks,
        ]);

        $chequeCollection->update([
            'paid_amount' => $newTotalPaid,
            'cheque_number' => $chequeNumber,
            'cheque_date' => $request->cheque_date ?: ($chequeCollection->cheque_date ?: now()),
            'status' => 'Collected',
            'photo_path' => $photoPath,
            'remarks' => $request->remarks ?: $chequeCollection->remarks,
            'submission_time' => now(),
        ]);

        return back()->with('success', "Payment #{$nextPaymentNumber} of QAR ".number_format($currentPaidAmount, 2)." recorded for {$chequeCollection->collection_ref}. Remaining balance: QAR ".number_format($remainingBalance, 2).'.');
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
