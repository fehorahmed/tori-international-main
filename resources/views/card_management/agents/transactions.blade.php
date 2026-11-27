@extends('layouts.app')
@section('title', 'Agent Transactions')
@section('content')
<div class="card"><div class="card-header"><h3 class="card-title">Filter Agent Transactions</h3></div><div class="card-body">
<div id="transaction-table-error" class="alert alert-danger d-none" role="alert"></div>
<form id="agent-transaction-filters">
    <div class="row">
        <div class="col-md-3 mb-3"><label for="start_date">From</label><input id="start_date" type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">@error('start_date')<div class="text-danger">{{ $message }}</div>@enderror</div>
        <div class="col-md-3 mb-3"><label for="end_date">To</label><input id="end_date" type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">@error('end_date')<div class="text-danger">{{ $message }}</div>@enderror</div>
        <div class="col-md-3 mb-3"><label for="type">Type</label><select id="type" name="type" class="form-control"><option value="">All</option><option value="agent_cashin" @selected(request('type') === 'agent_cashin')>Member Cash-in</option><option value="agent_cashout" @selected(request('type') === 'agent_cashout')>Agent Cash-out</option></select></div>
        <div class="col-md-3 mb-3">
            <label for="agent_id">Agent</label>
            <select id="agent_id" name="agent_id" class="form-control">
                <option value="">All agents</option>
                @foreach($agents as $agent)
                <option value="{{ $agent->id }}" @selected(request('agent_id') == $agent->id)>{{ implode(' / ', array_filter([$agent->customer?->name, $agent->agent_code, $agent->customer?->member_no, $agent->customer?->mobile_no])) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label for="customer_id">Customer</label>
            <select id="customer_id" name="customer_id" class="form-control">
                <option value="">All customers</option>
                @foreach($customers as $customer)
                <option value="{{ $customer->id }}" @selected(request('customer_id') == $customer->id)>{{ implode(' / ', array_filter([$customer->name, $customer->member_no, $customer->mobile_no])) }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <button type="submit" class="btn btn-primary">Search</button>
    <button type="reset" class="btn btn-secondary">Reset</button>
</form>
</div></div>
<div class="card"><div class="card-body table-responsive">
    <table id="agent-transactions-table" class="table table-bordered table-striped w-100">
        <thead><tr><th>Date</th><th>Reference</th><th>Type</th><th>Agent</th><th>Customer</th><th>Amount</th><th>Charge</th><th>Commission</th><th>Status</th></tr></thead>
    </table>
</div></div>
@endsection
@section('script')
<script>
$(function () {
    const form = $('#agent-transaction-filters');
    const error = $('#transaction-table-error');
    $('#agent_id').select2({width: '100%', placeholder: 'All agents', allowClear: true});
    $('#customer_id').select2({width: '100%', placeholder: 'All customers', allowClear: true});
    let filters = {start_date: $('#start_date').val(), end_date: $('#end_date').val(), type: $('#type').val(), agent_id: $('#agent_id').val(), customer_id: $('#customer_id').val()};
    const table = $('#agent-transactions-table').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        order: [[0, 'desc'], [1, 'desc']],
        ajax: {
            url: @json(route('card-management.agents.transactions')),
            data: function (data) { Object.assign(data, filters); },
            error: function (xhr) {
                error.text(xhr.responseJSON?.message || 'Unable to load agent transactions. Please try again.').removeClass('d-none');
            }
        },
        columns: [
            {data: 'completed_at', name: 'completed_at'},
            {data: 'reference', name: 'reference'},
            {data: 'type_label', name: 'type'},
            {data: 'agent_label', orderable: false},
            {data: 'customer_label', orderable: false},
            {data: 'amount', name: 'amount'},
            {data: 'customer_charge', name: 'customer_charge'},
            {data: 'agent_commission', name: 'agent_commission'},
            {data: 'status', name: 'status'}
        ],
        language: {searchPlaceholder: 'Reference / name / mobile / agent code'}
    });
    $('#agent-transactions-table_filter input').attr('maxlength', 100);
    $('#agent-transactions-table').on('preXhr.dt', function () { error.addClass('d-none').empty(); });
    form.on('submit', function (event) {
        event.preventDefault();
        error.addClass('d-none').empty();
        const from = $('#start_date').val(), to = $('#end_date').val();
        if (from && to && to < from) {
            error.text('To date must be on or after from date.').removeClass('d-none');
            return;
        }
        filters = {start_date: from, end_date: to, type: $('#type').val(), agent_id: $('#agent_id').val(), customer_id: $('#customer_id').val()};
        table.ajax.reload();
    });
    form.on('reset', function (event) {
        event.preventDefault();
        form.find('input, select').val('');
        $('#agent_id, #customer_id').trigger('change.select2');
        filters = {};
        error.addClass('d-none').empty();
        table.search('').columns().search('');
        table.order([[0, 'desc'], [1, 'desc']]).ajax.reload();
    });
});
</script>
@endsection
