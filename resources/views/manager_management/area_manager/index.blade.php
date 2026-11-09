@extends('layouts.app')
@section('title','Area Managers')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                     
                    <a href="{{ route('employees.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Area Manager <i class="fa fa-plus"></i></a>
                   
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Mobile No.</th>
                                <th>Email</th>
                                <th>Address</th>
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

    <!-- Address Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info">
                    <h5 class="modal-title text-white" id="addressModalLabel"><i class="fa fa-map-marked-alt mr-2"></i> Area Manager Address</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4" id="addressContent" style="background-color: #f9f9f9;">
                    <!-- Address will be loaded here -->
                </div>
                <div class="modal-footer" style="background-color: #f1f1f1;">
                    <button type="button" class="btn btn-secondary bg-gradient-secondary btn-sm" data-dismiss="modal">Close</button>
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
                ajax: '{{ route('area-manager.datatable') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'image', name: 'image', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'email', name: 'email'},
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            return '<button type="button" class="btn btn-info btn-xs btn-view-address" data-id="'+row.id+'"><i class="fa fa-map-marker-alt"></i> View Address</button>';
                        }
                    },
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                            if (data === 1) {
                                return '<span class="badge badge-success">Active</span>';
                            } else if (data === 0) {
                                return '<span class="badge badge-danger">Inactive</span>';
                            }
                            return data;
                        }
                    },
                    {data: 'action', name: 'action', orderable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            $('body').on('click', '.btn-view-address', function () {
                let id = $(this).data('id');
                $('#addressContent').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2">Loading address...</p></div>');
                $('#addressModal').modal('show');
                
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_employee_address_details') }}",
                    data: { id: id }
                }).done(function( response ) {
                    if (response.success) {
                        $('#addressContent').html(response.html);
                    } else {
                        $('#addressContent').html('<div class="alert alert-danger">'+response.message+'</div>');
                    }
                });
            });


        });
    </script>
@endsection
