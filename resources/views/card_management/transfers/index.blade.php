@extends('layouts.app')
@section('title', 'Member Send Money')
@section('content')
<div class="container-fluid">
@php($branch = auth()->user()->active_sister_concern)
    <h3>{{ $branch?->code ?: $branch?->id }} — {{ $branch?->name }}</h3>
    <p>Completed transfers involving this branch. Transfers are posted automatically after member PIN confirmation.</p>
    <div class="card">
        <div class="card-header"><h3 class="card-title">Filter Transfers</h3></div>
        <div class="card-body">
            <div id="filter-error" class="alert alert-danger d-none" role="alert"></div>
            <form id="transfer-filters">
                <div class="row">
                    <div class="col-md-3 mb-3"><label for="from_date">From Date</label><input type="date" id="from_date" class="form-control"></div>
                    <div class="col-md-3 mb-3"><label for="to_date">To Date</label><input type="date" id="to_date" class="form-control"></div>
                    @foreach(['sender' => ['Sender', $senders], 'recipient' => ['Recipient', $recipients]] as $field => $config)
                    <div class="col-md-3 mb-3">
                        <label for="{{ $field }}">{{ $config[0] }}</label>
                        <select id="{{ $field }}" name="{{ $field }}_id" class="form-control">
                            <option value="">All {{ strtolower($config[0]) }}s</option>
                            @foreach($config[1] as $member)
                            <option value="{{ $member->id }}">{{ implode(' / ', array_filter([$member->name, $member->member_no, $member->mobile_no])) }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endforeach
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
                <button type="reset" class="btn btn-secondary">Reset</button>
            </form>
        </div>
    </div>
    <div class="card"><div class="card-body table-responsive">
        <table id="table" class="table table-bordered table-striped w-100">
            <thead><tr><th>Reference</th><th>Date</th><th>Sender</th><th>Recipient</th><th>Amount</th><th>Charge</th><th>Total debit</th></tr></thead>
        </table>
    </div></div>
</div>
@endsection
@section('script')
<script>
$(function () {
    let filters = {};
    const error = $('#filter-error');
    $('#sender').select2({width: '100%', placeholder: 'All senders', allowClear: true});
    $('#recipient').select2({width: '100%', placeholder: 'All recipients', allowClear: true});
    const table = $('#table').DataTable({
        processing: true,
        serverSide: true,
        order: [[1, 'desc'], [0, 'desc']],
        ajax: {
            url: @json(route('card-management.transfers.index')),
            data: function (data) { Object.assign(data, filters); },
            error: function (xhr) { error.text(xhr.responseJSON?.message || 'Unable to load transfers. Please try again.').removeClass('d-none'); }
        },
        columns: [
            {data: 'reference'}, {data: 'created_at'},
            {data: 'sender_label', orderable: false}, {data: 'recipient_label', orderable: false},
            {data: 'amount'}, {data: 'fee'}, {data: 'total'}
        ]
    });
    $('#transfer-filters').on('submit', function (event) {
        event.preventDefault();
        error.addClass('d-none').empty();
        const from = $('#from_date').val(), to = $('#to_date').val();
        if (from && to && to < from) {
            error.text('To date must be on or after from date.').removeClass('d-none');
            return;
        }
        filters = {from_date: from, to_date: to, sender_id: $('#sender').val(), recipient_id: $('#recipient').val()};
        table.ajax.reload();
    });
    $('#transfer-filters').on('reset', function () {
        $('#sender, #recipient').val('').trigger('change.select2');
        filters = {};
        error.addClass('d-none').empty();
        table.search('').columns().search('');
        table.order([[1, 'desc'], [0, 'desc']]).ajax.reload();
    });
});
</script>
@endsection
