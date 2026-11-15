@extends('layouts.app')
@section('title', 'Referral Bonus & Commission')
@section('content')
<div class="container-fluid">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="GET" class="form-inline mb-3">
    <select name="type" class="form-control mr-2"><option value="">All referrers</option><option value="member" @selected(request('type') === 'member')>Member</option><option value="agent" @selected(request('type') === 'agent')>Agent</option></select>
    <input name="referrer_id" class="form-control mr-2" type="number" min="1" placeholder="Referrer record ID" value="{{ request('referrer_id') }}">
    <button class="btn btn-primary">Filter</button>
</form>
<div class="card"><div class="card-header">Referrer summary</div><div class="card-body table-responsive">
<table class="table table-bordered"><thead><tr><th>Referrer</th><th>New members</th><th>Fee paid / pending</th><th>Points earned / redeemed / available</th><th>Agent commission earned / paid / pending (Tk)</th><th>Milestone cash paid / pending (Tk)</th></tr></thead><tbody>
@forelse($summary as $s)
<tr><td>{{ ucfirst($s->referrer_type) }} #{{ $s->referrer_id }} — {{ $s->referrer?->name ?? 'Unavailable' }}<br>{{ $s->referrer_type === 'agent' ? $s->referrer?->username : $s->referrer?->member_no }}</td><td>{{ $s->referrals }}</td><td>{{ $s->fee_paid }} / {{ $s->fee_pending }}</td><td>{{ $s->points }} / {{ $s->redeemed_points }} / {{ $s->points - $s->redeemed_points }}</td><td>{{ number_format($s->commission, 2) }} / {{ number_format($s->paid, 2) }} / {{ number_format($s->commission - $s->paid, 2) }}</td><td>{{ number_format($s->milestone_paid, 2) }} / {{ number_format($s->milestone_pending, 2) }}</td></tr>
@empty<tr><td colspan="6">No referrals found.</td></tr>@endforelse
</tbody></table>{{ $summary->withQueryString()->links() }}</div></div>
<div class="card"><div class="card-header">Registration details & agent payments</div><div class="card-body table-responsive">
<p>Commission is payable only after the full registration fee is collected. Switch to the originating branch to pay its agent commissions.</p>
<table class="table table-bordered"><thead><tr><th>Member / Registered</th><th>Referrer / Code at registration</th><th>Registration fee</th><th>Reward</th><th>Payment</th></tr></thead><tbody>
@forelse($rows as $r)
<tr><td>{{ $r->customer_name }} / {{ $r->member_no }}<br>{{ $r->created_at }}</td><td>{{ ucfirst($r->referrer_type) }} #{{ $r->referrer_id }}<br>{{ $r->referral_code }}</td><td>{{ $r->fee_amount === null ? 'No fee obligation' : 'Tk '.$r->fee_amount }}<br>{{ $r->fee_paid_at ? 'Paid: '.$r->fee_paid_at : ($r->fee_amount === null ? 'Not applicable' : 'Pending') }}</td><td>@if(!$r->reward_id) Not earned @elseif($r->referrer_type === 'member') {{ $r->points }} points @else Tk {{ $r->bonus_amount }} @endif</td>
<td>@if($r->bonus_paid_at) Paid: {{ $r->bonus_paid_at }} / Voucher #{{ $r->payment_voucher_id }}
@elseif($r->referrer_type === 'agent' && $r->bonus_amount > 0)
    @if($r->branch_id == $branch)
    <form class="referral-payment-form" method="POST" action="{{ route('card-management.referrals.pay', $r->reward_id) }}">@csrf
    <select class="form-control mb-2" name="cash_head_id" required><option value="">Payment cash / bank ledger</option>@foreach($cashHeads as $head)<option value="{{ $head->id }}">{{ $head->name }}</option>@endforeach</select>
    <button type="submit" class="btn btn-success">Pay Tk {{ $r->bonus_amount }}</button></form>
    @else Pending at branch #{{ $r->branch_id }} @endif
@elseif($r->referrer_type === 'member' && $r->points > 0) Points credited; member redeems in app
@else — @endif</td></tr>
@empty<tr><td colspan="5">No registrations found.</td></tr>@endforelse
</tbody></table>{{ $rows->withQueryString()->links() }}</div></div>
</div>
@endsection
@push('script')
<script>
document.querySelectorAll('.referral-payment-form').forEach(form => {
let confirming = false;
form.addEventListener('submit', async event => {
    event.preventDefault();
    if (confirming || !form.reportValidity()) return;
    confirming = true;
    const result = await Swal.fire({title: 'Confirm agent payment?', text: 'This records the commission as paid from the selected cash / bank ledger.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Confirm payment'});
    if (result.isConfirmed || result.value === true) { form.querySelector('button').disabled = true; HTMLFormElement.prototype.submit.call(form); }
    else confirming = false;
});
});
</script>
@endpush
