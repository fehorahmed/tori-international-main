@extends('layouts.app')
@section('title', 'Inter-Company Sent Payments')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="row">
                    <div class="col-12 col-md-3">
                        <div class="form-group">
                            <label for="start_date" class="col-form-label">Start Date <span class="text-danger">*</span></label>
                            <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="start_date" class="form-control date-picker" id="start_date" placeholder="Enter Start Date">
                        </div>
                    </div>
                    <div class="col-12 col-md-3">
                        <div class="form-group">
                            <label for="end_date" class="col-form-label">End Date <span class="text-danger">*</span></label>
                            <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}" name="end_date" class="form-control date-picker" id="end_date" placeholder="Enter End Date">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <input style="margin-top: -4px;" type="button" id="search_btn" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <a href="{{ route('inter_company_settlements.create') }}" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-plus"></i> Send Payment</a>
            </div>
            <div class="card-body">
                <table id="table" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th class="text-center">S/L</th>
                            <th>Date</th>
                            <th>Payment No</th>
                            <th>Paid To Branch</th>
                            <th>Amount</th>
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
        var table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('inter_company_settlements.datatable') }}',
                data: function (d) {
                    d.start_date = $("#start_date").val()
                    d.end_date = $("#end_date").val()
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                {data: 'date', name: 'date'},
                {data: 'payment_no', name: 'payment_no'},
                {data: 'to_sister_concern.name', name: 'toSisterConcern.name'},
                {data: 'amount', name: 'amount'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false}
            ],
            order: [[1, 'desc']]
        });

        $('#start_date,#end_date,#search_btn').change(function () {
            table.ajax.reload();
        });

        $('body').on('click', '.btn-cancel', function (e) {
            e.preventDefault();
            var form = $(this).closest('form');
            Swal.fire({
                title: 'Are you sure?',
                text: "You are about to cancel this sent payment. The money will be reversed to your account.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
