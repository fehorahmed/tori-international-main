@extends('layouts.app')
@section('title', 'Referral Bonus & Commission')
@section('content')
<div class="container-fluid">
@if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div id="referral-error" class="alert alert-danger d-none" role="alert"></div>
<form id="referral-filters" class="form-inline mb-3">
    <label for="referral-from" class="mr-2">From Date</label><input type="date" id="referral-from" name="start_date" value="{{ request('start_date') }}" class="form-control mr-3 mb-2">
    <label for="referral-to" class="mr-2">To Date</label><input type="date" id="referral-to" name="end_date" value="{{ request('end_date') }}" class="form-control mr-3 mb-2">
    <select name="type" id="referrer-type" class="form-control mr-2"><option value="">All referrers</option><option value="member" @selected(request('type') === 'member')>Member</option><option value="agent" @selected(request('type') === 'agent')>Agent</option></select>
    <input name="referrer_id" id="referrer-id" class="form-control mr-2" type="number" min="1" placeholder="Referrer record ID" value="{{ request('referrer_id') }}">
    <button class="btn btn-primary mr-2">Filter</button><button type="reset" class="btn btn-secondary">Reset</button>
</form>
<p class="text-muted small">Date filters use registration date. Earned rewards follow the selected registrations; redeemed/shopping/available points and milestone balances remain lifetime totals.</p>
<div class="card"><div class="card-header">Referrer summary</div><div class="card-body table-responsive">
<table id="referral-summary" class="table table-bordered table-striped w-100"><thead><tr><th>Referrer</th><th>New members</th><th>Fee paid / pending</th><th>Points earned / redeemed / shopping (net) / available</th><th>Agent commission earned / paid / pending (Tk)</th><th>Milestone cash paid / pending (Tk)</th></tr></thead></table>
</div></div>
<div class="card"><div class="card-header">Registration details & agent payments</div><div class="card-body table-responsive">
<p>Commission is payable only after the full registration fee is collected. Switch to the originating branch to pay its agent commissions.</p>
<table id="referral-details" class="table table-bordered table-striped w-100"><thead><tr><th>Member</th><th>Registered</th><th>Referrer / Code at registration</th><th>Registration fee</th><th>Reward</th><th>Payment</th></tr></thead></table>
</div></div></div>
@endsection
@section('script')
<script>
$(function () {
    const error = $('#referral-error');
    let filters = {type: $('#referrer-type').val(), referrer_id: $('#referrer-id').val(), start_date: $('#referral-from').val(), end_date: $('#referral-to').val()};
    function ajax(kind) {
        return {
            url: @json(route('card-management.referrals.index')),
            data: function (data) { Object.assign(data, filters, {table: kind}); },
            error: function (xhr) { error.text(xhr.responseJSON?.message || 'Unable to load referrals. Please try again.').removeClass('d-none'); }
        };
    }
    const summary = $('#referral-summary').DataTable({
        processing: true, serverSide: true, pageLength: 25, ajax: ajax('summary'), order: [[1, 'desc']],
        columns: [
            {data: 'referrer_label', orderable: false}, {data: 'referrals', name: 'referrals'},
            {data: 'fee_label', orderable: false}, {data: 'points_label', orderable: false},
            {data: 'commission_label', orderable: false}, {data: 'milestone_label', orderable: false}
        ]
    });
    const details = $('#referral-details').DataTable({
        processing: true, serverSide: true, pageLength: 25, ajax: ajax('details'), order: [[1, 'desc']],
        columns: [
            {data: 'member_label', name: 'c.name'}, {data: 'created_at', name: 'r.created_at'},
            {data: 'referrer_label', orderable: false}, {data: 'fee_label', orderable: false},
            {data: 'reward_label', orderable: false}, {data: 'payment', orderable: false, searchable: false}
        ]
    });
    $('#referral-summary_filter input, #referral-details_filter input').attr('maxlength', 100);
    $('#referral-filters').on('submit', function (event) {
        event.preventDefault(); error.addClass('d-none').empty();
        const from = $('#referral-from').val(), to = $('#referral-to').val();
        if (from && to && to < from) {
            error.text('To date must be on or after from date.').removeClass('d-none');
            return;
        }
        filters = {type: $('#referrer-type').val(), referrer_id: $('#referrer-id').val(), start_date: $('#referral-from').val(), end_date: $('#referral-to').val()};
        summary.ajax.reload(); details.ajax.reload();
    }).on('reset', function (event) {
        event.preventDefault(); $('#referrer-type, #referrer-id, #referral-from, #referral-to').val(''); filters = {};
        error.addClass('d-none').empty();
        summary.search('').order([[1, 'desc']]).ajax.reload();
        details.search('').order([[1, 'desc']]).ajax.reload();
    });
    $(document).on('submit', '.referral-payment-form', async function (event) {
        event.preventDefault(); const form = this;
        if (form.dataset.confirming === '1' || !form.reportValidity()) return;
        form.dataset.confirming = '1';
        const result = await Swal.fire({title: 'Confirm agent payment?', text: 'This records the commission as paid from the selected cash / bank ledger.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Confirm payment', focusCancel: true});
        if (result.isConfirmed || result.value === true) { form.querySelector('button').disabled = true; HTMLFormElement.prototype.submit.call(form); }
        else delete form.dataset.confirming;
    });
});
</script>
@endsection
