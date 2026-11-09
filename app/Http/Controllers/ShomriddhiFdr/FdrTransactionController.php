<?php

namespace App\Http\Controllers\ShomriddhiFdr;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiFdr\FdrTransaction;

use Yajra\DataTables\Facades\DataTables;

class FdrTransactionController extends Controller
{
    public function index()
    {
        return view('shomriddhi_fdr.transactions.index');
    }

    public function dataTable(Request $request)
    {
        $query = FdrTransaction::with(['account.customer'])
            ->join('fdr_accounts', 'fdr_transactions.fdr_account_id', '=', 'fdr_accounts.id')
            ->where('fdr_accounts.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->select('fdr_transactions.*')
            ->orderBy('fdr_transactions.date', 'desc')
            ->orderBy('fdr_transactions.id', 'desc');

        if ($request->has('type') && $request->type != '') {
            $query->where('fdr_transactions.type', $request->type);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('date', function ($row) {
                return \Carbon\Carbon::parse($row->date)->format('d-M-Y');
            })
            ->addColumn('account_no', function ($row) {
                return '<span class="font-weight-bold">' . ($row->account ? $row->account->fdr_no : 'N/A') . '</span>';
            })
            ->addColumn('customer', function ($row) {
                if ($row->account && $row->account->customer) {
                    return e($row->account->customer->name) . '<br><small class="text-muted">' . e($row->account->customer->phone) . '</small>';
                }
                return '-';
            })
            ->addColumn('type', function ($row) {
                if ($row->type == 'Deposit') {
                    return '<span class="badge badge-success"><i class="fas fa-arrow-down"></i> Deposit</span>';
                } elseif ($row->type == 'Encashment') {
                    return '<span class="badge badge-danger"><i class="fas fa-arrow-up"></i> Encashment</span>';
                } elseif ($row->type == 'Interest Payout') {
                    return '<span class="badge badge-primary"><i class="fas fa-gift"></i> Interest Payout</span>';
                } else {
                    return '<span class="badge badge-warning">' . e($row->type) . '</span>';
                }
            })
            ->addColumn('amount', function ($row) {
                return '<span class="font-weight-bold text-dark">৳ ' . number_format($row->amount, 2) . '</span>';
            })
            ->addColumn('note', function ($row) {
                return $row->note ?? '-';
            })
            ->rawColumns(['account_no', 'customer', 'type', 'amount'])
            ->make(true);
    }
}
