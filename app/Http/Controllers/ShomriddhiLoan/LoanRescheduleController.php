<?php

namespace App\Http\Controllers\ShomriddhiLoan;

use App\Http\Controllers\Controller;
use App\Models\ShomriddhiLoan\LoanInstallment;
use App\Models\Hrm\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LoanRescheduleController extends Controller
{
    public function index()
    {
        $holidays = Holiday::orderBy('start_date', 'desc')->get();
        return view('shomriddhi_loan.reschedule.index', compact('holidays'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'holiday_id' => 'required',
            'shift_days' => 'required|integer|min:1'
        ]);

        try {
            DB::beginTransaction();

            $holiday = Holiday::findOrFail($request->holiday_id);
            $shiftDays = $request->shift_days;

            // Find all unpaid or partial installments whose due date falls within the holiday
            $installmentsToShift = LoanInstallment::whereIn('status', ['unpaid', 'partial', 'overdue'])
                ->whereBetween('due_date', [$holiday->start_date, $holiday->end_date])
                ->get();
            
            $affectedLoansCount = 0;

            foreach ($installmentsToShift as $installment) {
                // Get all subsequent installments for this loan account
                $subsequentInstallments = LoanInstallment::where('loan_account_id', $installment->loan_account_id)
                    ->where('installment_no', '>=', $installment->installment_no)
                    ->get();

                foreach ($subsequentInstallments as $sub) {
                    $newDate = Carbon::parse($sub->due_date)->addDays($shiftDays);
                    $sub->due_date = $newDate->format('Y-m-d');
                    
                    // If this was an overdue installment, and the new date is in the future or today, 
                    // change status back to unpaid (or partial)
                    if ($sub->status == 'overdue' && $newDate->gte(Carbon::today())) {
                        $sub->status = $sub->paid_amount > 0 ? 'partial' : 'unpaid';
                    }
                    
                    $sub->save();
                }
                
                $affectedLoansCount++;
            }

            DB::commit();
            return back()->with('success', "Successfully shifted schedule for {$affectedLoansCount} loan installments by {$shiftDays} days due to {$holiday->title}.");
            
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
