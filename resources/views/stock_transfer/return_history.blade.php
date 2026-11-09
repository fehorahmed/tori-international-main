@extends('layouts.app')
@section('title','Return Transfer History')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-history text-success mr-2"></i> Return Transfer History</h3>
                <div class="card-tools">
                    <a href="{{ route('stock-transfers.pending_returns') }}" class="btn btn-warning btn-sm text-dark font-weight-bold">
                        <i class="fa fa-undo"></i> Pending Returns
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-success py-2">
                    <i class="fa fa-check-circle"></i>
                    These are <strong>shortage return transfers</strong> that have been successfully processed and received back into your warehouse.
                </div>
                <table id="historyReturnTable" class="table table-bordered table-hover table-sm">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Return Order No</th>
                        <th>Original Transfer</th>
                        <th>From Branch</th>
                        <th>To Branch (You)</th>
                        <th>Date</th>
                        <th>Items</th>
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
    $('#historyReturnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("stock-transfers.return_history_datatable") }}',
        columns: [
            { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'order_no',    name: 'order_no' },
            { data: 'parent_order', name: 'parent_order' },
            { data: 'from_branch', name: 'from_branch' },
            { data: 'to_branch',   name: 'to_branch' },
            { data: 'date',        name: 'date' },
            { data: 'items_count', name: 'items_count' },
            { data: 'action',      name: 'action', orderable: false, searchable: false },
        ]
    });
});
</script>
@endsection
