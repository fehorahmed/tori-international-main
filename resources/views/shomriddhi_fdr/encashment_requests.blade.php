@extends('layouts.app')
@section('title','FDR Encashment Requests')
@section('content')
<div class="card"><div class="card-body">
<form class="row mb-3" method="get"><div class="col-md-3"><label>From</label><input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control"></div><div class="col-md-3"><label>To</label><input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control"></div><div class="col-md-3"><label>Status</label><select name="status" class="form-control"><option value="">All</option>@foreach(['Pending','Approved','Rejected'] as $status)<option @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></div><div class="col-md-3 pt-4"><button class="btn btn-primary">Search</button> <a href="{{ route('shomriddhi-fdr.encashment-requests') }}" class="btn btn-secondary">Reset</a></div></form>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="table-responsive">
    <table id="table" class="table table-bordered table-striped" style="width:100%">
        <thead><tr><th>S/L</th><th>Requested</th><th>Member / FDR</th><th>Settlement preview</th><th>Status / Review</th><th>Action</th></tr></thead>
        <tbody></tbody>
    </table>
</div>
</div></div>
@endsection
@section('script')
<script>
$(function () {
    $('#table').on('submit', '.encashment-review-form', function (event) {
        event.preventDefault();
        const form = this;
        if ($(form).data('confirming')) return;
        const button = event.originalEvent && event.originalEvent.submitter;
        const decision = button && button.value;
        if (decision !== 'approve' && decision !== 'reject') return;
        const approving = decision === 'approve';
        $(form).data('confirming', true);
        Swal.fire({
            title: approving ? 'Approve encashment?' : 'Reject encashment?',
            text: approving
                ? 'The final settlement amount will be credited to the member’s savings account and the FDR will be closed.'
                : 'The request will be rejected. No funds will be returned and the FDR will remain open.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: approving ? 'Yes, approve' : 'Yes, reject',
            cancelButtonText: 'Cancel',
            confirmButtonColor: approving ? '#28a745' : '#dc3545',
            focusCancel: true
        }).then(function (result) {
            if (!result.isConfirmed) {
                $(form).data('confirming', false);
                return;
            }
            $('<input>', {type: 'hidden', name: 'decision', value: decision}).appendTo(form);
            $(form).find('button').prop('disabled', true);
            HTMLFormElement.prototype.submit.call(form);
        });
    });

    $('#table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {url: @json(route('shomriddhi-fdr.encashment-requests.datatable')), data: function (d) {
            d.start_date = $('input[name="start_date"]').val();
            d.end_date = $('input[name="end_date"]').val();
            d.status = $('select[name="status"]').val();
        }},
        order: [[1, 'desc']],
        pagingType: 'full_numbers',
        lengthMenu: [10, 25, 50, 100],
        autoWidth: false,
        columns: [
            {data:'DT_RowIndex', orderable:false, searchable:false},
            {data:'created_at', name:'created_at'},
            {data:'member', name:'member', orderable:false},
            {data:'settlement', orderable:false, searchable:false},
            {data:'status', name:'status'},
            {data:'action', orderable:false, searchable:false}
        ]
    });
});
</script>
@endsection
