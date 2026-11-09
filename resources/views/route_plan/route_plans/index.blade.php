@extends('layouts.app')
@section('title','SR Route Plans')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('route-plans.create') }}" class="btn btn-info bg-gradient-info btn-sm">Assign Route Plan <i class="fa fa-plus"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>SR Name</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    <!-- PJP View Modal -->
    <div class="modal fade" id="pjpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="pjpModalTitle">Weekly PJP</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pjpModalBody">
                    <div class="text-center py-5">
                        <i class="fas fa-spinner fa-spin fa-3x text-info"></i>
                        <p class="mt-2">Loading PJP...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
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
                ajax: '{{ route('route-plans.datatable') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className:'text-center' },
                    {data: 'sr_name', name: 'suppliers.name'},
                    {data: 'action', name: 'action', orderable: false, className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false, "colReorder": true,
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
                            url: "{{ url('admin/route-plans') }}/" + id,
                            data: { _token: '{{ csrf_token() }}' }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
                                    $('#table').DataTable().ajax.reload();
                                });
                            } else {
                                Swal.fire({icon: 'error', title: 'Oops...', text: response.message});
                            }
                        });
                    }
                })
            });

            // View PJP
            $('body').on('click', '.btn-view-pjp', function () {
                let sr_id = $(this).data('id');
                
                // Show modal with loading state
                $('#pjpModalTitle').text('Loading...');
                $('#pjpModalBody').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-info"></i><p class="mt-2">Loading Route Plan...</p></div>');
                $('#pjpModal').modal('show');
                
                $.ajax({
                    url: "{{ route('route-plans.view-pjp') }}",
                    type: "GET",
                    data: { sr_id: sr_id },
                    success: function(response) {
                        $('#pjpModalTitle').text(response.title);
                        $('#pjpModalBody').html(response.html);
                    },
                    error: function() {
                        $('#pjpModalBody').html('<div class="alert alert-danger">Failed to load data. Please try again.</div>');
                    }
                });
            });
        });
    </script>
@endsection
