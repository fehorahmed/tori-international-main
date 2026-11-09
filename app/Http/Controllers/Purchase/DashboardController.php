<?php

namespace App\Http\Controllers\Purchase;

use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $sisterConcernId = auth()->user()->activeConcernId();

        // 1. Total Suppliers
        $totalSuppliers = Supplier::where('type', SupplierType::$SUPPLIER)->count();

        // 2. Total Purchase Orders
        $totalPurchaseOrders = PurchaseOrder::where('sister_concern_id', $sisterConcernId)->count();

        // 3. Total Purchase Amount (using total from PurchaseOrder)
        $totalPurchaseAmount = PurchaseOrder::where('sister_concern_id', $sisterConcernId)->sum('total');

        // 4. Total Paid Amount
        $totalPaid = PurchaseOrder::where('sister_concern_id', $sisterConcernId)->sum('paid');

        // 5. Total Due Amount
        $suppliers = Supplier::where('type', SupplierType::$SUPPLIER)
            ->where('origin_sister_concern_id', $sisterConcernId)
            ->get();
            
        $totalDue = 0;
        foreach ($suppliers as $supplier) {
            $balance = $supplier->supplier_current_balance;
            if ($balance > 0) {
                $totalDue += $balance;
            }
        }

        // 6. Recent Purchases
        $recentPurchases = PurchaseOrder::with('supplier')
            ->where('sister_concern_id', $sisterConcernId)
            ->whereDate('date', auth()->user()->business_date)
            ->orderBy('id', 'desc')
            ->limit(5)
            ->get();

        return view('purchase.dashboard', compact(
            'totalSuppliers',
            'totalPurchaseOrders',
            'totalPurchaseAmount',
            'totalPaid',
            'totalDue',
            'recentPurchases'
        ));
    }
}
