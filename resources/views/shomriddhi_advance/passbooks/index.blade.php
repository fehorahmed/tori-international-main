@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Data Filter Card -->
                <div class="card card-outline card-default mb-3">
                    <div class="card-header">
                        <h3 class="card-title">Filter Passbooks</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="account_id">Select Account</label>
                                    <select name="account_id" id="account_id" class="form-control select2">
                                        <option value="">All Accounts</option>
                                        @foreach($accounts as $account)
                                            <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                                {{ $account->account_number }} - {{ $account->customer->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-default">
                    <div class="card-header">
                        <h5 class="card-title mb-0"><i class="fa fa-book mr-2"></i> Passbook History</h5>
                        <div class="card-tools">
                            <button type="button" class="btn btn-info btn-sm" id="btnIssuePassbook">
                                <i class="fa fa-plus"></i> Issue New Passbook
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped" id="passbooksTable" width="100%">
                                <thead class="bg-light">
                                    <tr>
                                        <th width="5%">S/L</th>
                                        <th>Account No</th>
                                        <th>Member No</th>
                                        <th>Member Name</th>
                                        <th>Passbook No</th>
                                        <th>Issued At</th>
                                        <th>Status</th>
                                        <th>Reason</th>
                                        <th width="20%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data populated by DataTables -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Passbook Modal -->
    <div class="modal fade" id="issuePassbookModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-md" role="document">
            <form id="issuePassbookForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-primary">
                        <h5 class="modal-title text-white">Issue New Passbook</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Select Account <span class="text-danger">*</span></label>
                            <select name="account_id" class="form-control select2 w-100" required>
                                <option value="">Choose Account...</option> 
                                @foreach($accounts as $account)
                                    <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                        {{ $account->customer->member_no ?? '' }} | {{ $account->customer->name ?? '' }} | SA/C
                                        No: {{ $account->account_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reason (Optional)</label>
                            <input type="text" name="reason" class="form-control"
                                placeholder="E.g. New Account / Lost Replacement">
                        </div>
                        <p class="text-danger small"><i class="fas fa-info-circle"></i> Note: If the account already has an
                            'Active' passbook, you must change its status (Lost/Damaged) before issuing a new one.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm" id="btnSubmitIssue">Confirm Issue</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Change Status Modal -->
    <div class="modal fade" id="changeStatusModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <form id="changeStatusForm">
                @csrf
                <div class="modal-content">
                    <div class="modal-header bg-warning">
                        <h5 class="modal-title">Change Status</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>New Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-control" required>
                                <option value="Lost">Lost</option>
                                <option value="Damaged">Damaged</option>
                                <option value="Filled">Pages Full</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Reason Details (Optional)</label>
                            <input type="text" name="reason" class="form-control" placeholder="E.g. Customer lost it">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning btn-sm" id="btnSubmitStatus">Update</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2({ theme: 'bootstrap4' });

            var table = $('#passbooksTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('shomriddhi-advance.passbooks.datatable') }}',
                    data: function (d) {
                        d.account_id = $('#account_id').val();
                    }
                },
                "dom": 'lBfrtip',
                "buttons": typeof datatableButtons === 'function' ? datatableButtons() : [],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'account_number', name: 'account_number' },
                    { data: 'member_no', name: 'member_no' },
                    { data: 'customer_name', name: 'customer_name' },
                    { data: 'passbook_number', name: 'passbook_number', className: 'font-weight-bold' },
                    { data: 'issued_at', name: 'created_at', className: 'text-center' },
                    { data: 'status', name: 'status', className: 'text-center' },
                    { data: 'reason', name: 'reason' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                order: [[0, 'desc']]
            });

            $('#account_id').on('change', function () {
                table.ajax.reload();
            });

            // Issue Modal
            $('#btnIssuePassbook').on('click', function () {
                $('#issuePassbookForm')[0].reset();
                $('#issuePassbookForm select').val('').trigger('change');
                $('#issuePassbookModal').modal('show');
            });

            $('#issuePassbookModal').on('hidden.bs.modal', function () {
                $('#issuePassbookForm')[0].reset();
                $('#issuePassbookForm select').val('').trigger('change');
            });

            $('#issuePassbookForm').on('submit', function (e) {
                e.preventDefault();
                $('#btnSubmitIssue').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                $.ajax({
                    url: '{{ route('shomriddhi-advance.passbooks.store') }}',
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#btnSubmitIssue').prop('disabled', false).text('Confirm Issue');
                        $('#issuePassbookModal').modal('hide');
                        if (response.success) {
                            toastr.success(response.message);
                            table.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        $('#btnSubmitIssue').prop('disabled', false).text('Confirm Issue');
                        toastr.error('An error occurred.');
                    }
                });
            });

            // Change Status Modal
            let statusUrl = '';
            $(document).on('click', '.change-status-btn', function () {
                statusUrl = $(this).data('url');
                $('#changeStatusForm')[0].reset();
                $('#changeStatusModal').modal('show');
            });

            $('#changeStatusForm').on('submit', function (e) {
                e.preventDefault();
                $('#btnSubmitStatus').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>...');

                $.ajax({
                    url: statusUrl,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function (response) {
                        $('#btnSubmitStatus').prop('disabled', false).text('Update');
                        $('#changeStatusModal').modal('hide');
                        if (response.success) {
                            toastr.success(response.message);
                            table.ajax.reload();
                        } else {
                            toastr.error(response.message);
                        }
                    },
                    error: function (xhr) {
                        $('#btnSubmitStatus').prop('disabled', false).text('Update');
                        toastr.error('An error occurred.');
                    }
                });
            });
        });
    </script>
@endsection