@extends('layouts.app')
@section('title', 'Contractor Types')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <a href="{{ route('contractor_management.types.create') }}" class="btn btn-info bg-gradient-info">Create Contractor Type</a>
            </div>

            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive-sm">
                    <table class="table table-bordered" id="types-table">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
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
    $(document).ready(function() {
        var table = $('#types-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('contractor_management.types.datatable') }}",
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
            columns: [
                {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: 'status', name: 'status'},
                {data: 'action', name: 'action', orderable: false, searchable: false},
            ],
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            "responsive": true, "autoWidth": false,"colReorder": true,
        });

        $('body').on('click', '.btn-delete', function () {
            let id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    preloaderToggle(true);
                    $.ajax({
                        method: "DELETE",
                        url: "{{ route('contractor_management.types.destroy', ['type' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                        data: { id: id, _token: "{{ csrf_token() }}" }
                    }).done(function( response ) {
                        preloaderToggle(false);
                        if (response.success) {
                            Swal.fire(
                                'Deleted!',
                                response.message,
                                'success'
                            ).then((result) => {
                                table.ajax.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message,
                            });
                        }
                    });
                }
            })
        });
    });
</script>
@endsection
