<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Settings\Product;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Category;
use App\Models\Inventory\Inventory;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function stockReport(Request $request) {
        $products = Product::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->get();

        if ($request->has('search')) {
            $selectedWarehouseId = $request->warehouse;
            if (empty($selectedWarehouseId) && $warehouses->isNotEmpty()) {
                $selectedWarehouseId = $warehouses->first()->id;
            }

            $query = Inventory::where('sister_concern_id', auth()->user()->activeConcernId())->with('product');

            if (!empty($selectedWarehouseId)) {
                $query->where('warehouse_id', $selectedWarehouseId);
            }
            if ($request->product != '') {
                $query->where('product_id', $request->product);
            }
            if ($request->category != '') {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }

            $inventories = $query->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('AVG(unit_price) as avg_unit_price')
                )
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0')
                ->get();
                
            $selectedWh = $warehouses->firstWhere('id', $selectedWarehouseId);

            return view('inventory.reports.stock_report_data', compact('inventories', 'selectedWh'));
        }

        return view('inventory.reports.stock_report', compact('products', 'warehouses', 'categories'));
    }

    public function batchStockReport(Request $request) {
        $products = Product::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->get();

        if ($request->has('search')) {
            $selectedWarehouseId = $request->warehouse;
            if (empty($selectedWarehouseId) && $warehouses->isNotEmpty()) {
                $selectedWarehouseId = $warehouses->first()->id;
            }

            $query = Inventory::where('sister_concern_id', auth()->user()->activeConcernId())->with('product', 'warehouse');

            if (!empty($selectedWarehouseId)) {
                $query->where('warehouse_id', $selectedWarehouseId);
            }
            if ($request->product != '') {
                $query->where('product_id', $request->product);
            }
            if ($request->category != '') {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }
            if ($request->batch_no != '') {
                $query->where('batch_no', 'like', '%' . $request->batch_no . '%');
            }

            $query->where('quantity', '>', 0);

            $inventories = $query->orderBy('batch_no', 'asc')->get();
            $selectedWh = $warehouses->firstWhere('id', $selectedWarehouseId);

            return view('inventory.reports.batch_stock_report_data', compact('inventories', 'selectedWh'));
        }

        return view('inventory.reports.batch_stock_report', compact('products', 'warehouses', 'categories'));
    }

    public function stockValuationReport(Request $request) {
        $products = Product::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->get();

        if ($request->has('search')) {
            $selectedWarehouseId = $request->warehouse;
            if (empty($selectedWarehouseId) && $warehouses->isNotEmpty()) {
                $selectedWarehouseId = $warehouses->first()->id;
            }

            $query = Inventory::where('sister_concern_id', auth()->user()->activeConcernId())->with('product.productCategory');

            if (!empty($selectedWarehouseId)) {
                $query->where('warehouse_id', $selectedWarehouseId);
            }
            if ($request->product != '') {
                $query->where('product_id', $request->product);
            }
            if ($request->category != '') {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }

            $inventories = $query->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('SUM(quantity * unit_price) as total_valuation')
                )
                ->groupBy('product_id')
                ->havingRaw('SUM(quantity) > 0')
                ->get()
                ->groupBy(function($item) {
                    return $item->product && $item->product->productCategory ? $item->product->productCategory->name : 'Uncategorized';
                });
                
            $selectedWh = $warehouses->firstWhere('id', $selectedWarehouseId);

            return view('inventory.reports.stock_valuation_report_data', compact('inventories', 'selectedWh'));
        }

        return view('inventory.reports.stock_valuation_report', compact('products', 'warehouses', 'categories'));
    }

    public function batchStockValuationReport(Request $request) {
        $products = Product::where('status', 1)->get();
        $categories = Category::where('status', 1)->get();
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->get();

        if ($request->has('search')) {
            $selectedWarehouseId = $request->warehouse;
            if (empty($selectedWarehouseId) && $warehouses->isNotEmpty()) {
                $selectedWarehouseId = $warehouses->first()->id;
            }

            $query = Inventory::where('sister_concern_id', auth()->user()->activeConcernId())->with('product.productCategory', 'warehouse');

            if (!empty($selectedWarehouseId)) {
                $query->where('warehouse_id', $selectedWarehouseId);
            }
            if ($request->product != '') {
                $query->where('product_id', $request->product);
            }
            if ($request->category != '') {
                $query->whereHas('product', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            }
            if ($request->batch_no != '') {
                $query->where('batch_no', 'like', '%' . $request->batch_no . '%');
            }

            $query->where('quantity', '>', 0);

            $inventories = $query->orderBy('batch_no', 'asc')->get()
                ->groupBy(function($item) {
                    return $item->product && $item->product->productCategory ? $item->product->productCategory->name : 'Uncategorized';
                });
            $selectedWh = $warehouses->firstWhere('id', $selectedWarehouseId);

            return view('inventory.reports.batch_stock_valuation_report_data', compact('inventories', 'selectedWh'));
        }

        return view('inventory.reports.batch_stock_valuation_report', compact('products', 'warehouses', 'categories'));
    }
    public function productLedger(Request $request) {
        $categories = Category::where('status', 1)->get();
        $products = [];
        
        if ($request->has('search')) {
            $request->validate([
                'product' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
            ]);

            $product = Product::find($request->product);
            $startDate = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $endDate = \Carbon\Carbon::parse($request->end_date)->endOfDay();
            
            // IN Types (add to balance)
            $inTypes = [
                \App\Enumeration\InventoryLogType::$PURCHASE_IN,
                \App\Enumeration\InventoryLogType::$FINISHED_GOODS_MANUFACTURED,
                \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_IN,
                \App\Enumeration\InventoryLogType::$SALES_RETURN_IN,
                \App\Enumeration\InventoryLogType::$STOCK_CONVERSION_IN,
                \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_RETURN_IN,
                \App\Enumeration\InventoryLogType::$OPENING_STOCK,
            ];

            // Calculate Opening Balance (Before start_date)
            $openingIn = \App\Models\Inventory\InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('product_id', $product->id)
                ->where('date', '<', $startDate)
                ->whereIn('type', $inTypes)
                ->sum('quantity');

            $openingOut = \App\Models\Inventory\InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('product_id', $product->id)
                ->where('date', '<', $startDate)
                ->whereNotIn('type', $inTypes)
                ->sum('quantity');

            $openingBalance = $openingIn - $openingOut;

            // Get Logs for the period
            $logs = \App\Models\Inventory\InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('product_id', $product->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->with(['purchaseOrder', 'saleOrder', 'supplier', 'warehouse', 'stockTransfer'])
                ->orderBy('date', 'asc')
                ->orderBy('created_at', 'asc')
                ->get();

            return view('inventory.reports.product_ledger_data', compact('product', 'logs', 'openingBalance', 'startDate', 'endDate', 'inTypes'));
        }

        return view('inventory.reports.product_ledger', compact('categories', 'products'));
    }
}
