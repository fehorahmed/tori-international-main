<?php

namespace App\Http\Controllers\CardManagement;

use App\Http\Controllers\Controller;
use App\Models\CardManagement\Card;
use App\Models\Hrm\Employee;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\ShomriddhiAdvance\ShomriddhiAccount;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Account\AccountHead;
use App\Models\Account\Transaction;
use App\Models\Account\Voucher;
use App\Enumeration\TransactionType;
use App\Enumeration\VoucherType;
use App\Enumeration\ReservedAccountHead;
use App\Enumeration\PaymentPurpose;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use App\Models\CardManagement\CardSetting;

class CardCollectionController extends Controller
{
    public function index()
    {
        // Same pattern as ReceiptPaymentController — accounts with payment_mode > 0 are Cash/Bank/MFS heads
        $paymentModes = AccountHead::where(function ($q) {
            $q->whereNull('sister_concern_id')
              ->orWhere('sister_concern_id', auth()->user()->activeConcernId());
        })->where('payment_mode', '>', 0)->where('status', 1)->get();

        return view('card_management.collection.index', compact('paymentModes'));
    }

    public function dataTable()
    {
        // Query users who have pending cards
        $query = User::whereHas('cards', function ($q) {
                $q->where('payment_status', 'Pending')
                  ->where('cardable_type', Supplier::class)
                  ->where('origin_sister_concern_id', auth()->user()->activeConcernId());
            })
            ->withCount(['cards as pending_cards_count' => function ($q) {
                $q->where('payment_status', 'Pending')
                  ->where('cardable_type', Supplier::class)
                  ->where('origin_sister_concern_id', auth()->user()->activeConcernId());
            }])
            ->withSum(['cards as pending_amount_sum' => function ($q) {
                $q->where('payment_status', 'Pending')
                  ->where('cardable_type', Supplier::class)
                  ->where('origin_sister_concern_id', auth()->user()->activeConcernId());
            }], 'amount')
            ->get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee_name', function ($row) {
                return $row->name . '<br><small class="text-muted">' . $row->id_no . '</small>';
            })
            ->addColumn('pending_cards', function ($row) {
                return '<span class="badge badge-warning">' . $row->pending_cards_count . ' Cards</span>';
            })
            ->addColumn('pending_amount', function ($row) {
                return '<span class="badge badge-danger">৳ ' . number_format($row->pending_amount_sum, 2) . '</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-sm btn-success receive-btn" 
                            data-id="' . $row->id . '" 
                            data-name="' . $row->name . '" 
                            data-count="' . $row->pending_cards_count . '" 
                            data-amount="' . $row->pending_amount_sum . '">
                            <i class="fa fa-money"></i> Receive Cash
                        </button>';
            })
            ->rawColumns(['employee_name', 'pending_cards', 'pending_amount', 'action'])
            ->make(true);
    }

    public function receivePayment(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'payment_mode' => 'required',
        ]);

        try {
            DB::beginTransaction();

            [$paymentAccountHeadId] = explode('|', $request->payment_mode);

            $userId = $request->user_id;
            
            // Get all pending cards for this user in the active concern
            $cards = Card::where('created_by', $userId)
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('cardable_type', Supplier::class)
                ->where('payment_status', 'Pending')
                ->get();

            if ($cards->isEmpty()) {
                return response()->json(['success' => false, 'message' => 'No pending cards found for this user.']);
            }

            $totalAmount = $cards->sum('amount');
            $totalCards = $cards->count();

            $voucherId = $this->generateAccountingEntries($cards, $userId, $paymentAccountHeadId, $totalAmount);

            // 1. Update Cards Status
            Card::where('created_by', $userId)
                ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                ->where('cardable_type', Supplier::class)
                ->where('payment_status', 'Pending')
                ->update([
                    'payment_status' => 'Received',
                    'received_by' => auth()->id(),
                    'received_at' => now(),
                    'voucher_id' => $voucherId,
                ]);

            DB::commit();

            return response()->json([
                'success' => true, 
                'message' => "Successfully received ৳ {$totalAmount} for {$totalCards} cards from User."
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    protected function generateAccountingEntries($cards, $userId, $paymentAccountHeadId, $totalAmount)
    {
        $scId = auth()->user()->activeConcernId();
        $paymentHead = AccountHead::findOrFail($paymentAccountHeadId);
        // Search by name because 'Other Income' doesn't have a reserved_id
        $otherIncomeHead = AccountHead::where('name', 'Other Income')->first();
        
        if (!$otherIncomeHead) {
            throw new \Exception('Other Income Account Head not found.');
        }

        $creatingUser = User::find($userId);
        
        // Find the reserved Card Commission Payable Head
        $cardCommissionPayableHead = AccountHead::where('reserved_id', ReservedAccountHead::CARD_COMMISSION_PAYABLE)->first();
        if (!$cardCommissionPayableHead) {
            throw new \Exception('Card Commission Payable Account Head not found.');
        }

        $customerCommissions = [];
        $employeeCommission = 0;
        $companyIncome = 0;

        $setting = CardSetting::first();
        if (!$setting) {
            throw new \Exception('Card Settings not configured.');
        }

        foreach ($cards as $card) {
            $isFirstCard = Card::where('cardable_type', $card->cardable_type)
                ->where('cardable_id', $card->cardable_id)
                ->where('id', '<', $card->id)
                ->doesntExist();

            if ($isFirstCard) {
                $empPortion = $setting->first_card_agent_commission;
                $custPortion = $setting->first_card_member_bonus;
                $incomePortion = $setting->first_card_company_income;
            } else {
                // Second time or replacement card
                $empPortion = $setting->replacement_card_agent_commission;
                $custPortion = $setting->replacement_card_member_bonus;
                $incomePortion = $setting->replacement_card_company_income;
            }

            // If the user who generated the card is not an employee (e.g. Admin), 
            // the employee commission portion goes to the company income instead.
            if (!$creatingUser || empty($creatingUser->employee_id)) {
                $incomePortion += $empPortion;
                $empPortion = 0;
            }

            $employeeCommission += $empPortion;
            $companyIncome += $incomePortion;

            $cardableHeadId = null;
            if ($card->cardable_type == Supplier::class) {
                $head = AccountHead::where('supplier_id', $card->cardable_id)->where('head_type', 'savings')->first();
                $cardableHeadId = $head ? $head->id : null;
            }

            if ($cardableHeadId) {
                if (!isset($customerCommissions[$cardableHeadId])) {
                    $customerCommissions[$cardableHeadId] = 0;
                }
                $customerCommissions[$cardableHeadId] += $custPortion;
            } else {
                $companyIncome += $custPortion;
            }
        }

        // Generate Voucher
        $date = Carbon::parse(auth()->user()->business_date)->format('Y-m-d');
        
        $voucherNo = generateVoucherNo(VoucherType::$COLLECTION_VOUCHER);
        $voucherNoGroupSl = abs((int)filter_var($voucherNo, FILTER_SANITIZE_NUMBER_INT));

        $voucher = Voucher::create([
            'sister_concern_id'       => $scId,
            'voucher_type'            => VoucherType::$COLLECTION_VOUCHER,
            'payment_type_id'         => $paymentHead->payment_mode, // Cash/Bank/Mobile
            'payment_account_head_id' => $paymentHead->id,
            'amount'                  => $totalAmount,
            'date'                    => $date,
            'voucher_no'              => $voucherNo,
            'voucher_no_group_sl'     => $voucherNoGroupSl,
            'notes'                   => "Card Issuance Collection from " . ($creatingUser ? $creatingUser->name : 'User'),
            'user_id'                 => auth()->id(),
            'payment_purpose'         => PaymentPurpose::$SHOMRIDDHI_CARD_FEE,
        ]);

        // Debit Cash/Bank
        Transaction::create([
            'sister_concern_id' => $scId,
            'voucher_id' => $voucher->id,
            'account_head_id' => $paymentHead->id,
            'voucher_type' => VoucherType::$COLLECTION_VOUCHER,
            'transaction_type' => TransactionType::$DEBIT,
            'amount' => $totalAmount,
            'date' => $date,
            'user_id' => auth()->id(),
            'notes' => "Card Issuance Collection from " . ($creatingUser ? $creatingUser->name : 'User'),
            'voucher_no' => $voucherNo,
            'voucher_no_group_sl' => $voucherNoGroupSl,
            'payment_purpose' => PaymentPurpose::$SHOMRIDDHI_CARD_FEE,
        ]);

        // Credit Other Income
        if ($companyIncome > 0) {
            Transaction::create([
                'sister_concern_id' => $scId,
                'voucher_id' => $voucher->id,
                'account_head_id' => $otherIncomeHead->id,
                'voucher_type' => VoucherType::$COLLECTION_VOUCHER,
                'transaction_type' => TransactionType::$CREDIT,
                'amount' => $companyIncome,
                'date' => $date,
                'user_id' => auth()->id(),
                'notes' => "Card Issuance Income",
                'voucher_no' => $voucherNo,
                'voucher_no_group_sl' => $voucherNoGroupSl,
                'payment_purpose' => PaymentPurpose::$SHOMRIDDHI_CARD_FEE,
            ]);
        }

        // We always have the payable head now, so no need to fallback to companyIncome.
        // Credit Employee Commission
        if ($employeeCommission > 0) {
            Transaction::create([
                'sister_concern_id' => $scId,
                'voucher_id' => $voucher->id,
                'account_head_id' => $cardCommissionPayableHead->id,
                'voucher_type' => VoucherType::$COLLECTION_VOUCHER,
                'transaction_type' => TransactionType::$CREDIT,
                'amount' => $employeeCommission,
                'date' => $date,
                'user_id' => auth()->id(),
                'employee_id' => $creatingUser ? $creatingUser->employee_id : null,
                'notes' => "Card Issuance Commission for " . ($creatingUser ? $creatingUser->name : 'User'),
                'voucher_no' => $voucherNo,
                'voucher_no_group_sl' => $voucherNoGroupSl,
                'payment_purpose' => PaymentPurpose::$SHOMRIDDHI_CARD_FEE,
            ]);
        }

        // Credit Customers/Members Savings
        foreach ($customerCommissions as $headId => $amount) {
            if ($amount > 0) {
                Transaction::create([
                    'sister_concern_id' => $scId,
                    'voucher_id' => $voucher->id,
                    'account_head_id' => $headId,
                    'voucher_type' => VoucherType::$COLLECTION_VOUCHER,
                    'transaction_type' => TransactionType::$CREDIT,
                    'amount' => $amount,
                    'date' => $date,
                    'user_id' => auth()->id(),
                    'notes' => "Card Issue Bonus Deposit",
                    'voucher_no' => $voucherNo,
                    'voucher_no_group_sl' => $voucherNoGroupSl,
                    'payment_purpose' => PaymentPurpose::$SHOMRIDDHI_CARD_FEE,
                ]);

                // Also insert into Shomriddhi Advance table so it shows in the module Dashboard/Summary
                $savingsHead = AccountHead::find($headId);
                if ($savingsHead && $savingsHead->supplier_id) {
                    ShomriddhiTransaction::create([
                        'customer_id' => $savingsHead->supplier_id,
                        'type' => 'deposit',
                        'amount' => $amount,
                        'date' => $date,
                        'note' => "Card Issue Bonus Deposit (Voucher: {$voucherNo})",
                        'status' => 'Approved',
                        'voucher_id' => $voucher->id,
                        'created_by' => auth()->id()
                    ]);

                    $shomriddhiAccount = ShomriddhiAccount::where('customer_id', $savingsHead->supplier_id)->first();
                    if ($shomriddhiAccount) {
                        $shomriddhiAccount->increment('principal_balance', $amount);
                    }
                }
            }
        }

        return $voucher->id;
    }
}
