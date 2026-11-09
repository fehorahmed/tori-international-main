<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Http\Controllers\Controller;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Models\BranchManagement\SisterConcern;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ExpenseHeadController extends Controller
{
    public function index(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('contractor_expense_head_view'), 403, 'Unauthorized');
        $sister_concerns = SisterConcern::where('status', 1)->get();

        return view('contractor_management.expense_heads.index', compact('sister_concerns'));
    }

    public function dataTable()
    {
        $group = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();

        $query = AccountHead::with('accountGroup')
            ->where('account_group_id', $group->id)
            // ->where('is_editable', 1)
            ->where(function ($q) {
                $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
            });

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (AccountHead $accountHead) {
                $btn = '';
                if (auth()->user()->can('contractor_expense_head_edit') && $accountHead->is_editable) {
                    $btn .= '<a href="' . route('contractor_management.expense-heads.edit', $accountHead->id) . '" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }
                if (auth()->user()->can('contractor_expense_head_delete') && $accountHead->is_editable) {
                    $btn .= ' <a role="button" data-id="' . $accountHead->id . '" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('contractor_expense_head_create'), 403, 'Unauthorized');
        $sister_concerns = SisterConcern::where('status', 1)->get();

        return view('contractor_management.expense_heads.create', compact('sister_concerns'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('contractor_expense_head_create'), 403, 'Unauthorized');

        $validatedData = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('account_heads')],
            'opening_balance' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $group = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
            $topReservedId = $group->top_reserved_id;

            $validatedData['code'] = generateAccountCode($topReservedId);
            $validatedData['nature'] = 'Debit';
            $validatedData['payment_mode'] = 0;
            $validatedData['is_editable'] = 1;
            $validatedData['sister_concern_id'] = null;
            $validatedData['account_group_id'] = $group->id;

            $openingBalance = $validatedData['opening_balance'];
            unset($validatedData['opening_balance']);

            $accountHead = AccountHead::create($validatedData);

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
                $voucher->notes = 'Opening Balance - ' . $request->name;
                $voucher->is_opening_balance = 1;
                $voucher->user_id = auth()->id();
                $voucher->save();

                $isDebitNature = true; // Expenses are always Debit

                $tx1 = new Transaction();
                $tx1->sister_concern_id = auth()->user()->activeConcernId();
                $tx1->voucher_id = $voucher->id;
                $tx1->account_head_id = $accountHead->id;
                $tx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $tx1->transaction_type = $isDebitNature ? TransactionType::$DEBIT : TransactionType::$CREDIT;
                $tx1->amount = $openingBalance;
                $tx1->date = $today;
                $tx1->voucher_no = $voucherNo;
                $tx1->voucher_no_group_sl = $voucherNoGroupSl;
                $tx1->account_head_payee_depositor_id = $accountHead->id;
                $tx1->notes = 'Opening Balance - ' . $request->name;
                $tx1->is_opening_balance = 1;
                $tx1->user_id = auth()->id();
                $tx1->save();

                $tx2 = new Transaction();
                $tx2->sister_concern_id = auth()->user()->activeConcernId();
                $tx2->voucher_id = $voucher->id;
                $tx2->account_head_id = $obHeadId;
                $tx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $tx2->transaction_type = $isDebitNature ? TransactionType::$CREDIT : TransactionType::$DEBIT;
                $tx2->amount = $openingBalance;
                $tx2->date = $today;
                $tx2->voucher_no = $voucherNo;
                $tx2->voucher_no_group_sl = $voucherNoGroupSl;
                $tx2->account_head_payee_depositor_id = $accountHead->id;
                $tx2->notes = 'Opening Balance - ' . $request->name;
                $tx2->is_opening_balance = 1;
                $tx2->user_id = auth()->id();
                $tx2->save();
            }

            DB::commit();
            return redirect()->route('contractor_management.expense-heads.index')->with('success', 'Expense Head added successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('contractor_management.expense-heads.create')->withInput()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function edit($id, Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('contractor_expense_head_edit'), 403, 'Unauthorized');

        $account_head = AccountHead::findOrFail($id);
        abort_if(!$account_head->is_editable, 403, 'Unauthorized');
        abort_if($account_head->sister_concern_id !== null && $account_head->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        return view('contractor_management.expense_heads.edit', compact('account_head'));
    }

    public function update(Request $request, $id)
    {
        abort_if(!auth()->user()->hasPermissionTo('contractor_expense_head_edit'), 403, 'Unauthorized');
        $account_head = AccountHead::findOrFail($id);
        abort_if(!$account_head->is_editable, 403, 'Unauthorized');
        abort_if($account_head->sister_concern_id !== null && $account_head->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        $validatedData = $request->validate([
            'name' => ['required', 'max:255', Rule::unique('account_heads')->ignore($account_head)],
            'opening_balance' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $account_head->update(['name' => $validatedData['name']]);

            $existingObVoucher = Voucher::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('account_head_payee_depositor_id', $account_head->id)
                ->where('is_opening_balance', 1)
                ->first();

            if ($validatedData['opening_balance'] > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');
                $isDebitNature = true;

                if ($existingObVoucher) {
                    $existingObVoucher->amount = $validatedData['opening_balance'];
                    $existingObVoucher->date = $today;
                    $existingObVoucher->notes = 'Opening Balance - ' . $request->name;
                    $existingObVoucher->save();

                    Transaction::where('voucher_id', $existingObVoucher->id)
                        ->where('account_head_id', $account_head->id)
                        ->update([
                            'amount' => $validatedData['opening_balance'],
                            'date' => $today,
                            'notes' => 'Opening Balance - ' . $request->name,
                            'transaction_type' => TransactionType::$DEBIT
                        ]);

                    Transaction::where('voucher_id', $existingObVoucher->id)
                        ->where('account_head_id', $obHeadId)
                        ->update([
                            'amount' => $validatedData['opening_balance'],
                            'date' => $today,
                            'notes' => 'Opening Balance - ' . $request->name,
                            'transaction_type' => TransactionType::$CREDIT
                        ]);
                } else {
                    $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
                    $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

                    $voucher = new Voucher();
                    $voucher->sister_concern_id = auth()->user()->activeConcernId();
                    $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $voucher->amount = $validatedData['opening_balance'];
                    $voucher->date = $today;
                    $voucher->voucher_no = $voucherNo;
                    $voucher->voucher_no_group_sl = $voucherNoGroupSl;
                    $voucher->account_head_payee_depositor_id = $account_head->id;
                    $voucher->notes = 'Opening Balance - ' . $request->name;
                    $voucher->is_opening_balance = 1;
                    $voucher->user_id = auth()->id();
                    $voucher->save();

                    $tx1 = new Transaction();
                    $tx1->sister_concern_id = auth()->user()->activeConcernId();
                    $tx1->voucher_id = $voucher->id;
                    $tx1->account_head_id = $account_head->id;
                    $tx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $tx1->transaction_type = TransactionType::$DEBIT;
                    $tx1->amount = $validatedData['opening_balance'];
                    $tx1->date = $today;
                    $tx1->voucher_no = $voucherNo;
                    $tx1->voucher_no_group_sl = $voucherNoGroupSl;
                    $tx1->account_head_payee_depositor_id = $account_head->id;
                    $tx1->notes = 'Opening Balance - ' . $request->name;
                    $tx1->is_opening_balance = 1;
                    $tx1->user_id = auth()->id();
                    $tx1->save();

                    $tx2 = new Transaction();
                    $tx2->sister_concern_id = auth()->user()->activeConcernId();
                    $tx2->voucher_id = $voucher->id;
                    $tx2->account_head_id = $obHeadId;
                    $tx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                    $tx2->transaction_type = TransactionType::$CREDIT;
                    $tx2->amount = $validatedData['opening_balance'];
                    $tx2->date = $today;
                    $tx2->voucher_no = $voucherNo;
                    $tx2->voucher_no_group_sl = $voucherNoGroupSl;
                    $tx2->account_head_payee_depositor_id = $account_head->id;
                    $tx2->notes = 'Opening Balance - ' . $request->name;
                    $tx2->is_opening_balance = 1;
                    $tx2->user_id = auth()->id();
                    $tx2->save();
                }
            } else {
                if ($existingObVoucher) {
                    Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                    $existingObVoucher->delete();
                }
            }

            DB::commit();
            return redirect()->route('contractor_management.expense-heads.index')->with('success', 'Expense head updated successfully');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('contractor_management.expense-heads.edit', $id)->withInput()->with('error', 'An error occurred: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            abort_if(!auth()->user()->hasPermissionTo('contractor_expense_head_delete'), 403, 'Unauthorized');
            $account_head = AccountHead::findOrFail($id);

            if ($account_head->reserved_id > 0) {
                return response()->json(['success' => false, 'message' => 'This is a reserved account header. It cannot be deleted.'], 200);
            }

            $transaction = Transaction::where('account_head_id', $account_head->id)->where('is_opening_balance', 0)->first();
            if ($transaction) {
                return response()->json(['success' => false, 'message' => 'It has transaction logs. Cannot delete.'], 200);
            }
            
            $existingObVoucher = Voucher::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('account_head_payee_depositor_id', $account_head->id)
                ->where('is_opening_balance', 1)
                ->first();

            if ($existingObVoucher) {
                Transaction::where('voucher_id', $existingObVoucher->id)->delete();
                $existingObVoucher->delete();
            }

            $account_head->delete();
            return response()->json(['success' => true, 'message' => 'Expense head deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 200);
        }
    }
}
