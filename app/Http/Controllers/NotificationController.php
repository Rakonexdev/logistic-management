<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of the notifications.
     */
    public function index(Request $request)
    {
        $query = Auth::user()->notifications()->latest();

        // Filter by module
        if ($request->filled('module')) {
            $query->where('module', $request->module);
        }

        // Filter by type (info, success, warning, error)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by read/unread status
        if ($request->filled('read_status')) {
            $isRead = $request->read_status === 'read';
            $query->where('is_read', $isRead);
        }

        // Filter by date range
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [$request->start_date.' 00:00:00', $request->end_date.' 23:59:59']);
        }

        // Search by document reference
        if ($request->filled('search')) {
            $query->where('doc_reference', 'like', '%'.$request->search.'%');
        }

        if ($request->wantsJson() || $request->ajax()) {
            $notifications = $query->paginate(10);

            return response()->json($notifications);
        }

        $notifications = $query->paginate(15)->withQueryString();

        // Get unique modules for the filter dropdown
        $modules = Auth::user()->notifications()->select('module')->distinct()->pluck('module');

        return view('dashboards.notifications.index', compact('notifications', 'modules'));
    }

    /**
     * Get the unread notification count.
     */
    public function getUnreadCount(): JsonResponse
    {
        $count = Auth::user()->notifications()->where('is_read', false)->count();

        return response()->json(['unread_count' => $count]);
    }

    /**
     * Mark the specified notification as read.
     */
    public function markAsRead(int $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->update(['is_read' => true]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        Auth::user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'All notifications marked as read.');
    }

    /**
     * Remove the specified notification from storage.
     */
    public function destroy(int $id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->delete();

        if (request()->wantsJson() || request()->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Notification deleted successfully.');
    }

    /**
     * Trigger demo notifications for testing.
     */
    public function triggerDemoNotification(Request $request)
    {
        $user = Auth::user();

        $demos = [
            [
                'title' => 'Sales Order Draft Created',
                'message' => 'A new Sales Order draft SO-2026-0001 has been successfully initialized.',
                'type' => 'success',
                'module' => 'sales_orders',
                'doc_reference' => 'SO-2026-0001',
                'action_url' => route('sales-orders.index'),
                'sender' => 'System',
                'status' => 'pending',
                'is_read' => false,
            ],
            [
                'title' => 'ASN Pending Review',
                'message' => 'Advance Shipping Note ASN-REF-9988 requires warehouse verification.',
                'type' => 'warning',
                'module' => 'asns',
                'doc_reference' => 'ASN-REF-9988',
                'action_url' => route('asns.index'),
                'sender' => 'System',
                'status' => 'pending',
                'is_read' => false,
            ],
            [
                'title' => 'Product Inventory Low',
                'message' => 'Stock levels for Product SKU-STOCK are below the safety threshold.',
                'type' => 'error',
                'module' => 'products',
                'doc_reference' => 'SKU-STOCK',
                'action_url' => route('products.index'),
                'sender' => 'System',
                'status' => 'pending',
                'is_read' => false,
            ],
            [
                'title' => 'System Maintenance Scheduled',
                'message' => 'System maintenance is scheduled for Sunday, July 19th, from 02:00 to 04:00 AM UTC.',
                'type' => 'info',
                'module' => 'system',
                'doc_reference' => null,
                'action_url' => null,
                'sender' => 'System',
                'status' => 'pending',
                'is_read' => false,
            ],
        ];

        foreach ($demos as $demo) {
            $user->notifications()->create($demo);
        }

        return back()->with('success', 'Demo notifications triggered successfully.');
    }
}
