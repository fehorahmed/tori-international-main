@extends('layouts.app')
@section('title', 'Employee Advances')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-list mr-2"></i> Employee Advances
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('employee-advances.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> New Advance
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <!-- Filter -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label>Employee</label>
                            <select id="filter_employee" class="form-control select2">
                                <option value="">All Employees</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->id_no }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Status</label>
                            <select id="filter_status" class="form-control select2">
                                <option value="">All</option>
                                <option value="Pending">Pending</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Paid">Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Date</label>
                            <input type="text" id="filter_date" class="form-control date-picker" placeholder="YYYY-MM-DD">
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button type="button" id="btn-filter" class="btn btn-info form-control"><i class="fa fa-search"></i> Filter</button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="advance-table" class="table table-bordered table-striped w-100">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Employee</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Audit Info</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Employee Advance</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="approve-details" class="mb-3 p-2 bg-light rounded"></p>
                
                <div class="form-group">
                    <label>Select Payment Account (Cash/Bank) <span class="text-danger">*</span></label>
                    <select id="approve_payment_mode" class="form-control select2" style="width:100%;">
                        <option value="">Select Account</option>
                        @foreach($paymentModes as $mode)
                            <option value="{{ $mode->id }}|{{ $mode->payment_mode_type }}">{{ $mode->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-success" id="btn-confirm-approve">Confirm Approve</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    var table = $('#advance-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('employee-advances.requests-datatable') }}",
            data: function (d) {
                d.employee_id = $('#filter_employee').val();
                d.status = $('#filter_status').val();
                d.date = $('#filter_date').val();
            }
        },
        columns: [
            {data: 'date', name: 'date'},
            {data: 'employee_id', name: 'employee_id'},
            {data: 'amount', name: 'amount'},
            {data: 'status', name: 'status'},
            {data: 'audit_info', name: 'audit_info', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ]
    });

    $('#btn-filter').on('click', function() {
        table.ajax.reload();
    });

    // Initialize Datepicker if needed
    if($('.date-picker').length > 0) {
        $('.date-picker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true,
            todayHighlight: true
        });
    }

    var currentApproveId = null;

    // Open Approve Modal
    $('body').on('click', '.btn-approve', function() {
        currentApproveId = $(this).data('id');
        let amount = $(this).data('amount');
        let employee = $(this).data('employee');

        $('#approve-details').html(
            '<b>Employee:</b> ' + employee + '<br>' +
            '<b>Amount:</b> ৳ ' + parseFloat(amount).toFixed(2)
        );
        $('#approve_payment_mode').val('').trigger('change');
        $('#approveModal').modal('show');
    });

    // Select2 inside modal
    $('#approveModal').on('shown.bs.modal', function () {
        if (!$('#approve_payment_mode').data('select2')) {
            $('#approve_payment_mode').select2({ dropdownParent: $('#approveModal') });
        }
    });

    // Confirm Approve
    $('#btn-confirm-approve').on('click', function () {
        var paymentMode = $('#approve_payment_mode').val();
        var accountText = $('#approve_payment_mode option:selected').text().trim();
        
        if (!paymentMode) {
            Swal.fire('Warning!', 'Please select a Cash/Bank account.', 'warning');
            return;
        }

        Swal.fire({
            title: 'Are you sure?',
            html: "You are about to approve this advance via <br><b class='text-success'>" + accountText + "</b>.<br><small class='text-muted'>This will automatically create a Payment Voucher.</small>",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#approveModal').modal('hide');
                preloaderToggle(true);

                $.ajax({
                    method: 'POST',
                    url: '/admin/employee-advances/' + currentApproveId + '/approve',
                    data: { _token: '{{ csrf_token() }}', payment_mode: paymentMode }
                }).done(function (response) {
                    preloaderToggle(false);
                    if (response.success) {
                        table.ajax.reload(null, false);
                        Swal.fire('Approved!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                }).fail(function () {
                    preloaderToggle(false);
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                });
            }
        });
    });

    // Reject
    $('body').on('click', '.btn-reject', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Reject this advance?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Reject!',
        }).then((result) => {
            if (result.isConfirmed) {
                preloaderToggle(true);
                $.ajax({
                    method: 'POST',
                    url: '/admin/employee-advances/' + id + '/reject',
                    data: { _token: '{{ csrf_token() }}' }
                }).done(function (response) {
                    preloaderToggle(false);
                    if (response.success) {
                        table.ajax.reload(null, false);
                        Swal.fire('Rejected!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                }).fail(function () {
                    preloaderToggle(false);
                    Swal.fire('Error!', 'Something went wrong.', 'error');
                });
            }
        });
    });
});
</script>
@endsection
