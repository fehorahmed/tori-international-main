<?php

namespace App\Http\Controllers\ContractorManagement;

use App\Http\Controllers\Controller;
use App\Models\ContractorManagement\Contractor;
use App\Models\ContractorManagement\ContractorBill;
use App\Models\ContractorManagement\ContractorBillDetail;
use App\Models\Account\AccountHead;
use App\Models\Account\AccountGroup;
use App\Models\Account\Voucher;
use App\Models\Account\Transaction;
use App\Models\BranchManagement\SisterConcern;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;

class ContractorBillController extends Controller
{
    public function index()
    {
        $contractors = Contractor::where('status', 1)->where(function ($q) {
            $q->whereNull('origin_sister_concern_id')->orWhere('origin_sister_concern_id', auth()->user()->activeConcernId());
        })->get();
        return view('contractor_management.bills.index', compact('contractors'));
    }

    public function dataTable(Request $request)
    {
        $query = ContractorBill::with('contractor')->where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        });

        if ($request->contractor_id) {
            $query->where('contractor_id', $request->contractor_id);
        }

        if ($request->start_date && $request->end_date) {
            $startDate = Carbon::parse($request->start_date)->format('Y-m-d');
            $endDate = Carbon::parse($request->end_date)->format('Y-m-d');
            $query->whereBetween('bill_date', [$startDate, $endDate]);
        }

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('contractor_name', function (ContractorBill $bill) {
                return $bill->contractor->name ?? '';
            })
            ->addColumn('bill_date', function (ContractorBill $bill) {
                return Carbon::parse($bill->bill_date)->format('d-m-Y');
            })
            ->addColumn('action', function (ContractorBill $bill) {
                $btn = '<a href="' . route('contractor_management.bills.show', $bill->id) . '" class="btn btn-info btn-sm bg-gradient-info"><i class="fa fa-eye"></i></a>';
                $btn .= ' <a role="button" data-id="' . $bill->id . '" class="btn btn-danger btn-sm bg-gradient-danger btn-delete"><i class="fa fa-trash"></i></a>';
                return dropdownMenuContainer($btn);
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create()
    {
        $contractors = Contractor::where('status', 1)->where(function ($q) {
            $q->whereNull('origin_sister_concern_id')->orWhere('origin_sister_concern_id', auth()->user()->activeConcernId());
        })->get();

        $group = AccountGroup::where('reserved_id', ReservedAccountGroup::OPERATING_EXPENSES)->first();
        $expenseHeads = AccountHead::where('account_group_id', $group->id)->where(function ($q) {
            $q->whereNull('sister_concern_id')->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->get();

        return view('contractor_management.bills.create', compact('contractors', 'expenseHeads'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'contractor_id' => 'required|exists:contractors,id',
            'bill_date' => 'required|date',
            'expense_head_id' => 'required|array',
            'expense_head_id.*' => 'required|exists:account_heads,id',
            'amount' => 'required|array',
            'amount.*' => 'required|numeric|min:1',
            'note' => 'nullable|string'
        ]);

        DB::beginTransaction();

        try {
            $totalAmount = array_sum($request->amount);

            $bill = new ContractorBill();
            $bill->contractor_id = $request->contractor_id;
            $bill->sister_concern_id = auth()->user()->activeConcernId();
            $bill->bill_date = Carbon::parse($request->bill_date)->format('Y-m-d');
            $bill->bill_no = 'CB-' . date('YmdHis') . rand(10, 99);
            $bill->total_amount = $totalAmount;
            $bill->note = $request->note;
            $bill->created_by = auth()->id();
            $bill->save();

            foreach ($request->expense_head_id as $key => $head_id) {
                $detail = new ContractorBillDetail();
                $detail->contractor_bill_id = $bill->id;
                $detail->expense_head_id = $head_id;
                $detail->amount = $request->amount[$key];
                $detail->remarks = $request->remarks[$key] ?? null;
                $detail->save();
            }

            // Post to GL
            $contractor = Contractor::findOrFail($request->contractor_id);

            $voucherNo = generateVoucherNo(VoucherType::$JOURNAL_VOUCHER);
            $voucherNoGroupSl = abs((int) filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

            $voucher = new Voucher();
            $voucher->sister_concern_id = auth()->user()->activeConcernId();
            $voucher->voucher_no = $voucherNo;
            $voucher->voucher_no_group_sl = $voucherNoGroupSl;
            $voucher->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $voucher->account_head_payee_depositor_id = $contractor->account_head_id;
            $voucher->date = $bill->bill_date;
            $voucher->amount = $totalAmount;
            $voucher->notes = 'Contractor Bill No: ' . $bill->bill_no;
            $voucher->user_id = auth()->id();
            $voucher->save();

            $bill->voucher_id = $voucher->id;
            $bill->save();

            // Credit the Contractor
            $tx1 = new Transaction();
            $tx1->sister_concern_id = auth()->user()->activeConcernId();
            $tx1->voucher_id = $voucher->id;
            $tx1->account_head_id = $contractor->account_head_id;
            $tx1->voucher_type = VoucherType::$JOURNAL_VOUCHER;
            $tx1->transaction_type = TransactionType::$CREDIT;
            $tx1->amount = $totalAmount;
            $tx1->date = $bill->bill_date;
            $tx1->voucher_no = $voucherNo;
            $tx1->voucher_no_group_sl = $voucherNoGroupSl;
            $tx1->notes = 'Contractor Bill No: ' . $bill->bill_no;
            $tx1->user_id = auth()->id();
            $tx1->save();

            // Debit the Expense Heads
            foreach ($request->expense_head_id as $key => $head_id) {
                $tx2 = new Transaction();
                $tx2->sister_concern_id = auth()->user()->activeConcernId();
                $tx2->voucher_id = $voucher->id;
                $tx2->account_head_id = $head_id;
                $tx2->voucher_type = VoucherType::$JOURNAL_VOUCHER;
                $tx2->transaction_type = TransactionType::$DEBIT;
                $tx2->amount = $request->amount[$key];
                $tx2->date = $bill->bill_date;
                $tx2->voucher_no = $voucherNo;
                $tx2->voucher_no_group_sl = $voucherNoGroupSl;
                $tx2->account_head_payee_depositor_id = $contractor->account_head_id;
                $tx2->notes = 'Contractor Bill No: ' . $bill->bill_no . ($request->remarks[$key] ? ' - ' . $request->remarks[$key] : '');
                $tx2->user_id = auth()->id();
                $tx2->save();
            }

            DB::commit();
            return redirect()->route('contractor_management.bills.index')->with('success', 'Contractor bill added successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->withInput()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $bill = ContractorBill::with('contractor', 'details.expenseHead')->findOrFail($id);
        $voucher = Voucher::find($bill->voucher_id);
        
        if (!$voucher) {
            $voucher = Voucher::where('notes', 'Contractor Bill No: ' . $bill->bill_no)->first();
        }
        
        return view('contractor_management.bills.show', compact('bill', 'voucher'));
    }

    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $bill = ContractorBill::findOrFail($id);
            
            if ($bill->voucher_id) {
                $voucher = Voucher::find($bill->voucher_id);
                if ($voucher) {
                    Transaction::where('voucher_id', $voucher->id)->delete();
                    $voucher->delete();
                }
            } else {
                // Fallback for old records
                $voucher = Voucher::where('notes', 'Contractor Bill No: ' . $bill->bill_no)->first();
                if ($voucher) {
                    Transaction::where('voucher_id', $voucher->id)->delete();
                    $voucher->delete();
                }
            }

            ContractorBillDetail::where('contractor_bill_id', $bill->id)->delete();
            $bill->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Contractor bill deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }
}
