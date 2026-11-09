@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('incentive-settings.global') }}" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-globe"></i> Global Settings</a>
                    <a href="{{ route('incentive-settings.create') }}" class="btn btn-info bg-gradient-info btn-sm">Category Wise Settings <i class="fa fa-plus"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label>Filter by Branch</label>
                            <select id="filter_branch" class="form-control select2">
                                @foreach($branches as $index => $branch)
                                    <option value="{{ $branch->id }}" {{ $index == 0 ? 'selected' : '' }}>{{ $branch->display_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="settingsDT" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Branch</th>
                                    <th>Product</th>
                                    <th>SR <small class="text-muted">(Sales Rep)</small></th>
                                    <th>Branch Manager</th>
                                    <th>Area Manager</th>
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
    let table = $('#settingsDT').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('incentive-settings.datatable') }}',
            data: function (d) {
                d.sister_concern_id = $('#filter_branch').val();
            }
        },
        "pagingType": "full_numbers",
        "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'branch_name', name: 'sisterConcern.name' },
            { data: 'product_name', name: 'product.name' },
            { data: 'sr_status', orderable: false, searchable: false },
            { data: 'bm_status', orderable: false, searchable: false },
            { data: 'am_status', orderable: false, searchable: false },
        ],
        "dom": 'lBfrtip',
        "buttons": datatableButtons(),
        "responsive": true, "autoWidth": false,"colReorder": true,
    });

    $('#filter_branch').change(function () {
        table.ajax.reload();
    });

    $('body').on('change', '.status-toggle', function() {
        let id = $(this).data('id');
        let field = $(this).data('field');
        let status = $(this).is(':checked') ? 1 : 0;

        $.ajax({
            url: '{{ route('incentive-settings.toggle-status') }}',
            method: 'POST',
            data: {
                id: id,
                field: field,
                status: status,
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if(response.success) {
                    toastr.success(response.message);
                }
            },
            error: function() {
                toastr.error('Something went wrong!');
                table.ajax.reload(null, false); // Reload to reset switch to correct state on error
            }
        });
    });
});
</script>
@endsection
