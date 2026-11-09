@extends('layouts.app')
@section('title','Pending Return Transfers')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title"><i class="fa fa-undo text-warning mr-2"></i> Pending Return Transfers</h3>
                <div class="card-tools">
                    <a href="{{ route('stock-transfers.receive_list') }}" class="btn btn-info btn-sm">
                        <i class="fa fa-inbox"></i> Receive List
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="alert alert-warning py-2">
                    <i class="fa fa-info-circle"></i>
                    These are <strong>shortage items</strong> being returned to your branch from receivers. Receive them to add the stock back to your main warehouse.
                </div>
                <table id="returnTable" class="table table-bordered table-hover table-sm">
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
    $('#returnTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ route("stock-transfers.pending_returns_datatable") }}',
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
