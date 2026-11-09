@extends('layouts.app')
@section('title', 'Employee Advances Summary')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-hand-holding-usd mr-2"></i> Employee Advances Summary
                    </h5>
                    <div class="card-tools">
                        <a href="{{ route('employee-advances.create') }}" class="btn btn-sm btn-success">
                            <i class="fa fa-plus"></i> New Advance
                        </a>
                        <a href="{{ route('employee-advances.requests') }}" class="btn btn-sm btn-info ml-2">
                            <i class="fa fa-paper-plane"></i> View Requests
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="advance-summary-table" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th>Employee ID</th>
                                    <th>Name</th>
                                    <th>Designation</th>
                                    <th>Total Advance Taken</th>
                                    <th>Total Repaid</th>
                                    <th>Current Balance Due</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(document).ready(function() {
    $('#advance-summary-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('employee-advances.datatable') }}",
        columns: [
            {data: 'id_no', name: 'id_no'},
            {data: 'name', name: 'name'},
            {data: 'designation', name: 'designation', orderable: false, searchable: false},
            {data: 'total_amount', name: 'total_amount', orderable: false, searchable: false, className: 'text-right'},
            {data: 'total_paid', name: 'total_paid', orderable: false, searchable: false, className: 'text-right'},
            {data: 'balance', name: 'balance', orderable: false, searchable: false, className: 'text-right font-weight-bold'}
        ]
    });
});
</script>
@endsection
