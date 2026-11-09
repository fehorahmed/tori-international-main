@extends('layouts.app')

@section('title', 'Shomriddhi Advance List')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-list mr-2"></i> Shomriddhi Advance Transactions
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('shomriddhi-advance.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> New Transaction
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible">
                            <button type="button" class="close" data-dismiss="alert">&times;</button>
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Filter by Status</label>
                            <select id="filter_status" class="form-control">
                                <option value="Pending">Pending</option>
                                <option value="">All Transactions</option>
                                <option value="Approved">Approved</option>
                                <option value="Rejected">Rejected</option>
                                <option value="Deleted">Deleted</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>Filter by Type</label>
                            <select id="filter_type" class="form-control">
                                <option value="">All Types</option>
                                <option value="deposit">Deposit</option>
                                <option value="withdraw">Withdraw</option>
                                <option value="interest_capitalization">Interest</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date</label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="End Date">
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="advance-table" width="100%">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th>Audit Info</th>
                                    <th width="15%">Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Approve Modal --}}
<div class="modal fade" id="approveModal" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title"><i class="fa fa-check-circle mr-2"></i> Approve Transaction</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info mb-3" id="approve-txn-info"></div>
                <div class="form-group mb-0">
                    <label>Receipt / Payment Mode <span class="text-danger">*</span></label>
                    <select id="approve_payment_mode" class="form-control select2-modal" style="width:100%;">
                        <option value="">-- Select Cash / Bank Account --</option>
                        @foreach($paymentModes as $pm)
                            <option value="{{ $pm->id }}|{{ $pm->payment_mode }}">
                                {{ $pm->name }} - {{ $pm->code }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">টাকা কোন Cash/Bank account-এ আসবে বা কোথা থেকে দেওয়া হবে সেটা select করুন।</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="btn-confirm-approve">
                    <i class="fa fa-check"></i> Confirm Approve
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    var currentApproveId = null;

    var table = $('#advance-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('shomriddhi-advance.datatable') }}",
            data: function(d) {
                d.status = $('#filter_status').val();
                d.type = $('#filter_type').val();
                d.start_date = $('#start_date').val();
                d.end_date = $('#end_date').val();
            }
        },
        "dom": 'lBfrtip',
        "buttons": datatableButtons(),
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'date', name: 'date'},
            {data: 'customer_name', name: 'customer_name'},
            {data: 'type_badge', name: 'type', searchable: false},
            {data: 'amount_formatted', name: 'amount', searchable: false},
            {data: 'status_badge', name: 'status', searchable: false},
            {data: 'audit_info', name: 'audit_info', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false},
        ],
        order: [[1, 'desc']]
    });

    $('#filter_status, #filter_type, #start_date, #end_date').change(function() {
        table.ajax.reload();
    });

    // Open Approve Modal
    $('body').on('click', '.btn-approve', function () {
        currentApproveId = $(this).data('id');
        var customer     = $(this).data('customer');
        var type         = $(this).data('type');
        var amount       = $(this).data('amount');

        $('#approve-txn-info').html(
            '<b>Customer:</b> ' + customer + '<br>' +
            '<b>Type:</b> ' + type + '&nbsp;&nbsp; <b>Amount:</b> ৳ ' + amount
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
            html: "You are about to approve this transaction via <br><b class='text-success'>" + accountText + "</b>.<br><small class='text-muted'>This will automatically create the required accounting entries.</small>",
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
                    url: '{{ route("shomriddhi-advance.approve", ":id") }}'.replace(':id', currentApproveId),
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
            title: 'Reject this transaction?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Reject!',
        }).then((result) => {
            if (result.isConfirmed) {
                preloaderToggle(true);
                $.ajax({
                    method: 'POST',
                    url: '{{ route("shomriddhi-advance.reject", ":id") }}'.replace(':id', id),
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

    // Delete
    $('body').on('click', '.btn-delete', function () {
        let id = $(this).data('id');
        Swal.fire({
            title: 'Delete this transaction?',
            text: 'This will reverse the balance and delete related accounting vouchers!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            confirmButtonText: 'Yes, Delete!',
        }).then((result) => {
            if (result.isConfirmed) {
                preloaderToggle(true);
                $.ajax({
                    method: 'DELETE',
                    url: '{{ route("shomriddhi-advance.destroy", ":id") }}'.replace(':id', id),
                    data: { _token: '{{ csrf_token() }}' }
                }).done(function (response) {
                    preloaderToggle(false);
                    if (response.success) {
                        table.ajax.reload(null, false);
                        Swal.fire('Deleted!', response.message, 'success');
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
