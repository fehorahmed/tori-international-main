<?php

namespace App\Services\Payroll;

use App\Models\Payroll\EmployeeAdvance;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Models\Account\AccountHead;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\VoucherType;
use App\Enumeration\TransactionType;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

use App\Repositories\Payroll\Contracts\EmployeeAdvanceRepositoryInterface;

class EmployeeAdvanceService
{
    protected $advanceRepository;

    public function __construct(EmployeeAdvanceRepositoryInterface $advanceRepository)
    {
        $this->advanceRepository = $advanceRepository;
    }

    public function approve(EmployeeAdvance $advance, int $paymentAccountHeadId): array
    {
        try {
            DB::beginTransaction();

            $date = Carbon::parse(auth()->user()->business_date)->format('Y-m-d');
            $scId = auth()->user()->activeConcernId();

            // Check Cash/Bank Account Head
            $paymentHead = AccountHead::find($paymentAccountHeadId);
            if (!$paymentHead) {
                throw new Exception('Payment account head not found.');
            }

            // Check if there is enough balance in the Cash/Bank account
            if (!$paymentHead->isSufficient($advance->amount, $scId)) {
                $currentBalance = $paymentHead->getCurrentBalance($scId);
                throw new Exception("Insufficient balance in {$paymentHead->name}. Current Balance: " . number_format($currentBalance, 2));
            }

            // Find Employee Advance Reserved Account Head
            $advanceHead = AccountHead::where('reserved_id', ReservedAccountHead::EMPLOYEE_ADVANCE)->first();
            if (!$advanceHead) {
                throw new Exception('Employee Advance Account Head not found.');
            }

            // Generate Voucher (Payment Voucher because money goes out)
            $voucherNo = generateVoucherNo(VoucherType::$PAYMENT_VOUCHER);
            $newNumber = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));
            $noteText = "Employee Advance paid to " . ($advance->employee->name ?? 'Employee');

            $voucher = Voucher::create([
                'sister_concern_id'       => $scId,
                'voucher_type'            => VoucherType::$PAYMENT_VOUCHER,
                'payment_type_id'         => $paymentHead->payment_mode ?? null,
                'payment_account_head_id' => $paymentHead->id, // Payment Mode
                'amount'                  => $advance->amount,
                'date'                    => $date,
                'voucher_no'              => $voucherNo,
                'voucher_no_group_sl'     => $newNumber,
                'notes'                   => $noteText,
                'user_id'                 => auth()->id(),
            ]);

            // 1. Credit Cash/Bank (Money goes out)
            Transaction::create([
                'sister_concern_id'   => $scId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $paymentHead->id,
                'voucher_type'        => VoucherType::$PAYMENT_VOUCHER,
                'transaction_type'    => TransactionType::$CREDIT,
                'amount'              => $advance->amount,
                'date'                => $date,
                'user_id'             => auth()->id(),
                'employee_id'         => $advance->employee_id,
                'notes'               => $noteText,
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
            ]);

            // 2. Debit Employee Advance Account (Asset increases)
            Transaction::create([
                'sister_concern_id'   => $scId,
                'voucher_id'          => $voucher->id,
                'account_head_id'     => $advanceHead->id,
                'voucher_type'        => VoucherType::$PAYMENT_VOUCHER,
                'transaction_type'    => TransactionType::$DEBIT,
                'amount'              => $advance->amount,
                'date'                => $date,
                'user_id'             => auth()->id(),
                'employee_id'         => $advance->employee_id,
                'notes'               => $noteText,
                'voucher_no'          => $voucherNo,
                'voucher_no_group_sl' => $newNumber,
            ]);

            // Update Advance Status
            $this->advanceRepository->update($advance->id, [
                'status' => 'Approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
                'voucher_no' => $voucherNo,
                'voucher_no_group_sl' => $newNumber
            ]);

            DB::commit();
            return ['success' => true, 'message' => 'Employee Advance approved successfully with accounting entries.'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
