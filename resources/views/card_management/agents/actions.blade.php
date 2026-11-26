@if($agent->status !== 'Removed')
<a class="btn btn-sm btn-outline-secondary mb-1" target="_blank" rel="noopener" href="{{ route('card-management.agents.qr', $agent->id) }}">View / Print QR</a>
@endif
<form method="post" action="{{ route('card-management.agents.status', $agent->id) }}" class="d-inline agent-confirm-form" data-agent-action="{{ $agent->status === 'Active' ? 'suspend' : ($agent->status === 'Removed' ? 'restore' : 'activate') }}" data-agent-code="{{ $agent->agent_code }}">
    @csrf
    <input type="hidden" name="status" value="{{ $agent->status === 'Active' ? 'Suspended' : 'Active' }}">
    <button class="btn btn-sm btn-outline-primary mb-1">{{ $agent->status === 'Active' ? 'Suspend' : ($agent->status === 'Removed' ? 'Make Agent Again' : 'Activate') }}</button>
</form>
@if($agent->status !== 'Removed')
<form method="post" action="{{ route('card-management.agents.status', $agent->id) }}" class="d-inline agent-confirm-form" data-agent-action="remove" data-agent-code="{{ $agent->agent_code }}">
    @csrf
    <input type="hidden" name="status" value="Removed">
    <button class="btn btn-sm btn-outline-danger mb-1">Make Regular Member</button>
</form>
@endif
