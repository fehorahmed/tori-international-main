<?php

namespace App\Http\Controllers\Sales\Service;

use App\Enumeration\InventoryLogType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\SaleType;
use App\Enumeration\RoleType;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Hrm\Employee;
use App\Models\Account\AccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use App\Models\Sales\Service\Service;
use App\Models\Sales\Service\ServiceItem;
use App\Models\Sales\Service\ServiceUtilizedItem;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index()
    {
        return view('sales.service.request.index');
    }

    public function dataTable()
    {
        $query = Service::where('sister_concern_id', auth()->user()->activeConcernId())
            ->with('customer', 'technician', 'saleOrder');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (Service $service) {
                $btn = '';
                $btn .= ' <a href="' . route('service.request.details', ['service' => $service->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-info-circle"></i></a>';
                return $btn;
            })
            ->addColumn('customer_name', function (Service $service) {
                return $service->customer->name ?? '';
            })
            ->addColumn('technician_name', function (Service $service) {
                return $service->technician->name ?? '';
            })
            ->addColumn('order_no', function (Service $service) {
                return $service->saleOrder->order_no ?? '';
            })
            ->addColumn('status_text', function (Service $service) {
                $status = [
                    1 => '<span class="badge badge-warning">Pending</span>',
                    2 => '<span class="badge badge-info">In Progress</span>',
                    3 => '<span class="badge badge-primary">Waiting for Approval</span>',
                    4 => '<span class="badge badge-success">Completed</span>',
                    5 => '<span class="badge badge-secondary">Delivered</span>',
                ];
                return $status[$service->status] ?? '';
            })
            ->rawColumns(['action', 'status_text'])
            ->toJson();
    }

    public function create(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('service_request_create'), 403, 'Unauthorized');

        $preselectedSaleType = $request->query('sale_type');
        $preselectedSaleOrderId = $request->query('sale_order_id');
        
        // Manual Access Prevention Guard
        if (!$preselectedSaleOrderId || !$preselectedSaleType) {
            return redirect()->route('sales.index')->with('error', 'Service requests cannot be created manually. Please initiate the request by clicking the "Service" button from the Sales List or EMI Sales List.');
        }

        $technicians = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->technicians()
            ->where('status', 1)
            ->get();
        
        $preselectedOrder = SaleOrder::find($preselectedSaleOrderId);
        
        // Ensure the order exists
        if (!$preselectedOrder) {
            return redirect()->route('sales.index')->with('error', 'The selected Sales Order was not found.');
        }

        // Ensure the order has active warranty items
        if (!$preselectedOrder->hasActiveWarrantyItems()) {
            return redirect()->route('sales.index')->with('error', 'The selected Sales Order does not have any products with active warranty.');
        }
        
        return view('sales.service.request.create', compact('technicians', 'preselectedSaleType', 'preselectedSaleOrderId', 'preselectedOrder'));
    }

    public function getOrders(Request $request)
    {
        $orders = SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', $request->sale_type)
            ->where(function($query) use ($request) {
                $query->where('order_no', 'LIKE', "%{$request->term}%");
            })
            ->limit(20)
            ->get(['id', 'order_no as text']);

        return response()->json($orders);
    }

    public function getOrderProducts(Request $request)
    {
        $order = SaleOrder::with('saleOrderItems.product')->find($request->order_id);
        
        if (!$order) {
            return response()->json(['success' => false, 'message' => 'Order not found']);
        }

        $items = [];
        foreach ($order->saleOrderItems as $item) {
            // Check if product has active warranty
            if ($item->hasActiveWarranty()) {
                $items[] = [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->name ?? '',
                    'product_code' => $item->product->code ?? '',
                    'warranty_expiry' => $item->warranty_expiry_date ? $item->warranty_expiry_date->format('Y-m-d') : 'N/A',
                ];
            }
        }

        return response()->json(['success' => true, 'items' => $items, 'customer' => $order->customer]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('service_request_create'), 403, 'Unauthorized');

        $request->validate([
            'sale_type' => 'required',
            'sale_order' => 'required',
            'technician' => 'required',
            'date' => 'required|date',
            'product_id' => 'required|array|min:1',
            'cause' => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $order = SaleOrder::find($request->sale_order);

            $service = new Service();
            $service->sister_concern_id = auth()->user()->activeConcernId();
            $service->customer_id = $order->customer_id;
            $service->sale_order_id = $order->id;
            $service->sale_type = $request->sale_type;
            $service->technician_id = $request->technician;
            $service->service_no = generateOrderNo('services', 'service_no', 'SRV-');
            $service->date = Carbon::parse($request->date);
            $service->status = 1; // Pending
            $service->user_id = auth()->id();
            $service->notes = $request->notes;
            $service->save();

            foreach ($request->product_id as $key => $productId) {
                $saleOrderItemId = $request->sale_order_item_id[$key];
                
                $serviceItem = new ServiceItem();
                $serviceItem->service_id = $service->id;
                $serviceItem->product_id = $productId;
                $serviceItem->sale_order_item_id = $saleOrderItemId;
                $serviceItem->cause = $request->cause[$key];
                $serviceItem->warranty_status = 1; // Verified in getOrderProducts
                $serviceItem->save();
            }

            DB::commit();
            return redirect()->route('service.request.index')->with('success', 'Service request created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function details(Service $service)
    {
        $service->load('customer', 'technician', 'saleOrder', 'serviceItems.product', 'serviceUtilizedItems.product');
        return view('sales.service.request.details', compact('service'));
    }

    public function approve(Service $service, Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('service_request_approve'), 403, 'Unauthorized');

        if ($service->status != 3) {
            return redirect()->back()->with('error', 'Only services waiting for approval can be approved.');
        }

        DB::beginTransaction();
        try {
            $totalUtilizedCost = $service->serviceUtilizedItems->sum(function($item) {
                return $item->quantity * $item->unit_price;
            });

            // 1. Inventory Decrement & Logs
            foreach ($service->serviceUtilizedItems as $item) {
                $inventory = Inventory::find($item->inventory_id);
                if (!$inventory || $inventory->quantity < $item->quantity) {
                    throw new \Exception("Insufficient stock for part: " . ($item->product->name ?? 'Unknown'));
                }

                $inventory->decrement('quantity', $item->quantity);

                // Inventory Log
                $inventoryLog = new InventoryLog();
                $inventoryLog->sister_concern_id = $service->sister_concern_id;
                $inventoryLog->type = InventoryLogType::$PRODUCT_UTILIZED; // Using existing type for simplicity
                $inventoryLog->warehouse_id = $inventory->warehouse_id;
                $inventoryLog->inventory_id = $inventory->id;
                $inventoryLog->product_id = $item->product_id;
                $inventoryLog->quantity = $item->quantity;
                $inventoryLog->unit_price = $item->unit_price;
                $inventoryLog->date = now();
                $inventoryLog->user_id = auth()->id();
                $inventoryLog->notes = 'Service Utilized: ' . $service->service_no;
                $inventoryLog->save();
            }

            // 2. Accounting Entries
            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $customerAccountHeadId = AccountHead::where('head_type', 'primary')->where('supplier_id', $service->customer_id)->first()->id ?? null;

            $voucher = new Voucher();
            $voucher->sister_concern_id = $service->sister_concern_id;
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->amount = $service->service_charge + $totalUtilizedCost;
            $voucher->date = now()->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->service_id = $service->id;
            $voucher->user_id = auth()->id();
            $voucher->notes = 'Service Revenue & Parts Consumption - ' . $service->service_no;
            $voucher->save();

            // A. Service Revenue (Credit)
            $transaction = new Transaction();
            $transaction->sister_concern_id = $service->sister_concern_id;
            $transaction->voucher_id = $voucher->id;
            $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::SERVICE_REVENUE);
            $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $service->service_charge;
            $transaction->date = now()->format('Y-m-d');
            $transaction->service_id = $service->id;
            $transaction->user_id = auth()->id();
            $transaction->save();

            // B. Payment/Cash (Debit)
            if ($service->paid_amount > 0 && $service->payment_account_head_id) {
                $transaction = new Transaction();
                $transaction->sister_concern_id = $service->sister_concern_id;
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = $service->payment_account_head_id;
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $service->paid_amount;
                $transaction->date = now()->format('Y-m-d');
                $transaction->service_id = $service->id;
                $transaction->user_id = auth()->id();
                $transaction->save();
            }

            // B2. Accounts Receivable (Debit) - Remainder if any
            $remainder = $service->service_charge - $service->paid_amount;
            if ($remainder > 0 && $customerAccountHeadId) {
                $transaction = new Transaction();
                $transaction->sister_concern_id = $service->sister_concern_id;
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = $customerAccountHeadId;
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $remainder;
                $transaction->date = now()->format('Y-m-d');
                $transaction->service_id = $service->id;
                $transaction->user_id = auth()->id();
                $transaction->save();
            }

            // C. Cost of Goods Sold (Debit) - For Parts
            if ($totalUtilizedCost > 0) {
                $transaction = new Transaction();
                $transaction->sister_concern_id = $service->sister_concern_id;
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD);
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$DEBIT;
                $transaction->amount = $totalUtilizedCost;
                $transaction->date = now()->format('Y-m-d');
                $transaction->service_id = $service->id;
                $transaction->user_id = auth()->id();
                $transaction->save();

                // D. Inventory (Credit) - For Parts
                $transaction = new Transaction();
                $transaction->sister_concern_id = $service->sister_concern_id;
                $transaction->voucher_id = $voucher->id;
                $transaction->account_head_id = reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                $transaction->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $transaction->transaction_type = TransactionType::$CREDIT;
                $transaction->amount = $totalUtilizedCost;
                $transaction->date = now()->format('Y-m-d');
                $transaction->service_id = $service->id;
                $transaction->user_id = auth()->id();
                $transaction->save();
            }

            // 3. Finalize Service
            $service->status = 4; // Completed
            $service->approved_by = auth()->id();
            $service->approved_at = now();
            $service->save();

            DB::commit();
            return redirect()->route('service.request.details', ['service' => $service->id])->with('success', 'Service approved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
