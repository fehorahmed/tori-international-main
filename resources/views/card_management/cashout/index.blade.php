@extends('layouts.app')
@section('title', 'Membership Cash-out Counter')
@section('content')
<div class="container-fluid">
    @php($branch = auth()->user()->active_sister_concern)
    <h3>{{ $branch?->code ?: $branch?->id }} — {{ $branch?->name }}</h3>
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
    <div class="card"><div class="card-header">This branch counter</div><div class="card-body">
        <form method="POST" action="{{ route('card-management.cashout.booth') }}">@csrf
            <label>Cash ledger for payouts</label><select name="cash_head_id" class="form-control" required>@foreach($cashHeads as $head)<option value="{{ $head->id }}" @selected($head->id == $booth?->cash_head_id)>{{ $head->name }}</option>@endforeach</select>
            <label class="mt-2"><input type="checkbox" name="enabled" value="1" @checked($booth?->enabled ?? true)> Counter enabled</label>
            <label class="ml-3"><input type="checkbox" name="rotate" value="1"> Replace QR (old printed QR stops working)</label>
            <button class="btn btn-primary ml-3">Save counter</button>
        </form>
        @if($booth)
            <div class="mt-3"><img src="{{ route('card-management.cashout.qr') }}" width="260" alt="Branch cash-out QR"><p>Members scan this QR in the Cash Out screen.</p>
                <a href="{{ route('card-management.cashout.qr') }}" target="_blank" rel="noopener">Open QR to print</a>
            </div>
        @endif
    </div></div>
    <h4>Cash-out requests <a class="btn btn-secondary btn-sm" href="{{ route('card-management.cashout.index') }}">Refresh</a></h4>
    <p>Verify the member and the request reference shown in their app before handing over cash. Accept reserves the request for the counter. Accepted requests do not expire automatically. Confirm cash payment at handover; reject with a reason if payment cannot proceed.</p>
    <div class="card"><div class="card-body">
        <div id="cashout-filter-error" class="alert alert-danger d-none" role="alert"></div>
        <form id="cashout-filters" class="row">
            <div class="col-md-3 mb-3"><label for="from_date">From Date</label><input type="date" id="from_date" class="form-control"></div>
            <div class="col-md-3 mb-3"><label for="to_date">To Date</label><input type="date" id="to_date" class="form-control"></div>
            <div class="col-md-3 mb-3"><label for="status">Status</label><select id="status" class="form-control">
                <option value="">All statuses</option>
                @foreach(['Pending', 'Accepted', 'Completed', 'Rejected', 'Cancelled', 'Expired'] as $status)
                <option value="{{ $status }}">{{ $status }}</option>
                @endforeach
            </select></div>
            <div class="col-md-3 mb-3">
                <label for="cashout-member">Member</label>
                <select id="cashout-member" name="customer_id" class="form-control">
                    <option value="">All members</option>
                    @foreach($members as $member)
                    <option value="{{ $member->id }}">{{ implode(' / ', array_filter([$member->name, $member->member_no, $member->mobile_no])) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3 align-self-end"><button type="submit" class="btn btn-primary">Search</button> <button type="reset" class="btn btn-secondary">Reset</button></div>
        </form>
        <div class="table-responsive"><table id="table" class="table table-bordered table-striped w-100"><thead><tr>
            <th>Reference</th><th>Requested At</th><th>Member</th><th>Cash Amount</th><th>Charge</th><th>Total Debit</th><th>Status</th><th>Actions</th>
        </tr></thead></table></div>
    </div></div>
</div>
@endsection

@push('script')
<script>
$(function () {
    let filters = {};
    const error = $('#cashout-filter-error');
    $('#cashout-member').select2({width: '100%', placeholder: 'All members', allowClear: true});
    const table = $('#table').DataTable({processing: true, serverSide: true, order: [[1, 'desc'], [0, 'desc']],
        ajax: {url: @json(route('card-management.cashout.index')),
            data: function (data) { Object.assign(data, filters); },
            error: function (xhr) { error.text(xhr.responseJSON?.message || 'Unable to load cash-out requests.').removeClass('d-none'); }},
        columns: [{data:'reference'}, {data:'created_at'}, {data:'member', orderable:false},
            {data:'amount'}, {data:'fee'}, {data:'total'}, {data:'status', searchable:false, orderable:false},
            {data:'actions', searchable:false, orderable:false}]
    });
    $('#cashout-filters').on('submit', function (event) {
        event.preventDefault();
        error.addClass('d-none').empty();
        const from = $('#from_date').val(), to = $('#to_date').val();
        if (from && to && to < from) {
            error.text('To date must be on or after from date.').removeClass('d-none'); return;
        }
        filters = {from_date:from, to_date:to, status:$('#status').val(), customer_id:$('#cashout-member').val()};
        table.ajax.reload();
    });
    $('#cashout-filters').on('reset', function () {
        $('#cashout-member').val('').trigger('change.select2');
        filters = {};
        error.addClass('d-none').empty();
        table.search('').columns().search('');
        table.order([[1,'desc'], [0,'desc']]).ajax.reload();
    });
    $('#table').on('click', '.cashout-details', function () {
        const row = table.row($(this).closest('tr'));
        if (row.child.isShown()) row.child.hide();
        else row.child(row.data().details).show();
    });
});

    document.addEventListener('submit', function (event) {
        const form = event.target;
        if (!form.matches('form[data-cashout-confirm]')) return;
            event.preventDefault();
            if (form.dataset.confirming === 'true' || !form.reportValidity()) return;
            form.dataset.confirming = 'true';
            Swal.fire({
                title: form.dataset.confirmTitle,
                text: form.dataset.confirmMessage,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: form.dataset.confirmButton,
                cancelButtonText: 'Go back',
                focusCancel: true,
                allowOutsideClick: false
            }).then(function (result) {
                if (result.isConfirmed || result.value === true) {
                    form.querySelectorAll('button[type="submit"], button:not([type])').forEach(function (button) { button.disabled = true; });
                    HTMLFormElement.prototype.submit.call(form);
                } else {
                    form.dataset.confirming = 'false';
                }
            }).catch(function () { form.dataset.confirming = 'false'; });
    });
</script>
@endpush
