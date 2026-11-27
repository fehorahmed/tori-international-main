@if($r->bonus_paid_at) Paid: {{ $r->bonus_paid_at }} / Voucher #{{ $r->payment_voucher_id }}
@elseif($r->referrer_type === 'agent' && $r->bonus_amount > 0)
    @if($r->branch_id == $branch)
    <form class="referral-payment-form" method="POST" action="{{ route('card-management.referrals.pay', $r->reward_id) }}">@csrf
    <select class="form-control mb-2" name="cash_head_id" required><option value="">Payment cash / bank ledger</option>@foreach($cashHeads as $head)<option value="{{ $head->id }}">{{ $head->name }}</option>@endforeach</select>
    <button type="submit" class="btn btn-success">Pay Tk {{ $r->bonus_amount }}</button></form>
    @else Pending at branch #{{ $r->branch_id }} @endif
@elseif($r->referrer_type === 'member' && $r->points > 0) Points credited; member redeems in app
@else — @endif