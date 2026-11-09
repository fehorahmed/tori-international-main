<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Enumeration\ReservedAccountGroup;
use App\Http\Controllers\Controller;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\ContractorManagement\Contractor;
use App\Models\ContractorManagement\ContractorType;
use App\Models\Division;
use App\Models\District;
use App\Models\Account\Voucher;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use App\Enumeration\ReservedAccountHead;
use Carbon\Carbon;
use App\Traits\UploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ContractorController extends Controller
{
    use UploadTrait;

    public function index()
    {
        $types = ContractorType::where('status', 1)->get();
        $divisions = Division::orderBy('name')->get();
        return view('contractor_management.contractors.index', compact('types', 'divisions'));
    }

    public function datatable(Request $request)
    {
        $query = Contractor::with(['contractorType', 'division', 'district', 'upazila', 'union', 'pourashava'])
            ->withSum(['transactions as total_debit' => function ($q) {
                $q->where('transaction_type', 1);
            }], 'amount')
            ->withSum(['transactions as total_credit' => function ($q) {
                $q->where('transaction_type', 2);
            }], 'amount')
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId());

        if ($request->contractor_type_id) {
            $query->where('contractor_type_id', $request->contractor_type_id);
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

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('type_name', function ($contractor) {
                return $contractor->contractorType ? $contractor->contractorType->name : '';
            })
            ->addColumn('address', function($contractor) {
                return '<button class="btn btn-info btn-sm btn-view-address" data-id="'.$contractor->id.'">View Address</button>';
            })
            ->addColumn('total_bill', function ($contractor) {
                return number_format($contractor->total_credit, 2);
            })
            ->addColumn('total_paid', function ($contractor) {
                return number_format($contractor->total_debit, 2);
            })
            ->addColumn('balance', function ($contractor) {
                $balance = $contractor->total_credit - $contractor->total_debit;
                if ($balance > 0) {
                    return '<span class="text-danger">' . number_format($balance, 2) . ' (Due)</span>';
                } elseif ($balance < 0) {
                    return '<span class="text-success">' . number_format(abs($balance), 2) . ' (Adv)</span>';
                }
                return '<span class="text-muted">0.00</span>';
            })
            ->addColumn('status', function ($contractor) {
                if ($contractor->status == 1) {
                    return '<span class="badge badge-success">Active</span>';
                }
                return '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($contractor) {
                $btn = '<a href="' . route('contractor_management.contractors.show', $contractor->id) . '" class="btn btn-primary bg-gradient-primary btn-sm" title="View"><i class="fa fa-eye"></i></a>';
                $btn .= ' <a href="' . route('contractor_management.contractors.edit', $contractor->id) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit" title="Edit"><i class="fa fa-edit"></i></a>';
                
                $btn .= ' <a href="' . route('contractor_management.bills.create', ['contractor_id' => $contractor->id]) . '" class="btn btn-warning bg-gradient-warning btn-sm" title="Create Bill"><i class="fa fa-file-invoice"></i> Bill</a>';
                $btn .= ' <a href="' . route('contractor_management.payments.create', ['contractor_id' => $contractor->id]) . '" class="btn btn-success bg-gradient-success btn-sm" title="Create Payment"><i class="fa fa-money-bill-wave"></i> Pay</a>';

                if ($contractor->hasTransactions()) {
                    $btn .= ' <button class="btn btn-secondary btn-sm" title="Locked: Transactions Exist" disabled><i class="fa fa-lock"></i></button>';
                } else {
                    $btn .= ' <a role="button" data-id="' . $contractor->id . '" class="btn btn-danger btn-sm btn-delete" title="Delete"><i class="fa fa-trash"></i></a>';
                }
                
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['status', 'action', 'address', 'balance'])
            ->toJson();
    }

    public function getAddressDetails(Request $request)
    {
        $contractor = Contractor::with('division', 'district', 'upazila', 'union', 'pourashava', 'village')
            ->find($request->id);

        if (!$contractor) {
            return response()->json(['success' => false, 'message' => 'Contractor not found']);
        }

        $html = '<table class="table table-sm table-borderless">';
        $html .= '<tr><th width="35%"><i class="fa fa-map-marker-alt text-primary mr-2"></i> Division</th><td>: ' . ($contractor->division->name ?? '') . '</td></tr>';
        $html .= '<tr><th><i class="fa fa-city text-info mr-2"></i> District</th><td>: ' . ($contractor->district->name ?? '') . '</td></tr>';
        $html .= '<tr><th><i class="fa fa-building text-warning mr-2"></i> Upazila</th><td>: ' . ($contractor->upazila->name ?? '') . '</td></tr>';

        if ($contractor->pourashava) {
            $html .= '<tr><th><i class="fa fa-landmark text-success mr-2"></i> Pourashava</th><td>: ' . ($contractor->pourashava->name ?? '') . '</td></tr>';
        }
        if ($contractor->union) {
            $html .= '<tr><th><i class="fa fa-users text-purple mr-2"></i> Union</th><td>: ' . ($contractor->union->name ?? '') . '</td></tr>';
        }

        $html .= '<tr><th><i class="fa fa-home text-success mr-2"></i> Village</th><td>: ' . ($contractor->village->name ?? 'N/A') . '</td></tr>';
        if ($contractor->address) {
            $html .= '<tr><th><i class="fa fa-map-signs text-danger mr-2"></i> Details Address</th><td>: ' . $contractor->address . '</td></tr>';
        }
        $html .= '</table>';

        return response()->json(['success' => true, 'html' => $html]);
    }

    public function create()
    {
        $types = ContractorType::where('status', 1)->get();
        $divisions = Division::orderBy('name')->get();
        return view('contractor_management.contractors.create', compact('types', 'divisions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contractor_type_id' => 'required|exists:contractor_types,id',
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20|unique:contractors,mobile_no',
            'email' => 'nullable|email|max:255',
            'nid_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            'opening_balance' => 'nullable|numeric|min:0',
            'balance_type' => 'required_with:opening_balance|in:due,advance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            // 1. Determine Account Group for Contractor (using ACCOUNTS_PAYABLE since we owe them money)
            $payablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_PAYABLE)->first();

            // 2. Create Account Head
            $code = generateAccountCode($payablesGroup->top_reserved_id);
            $accountHead = new AccountHead();
            $accountHead->sister_concern_id = null;
            $accountHead->code = $code;
            $accountHead->account_group_id = $payablesGroup->id;
            $accountHead->name = $request->name . ' (Contractor)';
            $accountHead->nature = 'Credit';
            $accountHead->is_system = 1;
            $accountHead->is_control_account = 0;
            $accountHead->save();

            // 3. Prepare data and Create Contractor
            $data = $request->except(['_token', 'image', 'division', 'district', 'upazila', 'union']);
            $data['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $data['account_head_id'] = $accountHead->id;
            $data['division_id'] = $request->division;
            $data['district_id'] = $request->district;
            $data['upazila_id'] = $request->upazila;
            if ($request->union) {
                if (strpos($request->union, 'union_') !== false) {
                    $data['union_id'] = str_replace('union_', '', $request->union);
                } elseif (strpos($request->union, 'pourashava_') !== false) {
                    $data['pourashava_id'] = str_replace('pourashava_', '', $request->union);
                }
            }

            if ($request->hasFile('image')) {
                $data['image'] = $this->uploadImage($request->file('image'), 'contractor');
            }

            Contractor::create($data);

            // --- Opening Balance Journal Entry ---
            if ($request->opening_balance > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $today = Carbon::now()->subDay()->format('Y-m-d');
                
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
                $voucher->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                $voucher->is_opening_balance = 1;
                $voucher->user_id = auth()->id();
                $voucher->save();

                // If balance is 'due' (we owe them), we Credit their account. 
                // If 'advance' (we paid beforehand), we Debit their account.
                $isAdvance = ($request->balance_type == 'advance');

                $txDebit = new Transaction();
                $txDebit->sister_concern_id = auth()->user()->activeConcernId();
                $txDebit->voucher_id = $voucher->id;
                $txDebit->account_head_id = $isAdvance ? $accountHead->id : $obHeadId;
                $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $txDebit->transaction_type = TransactionType::$DEBIT;
                $txDebit->amount = $request->opening_balance;
                $txDebit->date = $today;
                $txDebit->voucher_no = $voucherNo;
                $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                $txDebit->account_head_payee_depositor_id = $accountHead->id;
                $txDebit->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                $txDebit->is_opening_balance = 1;
                $txDebit->user_id = auth()->id();
                $txDebit->save();

                $txCredit = new Transaction();
                $txCredit->sister_concern_id = auth()->user()->activeConcernId();
                $txCredit->voucher_id = $voucher->id;
                $txCredit->account_head_id = $isAdvance ? $obHeadId : $accountHead->id;
                $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $txCredit->transaction_type = TransactionType::$CREDIT;
                $txCredit->amount = $request->opening_balance;
                $txCredit->date = $today;
                $txCredit->voucher_no = $voucherNo;
                $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                $txCredit->account_head_payee_depositor_id = $accountHead->id;
                $txCredit->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                $txCredit->is_opening_balance = 1;
                $txCredit->user_id = auth()->id();
                $txCredit->save();
            }

            DB::commit();
            return redirect()->route('contractor_management.contractors.index')->with('success', 'Contractor created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(Contractor $contractor)
    {
        $contractor->load(['contractorType', 'division', 'district', 'upazila', 'union', 'pourashava', 'accountHead']);
        $transactions = Transaction::where('account_head_id', $contractor->account_head_id)
            ->orderBy('date', 'asc')
            ->orderBy('is_opening_balance', 'desc')
            ->get();
        return view('contractor_management.contractors.show', compact('contractor', 'transactions'));
    }

    public function edit(Contractor $contractor)
    {
        $types = ContractorType::where('status', 1)->get();
        $divisions = Division::orderBy('name')->get();
        $districts = District::where('division_id', $contractor->division_id)->get();
        $openingBalance = 0;
        $balanceType = 'due';
        if ($contractor->accountHead) {
            $existingObVoucher = Voucher::where('account_head_payee_depositor_id', $contractor->accountHead->id)
                ->where('is_opening_balance', 1)
                ->first();
            if ($existingObVoucher) {
                $openingBalance = $existingObVoucher->amount;
                $tx = Transaction::where('voucher_id', $existingObVoucher->id)
                    ->where('account_head_id', $contractor->accountHead->id)
                    ->first();
                if ($tx && $tx->transaction_type == TransactionType::$DEBIT) {
                    $balanceType = 'advance';
                }
            }
        }
        return view('contractor_management.contractors.edit', compact('contractor', 'types', 'divisions', 'districts', 'openingBalance', 'balanceType'));
    }

    public function update(Request $request, Contractor $contractor)
    {
        $request->validate([
            'contractor_type_id' => 'required|exists:contractor_types,id',
            'name' => 'required|string|max:255',
            'mobile_no' => 'required|string|max:20|unique:contractors,mobile_no,' . $contractor->id,
            'email' => 'nullable|email|max:255',
            'nid_number' => 'nullable|string|max:50',
            'company_name' => 'nullable|string|max:255',
            'status' => 'required|in:0,1',
            // 'opening_balance' => 'nullable|numeric|min:0',
            // 'balance_type' => 'required_with:opening_balance|in:due,advance',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $data = $request->except(['_token', 'image', 'division', 'district', 'upazila', 'union']);
            $data['division_id'] = $request->division;
            $data['district_id'] = $request->district;
            $data['upazila_id'] = $request->upazila;
            
            if ($request->union) {
                if (strpos($request->union, 'union_') !== false) {
                    $data['union_id'] = str_replace('union_', '', $request->union);
                    $data['pourashava_id'] = null;
                } elseif (strpos($request->union, 'pourashava_') !== false) {
                    $data['pourashava_id'] = str_replace('pourashava_', '', $request->union);
                    $data['union_id'] = null;
                }
            } else {
                $data['union_id'] = null;
                $data['pourashava_id'] = null;
            }

            if ($request->hasFile('image')) {
                if ($contractor->image) {
                    $this->deleteFile($contractor->image);
                }
                $data['image'] = $this->uploadImage($request->file('image'), 'contractor');
            }

            $contractor->update($data);

            if ($contractor->accountHead) {
                $contractor->accountHead->name = $request->name . ' (Contractor)';
                $contractor->accountHead->save();

                /* Edit a opening balance change off kora holo
                // --- Update Opening Balance Journal Entry ---
                $existingObVoucher = Voucher::where('account_head_payee_depositor_id', $contractor->accountHead->id)
                    ->where('is_opening_balance', 1)
                    ->first();

                if ($request->opening_balance > 0) {
                    $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                    $today = Carbon::now()->subDay()->format('Y-m-d');
                    $isAdvance = ($request->balance_type == 'advance');
                    $accountHeadId = $contractor->accountHead->id;

                    if ($existingObVoucher) {
                        $existingObVoucher->amount = $request->opening_balance;
                        $existingObVoucher->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                        $existingObVoucher->save();

                        // Re-evaluate Debit side
                        Transaction::where('voucher_id', $existingObVoucher->id)
                            ->where('transaction_type', TransactionType::$DEBIT)
                            ->update([
                                'account_head_id' => $isAdvance ? $accountHeadId : $obHeadId,
                                'amount' => $request->opening_balance, 
                                'notes' => 'Opening Balance - ' . $request->name . ' (Contractor)'
                            ]);

                        // Re-evaluate Credit side
                        Transaction::where('voucher_id', $existingObVoucher->id)
                            ->where('transaction_type', TransactionType::$CREDIT)
                            ->update([
                                'account_head_id' => $isAdvance ? $obHeadId : $accountHeadId,
                                'amount' => $request->opening_balance, 
                                'notes' => 'Opening Balance - ' . $request->name . ' (Contractor)'
                            ]);
                    } else {
                        // Create new if it didn't exist
                        $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                        $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                        $voucher = new Voucher();
                        $voucher->sister_concern_id = auth()->user()->activeConcernId();
                        $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $voucher->amount = $request->opening_balance;
                        $voucher->date = $today;
                        $voucher->voucher_no = $voucherNo;
                        $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                        $voucher->account_head_payee_depositor_id = $accountHeadId;
                        $voucher->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                        $voucher->is_opening_balance = 1;
                        $voucher->user_id = auth()->id();
                        $voucher->save();

                        $txDebit = new Transaction();
                        $txDebit->voucher_id = $voucher->id;
                        $txDebit->account_head_id = $isAdvance ? $accountHeadId : $obHeadId;
                        $txDebit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $txDebit->transaction_type = TransactionType::$DEBIT;
                        $txDebit->amount = $request->opening_balance;
                        $txDebit->date = $today;
                        $txDebit->voucher_no = $voucherNo;
                        $txDebit->voucher_no_group_sl = $voucherNoGroupSl;
                        $txDebit->account_head_payee_depositor_id = $accountHeadId;
                        $txDebit->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                        $txDebit->is_opening_balance = 1;
                        $txDebit->user_id = auth()->id();
                        $txDebit->save();

                        $txCredit = new Transaction();
                        $txCredit->voucher_id = $voucher->id;
                        $txCredit->account_head_id = $isAdvance ? $obHeadId : $accountHeadId;
                        $txCredit->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                        $txCredit->transaction_type = TransactionType::$CREDIT;
                        $txCredit->amount = $request->opening_balance;
                        $txCredit->date = $today;
                        $txCredit->voucher_no = $voucherNo;
                        $txCredit->voucher_no_group_sl = $voucherNoGroupSl;
                        $txCredit->account_head_payee_depositor_id = $accountHeadId;
                        $txCredit->notes = 'Opening Balance - ' . $request->name . ' (Contractor)';
                        $txCredit->is_opening_balance = 1;
                        $txCredit->user_id = auth()->id();
                        $txCredit->save();
                    }
                } elseif ($existingObVoucher) {
                    Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                    $existingObVoucher->delete();
                }
                */
            }

            DB::commit();
            return redirect()->route('contractor_management.contractors.index')->with('success', 'Contractor updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(Contractor $contractor)
    {
        try {
            // Check if there are transactions in the account head
            if ($contractor->accountHead && $contractor->accountHead->transactions()->exists()) {
                 return response()->json(['success' => false, 'message' => 'Cannot delete! Contractor has financial transactions.']);
            }

            DB::beginTransaction();
            if ($contractor->accountHead) {
                $contractor->accountHead->delete();
            }
            if ($contractor->image) {
                $this->deleteFile($contractor->image);
            }
            $contractor->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Contractor deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error deleting contractor: ' . $e->getMessage()]);
        }
    }
}
