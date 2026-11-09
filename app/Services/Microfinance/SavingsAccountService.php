<?php

namespace App\Services\Microfinance;

use App\Models\Microfinance\MfMember;
use App\Models\Microfinance\MfSavingsAccount;
use App\Models\Microfinance\MfSavingsProduct;
use App\Models\Microfinance\MfSavingsTransaction;
use App\Models\Microfinance\MfNominee;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SavingsAccountService
{
    /**
     * Main entry point to open a savings account (Pending State)
     */
    public function openAccount(array $data)
    {
        return DB::transaction(function () use ($data) {
            $member = MfMember::findOrFail($data['member_id']);
            $product = MfSavingsProduct::findOrFail($data['mf_savings_product_id']);

            // Enforce MFI Professional rules:
            if ($product->product_type == 1) { // General Savings
                $hasActiveGS = MfSavingsAccount::where('member_id', $member->id)
                    ->where('status', 1) // Active
                    ->whereHas('product', function($q) {
                        $q->where('product_type', 1);
                    })
                    ->exists();
                if ($hasActiveGS) {
                    throw new \Exception("এই সদস্যের ইতিমধ্যে একটি সাধারণ সঞ্চয় হিসাব সচল রয়েছে!");
                }
            } else { // DPS or FDR
                $hasActiveGS = MfSavingsAccount::where('member_id', $member->id)
                    ->where('status', 1) // Active
                    ->whereHas('product', function($q) {
                        $q->where('product_type', 1);
                    })
                    ->exists();
                if (!$hasActiveGS) {
                    throw new \Exception("ডিপিএস অথবা এফডিআর হিসাব খোলার পূর্বে সদস্যের একটি সাধারণ সঞ্চয় হিসাব সচল থাকা বাধ্যতামূলক।");
                }
            }

            // Enforce minimum deposit or installment amount rule on backend:
            $openingBalance = floatval($data['opening_balance'] ?? 0);
            if ($product->product_type == 2) { // DPS
                $requiredDpsAmount = floatval($product->installment_amount);
                if ($openingBalance < $requiredDpsAmount) {
                    throw new \Exception("ডিপিএস হিসাবের প্রারম্ভিক কিস্তির পরিমাণ অবশ্যই ৳ " . number_format($requiredDpsAmount, 2) . " হতে হবে!");
                }
            } else { // GS or FDR
                $minDeposit = floatval($product->min_deposit) || 0;
                if ($minDeposit > 0 && $openingBalance < $minDeposit) {
                    throw new \Exception("এই স্কিমের জন্য প্রারম্ভিক জমার পরিমাণ অবশ্যই সর্বনিম্ন ৳ " . number_format($minDeposit, 2) . " হতে হবে!");
                }
            }

            // 1. Generate Unique Account Number
            $accountNo = $this->generateAccountNumber($member, $product);

            // 2. Prepare Account Data (Base)
            $accountData = [
                'sister_concern_id' => auth()->user()->activeConcernId(),
                'somiti_id' => $member->somiti_id,
                'member_id' => $member->id,
                'mf_savings_product_id' => $product->id,
                'account_no' => $accountNo,
                'account_title' => $data['account_title'] ?? $member->name,
                'introducer_id' => $data['introducer_id'] ?? null,
                'auto_renewal' => $data['auto_renewal'] ?? false,
                'interest_rate' => $product->interest_rate, // Lock interest rate at opening
                'opening_balance' => $data['opening_balance'],
                'current_balance' => 0.00,
                'opening_date' => Carbon::createFromFormat('d-m-Y', $data['opening_date'])->format('Y-m-d'),
                'status' => 0, // 0 = Pending
                'created_by' => auth()->id(),
            ];

            // 3. Apply Strategy based on Product Type
            $accountData = $this->applyProductStrategy($accountData, $product);

            // 4. Create the Account
            $account = MfSavingsAccount::create($accountData);

            // 5. Sync Nominees
            if (isset($data['nominee_id'])) {
                $this->syncNominees($account, $data['nominee_id'], $data['share_percentage'] ?? []);
            }

            return $account;
        });
    }

    /**
     * Automatically ensure General Savings Product exists and open a Pending account for a new Member.
     * Keeps logic completely isolated to prevent issues with existing code.
     */
    public function autoOpenGeneralSavingsAccount(MfMember $member)
    {
        return DB::transaction(function () use ($member) {
            // 1. Ensure General Savings Product exists (product_type = 1, status = 1)
            $product = MfSavingsProduct::where('product_type', 1)
                ->where('status', 1)
                ->first();

            if (!$product) {
                $product = MfSavingsProduct::create([
                    'name' => 'General Savings (GS)',
                    'code' => 'GS-001',
                    'product_type' => 1,
                    'interest_rate' => 0.00,
                    'min_deposit' => 10.00,
                    'opening_fee' => 100.00,
                    'status' => 1,
                ]);
            }

            // 2. Check if a General Savings account already exists for this member
            $exists = MfSavingsAccount::where('member_id', $member->id)
                ->whereHas('product', function($q) {
                    $q->where('product_type', 1);
                })
                ->exists();

            if ($exists) {
                return null;
            }

            // 3. Generate account number
            $accountNo = $product->code . '-' . $member->member_no . '-1';

            // 4. Create Pending Savings Account
            $account = MfSavingsAccount::create([
                'sister_concern_id' => $member->sister_concern_id,
                'somiti_id' => $member->somiti_id,
                'member_id' => $member->id,
                'mf_savings_product_id' => $product->id,
                'account_no' => $accountNo,
                'account_title' => $member->name,
                'interest_rate' => $product->interest_rate,
                'opening_balance' => $product->min_deposit ?? 0.00,
                'current_balance' => 0.00,
                'opening_date' => $member->admission_date ? $member->admission_date->format('Y-m-d') : date('Y-m-d'),
                'status' => 0, // Pending
                'created_by' => auth()->id() ?? $member->created_by,
            ]);

            // 5. Automatically sync member nominees to this account
            $this->syncGeneralSavingsNominees($member);

            return $account;
        });
    }

    /**
     * Sync member nominees to their General Savings account (used during Member creation or update)
     */
    public function syncGeneralSavingsNominees(MfMember $member)
    {
        $gsAccount = MfSavingsAccount::where('member_id', $member->id)
            ->whereHas('product', function($q) {
                $q->where('product_type', 1);
            })
            ->first();

        if ($gsAccount) {
            $nominees = MfNominee::where('mf_member_id', $member->id)->get();
            $syncData = [];
            foreach ($nominees as $nominee) {
                $syncData[$nominee->id] = ['share_percentage' => floatval($nominee->share_percentage) ?: 100.00];
            }
            $gsAccount->nominees()->sync($syncData);
        }
    }

    /**
     * Approve Account: Update Status and Record Initial Transactions
     */
    public function approveAccount(MfSavingsAccount $account, array $customFees = [])
    {
        return DB::transaction(function () use ($account, $customFees) {
            if ($account->status != 0) {
                throw new \Exception("Account is already approved or closed.");
            }

            $product = $account->product;
            // Allow dynamic custom opening_balance (Savings Deposit)
            $intendedDeposit = isset($customFees['opening_balance']) ? floatval($customFees['opening_balance']) : (floatval($account->opening_balance) ?: 0);
            $member = $account->member;

            // 1. Activate account with the exact intended starting balance (e.g. 5,000)
            $account->status          = 1; // Active
            $account->opening_balance = $intendedDeposit;
            $account->current_balance = $intendedDeposit;
            $account->save();

            // 2. Record Initial Deposit Transaction for the intended starting balance
            if ($intendedDeposit > 0) {
                MfSavingsTransaction::create([
                    'sister_concern_id'     => $account->sister_concern_id,
                    'somiti_id'             => $account->somiti_id,
                    'mf_savings_account_id' => $account->id,
                    'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                    'amount'                => $intendedDeposit,
                    'date'                  => date('Y-m-d'),
                    'notes'                 => 'Opening Balance Deposit',
                    'created_by'            => auth()->id(),
                ]);
            }

            // 3. Double-entry transaction flows for Account Opening Fee (Applicable to all account types: GS, DPS, FDR)
            $openingFee = isset($customFees['opening_fee']) ? floatval($customFees['opening_fee']) : (floatval($product->opening_fee) ?: 0);
            if ($openingFee > 0) {
                MfSavingsTransaction::create([
                    'sister_concern_id'     => $account->sister_concern_id,
                    'somiti_id'             => $account->somiti_id,
                    'mf_savings_account_id' => $account->id,
                    'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                    'amount'                => $openingFee,
                    'date'                  => date('Y-m-d'),
                    'notes'                 => 'Account Opening Fee Deposit',
                    'created_by'            => auth()->id(),
                ]);
                MfSavingsTransaction::create([
                    'sister_concern_id'     => $account->sister_concern_id,
                    'somiti_id'             => $account->somiti_id,
                    'mf_savings_account_id' => $account->id,
                    'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                    'amount'                => $openingFee,
                    'date'                  => date('Y-m-d'),
                    'notes'                 => 'Account Opening Fee Deduction',
                    'created_by'            => auth()->id(),
                ]);
            }

            // 4. Double-entry transaction flows for Member Admission Fees (Only for General Savings product)
            if ($product->product_type == 1) {
                $admissionFee = isset($customFees['admission_fee']) ? floatval($customFees['admission_fee']) : (floatval($member->admission_fee) ?: 0);
                $formFee = isset($customFees['form_fee']) ? floatval($customFees['form_fee']) : (floatval($member->form_fee) ?: 0);
                $passbookFee = isset($customFees['passbook_fee']) ? floatval($customFees['passbook_fee']) : (floatval($member->passbook_fee) ?: 0);

                // Update member's fees to the actual collected amounts so profile remains accurate
                if (isset($customFees['admission_fee']) || isset($customFees['form_fee']) || isset($customFees['passbook_fee'])) {
                    $member->update([
                        'admission_fee' => $admissionFee,
                        'form_fee'      => $formFee,
                        'passbook_fee'  => $passbookFee,
                    ]);
                }

                // Admission Fee In/Out
                if ($admissionFee > 0) {
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                        'amount'                => $admissionFee,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Member Admission Fee Deposit',
                        'created_by'            => auth()->id(),
                    ]);
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                        'amount'                => $admissionFee,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Member Admission Fee Deduction',
                        'created_by'            => auth()->id(),
                    ]);
                }

                // Form Fee In/Out
                if ($formFee > 0) {
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                        'amount'                => $formFee,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Member Form Fee Deposit',
                        'created_by'            => auth()->id(),
                    ]);
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                        'amount'                => $formFee,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Member Form Fee Deduction',
                        'created_by'            => auth()->id(),
                    ]);
                }

                // Passbook Fee In/Out
                if ($passbookFee > 0) {
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                        'amount'                => $passbookFee,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Member Passbook Fee Deposit',
                        'created_by'            => auth()->id(),
                    ]);
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                        'amount'                => $passbookFee,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Member Passbook Fee Deduction',
                        'created_by'            => auth()->id(),
                    ]);
                }
            }

            return $account;
        });
    }

    /**
     * Generate Account No: [ProductCode]-[MemberNo]-[Serial]
     */
    private function generateAccountNumber($member, $product)
    {
        $sisterConcernId = auth()->user()->activeConcernId();
        $count = MfSavingsAccount::where('sister_concern_id', $sisterConcernId)
                                 ->where('member_id', $member->id)
                                 ->count() + 1;
        return $product->code . '-' . $member->member_no . '-' . $count;
    }

    /**
     * Strategy: Apply different rules for GS, DPS, FDR
     */
    private function applyProductStrategy(array $data, $product)
    {
        $openingDate = isset($data['opening_date']) ? $data['opening_date'] : date('Y-m-d');
        
        switch ($product->product_type) {
            case 2: // DPS
                $data['installment_amount'] = $product->installment_amount;
                $data['installment_frequency'] = $product->installment_frequency;
                $data['duration_months'] = $product->duration_months;
                $data['maturity_date'] = Carbon::parse($openingDate)->addMonths((int)$product->duration_months)->format('Y-m-d');
                break;

            case 3: // FDR
                $data['duration_months'] = $product->duration_months;
                $data['maturity_date'] = Carbon::parse($openingDate)->addMonths((int)$product->duration_months)->format('Y-m-d');
                break;
            
            default: // General Savings (GS)
                // No specific maturity for GS
                break;
        }

        return $data;
    }

    /**
     * Sync Nominees to Account
     */
    private function syncNominees($account, $nomineeIds, $shares)
    {
        $syncData = [];
        foreach ($nomineeIds as $id) {
            $syncData[$id] = ['share_percentage' => $shares[$id] ?? 0];
        }
        $account->nominees()->sync($syncData);
    }

    /**
     * Update an existing pending account
     */
    public function updateAccount(MfSavingsAccount $account, array $data)
    {
        return DB::transaction(function () use ($account, $data) {
            if ($account->status != 0) {
                throw new \Exception("Only pending accounts can be edited.");
            }

            // Enforce minimum deposit or installment amount rule on backend:
            $product = MfSavingsProduct::findOrFail($data['mf_savings_product_id']);
            $openingBalance = floatval($data['opening_balance'] ?? 0);
            if ($product->product_type == 2) { // DPS
                $requiredDpsAmount = floatval($product->installment_amount);
                if ($openingBalance < $requiredDpsAmount) {
                    throw new \Exception("ডিপিএস হিসাবের প্রারম্ভিক কিস্তির পরিমাণ অবশ্যই ৳ " . number_format($requiredDpsAmount, 2) . " হতে হবে!");
                }
            } else { // GS or FDR
                $minDeposit = floatval($product->min_deposit) || 0;
                if ($minDeposit > 0 && $openingBalance < $minDeposit) {
                    throw new \Exception("এই স্কিমের জন্য প্রারম্ভিক জমার পরিমাণ অবশ্যই সর্বনিম্ন ৳ " . number_format($minDeposit, 2) . " হতে হবে!");
                }
            }

            // 1. Update Base Fields
            $account->update([
                'member_id' => $data['member_id'],
                'account_title' => $data['account_title'] ?? $account->account_title,
                'introducer_id' => $data['introducer_id'] ?? $account->introducer_id,
                'auto_renewal' => $data['auto_renewal'] ?? $account->auto_renewal,
                'mf_savings_product_id' => $data['mf_savings_product_id'],
                'opening_balance' => $data['opening_balance'],
                'opening_date' => Carbon::createFromFormat('d-m-Y', $data['opening_date'])->format('Y-m-d'),
            ]);

            // 2. Re-apply Strategy (Duration, Installment, Maturity, etc.)
            $product = MfSavingsProduct::findOrFail($data['mf_savings_product_id']);
            $strategyData = $this->applyProductStrategy(['opening_date' => $account->opening_date], $product);
            $account->update($strategyData);

            // 3. Sync Nominees
            if (isset($data['nominee_id'])) {
                $this->syncNominees($account, $data['nominee_id'], $data['share_percentage'] ?? []);
            }

            return $account;
        });
    }

    /**
     * Withdraw from a savings account
     */
    public function withdraw(MfSavingsAccount $account, array $data): MfSavingsTransaction
    {
        return DB::transaction(function () use ($account, $data) {
            if ($account->status !== 1) {
                throw new \Exception('Only active accounts can be withdrawn from.');
            }

            $amount = floatval($data['amount']);

            if ($amount <= 0) {
                throw new \Exception('Withdrawal amount must be greater than zero.');
            }

            if ($amount > $account->current_balance) {
                throw new \Exception('Insufficient balance. Current balance: ' . number_format($account->current_balance, 2));
            }

            $transaction = MfSavingsTransaction::create([
                'sister_concern_id'     => $account->sister_concern_id,
                'somiti_id'             => $account->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_WITHDRAW,
                'amount'                => $amount,
                'date'                  => $data['date'] ?? date('Y-m-d'),
                'notes'                 => $data['notes'] ?? 'Withdrawal',
                'created_by'            => auth()->id(),
            ]);

            $account->decrement('current_balance', $amount);

            return $transaction;
        });
    }

    /**
     * Post interest to a savings account
     */
    public function postInterest(MfSavingsAccount $account): MfSavingsTransaction
    {
        return DB::transaction(function () use ($account) {
            if ($account->status !== 1) {
                throw new \Exception('Only active accounts can receive interest.');
            }

            $balance  = $account->current_balance;
            $rate     = $account->interest_rate;
            $interest = round(($balance * $rate) / 100 / 12, 2); // Monthly interest

            if ($interest <= 0) {
                throw new \Exception('No interest to post.');
            }

            $transaction = MfSavingsTransaction::create([
                'sister_concern_id'     => $account->sister_concern_id,
                'somiti_id'             => $account->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_INTEREST,
                'amount'                => $interest,
                'date'                  => date('Y-m-d'),
                'notes'                 => 'Monthly Interest Posting @ ' . $rate . '%',
                'created_by'            => auth()->id(),
            ]);

            $account->increment('current_balance', $interest);

            return $transaction;
        });
    }

    /**
     * Post annual interest to a savings account (Option B)
     */
    public function postAnnualInterest(MfSavingsAccount $account, float $amount): MfSavingsTransaction
    {
        return DB::transaction(function () use ($account, $amount) {
            if ($account->status !== 1) {
                throw new \Exception('Only active accounts can receive interest.');
            }

            if ($amount <= 0) {
                throw new \Exception('No interest to post.');
            }

            $transaction = MfSavingsTransaction::create([
                'sister_concern_id'     => $account->sister_concern_id,
                'somiti_id'             => $account->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_INTEREST,
                'amount'                => $amount,
                'date'                  => date('Y-m-d'),
                'notes'                 => 'Annual Interest Posting (Daily Product System) @ ' . $account->interest_rate . '%',
                'created_by'            => auth()->id() ?? $account->created_by,
            ]);

            $account->increment('current_balance', $amount);
            $account->update(['accrued_interest' => 0.0000]);

            return $transaction;
        });
    }

    /**
     * Close a savings account
     */
    public function closeAccount(MfSavingsAccount $account, string $reason = ''): MfSavingsAccount
    {
        return DB::transaction(function () use ($account, $reason) {
            if ($account->status === 3) {
                throw new \Exception('Account is already closed.');
            }

            $product = $account->product;

            // Forfeit any accrued interest upon early/mid-year closing
            if ($account->accrued_interest > 0) {
                $account->update(['accrued_interest' => 0.0000]);
            }

            // DPS & FDR Early Closure Logic (মেয়াদপূর্বে ডিপিএস বা এফডিআর ভাঙলে মুনাফা বাজেয়াপ্ত এবং প্রসেসিং জরিমানা কর্তন)
            if (in_array($product->product_type, [2, 3]) && $account->maturity_date && date('Y-m-d') < $account->maturity_date->format('Y-m-d')) {
                // 1. Clawback of previously posted interest
                $totalPostedInterest = MfSavingsTransaction::where('mf_savings_account_id', $account->id)
                    ->where('transaction_type', MfSavingsTransaction::TYPE_INTEREST)
                    ->sum('amount');

                if ($totalPostedInterest > 0) {
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                        'amount'                => $totalPostedInterest,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Early Closure: Forfeit of previously posted profit (মেয়াদপূর্বে ক্লোজ করার কারণে জমাকৃত লভ্যাংশ বাজেয়াপ্ত)',
                        'created_by'            => auth()->id() ?? $account->created_by,
                    ]);
                    $account->decrement('current_balance', $totalPostedInterest);
                }

                // 2. Pre-closure Processing Fee (জরিমানা/প্রসেসিং ফি)
                $earlyFine = 0;
                if ($product->early_closing_fine_amount > 0) {
                    if ($product->early_closing_fine_type === 'flat') {
                        $earlyFine = $product->early_closing_fine_amount;
                    } elseif ($product->early_closing_fine_type === 'percentage') {
                        $baseForPercentage = ($product->product_type == 3) ? $account->opening_balance : $account->current_balance;
                        $earlyFine = round(($baseForPercentage * $product->early_closing_fine_amount) / 100, 2);
                    }
                }

                if ($earlyFine > 0) {
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                        'amount'                => $earlyFine,
                        'date'                  => date('Y-m-d'),
                        'notes'                 => 'Early Closure Processing Fee (মেয়াদপূর্বে ক্লোজ করার কারণে জরিমানা/প্রসেসিং ফি)',
                        'created_by'            => auth()->id() ?? $account->created_by,
                    ]);
                    $account->decrement('current_balance', $earlyFine);
                }
            }

            // Apply closing fee if applicable
            if ($product->closing_fee > 0 && $account->current_balance >= $product->closing_fee) {
                MfSavingsTransaction::create([
                    'sister_concern_id'     => $account->sister_concern_id,
                    'somiti_id'             => $account->somiti_id,
                    'mf_savings_account_id' => $account->id,
                    'transaction_type'      => MfSavingsTransaction::TYPE_PENALTY,
                    'amount'                => $product->closing_fee,
                    'date'                  => date('Y-m-d'),
                    'notes'                 => 'Account Closing Fee',
                    'created_by'            => auth()->id(),
                ]);
                $account->decrement('current_balance', $product->closing_fee);
            }

            $account->update([
                'status'       => 3, // Closed
                'closed_date'  => date('Y-m-d'),
                'close_reason' => $reason,
            ]);

            return $account->fresh();
        });
    }

    /**
     * Manually renew an FDR account
     */
    public function renewAccount(MfSavingsAccount $account, array $data): MfSavingsAccount
    {
        return DB::transaction(function () use ($account, $data) {
            if ($account->status !== 1 && $account->status !== 2) {
                throw new \Exception('শুধুমাত্র সচল অথবা মেয়াদোত্তীর্ণ এফডিআর অ্যাকাউন্ট নবায়ন করা সম্ভব।');
            }

            $product = $account->product;
            if ($product->product_type !== 3) {
                throw new \Exception('শুধুমাত্র এফডিআর অ্যাকাউন্ট নবায়ন করা সম্ভব।');
            }

            $renewalDate = Carbon::createFromFormat('d-m-Y', $data['renewal_date'])->format('Y-m-d');
            $renewalMode = $data['renewal_mode']; // 'principal_only' or 'compound'
            
            $currentBalance = $account->current_balance;
            $originalPrincipal = $account->opening_balance;
            $accumulatedInterest = max(0, $currentBalance - $originalPrincipal);

            if ($renewalMode === 'principal_only') {
                if ($accumulatedInterest > 0) {
                    // Credit/Transfer the accumulated interest to the member's General Savings account
                    $gsAccount = MfSavingsAccount::where('member_id', $account->member_id)
                        ->where('status', 1)
                        ->whereHas('product', function($q) {
                            $q->where('product_type', 1);
                        })
                        ->first();

                    if (!$gsAccount) {
                        throw new \Exception('মুনাফা স্থানান্তরের জন্য সদস্যের কোনো সচল সাধারণ সঞ্চয় হিসাব পাওয়া যায়নি!');
                    }

                    // 1. Debit the interest from FDR
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $account->sister_concern_id,
                        'somiti_id'             => $account->somiti_id,
                        'mf_savings_account_id' => $account->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_WITHDRAW,
                        'amount'                => $accumulatedInterest,
                        'date'                  => $renewalDate,
                        'notes'                 => 'FDR Renewal: Accumulated profit transferred to GS (এফডিআর নবায়ন: পুঞ্জীভূত লভ্যাংশ সাধারণ সঞ্চয়ে স্থানান্তর)',
                        'created_by'            => auth()->id(),
                    ]);
                    $account->decrement('current_balance', $accumulatedInterest);

                    // 2. Credit the interest to GS
                    MfSavingsTransaction::create([
                        'sister_concern_id'     => $gsAccount->sister_concern_id,
                        'somiti_id'             => $gsAccount->somiti_id,
                        'mf_savings_account_id' => $gsAccount->id,
                        'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                        'amount'                => $accumulatedInterest,
                        'date'                  => $renewalDate,
                        'notes'                 => 'FDR Profit Transfer from FDR Account No: ' . $account->account_no,
                        'created_by'            => auth()->id(),
                    ]);
                    $gsAccount->increment('current_balance', $accumulatedInterest);
                }
                
                $newPrincipal = $originalPrincipal;
            } else {
                // Compound: Principal + Profit
                $newPrincipal = $currentBalance;
            }

            // Calculate new maturity date based on selected product/duration
            $selectedProduct = MfSavingsProduct::findOrFail($data['mf_savings_product_id'] ?? $product->id);
            $durationMonths = $selectedProduct->duration_months ?? $product->duration_months;
            $newMaturityDate = Carbon::parse($renewalDate)->addMonths((int)$durationMonths)->format('Y-m-d');

            // Update FDR Account state
            $account->update([
                'mf_savings_product_id' => $selectedProduct->id,
                'interest_rate'         => $selectedProduct->interest_rate,
                'duration_months'       => $durationMonths,
                'opening_balance'       => $newPrincipal,
                'current_balance'       => $newPrincipal,
                'opening_date'          => $renewalDate,
                'maturity_date'         => $newMaturityDate,
                'status'                => 1, // Keep Active
            ]);

            // Record renewal deposit transaction in FDR to show the rollover
            MfSavingsTransaction::create([
                'sister_concern_id'     => $account->sister_concern_id,
                'somiti_id'             => $account->somiti_id,
                'mf_savings_account_id' => $account->id,
                'transaction_type'      => MfSavingsTransaction::TYPE_DEPOSIT,
                'amount'                => $newPrincipal,
                'date'                  => $renewalDate,
                'notes'                 => 'FDR Manual Renewal (' . ($renewalMode === 'principal_only' ? 'Principal Only' : 'Principal + Profit Compounded') . ')',
                'created_by'            => auth()->id(),
            ]);

            return $account->fresh();
        });
    }
}