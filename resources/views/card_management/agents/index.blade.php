@extends('layouts.app')
@section('title','Member Agents')
@section('content')
<div class="card"><div class="card-body">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any() && !$errors->has('member'))<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form method="post" action="{{ route('card-management.agents.store') }}" class="form-inline mb-3 agent-confirm-form" data-agent-action="make">@csrf
<label class="mr-2">Member number / mobile</label><input name="member" value="{{ old('member') }}" class="form-control mr-2 @error('member') is-invalid @enderror" required maxlength="64"><button class="btn btn-primary">Make Agent</button></form>
@error('member')<div class="text-danger mb-3" role="alert">{{ $message }}</div>@enderror
<p class="text-muted small">Make Agent applies to members registered under the selected branch. Current branch: <strong>{{ auth()->user()->active_sister_concern?->name ?? 'No branch selected' }}</strong>.</p>
<div id="agent-table-error" class="alert alert-danger d-none" role="alert"></div>
<button type="button" id="reset-agents" class="btn btn-secondary mb-3">Reset Search</button>
<div class="table-responsive">
    <table id="agents-table" class="table table-bordered table-striped w-100">
        <thead><tr><th>Agent Code</th><th>Member Name</th><th>Member No</th><th>Mobile</th><th>Status</th><th>Action</th></tr></thead>
    </table>
</div>
</div></div>
@endsection
@section('script')
<script>
$(function () {
    @if($errors->any())
    Swal.fire({icon: 'error', title: 'Unable to make agent', text: @json($errors->first())});
    @endif
    const error = $('#agent-table-error');
    const table = $('#agents-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'desc']],
        search: {search: @json(request('search', ''))},
        ajax: {
            url: @json(route('card-management.agents.index')),
            error: function (xhr) {
                error.text(xhr.responseJSON?.message || 'Unable to load agents. Please try again.').removeClass('d-none');
            }
        },
        columns: [
            {data: 'agent_code', name: 'membership_agents.agent_code'},
            {data: 'member_name', name: 'customer.name'},
            {data: 'member_no', name: 'customer.member_no'},
            {data: 'mobile_no', name: 'customer.mobile_no'},
            {data: 'status', name: 'membership_agents.status'},
            {data: 'action', orderable: false, searchable: false}
        ],
        language: {searchPlaceholder: 'Name / member / mobile / agent code'}
    });
    // Delegation also covers action forms loaded by DataTables on later pages.
    $(document).on('submit', '.agent-confirm-form', function (event) {
        event.preventDefault();
        const form = this;
        if (form.dataset.confirming === '1') return;
        if (!form.reportValidity()) return;
        const action = form.dataset.agentAction;
        const member = action === 'make'
            ? form.querySelector('[name="member"]').value.trim()
            : form.dataset.agentCode;
        const options = {
            remove: {title: 'Make this agent a regular member?', text: 'Agent: ' + member + '. Agent services will stop and regular member features will return. Balance and history will be kept.', button: 'Yes, Make Regular Member', color: '#d33'},
            restore: {title: 'Make this member an agent again?', text: 'Agent: ' + member + '. Agent services will be restored.', button: 'Yes, Make Agent Again', color: '#3085d6'},
            make: {title: 'Make this member an agent?', text: 'Member: ' + member + '. This member will get agent access.', button: 'Yes, Make Agent', color: '#3085d6'},
            suspend: {title: 'Suspend this agent?', text: 'Agent: ' + member + '. Their agent services will be suspended.', button: 'Yes, Suspend', color: '#d33'},
            activate: {title: 'Activate this agent?', text: 'Agent: ' + member + '. Their agent services will be enabled.', button: 'Yes, Activate', color: '#3085d6'}
        }[action];
        if (!options) return;
        form.dataset.confirming = '1';
        Swal.fire({
            icon: 'warning',
            title: options.title,
            text: options.text,
            showCancelButton: true,
            confirmButtonText: options.button,
            cancelButtonText: 'Cancel',
            confirmButtonColor: options.color,
            focusCancel: true
        }).then(function (result) {
            if (!(result.isConfirmed || result.value === true)) {
                delete form.dataset.confirming;
                return;
            }
            $(form).find('button').prop('disabled', true);
            HTMLFormElement.prototype.submit.call(form);
        });
    });
    $('#agents-table_filter input').attr('maxlength', 100);
    $('#agents-table').on('preXhr.dt', function () { error.addClass('d-none').empty(); });
    $('#reset-agents').on('click', function () {
        table.search('').columns().search('');
        table.order([[0, 'desc']]).draw();
    });
});
</script>
@endsection
