@extends('layouts.app')
@section('title','Employees')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('employee_create'))
                    <a href="{{ route('employees.create') }}" class="btn btn-info bg-gradient-info btn-sm">Employee Create <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>ID No.</th>
                                <th>Name</th>
                                <th>Designation</th>
                                <th>Department</th>
                                <th>Mobile No.</th>
                                <th>Address</th>
                                <th>Status</th>
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

    <!-- Address Modal -->
    <div class="modal fade" id="addressModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Employee Address Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="address_details_body">
                    <!-- Data will be loaded via AJAX -->
                    <div class="text-center p-5">
                        <i class="fa fa-spinner fa-spin fa-3x"></i>
                        <p>Loading address...</p>
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
                ajax: '{{ route('employees.datatable') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'id_no', name: 'id_no'},
                    {data: 'name', name: 'name'},
                    {data: 'designation_name', name: 'designation.name'},
                    {data: 'department_name', name: 'department.name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'address', name: 'address', orderable: false, className: 'text-center'},
                    {data: 'employee_status_name', name: 'employeeStatus.name'},
                    {data: 'action', name: 'action', orderable: false,className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('body').on('click', '.btn-address', function () {
                let id = $(this).data('id');
                $('#address_details_body').html('<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-3x"></i><p>Loading address...</p></div>');
                $('#addressModal').modal('show');

                $.ajax({
                    method: "GET",
                    url: "{{ route('get_employee_address_details') }}",
                    data: { id: id }
                }).done(function( response ) {
                    if (response.success) {
                        $('#address_details_body').html(response.html);
                    } else {
                        $('#address_details_body').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                });
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
                            url: "{{ route('employees.destroy', ['employee' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                ).then((result) => {
                                    location.reload();
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
