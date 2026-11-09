@extends('layouts.app')
@section('title','Inter-Company Loans')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Loan List</h3>
                    <div class="card-tools">
                        <a href="{{ route('company_loan.create') }}" class="btn btn-default btn-sm">Request New Loan</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Date</th>
                                <th>Lender</th>
                                <th>Borrower</th>
                                <th>Amount</th>
                                <th>Rate (%)</th>
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
@endsection
@section('script')
    <script>
        $(function () {
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('company_loan.datatable') }}',
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'start_date', name: 'start_date'},
                    {data: 'lender_name', name: 'lender_name'},
                    {data: 'borrower_name', name: 'borrower_name'},
                    {data: 'amount', name: 'amount'},
                    {data: 'interest_rate', name: 'interest_rate'},
                    {data: 'status_text', name: 'status'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,
            });
        });
    </script>
@endsection
