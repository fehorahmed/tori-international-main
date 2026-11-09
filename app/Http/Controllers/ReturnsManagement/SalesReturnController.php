<?php

namespace App\Http\Controllers\ReturnsManagement;

use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Settings\Warehouse;
use App\Models\Settings\Product;
use App\Models\ReturnsManagement\SalesReturn;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use Carbon\Carbon;
use App\Services\ReturnsManagement\SalesReturnService;
use App\Enumeration\SaleType;

class SalesReturnController extends Controller
{
    protected $salesReturnService;

    public function __construct(SalesReturnService $salesReturnService)
    {
        $this->salesReturnService = $salesReturnService;
    }

    public function index()
    {
        $sisterConcernId = auth()->user()->sister_concern_id;
        $customers = Supplier::where('origin_sister_concern_id', $sisterConcernId)
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$DEALER, SupplierType::$CORPORATE_CUSTOMER])
            ->get();
            
        return view('returns_management.sales_return.index', compact('customers'));
    }

    public function dataTable(Request $request)
    {
        $sisterConcernId = auth()->user()->sister_concern_id;
        if ($request->status === 'Deleted') {
            $query = SalesReturn::onlyTrashed()->with('customer')
                ->where('sister_concern_id', $sisterConcernId);
        } else if ($request->status === 'Active') {
            $query = SalesReturn::with('customer')
                ->where('sister_concern_id', $sisterConcernId);
        } else {
            $query = SalesReturn::withTrashed()->with('customer')
                ->where('sister_concern_id', $sisterConcernId);
        }

        if ($request->has('customer') && $request->customer != '') {
            $query->where('customer_id', $request->customer);
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
            ->filterColumn('customer', function($query, $keyword) {
                $query->whereHas('customer', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('member_no', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('return_no', function ($row) {
                $link = '<a href="' . route('returns-management.sales-returns.show', $row->id) . '" class="text-info">' . $row->return_no . '</a>';
                if ($row->trashed()) {
                    return '<span style="text-decoration: line-through; opacity: 0.6;">' . $link . '</span>';
                }
                return $link;
            })
            ->addColumn('customer', function ($row) {
                if ($row->customer) {
                    return ($row->customer->member_no ? $row->customer->member_no . ' - ' : '') . $row->customer->name;
                }
                return 'N/A';
            })
            ->addColumn('sale_order', function ($row) {
                if ($row->saleOrder) {
                    $route = route('sales.details', ['sale' => $row->sale_order_id]);
                    if ($row->saleOrder->type == SaleType::$CORPORATE) {
                        $route = route('corporate-sales.show', ['corporate_sale' => $row->sale_order_id]);
                    } elseif ($row->saleOrder->type == SaleType::$DEALER) {
                        $route = route('dealer-sales.show', ['dealer_sale' => $row->sale_order_id]);
                    }
                    return '<a href="' . $route . '" class="text-info">' . $row->saleOrder->order_no . '</a>';
                }
                return 'N/A';
            })
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('d-m-Y');
            })
            ->editColumn('net_payable_or_receivable', function ($row) {
                if ($row->net_payable_or_receivable < 0) {
                    return number_format(abs($row->net_payable_or_receivable), 2) . ' (Payable)';
                } elseif ($row->net_payable_or_receivable > 0) {
                    return number_format($row->net_payable_or_receivable, 2) . ' (Receivable)';
                }
                return '0.00';
            })
            // ->addColumn('status', function ($row) {
            //     return '<span class="badge badge-success">Approved</span>';
            // })
            ->addColumn('action', function ($row) {
                if ($row->trashed()) {
                    $btn = '<a href="'.route('returns-management.sales-returns.show', $row->id).'" class="btn btn-sm btn-info" title="View/Print"><i class="fa fa-print"></i></a>';
                    return '<div class="d-flex align-items-center justify-content-center"><span class="badge badge-danger mr-2">Cancelled</span>' . $btn . '</div>';
                }
                
                $btn = '<a href="'.route('returns-management.sales-returns.show', $row->id).'" class="btn btn-sm btn-info" title="View/Print"><i class="fa fa-print"></i></a>';
                if (auth()->user()->can('sales_return_delete')) {
                    $btn .= ' <a role="button" data-id="' . $row->id . '" class="btn btn-danger btn-sm btn-delete" title="Cancel & Reverse"><i class="fa fa-times"></i> Cancel</a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action', 'return_no', 'sale_order'])
            ->make(true);
    }

    public function create()
    {
        $sisterConcernId = auth()->user()->sister_concern_id;
        $customers = Supplier::where('origin_sister_concern_id', $sisterConcernId)->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$DEALER, SupplierType::$CORPORATE_CUSTOMER])->get();
        $warehouses = Warehouse::whereIn('type', ["main","damage"])->where('status', 1)->where('sister_concern_id', $sisterConcernId)->get();
        $products = Product::where('status', 1)->get();

        return view('returns_management.sales_return.create', compact('customers', 'warehouses', 'products'));
    }

    public function getCustomerInvoices(Request $request)
    {
        $invoices = SaleOrder::where('customer_id', $request->customer_id)
            ->where('status', '>', 0)
            ->orderBy('id', 'desc')
            ->get();
        return response()->json($invoices);
    }

    public function getInvoiceItems(Request $request)
    {
        $saleOrderItems = SaleOrderItem::with('product')
            ->where('sale_order_id', $request->sale_order_id)
            ->get();
        return response()->json($saleOrderItems);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'sale_order_id' => 'required',
            'date' => 'required|date',
            'return_warehouse_id' => 'required',
            'return_items' => 'required|array',
            'return_items.*.product_id' => 'required',
            'return_items.*.quantity' => 'required|numeric|min:0',
            'return_items.*.unit_price' => 'required|numeric|min:0',
            'replacement_items.*.inventory_id' => 'required_with:replacement_items'
        ]);

        try {
            $this->salesReturnService->createAndApprove($request->all());
            return redirect()->route('returns-management.sales-returns.index')->with('success', 'Sales Return Created & Approved Successfully!');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function show($id)
    {
        $salesReturn = SalesReturn::withTrashed()->with(['customer', 'saleOrder', 'items.product', 'items.inventory', 'createUser', 'returnWarehouse'])->findOrFail($id);
        return view('returns_management.sales_return.show', compact('salesReturn'));
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
            $this->salesReturnService->deleteAndReverse($id);
            return response()->json(['success' => true, 'message' => 'Sales Return deleted and all transactions reversed successfully.']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
