<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\Sale;

class DashboardController extends Controller
{
    public function index()
    {
        $currentMonth = Carbon::now()->month;

        $dailyPurchases = Sale::whereMonth('sale_date', $currentMonth)
            ->selectRaw('DATE(sale_date) as date, COUNT(*) as total')
            ->groupBy('date')
            ->get();

        $productPurchases = Sale::with('detailSales.product')
            ->whereMonth('sale_date', $currentMonth)
            ->get()
            ->flatMap(function ($sale) {
                return $sale->detailSales;
            })
            ->groupBy('product_id')
            ->map(function ($details) {
                return [
                    'product_name' => $details->first()->product->name,
                    'total' => $details->sum('amount'),
                ];
            });

        return view('dashboard', compact('dailyPurchases', 'productPurchases'));
    }
}
