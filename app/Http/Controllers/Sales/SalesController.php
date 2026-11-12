<?php

namespace App\Http\Controllers\Sales;

use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\RoleType;
use App\Enumeration\SaleType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\SupplierType;
use App\Enumeration\PaymentPurpose;
use App\Jobs\CalculateIncentiveJob;
use App\Models\Account\AccountGroup;
use App\Rules\BusinessDate;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Hrm\Employee;
use App\Models\IncentiveManagement\IncentiveLedger;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use App\Models\Sales\SalePayment;
use App\Enumeration\SalePaymentType;
use App\Models\SaleReturn;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use SakibRahaman\DecimalToWords\DecimalToWords;
use Yajra\DataTables\Facades\DataTables;

class SalesController extends Controller
{
    public function index()
    {
        if (!auth()->user()->hasPermissionTo('sales')) {
            abort(403, 'Unauthorized');
        }

        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();
        $customers = Supplier::where('status', 1)
            ->where('type', SupplierType::$CUSTOMER)
            ->get();

        $srs = Employee::where('status', 1)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->srs()
            ->get();

        return view('pos.index', compact(
            'paymentModes',
            'customers',
            'srs'
        ));
    }

    public function details(SaleOrder $sale)
    {
        if (!auth()->user()->hasPermissionTo('sales')) {
            abort(403, 'Unauthorized');
        }

        $sale->load('saleOrderItems');
        return view('pos.details', compact('sale'));
    }
    public function smDetails(SaleOrder $sale)
    {
        if (!auth()->user()->hasPermissionTo('sales')) {
            abort(403, 'Unauthorized');
        }

        $sale->load('saleOrderItems');
        $sale->amount_in_word = DecimalToWords::convert(
            $sale->total,
            'Taka',
            'Poisa'
        );
        return view('pos.sm_details', compact('sale'));
    }

    public function dataTable()
    {
        $query = SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SaleType::$GENERAL)
            ->with('customer', 'sr', 'saleOrderItems.product');

        if (auth()->user()->sr_id) {
            $query->where('sr_id', auth()->user()->sr_id);
        }

