@extends('layouts.app')
@section('title','Groups List')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Groups</h3>
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
                    <a href="{{ route('shomriddhi-advance.groups.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Group <i class="fa fa-plus"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped table-hover">
                            <thead>
                            <tr>
                                <th>Sl</th>
                                <th>Group Code</th>
                                <th>Name (English)</th>
                                <th>Name (Bangla)</th>
                                <th>Field Officer</th>
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
@endsection
@section('script')
    @include('layouts.partial.__location_filter_js')
    <script>
        $(function () {
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('shomriddhi-advance.groups.datatable') }}',
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
                    {data: 'DT_RowIndex', 'orderable': false, 'searchable': false },
                    {data: 'code', name: 'code'},
                    {data: 'name_en', name: 'name_en'},
                    {data: 'name_bn', name: 'name_bn'},
                    {data: 'field_officer', name: 'field_officer', searchable: false, orderable: false},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                order: [[1, 'asc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            
            $(document).on('location_filter_changed', function() {
                table.ajax.reload();
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
                            url: "{{ route('shomriddhi-advance.groups.destroy', ['group' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
