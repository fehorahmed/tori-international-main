<?php

namespace App\Http\Controllers\Purchase;

use App\Enumeration\ReservedAccountGroup;
 use App\Enumeration\ReservedAccountHead;
use App\Enumeration\SupplierType;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Purchase\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Division;
use App\Models\District;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class SupplierController extends Controller
{
    use UploadTrait;

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        return view('purchase.supplier.index', compact('divisions'));
    }
    public function advance()
    {
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)->get();

        return view('purchase.supplier.advance',compact('paymentModes'));
    }
    public function dataTable(Request $request)
    {
        $query = Supplier::with('division', 'district', 'upazila', 'union', 'pourashava')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())->where('type',SupplierType::$SUPPLIER);

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

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('name', function(Supplier $supplier) {
                return '<a href="'.route('supplier.show', ['supplier' => $supplier->id]).'">'.$supplier->name.'</a>';
            })
            ->addColumn('address', function(Supplier $supplier) {
                $address = '';
                if ($supplier->village) $address .= $supplier->village . ', ';
                if ($supplier->union) $address .= $supplier->union->name . ' (U), ';
                if ($supplier->pourashava) $address .= $supplier->pourashava->name . ' (P), ';
                if ($supplier->upazila) $address .= $supplier->upazila->name . ', ';
                if ($supplier->district) $address .= $supplier->district->name . ', ';
                if ($supplier->division) $address .= $supplier->division->name;
                
                return '<button class="btn btn-info btn-sm btn-view-address" data-id="'.$supplier->id.'">View Address</button>';
            })
            ->addColumn('action', function(Supplier $supplier) {
                $btn = '';
                if(auth()->user()->can('supplier')){
                    $btn  .='<a href="'.route('supplier.show',['supplier'=>$supplier->id]).'" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-eye"></i></a>';
                }
                
                $hasEdit = auth()->user()->can('supplier_edit');
                $hasDelete = auth()->user()->can('supplier_delete');
                $isOrigin = auth()->user()->activeConcernId() == $supplier->origin_sister_concern_id;

                if ($hasEdit || $hasDelete) {
                    if ($isOrigin) {
                        if ($hasEdit) {
                            $btn .= ' <a href="' . route('supplier.edit', ['supplier' => $supplier->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                        }
                        if ($hasDelete) {
                            $btn .= ' <a role="button" data-id="' . $supplier->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                        }
                    } else {
                        $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Created by another branch"><i class="fa fa-lock"></i></button>';
                    }
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('advance_action', function(Supplier $supplier) {
                $btn = '';
                if(auth()->user()->can('supplier_payment')) {
                    $btn .= ' <a role="button" data-total="'.$supplier->advance_payment.'" data-paid="'.$supplier->advance_settled.'" data-due="'.$supplier->outstanding_balance.'" data-id="' . $supplier->id . '" data-name="'.$supplier->name.'" class="btn-info bg-gradient-info btn-sm btn-advance-supplier">Payment</a>';
                }
                return $btn;
            })
            ->rawColumns(['action','advance_action', 'address', 'name'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('supplier_create')) {
            abort(403, 'Unauthorized');
        }
        $divisions = Division::orderBy('name')->get();
        return view('purchase.supplier.create', compact('divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('supplier_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('suppliers')
                    ->where('type', SupplierType::$SUPPLIER)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('suppliers')
                    ->where('type', SupplierType::$SUPPLIER)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
                    ->where('type', SupplierType::$SUPPLIER)
            ],
            'address' => 'nullable|max:255',
            'mobile_banking_no' => 'nullable|max:255',
            'bank_name' => 'nullable|max:255',
            'branch_name' => 'nullable|max:255',
            'bank_account_no' => 'nullable|max:255',
            'routing_no' => 'nullable|max:255',
            'balance_type' => 'required|in:1,2',
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'division' => 'nullable',
            'district' => 'nullable',
            'upazila' => 'nullable',
            'union' => 'nullable',
            'village_id' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$SUPPLIER;
            $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$SUPPLIER);
            $validatedData['division_id'] = $request->division;
            $validatedData['district_id'] = $request->district;
            $validatedData['upazila_id'] = $request->upazila;
            if ($request->union) {
                if (strpos($request->union, 'union_') !== false) {
                    $validatedData['union_id'] = str_replace('union_', '', $request->union);
                } elseif (strpos($request->union, 'pourashava_') !== false) {
                    $validatedData['pourashava_id'] = str_replace('pourashava_', '', $request->union);
                }
            }
            $validatedData['village_id'] = $request->village_id;

            if ($request->hasFile('image')) {
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'supplier');
            }

            // Remove extra keys that are not in the table
            unset($validatedData['division']);
            unset($validatedData['district']);
            unset($validatedData['upazila']);
            unset($validatedData['union']);
            $openingBalance = $validatedData['opening_balance'] ?? 0;
            $balanceType = $validatedData['balance_type'];
            
            unset($validatedData['balance_type']);
            unset($validatedData['opening_balance']);

            $supplier = Supplier::create($validatedData);

            $payablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_PAYABLE)
                ->first();

            // Create a new Account Head record in the database
            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id',$supplier->id)->first();
            if (!$accountHead){
                $code = generateAccountCode($payablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $payablesGroup->id;
            $accountHead->supplier_id = $supplier->id;
            $accountHead->name = $request->name.'(Supplier)';

            $accountHead->nature = 'Credit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->is_editable = 0;
            $accountHead->save();

            // --- Opening Balance Journal Entry ---
            if ($openingBalance > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
                
                $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $voucher->amount = $openingBalance;
                $voucher->date = $today;
                $voucher->voucher_no = $voucherNo;
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->account_head_payee_depositor_id = $accountHead->id;
                $voucher->notes = 'Opening Balance - ' . $request->name . ' (Supplier)';
                $voucher->is_opening_balance = 1;
                $voucher->user_id = auth()->id();
                $voucher->save();

                if ($balanceType == 1) { // Due (Payable)
                    // Debit: Opening Balance Equity
                    $txDebit = new Transaction();
                    $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                    $txDebit->voucher_id = $voucher->id;
                    $txDebit->account_head_id = $obHeadId;
                    $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txDebit->transaction_type = TransactionType::$DEBIT;
                    $txDebit->amount = $openingBalance;
                    $txDebit->date = $today;
                    $txDebit->voucher_no = $voucherNo;
                    $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                    $txDebit->account_head_payee_depositor_id = $accountHead->id;
                    $txDebit->notes = 'Opening Balance — ' . $request->name . ' (Supplier)';
                    $txDebit->is_opening_balance = 1;
                    $txDebit->user_id = auth()->id();
                    $txDebit->save();

                    // Credit: Supplier Account Head
                    $txCredit = new Transaction();
                    $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                    $txCredit->voucher_id = $voucher->id;
                    $txCredit->account_head_id = $accountHead->id;
                    $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txCredit->transaction_type = TransactionType::$CREDIT;
                    $txCredit->amount = $openingBalance;
                    $txCredit->date = $today;
                    $txCredit->voucher_no = $voucherNo;
                    $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                    $txCredit->account_head_payee_depositor_id = $accountHead->id;
                    $txCredit->notes = 'Opening Balance — ' . $request->name . ' (Supplier)';
                    $txCredit->is_opening_balance = 1;
                    $txCredit->user_id = auth()->id();
                    $txCredit->save();
                } else { // Advance (Receivable)
                    // Debit: Supplier Account Head
                    $txDebit = new Transaction();
                    $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                    $txDebit->voucher_id = $voucher->id;
                    $txDebit->account_head_id = $accountHead->id;
                    $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txDebit->transaction_type = TransactionType::$DEBIT;
                    $txDebit->amount = $openingBalance;
                    $txDebit->date = $today;
                    $txDebit->voucher_no = $voucherNo;
                    $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                    $txDebit->account_head_payee_depositor_id = $accountHead->id;
                    $txDebit->notes = 'Opening Balance (Advance) — ' . $request->name . ' (Supplier)';
                    $txDebit->is_opening_balance = 1;
                    $txDebit->user_id = auth()->id();
                    $txDebit->save();

                    // Credit: Opening Balance Equity
                    $txCredit = new Transaction();
                    $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                    $txCredit->voucher_id = $voucher->id;
                    $txCredit->account_head_id = $obHeadId;
                    $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txCredit->transaction_type = TransactionType::$CREDIT;
                    $txCredit->amount = $openingBalance;
                    $txCredit->date = $today;
                    $txCredit->voucher_no = $voucherNo;
                    $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                    $txCredit->account_head_payee_depositor_id = $accountHead->id;
                    $txCredit->notes = 'Opening Balance (Advance) — ' . $request->name . ' (Supplier)';
                    $txCredit->is_opening_balance = 1;
                    $txCredit->user_id = auth()->id();
                    $txCredit->save();
                }
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('supplier.index')->with('success', 'Supplier created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('supplier.create')->with('error', 'An error occurred while creating the Supplier: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Supplier $supplier)
    {
        if (!auth()->user()->hasPermissionTo('supplier')) {
            abort(403, 'Unauthorized');
        }
        $supplier->load('division', 'district', 'upazila', 'union', 'pourashava');
        return view('purchase.supplier.show', compact('supplier'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $supplier)
    {
        if (!auth()->user()->hasPermissionTo('supplier_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($supplier->type != SupplierType::$SUPPLIER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $supplier->origin_sister_concern_id) {
            abort(403, 'You can only edit suppliers created by your branch.');
        }
        try {
            $divisions = Division::orderBy('name')->get();
            $districts = District::where('division_id', $supplier->division_id)->get();
            
            // If the Supplier exists, display the edit view
            return view('purchase.supplier.edit', compact('supplier', 'divisions', 'districts'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Supplier is not found
            return redirect()->route('supplier.index')->with('error', 'Supplier not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $supplier)
    {
        if (!auth()->user()->hasPermissionTo('supplier_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($supplier->type != SupplierType::$SUPPLIER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $supplier->origin_sister_concern_id) {
            abort(403, 'You can only edit suppliers created by your branch.');
        }
        // Validate the request data
        $validatedData = $request->validate([
            'name' =>[
                'required','max:255',
                Rule::unique('suppliers')
                    ->where('type', SupplierType::$SUPPLIER)
                    ->ignore($supplier)
            ],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('suppliers')
                    ->where('type', SupplierType::$SUPPLIER)
                    ->ignore($supplier)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
                    ->where('type', SupplierType::$SUPPLIER)
                    ->ignore($supplier)
            ],
            'address' => 'nullable|string|max:255',
            'mobile_banking_no' => 'nullable|max:255',
            'bank_name' => 'nullable|max:255',
            'branch_name' => 'nullable|max:255',
            'bank_account_no' => 'nullable|max:255',
            'routing_no' => 'nullable|max:255',
            'balance_type' => 'required|in:1,2',
            'opening_balance' => 'required|numeric|min:0',
            'status' => 'required|boolean',
            'division' => 'nullable',
            'district' => 'nullable',
            'upazila' => 'nullable',
            'union' => 'nullable',
            'village_id' => 'nullable',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$SUPPLIER;
            if ($supplier->member_no == null){
                $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$SUPPLIER);
            }

            $validatedData['division_id'] = $request->division;
            $validatedData['district_id'] = $request->district;
            $validatedData['upazila_id'] = $request->upazila;
            if ($request->union) {
                if (strpos($request->union, 'union_') !== false) {
                    $validatedData['union_id'] = str_replace('union_', '', $request->union);
                    $validatedData['pourashava_id'] = null;
                } elseif (strpos($request->union, 'pourashava_') !== false) {
                    $validatedData['pourashava_id'] = str_replace('pourashava_', '', $request->union);
                    $validatedData['union_id'] = null;
                }
            } else {
                $validatedData['union_id'] = null;
                $validatedData['pourashava_id'] = null;
            }
            $validatedData['village_id'] = $request->village_id;

            if ($request->hasFile('image')) {
                $this->deleteFile($supplier->image);
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'supplier');
            }

            // Remove extra keys that are not in the table
            unset($validatedData['division']);
            unset($validatedData['district']);
            unset($validatedData['upazila']);
            unset($validatedData['union']);
            // Handle balance type
            $openingBalance = $validatedData['opening_balance'] ?? 0;
            $balanceType = $validatedData['balance_type'];
            
            unset($validatedData['balance_type']);
            unset($validatedData['opening_balance']);

            $supplier->update($validatedData);

            $payablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_PAYABLE)
                ->first();

            // Create a new Account Payable Head record in the database
            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id',$supplier->id)->first();
            if (!$accountHead){
                $code = generateAccountCode($payablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $payablesGroup->id;
            $accountHead->supplier_id = $supplier->id;
            $accountHead->name = $request->name.'(Supplier)';

            $accountHead->nature = 'Credit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->save();

            // --- Opening Balance Journal Entry (update or create) ---
            $existingObVoucher = Voucher::where('account_head_payee_depositor_id', $accountHead->id)
                ->where('is_opening_balance', 1)
                ->first();

            if ($request->opening_balance > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? \Carbon\Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : \Carbon\Carbon::now()->subDay()->format('Y-m-d');

                if ($existingObVoucher) {
                    // Update existing voucher
                    $existingObVoucher->amount = $request->opening_balance;
                    $existingObVoucher->notes = 'Opening Balance - ' . $request->name . ' (Supplier)';
                    $existingObVoucher->save();

                    // Delete existing transactions and recreate them based on type
                    Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                    
                    if ($balanceType == 1) { // Due
                        Transaction::create([
                            'sister_concern_id' => auth()->user()->activeConcernId(),
                            'voucher_id' => $existingObVoucher->id,
                            'account_head_id' => $obHeadId,
                            'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                            'transaction_type' => TransactionType::$DEBIT,
                            'amount' => $request->opening_balance,
                            'date' => $today,
                            'voucher_no' => $existingObVoucher->voucher_no,
                            'voucher_no_group_sl' => $existingObVoucher->voucher_no_group_sl,
                            'account_head_payee_depositor_id' => $accountHead->id,
                            'notes' => 'Opening Balance - ' . $request->name . ' (Supplier)',
                            'is_opening_balance' => 1,
                            'user_id' => auth()->id()
                        ]);

                        Transaction::create([
                            'sister_concern_id' => auth()->user()->activeConcernId(),
                            'voucher_id' => $existingObVoucher->id,
                            'account_head_id' => $accountHead->id,
                            'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                            'transaction_type' => TransactionType::$CREDIT,
                            'amount' => $request->opening_balance,
                            'date' => $today,
                            'voucher_no' => $existingObVoucher->voucher_no,
                            'voucher_no_group_sl' => $existingObVoucher->voucher_no_group_sl,
                            'account_head_payee_depositor_id' => $accountHead->id,
                            'notes' => 'Opening Balance - ' . $request->name . ' (Supplier)',
                            'is_opening_balance' => 1,
                            'user_id' => auth()->id()
                        ]);
                    } else { // Advance
                        Transaction::create([
                            'sister_concern_id' => auth()->user()->activeConcernId(),
                            'voucher_id' => $existingObVoucher->id,
                            'account_head_id' => $accountHead->id,
                            'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                            'transaction_type' => TransactionType::$DEBIT,
                            'amount' => $request->opening_balance,
                            'date' => $today,
                            'voucher_no' => $existingObVoucher->voucher_no,
                            'voucher_no_group_sl' => $existingObVoucher->voucher_no_group_sl,
                            'account_head_payee_depositor_id' => $accountHead->id,
                            'notes' => 'Opening Balance (Advance) - ' . $request->name . ' (Supplier)',
                            'is_opening_balance' => 1,
                            'user_id' => auth()->id()
                        ]);

                        Transaction::create([
                            'sister_concern_id' => auth()->user()->activeConcernId(),
                            'voucher_id' => $existingObVoucher->id,
                            'account_head_id' => $obHeadId,
                            'voucher_type' => VoucherType::$JOURNAL_VOUCHER,
                            'transaction_type' => TransactionType::$CREDIT,
                            'amount' => $request->opening_balance,
                            'date' => $today,
                            'voucher_no' => $existingObVoucher->voucher_no,
                            'voucher_no_group_sl' => $existingObVoucher->voucher_no_group_sl,
                            'account_head_payee_depositor_id' => $accountHead->id,
                            'notes' => 'Opening Balance (Advance) - ' . $request->name . ' (Supplier)',
                            'is_opening_balance' => 1,
                            'user_id' => auth()->id()
                        ]);
                    }
                } else {
                    // Create new voucher
                    $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                    $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                    $voucher = new Voucher();
                    $voucher->sister_concern_id = auth()->user()->activeConcernId();
                    $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $voucher->amount = $request->opening_balance;
                    $voucher->date = $today;
                    $voucher->voucher_no = $voucherNo;
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->account_head_payee_depositor_id = $accountHead->id;
                    $voucher->notes = 'Opening Balance - ' . $request->name . ' (Supplier)';
                    $voucher->is_opening_balance = 1;
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    if ($balanceType == 1) { // Due
                        // Debit: Opening Balance Equity
                        $txDebit = new Transaction();
                        $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                        $txDebit->voucher_id = $voucher->id;
                        $txDebit->account_head_id = $obHeadId;
                        $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $txDebit->transaction_type = TransactionType::$DEBIT;
                        $txDebit->amount = $request->opening_balance;
                        $txDebit->date = $today;
                        $txDebit->voucher_no = $voucherNo;
                        $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                        $txDebit->account_head_payee_depositor_id = $accountHead->id;
                        $txDebit->notes = 'Opening Balance - ' . $request->name . ' (Supplier)';
                        $txDebit->is_opening_balance = 1;
                        $txDebit->user_id = auth()->id();
                        $txDebit->save();

                        // Credit: Supplier Account Head
                        $txCredit = new Transaction();
                        $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                        $txCredit->voucher_id = $voucher->id;
                        $txCredit->account_head_id = $accountHead->id;
                        $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $txCredit->transaction_type = TransactionType::$CREDIT;
                        $txCredit->amount = $request->opening_balance;
                        $txCredit->date = $today;
                        $txCredit->voucher_no = $voucherNo;
                        $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                        $txCredit->account_head_payee_depositor_id = $accountHead->id;
                        $txCredit->notes = 'Opening Balance - ' . $request->name . ' (Supplier)';
                        $txCredit->is_opening_balance = 1;
                        $txCredit->user_id = auth()->id();
                        $txCredit->save();
                    } else { // Advance
                        // Debit: Supplier Account Head
                        $txDebit = new Transaction();
                        $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                        $txDebit->voucher_id = $voucher->id;
                        $txDebit->account_head_id = $accountHead->id;
                        $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $txDebit->transaction_type = TransactionType::$DEBIT;
                        $txDebit->amount = $request->opening_balance;
                        $txDebit->date = $today;
                        $txDebit->voucher_no = $voucherNo;
                        $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                        $txDebit->account_head_payee_depositor_id = $accountHead->id;
                        $txDebit->notes = 'Opening Balance (Advance) - ' . $request->name . ' (Supplier)';
                        $txDebit->is_opening_balance = 1;
                        $txDebit->user_id = auth()->id();
                        $txDebit->save();

                        // Credit: Opening Balance Equity
                        $txCredit = new Transaction();
                        $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                        $txCredit->voucher_id = $voucher->id;
                        $txCredit->account_head_id = $obHeadId;
                        $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $txCredit->transaction_type = TransactionType::$CREDIT;
                        $txCredit->amount = $request->opening_balance;
                        $txCredit->date = $today;
                        $txCredit->voucher_no = $voucherNo;
                        $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                        $txCredit->account_head_payee_depositor_id = $accountHead->id;
                        $txCredit->notes = 'Opening Balance (Advance) - ' . $request->name . ' (Supplier)';
                        $txCredit->is_opening_balance = 1;
                        $txCredit->user_id = auth()->id();
                        $txCredit->save();
                    }
                }
            } elseif ($existingObVoucher) {
                // opening_balance = 0 হলে existing OB entry মুছে দাও
                Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                $existingObVoucher->delete();
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('supplier.index')->with('success', 'Supplier updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('supplier.edit',['supplier'=>$supplier->id])->with('error', 'An error occurred while updating the Supplier: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        DB::beginTransaction();
        try {
            if (!auth()->user()->hasPermissionTo('supplier_delete')) {
                abort(403, 'Unauthorized');
            }

            if (auth()->user()->activeConcernId() != $supplier->origin_sister_concern_id) {
                return response()->json(['success' => false, 'message' => 'You can only delete suppliers created by your branch.'], Response::HTTP_OK);
            }
            
            // Check for activity
            if (PurchaseOrder::where('sister_concern_id', auth()->user()->activeConcernId())->where('supplier_id', $supplier->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Supplier has purchase orders. Cannot delete.'], Response::HTTP_OK);
            }

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $supplier->id)->first();
            if ($accountHead && Transaction::where('sister_concern_id', auth()->user()->activeConcernId())->where('account_head_id', $accountHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Supplier has financial transactions. Cannot delete.'], Response::HTTP_OK);
            }

            // Delete the Supplier record
            if ($accountHead) {
                $accountHead->delete();
            }
            $supplier->delete();
            DB::commit();

            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Supplier deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollback();
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Error deleting supplier: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
