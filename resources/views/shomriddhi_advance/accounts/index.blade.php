@extends('layouts.app')

@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Data Filter Card -->
            <div class="card card-outline card-default mb-3">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <div class="card-body">
                    <div id="account-filter-error" class="alert alert-danger d-none" role="alert"></div>
                    <div class="row">
                        <div class="col-md-3 form-group">
                            <label for="account-from">Opening Date — From</label>
                            <input type="date" id="account-from" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-3 form-group">
                            <label for="account-to">Opening Date — To</label>
                            <input type="date" id="account-to" name="end_date" class="form-control">
                        </div>
                        <div class="col-md-3 d-flex align-items-end">
                            <button type="button" id="clear-account-dates" class="btn btn-secondary mb-3">Clear Dates</button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="division_id">Division</label>
                                <select name="division_id" id="division_id" class="form-control select2">
                                    <option value="">All Divisions</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="district_id">District</label>
                                <select name="district_id" id="district_id" class="form-control select2">
                                    <option value="">All Districts</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="upazila_id">Upazila</label>
                                <select name="upazila_id" id="upazila_id" class="form-control select2">
                                    <option value="">All Upazilas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="union_id">Pourashava/Union</label>
                                <select name="union_id" id="union_id" class="form-control select2">
                                    <option value="">All Unions</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="village_id">Village/Road</label>
                                <select name="village_id" id="village_id" class="form-control select2">
                                    <option value="">All Villages</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fa fa-users mr-2"></i> All Customer Accounts</h5>
                    <div class="card-tools">
                        @if(auth()->user()->hasPermissionTo('shomriddhi_advance_transaction_create'))
                            <a href="{{ route('shomriddhi-advance.accounts.create') }}" class="btn btn-info btn-sm"><i class="fa fa-plus"></i> Add New Account</a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped" id="accountsTable" width="100%">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">S/L</th>
                                    <th>Image</th>
                                    <th>Member No</th>
                                    <th>Account No</th>
                                    <th>Customer Name</th>
                                    <th>Principal Balance (৳)</th>
                                    <th>Accrued Interest (৳)</th>
                                    <th>Last Interest Date</th>
                                    <th>Status</th>
                                    <th width="10%" class="text-center">Action</th>
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
@endsection

@section('script')
@include('layouts.partial.__location_filter_js')
<script>
    $(function() {
        var table = $('#accountsTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('shomriddhi-advance.accounts.datatable') }}',
                data: function(d) {
                    d.start_date = $('#account-from').val();
                    d.end_date = $('#account-to').val();
                    d.division_id = $('#division_id').val();
                    d.district_id = $('#district_id').val();
                    d.upazila_id = $('#upazila_id').val();
                    d.union_id = $('#union_id').val();
                    d.village_id = $('#village_id').val();
                },
                error: function (xhr) {
                    $('#account-filter-error').text(xhr.responseJSON?.message || 'Unable to load accounts. Please try again.').removeClass('d-none');
                }
            },
            "dom": 'lBfrtip',
            "buttons": typeof datatableButtons === 'function' ? datatableButtons() : [],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'image', name: 'image', orderable: false, searchable: false, className: 'text-center'},
                {data: 'member_no', name: 'customer.member_no'},
                {data: 'account_number', name: 'account_number'},
                {data: 'customer_name', name: 'customer.name'},
                {data: 'principal_balance', name: 'principal_balance', className: 'text-right font-weight-bold'},
                {data: 'accrued_interest', name: 'accrued_interest', className: 'text-right text-success font-weight-bold'},
                {data: 'last_interest_date', name: 'last_interest_date', className: 'text-center'},
                {data: 'status', name: 'status', className: 'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ],
            order: [[0, 'desc']]
        });

        $('#account-from, #account-to').on('change', function () {
            const from = $('#account-from').val(), to = $('#account-to').val();
            const error = $('#account-filter-error');
            error.addClass('d-none').empty();
            if (from && to && to < from) {
                error.text('Opening end date must be on or after start date.').removeClass('d-none');
                return;
            }
            table.ajax.reload();
        });
        $('#clear-account-dates').on('click', function () {
            $('#account-from, #account-to').val('');
            $('#account-filter-error').addClass('d-none').empty();
            table.ajax.reload();
        });

        $(document).on('location_filter_changed', function() {
            table.ajax.reload();
        });
    });
</script>
@endsection