        if (request()->has('start_date') && request('end_date') != '') {
            $query->whereBetween('date', [Carbon::parse(request('start_date'))->format('Y-m-d'), Carbon::parse(request('end_date'))->format('Y-m-d')]);
        }
        if (request()->has('customer') && request('customer') != '') {
            $query->where('customer_id', request('customer'));
        }
        if (request()->has('sr') && request('sr') != '') {
            $query->where('sr_id', request('sr'));
        }
        return DataTables::eloquent($query)
            ->addColumn('action', function (SaleOrder $sales_order) {
                $btn = '';
                // if (auth()->user()->hasPermissionTo('sales_payment_receive')) {
                //     if ($sales_order->due > 0)
                //         $btn .= '<a role="button" data-id="' . $sales_order->id . '" data-advance="' . ($sales_order->customer->outstanding_balance ?? 0) . '" data-total="' . $sales_order->total . '" data-due="' . $sales_order->due . '" data-order_no="' . $sales_order->order_no . '" class="btn btn-primary bg-gradient-primary btn-sm customer-pay">Paid</a>';
                // }
                // if (auth()->user()->hasPermissionTo('sales')) {
                //     $btn .= ' <a href="' . route('sales.sm_details', ['sale' => $sales_order->id]) . '" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i></a>';
                // }
                if (auth()->user()->hasPermissionTo('sales_edit') && $sales_order->is_return == 0 && $sales_order->type != SaleType::$PRE_ORDER) {
                    // if($sales_order->is_pos == 1){
                    //     $btn .= ' <a href="' . route('pos', ['saleOrder' => $sales_order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-edit"></i></a>';
                    // }else{
                    //     $btn .= ' <a href="' . route('sales.edit', ['sale' => $sales_order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-edit"></i></a>';
                    // }
                }
                if (auth()->user()->hasPermissionTo('sales')) {
                    $btn .= ' <a href="' . route('sales.details', ['sale' => $sales_order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-info-circle"></i></a>';
                }
                if ($sales_order->hasActiveWarrantyItems()) {
                    $btn .= ' <a href="' . route('service.request.create', ['sale_type' => \App\Enumeration\SaleType::$GENERAL, 'sale_order_id' => $sales_order->id]) . '" class="btn btn-warning bg-gradient-warning btn-sm" title="Service Request"><i class="fa fa-cogs text-white"></i> Service</a>';
                }
                if (auth()->user()->can('sales_delete')) {
                    if ($sales_order->salesReturns()->exists()) {
                        $btn .= ' <a role="button" class="btn btn-secondary btn-sm" title="Cannot delete: Return orders exist"><i class="fa fa-lock"></i></a>';
                    } else {
                        $btn .= ' <a role="button" data-id="' . $sales_order->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                    }
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('customer_name', function (SaleOrder $sales_order) {
                return $sales_order->customer->name ?? '';
            })
            ->addColumn('sr_name', function (SaleOrder $sales_order) {
                return $sales_order->sr->name ?? '';
            })
            ->addColumn('order_no', function (SaleOrder $sales_order) {
                return '<a href="' . route('sales.details', ['sale' => $sales_order->id]) . '">' . $sales_order->order_no . '</a>';
            })
            ->rawColumns(['action', 'order_no'])
            ->toJson();
    }



    public function pos(SaleOrder $saleOrder)
    {
        if (!auth()->user()->hasPermissionTo('pos')) {
            abort(403, 'Unauthorized');
        }
        $saleOrder->load('saleOrderItems');
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();

        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();

        return view('pos.pos', compact(
            'paymentModes',
            'warehouses',
            'saleOrder'
        ));
    }
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('sales_create')) {
            abort(403, 'Unauthorized');
        }
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->srs()
            ->get();

        return view('sales.create', compact('warehouses', 'products', 'paymentModes', 'srs'));
    }
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('sales_create'))
            abort(403);

        $rules = [
            'memo_no' => 'required',
            'notes' => 'nullable|max:255',
            'warehouse' => 'required',
            'customer' => 'required',
            'date' => ['required', 'date', new BusinessDate()],
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'sr' => 'required|exists:suppliers,id',
        ];
        if ($request->is_pos != 1) {
            $rules['discount_type'] = 'required|in:1,2';
            $rules['discount_percent'] = 'nullable|numeric';
        }
        if (($request->paid_amount ?? 0) > 0)
            $rules['payment_method'] = 'required';
        $request->validate($rules);

        $customer = Supplier::find($request->customer);
        if ($customer && $customer->customer_current_balance > 0) {
            return redirect()->back()->with('error', 'This client has previous dues. You cannot generate a new order until it is paid.')->withInput();
        }

        if ($customer && $customer->hasPendingPreOrder()) {
            return redirect()->back()->with('error', 'This client has an unresolved Pre-Order. Please deliver or cancel it first.')->withInput();
        }

        // Normalize discount after validation passes so that old input is preserved on validation error
        if ($request->is_pos == 1) {
            $request['discount_percent'] = $request->discount_amount > 0 ? ($request->discount_amount * 100) / $request->sub_total : 0;
            $request['discount_type'] = 1; // POS discount is Flat amount
        } else {
            if ($request->discount_type == 1) {
                // Flat BDT
                $request['discount_amount'] = $request->discount_percent ?? 0;
                $request['discount_percent'] = 0;
            } else {
                // Percentage (%)
                $request['discount_amount'] = 0;
                if (($request->discount_percent ?? 0) > 0) {
                    $request['discount_amount'] = ($request->sub_total / 100) * $request->discount_percent;
                }
            }
        }

        DB::beginTransaction();
        try {
            $sisterConcern = auth()->user()->sisterConcern;
            $areaManagerId = $sisterConcern->area_manager_id ?? null;
            $branchManagerId = $sisterConcern->branch_manager_id ?? null;

            $saleOrder = new SaleOrder();
            $saleOrder->sister_concern_id = auth()->user()->activeConcernId();
            $saleOrder->area_manager_id = $areaManagerId;
            $saleOrder->branch_manager_id = $branchManagerId;
            $saleOrder->warehouse_id = $request->warehouse;
            $saleOrder->order_no = generateOrderNo('sale_orders', 'order_no', 'SO-');
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type ?? 2;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->vat_percent = $request->vat_percent ?? 0;
            $saleOrder->vat_amount = $request->vat_amount ?? 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->advance_adjust = $request->advance_amount ?? 0;
            $saleOrder->paid = ($request->paid_amount ?? 0) + ($request->advance_amount ?? 0);
            $saleOrder->due = $request->due_amount ?? 0;
            $saleOrder->notes = $request->notes;
            $saleOrder->memo_no = $request->memo_no;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->is_pos = $request->is_pos;
            $saleOrder->type = SaleType::$GENERAL;
            $saleOrder->user_id = auth()->id();
            $saleOrder->save();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->inventory_id as $reqInventoryId) {
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) {
                    throw new \Exception("Insufficient stock for " . Product::find($request->product_id[$counter])->name);
                }

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $request->product_id[$counter];
                $saleItem->product_code = $request->product_code[$counter];
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $inventory->decrement('quantity', $request->product_quantity[$counter]);
                $this->inventoryLog($saleOrder, $inventory, $request->product_quantity[$counter], $request->product_unit_price[$counter], $request->product_code[$counter]);
                $counter++;
            }

            $this->createAccountingEntries($saleOrder, $costingTotal, $request);

            CalculateIncentiveJob::dispatchSync($saleOrder);
            DB::commit();

