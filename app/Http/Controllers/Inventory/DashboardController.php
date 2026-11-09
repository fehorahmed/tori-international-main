<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Inventory;
use App\Models\Settings\Product;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        // 1. Total Stock Quantity
        $totalStockQty = Inventory::where('sister_concern_id', $sisterConcernId)->sum('quantity');

        // 2. Total Stock Valuation (qty * unit_price)
        $totalValuation = Inventory::where('sister_concern_id', $sisterConcernId)
            ->select(DB::raw('SUM(quantity * unit_price) as valuation'))
            ->value('valuation');

        // 3. Out of Stock Products
        // Get products where sum of quantity <= 0 or no inventory exists
        $outOfStockCount = Product::where('status', 1)
            ->withSum(['inventories' => function($query) use ($sisterConcernId) {
                $query->where('sister_concern_id', $sisterConcernId);
            }], 'quantity')
            ->get()
            ->filter(function($product) {
                return $product->inventories_sum_quantity === null || $product->inventories_sum_quantity <= 0;
            })
            ->count();

        // 4. Low Stock Products (Total Qty < 10 for simplicity, or use alert_quantity if it exists)
        // Let's assume alert qty is 10 for now if there is no field
        $lowStockProducts = Product::where('status', 1)->whereHas('inventories', function($query) use ($sisterConcernId) {
                $query->where('sister_concern_id', $sisterConcernId);
            })
            ->withSum(['inventories' => function($query) use ($sisterConcernId) {
                $query->where('sister_concern_id', $sisterConcernId);
            }], 'quantity')
            ->get()
            ->filter(function($product) {
                return $product->inventories_sum_quantity > 0 && $product->inventories_sum_quantity <= 10;
            });
            
        $lowStockCount = $lowStockProducts->count();

        // 5. Recent Stock Entries
        $recentInventories = Inventory::with('product', 'warehouse')
            ->where('sister_concern_id', $sisterConcernId)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('inventory.dashboard', compact(
            'totalStockQty', 
            'totalValuation', 
            'outOfStockCount', 
            'lowStockCount', 
            'recentInventories',
            'lowStockProducts'
        ));
    }
}
