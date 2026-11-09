<?php

namespace App\Http\Controllers\ReturnsManagement;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Product;
use App\Models\ReturnsManagement\PurchaseReturn;
use App\Models\ReturnsManagement\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Account\AccountHead;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use App\Enumeration\PaymentPurpose;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Enumeration\InventoryLogType;
use App\Services\ReturnsManagement\PurchaseReturnService;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Purchase\PurchaseOrderItem;

class PurchaseReturnController extends Controller
{
    protected $purchaseReturnService;

    public function __construct(PurchaseReturnService $purchaseReturnService)
    {
        $this->purchaseReturnService = $purchaseReturnService;
    }
    public function index()
    {
        $suppliers = Supplier::where('type', 1)->get();
        return view('returns_management.purchase_return.index', compact('suppliers'));
    }

    public function dataTable(Request $request)
    {
        $sisterConcernId = auth()->user()->sister_concern_id;
        if ($request->status === 'Deleted') {
            $query = PurchaseReturn::onlyTrashed()->with('supplier')
                ->where('sister_concern_id', $sisterConcernId);
        } else if ($request->status === 'Active') {
            $query = PurchaseReturn::with('supplier')
                ->where('sister_concern_id', $sisterConcernId);
        } else {
            $query = PurchaseReturn::withTrashed()->with('supplier')
                ->where('sister_concern_id', $sisterConcernId);
        }
        if ($request->has('supplier') && $request->supplier != '') {
            $query->where('supplier_id', $request->supplier);
        }
        if ($request->has('start_date') && $request->start_date != '') {
            $query->whereDate('date', '>=', Carbon::parse($request->start_date)->format('Y-m-d'));
        }
        if ($request->has('end_date') && $request->end_date != '') {
            $query->whereDate('date', '<=', Carbon::parse($request->end_date)->format('Y-m-d'));
        }

        $query->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->filterColumn('supplier', function($query, $keyword) {
                $query->whereHas('supplier', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('member_no', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('supplier', function ($row) {
                if ($row->supplier) {
                    return ($row->supplier->member_no ? $row->supplier->member_no . ' - ' : '') . $row->supplier->name;
                }
                return 'N/A';
            })
            ->editColumn('return_no', function ($row) {
                $link = '<a href="' . route('returns-management.purchase-returns.show', $row->id) . '" class="text-info">' . $row->return_no . '</a>';
                if ($row->trashed()) {
                    return '<span style="text-decoration: line-through; opacity: 0.6;">' . $link . '</span>';
                }
                return $link;
            })
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('d-m-Y');
            })
            ->addColumn('status', function ($row) {
                if ($row->trashed()) {
                    return '<span class="badge badge-danger">Cancelled</span>';
                }
                if ($row->status == 1) {
                    return '<span class="badge badge-warning">Pending</span>';
                }
                return '<span class="badge badge-success">Sent/Approved</span>';
            })
            ->addColumn('action', function ($row) {
                if ($row->trashed()) {
                    return ' <a href="' . route('returns-management.purchase-returns.show', $row->id) . '" class="btn btn-sm btn-info" title="View/Print"><i class="fa fa-print"></i></a>';
                }
                
                $btn = ' <a href="' . route('returns-management.purchase-returns.show', $row->id) . '" class="btn btn-sm btn-info" title="View/Print"><i class="fa fa-print"></i></a>';
                if (auth()->user()->can('purchase_return_delete')) {
                    $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm btn-delete" title="Cancel & Reverse"><i class="fa fa-times"></i> Cancel</a>';
                }
                return $btn;
            })
            ->rawColumns(['return_no', 'status', 'action'])
            ->make(true);
    }

    public function getSupplierOrders(Request $request)
    {
        $supplierId = $request->supplier_id;
        // Status 'received' indicates the purchase order has been received
        $orders = PurchaseOrder::where('supplier_id', $supplierId)
            ->where('status', 'received')
            ->get();
        return response()->json($orders);
    }

    public function getOrderItems(Request $request)
    {
        $orderId = $request->purchase_order_id;
        $warehouseId = $request->warehouse_id;
        
        $items = PurchaseOrderItem::with([
            'product' => function ($q) {
                $q->select('id', 'name');
            }
        ])
            ->where('purchase_order_id', $orderId)
            ->get();

        foreach ($items as $item) {
            if (empty($item->batch_no)) {
                $item->available_stock = 0;
                continue;
            }

            $query = Inventory::where('product_id', $item->product_id)
                ->where('warehouse_id', $warehouseId)
                ->where('batch_no', $item->batch_no);

            $item->available_stock = $query->sum('quantity');
        }

        return response()->json($items);
    }

    public function create()
    {
        $sisterConcernId = auth()->user()->sister_concern_id;
        $suppliers = Supplier::where('type', 1)->get(); // 1 is typically supplier
        $warehouses = Warehouse::where('status', 1)->where('sister_concern_id', $sisterConcernId)->where('type', 'damage')->get();
        $products = Product::where('status', 1)->get();

        return view('returns_management.purchase_return.create', compact('suppliers', 'warehouses', 'products'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required',
            'purchase_order_id' => 'required',
            'date' => 'required|date',
            'return_warehouse_id' => 'required',
            'return_items' => 'required|array',
            'return_items.*.product_id' => 'required',
            'return_items.*.quantity' => 'nullable|numeric|min:0',
            'return_items.*.unit_price' => 'nullable|numeric|min:0'
        ]);

        try {
            $this->purchaseReturnService->storeAndApprove($request->all(), auth()->id(), auth()->user()->sister_concern_id);
            return redirect()->route('returns-management.purchase-returns.index')->with('success', 'Purchase Return Created and Approved Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $purchaseReturn = PurchaseReturn::withTrashed()->with(['supplier', 'items.product', 'items.inventory', 'createUser', 'warehouse'])->findOrFail($id);
        return view('returns_management.purchase_return.show', compact('purchaseReturn'));
    }

    public function edit($id)
    {
        // Edit logic will go here
    }

    public function update(Request $request, $id)
    {
        // Update logic will go here
    }

    public function destroy($id)
    {
        try {
            $this->purchaseReturnService->deleteAndReverse($id);
            return response()->json(['success' => true, 'message' => 'Purchase Return deleted and reversed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
