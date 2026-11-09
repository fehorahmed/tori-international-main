<?php

namespace App\Http\Controllers\Sales;

use App\Enumeration\SaleType;
use App\Enumeration\SupplierType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Division;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SalePayment;
use App\Models\Supplier;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function saleReport(Request $request){
        $orders = [];
        $customers = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())->where('type', SupplierType::$CUSTOMER)->get();
        $query = SaleOrder::with('customer'); 
        
        if ($request->has('search')) {
            $query->where('sister_concern_id',auth()->user()->activeConcernId());

            if ($request->customer != ''){
                $query = $query->where('customer_id', $request->customer);
            }
            $query = $query->whereBetween('date',[
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            $orders = $query->get();

            return view('sales.reports.sale_report_data', compact('orders'));
        }
        $sister_concerns = SisterConcern::where('status',1)->get();
        $divisions = Division::orderBy('name')->get();
        return view('sales.reports.sale', compact('customers','sister_concerns','divisions'));
    }
    public function customerDueReport(Request $request)
    {
        $query = Supplier::with('division', 'district', 'upazila', 'union')
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER]);

        if ($request->customer_type) {
            $query->where('type', $request->customer_type);
        }
        
        if ($request->division_id) {
            $query->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $query->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $query->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $query->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $query->where('village_id', $request->village_id);
        }

        if ($request->has('search')) {
            $customers = $query->orderBy('name')->get();

            $customerIds = $customers->pluck('id')->toArray();

            $accountHeads = AccountHead::whereIn('supplier_id', $customerIds)->get()->keyBy('supplier_id');

            $salesSums = SaleOrder::whereIn('customer_id', $customerIds)
                ->whereNotIn('status', [1, 4]) // exclude pending and cancelled
                ->where('type', '!=', SaleType::$PRE_ORDER) // exclude preorder
                ->groupBy('customer_id')
                ->select('customer_id', DB::raw('SUM(total) as total_sales'))
                ->get()
                ->pluck('total_sales', 'customer_id');

            $paymentsSums = SalePayment::whereIn('customer_id', $customerIds)
                ->groupBy('customer_id')
                ->select('customer_id', DB::raw('SUM(amount) as total_payments'))
                ->get()
                ->pluck('total_payments', 'customer_id');

            foreach ($customers as $customer) {
                $customer->total_sales = $salesSums->get($customer->id) ?? 0;
                $customer->total_paid = $paymentsSums->get($customer->id) ?? 0;
                    
                $accHead = $accountHeads->get($customer->id);
                $customer->real_opening_balance = $accHead ? $accHead->getOpeningBalance() : 0;
                
                // Calculate real current balance to avoid corrupted database values
                $customer->real_current_balance = $customer->real_opening_balance + $customer->total_sales - $customer->total_paid;
            }

            return view('sales.reports.customer_due_report_data', compact('customers'));
        }

        $divisions = Division::orderBy('name')->get();
        return view('sales.reports.customer_due_report', compact('divisions'));
    }

    public function customerReport(Request $request)
    {
        if ($request->has('search')) {
            $query = Supplier::whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

            if ($request->customer_type) {
                $query->where('type', $request->customer_type);
            }
            if ($request->division_id) {
                $query->where('division_id', $request->division_id);
            }
            if ($request->district_id) {
                $query->where('district_id', $request->district_id);
            }
            if ($request->upazila_id) {
                $query->where('upazila_id', $request->upazila_id);
            }
            if ($request->union_id) {
                if (strpos($request->union_id, 'union_') !== false) {
                    $query->where('union_id', str_replace('union_', '', $request->union_id));
                } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                    $query->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
                }
            }
            if ($request->village_id) {
                $query->where('village_id', $request->village_id);
            }

            $customers = $query->orderBy('name')->get();

            return view('sales.reports.customer_report_data', compact('customers'));
        }

        $divisions = Division::orderBy('name')->get();

        return view('sales.reports.customer_report', compact('divisions'));
    } 

    public function customerLedger(Request $request)
    {
        $startDate = $request->start_date ? date('Y-m-d', strtotime($request->start_date)) : null;
        $endDate = $request->end_date ? date('Y-m-d', strtotime($request->end_date)) : null;

        $customersQuery = Supplier::whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
            ->where('status', 1)
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($request->division_id) {
            $customersQuery->where('division_id', $request->division_id);
        }
        if ($request->district_id) {
            $customersQuery->where('district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $customersQuery->where('upazila_id', $request->upazila_id);
        }
        if ($request->union_id) {
            if (strpos($request->union_id, 'union_') !== false) {
                $customersQuery->where('union_id', str_replace('union_', '', $request->union_id));
            } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                $customersQuery->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
            }
        }
        if ($request->village_id) {
            $customersQuery->where('village_id', $request->village_id);
        }

        $customers = $customersQuery->orderBy('name')->get();
        $customerIds = $customers->pluck('id')->toArray();

        $accountHeads = [];
        if ($startDate && $endDate) {
            $previousDay = date('Y-m-d', strtotime('-1 day', strtotime($startDate)));

            $query = AccountHead::where('status', 1)
                ->where('head_type','primary')
                ->whereIn('supplier_id', $customerIds);

            if ($request->customer_id != '') {
                $query->where('supplier_id', $request->customer_id);
            }

            $query->with([
                'openingTransactions' => function ($query) use ($previousDay) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());
                    $query->whereDate('date', '<=', $previousDay)
                        ->select(DB::raw('account_head_id,
                            SUM(CASE WHEN transaction_type IN (1) THEN amount ELSE 0 END) AS previous_debit,
                            SUM(CASE WHEN transaction_type IN (2) THEN amount ELSE 0 END) AS previous_credit'))
                        ->groupBy('account_head_id');
                }, 'transactions' => function ($query) use ($startDate, $endDate) {
                    $query->where('sister_concern_id', auth()->user()->activeConcernId());
                    $query->whereBetween('date', [$startDate, $endDate])
                        ->orderBy('date')
                        ->orderBy('voucher_no_group_sl');
                },
                'supplier'
            ]);

            $accountHeads = $query->orderBy('name')->get();
        }

        $currentMonth = date('m');
        if ($currentMonth < 7) {
            $currentYear = date('Y') - 1;
            $currentDate = date('01-07-' . $currentYear);
        } else {
            $currentDate = date('01-07-Y');
        }

        $divisions = Division::orderBy('name')->get();

        $selectedCustomerInfo = null;
        if ($request->customer_id != '') {
            $selectedCustomerInfo = Supplier::find($request->customer_id);
        }

        if ($request->has('search') || $request->has('customer_id')) {
            return view('sales.reports.customer_ledger_report', compact('accountHeads', 'currentDate', 'customers', 'divisions', 'selectedCustomerInfo'));
        }

        return view('sales.reports.customer_ledger', compact('accountHeads', 'currentDate', 'customers', 'divisions'));
    }

    public function invoiceProfitLossReport(Request $request)
    {
        $orders = [];
        $query = SaleOrder::with(['customer', 'saleOrderItems']); 
        
        if ($request->has('search')) {
            $query->where('sister_concern_id',auth()->user()->activeConcernId());
            $query->whereNotIn('status', [1, 4]); // Exclude pending and cancelled
            
            $query = $query->whereBetween('date',[
                Carbon::parse($request->start_date)->format('Y-m-d'),
                Carbon::parse($request->end_date)->format('Y-m-d')
            ]);
            
            $orders = $query->get()->map(function($order) {
                $totalSalesValue = $order->total; // Final paid/payable amount (sub_total - discount)
                
                $totalCost = $order->saleOrderItems->sum(function($item) {
                     return $item->unit_price * $item->quantity; // unit_price holds the purchase cost
                });
                
                $order->total_cost = $totalCost;
                $order->profit = $totalSalesValue - $totalCost;
                return $order;
            });

            return view('sales.reports.invoice_profit_loss_data', compact('orders'));
        }
        
        return view('sales.reports.invoice_profit_loss');
    }

    public function productProfitLossReport(Request $request)
    {
        $productsData = [];
        
        if ($request->has('search')) {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            $concernId = auth()->user()->activeConcernId();
            
            $productsData = DB::table('sale_order_items')
                ->join('products', 'sale_order_items.product_id', '=', 'products.id')
                ->join('sale_orders', 'sale_order_items.sale_order_id', '=', 'sale_orders.id')
                ->where('sale_order_items.sister_concern_id', $concernId)
                ->whereNotIn('sale_orders.status', [1, 4])
                ->whereBetween('sale_order_items.date', [$startDate, $endDate])
                ->whereNull('sale_order_items.deleted_at')
                ->whereNull('sale_orders.deleted_at')
                ->selectRaw('
                    products.name as product_name,
                    SUM(sale_order_items.quantity) as sold_qty,
                    SUM(sale_order_items.selling_price * sale_order_items.quantity) as total_revenue,
                    SUM(sale_order_items.unit_price * sale_order_items.quantity) as total_cost,
                    SUM((sale_order_items.selling_price * sale_order_items.quantity) - (sale_order_items.unit_price * sale_order_items.quantity)) as profit
                ')
                ->groupBy('products.id', 'products.name')
                ->orderByDesc('profit')
                ->get();

            return view('sales.reports.product_profit_loss_data', compact('productsData'));
        }
        
        return view('sales.reports.product_profit_loss');
    }
}
