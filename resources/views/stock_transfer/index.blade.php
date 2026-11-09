@extends('layouts.app')
@section('title','Transfer Stock List')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title m-0"><i class="fa fa-truck text-info mr-2"></i> Standard Outgoing Transfers</h3>
                    @if (auth()->user()->can('stock_transfer'))
                        <a href="{{ route('stock-transfers.create') }}" class="btn btn-info bg-gradient-info btn-sm">
                            <i class="fa fa-plus-circle"></i> Create Stock Transfer
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label>Start Date</label>
                                <input autocomplete="off" type="text" class="form-control date-picker start_date" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" placeholder="Enter Start Date">
                            </div>
                            <div class="col-md-3">
                                <label>End Date</label>
                                <input autocomplete="off" type="text" class="form-control date-picker end_date" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" placeholder="Enter End Date">
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-info bg-gradient-info form-control search_btn">Search</button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="standardTable" class="table table-bordered table-striped w-100">
                                <thead>
                                <tr>
                                    <th>Order No.</th>
                                    <th>Date</th>
                                    <th>From Branch</th>
                                    <th>To Branch</th>
                                    <th>Notes</th>
                                    <th>Status</th>
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
    // 1. Standard Transfers Datatable
    var standardTable = $('#standardTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('stock-transfers.datatable') }}",
            data: function (d) {
                d.start_date = $("#standard-transfers .start_date").val();
                d.end_date = $("#standard-transfers .end_date").val();
            }
        },
        "pagingType": "full_numbers",
        "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],
        columns: [
            {data: 'order_no', name: 'order_no'},
            {data: 'date', name: 'date'},
            {data: 'from_warehouse', name: 'fromWarehouse.name'},
            {data: 'to_warehouse', name: 'toWarehouse.name'},
            {data: 'notes', name: 'notes'},
            {data: 'status', name: 'status', orderable: false},
            {data: 'action', name: 'action', orderable: false},
        ],
        order: [[0, 'desc']],
        "dom": 'lBfrtip',
        "buttons": datatableButtons(),
        "responsive": true, "autoWidth": false, "colReorder": true,
    });



    // Search and Filters
    $('.search_btn, .start_date, .end_date').on('click change', function () {
        standardTable.ajax.reload();
    });

    // Standard Delete Action
    $('body').on('click', '.btn-delete', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Delete it!'
        }).then((result) => {
            if (result.isConfirmed) {
                preloaderToggle(true);
                $.ajax({
                    method: "DELETE",
                    url: "{{ route('stock-transfers.destroy', ['stock_transfer' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                    data: { id: id }
                }).done(function( response ) {
                    preloaderToggle(false);
                    if (response.success) {
                        Swal.fire('Deleted!', response.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire({ icon: 'error', title: 'Oops...', text: response.message });
                    }
                });
            }
        });
    });

    // Return Dispatch Confirmation Action with Vehicle & Driver inputs
    $('body').on('click', '.btn-dispatch-return', function (e) {
        e.preventDefault();
        var form = $(this).closest('form');
        
        Swal.fire({
            title: 'Dispatch Return Transfer',
            html:
                '<div class="text-left mb-3"><i class="fa fa-info-circle text-info"></i> <i>(Optional) Enter vehicle & driver details for shortage return.</i></div>' +
                '<div class="form-group text-left">' +
                '   <label for="swal-return-vehicle" style="font-weight:bold;">Vehicle / Truck No</label>' +
                '   <input id="swal-return-vehicle" class="form-control" placeholder="e.g. Dhaka Metro-T 11-2233">' +
                '</div>' +
                '<div class="form-group text-left">' +
                '   <label for="swal-return-driver" style="font-weight:bold;">Driver Name</label>' +
                '   <input id="swal-return-driver" class="form-control" placeholder="e.g. Robin Mia">' +
                '</div>' +
                '<div class="form-group text-left">' +
                '   <label for="swal-return-phone" style="font-weight:bold;">Driver Mobile No.</label>' +
                '   <input id="swal-return-phone" class="form-control" placeholder="e.g. 017XXXXXXXX">' +
                '</div>',
            icon: 'info',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Confirm & Dispatch <i class="fa fa-check"></i>',
            cancelButtonText: 'Cancel',
            focusConfirm: false,
            preConfirm: () => {
                return {
                    vehicle: document.getElementById('swal-return-vehicle').value,
                    driver: document.getElementById('swal-return-driver').value,
                    phone: document.getElementById('swal-return-phone').value
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Dynamic hidden inputs creation and append to form
                form.append('<input type="hidden" name="vehicle_no" value="' + result.value.vehicle + '">');
                form.append('<input type="hidden" name="driver_name" value="' + result.value.driver + '">');
                form.append('<input type="hidden" name="driver_phone" value="' + result.value.phone + '">');
                
                preloaderToggle(true);
                form.submit();
            }
        });
    });
});
</script>
@endsection
