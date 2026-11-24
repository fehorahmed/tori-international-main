@extends('layouts.app')
@section('title','Agent Transactions')
@section('content')
<div class="card"><div class="card-body">
<form method="get" class="row mb-3"><div class="col-md-3"><label>From</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control"></div><div class="col-md-3"><label>To</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control"></div><div class="col-md-3"><label>Type</label><select name="type" class="form-control"><option value="">All</option><option value="agent_cashin" @selected(request('type')==='agent_cashin')>Member Cash-in</option><option value="agent_cashout" @selected(request('type')==='agent_cashout')>Agent Cash-out</option></select></div><div class="col-md-3 pt-4"><button class="btn btn-primary">Search</button> <a class="btn btn-secondary" href="{{ route('card-management.agents.transactions') }}">Reset</a></div></form>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Date / Reference</th><th>Type</th><th>Agent</th><th>Customer</th><th>Amount</th><th>Charge</th><th>Commission</th><th>Status</th></tr></thead><tbody>
@forelse($records as $r)<tr><td>{{ $r->completed_at }}<br>{{ $r->reference }}</td><td>{{ $r->type==='agent_cashin' ? 'Member Cash-in' : 'Agent Cash-out' }}</td><td>{{ $r->agent?->customer?->name }}<br>{{ $r->agent?->agent_code }}</td><td>{{ $r->customer?->name }}</td><td>{{ $r->amount }}</td><td>{{ $r->customer_charge }}</td><td>{{ $r->agent_commission }}</td><td>{{ $r->status }}</td></tr>@empty<tr><td colspan="8">No transactions.</td></tr>@endforelse
</tbody></table></div>{{ $records->links() }}
</div></div>
@endsection
