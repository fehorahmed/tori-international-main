<?php

namespace App\Http\Controllers\ReturnsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Category;
use Yajra\DataTables\Facades\DataTables;
use App\Enumeration\InventoryLogType;
use Carbon\Carbon;

class DefectStockController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Defect / Damage Stock';
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'damage')->where('status', 1)->get();
        $categories = Category::where('status', 1)->orderBy('name')->get();
        
        return view('returns_management.defect_stock.index', compact('pageTitle', 'warehouses', 'categories'));
    }

    public function dataTable(Request $request)
    {
        $query = Inventory::whereHas('warehouse', function($q) {
            $q->where('type', 'damage');
        })->with('product', 'product.unit', 'product.category', 'warehouse')
          ->where('sister_concern_id', auth()->user()->activeConcernId());

        if (request()->has('warehouse') && request('warehouse') != '') {
            $query->where('warehouse_id', request('warehouse'));
        }
        if (request()->has('category') && request('category') != '') {
            $query->whereHas('product', function($q) {
                $q->where('category_id', request('category'));
            });
        }
        if (request()->has('product') && request('product') != '') {
            $query->where('product_id', request('product'));
        }
        
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(Inventory $inventory) {
                $btn = ' <a href="' . route('returns-management.defect-stock.details', ['inventory' => $inventory->id]) . '" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-info-circle"></i></a>';
                return $btn;
            })
            ->addColumn('warehouse_name', function(Inventory $inventory) {
                return $inventory->warehouse->name ?? '';
            })
            ->addColumn('product_name', function(Inventory $inventory) {
                return $inventory->product->name ?? '';
            })
            ->addColumn('product_code', function(Inventory $inventory) {
                return $inventory->product->code ?? '';
            })
            ->addColumn('product_image', function(Inventory $inventory) {
                $preview_html = '';
                $preview_html .= '<div class="zoom-gallery">';
                $preview_html .= '<a class="image_preview" title="'. ($inventory->product->name ?? '') .'" data-source="'. asset(file_exists($inventory->product->thumbs ?? '') ? $inventory->product->thumbs : 'img/no-image.webp') .'" href="'. asset(file_exists($inventory->product->thumbs ?? '') ? $inventory->product->thumbs : 'img/no-image.webp') .'"><img height="50px" src="'.asset(file_exists($inventory->product->thumbs ?? '') ? $inventory->product->thumbs : 'img/no-image.webp').'"></a>';
                $preview_html .= '</div>';
                return $preview_html;
            })
            ->addColumn('unit_name', function(Inventory $inventory) {
                return $inventory->product->unit->name ?? '';
            })
            ->rawColumns(['action', 'product_image'])
            ->toJson();
    }

    public function details(Request $request)
    {
        $inventory = Inventory::with('product.unit','warehouse')
                ->where('sister_concern_id', auth()->user()->activeConcernId())
                ->findOrFail($request->inventory);
                
        $product = $inventory->product;
        
        $inTypes = InventoryLogType::inTypes();

        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::parse(auth()->user()->business_date)->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::parse(auth()->user()->business_date)->endOfDay();

        $openingIn = InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_id', $inventory->id)
            ->where('date', '<', $startDate)
            ->whereIn('type', $inTypes)
            ->sum('quantity');

        $openingOut = InventoryLog::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_id', $inventory->id)
            ->where('date', '<', $startDate)
            ->whereNotIn('type', $inTypes)
            ->sum('quantity');
            
        $openingBalance = $openingIn - $openingOut;

        $logs = InventoryLog::with(['supplier', 'warehouse', 'salesReturn', 'purchaseReturn'])
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('inventory_id', $inventory->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date', 'asc')
            ->orderBy('created_at', 'asc')
            ->get();
        
        return view('returns_management.defect_stock.details', compact('product', 'inventory', 'logs', 'inTypes', 'openingBalance', 'startDate', 'endDate'));
    }

    public function inventoryLogDataTable()
    {
        $query = InventoryLog::with('supplier', 'warehouse', 'salesReturn', 'purchaseReturn', 'product')
            ->where('sister_concern_id', auth()->user()->activeConcernId());
        
        if (request()->has('inventory_id') && request('inventory_id') != ''){
            $query->where('inventory_id', request('inventory_id'));
        }

        return DataTables::eloquent($query)
            ->addColumn('reference', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->salesReturn) {
                    return '<span class="badge badge-info">SR: ' . $inventoryLog->salesReturn->return_no . '</span>';
                } elseif ($inventoryLog->purchaseReturn) {
                    return '<span class="badge badge-danger">PR: ' . $inventoryLog->purchaseReturn->return_no . '</span>';
                }
                return '-';
            })
            ->addColumn('warehouse_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->warehouse->name ?? '';
            })
            ->addColumn('type', function(InventoryLog $inventoryLog) {
                if ($inventoryLog->type == InventoryLogType::$SALES_RETURN_IN) {
                    return '<span class="badge badge-success">Sales Return In</span>';
                } elseif ($inventoryLog->type == InventoryLogType::$PURCHASE_RETURN_OUT) {
                    return '<span class="badge badge-danger">Purchase Return Out</span>';
                } elseif ($inventoryLog->type == InventoryLogType::$STOCK_TRANSFER_IN) {
                    return '<span class="badge badge-success">Stock Transferred In</span>';
                } elseif ($inventoryLog->type == InventoryLogType::$STOCK_TRANSFER_OUT) {
                    return '<span class="badge badge-danger">Stock Transferred Out</span>';
                }
                return '<span class="badge badge-secondary">Other</span>';
            })
            ->addColumn('stock_in', function(InventoryLog $inventoryLog) {
                if (in_array($inventoryLog->type, InventoryLogType::inTypes())) {
                    return $inventoryLog->quantity ?? '';
                }
                return '';
            })
            ->addColumn('stock_out', function(InventoryLog $inventoryLog) {
                if (in_array($inventoryLog->type, InventoryLogType::outTypes())) {
                    return $inventoryLog->quantity ?? '';
                }
                return '';
            })
            ->addColumn('balance_quantity', function(InventoryLog $inventoryLog) {
                return $inventoryLog->balance_quantity ?? '';
            })
            ->addColumn('product_name', function(InventoryLog $inventoryLog) {
                return $inventoryLog->product->name ?? '';
            })
            ->rawColumns(['reference', 'type'])
            ->toJson();
    }
}
