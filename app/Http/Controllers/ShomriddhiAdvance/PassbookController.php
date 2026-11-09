<?php

namespace App\Http\Controllers\ShomriddhiAdvance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiPassbook;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class PassbookController extends Controller
{
    public function index()
    {
        $pageTitle = 'Manage Passbooks';
        $accounts = ShomriddhiAccount::with('customer')->orderBy('id', 'desc')->get();
        return view('shomriddhi_advance.passbooks.index', compact('pageTitle', 'accounts'));
    }

    public function dataTable(Request $request)
    {
        $passbooks = ShomriddhiPassbook::with(['account.customer', 'issuer']);

        if ($request->filled('account_id')) {
            $passbooks->where('shomriddhi_account_id', $request->account_id);
        }

        $passbooks->orderBy('id', 'desc');

        return DataTables::of($passbooks)
            ->addIndexColumn()
            ->addColumn('account_number', function ($passbook) {
                return $passbook->account ? $passbook->account->account_number : '-';
            })
            ->addColumn('customer_name', function ($passbook) {
                return $passbook->account && $passbook->account->customer ? $passbook->account->customer->name : '-';
            })
            ->addColumn('member_no', function ($passbook) {
                return $passbook->account && $passbook->account->customer ? $passbook->account->customer->member_no : '-';
            })
            ->filterColumn('customer_name', function($query, $keyword) {
                $query->whereHas('account.customer', function($q) use ($keyword) {
                    $q->where('name', 'like', "%{$keyword}%")
                      ->orWhere('mobile_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('member_no', function($query, $keyword) {
                $query->whereHas('account.customer', function($q) use ($keyword) {
                    $q->where('member_no', 'like', "%{$keyword}%");
                });
            })
            ->filterColumn('account_number', function($query, $keyword) {
                $query->whereHas('account', function($q) use ($keyword) {
                    $q->where('account_number', 'like', "%{$keyword}%");
                });
            })
            ->editColumn('issued_at', function ($passbook) {
                return $passbook->created_at->format('d-M-Y H:i A');
            })
            ->editColumn('status', function ($passbook) {
                $badges = [
                    'Active' => 'success',
                    'Filled' => 'info',
                    'Lost' => 'danger',
                    'Damaged' => 'warning'
                ];
                $color = $badges[$passbook->status] ?? 'secondary';
                return '<span class="badge badge-'.$color.'">' . $passbook->status . '</span>';
            })
            ->addColumn('action', function ($passbook) {
                $btn = '';
                if ($passbook->status == 'Active') {
                    $blankUrl = route('shomriddhi-advance.accounts.blank_passbook', $passbook->shomriddhi_account_id);
                    
                    $btn .= '<a href="' . $blankUrl . '" target="_blank" class="btn btn-sm btn-info" title="Print Passbook"><i class="fas fa-print"></i> Print Passbook</a> ';
                    
                    $statusUrl = route('shomriddhi-advance.passbooks.update_status', $passbook->id);
                    $btn .= '<button type="button" class="btn btn-sm btn-warning change-status-btn" data-url="'.$statusUrl.'" title="Change Status"><i class="fas fa-sync"></i> Void/Replace</button>';
                }
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'account_id' => 'required|exists:shomriddhi_accounts,id',
            'reason' => 'nullable|string|max:255'
        ]);

        $account = ShomriddhiAccount::findOrFail($request->account_id);

        if ($account->activePassbook) {
            return response()->json([
                'success' => false, 
                'message' => 'An Active Passbook already exists for this account! Please change its status (Lost/Damaged/Filled) first.'
            ]);
        }

        DB::beginTransaction();
        try {

            // Generate new Passbook Number
            $count = ShomriddhiPassbook::where('shomriddhi_account_id', $account->id)->count();
            $nextCount = str_pad($count + 1, 2, '0', STR_PAD_LEFT);
            $pbNumber = $account->account_number . '-P-' . $nextCount;

            ShomriddhiPassbook::create([
                'shomriddhi_account_id' => $account->id,
                'passbook_number' => $pbNumber,
                'status' => 'Active',
                'reason' => $request->reason,
                'issued_by' => auth()->id()
            ]);

            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Passbook issued successfully! Passbook No: ' . $pbNumber
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        $passbook = ShomriddhiPassbook::findOrFail($id);

        $request->validate([
            'status' => 'required|in:Lost,Damaged,Filled',
            'reason' => 'nullable|string|max:255'
        ]);

        DB::beginTransaction();
        try {
            $passbook->update([
                'status' => $request->status,
                'reason' => $request->reason
            ]);
            
            DB::commit();
            return response()->json([
                'success' => true, 
                'message' => 'Passbook status updated successfully. You can now issue a new one.'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
