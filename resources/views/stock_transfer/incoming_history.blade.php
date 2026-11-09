@extends('layouts.app')
@section('title','Transfer & Return History')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default card-tabs">
            <div class="card-header p-0 pt-1">
                <ul class="nav nav-tabs" id="historyTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active font-weight-bold text-uppercase" id="incoming-tab" data-toggle="pill" href="#incoming-history" role="tab" aria-controls="incoming-history" aria-selected="true">
                            <i class="fa fa-history mr-2 text-info"></i> Received Transfers
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link font-weight-bold text-uppercase" id="returns-history-tab" data-toggle="pill" href="#returns-history" role="tab" aria-controls="returns-history" aria-selected="false">
                            <i class="fa fa-undo mr-2 text-success"></i> Received Returns
                        </a>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="historyTabContent">
                    <!-- Standard Incoming History Tab -->
                    <div class="tab-pane fade show active" id="incoming-history" role="tabpanel" aria-labelledby="incoming-tab">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="text-muted"><i class="fa fa-check-circle text-info"></i> Standard Receipts History</h5>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Start Date</label>
                                <input autocomplete="off" type="text" class="form-control date-picker start_date" placeholder="Enter Start Date">
                            </div>
                            <div class="col-md-3">
                                <label>End Date</label>
                                <input autocomplete="off" type="text" class="form-control date-picker end_date" placeholder="Enter End Date">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-info bg-gradient-info form-control search_btn">Search</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="incomingTable" class="table table-bordered table-striped w-100">
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Order No.</th>
                                    <th>Date</th>
                                    <th>From</th>
                                    <th>To</th>
                                    <th>Notes</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
                        </div>
                    </div>

                    <!-- Received Returns Tab -->
                    <div class="tab-pane fade" id="returns-history" role="tabpanel" aria-labelledby="returns-history-tab">
                        <div class="alert alert-success py-2 mb-3">
                            <i class="fa fa-check-circle"></i>
                            These are <strong>shortage return transfers</strong> that have been successfully processed and received back into your warehouse.
                        </div>
                        <div class="table-responsive">
                            <table id="returnHistoryTable" class="table table-bordered table-striped w-100">
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
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    // 1. Standard Incoming History Datatable
    var incomingTable = $('#incomingTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('stock-transfers.incoming_datatable') }}",
            data: function (d) {
                d.start_date = $("#incoming-history .start_date").val();
                d.end_date = $("#incoming-history .end_date").val();
            }
        },
        "pagingType": "full_numbers",
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'order_no', name: 'order_no'},
            {data: 'date', name: 'date'},
            {data: 'from_warehouse', name: 'fromWarehouse.name'},
            {data: 'to_warehouse', name: 'toWarehouse.name'},
            {data: 'notes', name: 'notes'},
            {data: 'status', name: 'status', orderable: false},
            {data: 'action', name: 'action', orderable: false},
        ],
        order: [[1, 'desc']],
        "dom": 'lBfrtip',
        "buttons": datatableButtons(),
        "responsive": true, "autoWidth": false, "colReorder": true,
    });

    // 2. Return History Datatable
    var returnHistoryTable = $('#returnHistoryTable').DataTable({
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
        ],
        order: [[1, 'desc']],
        "responsive": true, "autoWidth": false
    });

    // Search and Filters
    $('#incoming-history .search_btn').on('click', function () {
        incomingTable.ajax.reload();
    });

    // Refresh Datatables on Tab Switch to prevent column layout squeezing
    $('a[data-toggle="pill"]').on('shown.bs.tab', function (e) {
        $($.fn.dataTable.tables(true)).DataTable().columns.adjust().responsive.recalc();
    });
});
</script>
@endsection