            if ($request->is_pos == 1) {
                return response()->json(['status' => true, 'message' => 'Sales order created.', 'redirect_url' => route('sales.sm_details', ['sale' => $saleOrder->id])]);
            }
            return redirect()->route('sales.details', ['sale' => $saleOrder->id])->with('success', 'Sales order created.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->is_pos == 1)
                return response()->json(['status' => false, 'message' => $e->getMessage()]);
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function edit(SaleOrder $sale)
    {
        $sale->load('warehouse');
        if (!auth()->user()->hasPermissionTo('sales_edit')) {
            abort(403, 'Unauthorized');
        } else {
            if (auth()->user()->activeConcernId() != $sale->sister_concern_id) {
                abort(403, 'Unauthorized');
            }
        }
        if ($sale->is_return == 1) {
            abort(403, 'Unauthorized');
        }
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })
            ->where('payment_mode', '>', 0)
            ->get();
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('status', 1)
            ->srs()
            ->get();

        $selectedCustomer = null;
        if (old('customer')) {
            $selectedCustomer = Supplier::find(old('customer'));
        }

        return view('sales.edit', compact('warehouses', 'products', 'paymentModes', 'sale', 'srs', 'selectedCustomer'));
    }
    public function update(SaleOrder $sale, Request $request)
    {
        abort_if($sale->packageCheckout !== null, 403, 'Use the package order workflow.');
        if (!auth()->user()->hasPermissionTo('sales_edit'))
            abort(403);
        if ($sale->sister_concern_id != auth()->user()->activeConcernId())
            abort(403);

        $rules = [
            'memo_no' => 'required',
            'notes' => 'nullable|max:255',
            'warehouse' => 'required',
            'customer' => 'required',
            'date' => ['required', 'date', new BusinessDate()],
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'sr' => 'required',
        ];
        if ($request->is_pos != 1) {
            $rules['discount_type'] = 'required|in:1,2';
            $rules['discount_percent'] = 'nullable|numeric';
        }
        if (($request->paid_amount ?? 0) > 0)
            $rules['payment_method'] = 'required';
        $request->validate($rules);

        // Normalize discount after validation passes so that old input is preserved on validation error
        if ($request->is_pos == 1) {
            $request['discount_percent'] = $request->discount_amount > 0 ? ($request->discount_amount * 100) / $request->sub_total : 0;
            $request['discount_type'] = 1; // POS discount is Flat amount
        } else {
            if ($request->discount_type == 1) {
                // Flat BDT
                $request['discount_amount'] = $request->discount_percent ?? 0;
                $request['discount_percent'] = 0;
            } else {
                // Percentage (%)
                $request['discount_amount'] = 0;
                if (($request->discount_percent ?? 0) > 0) {
                    $request['discount_amount'] = ($request->sub_total / 100) * $request->discount_percent;
                }
            }
        }

        DB::beginTransaction();
        try {
            $saleOrder = $sale;
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type ?? 2;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->vat_percent = $request->vat_percent ?? 0;
            $saleOrder->vat_amount = $request->vat_amount ?? 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->advance_adjust = $request->advance_amount ?? 0;
            $saleOrder->paid = ($request->paid_amount ?? 0) + ($request->advance_amount ?? 0);
            $saleOrder->due = $request->due_amount ?? 0;
            $saleOrder->notes = $request->notes;
            $saleOrder->memo_no = $request->memo_no;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->save();

            // Stock Reset
            foreach ($saleOrder->saleOrderItems as $item) {
                Inventory::find($item->inventory_id)->increment('quantity', $item->quantity);
                InventoryLog::where('sale_order_id', $saleOrder->id)->where('inventory_id', $item->inventory_id)->delete();
            }
            SaleOrderItem::where('sale_order_id', $saleOrder->id)->delete();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->inventory_id as $reqInventoryId) {
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) {
                    throw new \Exception("Stock error.");
                }

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $request->product_id[$counter];
                $saleItem->product_code = $request->product_code[$counter];
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter];
                $saleItem->save();

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $inventory->decrement('quantity', $request->product_quantity[$counter]);
                $this->inventoryLog($saleOrder, $inventory, $request->product_quantity[$counter], $request->product_unit_price[$counter], $request->product_code[$counter]);
                $counter++;
            }

            Voucher::where('sale_order_id', $saleOrder->id)->delete();
            Transaction::where('sale_order_id', $saleOrder->id)->delete();
            Transaction::where('sale_order_id', $saleOrder->id)->delete();

            $this->createAccountingEntries($saleOrder, $costingTotal, $request);

            // Re-calculate incentive for updated sale
            $oldLedgers = IncentiveLedger::where('reference_type', SaleOrder::class)
                ->where('reference_id', $saleOrder->id)
                ->get();
            foreach ($oldLedgers as $ledger) {
                if ($ledger->payment_status === 'paid') {
                    throw new \Exception("এই সেলসটি আপডেট করা সম্ভব নয়! কারণ এই সেলসের বিপরীতে জেনারেট হওয়া ইনসেনটিভের টাকা ইতিমধ্যে এমপ্লয়িকে পেমেন্ট করা হয়ে গেছে।");
                }
                $ledger->delete();
            }

            CalculateIncentiveJob::dispatchSync($saleOrder);
            DB::commit();

            return redirect()->route('sales.details', ['sale' => $saleOrder->id])->with('success', 'Sales order updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function advancePayment(Request $request)
    {

        // Validate the request data
        $rules = [
            'customer_name' => 'required',
            'payment_mode' => 'required',
            'payment' => 'required|numeric|min:.01|',
            'due' => 'required|numeric|min:0',
            'date' => ['required', 'date', new BusinessDate()],
            'notes' => 'nullable|string|max:255',
        ];
        $paymentMode = 0;
        $paymentModeType = 0;
        if ($request->payment_mode != '') {
            [$paymentMode, $paymentModeType] = explode('|', $request->payment_mode);
        }
        if ($paymentModeType == 1) {
            //bank
            $rules['cheque_no'] = 'nullable|max:255';
        }
        $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {
            if (!auth()->user()->hasPermissionTo('advance_from_customer')) {
                abort(403, 'Unauthorized');
            }


            $voucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));


            $customer = Supplier::find($request->customer_id);

            $customer->increment('advance_payment', $request->payment);
            $customer->increment('outstanding_balance', $request->payment);

            $payeeId = AccountHead::where('head_type', 'primary')->where('supplier_id', $customer->id)->first()->id ?? null;
            $advanceAccountHead = AccountHead::where('advance_supplier_id', $customer->id)->first();

            $voucher = new Voucher();
            $voucher->showroom_id = auth()->user()->showroom_id;
            $voucher->payment_type_id = $paymentModeType;
            $voucher->payment_account_head_id = $paymentMode;//Payment Mode
            $voucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $voucher->amount = $request->payment;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->advance_supplier_id = $customer->id;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit
            $transaction = new Transaction();
            $transaction->showroom_id = auth()->user()->showroom_id;
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            $transaction->account_head_id = $paymentMode;//Assets: Decrease in Cash/Bank
            $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->advance_supplier_id = $customer->id;
            $transaction->notes = $request->notes;
            $transaction->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit
            $transaction = new Transaction();
            $transaction->showroom_id = auth()->user()->showroom_id;
            $transaction->voucher_id = $voucher->id;
            $transaction->payment_type_id = $paymentModeType;
            $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            $transaction->account_head_id = $advanceAccountHead->id;//Current Asset: Advance from Customer
            $transaction->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $request->payment;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->advance_supplier_id = $customer->id;
            $transaction->notes = $request->notes;
            $transaction->payment_purpose = PaymentPurpose::$ADVANCE_COLLECTION;
            $transaction->user_id = auth()->id();
            $transaction->save();



            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status' => true,
                'redirect_url' => route('customer.advance'),
                'message' => 'Customer advance receive successful',
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                ' ' . $e->getLine(),
            ]);
        }
    }
    public function paymentReceive(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('sales_payment_receive')) {
            abort(403, 'Unauthorized');
        }
        if ($request->order_id) {
            $saleOrder = SaleOrder::find($request->order_id);
            $request['due_hidden'] = $saleOrder->due;
        }

        // Validate the request data
        $rules = [
            'order_no' => 'required',
            'payment_mode' => 'required',
            'paid' => 'required|numeric|min:.01|max:' . $request->due_hidden,
            'due' => 'required|numeric|min:0',
            'date' => ['required', 'date', new BusinessDate()],
            'notes' => 'nullable|string|max:255',
        ];
        $paymentMode = 0;
        $paymentModeType = 0;
        if ($request->payment_mode != '' && $request->payment_mode !== 'advance') {
            [$paymentMode, $paymentModeType] = explode('|', $request->payment_mode);
        }
        if ($paymentModeType == 1) {
            //bank
            $rules['cheque_no'] = 'nullable|max:255';
        }
        $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {
            $saleOrder = SaleOrder::find($request->order_id);

            if ($request->payment_mode !== 'advance') {
                $advanceAdjust = false;
            } else {
                $customer = Supplier::where('id', $saleOrder->customer_id)->first();
                if ($customer->outstanding_balance < $request->paid) {
                    throw new \Exception('Advance outstanding balance is greater than paid amount');
                }
                $advanceAdjust = true;
            }

            $sisterConcern = auth()->user()->sisterConcern;
            $areaManagerId = $sisterConcern->area_manager_id ?? null;
            $branchManagerId = $sisterConcern->branch_manager_id ?? null;


            $isHideTrnx = 1;
            if ($saleOrder->vat_amount == 0) {
                $isHideTrnx = 0;
            }
            $voucherNo = generateVoucherNo($advanceAdjust ? VoucherType::$JOURNAL_VOUCHER : VoucherType::$COLLECTION_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $payeeId = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first()->id ?? null;
            $advanceAccountHead = AccountHead::where('advance_supplier_id', $saleOrder->customer_id)->first();

            $voucher = new Voucher();
            $voucher->is_show = $isHideTrnx;
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->due_payment = 1;//Due Collection
            if (!$advanceAdjust) {
                $voucher->payment_type_id = $paymentModeType;
                $voucher->payment_account_head_id = $paymentMode;//Payment Mode
            } else {
                $voucher->advance_supplier_id = $customer->id;
            }
            $voucher->voucher_type = $advanceAdjust ? VoucherType::$JOURNAL_VOUCHER : VoucherType::$COLLECTION_VOUCHER;

            $voucher->amount = $request->paid;
            $voucher->date = Carbon::parse($request->date)->format('Y-m-d');
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_no = $voucherNo;
            $voucher->account_head_payee_depositor_id = $payeeId;
            $voucher->sale_order_id = $saleOrder->id;
            $voucher->cheque_no = $request->cheque_no;
            $voucher->notes = $request->notes;
            $voucher->payment_purpose = $advanceAdjust ? PaymentPurpose::$ADVANCE_COLLECTION_ADJUST : PaymentPurpose::$ORDER_DUE_COLLECTION;
            $voucher->user_id = auth()->id();
            $voucher->save();

            //Debit: Cash/Bank (Asset account)
            $transaction = new Transaction();
            $transaction->is_show = $isHideTrnx;
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            if (!$advanceAdjust) {
                $transaction->payment_type_id = $paymentModeType;
                $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            } else {
                $transaction->advance_supplier_id = $customer->id;
            }
            $transaction->voucher_type = $advanceAdjust ? VoucherType::$JOURNAL_VOUCHER : VoucherType::$COLLECTION_VOUCHER;
            $transaction->account_head_id = $advanceAdjust ? ($advanceAccountHead->id) : $paymentMode;//Assets: Decrease in Cash/Bank/Advance Adjust
            $transaction->transaction_type = TransactionType::$DEBIT;
            $transaction->amount = $request->paid;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->sale_order_id = $saleOrder->id;
            $transaction->notes = $request->notes;
            $transaction->payment_purpose = $advanceAdjust ? PaymentPurpose::$ADVANCE_COLLECTION_ADJUST : PaymentPurpose::$ORDER_DUE_COLLECTION;
            $transaction->user_id = auth()->id();
            $transaction->save();

            //Credit: Accounts Receivable (Asset account)
            $transaction = new Transaction();
            $transaction->is_show = $isHideTrnx;
            $transaction->sister_concern_id = auth()->user()->activeConcernId();
            $transaction->voucher_id = $voucher->id;
            if (!$advanceAdjust) {
                $transaction->payment_type_id = $paymentModeType;
                $transaction->payment_account_head_id = $paymentMode;//Payment Mode
            } else {
                $transaction->advance_supplier_id = $customer->id;
            }
            $transaction->voucher_type = $advanceAdjust ? VoucherType::$JOURNAL_VOUCHER : VoucherType::$COLLECTION_VOUCHER;

            $transaction->account_head_id = $payeeId;//
            $transaction->transaction_type = TransactionType::$CREDIT;
            $transaction->amount = $request->paid;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->voucher_no_group_sl = $voucherNoGroupSl;
            $transaction->voucher_no = $voucherNo;
            $transaction->account_head_payee_depositor_id = $payeeId;
            $transaction->sale_order_id = $saleOrder->id;
            $transaction->notes = $request->notes;
            $transaction->payment_purpose = $advanceAdjust ? PaymentPurpose::$ADVANCE_COLLECTION_ADJUST : PaymentPurpose::$ORDER_DUE_COLLECTION;
            $transaction->user_id = auth()->id();
            $transaction->save();



            //Sale Order Update
            $saleOrder->increment('paid', $request->paid);
            $saleOrder->decrement('due', $request->paid);

            if ($advanceAdjust) {
                $saleOrder->increment('advance_adjust', $request->paid);
                if ($customer) {
                    $customer->increment('advance_settled', $request->paid);
                    $customer->decrement('outstanding_balance', $request->paid);
                }

            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status' => true,
                'redirect_url' => route('sales.details', ['sale' => $saleOrder->id]),
                'message' => 'Payment successful',
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
            ]);
        }
    }
    public function getReferralDetails(Request $request)
    {
        $employee = Employee::select('id', 'name', 'referral_code', 'referral_discount')
            ->where('referral_code', $request->referralCode)
            ->first();
        if ($employee) {
            return response()->json([
                'status' => true,
                'employee' => $employee,
                'message' => 'This referral code valid!',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'This referral code invalid!',
            ]);
        }
    }
    public function getCouponDetails(Request $request)
    {

        $coupon = Coupon::where('showroom_id', auth()->user()->showroom_id)
            ->where('code', $request->couponCode)
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('end_date', '>=', date('Y-m-d'))
            ->where('is_active', 1)
            ->first();
        if ($coupon) {
            $saleOrder = SaleOrder::where('showroom_id', auth()->user()->showroom_id)
                ->where('coupon_code', $request->couponCode)
                ->where('customer_id', $request->customerId)
                ->first();

            if ($saleOrder) {
                return response()->json([
                    'status' => false,
                    'message' => 'This coupon code has already been used by this customer!',
                ]);
            } else {
                return response()->json([
                    'status' => true,
                    'coupon' => $coupon,
                    'message' => 'This coupon code valid!',
                ]);
            }

        } else {
            return response()->json([
                'status' => false,
                'message' => 'This coupon code invalid!',
            ]);
        }
    }
    public function posProductSearch(Request $request)
    {
        $inventory = Inventory::where('product_code', $request->productCode)
            ->where('batch_no', $request->productBatch)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->with('product', 'product.unit')
            ->first();
        if ($inventory && $inventory->quantity > 0) {
            $inventory->selling_price = $inventory->product->selling_price ?? 0;
            $inventory->wholesale_price = $inventory->product->wholesale_price ?? 0;
            return response()->json([
                'status' => true,
                'inventory' => $inventory,
            ]);
        } elseif ($inventory && $inventory->quantity == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Stock out !',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not Found !',
            ]);
        }
    }
    public function utilizeProductSearch(Request $request)
    {
        $inventory = Inventory::where('inventory_type', 2)
            ->where('product_code', $request->productCode)
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->with('product', 'product.unit')
            ->first();
        if ($inventory && $inventory->quantity > 0) {
            $inventory->selling_price = $inventory->product->selling_price ?? 0;
            $inventory->wholesale_price = $inventory->product->wholesale_price ?? 0;
            return response()->json([
                'status' => true,
                'inventory' => $inventory,
            ]);
        } elseif ($inventory && $inventory->quantity == 0) {
            return response()->json([
                'status' => false,
                'message' => 'Stock out !',
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not Found !',
            ]);
        }
    }
    public function rawProductSearch(Request $request)
    {
        $inventory = Inventory::where('inventory_type', 0)
            ->where('product_code', $request->productCode)
            ->where('showroom_id', auth()->user()->showroom_id)
            ->with('product', 'product.productCategory', 'product.unit', 'product.brand')
            ->first();
        if ($inventory) {
            $inventory->selling_price = $inventory->product->selling_price ?? 0;
            $inventory->wholesale_price = $inventory->product->wholesale_price ?? 0;
            return response()->json([
                'status' => true,
                'inventory' => $inventory,
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Not Found !',
            ]);
        }
    }
    public function partyJson(Request $request)
    {
        if (!$request->searchTerm) {
            $clients = Supplier::select('id', 'name', 'member_no', 'mobile_no')
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->whereIn('type', $request->type)
                ->limit(10)
                ->get();
        } else {
            $clients = Supplier::select('id', 'name', 'member_no', 'mobile_no')
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where(function ($query) use ($request) {
                    $query->where('name', 'like', '%' . $request->searchTerm . '%')
                        ->orWhere('mobile_no', 'like', '%' . $request->searchTerm . '%')
                        ->orWhere('member_no', 'like', '%' . $request->searchTerm . '%');
                })->whereNotNull('id')
                ->whereIn('type', $request->type)
                ->limit(50)
                ->get();
        }
        $data = array();
        foreach ($clients as $client) {
            if (!empty($client->member_no)) {
                $text = $client->member_no . ' - ' . $client->name;
            } else {
                $text = $client->name;
            }

            $data[] = [
                'id' => $client->id,
                'text' => $text,
            ];
        }

        return response()->json($data);
    }

    public function customerValidationCheck(Request $request)
    {
        $customerId = $request->customer_id;
        $customer = Supplier::find($customerId);
        $currentBalance = $customer ? $customer->customer_current_balance : 0;
        $hasPendingPreOrder = $customer ? $customer->hasPendingPreOrder() : false;

        return response()->json([
            'current_balance' => $currentBalance,
            'has_pending_preorder' => $hasPendingPreOrder
        ]);
    }

    public function searchCouponAutocomplete(Request $request)
    {
        // Retrieve the 'query' term from the request
        $search = $request->get('query');

        // Query the Inventory model for matching product codes
        $result = Coupon::where('showroom_id', auth()->user()->showroom_id)
            ->whereDate('start_date', '<=', date('Y-m-d'))
            ->whereDate('end_date', '>=', date('Y-m-d'))
            ->where('is_active', 1)
            ->where('code', 'LIKE', '%' . $search . '%')
            ->pluck('code'); // Only return product codes as an array

        // Return the array of product codes in JSON format
        return response()->json($result);
    }
    public function searchBarcodeAutocomplete(Request $request)
    {
        // Retrieve the 'query' term from the request
        $search = $request->get('query');
        $warehouse = $request->get('warehouse');

        // Query the Inventory model for matching product codes
        // $result = Inventory::with('product')->where('sister_concern_id', auth()->user()->activeConcernId())
        //     ->where('warehouse_id',$warehouse)
        //     ->where('quantity','>',0)
        //     ->where('product_code', 'LIKE', '%' . $search . '%')
        //     ->pluck('product_code'); // Only return product codes as an array
        $result = Inventory::with('product')
            ->where('sister_concern_id', auth()->user()->activeConcernId())
            ->where('warehouse_id', $warehouse)
            ->where('quantity', '>', 0)
            ->where(function ($query) use ($search) {
                $query->where('product_code', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('product', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    });
            })
            ->get()
            ->filter(function ($inventory) {
                return $inventory->product !== null; // Avoid null relations
            })
            ->map(function ($inventory) {
                return $inventory->product->name . ' | ' . $inventory->product_code . ' | Batch-' . $inventory->batch_no;
            })
            ->values();

        // Return the array of product codes in JSON format
        return response()->json($result);
    }
    public function searchBarcodeAutocompleteGlobal(Request $request)
    {
        // Retrieve the 'query' term from the request
        $search = $request->get('query');
        $inventoryType = $request->get('inventory_type');

        // Query the Inventory model for matching product codes
        $result = Inventory::select('product_code')
            ->where('inventory_type', $inventoryType)//Finished Goods/Raw
            ->where('product_code', 'LIKE', '%' . $search . '%')
            ->groupBy('product_code')
            ->pluck('product_code'); // Only return product codes as an array

        // Return the array of product codes in JSON format
        return response()->json($result);
    }

    public function destroy(SaleOrder $sale)
    {
        abort_if($sale->packageCheckout !== null, 403, 'Use the package order workflow.');
        if (!auth()->user()->hasPermissionTo('sales_delete')) {
            abort(403, 'Unauthorized');
        }

        DB::beginTransaction();
        try {
            // Check for Sales Returns
            // if (\App\Models\SalesReturn::where('sale_order_id', $sale->id)->exists()) {
            //     return response()->json(['success' => false, 'message' => 'Cannot delete: Invoice has associated Sales Returns.'], 200);
            // }

            // Check if there are any vouchers created later (e.g. from CustomerPaymentController)
            // A normal sale has at most 2 vouchers: JOURNAL_VOUCHER (Sale) and optionally COLLECTION_VOUCHER (Initial Cash)
            // But if a Voucher's DEBIT transaction doesn't have sale_order_id, it means it's a split payment from CustomerPaymentController
            $hasLaterPayments = Voucher::where('sale_order_id', $sale->id)
                ->whereHas('transactions', function ($q) {
                    $q->whereNull('sale_order_id');
                })->exists();

            if ($hasLaterPayments) {
                return response()->json(['success' => false, 'message' => 'Cannot delete: Invoice has subsequent Customer Payments. Please delete the payments first.'], 200);
            }

            $saleItems = SaleOrderItem::where('sale_order_id', $sale->id)->get();
            foreach ($saleItems as $saleItem) {
                $inventory = Inventory::where('id', $saleItem->inventory_id)->first();
                if ($inventory) {
                    $inventory->increment('quantity', $saleItem->quantity);
                }
            }
            SaleOrderItem::where('sale_order_id', $sale->id)->delete();
            InventoryLog::where('sale_order_id', $sale->id)->delete();
            Voucher::where('sale_order_id', $sale->id)->delete();
            Transaction::where('sale_order_id', $sale->id)->delete();

            // Delete incentive entries so they don't get paid for a deleted sale
            $ledgers = IncentiveLedger::where('reference_type', SaleOrder::class)
                ->where('reference_id', $sale->id)
                ->get();
            foreach ($ledgers as $ledger) {
                if ($ledger->payment_status === 'paid') {
                    throw new \Exception("এই সেলসটি ডিলিট করা সম্ভব নয়! কারণ এই সেলসের বিপরীতে জেনারেট হওয়া ইনসেনটিভের টাকা ইতিমধ্যে এমপ্লয়িকে পেমেন্ট করা হয়ে গেছে।");
                }
                $ledger->delete();
            }

            // Delete the User record
            $sale->delete();
            // Return a JSON success response
            DB::commit();
            return response()->json(['success' => true, 'message' => 'Sales order deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            // Handle any errors, such as record not found
            return response()->json(['success' => false, 'message' => 'Sales order not found: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }

    private function createAccountingEntries($saleOrder, $costingTotal, $request)
    {
        $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first();
        $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->first();
        if (!$customerAccountHead) {
            $customerAccountHead = new AccountHead();
            $customerAccountHead->code = generateAccountCode($receivablesGroup->top_reserved_id);
            $customerAccountHead->sister_concern_id = null;
            $customerAccountHead->account_group_id = $receivablesGroup->id;
            $customerAccountHead->supplier_id = $saleOrder->customer_id;
            $customerAccountHead->name = $saleOrder->customer->name . '(Customer)';
            $customerAccountHead->save();
        }
        $payeeId = $customerAccountHead->id;

        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
        $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = new Voucher();
        $voucher->sister_concern_id = auth()->user()->activeConcernId();
        $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $voucher->amount = $saleOrder->total + $saleOrder->discount + $costingTotal;
        $voucher->date = $saleOrder->date->format('Y-m-d');
        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
        $voucher->voucher_no = $voucherNo;
        $voucher->account_head_payee_depositor_id = $payeeId;
        $voucher->sale_order_id = $saleOrder->id;
        $voucher->notes = $saleOrder->notes;
        $voucher->user_id = auth()->id();
        $voucher->save();

        $this->transaction($voucher, $payeeId, TransactionType::$DEBIT, $saleOrder->total, $saleOrder->id, $saleOrder->notes);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE), TransactionType::$CREDIT, $saleOrder->sub_total, $saleOrder->id, $saleOrder->notes);

        if ($saleOrder->discount > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_DISCOUNT), TransactionType::$DEBIT, $saleOrder->discount, $saleOrder->id, $saleOrder->notes);
        }

        if ($saleOrder->vat_amount > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::VAT_PAYABLE), TransactionType::$CREDIT, $saleOrder->vat_amount, $saleOrder->id, $saleOrder->notes);
        }

        if ($costingTotal > 0) {
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD), TransactionType::$DEBIT, $costingTotal, $saleOrder->id, $saleOrder->notes);
            $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY), TransactionType::$CREDIT, $costingTotal, $saleOrder->id, $saleOrder->notes);
        }

        $advanceAdjustAmount = $request->advance_amount ?? 0;
        if ($advanceAdjustAmount > 0) {
            // Note: Journal voucher for advance offset could be created here if needed.
            // Currently using Voucher and Transaction for accounts, SalePayment is removed.
        }

        $cashPaidAmount = $request->paid_amount ?? 0;

        if ($cashPaidAmount > 0) {
            $pNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
            $pNoGroupSl = abs((int) filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
            $pVoucher = new Voucher();
            $pVoucher->sister_concern_id = auth()->user()->activeConcernId();
            $pVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
            $pVoucher->payment_type_id = AccountHead::find($request->payment_method)->payment_mode ?? null;
            $pVoucher->payment_account_head_id = $request->payment_method; // Receipt Mode
            $pVoucher->amount = $cashPaidAmount;
            $pVoucher->date = $saleOrder->date->format('Y-m-d');
            $pVoucher->voucher_no_group_sl = $pNoGroupSl;
            $pVoucher->voucher_no = $pNo;
            $pVoucher->account_head_payee_depositor_id = $payeeId;
            $pVoucher->sale_order_id = $saleOrder->id;
            $pVoucher->notes = $saleOrder->notes;
            $pVoucher->payment_purpose = PaymentPurpose::$INSTANT_COLLECTION;
            $pVoucher->user_id = auth()->id();
            $pVoucher->save();

            $this->transaction($pVoucher, $request->payment_method, TransactionType::$DEBIT, $cashPaidAmount, $saleOrder->id, $saleOrder->notes, PaymentPurpose::$INSTANT_COLLECTION);
            $this->transaction($pVoucher, $payeeId, TransactionType::$CREDIT, $cashPaidAmount, $saleOrder->id, $saleOrder->notes, PaymentPurpose::$INSTANT_COLLECTION);
        }
    }

    private function transaction($voucher, $headId, $type, $amount, $saleOrderId, $notes = null, $paymentPurpose = null)
    {
        $transaction = new Transaction();
        $transaction->sister_concern_id = auth()->user()->activeConcernId();
        $transaction->voucher_id = $voucher->id;
        $transaction->account_head_id = $headId;
        $transaction->voucher_type = $voucher->voucher_type;
        $transaction->transaction_type = $type;
        $transaction->amount = $amount;
        $transaction->date = $voucher->date;
        $transaction->voucher_no_group_sl = $voucher->voucher_no_group_sl;
        $transaction->voucher_no = $voucher->voucher_no;
        $transaction->sale_order_id = $saleOrderId;
        $transaction->notes = $notes;
        $transaction->payment_purpose = $paymentPurpose;
        $transaction->user_id = auth()->id();
        $transaction->save();
    }

    private function inventoryLog($saleOrder, $inventory, $qty, $sellingPrice, $code)
    {
        $log = new InventoryLog();
        $log->type = 2;
        $log->sister_concern_id = auth()->user()->activeConcernId();
        $log->inventory_id = $inventory->id;
        $log->supplier_id = $saleOrder->customer_id;
        $log->sale_order_id = $saleOrder->id;
        $log->product_id = $inventory->product_id;
        $log->batch_no = $inventory->batch_no;
        $log->quantity = $qty;
        $log->unit_price = $sellingPrice;
        $log->product_code = $code;
        $log->user_id = auth()->id();
        $log->date = $saleOrder->date;
        $log->save();
    }

    public function printInvoice(SaleOrder $sale)
    {
        if ($sale->type != SaleType::$GENERAL)
            abort(404);
        $sale->load('saleOrderItems.product.unit', 'saleOrderItems.inventory', 'customer', 'user');

        // Group items by product_id AND selling_price for Customer Copy
        $groupedItems = $sale->saleOrderItems->groupBy(function ($item) {
            return $item->product_id . '-' . $item->selling_price;
        })->map(function ($items) {
            $firstItem = $items->first();
            return (object) [
                'product' => $firstItem->product,
                'product_code' => $firstItem->product_code,
                'total_quantity' => $items->sum('quantity'),
                'selling_price' => $firstItem->selling_price,
                'total_price' => $items->sum(function ($item) {
                    return $item->quantity * $item->selling_price;
                }),
                'batches' => $items->map(function ($item) {
                    return 'Batch-' . $item->inventory->batch_no;
                })->unique()->implode(', '),
            ];
        });

        $sale->amount_in_word = DecimalToWords::convert($sale->total, 'Taka', 'Poisa');

        return view('sales.print', compact('sale', 'groupedItems'));
    }
}

