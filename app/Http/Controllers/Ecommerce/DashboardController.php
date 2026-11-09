<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Sales\SaleOrder;
use App\Models\Settings\Product;
use App\Enumeration\SaleType;

class DashboardController extends Controller
{
    public function index()
    {
        $totalOrders = SaleOrder::where('type', SaleType::$ECOMMERCE)->count();
        $totalRevenue = SaleOrder::where('type', SaleType::$ECOMMERCE)->sum('total');
        $totalProducts = Product::where('is_show', 1)->count();
        
        $recentOrders = SaleOrder::with('customer')
            ->where('type', SaleType::$ECOMMERCE)
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('ecommerce.dashboard', compact(
            'totalOrders', 
            'totalRevenue', 
            'totalProducts',
            'recentOrders'
        ));
    }
}
