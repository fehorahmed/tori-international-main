<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\CardManagement\Card;
use App\Models\CardManagement\CardPrintLog;
use App\Models\Hrm\Employee;
use App\Models\IncentiveManagement\IncentiveLedger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;

class EmployeeCardController extends Controller
{
    public function index()
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card'), 403);
        $pageTitle = 'Employee Card Management';
        return view('card_management.employee.index', compact('pageTitle'));
    }

    public function dataTable()
    {
        $cards = Card::where('cardable_type', Employee::class)
            ->where('cards.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->with(['cardable', 'creator'])
            ->orderBy('cards.id', 'DESC');

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
                return '<span class="badge badge-primary">Employee</span>';
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
            ->addColumn('action', function (Card $card) {
                $actions = '';
                $actions .= '<button class="btn btn-xs btn-primary btn-print-card mr-1" data-id="' . $card->id . '" data-name="' . ($card->cardable ? $card->cardable->name : 'N/A') . '"><i class="fa fa-print"></i> Print</button>';
                
                $actions .= '<div class="btn-group">';
                $actions .= '<button type="button" class="btn btn-xs btn-secondary dropdown-toggle" data-toggle="dropdown">Status</button>';
                $actions .= '<div class="dropdown-menu dropdown-menu-right">';
                foreach (['Active', 'Suspended', 'Lost', 'Damaged', 'Expired', 'Revoked'] as $st) {
                    if ($card->status !== $st) {
                        $actions .= '<a class="dropdown-item change-status-btn" href="#" data-id="' . $card->id . '" data-status="' . $st . '">' . $st . '</a>';
                    }
                }
                $actions .= '</div></div>';
                return $actions;
            })
            ->rawColumns(['checkbox', 'holder_type', 'status', 'action', 'holder_name'])
            ->toJson();
    }

    public function create()
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_issue'), 403);
        $pageTitle = 'Issue Employee Card';

        $activeCardEmployeeIds = Card::where('cardable_type', Employee::class)
            ->where('status', 'Active')
            ->pluck('cardable_id')
            ->toArray();

        $employees = Employee::where('sister_concern_id', auth()->user()->activeConcernId())
            ->whereNotIn('id', $activeCardEmployeeIds)
            ->orderBy('name')
            ->get(['id', 'name', 'mobile_no']);

        return view('card_management.employee.create', compact('pageTitle', 'employees'));
    }

    public function store(Request $request)
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_issue'), 403);
        $request->validate([
            'holder_id' => 'required|integer',
        ]);

        $id = $request->holder_id;
        
        $existingActive = Card::where('cardable_type', Employee::class)
            ->where('cardable_id', $id)
            ->where('status', 'Active')
            ->first();

        if ($existingActive) {
            return redirect()->back()->with('error', 'This employee already has an Active Card. Revoke or change status of the current card to issue a new one.');
        }

        try {
            DB::beginTransaction();

            $cardNo = $this->generateCardNumber('employee');

            Card::create([
                'origin_sister_concern_id' => auth()->user()->activeConcernId(),
                'card_number' => $cardNo,
                'cardable_type' => Employee::class,
                'cardable_id' => $id,
                'status' => 'Active',
                'amount' => 0,
                'payment_status' => 'Received',
                'created_by' => auth()->id(),
            ]);

            DB::commit();
            return redirect()->route('card-management.employee.index')->with('success', 'Employee card issued successfully. Card No: ' . $cardNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error issuing card: ' . $e->getMessage());
        }
    }

    public function printPreview(Card $card)
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_print'), 403);
        abort_if($card->cardable_type !== Employee::class, 404);
        
        $card->load(['cardable']);
        $pageTitle = 'Print Preview: ' . $card->card_number;
        
        return view('card_management.employee.print_preview', compact('pageTitle', 'card'));
    }

    public function logPrint(Request $request, Card $card)
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_print'), 403);
        abort_if($card->cardable_type !== Employee::class, 404);
        
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

    public function updateStatus(Request $request, Card $card)
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_status'), 403);
        abort_if($card->cardable_type !== Employee::class, 404);
        
        $request->validate([
            'status' => 'required|in:Active,Suspended,Lost,Damaged,Expired,Revoked',
        ]);

        $card->update(['status' => $request->status]);

        return response()->json(['success' => true, 'message' => 'Card status updated to ' . $request->status]);
    }

    public function printLogs()
    {
        abort_if(!auth()->user()->hasPermissionTo('employee_card_logs'), 403);
        $pageTitle = 'Employee Card Print Logs';
        return view('card_management.employee.print_logs', compact('pageTitle'));
    }

    public function printLogsDataTable()
    {
        $logs = CardPrintLog::with(['card.cardable', 'printer'])
            ->where('card_print_logs.origin_sister_concern_id', auth()->user()->activeConcernId())
            ->whereHas('card', function($q) {
                $q->where('cardable_type', Employee::class);
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
                return 'Employee';
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
        abort_if(!auth()->user()->hasPermissionTo('employee_card_print'), 403);
        $request->validate([
            'card_ids' => 'required|array',
            'card_ids.*' => 'required|integer|exists:cards,id',
            'print_reason' => 'required|in:First Issue,Lost Replacement,Damaged Replacement,Renewal',
            'notes' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->card_ids as $cardId) {
                $card = Card::where('id', $cardId)
                    ->where('cardable_type', Employee::class)
                    ->first();

                if (!$card) continue;

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
        abort_if(!auth()->user()->hasPermissionTo('employee_card_print'), 403);
        $request->validate([
            'ids' => 'required|string',
        ]);

        $cardIds = explode(',', $request->ids);

        $cards = Card::whereIn('id', $cardIds)
            ->where('cardable_type', Employee::class)
            ->with(['cardable'])
            ->get();

        if ($cards->isEmpty()) {
            abort(404, 'No valid cards found for preview.');
        }

        $pageTitle = 'Bulk Print Preview (' . $cards->count() . ' Cards)';
        return view('card_management.employee.bulk_print_preview', compact('pageTitle', 'cards'));
    }

    public function verifyPublic($cardNumber)
    {
        $card = Card::where('card_number', $cardNumber)
            ->where('cardable_type', Employee::class)
            ->with(['cardable'])
            ->first();

        if (!$card) {
            return view('card_management.verify_public', [
                'success' => false,
                'message' => 'Invalid Card Number or Card not found.'
            ]);
        }

        $photoUrl = null;
        $role = 'N/A';
        $department = 'N/A';

        if ($card->cardable) {
            if ($card->cardable->photo) {
                $photoUrl = asset($card->cardable->photo);
            }
            $role = $card->cardable->designation ? $card->cardable->designation->name : 'Staff';
            $department = $card->cardable->department ? $card->cardable->department->name : 'N/A';
        }

        $incentiveBalance = 0;
        if ($card->cardable) {
            $employeeId = $card->cardable_id; // Sale system uses Employee ID directly
            $incentiveBalance = IncentiveLedger::where('employee_id', $employeeId)->sum('amount');
        }

        return view('card_management.employee.verify_public', [
            'success' => true,
            'card' => $card,
            'photoUrl' => $photoUrl,
            'role' => $role,
            'department' => $department,
            'incentiveBalance' => $incentiveBalance
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
