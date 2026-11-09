<?php

namespace App\Http\Controllers\Microfinance;

use App\Http\Controllers\Controller;
use App\Models\Microfinance\MfInstallment;
use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSomiti;
use App\Models\Microfinance\MfSavingsTransaction;
use App\Models\Microfinance\MfSavingsAccount;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class CollectionController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_collection'), 403, 'Unauthorized');
        $pageTitle = 'Loan Installments (Due)';
        return view('microfinance.collection.index', compact('pageTitle'));
    }

    public function dueInstallmentsDataTable()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_collection'), 403, 'Unauthorized');
        $query = MfInstallment::with('loan.member', 'loan.product')
            ->where('status', '!=', 2)
            ->where('sister_concern_id', auth()->user()->activeConcernId());

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('action', function (MfInstallment $installment) {
                return '<button data-id="' . $installment->id . '" data-amount="' . $installment->total_due . '" class="btn btn-success btn-sm btn-collect">Collect</button>';
            })
            ->editColumn('due_date', function(MfInstallment $installment) {
                return date('d M, Y', strtotime($installment->due_date));
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function sheet()
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_collection_sheet'), 403, 'Unauthorized');
        $pageTitle = 'Master Collection Sheet (মাস্টার কালেকশন শিট)';
        $somitis = MfSomiti::where('sister_concern_id', auth()->user()->activeConcernId())->where('status', 1)->get();
        return view('microfinance.collection.sheet', compact('pageTitle', 'somitis'));
    }

    public function fetchSheet(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_collection_sheet'), 403, 'Unauthorized');
        $somitiId = $request->somiti_id;
        
        try {
            $date = \Carbon\Carbon::parse($request->date)->toDateString();
        } catch (\Exception $e) {
            $date = \Carbon\Carbon::today()->toDateString();
        }

        $members = MfMember::with([
            'somiti',
            'loans' => function($q) {
                $q->where('status', 3); // Active loans
            },
            'loans.product',
            'loans.installments' => function($q) use ($date) {
                $q->where('due_date', '<=', $date)->where('status', '!=', 2);
            },
            'savingsAccounts' => function($q) {
                $q->where('status', 1)
                  ->withCount(['transactions as total_deposits' => function($sq) {
                      $sq->where('transaction_type', 1);
                  }]);
            },
            'savingsAccounts.product',
            'savingsAccounts.transactions' => function($q) use ($date) {
                $carbonDate = \Carbon\Carbon::parse($date);
                $monthStart = $carbonDate->copy()->startOfMonth()->toDateString();
                $monthEnd = $carbonDate->copy()->endOfMonth()->toDateString();
                $q->where('transaction_type', 1)
                  ->whereBetween('date', [$monthStart, $monthEnd]);
            }
        ])
        ->where('somiti_id', $somitiId)
        ->get();

        return view('microfinance.collection._sheet_data', compact('members', 'date'))->render();
    }

    public function saveSheet(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_collection_sheet'), 403, 'Unauthorized');
        $request->validate([
            'date'       => 'required|date',
            'collection' => 'required|array',
        ]);

        $data     = $request->input('collection');
        $date     = $request->input('date');
        $scId     = auth()->user()->activeConcernId();
        $saved    = 0;
        $skipped  = 0;

        try {
            DB::beginTransaction();

            foreach ($data as $memberId => $entries) {

                // 1. Process Loan Installments
                if (isset($entries['loans'])) {
                    foreach ($entries['loans'] as $installmentId => $paidAmount) {
                        $paidAmount = floatval($paidAmount);
                        if ($paidAmount <= 0) continue;

                        $installment = MfInstallment::with('loan.member')->find($installmentId);

                        // Safety: skip if not found or not owned by this company
                        if (!$installment || $installment->sister_concern_id !== $scId) {
                            $skipped++;
                            continue;
                        }

                        // ── Double payment guard ──
                        if ($installment->status === 2) {
                            $skipped++;
                            continue;
                        }

                        // Partial or full payment
                        $remaining  = $installment->total_due - $installment->paid_amount;

                        // Check if paidAmount exceeds remaining due
                        if ($paidAmount > $remaining) {
                            $memberName = $installment->loan?->member?->name ?? 'সদস্য';
                            $memberNo = $installment->loan?->member?->member_no ?? '';
                            $installmentNo = $installment->installment_no ?? '';
                            throw new \Exception("ভুল কিস্তি কালেকশন! {$memberName} ({$memberNo}) এর কিস্তি নং {$installmentNo} এর জন্য বকেয়া পরিমাণ হল ৳" . number_format($remaining, 2) . ", কিন্তু আদায় করা হয়েছে ৳" . number_format($paidAmount, 2));
                        }

                        $newPaid    = $installment->paid_amount + $paidAmount;
                        $newStatus  = $newPaid >= $installment->total_due ? 2 : 3; // 2=Paid, 3=Partial

                        $installment->update([
                            'paid_amount'    => min($newPaid, $installment->total_due),
                            'paid_date'      => $date,
                            'status'         => $newStatus,
                            'collected_by'   => auth()->id(),
                        ]);

                        // Auto-close loan when all installments paid
                        if ($newStatus === 2) {
                            $loan = $installment->loan;
                            if ($loan && $loan->installments()->whereIn('status', [1, 3])->count() === 0) {
                                $loan->update(['status' => 4]); // Closed
                            }
                        }

                        $saved++;
                    }
                }

                // 2. Process Savings Deposits
                if (isset($entries['savings'])) {
                    foreach ($entries['savings'] as $accountId => $depositAmount) {
                        $depositAmount = floatval($depositAmount);
                        if ($depositAmount <= 0) continue;

                        $account = MfSavingsAccount::find($accountId);

                        if (!$account || $account->sister_concern_id !== $scId) {
                            $skipped++;
                            continue;
                        }

                        if ($account->status !== 1) {
                            $skipped++;
                            continue;
                        }

                        MfSavingsTransaction::create([
                            'sister_concern_id'      => $scId,
                            'somiti_id'              => $account->somiti_id,
                            'mf_savings_account_id'  => $account->id,
                            'transaction_type'       => MfSavingsTransaction::TYPE_DEPOSIT,
                            'amount'                 => $depositAmount,
                            'date'                   => $date,
                            'created_by'             => auth()->id(),
                        ]);

                        $account->increment('current_balance', $depositAmount);
                        $saved++;
                    }
                }

                // 3. Process Savings Late Penalties
                if (isset($entries['penalties'])) {
                    foreach ($entries['penalties'] as $accountId => $penaltyAmount) {
                        $penaltyAmount = floatval($penaltyAmount);
                        if ($penaltyAmount <= 0) continue;

                        $account = MfSavingsAccount::find($accountId);

                        if (!$account || $account->sister_concern_id !== $scId) {
                            $skipped++;
                            continue;
                        }

                        if ($account->status !== 1) {
                            $skipped++;
                            continue;
                        }

                        MfSavingsTransaction::create([
                            'sister_concern_id'      => $scId,
                            'somiti_id'              => $account->somiti_id,
                            'mf_savings_account_id'  => $account->id,
                            'transaction_type'       => 4, // TYPE_PENALTY
                            'amount'                 => $penaltyAmount,
                            'date'                   => $date,
                            'created_by'             => auth()->id(),
                        ]);

                        $account->decrement('current_balance', $penaltyAmount);
                        $saved++;
                    }
                }
            }

            DB::commit();

            $message = "Collection saved! ({$saved} records processed" . ($skipped ? ", {$skipped} skipped" : '') . ')';
            return response()->json(['success' => true, 'message' => $message]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Single installment collect (from due list page)
     */
    public function collect(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('mf_collection'), 403, 'Unauthorized');
        $request->validate([
            'installment_id' => 'required|exists:mf_installments,id',
            'paid_amount'    => 'required|numeric|min:0.01',
            'paid_date'      => 'required|date',
        ]);

        $scId        = auth()->user()->activeConcernId();
        $installment = MfInstallment::with('loan.member')->findOrFail($request->installment_id);

        if ($installment->sister_concern_id !== $scId) {
            return response()->json(['success' => false, 'message' => 'অননুমোদিত অ্যাক্সেস।'], 403);
        }

        if ($installment->status === 2) {
            return response()->json(['success' => false, 'message' => 'কিস্তিটি ইতিমধ্যে সম্পূর্ণ পরিশোধিত।'], 422);
        }

        // Validate overpayment
        $remaining = $installment->total_due - $installment->paid_amount;
        if ($request->paid_amount > $remaining) {
            return response()->json([
                'success' => false,
                'message' => 'প্রদেয় পরিমাণ বাকি বকেয়া টাকার চেয়ে বেশি। বকেয়া পরিমাণ হল ৳' . number_format($remaining, 2)
            ], 422);
        }

        try {
            DB::beginTransaction();

            $newPaid   = $installment->paid_amount + $request->paid_amount;
            $newStatus = $newPaid >= $installment->total_due ? 2 : 3;

            $installment->update([
                'paid_amount'  => min($newPaid, $installment->total_due),
                'paid_date'    => $request->paid_date,
                'status'       => $newStatus,
                'collected_by' => auth()->id(),
            ]);

            if ($newStatus === 2) {
                $loan = $installment->loan;
                if ($loan && $loan->installments()->whereIn('status', [1, 3])->count() === 0) {
                    $loan->update(['status' => 4]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => $newStatus === 2 ? 'কিস্তি সম্পূর্ণ পরিশোধিত হয়েছে।' : 'আংশিক কিস্তি পরিশোধ সফল হয়েছে।'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'সংরক্ষণ করতে ব্যর্থ হয়েছে: ' . $e->getMessage()
            ], 500);
        }
    }
}
