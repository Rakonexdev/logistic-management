<?php

namespace App\Http\Controllers;

use App\Models\AdvanceShippingNote;
use App\Models\AsnItem;
use App\Models\ChequeCollectionInvoice;
use App\Models\DeliveryInstruction;
use App\Models\DeliveryInstructionItem;
use App\Models\DeliveryInvoice;
use App\Models\DeliveryNote;
use App\Models\Location;
use App\Models\Product;
use App\Models\RentInvoice;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function endUser(Request $request)
    {
        $metrics = $this->getDashboardMetrics($request);
        $monthlyStockData = $this->getMonthlyStockMovement($request);
        $customers = User::where('role', 'end_user')->orderBy('name')->get();
        $warehouses = Location::whereNotNull('warehouse')->distinct()->pluck('warehouse');

        return view('dashboards.end_user', compact('metrics', 'monthlyStockData', 'customers', 'warehouses'));
    }

    public function sfqUser(Request $request)
    {
        $metrics = $this->getDashboardMetrics($request);
        $monthlyStockData = $this->getMonthlyStockMovement($request);
        $customers = User::where('role', 'end_user')->orderBy('name')->get();
        $warehouses = Location::whereNotNull('warehouse')->distinct()->pluck('warehouse');

        return view('dashboards.sfq_user', compact('metrics', 'monthlyStockData', 'customers', 'warehouses'));
    }

    private function getDashboardMetrics(Request $request): array
    {
        $userId = Auth::user()->role === 'end_user' ? Auth::id() : null;

        $filterDate = $request->input('date');
        $filterCustomerId = $request->input('customer_id');
        $filterWarehouse = $request->input('warehouse');
        $filterStatus = $request->input('status');

        if ($filterCustomerId && $filterCustomerId !== 'all') {
            $userId = (int) $filterCustomerId;
        }

        // 1. Stocks Metric
        $productQuery = Product::query();
        if ($filterStatus && $filterStatus !== 'all') {
            $productQuery->where('status', $filterStatus);
        }
        $totalStock = (int) $productQuery->sum('qty');
        $totalSkus = (int) $productQuery->count();

        // 2. ASN Metric
        $asnQuery = AdvanceShippingNote::query();
        if ($userId) {
            $asnQuery->where('user_id', $userId);
        }
        if ($filterDate) {
            $asnQuery->whereDate('created_at', '<=', $filterDate);
        }
        if ($filterStatus && $filterStatus !== 'all') {
            $asnQuery->where('status', $filterStatus);
        }
        $totalAsn = (int) $asnQuery->count();

        $completedAsnQuery = (clone $asnQuery)->whereIn('status', ['completed', 'received', 'confirmed']);
        $completedAsn = (int) $completedAsnQuery->count();
        $asnPercentage = $totalAsn > 0 ? (int) round(($completedAsn / $totalAsn) * 100) : 0;

        // 3. Active Deliveries Metric
        $diQuery = DeliveryInstruction::query();
        if ($userId) {
            $diQuery->where('user_id', $userId);
        }
        if ($filterDate) {
            $diQuery->whereDate('created_at', '<=', $filterDate);
        }
        if ($filterStatus && $filterStatus !== 'all') {
            $diQuery->where('status', $filterStatus);
        }
        $totalDeliveries = (int) $diQuery->count();

        $dnQuery = DeliveryNote::query();
        if ($userId) {
            $dnQuery->where('user_id', $userId);
        }
        if ($filterDate) {
            $dnQuery->whereDate('created_at', '<=', $filterDate);
        }
        $inTransitCount = (int) (clone $dnQuery)->whereIn('status', ['released', 'dispatched', 'in_transit'])->count();
        $deliveriesPercentage = $totalDeliveries > 0 ? (int) round(($inTransitCount / $totalDeliveries) * 100) : 0;

        // 4. Open Invoices Metric
        $delivInvoiceQuery = DeliveryInvoice::whereNotIn('status', ['paid', 'Paid', 'completed']);
        $rentInvoiceQuery = RentInvoice::whereNotIn('status', ['paid', 'Paid']);
        $chequeInvoiceQuery = ChequeCollectionInvoice::whereNotIn('status', ['paid', 'Paid']);

        if ($userId) {
            $delivInvoiceQuery->where('user_id', $userId);
            $rentInvoiceQuery->where('user_id', $userId);
            $chequeInvoiceQuery->where('user_id', $userId);
        }
        if ($filterDate) {
            $delivInvoiceQuery->whereDate('created_at', '<=', $filterDate);
            $rentInvoiceQuery->whereDate('created_at', '<=', $filterDate);
            $chequeInvoiceQuery->whereDate('created_at', '<=', $filterDate);
        }

        $openDelivAmount = (float) $delivInvoiceQuery->sum('total_amount');
        $openRentAmount = (float) $rentInvoiceQuery->sum('total_amount');
        $openChequeAmount = (float) $chequeInvoiceQuery->sum('total_amount');
        $totalOpenInvoicesAmount = $openDelivAmount + $openRentAmount + $openChequeAmount;

        $openDelivCount = (int) $delivInvoiceQuery->count();
        $openRentCount = (int) $rentInvoiceQuery->count();
        $openChequeCount = (int) $chequeInvoiceQuery->count();
        $totalOpenInvoicesCount = $openDelivCount + $openRentCount + $openChequeCount;

        return [
            'stocks' => [
                'total_qty' => $totalStock,
                'total_skus' => $totalSkus,
            ],
            'asn' => [
                'total' => $totalAsn,
                'completed' => $completedAsn,
                'percentage' => $asnPercentage,
            ],
            'deliveries' => [
                'total' => $totalDeliveries,
                'in_transit' => $inTransitCount,
                'percentage' => $deliveriesPercentage,
            ],
            'open_invoices' => [
                'total_amount' => $totalOpenInvoicesAmount,
                'count' => $totalOpenInvoicesCount,
            ],
        ];
    }

    private function getMonthlyStockMovement(?Request $request = null)
    {
        $months = [];
        $stockIn = [];
        $stockOut = [];

        $filterCustomerId = $request ? $request->input('customer_id') : null;

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->format('M Y');

            $months[] = $monthLabel;

            $inQty = AsnItem::whereHas('asn', function ($q) use ($date, $filterCustomerId) {
                if (Auth::user()->role === 'end_user') {
                    $q->where('user_id', Auth::id());
                } elseif ($filterCustomerId && $filterCustomerId !== 'all') {
                    $q->where('user_id', $filterCustomerId);
                }
                $q->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
            })->sum('quantity');

            $outQty = DeliveryInstructionItem::whereHas('deliveryInstruction', function ($q) use ($date, $filterCustomerId) {
                if (Auth::user()->role === 'end_user') {
                    $q->where('user_id', Auth::id());
                } elseif ($filterCustomerId && $filterCustomerId !== 'all') {
                    $q->where('user_id', $filterCustomerId);
                }
                $q->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
            })->sum('quantity');

            $stockIn[] = (int) $inQty;
            $stockOut[] = (int) $outQty;
        }

        return [
            'labels' => $months,
            'stock_in' => $stockIn,
            'stock_out' => $stockOut,
        ];
    }
}
