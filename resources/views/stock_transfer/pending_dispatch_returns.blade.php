@extends('layouts.app')
@section('title','Pending Dispatch Return Transfers')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-truck text-primary mr-2"></i> Outbound Return Transfers</h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info py-2">
                    <i class="fa fa-info-circle"></i>
                    These are auto-generated <strong>return transfers</strong> for shortage items. Dispatch them when physically sending these shortage items back to the original sender.
                </div>
                <table id="dispatchReturnTable" class="table table-bordered table-hover table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Return Order No</th>
                        <th>Original Transfer</th>
                        <th>From Branch (You)</th>
                        <th>To Branch</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
$(function () {
    var table = $('#dispatchReturnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("stock-transfers.pending_dispatch_returns_datatable") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_no',    name: 'order_no' },
            { data: 'parent_order', name: 'parent_order' },
            { data: 'from_branch', name: 'from_branch' },
            { data: 'to_branch',   name: 'to_branch' },
            { data: 'date',        name: 'date' },
            { data: 'items_count', name: 'items_count' },
            { data: 'status_label', name: 'status_label', orderable: false },
            { data: 'action',      name: 'action', orderable: false, searchable: false },
        ]
    });

    $('body').on('click', '.btn-dispatch-return', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        Swal.fire({
            title: 'Dispatch Return Transfer?',
            text: 'Are you sure you want to dispatch this shortage return to the sender?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Dispatch!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
});
</script>
@endsection
