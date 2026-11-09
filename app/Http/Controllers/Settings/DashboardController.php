<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Product;
use App\Models\Settings\Category;
use App\Models\Settings\Brand;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        $totalWarehouses = Warehouse::where('sister_concern_id', $sisterConcernId)->count();
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalBrands = Brand::where('sister_concern_id', $sisterConcernId)->count();

        return view('settings.dashboard', compact(
            'totalWarehouses',
            'totalProducts',
            'totalCategories',
            'totalBrands'
        ));
    }
}
