@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h3 class="card-title mb-0"><i class="fa fa-boxes mr-2"></i> {{ $pageTitle }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.savings-product.create') }}" class="btn btn-light btn-sm shadow-sm" style="border-radius: 10px;">
                            <i class="fa fa-plus-circle mr-1"></i> Add New Product
                        </a>
                    </div>
                </div>
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <div class="form-group mb-0">
                                <label class="small font-weight-bold text-muted mb-1">Filter by Product Type:</label>
                                <select id="product_type_filter" class="form-control form-control-sm select2">
                                    <option value="">All Types</option>
                                    <option value="1">General Savings (GS)</option>
                                    <option value="2">DPS</option>
                                    <option value="3">FDR</option>
                                </select>
                            </div>
                        </div>
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
                                    <th>Type</th>
                                    <th>Interest Rate (%)</th>
                                    <th>Duration (Months)</th>
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
        var table = $('.data-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('microfinance.savings-product.datatable') }}",
                data: function (d) {
                    d.product_type = $('#product_type_filter').val();
                }
            },
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'code', name: 'code'},
                {data: 'name', name: 'name'},
                {data: 'product_type', name: 'product_type'},
                {data: 'interest_rate', name: 'interest_rate'},
                {data: 'duration_months', name: 'duration_months'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ]
        });

        $('#product_type_filter').on('change', function() {
            table.draw();
        });
        $('.select2').select2({ width: '100%' });
    });
</script>
@endsection
