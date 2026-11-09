@extends('layouts.app')
@section('title', 'Asset Categories')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(auth()->user()->can('fixed_asset_category_create'))
                    <a href="{{ route('fixed-assets.categories.create') }}" class="btn btn-info bg-gradient-info">Category Create <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive-sm">
                        <table class="table table-bordered" id="categories-table">
                            <thead>
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Name</th>
                                    <th>Linked Account Head</th>
                                    <th>Depreciation Policy</th>
                                    <th>Active Assets</th>
                                    <th width="10%">Action</th>
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
        $('#categories-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('fixed-assets.categories.datatable') }}",
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'account_head_name', name: 'accountHead.name', orderable: false},
                {data: 'dep_info', name: 'depreciation_method', orderable: false},
                {data: 'assets_count', name: 'assets_count', orderable: false, searchable: false, className: 'text-center'},
                {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'}
            ],
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            "responsive": true, "autoWidth": false,"colReorder": true,
        });

        // Delete Handler
        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "Delete this category? If assets are linked, deletion will fail.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('fixed-assets.categories.destroy', ['category' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function (res) {
                            if (res.success) {
                                Swal.fire('Deleted!', res.message, 'success');
                                $('#categories-table').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Failed!', res.message, 'error');
                            }
                        },
                        error: function (err) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
