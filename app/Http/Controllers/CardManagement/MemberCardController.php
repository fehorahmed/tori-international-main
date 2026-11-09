<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\CardManagement\Card;
use App\Models\CardManagement\CardPrintLog;
use App\Models\CardManagement\CardSetting;
use App\Models\Microfinance\MfMember;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use App\Models\Account\AccountGroup;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountGroup;
use App\Enumeration\SupplierType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class MemberCardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card'), 403);
        $pageTitle = 'Member Card Management';
        
        // Get users who generated member cards
        $users = User::where('status',1)->get();
        
        // Get members who have cards
        $members = Supplier::whereHas('cards', function($q) {
                $q->where('cardable_type', Supplier::class);
            })
            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
            ->get(['id', 'name', 'member_no']);
        
        return view('card_management.member.index', compact('pageTitle', 'users', 'members'));
    }

    public function dataTable(Request $request)
    {
        $cards = Card::where('cardable_type', Supplier::class)
            ->where('cards.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->with(['cardable', 'creator'])
            ->orderBy('cards.id', 'DESC');
            
        if ($request->user_id) {
            $cards->where('created_by', $request->user_id);
        }
        
        if ($request->member_id) {
            $cards->where('cardable_id', $request->member_id);
        }

        return DataTables::of($cards)
            ->addColumn('checkbox', function (Card $card) {
                return '<input type="checkbox" class="card-checkbox" value="' . $card->id . '">';
            })
            ->addColumn('holder_name', function (Card $card) {
                if ($card->cardable) {
                    return $card->cardable->name;
                }
                return '<span class="text-danger">Deleted Record</span>';
            })
            ->addColumn('holder_type', function (Card $card) {
                return '<span class="badge badge-info">Customer / Member</span>';
            })
            ->addColumn('holder_mobile', function (Card $card) {
                return $card->cardable ? $card->cardable->mobile_no : '-';
            })
            ->editColumn('status', function (Card $card) {
                $badges = [
                    'Active' => 'badge-success',
                    'Suspended' => 'badge-warning',
                    'Lost' => 'badge-danger',
                    'Damaged' => 'badge-secondary',
                    'Expired' => 'badge-dark',
                    'Revoked' => 'badge-danger',
                ];
                $badge = $badges[$card->status] ?? 'badge-light';
                return '<span class="badge ' . $badge . '">' . $card->status . '</span>';
            })
            ->addColumn('amount', function (Card $card) {
                return number_format($card->amount, 2);
            })
            ->addColumn('payment_status', function (Card $card) {
                if ($card->payment_status === 'Pending') {
                    return '<span class="badge badge-warning">Pending</span>';
                }
                return '<span class="badge badge-success">Received</span>';
            })
            ->addColumn('action', function (Card $card) {
                $actions = '';
                if ($card->payment_status === 'Pending') {
                    $actions .= '<button class="btn btn-xs btn-secondary mr-1" disabled title="Pending Payment"><i class="fa fa-print"></i> Print</button>';
                } else {
                    $actions .= '<button class="btn btn-xs btn-primary btn-print-card mr-1" data-id="' . $card->id . '" data-name="' . ($card->cardable ? $card->cardable->name : 'N/A') . '"><i class="fa fa-print"></i> Print</button>';
                }
                
                $actions .= '<div class="btn-group">';
                $actions .= '<button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown">Status</button>';
                $actions .= '<div class="dropdown-menu dropdown-menu-right">';
                foreach (['Active', 'Suspended', 'Lost', 'Damaged', 'Expired', 'Revoked'] as $st) {
                    if ($card->status !== $st) {
                        $actions .= '<a class="dropdown-item change-status-btn" href="#" data-id="' . $card->id . '" data-status="' . $st . '">' . $st . '</a>';
                    }
                }
                $actions .= '</div></div>';
                
                // Add Delete Option if it has a voucher_id
                if ($card->voucher_id) {
                    $actions .= ' <button class="btn btn-xs btn-danger btn-delete-card" data-id="' . $card->id . '"><i class="fa fa-trash"></i></button>';
                }
                
                return $actions;
            })
            ->rawColumns(['checkbox', 'holder_type', 'status', 'payment_status', 'action', 'holder_name'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_issue'), 403);
        $pageTitle = 'Issue Member Card';
        $sisterConcernId = auth()->user()->activeConcernId();
        
        $activeCardCustomerIds = Card::where('cardable_type', Supplier::class)
            ->where('status', 'Active')
            ->pluck('cardable_id')
            ->toArray();

        $customers = Supplier::whereNotNull('id')
            ->where('origin_sister_concern_id', $sisterConcernId)
            ->whereIn('type', [SupplierType::$CUSTOMER, SupplierType::$CORPORATE_CUSTOMER, SupplierType::$DEALER])
            ->whereNotIn('id', $activeCardCustomerIds)
            ->whereHas('shomriddhiAccount')
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_no']);

        $setting = CardSetting::first();
        $feeAmount = $setting ? $setting->card_fee : 100;

        return view('card_management.member.create', compact('pageTitle', 'customers', 'feeAmount'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_issue'), 403);
        $request->validate([
            'holder_id' => 'required|integer',
        ]);

        $sisterConcernId = auth()->user()->activeConcernId();
        $id = $request->holder_id;
        
        $existingActive = Card::where('cardable_type', Supplier::class)
            ->where('cardable_id', $id)
            ->where('status', 'Active')
            ->first();

        if ($existingActive) {
            return redirect()->back()->with('error', 'This member already has an Active Card. Revoke or change status of the current card to issue a new one.');
        }

        try {
            DB::beginTransaction();

            $setting = CardSetting::first();
            $feeAmount = $setting ? $setting->card_fee : 100;

            $cardNo = $this->generateCardNumber('customer');

            $card = Card::create([
                'origin_sister_concern_id' => $sisterConcernId,
                'card_number' => $cardNo,
                'cardable_type' => Supplier::class,
                'cardable_id' => $id,
                'status' => 'Active',
                'amount' => $request->input('amount', $feeAmount),
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('card-management.member.index')->with('success', 'Member card issued successfully. Card No: ' . $cardNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error issuing card: ' . $e->getMessage());
        }
    }

    public function printPreview(Card $card)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_print'), 403);
        abort_if($card->cardable_type !== Supplier::class, 404);
        abort_if($card->payment_status === 'Pending', 403, 'Payment is pending. Please collect payment first.');
        
        $card->load(['cardable', 'cardable.mfMember.somiti']);
        $pageTitle = 'Print Preview: ' . $card->card_number;
        
        return view('card_management.member.print_preview', compact('pageTitle', 'card'));
    }

    public function logPrint(Request $request, Card $card)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_print'), 403);
        abort_if($card->cardable_type !== Supplier::class, 404);
        
        $request->validate([
            'print_reason' => 'required|in:First Issue,Lost Replacement,Damaged Replacement,Renewal',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            if ($request->print_reason !== 'First Issue') {
                Card::where('cardable_type', $card->cardable_type)
                    ->where('cardable_id', $card->cardable_id)
                    ->where('id', '!=', $card->id)
                    ->update(['status' => 'Revoked']);
            }

            CardPrintLog::create([
                'origin_sister_concern_id' => auth()->user()->activeConcernId(),
                'card_id' => $card->id,
                'printed_by' => auth()->id(),
                'printed_at' => Carbon::now(),
                'print_reason' => $request->print_reason,
                'notes' => $request->notes,
            ]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Print log saved successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error saving log: ' . $e->getMessage()], 500);
        }
    }

    public function destroy(Card $card)
    {
        // Must have permission
        abort_if(!auth()->user()->hasPermissionTo('member_card_issue'), 403);
        
        if (!$card->voucher_id) {
            return response()->json(['success' => false, 'message' => 'Cannot delete. No associated voucher found.']);
        }

        try {
            DB::beginTransaction();
            
            $voucherId = $card->voucher_id;
            
            // Revert all cards in this voucher to Pending and remove voucher_id
            Card::where('voucher_id', $voucherId)->update([
                'payment_status' => 'Pending',
                'voucher_id' => null,
                'received_by' => null,
                'received_at' => null
            ]);
            
            // Find voucher and its transactions
            $voucher = \App\Models\Account\Voucher::find($voucherId);
            if ($voucher) {
                // Delete associated shomriddhi transactions
                \App\Models\ShomriddhiAdvance\ShomriddhiTransaction::where('voucher_id', $voucherId)->get()->each(function ($st) {
                    // Revert principal balance
                    $sa = \App\Models\ShomriddhiAdvance\ShomriddhiAccount::where('customer_id', $st->customer_id)->first();
                    if ($sa) {
                        $sa->decrement('principal_balance', $st->amount);
                    }
                    $st->delete();
                });
                
                // Delete account transactions
                \App\Models\Account\Transaction::where('voucher_id', $voucherId)->delete();
                
                // Delete voucher
                $voucher->delete();
            }
            
            // Delete the specified card permanently
            $card->delete();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Card deleted and associated voucher has been fully reversed successfully. Other cards in the voucher are now marked as Pending.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error deleting card and reversing voucher: ' . $e->getMessage()]);
        }
    }

    public function updateStatus(Request $request, Card $card)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_status'), 403);
        abort_if($card->cardable_type !== Supplier::class, 404);
        
        $request->validate([
            'status' => 'required|in:Active,Suspended,Lost,Damaged,Expired,Revoked',
        ]);

        if ($request->status === 'Active') {
            $existingActive = Card::where('cardable_type', $card->cardable_type)
                ->where('cardable_id', $card->cardable_id)
                ->where('id', '!=', $card->id)
                ->where('status', 'Active')
                ->exists();

            if ($existingActive) {
                return response()->json([
                    'success' => false, 
                    'message' => 'This member already has another Active Card. You cannot have multiple active cards.'
                ]);
            }
        }

        $card->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Card status updated to ' . $request->status]);
    }

    public function printLogs()
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_logs'), 403);
        $pageTitle = 'Member Card Print Logs';
        return view('card_management.member.print_logs', compact('pageTitle'));
    }

    public function printLogsDataTable()
    {
        $logs = CardPrintLog::with(['card.cardable', 'printer'])
            ->where('card_print_logs.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('card', function($q) {
                $q->where('cardable_type', Supplier::class);
            })
            ->orderBy('card_print_logs.id', 'DESC');

        return DataTables::of($logs)
            ->addColumn('card_number', function(CardPrintLog $log) {
                return $log->card ? $log->card->card_number : '-';
            })
            ->addColumn('holder_name', function(CardPrintLog $log) {
                return ($log->card && $log->card->cardable) ? $log->card->cardable->name : '-';
            })
            ->addColumn('holder_type', function(CardPrintLog $log) {
                return 'Customer';
            })
            ->addColumn('printed_by_name', function(CardPrintLog $log) {
                return $log->printer ? $log->printer->name : '-';
            })
            ->editColumn('printed_at', function(CardPrintLog $log) {
                return $log->printed_at ? $log->printed_at->format('d-m-Y h:i A') : '-';
            })
            ->rawColumns([])
            ->toJson();
    }

    public function bulkLogPrint(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_print'), 403);
        $request->validate([
            'card_ids' => 'required|array',
            'card_ids.*' => 'required|integer|exists:cards,id',
            'print_reason' => 'required|in:First Issue,Lost Replacement,Damaged Replacement,Renewal',
            'notes' => 'nullable|string',
        ]);

        $sisterConcernId = auth()->user()->activeConcernId();

        try {
            DB::beginTransaction();

            foreach ($request->card_ids as $cardId) {
                $card = Card::where('id', $cardId)
                    ->where('cardable_type', Supplier::class)
                    ->first();

                if (!$card) continue;

                if ($request->print_reason !== 'First Issue') {
                    Card::where('cardable_type', $card->cardable_type)
                        ->where('cardable_id', $card->cardable_id)
                        ->where('id', '!=', $card->id)
                        ->update(['status' => 'Revoked']);
                }

                CardPrintLog::create([
                    'origin_sister_concern_id' => $sisterConcernId,
                    'card_id' => $card->id,
                    'printed_by' => auth()->id(),
                    'printed_at' => Carbon::now(),
                    'print_reason' => $request->print_reason,
                    'notes' => $request->notes,
                ]);
            }

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Bulk print logs recorded successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error saving bulk logs: ' . $e->getMessage()], 500);
        }
    }

    public function bulkPrintPreview(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('member_card_print'), 403);
        $request->validate([
            'ids' => 'required|string',
        ]);

        $cardIds = explode(',', $request->ids);
        $sisterConcernId = auth()->user()->activeConcernId();

        $cards = Card::whereIn('id', $cardIds)
            ->where('cardable_type', Supplier::class)
            ->where('payment_status', 'Received')
            ->with(['cardable'])
            ->get();

        if ($cards->isEmpty()) {
            abort(404, 'No valid cards found for preview.');
        }

        $pageTitle = 'Bulk Print Preview (' . $cards->count() . ' Cards)';
        $cards->load('cardable.mfMember.somiti');
        return view('card_management.member.bulk_print_preview', compact('pageTitle', 'cards'));
    }

    public function verifyPublic($cardNumber)
    {
        $card = Card::where('card_number', $cardNumber)
            ->where('cardable_type', Supplier::class)
            ->with(['cardable'])
            ->first();

        if (!$card) {
            return view('card_management.verify_public', [
                'success' => false,
                'message' => 'Invalid Card Number or Card not found.'
            ]);
        }

        $supplier = $card->cardable;
        $photoUrl = ($supplier && $supplier->image) ? asset($supplier->image) : null;
        
        $somitiName = 'N/A';
        $memberNo = $supplier->member_no;
        
        $member = MfMember::where('supplier_id', $card->cardable_id)->first();
        if ($member) {
            $somitiName = $member->somiti ? $member->somiti->name : 'N/A';
        }

        $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $card->cardable_id)->first();

        $statement = [];
        $openingBalance = 0;

        if ($shomriddhiAccount) {
            $allTransactions = ShomriddhiTransaction::where('customer_id', $card->cardable_id)
                ->where('status', 'Approved')
                ->orderBy('date', 'asc')
                ->orderBy('id', 'asc')
                ->get();

            $runningBalance = 0;
            $processedTransactions = [];

            foreach ($allTransactions as $trx) {
                if ($trx->type === 'Deposit' || $trx->type === 'Interest') {
                    $runningBalance += $trx->amount;
                } elseif ($trx->type === 'Withdrawal') {
                    $runningBalance -= $trx->amount;
                }
                
                $trx->running_balance = $runningBalance;
                $processedTransactions[] = $trx;
            }

            $statement = array_slice($processedTransactions, -10);
            
            if (count($statement) > 0) {
                $firstOfLast10 = $statement[0];
                if ($firstOfLast10->type === 'Deposit' || $firstOfLast10->type === 'Interest') {
                    $openingBalance = $firstOfLast10->running_balance - $firstOfLast10->amount;
                } else {
                    $openingBalance = $firstOfLast10->running_balance + $firstOfLast10->amount;
                }
            } else {
                $openingBalance = $runningBalance;
            }
        }

        return view('card_management.member.verify_public', [
            'success' => true,
            'card' => $card,
            'photoUrl' => $photoUrl,
            'somitiName' => $somitiName,
            'memberNo' => $memberNo,
            'shomriddhiAccount' => $shomriddhiAccount,
            'statement' => $statement,
            'openingBalance' => $openingBalance,
        ]);
    }

    private function generateCardNumber($type)
    {
        $prefix = $type === 'customer' ? 'C' : 'E';
        
        $lastCard = Card::withTrashed()
            ->where('card_number', 'LIKE', 'TORI-' . $prefix . '-%')
            ->orderBy('id', 'DESC')
            ->first();

        if ($lastCard) {
            $lastNoStr = str_replace('TORI-' . $prefix . '-', '', $lastCard->card_number);
            $lastNo = (int) $lastNoStr;
            $nextNo = $lastNo + 1;
        } else {
            $nextNo = 10001;
        }

        return 'TORI-' . $prefix . '-' . $nextNo;
    }
}
