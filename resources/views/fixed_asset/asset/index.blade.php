@extends('layouts.app')
@section('title', 'Fixed Asset Register')

@section('content')
<div class="row mb-3">
                <div class="col-md-3">
                    <select id="filter_status" class="form-control select2">
                        <option value="">All Statuses</option>
                        <option value="active">Active</option>
                        <option value="sold">Sold</option>
                        <option value="scrapped">Scrapped</option>
                        <option value="transferred">Transferred</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select id="filter_category" class="form-control select2">
                        <option value="">All Categories</option>
                        @foreach(\App\Models\FixedAsset\FixedAssetCategory::all() as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(auth()->user()->can('fixed_asset_create'))
                    <a href="{{ route('fixed-assets.create') }}" class="btn btn-info bg-gradient-info">Asset Create <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive-sm">
                        <table class="table table-bordered" id="assets-table" width="100%">
                            <thead>
                                <tr>
                                            <th>Code</th>
                                            <th>Name</th>
                                            <th>Category</th>
                                            <th>Cost</th>
                                            <th>Book Value</th>
                                            <th>Status</th>
                                            <th width="15%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        let table = $('#assets-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('fixed-assets.datatable') }}",
                data: function (d) {
                    d.status = $('#filter_status').val();
                    d.category_id = $('#filter_category').val();
                }
            },
            columns: [
                {data: 'asset_code', name: 'asset_code'},
                {data: 'name', name: 'name'},
                {data: 'category_name', name: 'category.name'},
                {data: 'total_formatted', name: 'total'},
                {data: 'book_value_formatted', name: 'book_value'},
                {data: 'status_badge', name: 'status', orderable: false, searchable: false},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ],
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            "responsive": true, "autoWidth": false,"colReorder": true,
        });

        $('#filter_status, #filter_category').change(function() {
            table.draw();
        });
    });
</script>
@endsection
