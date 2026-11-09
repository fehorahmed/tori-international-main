@extends('layouts.app')
@section('title', 'Contractors')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-default">
            <div class="card-header">
                <h3 class="card-title">Filter Contractors</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="contractor_type_id">Contractor Type</label>
                            <select name="contractor_type_id" id="contractor_type_id" class="form-control select2">
                                <option value="">All Types</option>
                                @foreach($types as $type)
                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
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
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="union_id">Pourashava/Union</label>
                            <select name="union_id" id="union_id" class="form-control select2">
                                <option value="">All Unions</option>
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
                <a href="{{ route('contractor_management.contractors.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Contractor <i class="fa fa-plus"></i></a>
            </div>
            <!-- /.card-header -->
            <div class="card-body">
                <div class="table-responsive">
                    <table id="table" class="table table-bordered">
                        <thead>
                        <tr>
                            <th>S/L</th>
                            <th>Type</th>
                            <th>Name</th>
                            <th>Mobile No.</th>
                            <th>Address</th>
                            <th>Total Bill</th>
                            <th>Total Paid</th>
                            <th>Due/Advance</th>
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

<!-- View Address Modal -->
<div class="modal fade" id="viewAddressModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content shadow-lg border-0" style="border-radius: 12px;">
            <div class="modal-header bg-gradient-info">
                <h5 class="modal-title text-white"><i class="fa fa-map-marker-alt"></i> Contractor Address Details</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body bg-light" id="addressContent">
                <!-- Address details will be loaded here via AJAX -->
            </div>
            <div class="modal-footer bg-white">
                <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal">Close</button>
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
                    url: '{{ route('contractor_management.contractors.datatable') }}',
                    data: function(d) {
                        d.contractor_type_id = $('#contractor_type_id').val();
                        d.division_id = $('#division_id').val();
                        d.district_id = $('#district_id').val();
                        d.upazila_id = $('#upazila_id').val();
                        d.union_id = $('#union_id').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'type_name', name: 'type_name', orderable: false},
                    {data: 'name', name: 'name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'address', name: 'address', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'total_bill', name: 'total_bill', orderable: false, searchable: false, className: 'text-right'},
                    {data: 'total_paid', name: 'total_paid', orderable: false, searchable: false, className: 'text-right'},
                    {data: 'balance', name: 'balance', orderable: false, searchable: false, className: 'text-right'},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            $('#contractor_type_id').on('change', function() {
                table.ajax.reload();
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
                    url: "{{ route('contractor_management.contractors.address_details') }}",
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
                            url: "{{ route('contractor_management.contractors.destroy', ['contractor' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
