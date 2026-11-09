@extends('layouts.app')
@section('title','Trainee List')
@section('style')
    <link rel="stylesheet" href="{{ asset('themes/backend/dist/css/premium-theme.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users-cog mr-2"></i> Trainee Management</h3>
                    <div class="card-tools">
                        <a href="{{ route('employees.create') }}" class="btn btn-sm btn-light text-primary font-weight-bold">
                            <i class="fas fa-plus-circle mr-1"></i> Add New Trainee
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table id="trainee-table" class="table table-bordered table-striped w-100">
                            <thead>
                            <tr>
                                <th>SL</th>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Mobile No</th>
                                <th>Address</th>
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

    <!-- Address Modal -->
    <div class="modal fade" id="addressModal" tabindex="-1" role="dialog" aria-labelledby="addressModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info">
                    <h5 class="modal-title text-white" id="addressModalLabel"><i class="fa fa-map-marked-alt mr-2"></i> Trainee Address Details</h5>
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
            $('#trainee-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('trainee.datatable') }}',
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {
                        data: 'image',
                        name: 'image',
                        render: function(data) {
                            var img = data ? '{{ asset('') }}' + data : '{{ asset('img/avatar.png') }}';
                            return '<img src="' + img + '" class="img-circle elevation-1" width="40" height="40" style="object-fit: cover;">';
                        }
                    },
                    {data: 'name', name: 'name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {
                        data: 'address',
                        name: 'address',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        render: function (data, type, row) {
                            if (data == 1) {
                                return '<span class="badge badge-success">Active</span>';
                            } else {
                                return '<span class="badge badge-danger">Inactive</span>';
                            }
                        }
                    },
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
                ]
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
