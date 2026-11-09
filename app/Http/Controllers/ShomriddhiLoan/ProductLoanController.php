<?php

namespace App\Http\Controllers\ShomriddhiLoan;

use App\Enumeration\RoleType;
use App\Enumeration\SaleType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\SupplierType;
use App\Models\Hrm\Employee;
use App\Rules\BusinessDate;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountHead;
use App\Models\Inventory\Inventory;
use App\Models\Inventory\InventoryLog;
use App\Models\Settings\Product;
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SaleOrderItem;
use App\Models\Sales\SalePayment;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Settings\Warehouse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use App\Traits\UploadTrait;
use App\Models\Sales\EmiSaleDetail;

class ProductLoanController extends Controller
{
    use UploadTrait;

    public function index()
    {
        if (!auth()->user()->hasPermissionTo('product_loans')) {
            abort(403, 'Unauthorized');
        }
        $paymentModes = AccountHead::where('payment_mode', '>', 0)->where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->get();
        $customers = Supplier::where('status', 1)->whereNotNull('id')->where('type', SupplierType::$CUSTOMER)->get();
        $srs = Employee::where('status', 1)->where('sister_concern_id', auth()->user()->activeConcernId())->srs()->get();
        return view('shomriddhi_loan.product_loans.index', compact('paymentModes', 'customers', 'srs'));
    }

