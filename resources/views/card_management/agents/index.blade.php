@extends('layouts.app')
@section('title','Member Agents')
@section('content')
<div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('card-management.agents.store') }}" class="form-inline mb-3">@csrf
<label class="mr-2">Member number / mobile</label><input name="member" class="form-control mr-2" required maxlength="64"><button class="btn btn-primary">Make Agent</button></form>
<form method="get" class="form-inline mb-3"><input name="search" value="{{ request('search') }}" class="form-control mr-2" placeholder="Name / member / mobile / agent code"><button class="btn btn-primary mr-2">Search</button><a class="btn btn-secondary" href="{{ route('card-management.agents.index') }}">Reset</a></form>
<div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Agent Code</th><th>Member</th><th>Mobile</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($agents as $agent)<tr><td>{{ $agent->agent_code }}<br><a target="_blank" href="{{ route('card-management.agents.qr', $agent->id) }}">View / Print QR</a></td><td>{{ $agent->customer?->name }}<br>{{ $agent->customer?->member_no }}</td><td>{{ $agent->customer?->mobile_no }}</td><td>{{ $agent->status }}</td><td><form method="post" action="{{ route('card-management.agents.status',$agent->id) }}">@csrf<input type="hidden" name="status" value="{{ $agent->status === 'Active' ? 'Suspended' : 'Active' }}"><button class="btn btn-sm btn-outline-primary">{{ $agent->status === 'Active' ? 'Suspend' : 'Activate' }}</button></form></td></tr>@empty<tr><td colspan="5">No agents.</td></tr>@endforelse
</tbody></table></div>{{ $agents->links() }}
</div></div>
@endsection
