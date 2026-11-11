@extends('layouts.app')
@section('title', 'Member Send Money')
@section('content')
<div class="container-fluid">
@php($branch = auth()->user()->active_sister_concern)
    <h3>{{ $branch?->code ?: $branch?->id }} — {{ $branch?->name }}</h3>
<p>Completed transfers involving this branch. Transfers are posted automatically after member PIN confirmation.</p>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Reference / Date</th><th>Sender</th><th>Recipient</th><th>Amount</th><th>Charge</th><th>Total debit</th></tr></thead><tbody>
@forelse($transfers as $transfer)
<tr><td>{{ $transfer->reference }}<br>{{ $transfer->created_at }}</td><td>{{ $transfer->sender?->name }} / {{ $transfer->sender?->member_no }}</td><td>{{ $transfer->recipient?->name }} / {{ $transfer->recipient?->member_no }}</td><td>{{ $transfer->amount }}</td><td>{{ $transfer->fee }}</td><td>{{ $transfer->total }}</td></tr>
@empty<tr><td colspan="6">No transfers for this branch.</td></tr>@endforelse
</tbody></table></div>{{ $transfers->links() }}
</div>
@endsection
