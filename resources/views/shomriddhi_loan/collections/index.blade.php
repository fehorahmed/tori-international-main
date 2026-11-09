@extends('layouts.app')
@section('title', 'Collection History')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list-alt mr-2"></i> Collection History
                </h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-loan.collections.create') }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-hand-holding-usd"></i> Collect Installment1
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-bordered table-striped" id="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Date</th>
                                <th>Loan No</th>
                                <th>Customer</th>
                                <th>Voucher</th>
                                <th>Mode</th>
                                <th>Collected By</th>
                                <th class="text-right">Amount</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(function () {
        const table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('shomriddhi-loan.collections.datatable') }}',
            pagingType: 'full_numbers',
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'collection_date', name: 'collection_date' },
                { data: 'loan_no', name: 'loan_no' },
                { data: 'customer', name: 'customer' },
                { data: 'voucher', name: 'voucher' },
                { data: 'payment_mode', name: 'payment_mode' },
                { data: 'collected_by', name: 'collected_by' },
                { data: 'amount', name: 'amount', className: 'text-right text-success font-weight-bold' },
            ],
            dom: 'lBfrtip',
            buttons: datatableButtons(),
            responsive: true,
            autoWidth: false,
        });
    });
</script>
@endsection
