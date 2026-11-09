<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Enumeration\ReservedAccountHead;
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

class AccountHeadController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes' : 'account_head';
        $permissionCreate = $request->query('payment_mode') == 1 ? 'payment_modes_create' : 'account_head_create';
        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');
        $sister_concerns = SisterConcern::where('status',1)->get();
        $paymentMode = $request->payment_mode;
        if ($request->payment_mode == 0){
            $accountHeadTitle = 'Account Head';
        }else{
            $accountHeadTitle = 'Payment Mode';
        }

        return view('accounts.account_head.index',compact('paymentMode',
        'accountHeadTitle','permissionCreate','sister_concerns'));
    }
    public function dataTable()
    {

        $query = AccountHead::with('accountGroup')->where(function($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        });
        if (request('payment_mode') == 0){
            $query->where('payment_mode',0);
        }else{
            $query->where('payment_mode','>',0);
        }
        $paymentMode = request('payment_mode');
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function(AccountHead $accountHead) use ($paymentMode) {
                $btn ='';
                $permissionDelete = $accountHead->payment_mode > 0 ? 'payment_modes_edit' : 'account_head_edit';
                $permissionEdit = $accountHead->payment_mode > 0 ? 'payment_modes_delete' : 'account_head_delete';

                if(auth()->user()->can($permissionDelete) && $accountHead->is_editable) {
                    $btn.='<a href="'.route('account-head.edit',['account_head'=>$accountHead->id,'payment_mode'=>$paymentMode]).'" class="btn btn-info bg-gradient-info btn-sm btn-edit"><i class="fa fa-edit"></i></a>';
                }

                if(auth()->user()->can($permissionEdit) && $accountHead->is_editable) {
                    $btn .=' <a role="button" data-id="'.$accountHead->id.'" class="btn btn-danger btn-sm btn-delete"><i class="fa fa-trash"></i></a>';
                }
                return $btn;
            })
            ->addColumn('account_group_name', function(AccountHead $accountHead){
                return $accountHead->accountGroup->name ?? '';

            })
            ->addColumn('opening_balance', function(AccountHead $accountHead) {
                return $accountHead->getOpeningBalance(auth()->user()->activeConcernId());
            })
            ->addColumn('current_balance', function(AccountHead $accountHead) {
                return $accountHead->getCurrentBalance(auth()->user()->activeConcernId());
            })
            ->rawColumns(['action'])
            ->toJson();
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_create' : 'account_head_create';
        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');

        $paymentMode = $request->payment_mode;
        if ($request->payment_mode == 0){
            $accountHeadTitle = 'Account Head';
        }else{
            $accountHeadTitle = 'Payment Mode';

        }
        $accountGroups = AccountGroup::where('is_leaf', 1)
            ->orderBy('name')
            ->get();
            
        $sister_concerns = SisterConcern::where('status',1)->get();

        return view('accounts.account_head.create',compact('paymentMode',
            'accountHeadTitle','accountGroups', 'sister_concerns'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_create' : 'account_head_create';
        abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');

        // Validate the request data
        $rules = [
            'name' =>[
                'required','max:255',
                Rule::unique('account_heads')
            ],
            'account_group'=>'required',
            'opening_balance'=>'required|numeric|min:0',
        ];
        if ($request->payment_mode == 1){
            $rules['payment_mode_type'] = 'required';
            $rules['bank_commission_percent'] = 'required|numeric|min:0';
        }
        $validatedData = $request->validate($rules);



        // Start a database transaction
        DB::beginTransaction();

        try {
            $group = AccountGroup::find($request->account_group);
            $topReservedId = $group->top_reserved_id;

            $maxCodeInTopParent = AccountHead::where('code', 'like', $topReservedId . '%')
                ->max('code');

            if ($maxCodeInTopParent) {
                $validatedData['code'] = $maxCodeInTopParent + 1;
            } else {
                $validatedData['code'] = $topReservedId . '001';
            }

            if (in_array($topReservedId, [1, 5])) {
                $validatedData['nature'] = 'Debit';
            } else {
                $validatedData['nature'] = 'Credit';
            }

            $validatedData['payment_mode'] = $request->payment_mode_type == '' ? 0 : $request->payment_mode_type;
            unset($validatedData['payment_mode_type']);
            
            if ($request->payment_mode == 1) {
                $validatedData['sister_concern_id'] = $request->sister_concern_id ?: null;
            } else {
                $validatedData['sister_concern_id'] = null;
            }
            
            $validatedData['account_group_id'] = $request->account_group;
            unset($validatedData['account_group']); // Remove the 'account_group' key
            unset($validatedData['opening_balance']);
            
            // Create a new Account Head record in the database
            $accountHead = AccountHead::create($validatedData);

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
                $voucher->notes = 'Opening Balance - ' . $request->name;
                $voucher->is_opening_balance = 1;
                $voucher->user_id = auth()->id();
                $voucher->save();

                // Debit/Credit based on Account Nature
                // Assets (1) and Expenses (5) are Debit nature
                // Liabilities (2), Equity (3), Revenue (4) are Credit nature
                $isDebitNature = in_array($topReservedId, [1, 5]);

                // Transaction 1: The Account Head itself
                $tx1 = new Transaction();
                $tx1->sister_concern_id = auth()->user()->activeConcernId();
                $tx1->voucher_id = $voucher->id;
                $tx1->account_head_id = $accountHead->id;
                $tx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $tx1->transaction_type = $isDebitNature ? TransactionType::$DEBIT : TransactionType::$CREDIT;
                $tx1->amount = $request->opening_balance;
                $tx1->date = $today;
                $tx1->voucher_no = $voucherNo;
                $tx1->voucher_no_group_sl = $voucherNoGroupSl;
                $tx1->account_head_payee_depositor_id = $accountHead->id;
                $tx1->notes = 'Opening Balance - ' . $request->name;
                $tx1->is_opening_balance = 1;
                $tx1->user_id = auth()->id();
                $tx1->save();

                // Transaction 2: Opening Balance Equity (opposite side)
                $tx2 = new Transaction();
                $tx2->sister_concern_id = auth()->user()->activeConcernId();
                $tx2->voucher_id = $voucher->id;
                $tx2->account_head_id = $obHeadId;
                $tx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $tx2->transaction_type = $isDebitNature ? TransactionType::$CREDIT : TransactionType::$DEBIT;
                $tx2->amount = $request->opening_balance;
                $tx2->date = $today;
                $tx2->voucher_no = $voucherNo;
                $tx2->voucher_no_group_sl = $voucherNoGroupSl;
                $tx2->account_head_payee_depositor_id = $accountHead->id;
                $tx2->notes = 'Opening Balance - ' . $request->name;
                $tx2->is_opening_balance = 1;
                $tx2->user_id = auth()->id();
                $tx2->save();
            }

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('account-head.index',['payment_mode'=>$request->payment_mode])->withInput()->with('success', 'Account head added successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('account-head.create',['payment_mode'=>$request->payment_mode])->withInput()->with('error', 'An error occurred while creating the Account head: '.$e->getMessage());
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(AccountHead $account_head,Request $request)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_edit' : 'account_head_edit';
        abort_if(!auth()->user()->hasPermissionTo($permission) && !$account_head->is_editable, 403, 'Unauthorized');
        abort_if($account_head->sister_concern_id !== null && $account_head->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        $paymentMode = $request->payment_mode;
        if ($request->payment_mode == 0){
            $accountHeadTitle = 'Account Head';
        }else{
            $accountHeadTitle = 'Payment Mode';

        }
        $accountGroups = AccountGroup::where('is_leaf', 1)
            ->orderBy('name')
            ->get();
            
        $sister_concerns = \App\Models\BranchManagement\SisterConcern::where('status',1)->get();
        try {
            // If the Account head exists, display the edit view
            return view('accounts.account_head.edit', compact('account_head','paymentMode','accountHeadTitle','accountGroups', 'sister_concerns'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            // Handle the case where the Account head is not found
            return redirect()->route('account-head.index')->with('error', 'Account head not found: '.$e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, AccountHead $account_head)
    {

        $permission = $request->query('payment_mode') == 1 ? 'payment_modes_edit' : 'account_head_edit';
        abort_if(!auth()->user()->hasPermissionTo($permission) && !$account_head->is_editable, 403, 'Unauthorized');
        abort_if($account_head->sister_concern_id !== null && $account_head->sister_concern_id != auth()->user()->activeConcernId(), 403, 'Unauthorized');

        // Validate the request data
        $rules = [
            'name' =>[
                'required','max:255',
                Rule::unique('account_heads')
                    ->ignore($account_head)
            ],
            'account_group'=>'required',
            'opening_balance'=>'required|numeric|min:0',
        ];
        if ($request->payment_mode == 1){
            $rules['payment_mode_type'] = 'required';
            $rules['bank_commission_percent'] = 'required|numeric|min:0';
        }
        $validatedData = $request->validate($rules);

        // Start a database transaction
        DB::beginTransaction();

        try {

            $group = AccountGroup::find($request->account_group);
            $topReservedId = $group->top_reserved_id;
            
            if (in_array($topReservedId, [1, 5])) {
                $validatedData['nature'] = 'Debit';
            } else {
                $validatedData['nature'] = 'Credit';
            }

            if ($request->payment_mode == 1) {
                $validatedData['sister_concern_id'] = $request->sister_concern_id ?: null;
            } else {
                $validatedData['sister_concern_id'] = null;
            }
            
            $validatedData['account_group_id'] = $request->account_group;
            $validatedData['payment_mode'] = $request->payment_mode_type == '' ? 0 : $request->payment_mode_type;
            
            unset($validatedData['account_group']);
            unset($validatedData['payment_mode_type']);
            unset($validatedData['opening_balance']);
            $account_head->update($validatedData);

            // --- Opening Balance Journal Entry (update or create) ---
            $existingObVoucher = Voucher::where('sister_concern_id', auth()->user()->activeConcernId())
                ->where('account_head_payee_depositor_id', $account_head->id)
                ->where('is_opening_balance', 1)
                ->first();

            if ($request->opening_balance > 0) {
                $obHeadId = reservedAccountHeadId(ReservedAccountHead::OPENING_BALANCE_EQUITY);
                $activeSisterConcern = SisterConcern::find(auth()->user()->activeConcernId());
                $today = $activeSisterConcern && $activeSisterConcern->business_date ? Carbon::parse($activeSisterConcern->business_date)->subDay()->format('Y-m-d') : Carbon::now()->subDay()->format('Y-m-d');
                $isDebitNature = in_array($topReservedId, [1, 5]);
 
                if ($existingObVoucher) {
                    $existingObVoucher->amount = $request->opening_balance;
                    $existingObVoucher->date = $today;
                    $existingObVoucher->notes = 'Opening Balance - ' . $request->name;
                    $existingObVoucher->save();

                    Transaction::where('voucher_id', $existingObVoucher->id)
                        ->where('account_head_id', $account_head->id)
                        ->update([
                            'amount' => $request->opening_balance,
                            'date' => $today,
                            'notes' => 'Opening Balance - ' . $request->name,
                            'transaction_type' => $isDebitNature ? TransactionType::$DEBIT : TransactionType::$CREDIT
                        ]);

                    Transaction::where('voucher_id', $existingObVoucher->id)
                        ->where('account_head_id', $obHeadId)
                        ->update([
                            'amount' => $request->opening_balance,
                            'date' => $today,
                            'notes' => 'Opening Balance - ' . $request->name,
                            'transaction_type' => $isDebitNature ? TransactionType::$CREDIT : TransactionType::$DEBIT
                        ]);
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
                    $tx1->transaction_type = $isDebitNature ? TransactionType::$DEBIT : TransactionType::$CREDIT;
                    $tx1->amount = $request->opening_balance;
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
                    $tx2->transaction_type = $isDebitNature ? TransactionType::$CREDIT : TransactionType::$DEBIT;
                    $tx2->amount = $request->opening_balance;
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

            // Commit the transaction
            DB::commit();

            // Redirect to the index page with a success message
            return redirect()->route('account-head.index',['payment_mode'=>$request->payment_mode])->withInput()->with('success', 'Account head updated successfully');
        } catch (\Exception $e) {
            // Roll back the transaction in case of an error
            DB::rollback();

            // Handle the error and redirect with an error message
            return redirect()->route('account-head.edit',['account_head'=>$account_head->id,'payment_mode'=>$request->payment_mode])->withInput()
                ->with('error', 'An error occurred while updating the Account head: '.$e->getMessage());
        }

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AccountHead $account_head)
    {
        try {
            $permission = $account_head->payment_mode > 0 ? 'payment_modes_delete' : 'account_head_delete';
            abort_if(!auth()->user()->hasPermissionTo($permission), 403, 'Unauthorized');
            if ($account_head->reserved_id > 0){
                return response()->json(['success' => false, 'message' => 'This is a reserved account header. It cannot be deleted.'], Response::HTTP_OK);
            }

            $transaction = Transaction::where('account_head_id',$account_head->id)->first();
            if ($transaction) {
                // If a related AccountHead exists, return an error message
                return response()->json(['success' => false, 'message' => 'It has transaction logs. Cannot delete.'], Response::HTTP_OK);
            }
            // Delete the Account Head record
            $account_head->delete();
            // Return a JSON success response
            return response()->json(['success'=>true,'message' => 'Account head deleted successfully'], Response::HTTP_OK);
        } catch (\Exception $e) {
            // Handle any errors, such as record not found
            return response()->json(['success'=>false,'message' => 'Account head not found: '.$e->getMessage()], Response::HTTP_OK);
        }
    }
}
