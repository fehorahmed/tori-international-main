@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary" style="border-radius: 15px 15px 0 0;">
                    <h3 class="card-title mb-0"><i class="fa fa-hand-holding-usd mr-2"></i> {{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.loan-product.create') }}" class="btn btn-light btn-sm shadow-sm" style="border-radius: 10px;">
                            <i class="fa fa-plus-circle mr-1"></i> Add New Scheme
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover data-table" style="width: 100%;">
                            <thead class="bg-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Code</th>
                                    <th>Name</th>
                                    <th>Interest (%)</th>
                                    <th>Frequency</th>
                                    <th>Late Penalty</th>
                                    <th>Status</th>
                                    <th width="100px">Action</th>
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
    $(function () {
        $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('microfinance.loan-product.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'interest_rate', name: 'interest_rate'},
                {data: 'installment_frequency', name: 'installment_frequency'},
                {data: 'late_penalty', name: 'late_penalty'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });
    });
</script>
@endsection
