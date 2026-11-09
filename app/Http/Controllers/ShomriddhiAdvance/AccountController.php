<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use Yajra\DataTables\Facades\DataTables;

use App\Models\Division;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Account\AccountGroup;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Supplier;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\SupplierType;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\PaymentPurpose;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Models\Nominee;
use App\Models\ShomriddhiAdvance\ShomriddhiNominee;
use App\Traits\UploadTrait;

class AccountController extends Controller
{
    use UploadTrait;
    public function index()
    {
        $pageTitle = 'Shomriddhi Accounts List';
        $divisions = Division::orderBy('name')->get();
        return view('shomriddhi_advance.accounts.index', compact('pageTitle', 'divisions'));
    }

    public function dataTable(Request $request)
    {
        $accounts = ShomriddhiAccount::with('customer')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($request->division_id || $request->district_id || $request->upazila_id || $request->union_id || $request->village_id) {
            $accounts->whereHas('customer', function($q) use ($request) {
                if ($request->division_id) {
                    $q->where('division_id', $request->division_id);
                }
                if ($request->district_id) {
                    $q->where('district_id', $request->district_id);
                }
                if ($request->upazila_id) {
                    $q->where('upazila_id', $request->upazila_id);
                }
                if ($request->union_id) {
                    if (strpos($request->union_id, 'union_') !== false) {
                        $q->where('union_id', str_replace('union_', '', $request->union_id));
                    } elseif (strpos($request->union_id, 'pourashava_') !== false) {
                        $q->where('pourashava_id', str_replace('pourashava_', '', $request->union_id));
                    }
                }
                if ($request->village_id) {
                    $q->where('village_id', $request->village_id);
                }
            });
        }

        $accounts->orderBy('id', 'desc');

        return DataTables::of($accounts)
            ->addIndexColumn()
            ->addColumn('image', function ($account) {
                $imagePath = ($account->customer && $account->customer->image) ? $account->customer->image : 'img/avatar.png';
                return renderZoomGalleryImage($imagePath, $account->customer->name ?? 'Default');
            })
            ->addColumn('member_no', function ($account) {
                return $account->customer->member_no ?? '-';
            })
            ->addColumn('account_number', function ($account) {
                return $account->account_number ?? '-';
            })
            ->addColumn('customer_name', function ($account) {
                return $account->customer ? $account->customer->name : '-';
            })
            ->editColumn('principal_balance', function ($account) {
                return number_format($account->principal_balance, 2);
            })
            ->editColumn('accrued_interest', function ($account) {
                return number_format($account->accrued_interest, 4);
            })
            ->editColumn('last_interest_date', function ($account) {
                return $account->last_interest_date ? Carbon::parse($account->last_interest_date)->format('d-m-Y') : 'Never';
            })
            ->editColumn('status', function ($account) {
                if ($account->status == 'Active') {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-danger">' . $account->status . '</span>';
            })
            ->addColumn('action', function ($account) {
                $passbookUrl = route('shomriddhi-advance.passbooks.index') . '?account_id=' . $account->id;
                $detailsUrl = route('shomriddhi-advance.accounts.show', $account->id);
                
                $btn = '<a href="' . $detailsUrl . '" class="btn btn-sm btn-info mr-1" title="View Details"><i class="fas fa-eye"></i></a>';
                
                if (auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create')) {
                    $editUrl = route('shomriddhi-advance.accounts.edit', $account->id);
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary mr-1" title="Edit Account"><i class="fas fa-edit"></i></a>';
                }
                
                $btn .= '<a href="' . $passbookUrl . '" class="btn btn-sm btn-info" title="Manage Passbooks"><i class="fas fa-book"></i></a>';
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['image', 'status', 'action'])
            ->toJson();
    }

    public function show($id)
    {
        $pageTitle = 'Account Details';
        $account = ShomriddhiAccount::with(['customer.division', 'customer.district', 'customer.upazila', 'nominees.nominee'])->findOrFail($id);
        
        return view('shomriddhi_advance.accounts.show', compact('pageTitle', 'account'));
    }

    public function openingBalance()
    {
        abort_if(!auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'), 403);
        $pageTitle = 'Shomriddhi Opening Balance';
        
        $sisterConcernId = auth()->user()->activeConcernId();

        $accounts = ShomriddhiAccount::with('customer')
            ->where('origin_sister_concern_id', $sisterConcernId)
            ->whereNotIn('customer_id', ShomriddhiTransaction::where('is_opening_balance', 1)->pluck('customer_id'))
            ->get();
            
        return view('shomriddhi_advance.accounts.opening_balance', compact('pageTitle', 'accounts'));
    }

    public function storeOpeningBalance(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'), 403);
        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'note' => 'nullable|string',
        ]);
        
        // Prevent double opening balance in backend
        $existing = ShomriddhiTransaction::where('customer_id', $request->customer_id)
            ->where('is_opening_balance', 1)
            ->exists();
            
        if ($existing) {
            return redirect()->back()->with('error', 'This customer already has an Opening Balance.');
        }
        
        DB::beginTransaction();
        try {
            $sisterConcernId = auth()->user()->activeConcernId();
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $noteText = 'Opening Balance' . ($request->note ? ' - ' . $request->note : '');
            
            // 1. Get ShomriddhiAccount
            $account = ShomriddhiAccount::where('customer_id', $request->customer_id)->firstOrFail();
            
            // 2. Add to principal_balance
            $account->principal_balance += $request->amount;
            $account->save();
            
            // 3. Create ShomriddhiAdvance
            $advance = ShomriddhiTransaction::create([
                'customer_id' => $request->customer_id,
                'type'        => 'deposit',
                'amount'      => $request->amount,
                'date'        => $date,
                'note'        => $noteText,
                'is_opening_balance' => 1,
                'status'      => 'Approved',
                'created_by'  => auth()->id(),
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);
            
            // 4. Accounting Entry: Dr Opening Balance Equity, Cr Customer Savings Account
            $savingsHead = AccountHead::where('supplier_id', $request->customer_id)->where('head_type', 'savings')->first();
            if (!$savingsHead) {
                throw new \Exception('Savings account head not found for this customer.');
            }
            
            $openingEquityHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
            
            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $newNumber = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = Voucher::create([
                'sister_concern_id'   => $sisterConcernId,
                'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                'amount'              => $request->amount,
                'date'                => $date,
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'notes'               => $noteText,
                'user_id'             => auth()->id(),
                'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
            ]);

            // DR Opening Balance Equity
            Transaction::create([
                'sister_concern_id'   => $sisterConcernId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $openingEquityHeadId,
                'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'    => TransactionType::$DEBIT,
                'amount'              => $request->amount,
                'date'                => $date,
                'account_head_payee_depositor_id' => $savingsHead->id,
                'user_id'             => auth()->id(),
                'notes'               => $noteText,
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
                'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
            ]);
            
            // CR Customer Savings
            Transaction::create([
                'sister_concern_id'   => $sisterConcernId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $savingsHead->id,
                'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                'transaction_type'    => TransactionType::$CREDIT,
                'amount'              => $request->amount,
                'date'                => $date,
                'account_head_payee_depositor_id' => $openingEquityHeadId,
                'user_id'             => auth()->id(),
                'notes'               => $noteText,
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
                'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
            ]);
            
            $advance->update(['voucher_id' => $voucher->id]);
            
            DB::commit();
            return redirect()->route('shomriddhi-advance.accounts.index')->with('success', 'Opening balance added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to add opening balance: ' . $e->getMessage());
        }
    }

    public function edit(ShomriddhiAccount $account)
    {
        abort_if(!auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'), 403);
        $pageTitle = 'Edit Shomriddhi Account';
        
        $sisterConcernId = auth()->user()->activeConcernId();

        $account->load(['customer', 'nominees']);

        $customers = Supplier::where('id', $account->customer_id)->get();
        $nominees = Nominee::where('customer_id', $account->customer_id)->get();
        $divisions = Division::orderBy('name')->get();

        return view('shomriddhi_advance.accounts.edit', compact('pageTitle', 'account', 'customers', 'nominees', 'divisions'));
    }

    public function update(Request $request, ShomriddhiAccount $account)
    {
        abort_if(!auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'), 403);
        $request->validate([
            'date' => 'required|date',
            'note' => 'nullable|string',
            'nominees' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            // Update Account Note and Date
            $account->update([
                'opening_date' => Carbon::parse($request->date)->format('Y-m-d'),
                'note' => $request->note,
            ]);

            // Sync Nominees (update existing, create new, delete removed)
            $submittedNomineeIds = [];

            foreach ($request->nominees as $nom) {
                if (!empty($nom['id']) && !empty($nom['share_percentage'])) {
                    $submittedNomineeIds[] = $nom['id'];

                    ShomriddhiNominee::updateOrCreate(
                        [
                            'account_id' => $account->id,
                            'nominee_id' => $nom['id'],
                        ],
                        [
                            'share_percentage' => $nom['share_percentage'],
                        ]
                    );
                }
            }

            // Delete nominees that are no longer in the submitted list
            $account->nominees()->whereNotIn('nominee_id', $submittedNomineeIds)->delete();

            DB::commit();
            return redirect()->route('shomriddhi-advance.accounts.index')->with('success', 'Shomriddhi Account updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error updating account: ' . $e->getMessage());
        }
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'), 403);
        $pageTitle = 'Create Shomriddhi Account';
        
        $sisterConcernId = auth()->user()->activeConcernId();

        $existingAccountCustomerIds = ShomriddhiAccount::pluck('customer_id')->toArray();

        $customers = Supplier::whereNotNull('id')
            ->where('origin_sister_concern_id', $sisterConcernId)
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
            ->whereNotIn('id', $existingAccountCustomerIds)
            ->orderBy('name')
            ->get(['id', 'name', 'member_no', 'father_name', 'mother_name', 'nid_number', 'dob']);
            
        $divisions = Division::orderBy('name')->get();

        return view('shomriddhi_advance.accounts.create', compact('pageTitle', 'customers', 'divisions'));
    }

    public function getCustomerDetails(Request $request)
    {
        $customer = Supplier::find($request->customer_id);
        if (!$customer) {
            return response()->json(['success' => false, 'message' => 'Customer not found']);
        }
        
        $nominees = Nominee::where('customer_id', $customer->id)->get();
        
        return response()->json([
            'success' => true,
            'customer' => [
                'father_name' => $customer->father_name,
                'mother_name' => $customer->mother_name,
                'nid_number' => $customer->nid_number,
                'dob' => $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('d-m-Y') : '',
            ],
            'nominees' => $nominees
        ]);
    }

    public function storeNominee(Request $request)
    {
        $request->validate([
            'customer_id' => 'required',
            'name' => 'required',
            'relation' => 'required',
        ]);

        $customer = Supplier::find($request->customer_id);

        $nomineeImagePath = null;
        if ($request->hasFile('image')) {
            $nomineeImagePath = $this->uploadImage($request->file('image'), 'nominee');
        }

        $nomineeUnionId = null;
        $nomineePourashavaId = null;
        if ($request->union) {
            if (strpos($request->union, 'union_') !== false) {
                $nomineeUnionId = str_replace('union_', '', $request->union);
            } elseif (strpos($request->union, 'pourashava_') !== false) {
                $nomineePourashavaId = str_replace('pourashava_', '', $request->union);
            }
        }

        $nominee = Nominee::create([
            'customer_id' => $customer->id,
            'name'        => $request->name,
            'relation'    => $request->relation,
            'nid_number'  => $request->nid_number,
            'phone'       => $request->phone,
            'image'       => $nomineeImagePath,
            'division_id' => $request->division_id,
            'district_id' => $request->district_id,
            'upazila_id'  => $request->upazila_id,
            'union_id'    => $nomineeUnionId,
            'pourashava_id' => $nomineePourashavaId,
            'village_id'  => $request->village_id,
        ]);

        return response()->json(['success' => true, 'nominee' => $nominee]);
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'), 403);
        $request->validate([
            'customer_id' => 'required|exists:suppliers,id',
            'opening_balance' => 'nullable|numeric|min:0',
            'date' => 'required|date',
            'note' => 'nullable|string',
            'nominees' => 'required|array',
        ]);

        $customerId = $request->customer_id;
        $openingBalanceAmount = $request->opening_balance ?? 0;

        $existing = ShomriddhiAccount::where('customer_id', $customerId)->first();
        if ($existing) {
            return redirect()->back()->with('error', 'This customer already has a Shomriddhi Account.');
        }

        try {
            DB::beginTransaction();

            $customer = Supplier::findOrFail($customerId);
            $sisterConcernId = $customer->origin_sister_concern_id ?? auth()->user()->activeConcernId();

            // Create Customer Savings Account Head
            $savingsGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::CUSTOMER_SAVINGS)->first();
            if (!$savingsGroup) {
                $currentLiabilities = AccountGroup::where('reserved_id', ReservedAccountGroup::CURRENT_LIABILITIES)->first();
                $savingsGroup = AccountGroup::create([
                    'account_group_id' => $currentLiabilities->id ?? null,
                    'name' => 'Customer Savings Accounts',
                    'reserved_id' => ReservedAccountGroup::CUSTOMER_SAVINGS,
                    'is_editable' => 0,
                    'is_leaf' => 1,
                ]);
            }

            // Check if head already exists to prevent duplicate
            $existingHead = AccountHead::where('supplier_id', $customer->id)
                ->where('account_group_id', $savingsGroup->id)
                ->where('head_type', 'savings')
                ->first();

            if (!$existingHead) {
                $code = generateAccountCode(ReservedAccountGroup::LIABILITIES);
                AccountHead::create([
                    'sister_concern_id' => null,
                    'code' => $code,
                    'account_group_id' => $savingsGroup->id,
                    'supplier_id' => $customer->id,
                    'name' => $customer->name . ' (Savings A/C)',
                    'nature' => 'Credit',
                    'head_type' => 'savings',
                    'is_system' => 1,
                    'is_editable' => 0,
                    'is_control_account' => 0,
                ]);
            }

            // Generate Account Number (Branch: 3 digits, Serial: 6 digits)
            $sisterConcern = SisterConcern::find($sisterConcernId);
            $branchCodeVal = $sisterConcern ? $sisterConcern->code : '001';
            $branchCode = str_pad($branchCodeVal, 3, '0', STR_PAD_LEFT);
            
            $lastAccount = ShomriddhiAccount::where('origin_sister_concern_id', $sisterConcernId)
                ->whereNotNull('account_number')
                ->orderBy('id', 'desc')
                ->first();
                
            $serial = 1;
            if ($lastAccount && $lastAccount->account_number) {
                // Determine serial by removing the 3-digit branch code
                // Handle cases where older accounts might still have 11 digits
                if (strlen($lastAccount->account_number) > 9) {
                    $lastSerialStr = substr($lastAccount->account_number, -6);
                } else {
                    $lastSerialStr = substr($lastAccount->account_number, 3);
                }
                $serial = (int) $lastSerialStr + 1;
            }
            
            $serialStr = str_pad($serial, 6, '0', STR_PAD_LEFT);
            $accountNumber = $branchCode . $serialStr;

            // Create Shomriddhi Account
            $account = ShomriddhiAccount::create([
                'account_number' => $accountNumber,
                'customer_id' => $customer->id,
                'origin_sister_concern_id' => $sisterConcernId,
                'opening_date' => Carbon::parse($request->date)->format('Y-m-d'),
                'principal_balance' => $openingBalanceAmount,
                'accrued_interest' => 0,
                'status' => 'Active'
            ]);



            // Handle Nominees
            if ($request->has('nominees') && is_array($request->nominees)) {
                foreach ($request->nominees as $nomineeData) {
                    if (!empty($nomineeData['id'])) {
                        ShomriddhiNominee::create([
                            'nominee_id'       => $nomineeData['id'],
                            'account_type'     => 'Savings',
                            'account_id'       => $account->id,
                            'share_percentage' => $nomineeData['share_percentage'] ?? 100,
                        ]);
                    }
                }
            }

            // Add Opening Balance logic if provided
            if ($openingBalanceAmount > 0) {
                $businessDate = $sisterConcern ? $sisterConcern->business_date : now()->format('Y-m-d');
                $date = Carbon::parse($businessDate)->subDay()->format('Y-m-d');
                $noteText = $request->note ?? 'Opening Balance';
                
                $advance = ShomriddhiTransaction::create([
                    'customer_id' => $customer->id,
                    'type'        => 'deposit',
                    'amount'      => $openingBalanceAmount,
                    'date'        => $date,
                    'note'        => $noteText,
                    'is_opening_balance' => 1,
                    'status'      => 'Approved',
                    'created_by'  => auth()->id(),
                    'approved_by' => auth()->id(),
                    'approved_at' => now(),
                ]);
                
                $savingsHead = AccountHead::where('supplier_id', $customer->id)->where('head_type', 'savings')->first();
                if (!$savingsHead) {
                    throw new \Exception('Savings account head not found for this customer.');
                }
                
                $openingEquityHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                
                $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                $newNumber = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                $voucher = Voucher::create([
                    'sister_concern_id'   => $sisterConcernId,
                    'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                    'amount'              => $openingBalanceAmount,
                    'date'                => $date,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'account_head_payee_depositor_id' => $openingEquityHeadId,
                    'notes'               => $noteText,
                    'user_id'             => auth()->id(),
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
                ]);

                // DR Opening Balance Equity
                Transaction::create([
                    'sister_concern_id'   => $sisterConcernId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $openingEquityHeadId,
                    'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type'    => TransactionType::$DEBIT,
                    'amount'              => $openingBalanceAmount,
                    'date'                => $date,
                    'account_head_payee_depositor_id' => $savingsHead->id,
                    'user_id'             => auth()->id(),
                    'notes'               => $noteText,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
                ]);
                
                // CR Customer Savings
                Transaction::create([
                    'sister_concern_id'   => $sisterConcernId,
                    'voucher_id'          => $voucher->id,
                    'account_head_id'     => $savingsHead->id,
                    'voucher_type'        => VoucherType::$JOURNAL_VOUCHER,
                    'transaction_type'    => TransactionType::$CREDIT,
                    'amount'              => $openingBalanceAmount,
                    'date'                => $date,
                    'account_head_payee_depositor_id' => $openingEquityHeadId,
                    'user_id'             => auth()->id(),
                    'notes'               => $noteText,
                    'voucher_no'          => $voucherNo,
                    'voucher_no_group_sl' => $newNumber,
                    'payment_purpose'     => PaymentPurpose::$SHOMRIDDHI_ADVANCE_DEPOSIT,
                ]);
                
                $advance->update(['voucher_id' => $voucher->id]);
            }

            DB::commit();
            return redirect()->route('shomriddhi-advance.accounts.index')->with('success', 'Shomriddhi Account created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error creating account: ' . $e->getMessage());
        }
    }


    public function blankPassbook($id)
    {
        $account = ShomriddhiAccount::with(['customer.cards' => function($q) {
                $q->orderBy('id', 'desc');
            }])
            ->findOrFail($id);

        $sisterConcern = SisterConcern::find($account->origin_sister_concern_id);
        $pageTitle = 'Blank Passbook - ' . ($account->customer ? $account->customer->name : 'Account');

        return view('shomriddhi_advance.accounts.blank_passbook', compact('account', 'sisterConcern', 'pageTitle'));
    }

    public function miniStatement(string $accountNumber)
    {
        $account = ShomriddhiAccount::where('account_number', $accountNumber)
            ->with('customer')
            ->firstOrFail();

        $allTxns = ShomriddhiTransaction::where('customer_id', $account->customer_id)
            ->where('status', 'Approved')
            ->orderBy('date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $runningBalance = 0;
        $balanceMap = [];
        $totalTxnsCount = $allTxns->count();
        $openingBalance = 0;
        
        $transactions = collect();

        foreach ($allTxns as $index => $t) {
            $runningBalance += ($t->type === 'deposit' ? $t->amount : -$t->amount);
            $balanceMap[$t->id] = $runningBalance;
            
            // If this transaction is within the last 10, add it to our list
            if ($totalTxnsCount - $index <= 10) {
                if ($transactions->isEmpty()) {
                    // The balance right before this very first transaction of the last 10
                    $openingBalance = $runningBalance - ($t->type === 'deposit' ? $t->amount : -$t->amount);
                }
                $transactions->push($t);
            }
        }
        
        // Reverse it if you want newest at the top, or keep it chronological. Mini statements usually have chronological order or newest at top. Let's keep chronological.
        // Actually, if they want newest at the top, we reverse it. I will pass it chronological (oldest to newest) because it's easier to read opening balance -> txns -> closing balance.

        $sisterConcern = SisterConcern::find($account->origin_sister_concern_id);

        return view('shomriddhi_advance.accounts.mini_statement', compact(
            'account', 'transactions', 'balanceMap', 'sisterConcern', 'openingBalance', 'runningBalance'
        ));
    }
}
