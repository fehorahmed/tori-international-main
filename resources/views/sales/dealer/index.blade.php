@extends('layouts.app')
@section('title','Dealers')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Dealers</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="division_id">Division</label>
                                <select name="division_id" id="division_id" class="form-control select2">
                                    <option value="">All Divisions</option>
                                    @foreach($divisions as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="district_id">District</label>
                                <select name="district_id" id="district_id" class="form-control select2">
                                    <option value="">All Districts</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="upazila_id">Upazila</label>
                                <select name="upazila_id" id="upazila_id" class="form-control select2">
                                    <option value="">All Upazilas</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="union_id">Pourashava/Union</label>
                                <select name="union_id" id="union_id" class="form-control select2">
                                    <option value="">All Unions</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="village_id">Village/Road</label>
                                <select name="village_id" id="village_id" class="form-control select2">
                                    <option value="">All Villages</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(auth()->user()->can('customer_create'))
                    <a href="{{ route('dealer.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Dealer <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name</th>
                                <th>Member No</th>
                                <th>Mobile No.</th>
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
    <div class="modal fade" id="viewAddressModal" tabindex="-1" role="dialog" aria-labelledby="viewAddressModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info">
                    <h5 class="modal-title text-white" id="viewAddressModalLabel"><i class="fa fa-map-marked-alt mr-2"></i> Dealer Address Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="addressContent">
                    <!-- Address will be loaded here -->
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    @include('layouts.partial.__location_filter_js')
    <script>
        $(function () {
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('dealer.datatable') }}',
                    data: function(d) {
                        d.division_id = $('#division_id').val();
                        d.district_id = $('#district_id').val();
                        d.upazila_id = $('#upazila_id').val();
                        d.union_id = $('#union_id').val();
                        d.village_id = $('#village_id').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {
                        data: 'name', 
                        name: 'name',
                        render: function(data, type, row) {
                            return '<a href="{{ route("dealer.show", ["dealer" => ":id"]) }}'.replace(':id', row.id) + '" class="font-weight-bold">' + data + '</a>';
                        }
                    },
                    {data: 'member_no', name: 'member_no'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'address', name: 'address', orderable: false, searchable: false},
                    {
                        data: 'status',
                        name: 'status',
                        render: function (data, type, row) {
                            if (data === 1) {
                                return '<span class="badge badge-success text-center">Active</span>';
                            } else if (data === 0) {
                                return '<span class="badge badge-danger text-center">Inactive</span>';
                            }
                            return data;
                        }, className: 'text-center'
                    },
                    {data: 'action', name: 'action', orderable: false, className: 'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            $(document).on('location_filter_changed', function() {
                table.ajax.reload();
            });

            $('body').on('click', '.btn-view-address', function() {
                var id = $(this).data('id');
                $('#addressContent').html('<div class="text-center p-4"><i class="fa fa-spinner fa-spin fa-2x text-info"></i><p class="mt-2">Loading address...</p></div>');
                $('#viewAddressModal').modal('show');

                $.ajax({
                    method: "GET",
                    url: "{{ route('get_supplier_address_details') }}",
                    data: { id: id }
                }).done(function( response ) {
                    if (response.success) {
                        $('#addressContent').html(response.html);
                    } else {
                        $('#addressContent').html('<div class="alert alert-danger">'+response.message+'</div>');
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
                            url: "{{ route('dealer.destroy', ['dealer' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success').then(() => {
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
