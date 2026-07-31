<?php

namespace App\Http\Controllers;

use App\Models\AsnItem;
use App\Models\DeliveryInstructionItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function endUser()
    {
        $monthlyStockData = $this->getMonthlyStockMovement();

        return view('dashboards.end_user', compact('monthlyStockData'));
    }

    public function sfqUser()
    {
        $monthlyStockData = $this->getMonthlyStockMovement();

        return view('dashboards.sfq_user', compact('monthlyStockData'));
    }

    private function getMonthlyStockMovement()
    {
        $months = [];
        $stockIn = [];
        $stockOut = [];

        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthLabel = $date->format('M Y');

            $months[] = $monthLabel;

            $inQty = AsnItem::whereHas('asn', function ($q) use ($date) {
                if (Auth::user()->role === 'end_user') {
                    $q->where('user_id', Auth::id());
                }
                $q->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month);
            })->sum('quantity');

            $outQty = DeliveryInstructionItem::whereHas('deliveryInstruction', function ($q) use ($date) {
                if (Auth::user()->role === 'end_user') {
                    $q->where('user_id', Auth::id());
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
