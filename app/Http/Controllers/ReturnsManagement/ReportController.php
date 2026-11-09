<?php

namespace App\Http\Controllers\ReturnsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ReturnsManagement\SalesReturn;
use App\Models\ReturnsManagement\PurchaseReturn;
use App\Models\Supplier;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Category;
use App\Models\Inventory\Inventory;
use App\Enumeration\SupplierType;
use Carbon\Carbon;
use DB;

class ReportController extends Controller
{
    public function salesReturnReport(Request $request)
    {
        $customers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$DEALER, SupplierType::$CORPORATE_CUSTOMER])
            ->get();
            
        if ($request->has('search')) {
            $query = SalesReturn::with('customer')
                ->where('sister_concern_id', auth()->user()->activeConcernId());

            if ($request->customer_id != '') {
                $query->where('customer_id', $request->customer_id);
            }

            $query->whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            
            $returns = $query->with('customer', 'saleOrder', 'returnWarehouse')->orderBy('date', 'desc')->get();

            return view('returns_management.reports.sales_return_report_data', compact('returns'));
        }
        
        return view('returns_management.reports.sales_return_report', compact('customers'));
    }

    public function purchaseReturnReport(Request $request)
    {
        $suppliers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', 2) // 2 = Supplier
            ->get();
            
        if ($request->has('search')) {
            $query = PurchaseReturn::with('supplier')
                ->where('sister_concern_id', auth()->user()->activeConcernId());

            if ($request->supplier_id != '') {
                $query->where('supplier_id', $request->supplier_id);
            }

            $query->whereBetween('date', [
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            
            $returns = $query->with('supplier', 'returnWarehouse')->orderBy('date', 'desc')->get();

            return view('returns_management.reports.purchase_return_report_data', compact('returns'));
        }
        
        return view('returns_management.reports.purchase_return_report', compact('suppliers'));
    }

    public function defectStockReport(Request $request)
    {
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', 'damage')
            ->get();
        $categories = Category::where('status', 1)->get();
            
        if ($request->has('search')) {
            $query = Inventory::whereHas('warehouse', function($q) {
                $q->where('type', 'damage');
            })->with('product', 'product.unit', 'product.category', 'warehouse')
              ->where('sister_concern_id', auth()->user()->activeConcernId())
              ->where('quantity', '>', 0); // Only show items with stock

            if ($request->warehouse_id != '') {
                $query->where('warehouse_id', $request->warehouse_id);
            }
            if ($request->category_id != '') {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('category_id', $request->category_id);
                });
            }
            
            $stocks = $query->get();

            return view('returns_management.reports.defect_stock_report_data', compact('stocks'));
        }
        
        return view('returns_management.reports.defect_stock_report', compact('warehouses', 'categories'));
    }

    public function returnSummaryReport(Request $request)
    {
        if ($request->has('search')) {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');

            $salesReturns = SalesReturn::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 2)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();
                
            $purchaseReturns = PurchaseReturn::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('status', 2)
                ->whereBetween('date', [$startDate, $endDate])
                ->get();

            $totalSalesReturnVal = $salesReturns->sum('total_return_amount');
            $totalSalesReplacementVal = $salesReturns->sum('total_replacement_amount');
            $totalSalesRefundVal = $salesReturns->sum('net_payable_or_receivable');

            $totalPurchaseReturnVal = $purchaseReturns->sum('total_return_value');

            return view('returns_management.reports.return_summary_report_data', compact(
                'salesReturns', 'purchaseReturns', 'totalSalesReturnVal', 'totalSalesReplacementVal', 'totalSalesRefundVal', 'totalPurchaseReturnVal', 'startDate', 'endDate'
            ));
        }
        
        return view('returns_management.reports.return_summary_report');
    }
}
