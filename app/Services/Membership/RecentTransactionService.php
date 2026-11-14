<?php

namespace App\Services\Membership;

use App\Models\MembershipCashout;
use App\Models\MembershipTransfer;
use App\Models\ShomriddhiAdvance\ShomriddhiTransaction;
use App\Models\Supplier;
use Carbon\Carbon;

class RecentTransactionService
{
    public function forCustomer(Supplier $customer): array
    {
        $logs = ShomriddhiTransaction::where('customer_id', $customer->id)->where('status', 'Approved')->orderByDesc('date')->orderByDesc('id')->limit(150)->get();
        $ids = $logs->pluck('voucher_id')->filter()->unique();
        $registrationFees = \Illuminate\Support\Facades\DB::table('membership_registration_fees')->where('customer_id', $customer->id)
            ->whereNotNull('paid_at')->get()->keyBy('voucher_id');
        $orderVouchers = \App\Models\Account\Voucher::whereIn('id', $ids)->whereIn('payment_purpose', [OrderPaymentService::PAYMENT, OrderPaymentService::REFUND])->get()->keyBy('id');
        $transfers = MembershipTransfer::with(['sender', 'recipient'])->where(fn ($q) => $q->where('sender_id', $customer->id)->orWhere('recipient_id', $customer->id))->where(function ($q) use ($ids) {
            $q->whereIn('voucher_id', $ids)->orWhereIn('recipient_voucher_id', $ids)->orWhereIn('fee_voucher_id', $ids);
        })->get();
        $cashouts = MembershipCashout::with('branch')->where('customer_id', $customer->id)->where('status', 'Completed')->where(function ($q) use ($ids) {
            $q->whereIn('voucher_id', $ids)->orWhereIn('source_voucher_id', $ids)->orWhereIn('fee_voucher_id', $ids);
        })->get();
        $transferMap = [];
        $cashoutMap = [];
        foreach ($transfers as $t) {
            foreach (array_filter([$t->voucher_id, $t->recipient_voucher_id, $t->fee_voucher_id]) as $id) {
                $transferMap[$id] = $t;
            }
        }
        foreach ($cashouts as $t) {
            foreach (array_filter([$t->voucher_id, $t->source_voucher_id, $t->fee_voucher_id]) as $id) {
                $cashoutMap[$id] = $t;
            }
        }
        $seen = [];
        $rows = [];
        foreach ($logs as $log) {
            $credit = $log->type === 'deposit';
            $amount = $log->amount;
            $fee = '0.00';
            $net = $amount;
            $note = $log->note;
            $id = 'savings-'.$log->id;
            $reference = 'SV-'.$log->id;
            $category = 'Savings';
            $categoryBn = 'সঞ্চয়';
            $icon = $credit ? 'deposit' : 'withdraw';
            $en = $credit ? 'Savings deposit' : 'Savings withdrawal';
            $bn = $credit ? 'সঞ্চয় জমা' : 'সঞ্চয় উত্তোলন';
            $at = $log->date;
            if ($orderPayment = $orderVouchers[$log->voucher_id] ?? null) {
                $en = $credit ? 'Ecommerce payment refund' : 'Ecommerce order payment';
                $bn = $credit ? 'ইকমার্স পেমেন্ট ফেরত' : 'ইকমার্স অর্ডার পেমেন্ট';
                $category = 'Ecommerce';
                $categoryBn = 'ইকমার্স';
                $reference = $orderPayment->voucher_no;
                $at = $log->created_at;
            } elseif ($registration = $registrationFees[$log->voucher_id] ?? null) {
                $id = 'registration-fee-'.$registration->id;
                $reference = 'RF-'.$registration->id;
                $en = 'Membership registration fee';
                $bn = 'সদস্য নিবন্ধন ফি';
                $category = 'Registration fee';
                $categoryBn = 'নিবন্ধন ফি';
                $at = $registration->paid_at;
            } elseif ($t = $transferMap[$log->voucher_id] ?? null) {
                $id = 'transfer-'.$t->id;
                $sent = $t->sender_id == $customer->id;
                $credit = ! $sent;
                $amount = $sent ? $t->total : $t->amount;
                $net = $t->amount;
                $fee = $sent ? $t->fee : '0.00';
                $name = $sent ? $t->recipient?->name : $t->sender?->name;
                $en = ($sent ? 'Sent to ' : 'Received from ').$name;
                $bn = ($sent ? 'টাকা পাঠিয়েছেন: ' : 'টাকা পেয়েছেন: ').$name;
                $reference = $t->reference;
                $category = 'Send Money';
                $categoryBn = 'সেন্ড মানি';
                $icon = 'transfer';
                $at = $t->created_at;
                $note = null;
            } elseif ($t = $cashoutMap[$log->voucher_id] ?? null) {
                $id = 'cashout-'.$t->id;
                $amount = $t->total;
                $net = $t->amount;
                $fee = $t->fee;
                $credit = false;
                $en = 'Cash-out — '.$t->branch?->name;
                $bn = 'ক্যাশ আউট — '.$t->branch?->name;
                $reference = $t->reference;
                $category = 'Cash-out';
                $categoryBn = 'ক্যাশ আউট';
                $icon = 'withdraw';
                $at = $t->completed_at;
                $note = null;
            }
            if (isset($seen[$id])) {
                continue;
            }$seen[$id] = true;
            $rows[] = ['id' => $id, 'reference' => $reference, 'title_en' => $en, 'title_bn' => $bn, 'category_en' => $category, 'category_bn' => $categoryBn, 'icon_type' => $icon, 'amount' => (string) $amount, 'net_amount' => (string) $net, 'fee' => (string) $fee, 'is_credit' => $credit, 'status_en' => 'Completed', 'status_bn' => 'সম্পন্ন', 'occurred_at' => Carbon::parse($at)->toIso8601String(), 'note' => $note];
        }
        usort($rows, fn ($a, $b) => Carbon::parse($b['occurred_at'])->timestamp <=> Carbon::parse($a['occurred_at'])->timestamp);

        return array_slice($rows,0,50);
    }
}