    public function dataTable()
    {
        $query = SaleOrder::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', SaleType::$EMI)->with('customer', 'sr', 'saleOrderItems.product');
        return DataTables::eloquent($query)
            ->addColumn('action', function (SaleOrder $sales_order) {
                $btn = '';
                
                $hasPaid = \App\Models\ShomriddhiLoan\LoanAccount::where('sale_order_id', $sales_order->id)->where('paid_amount', '>', 0)->exists();
                if (!$hasPaid) {
                    $btn .= ' <a href="' . route('shomriddhi-loan.product-loans.edit', ['emi_sale' => $sales_order->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-edit"></i></a>';
                }
                $btn .= ' <a href="' . route('shomriddhi-loan.product-loans.show', ['emi_sale' => $sales_order->id]) . '" class="btn btn-info bg-gradient-info btn-sm" title="Details"><i class="fa fa-info-circle"></i></a>';
                $btn .= ' <a href="' . route('shomriddhi-loan.product-loans.print', ['emi_sale' => $sales_order->id]) . '" target="_blank" class="btn btn-default btn-sm" title="Print"><i class="fa fa-print"></i></a>';
                if ($sales_order->hasActiveWarrantyItems()) {
                    $btn .= ' <a href="' . route('service.request.create', ['sale_type' => \App\Enumeration\SaleType::$EMI, 'sale_order_id' => $sales_order->id]) . '" class="btn btn-warning bg-gradient-warning btn-sm" title="Service Request"><i class="fa fa-cogs text-white"></i> Service</a>';
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('customer_name', function (SaleOrder $sales_order) { return $sales_order->customer->name ?? ''; })
            ->addColumn('sr_name', function (SaleOrder $sales_order) { return $sales_order->sr->name ?? ''; })
            ->rawColumns(['action'])->toJson();
    }

    public function show(SaleOrder $emi_sale)
    {
        if ($emi_sale->type != SaleType::$EMI) abort(404);
        if (!auth()->user()->hasPermissionTo('product_loans')) {
            abort(403, 'Unauthorized');
        }
        $emi_sale->load('saleOrderItems.product', 'customer', 'emiSaleDetail');
        $loanAccount = \App\Models\ShomriddhiLoan\LoanAccount::where('sale_order_id', $emi_sale->id)->with('installments')->first();
        
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
        return view('shomriddhi_loan.product_loans.details', compact('emi_sale', 'paymentModes', 'loanAccount'));
    }

    public function edit(SaleOrder $emi_sale)
    {
        if ($emi_sale->type != SaleType::$EMI) abort(404);
        if (!auth()->user()->hasPermissionTo('product_loans_edit')) abort(403);
        
        $loanAccount = \App\Models\ShomriddhiLoan\LoanAccount::where('sale_order_id', $emi_sale->id)->first();
        if ($loanAccount && $loanAccount->paid_amount > 0) {
            return redirect()->back()->with('error', 'Cannot edit EMI sale with paid installments.');
        }

        $emi_sale->load('saleOrderItems.product', 'customer', 'emiSaleDetail');
        $sale = $emi_sale;
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->srs()->get();
        
        $selectedPaymentMethod = null;
        if ($sale->paid > 0) {
            $collectionVoucher = Voucher::where('sale_order_id', $sale->id)
                ->where('voucher_type', VoucherType::$COLLECTION_VOUCHER)
                ->first();
            if ($collectionVoucher) {
                $debitTransaction = Transaction::where('voucher_id', $collectionVoucher->id)
                    ->where('transaction_type', TransactionType::$DEBIT)
                    ->first();
                if ($debitTransaction) {
                    $selectedPaymentMethod = $debitTransaction->account_head_id;
                }
            }
        }
        
        return view('shomriddhi_loan.product_loans.edit', compact('sale', 'warehouses', 'products', 'paymentModes', 'srs', 'selectedPaymentMethod'));
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('product_loans_create')) abort(403);
        $warehouses = Warehouse::where('sister_concern_id', auth()->user()->activeConcernId())->where('type', 'main')->where('status', 1)->get();
        $products = Product::where('status', 1)->get();
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();
        $srs = Employee::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->srs()->get();
        return view('shomriddhi_loan.product_loans.create', compact('warehouses', 'products', 'paymentModes', 'srs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'warehouse' => 'required',
            'customer' => 'required',
            'date' => ['required', 'date', new BusinessDate()],
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'sr' => 'required|exists:suppliers,id',
            'no_of_installment' => 'required|numeric|min:1',
            'installment_amount' => 'required|numeric|min:0',
            'installment_start_date' => 'required|date',
            'discount_type' => 'required|in:1,2',
            'payment_method' => 'required_if:paid_amount,>,0|nullable|exists:account_heads,id',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:20',
            'guarantor_nid' => 'nullable|string|max:30',
            'customer_nid_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'customer_photo_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'guarantor_nid_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'security_cheque_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'agreement_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'late_fee_type' => 'nullable|in:1,2',
            'late_fee_rate' => 'nullable|numeric|min:0',
            'memo_no' => 'required',
        ]);

        $customer = Supplier::find($request->customer);
        if ($customer && $customer->customer_current_balance > 0) {
            return redirect()->back()->with('error', 'This client has previous dues. You cannot generate a new order until it is paid.')->withInput();
        }

        if ($customer && $customer->hasPendingPreOrder()) {
            return redirect()->back()->with('error', 'This client has an unresolved Pre-Order. Please deliver or cancel it first.')->withInput();
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
            $saleOrder->order_no = generateOrderNo('sale_orders', 'order_no', 'ESO-');
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->vat_percent = $request->vat_percent ?? 0;
            $saleOrder->vat_amount = $request->vat_amount ?? 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->paid = $request->paid_amount ?? 0;
            $saleOrder->due = $request->due_amount ?? 0;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->notes = $request->notes;
            $saleOrder->memo_no = $request->memo_no;
            $saleOrder->type = SaleType::$EMI;
            $saleOrder->user_id = auth()->id();
            $saleOrder->save();

            // Save Guarantor and Security Documents
            $emiDetail = new EmiSaleDetail();
            $emiDetail->sale_order_id = $saleOrder->id;
            $emiDetail->guarantor_name = $request->guarantor_name;
            $emiDetail->guarantor_phone = $request->guarantor_phone;
            $emiDetail->guarantor_nid = $request->guarantor_nid;
            $emiDetail->late_fee_type = $request->late_fee_type ?? 1;
            $emiDetail->late_fee_rate = $request->late_fee_rate ?? 10;

            if ($request->hasFile('customer_nid_attachment')) {
                $emiDetail->customer_nid_attachment = $this->uploadImage($request->file('customer_nid_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('customer_photo_attachment')) {
                $emiDetail->customer_photo_attachment = $this->uploadImage($request->file('customer_photo_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('guarantor_nid_attachment')) {
                $emiDetail->guarantor_nid_attachment = $this->uploadImage($request->file('guarantor_nid_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('security_cheque_attachment')) {
                $emiDetail->security_cheque_attachment = $this->uploadImage($request->file('security_cheque_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('agreement_attachment')) {
                $emiDetail->agreement_attachment = $this->uploadImage($request->file('agreement_attachment'), 'emi/attachments');
            }
            $emiDetail->save();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->product_id as $productId) {
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) throw new \Exception("Stock error.");

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $productId;
                $saleItem->product_code = $request->product_code[$counter];
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter] + ($request->product_interest_amount[$counter] ?? 0);
                $saleItem->interest_rate = $request->product_interest_rate[$counter] ?? 0;
                $saleItem->interest_amount = $request->product_interest_amount[$counter] ?? 0;
                $saleItem->save();

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $inventory->decrement('quantity', $request->product_quantity[$counter]);
                $this->inventoryLog($saleOrder, $inventory, $request->product_quantity[$counter], $saleItem->selling_price, $request->product_code[$counter]);
                $counter++;
            }

            $totalEmiAmount = $saleOrder->due;
            $noOfInstallments = (int)$request->no_of_installment;
            $standardInstallmentAmount = round($totalEmiAmount / $noOfInstallments);
            
            // Create Loan Account for Product Loan
            $loanAccount = new \App\Models\ShomriddhiLoan\LoanAccount();
            $loanAccount->origin_sister_concern_id = $saleOrder->sister_concern_id;
            $loanAccount->loan_no = generateOrderNo('loan_accounts', 'loan_no', 'L-');
            $loanAccount->customer_id = $request->customer;
            $loanAccount->sale_order_id = $saleOrder->id;
            $loanAccount->type = 'product';
            $loanAccount->principal_amount = $totalEmiAmount;
            $loanAccount->profit_rate = 0; // Handled in product pricing
            $loanAccount->total_payable = $totalEmiAmount;
            $loanAccount->paid_amount = 0;
            $loanAccount->installment_type = 'monthly';
            $loanAccount->total_installments = $noOfInstallments;
            $loanAccount->installment_amount = $standardInstallmentAmount;
            $loanAccount->disbursement_date = $saleOrder->date;
            $loanAccount->status = 'active';
            $loanAccount->created_by = auth()->id();
            $loanAccount->save();

            $installmentStartDate = Carbon::parse($request->installment_start_date);
            for ($i = 1; $i <= $noOfInstallments; $i++) {
                $installment = new \App\Models\ShomriddhiLoan\LoanInstallment();
                $installment->loan_account_id = $loanAccount->id;
                $installment->installment_no = $i;
                
                $amount = ($i == $noOfInstallments) 
                    ? $totalEmiAmount - ($standardInstallmentAmount * ($noOfInstallments - 1)) 
                    : $standardInstallmentAmount;
                    
                $installment->principal_component = $amount;
                $installment->profit_component = 0;
                $installment->total_amount = $amount;
                $installment->paid_amount = 0;
                $installment->due_date = $installmentStartDate->copy()->addMonths($i - 1);
                $installment->status = 'unpaid';
                $installment->save();
            }

            $this->createAccountingEntries($saleOrder, $costingTotal, $request);

            DB::commit();
            return redirect()->route('shomriddhi-loan.product-loans.index')->with('success', 'EMI Sale recorded successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function update(SaleOrder $emi_sale, Request $request)
    {
        $hasPaid = \App\Models\ShomriddhiLoan\LoanAccount::where('sale_order_id', $emi_sale->id)->where('paid_amount', '>', 0)->exists();
        if ($hasPaid) abort(403);

        $request->validate([
            'customer' => 'required',
            'date' => ['required', 'date', new BusinessDate()],
            'sub_total' => 'required|numeric|min:0.1',
            'total_amount' => 'required|numeric|min:0.1',
            'sr' => 'required|exists:suppliers,id',
            'no_of_installment' => 'required|numeric|min:1',
            'installment_amount' => 'required|numeric|min:0',
            'installment_start_date' => 'required|date',
            'discount_type' => 'required|in:1,2',
            'payment_method' => 'required_if:paid_amount,>,0|nullable|exists:account_heads,id',
            'guarantor_name' => 'nullable|string|max:255',
            'guarantor_phone' => 'nullable|string|max:20',
            'guarantor_nid' => 'nullable|string|max:30',
            'customer_nid_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'customer_photo_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'guarantor_nid_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'security_cheque_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'agreement_attachment' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'late_fee_type' => 'nullable|in:1,2',
            'late_fee_rate' => 'nullable|numeric|min:0',
            'memo_no' => 'required',
        ]);

        DB::beginTransaction();
        try {
            $saleOrder = $emi_sale;
            $saleOrder->date = Carbon::parse($request->date);
            $saleOrder->customer_id = $request->customer;
            $saleOrder->sub_total = $request->sub_total;
            $saleOrder->discount_type = $request->discount_type;
            $saleOrder->discount_percent = $request->discount_percent ?? 0;
            $saleOrder->discount = $request->discount_amount ?? 0;
            $saleOrder->total = $request->total_amount;
            $saleOrder->paid = $request->paid_amount ?? 0;
            $saleOrder->due = $request->due_amount ?? 0;
            $saleOrder->sr_id = $request->sr;
            $saleOrder->notes = $request->notes;
            $saleOrder->memo_no = $request->memo_no;
            $saleOrder->save();

            // Save or Update Guarantor and Security Documents
            $emiDetail = EmiSaleDetail::where('sale_order_id', $saleOrder->id)->first();
            if (!$emiDetail) {
                $emiDetail = new EmiSaleDetail();
                $emiDetail->sale_order_id = $saleOrder->id;
            }
            $emiDetail->guarantor_name = $request->guarantor_name;
            $emiDetail->guarantor_phone = $request->guarantor_phone;
            $emiDetail->guarantor_nid = $request->guarantor_nid;
            $emiDetail->late_fee_type = $request->late_fee_type ?? 1;
            $emiDetail->late_fee_rate = $request->late_fee_rate ?? 10;

            if ($request->hasFile('customer_nid_attachment')) {
                if ($emiDetail->customer_nid_attachment) {
                    $this->deleteFile($emiDetail->customer_nid_attachment);
                }
                $emiDetail->customer_nid_attachment = $this->uploadImage($request->file('customer_nid_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('customer_photo_attachment')) {
                if ($emiDetail->customer_photo_attachment) {
                    $this->deleteFile($emiDetail->customer_photo_attachment);
                }
                $emiDetail->customer_photo_attachment = $this->uploadImage($request->file('customer_photo_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('guarantor_nid_attachment')) {
                if ($emiDetail->guarantor_nid_attachment) {
                    $this->deleteFile($emiDetail->guarantor_nid_attachment);
                }
                $emiDetail->guarantor_nid_attachment = $this->uploadImage($request->file('guarantor_nid_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('security_cheque_attachment')) {
                if ($emiDetail->security_cheque_attachment) {
                    $this->deleteFile($emiDetail->security_cheque_attachment);
                }
                $emiDetail->security_cheque_attachment = $this->uploadImage($request->file('security_cheque_attachment'), 'emi/attachments');
            }
            if ($request->hasFile('agreement_attachment')) {
                if ($emiDetail->agreement_attachment) {
                    $this->deleteFile($emiDetail->agreement_attachment);
                }
                $emiDetail->agreement_attachment = $this->uploadImage($request->file('agreement_attachment'), 'emi/attachments');
            }
            $emiDetail->save();

            foreach ($saleOrder->saleOrderItems as $item) {
                Inventory::find($item->inventory_id)->increment('quantity', $item->quantity);
                InventoryLog::where('sale_order_id', $saleOrder->id)->where('inventory_id', $item->inventory_id)->delete();
            }
            SaleOrderItem::where('sale_order_id', $saleOrder->id)->delete();

            $costingTotal = 0;
            $counter = 0;
            foreach ($request->product_id as $productId) {
                $inventory = Inventory::find($request->inventory_id[$counter]);
                if (!$inventory || $inventory->quantity < $request->product_quantity[$counter]) throw new \Exception("Stock error.");

                $saleItem = new SaleOrderItem();
                $saleItem->sister_concern_id = auth()->user()->activeConcernId();
                $saleItem->sale_order_id = $saleOrder->id;
                $saleItem->date = Carbon::parse($request->date);
                $saleItem->inventory_id = $inventory->id;
                $saleItem->product_id = $productId;
                $saleItem->product_code = $request->product_code[$counter];
                $saleItem->quantity = $request->product_quantity[$counter];
                $saleItem->unit_price = $inventory->unit_price;
                $saleItem->selling_price = $request->product_unit_price[$counter] + ($request->product_interest_amount[$counter] ?? 0);
                $saleItem->interest_rate = $request->product_interest_rate[$counter] ?? 0;
                $saleItem->interest_amount = $request->product_interest_amount[$counter] ?? 0;
                $saleItem->save();

                $costingTotal += $inventory->unit_price * $request->product_quantity[$counter];
                $inventory->decrement('quantity', $request->product_quantity[$counter]);
                $this->inventoryLog($saleOrder, $inventory, $request->product_quantity[$counter], $saleItem->selling_price, $request->product_code[$counter]);
                $counter++;
            }

            \App\Models\ShomriddhiLoan\LoanAccount::where('sale_order_id', $saleOrder->id)->delete();
            
            $totalEmiAmount = $saleOrder->due;
            $noOfInstallments = (int)$request->no_of_installment;
            $standardInstallmentAmount = round($totalEmiAmount / $noOfInstallments);
            
            $loanAccount = new \App\Models\ShomriddhiLoan\LoanAccount();
            $loanAccount->origin_sister_concern_id = $saleOrder->sister_concern_id;
            $loanAccount->loan_no = generateOrderNo('loan_accounts', 'loan_no', 'L-');
            $loanAccount->customer_id = $request->customer;
            $loanAccount->sale_order_id = $saleOrder->id;
            $loanAccount->type = 'product';
            $loanAccount->principal_amount = $totalEmiAmount;
            $loanAccount->profit_rate = 0;
            $loanAccount->total_payable = $totalEmiAmount;
            $loanAccount->paid_amount = 0;
            $loanAccount->installment_type = 'monthly';
            $loanAccount->total_installments = $noOfInstallments;
            $loanAccount->installment_amount = $standardInstallmentAmount;
            $loanAccount->disbursement_date = $saleOrder->date;
            $loanAccount->status = 'active';
            $loanAccount->created_by = auth()->id();
            $loanAccount->save();

            $installmentStartDate = Carbon::parse($request->installment_start_date);
            for ($i = 1; $i <= $noOfInstallments; $i++) {
                $installment = new \App\Models\ShomriddhiLoan\LoanInstallment();
                $installment->loan_account_id = $loanAccount->id;
                $installment->installment_no = $i;
                
                $amount = ($i == $noOfInstallments) 
                    ? $totalEmiAmount - ($standardInstallmentAmount * ($noOfInstallments - 1)) 
                    : $standardInstallmentAmount;
                    
                $installment->principal_component = $amount;
                $installment->profit_component = 0;
                $installment->total_amount = $amount;
                $installment->paid_amount = 0;
                $installment->due_date = $installmentStartDate->copy()->addMonths($i - 1);
                $installment->status = 'unpaid';
                $installment->save();
            }

            Voucher::where('sale_order_id', $saleOrder->id)->delete();
            Transaction::where('sale_order_id', $saleOrder->id)->delete();
            SalePayment::where('sale_order_id', $saleOrder->id)->delete();

            $this->createAccountingEntries($saleOrder, $costingTotal, $request);

            DB::commit();
            return redirect()->route('shomriddhi-loan.product-loans.index')->with('success', 'EMI Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', $e->getMessage())->withInput();
        }
    }

    private function createAccountingEntries($saleOrder, $costingTotal, $request)
    {
        $customerAccountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $saleOrder->customer_id)->first();
        $payeeId = $customerAccountHead->id;
        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
        $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = new Voucher();
        $voucher->sister_concern_id = auth()->user()->activeConcernId();
        $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
        $voucher->amount = $saleOrder->total + $saleOrder->discount + $costingTotal;
        $voucher->date = $saleOrder->date->format('Y-m-d');
        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
        $voucher->voucher_no = $voucherNo;
        $voucher->account_head_payee_depositor_id = $payeeId;
        $voucher->sale_order_id = $saleOrder->id;
        $voucher->user_id = auth()->id();
        $voucher->save();

        $this->transaction($voucher, $payeeId, TransactionType::$DEBIT, $saleOrder->total, $saleOrder->id);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_REVENUE), TransactionType::$CREDIT, $saleOrder->sub_total, $saleOrder->id);
        if ($saleOrder->discount > 0) $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::SALES_DISCOUNT), TransactionType::$DEBIT, $saleOrder->discount, $saleOrder->id);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::COST_OF_GOODS_SOLD), TransactionType::$DEBIT, $costingTotal, $saleOrder->id);
        $this->transaction($voucher, reservedAccountHeadId(ReservedAccountHead::FINISHED_GOODS_INVENTORY), TransactionType::$CREDIT, $costingTotal, $saleOrder->id);

        if ($saleOrder->paid > 0) {
            $advanceAdjust = $request->advance_adjust == 1;

            if ($advanceAdjust) {
                $payment = new SalePayment();
                $payment->sister_concern_id = auth()->user()->activeConcernId();
                $payment->area_manager_id = $saleOrder->area_manager_id;
                $payment->branch_manager_id = $saleOrder->branch_manager_id;
                $payment->sale_order_id = $saleOrder->id;
                $payment->customer_id = $saleOrder->customer_id;
                $payment->sr_id = $saleOrder->sr_id;
                $payment->voucher_id = null;
                $payment->amount = $saleOrder->paid;
                $payment->date = $saleOrder->date->format('Y-m-d');
                $payment->type = 1;
                $payment->notes = 'Paid by Advance Adjustment';
                $payment->user_id = auth()->id();
                $payment->save();

                $advanceOffset = new SalePayment();
                $advanceOffset->sister_concern_id = auth()->user()->activeConcernId();
                $advanceOffset->area_manager_id = $saleOrder->area_manager_id;
                $advanceOffset->branch_manager_id = $saleOrder->branch_manager_id;
                $advanceOffset->sale_order_id = $saleOrder->id;
                $advanceOffset->customer_id = $saleOrder->customer_id;
                $advanceOffset->sr_id = $saleOrder->sr_id;
                $advanceOffset->voucher_id = null;
                $advanceOffset->amount = -$saleOrder->paid;
                $advanceOffset->date = $saleOrder->date->format('Y-m-d');
                $advanceOffset->type = \App\Enumeration\SalePaymentType::$ADVANCE_PAYMENT;
                $advanceOffset->notes = 'Advance Deduction for Order ' . $saleOrder->order_no;
                $advanceOffset->user_id = auth()->id();
                $advanceOffset->save();
            } else {
                $pNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
                $pNoGroupSl = abs((int)filter_var($pNo, FILTER_SANITIZE_NUMBER_INT));
                $pVoucher = new Voucher();
                $pVoucher->sister_concern_id = auth()->user()->activeConcernId();
                $pVoucher->voucher_type = VoucherType::$COLLECTION_VOUCHER;
                $pVoucher->payment_type_id = AccountHead::find($request->payment_method)->payment_mode ?? null;
                $pVoucher->payment_account_head_id = $request->payment_method; // Receipt Mode
                $pVoucher->amount = $saleOrder->paid;
                $pVoucher->date = $saleOrder->date->format('Y-m-d');
                $pVoucher->voucher_no_group_sl = $pNoGroupSl;
                $pVoucher->voucher_no = $pNo;
                $pVoucher->account_head_payee_depositor_id = $payeeId;
                $pVoucher->sale_order_id = $saleOrder->id;
                $pVoucher->user_id = auth()->id();
                $pVoucher->save();

                $this->transaction($pVoucher, $request->payment_method, TransactionType::$DEBIT, $saleOrder->paid, $saleOrder->id);
                $this->transaction($pVoucher, $payeeId, TransactionType::$CREDIT, $saleOrder->paid, $saleOrder->id);

                $payment = new SalePayment();
                $payment->sister_concern_id = auth()->user()->activeConcernId();
                $payment->area_manager_id = $saleOrder->area_manager_id;
                $payment->branch_manager_id = $saleOrder->branch_manager_id;
                $payment->sale_order_id = $saleOrder->id;
                $payment->customer_id = $saleOrder->customer_id;
                $payment->sr_id = $saleOrder->sr_id;
                $payment->voucher_id = $pVoucher->id;
                $payment->amount = $saleOrder->paid;
                $payment->date = $saleOrder->date->format('Y-m-d');
                $payment->type = 1;
                $payment->user_id = auth()->id();
                $payment->save();
            }
        }
    }

    private function transaction($voucher, $headId, $type, $amount, $saleOrderId)
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
        $log->quantity = $qty;
        $log->unit_price = $sellingPrice;
        $log->product_code = $code;
        $log->user_id = auth()->id();
        $log->date = $saleOrder->date;
        $log->save();
    }

    public function printInvoice(SaleOrder $emi_sale)
    {
        if ($emi_sale->type != SaleType::$EMI) abort(404);
        $emi_sale->load('saleOrderItems.product.unit', 'saleOrderItems.inventory', 'customer', 'user');
        
        $loanAccount = \App\Models\ShomriddhiLoan\LoanAccount::where('sale_order_id', $emi_sale->id)->with('installments')->first();

        // Group items for Customer Copy
        $groupedItems = $emi_sale->saleOrderItems->groupBy(function($item) {
            return $item->product_id . '-' . $item->selling_price;
        })->map(function ($items) {
            $firstItem = $items->first();
            return (object) [
                'product' => $firstItem->product,
                'product_code' => $firstItem->product_code,
                'total_quantity' => $items->sum('quantity'),
                'selling_price' => $firstItem->selling_price,
                'total_price' => $items->sum(function($item) {
                    return $item->quantity * $item->selling_price;
                }),
                'batches' => $items->map(function($item) {
                    return 'Batch-'.$item->inventory->batch_no;
                })->unique()->implode(', '),
            ];
        });

        return view('shomriddhi_loan.product_loans.print', compact('emi_sale', 'groupedItems', 'loanAccount'));
    }
}

