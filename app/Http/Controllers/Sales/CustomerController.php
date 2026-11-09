<?php

namespace App\Http\Controllers\Sales;

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
use App\Models\Sales\SaleOrder;
use App\Models\Supplier;
use App\Models\Division;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    use UploadTrait;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        return view('sales.customer.index', compact('divisions'));
    }

    public function printEmptyForm()
    {
        if (!auth()->user()->hasPermissionTo('customer_create')) {
            abort(403, 'Unauthorized');
        }
        $pageTitle = 'Customer Data Collection Form (Blank)';
        return view('sales.customer.print_empty_form', compact('pageTitle'));
    }
    public function advance()
    {
        $paymentModes = AccountHead::where(function($q) { $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId()); })->where('payment_mode', '>', 0)
            ->get();
        return view('sales.customer.advance',compact('paymentModes'));
    }
    public function dataTable(Request $request)
    {
        $query = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SupplierType::$CUSTOMER)
            ->with('division', 'district', 'upazila', 'union')
            ->orderBy('id', 'desc');

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
            ->addColumn('action', function(Supplier $supplier) {
                $btn = '';
                $btn .= '<a href="' . route('customer.show', ['customer' => $supplier->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-eye"></i></a>';
                
                $hasEdit = auth()->user()->can('customer_edit');
                $hasDelete = auth()->user()->can('customer_delete');
                $isOrigin = auth()->user()->activeConcernId() == $supplier->origin_sister_concern_id;

                if ($hasEdit || $hasDelete) {
                    if ($isOrigin) {
                        if ($hasEdit) {
                            $btn .= ' <a href="' . route('customer.edit', ['customer' => $supplier->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                        }
                        if ($hasDelete) {
                            if ($supplier->hasShomriddhiAccount()) {
                                $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Shomriddhi Account exists"><i class="fa fa-lock"></i></button>';
                            } else {
                                $btn .= ' <a role="button" data-id="' . $supplier->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                            }
                        }
                    } else {
                        $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Created by another branch"><i class="fa fa-lock"></i></button>';
                    }
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('customer_image', function(Supplier $supplier) {
                return renderZoomGalleryImage($supplier->image, $supplier->name);
            })
            ->addColumn('address', function(Supplier $supplier) {
                return '<button class="btn btn-info btn-sm btn-view-address" data-id="'.$supplier->id.'">View Address</button>';
            })
            ->rawColumns(['action','address','customer_image'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!auth()->user()->hasPermissionTo('customer_create')) {
            abort(403, 'Unauthorized');
        }
        $divisions = Division::orderBy('name')->get();
        return view('sales.customer.create', compact('divisions'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('customer_create')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'father_name' =>['nullable','max:255'],
            'mother_name' =>['nullable','max:255'],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('suppliers')
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
            ],
            'balance_type' => 'required|in:1,2',
            'opening_balance' => 'required|numeric|min:0',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'union' => 'nullable',
            'village_id' => 'nullable|integer',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$CUSTOMER;
            $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER);
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
            $validatedData['password'] = Hash::make($request->mobile_no);

            if ($request->hasFile('image')) {
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'customer');
            }

            unset($validatedData['division'], $validatedData['district'], $validatedData['upazila'], $validatedData['union']);
            unset($validatedData['opening_balance'], $validatedData['balance_type']);
            $customer = Supplier::create($validatedData);

            // Create a new Account Head record in the database
            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)->first();

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id',$customer->id)->first();
            if (!$accountHead){
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $receivablesGroup->id;
            $accountHead->supplier_id = $customer->id;
            $accountHead->name = $request->name.'(Client)';

            $accountHead->nature = 'Debit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->is_editable = 0;
            $accountHead->save();

            // --- Opening Balance Journal Entry ---
            if ($request->opening_balance > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
                
                $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $voucher->amount = $request->opening_balance;
                $voucher->date = $today;
                $voucher->voucher_no = $voucherNo;
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->account_head_payee_depositor_id = $accountHead->id;
                
                if ($request->balance_type == '1') {
                    $voucher->notes = 'Opening Balance (Due) - ' . $request->name . ' (Client)';
                } else {
                    $voucher->notes = 'Opening Balance (Advance) - ' . $request->name . ' (Client)';
                }
                
                $voucher->is_opening_balance = 1;
                $voucher->user_id = auth()->id();
                $voucher->save();

                if ($request->balance_type == '1') {
                    // Due: Debit Customer, Credit OB Equity
                    $debitHeadId = $accountHead->id;
                    $creditHeadId = $obHeadId;
                } else {
                    // Advance: Debit OB Equity, Credit Customer
                    $debitHeadId = $obHeadId;
                    $creditHeadId = $accountHead->id;
                }

                // Debit Transaction
                $txDebit = new Transaction();
                $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                $txDebit->voucher_id = $voucher->id;
                $txDebit->account_head_id = $debitHeadId;
                $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $txDebit->transaction_type = TransactionType::$DEBIT;
                $txDebit->amount = $request->opening_balance;
                $txDebit->date = $today;
                $txDebit->voucher_no = $voucherNo;
                $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                $txDebit->account_head_payee_depositor_id = $accountHead->id;
                $txDebit->notes = $voucher->notes;
                $txDebit->is_opening_balance = 1;
                $txDebit->user_id = auth()->id();
                $txDebit->save();

                // Credit Transaction
                $txCredit = new Transaction();
                $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                $txCredit->voucher_id = $voucher->id;
                $txCredit->account_head_id = $creditHeadId;
                $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $txCredit->transaction_type = TransactionType::$CREDIT;
                $txCredit->amount = $request->opening_balance;
                $txCredit->date = $today;
                $txCredit->voucher_no = $voucherNo;
                $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                $txCredit->account_head_payee_depositor_id = $accountHead->id;
                $txCredit->notes = $voucher->notes;
                $txCredit->is_opening_balance = 1;
                $txCredit->user_id = auth()->id();
                $txCredit->save();
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('customer.index')->with('success', 'Client created successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('customer.create')
                ->withInput()
                ->with('error', 'An error occurred while creating the Customer: '.$e->getMessage());
        }
    }
    public function storeDirect(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('pos')) {
            abort(403, 'Unauthorized');
        }

        // Validate the request data
        $validatedData = $request->validate([
            'customer_name' =>[
                'required','max:255'
            ],
            'mobile_no' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
                    ->where('showroom_id',auth()->user()->showroom_id)
                    ->where('type', SupplierType::$CUSTOMER)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
                    ->where('showroom_id',auth()->user()->showroom_id)
                    ->where('type', SupplierType::$CUSTOMER)
            ],
            'address' => 'nullable|string|max:255', // Make 'address' nullable
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Create a new Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$CUSTOMER;
            $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER);
            $validatedData['status'] = 1;
            $validatedData['opening_balance'] = 0;
            $validatedData['name'] = $validatedData['customer_name'];
            $validatedData['password'] = Hash::make($request->mobile_no);
            unset($validatedData['customer_name']);
            $customer = Supplier::create($validatedData);

            $customer_info = $customer->name;
            if ($customer->mobile_no != ''){
                $customer_info .= ' - '.$customer->mobile_no;
            }
            $customer->customer_info = $customer_info;

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return response()->json([
                'status'=>true,
                'customer'=>$customer,
                'message'=>'Customer created successfully',
            ]);
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            return response()->json([
                'status'=>false,
                'message'=>$e->getMessage(),
            ]);
        }
    }

    public function show(Supplier $customer)
    {
        if ($customer->type != SupplierType::$CUSTOMER) {
            abort(404);
        }
        $customer->load('division', 'district', 'upazila', 'union', 'pourashava');
        return view('sales.customer.show', compact('customer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Supplier $customer)
    {
        if (!auth()->user()->hasPermissionTo('customer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($customer->type != SupplierType::$CUSTOMER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $customer->origin_sister_concern_id) {
            abort(403, 'You can only edit clients created by your branch.');
        }

        try {
            // If the Supplier exists, display the edit view
            $divisions = Division::orderBy('name')->get();
            return view('sales.customer.edit', compact('customer', 'divisions'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Supplier is not found
            return redirect()->route('customer.index')->with('error', 'Client not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Supplier $customer)
    {
        if (!auth()->user()->hasPermissionTo('customer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($customer->type != SupplierType::$CUSTOMER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $customer->origin_sister_concern_id) {
            abort(403, 'You can only edit clients created by your branch.');
        }
        // if ($customer->hasShomriddhiAccount()) {
        //     abort(403, 'This member has a Shomriddhi Account and cannot be edited.');
        // }
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'father_name' =>['nullable','max:255'],
            'mother_name' =>['nullable','max:255'],
            'mobile_no' =>[
                'required','max:255',
                Rule::unique('suppliers')
                    ->ignore($customer)
            ],
            'email' =>[
                'nullable','max:255',
                Rule::unique('suppliers')
                    ->ignore($customer)
            ],
            'balance_type' => 'required|in:1,2',
            'opening_balance' => 'required|numeric|min:0',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'union' => 'nullable',
            'village_id' => 'nullable|integer',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Start a database transaction
        DB::beginTransaction();

        try {
            // Update the Supplier record in the database
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$CUSTOMER;
            if ($customer->member_no == null){
                $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$CUSTOMER);
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
                $this->deleteFile($customer->image);
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'customer');
            }

            if (empty($customer->password)) {
                $validatedData['password'] = Hash::make($request->mobile_no);
            }

            unset($validatedData['division'], $validatedData['district'], $validatedData['upazila'], $validatedData['union']);
            unset($validatedData['opening_balance'], $validatedData['balance_type']);

            $customer->update($validatedData);

            // Create/update Account Head record
            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)
                ->first();

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $customer->id)->first();
            if (!$accountHead) {
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $receivablesGroup->id;
            $accountHead->supplier_id = $customer->id;
            $accountHead->name = $request->name.'(Client)';

            $accountHead->nature = 'Debit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->is_editable = 0;
            $accountHead->save();

            // --- Opening Balance Journal Entry (update or create) ---
            $existingObVoucher = Voucher::where('account_head_payee_depositor_id', $accountHead->id)
                ->where('is_opening_balance', 1)
                ->first();

            if ($request->opening_balance > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? \Carbon\Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : \Carbon\Carbon::now()->subDay()->format('Y-m-d');

                $voucherNotes = ($request->balance_type == '1') 
                    ? 'Opening Balance (Due) - ' . $request->name . ' (Client)' 
                    : 'Opening Balance (Advance) - ' . $request->name . ' (Client)';

                if ($request->balance_type == '1') {
                    $debitHeadId = $accountHead->id;
                    $creditHeadId = $obHeadId;
                } else {
                    $debitHeadId = $obHeadId;
                    $creditHeadId = $accountHead->id;
                }

                if ($existingObVoucher) {
                    $existingObVoucher->amount = $request->opening_balance;
                    $existingObVoucher->notes = $voucherNotes;
                    $existingObVoucher->save();
                    
                    // Since the balance type could have changed (due to advance or vice versa), it's safer to delete old txs and recreate them
                    Transaction::where('voucher_id', $existingObVoucher->id)->delete();

                    // Recreate Debit Transaction
                    $txDebit = new Transaction();
                    $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                    $txDebit->voucher_id = $existingObVoucher->id;
                    $txDebit->account_head_id = $debitHeadId;
                    $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txDebit->transaction_type = TransactionType::$DEBIT;
                    $txDebit->amount = $request->opening_balance;
                    $txDebit->date = $today;
                    $txDebit->voucher_no = $existingObVoucher->voucher_no;
                    $txDebit->voucher_no_group_sl = $existingObVoucher->voucher_no_group_sl;
                    $txDebit->account_head_payee_depositor_id = $accountHead->id;
                    $txDebit->notes = $voucherNotes;
                    $txDebit->is_opening_balance = 1;
                    $txDebit->user_id = auth()->id();
                    $txDebit->save();

                    // Recreate Credit Transaction
                    $txCredit = new Transaction();
                    $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                    $txCredit->voucher_id = $existingObVoucher->id;
                    $txCredit->account_head_id = $creditHeadId;
                    $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txCredit->transaction_type = TransactionType::$CREDIT;
                    $txCredit->amount = $request->opening_balance;
                    $txCredit->date = $today;
                    $txCredit->voucher_no = $existingObVoucher->voucher_no;
                    $txCredit->voucher_no_group_sl = $existingObVoucher->voucher_no_group_sl;
                    $txCredit->account_head_payee_depositor_id = $accountHead->id;
                    $txCredit->notes = $voucherNotes;
                    $txCredit->is_opening_balance = 1;
                    $txCredit->user_id = auth()->id();
                    $txCredit->save();

                } else {
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
                    $voucher->notes = $voucherNotes;
                    $voucher->is_opening_balance = 1;
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    $txDebit = new Transaction();
                    $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                    $txDebit->voucher_id = $voucher->id;
                    $txDebit->account_head_id = $debitHeadId;
                    $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txDebit->transaction_type = TransactionType::$DEBIT;
                    $txDebit->amount = $request->opening_balance;
                    $txDebit->date = $today;
                    $txDebit->voucher_no = $voucherNo;
                    $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                    $txDebit->account_head_payee_depositor_id = $accountHead->id;
                    $txDebit->notes = $voucherNotes;
                    $txDebit->is_opening_balance = 1;
                    $txDebit->user_id = auth()->id();
                    $txDebit->save();

                    $txCredit = new Transaction();
                    $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                    $txCredit->voucher_id = $voucher->id;
                    $txCredit->account_head_id = $creditHeadId;
                    $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $txCredit->transaction_type = TransactionType::$CREDIT;
                    $txCredit->amount = $request->opening_balance;
                    $txCredit->date = $today;
                    $txCredit->voucher_no = $voucherNo;
                    $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                    $txCredit->account_head_payee_depositor_id = $accountHead->id;
                    $txCredit->notes = $voucherNotes;
                    $txCredit->is_opening_balance = 1;
                    $txCredit->user_id = auth()->id();
                    $txCredit->save();
                }
            } elseif ($existingObVoucher) {
                Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                $existingObVoucher->delete();
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('customer.index')->with('success', 'Client updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('customer.edit', ['customer' => $customer->id])
                ->withInput()
                ->with('error', 'An error occurred while updating the Customer: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $customer)
    {
        try {
            if (!auth()->user()->hasPermissionTo('customer_delete')) {
                abort(403, 'Unauthorized');
            }
            if (auth()->user()->activeConcernId() != $customer->origin_sister_concern_id) {
                return response()->json(['success' => false, 'message' => 'You can only delete clients created by your branch.'], Response::HTTP_OK);
            }
            
            if ($customer->hasShomriddhiAccount()) {
                return response()->json(['success' => false, 'message' => 'Has Shomriddhi Account. Cannot delete.'], Response::HTTP_OK);
            }
            
            // Check for Sales
            if (SaleOrder::where('customer_id', $customer->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has sales records. Cannot delete.'], Response::HTTP_OK);
            }

            // Check for Accounting Transactions
            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $customer->id)->first();
            if ($accountHead && Transaction::where('account_head_id', $accountHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has accounting transactions. Cannot delete.'], Response::HTTP_OK);
            }

            $accountSavingsHead = AccountHead::where('head_type', 'savings')->where('supplier_id', $customer->id)->first();
            if ($accountSavingsHead && Transaction::where('account_head_id', $accountSavingsHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has savings transactions. Cannot delete.'], Response::HTTP_OK);
            }

            DB::beginTransaction();
            if ($accountHead) {
                AccountHead::where('supplier_id', $customer->id)->delete();
            }
            $customer->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Client deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }
}
