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
use App\Models\Sales\SaleOrder;
use App\Models\Sales\SalePayment;
use App\Models\SisterConcern;
use App\Models\Supplier;
use App\Models\Division;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Traits\UploadTrait;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class DealerController extends Controller
{
    use UploadTrait;
    public function index()
    {
        $divisions = Division::orderBy('name')->get();
        return view('sales.dealer.index', compact('divisions'));
    }

    public function dataTable(Request $request)
    {
        $query = Supplier::where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->where('type', SupplierType::$DEALER)
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
            ->addColumn('action', function (Supplier $supplier) {
                $btn = '';
                $btn .= '<a href="' . route('dealer.show', ['dealer' => $supplier->id]) . '" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-eye"></i></a> ';
                
                $hasEdit = auth()->user()->can('dealer_edit');
                $hasDelete = auth()->user()->can('dealer_delete');
                $isOrigin = auth()->user()->activeConcernId() == $supplier->origin_sister_concern_id;

                if ($hasEdit || $hasDelete) {
                    if ($isOrigin) {
                        if ($supplier->hasShomriddhiAccount()) {
                            $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Shomriddhi Account exists"><i class="fa fa-lock"></i></button>';
                        } else {
                            if ($hasEdit) {
                                $btn .= '<a href="' . route('dealer.edit', ['dealer' => $supplier->id]) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                            }
                            if ($hasDelete) {
                                $btn .= ' <a role="button" data-id="' . $supplier->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                            }
                        }
                    } else {
                        $btn .= ' <button type="button" class="btn btn-secondary btn-sm" disabled title="Locked: Created by another branch"><i class="fa fa-lock"></i></button>';
                    }
                }
                return dropdownMenuContainer($btn);
            })
            ->addColumn('address', function (Supplier $supplier) {
                return '<button class="btn btn-info btn-sm btn-view-address" data-id="'.$supplier->id.'">View Address</button>';
            })
            ->rawColumns(['action','address'])
            ->toJson();
    }

    public function create()
    {
        if (!auth()->user()->hasPermissionTo('dealer_create')) {
            abort(403, 'Unauthorized');
        }
        $divisions = Division::orderBy('name')->get();
        return view('sales.dealer.create', compact('divisions'));
    }

    public function store(Request $request)
    {
        if (!auth()->user()->hasPermissionTo('dealer_create')) {
            abort(403, 'Unauthorized');
        }

        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'mobile_no' => [
                'required', 'max:255',
                Rule::unique('suppliers')
            ],
            'email' => [
                'nullable', 'max:255',
                Rule::unique('suppliers')
            ],
            'opening_balance' => 'required|numeric|min:0',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'union' => 'nullable',
            'village_id' => 'nullable|integer',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            $validatedData['type'] = SupplierType::$DEALER;
            $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$DEALER);
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
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'dealer');
            }
            unset($validatedData['division'], $validatedData['district'], $validatedData['upazila'], $validatedData['union']);
            unset($validatedData['opening_balance']);
            $customer = Supplier::create($validatedData);

            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)
                ->first();

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $customer->id)
                ->first();

            if (!$accountHead) {
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $receivablesGroup->id;
            $accountHead->supplier_id = $customer->id;
            $accountHead->name = $request->name . '(Dealer Client)';

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
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? \Carbon\Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : \Carbon\Carbon::now()->subDay()->format('Y-m-d');

                $voucher = new Voucher();
                $voucher->sister_concern_id = auth()->user()->activeConcernId();
                $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $voucher->amount = $request->opening_balance;
                $voucher->date = $today;
                $voucher->voucher_no = $voucherNo;
                $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                $voucher->account_head_payee_depositor_id = $accountHead->id;
                $voucher->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
                $voucher->is_opening_balance = 1;
                $voucher->user_id = auth()->id();
                $voucher->save();

                // Debit: Customer Account Head (Receivable increases)
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
                $txDebit->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
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
                $txCredit->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
                $txCredit->is_opening_balance = 1;
                $txCredit->user_id = auth()->id();
                $txCredit->save();
            }

            DB::commit();
            return redirect()->route('dealer.index')->with('success', 'Dealer Client created successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('dealer.create')
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show(Supplier $dealer)
    {
        if ($dealer->type != SupplierType::$DEALER) {
            abort(404);
        }
        $dealer->load('division', 'district', 'upazila', 'union', 'pourashava');
        return view('sales.dealer.show', compact('dealer'));
    }

    public function edit(Supplier $dealer)
    {
        if (!auth()->user()->hasPermissionTo('dealer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($dealer->type != SupplierType::$DEALER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $dealer->origin_sister_concern_id) {
            abort(403, 'You can only edit dealers created by your branch.');
        }
        if ($dealer->hasShomriddhiAccount()) {
            abort(403, 'This member has a Shomriddhi Account and cannot be edited.');
        }
        $divisions = Division::orderBy('name')->get();
        return view('sales.dealer.edit', compact('dealer', 'divisions'));
    }

    public function update(Request $request, Supplier $dealer)
    {
        if (!auth()->user()->hasPermissionTo('dealer_edit')) {
            abort(403, 'Unauthorized');
        }
        if ($dealer->type != SupplierType::$DEALER) {
            abort(404);
        }
        if (auth()->user()->activeConcernId() != $dealer->origin_sister_concern_id) {
            abort(403, 'You can only edit dealers created by your branch.');
        }
        if ($dealer->hasShomriddhiAccount()) {
            abort(403, 'This member has a Shomriddhi Account and cannot be edited.');
        }

        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'mobile_no' => [
                'required', 'max:255',
                Rule::unique('suppliers')
                    ->ignore($dealer)
            ],
            'email' => [
                'nullable', 'max:255',
                Rule::unique('suppliers')
                    ->ignore($dealer)
            ],
            'opening_balance' => 'required|numeric|min:0',
            'division' => 'required',
            'district' => 'required',
            'upazila' => 'required',
            'union' => 'nullable',
            'village_id' => 'nullable|integer',
            'status' => 'required|boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        DB::beginTransaction();
        try {
            $validatedData['origin_sister_concern_id'] = auth()->user()->activeConcernId();
            if ($dealer->member_no == null){
                $validatedData['member_no'] = generatePartyMemberNo(auth()->user()->activeConcernId(), SupplierType::$DEALER);
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
                $this->deleteFile($dealer->image);
                $validatedData['image'] = $this->uploadImage($request->file('image'), 'dealer');
            }
            unset($validatedData['division'], $validatedData['district'], $validatedData['upazila'], $validatedData['union']);
            unset($validatedData['opening_balance']);

            $dealer->update($validatedData);

            // Create/update Account Head record
            $receivablesGroup = AccountGroup::where('reserved_id', ReservedAccountGroup::ACCOUNTS_RECEIVABLE)
                ->first();

            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $dealer->id)
                ->first();

            if (!$accountHead) {
                $code = generateAccountCode($receivablesGroup->top_reserved_id);
                $accountHead = new AccountHead();
                $accountHead->sister_concern_id = null;
                $accountHead->code = $code;
            }
            $accountHead->account_group_id = $receivablesGroup->id;
            $accountHead->supplier_id = $dealer->id;
            $accountHead->name = $request->name . '(Dealer Client)';

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
                $activeSisterConcern = \App\Models\BranchManagement\SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? \Carbon\Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : \Carbon\Carbon::now()->subDay()->format('Y-m-d');

                if ($existingObVoucher) {
                    $existingObVoucher->amount = $request->opening_balance;
                    $existingObVoucher->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
                    $existingObVoucher->save();

                    Transaction::where('voucher_id', $existingObVoucher->id)
                        ->where('account_head_id', $accountHead->id)
                        ->update(['amount' => $request->opening_balance, 'notes' => 'Opening Balance - ' . $request->name . ' (Dealer Client)']);

                    Transaction::where('voucher_id', $existingObVoucher->id)
                        ->where('account_head_id', $obHeadId)
                        ->update(['amount' => $request->opening_balance, 'notes' => 'Opening Balance - ' . $request->name . ' (Dealer Client)']);
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
                    $voucher->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
                    $voucher->is_opening_balance = 1;
                    $voucher->user_id = auth()->id();
                    $voucher->save();

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
                    $txDebit->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
                    $txDebit->is_opening_balance = 1;
                    $txDebit->user_id = auth()->id();
                    $txDebit->save();

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
                    $txCredit->notes = 'Opening Balance - ' . $request->name . ' (Dealer Client)';
                    $txCredit->is_opening_balance = 1;
                    $txCredit->user_id = auth()->id();
                    $txCredit->save();
                }
            } elseif ($existingObVoucher) {
                Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                $existingObVoucher->delete();
            }

            DB::commit();
            return redirect()->route('dealer.index')->with('success', 'Dealer Client updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('dealer.edit', ['dealer' => $dealer->id])
                ->withInput()
                ->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy(Supplier $dealer)
    {
        try {
            if (!auth()->user()->hasPermissionTo('dealer_delete')) {
                abort(403, 'Unauthorized');
            }
            if (auth()->user()->activeConcernId() != $dealer->origin_sister_concern_id) {
                return response()->json(['success' => false, 'message' => 'You can only delete dealers created by your branch.'], Response::HTTP_OK);
            }
            
            if ($dealer->hasShomriddhiAccount()) {
                return response()->json(['success' => false, 'message' => 'Has Shomriddhi Account. Cannot delete.'], Response::HTTP_OK);
            }

            // Check for Sales
            if (SaleOrder::where('customer_id', $dealer->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has sales records. Cannot delete.'], Response::HTTP_OK);
            }

            // Check for Accounting Transactions
            $accountHead = AccountHead::where('head_type', 'primary')->where('supplier_id', $dealer->id)->first();
            if ($accountHead && Transaction::where('account_head_id', $accountHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has accounting transactions. Cannot delete.'], Response::HTTP_OK);
            }

            $accountSavingsHead = AccountHead::where('head_type', 'savings')->where('supplier_id', $dealer->id)->first();
            if ($accountSavingsHead && Transaction::where('account_head_id', $accountSavingsHead->id)->exists()) {
                return response()->json(['success' => false, 'message' => 'Has savings transactions. Cannot delete.'], Response::HTTP_OK);
            }

            DB::beginTransaction();
            if ($accountHead) {
                AccountHead::where('supplier_id', $dealer->id)->delete();
            }
            $dealer->delete();
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Dealer Client deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], Response::HTTP_OK);
        }
    }
}

