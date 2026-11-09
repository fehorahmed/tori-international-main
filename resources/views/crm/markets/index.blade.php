@extends('layouts.app')
@section('title','Manage Markets')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('markets.create') }}" class="btn btn-info bg-gradient-info btn-sm">Add New Market <i class="fa fa-plus"></i></a>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <label for="division_id">Division</label>
                            <select id="division_id" class="form-control select2">
                                <option value="">All Divisions</option>
                                @foreach($divisions as $division)
                                    <option value="{{ $division->id }}">{{ $division->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="district_id">District</label>
                            <select id="district_id" class="form-control select2">
                                <option value="">All Districts</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="upazila_id">Upazila</label>
                            <select id="upazila_id" class="form-control select2">
                                <option value="">All Upazilas</option>
                            </select>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Market Name</th>
                                <th>Upazila</th>
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
    <script>
        $(function () {
            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('markets.datatable') }}',
                    data: function (d) {
                        d.division_id = $('#division_id').val();
                        d.district_id = $('#district_id').val();
                        d.upazila_id = $('#upazila_id').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                columns: [
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className:'text-center' },
                    {data: 'name', name: 'name'},
                    {data: 'upazila_name', name: 'upazila.name'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false, "colReorder": true,
            });

            $('#division_id').change(function () {
                let divisionId = $(this).val();
                $('#district_id').html('<option value="">All Districts</option>');
                $('#upazila_id').html('<option value="">All Upazilas</option>');

                if (divisionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_districts') }}",
                        data: { divisionId: divisionId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#district_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
                $('#table').DataTable().ajax.reload();
            });

            $('#district_id').change(function () {
                let districtId = $(this).val();
                $('#upazila_id').html('<option value="">All Upazilas</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#upazila_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
                $('#table').DataTable().ajax.reload();
            });

            $('#upazila_id').change(function() {
                $('#table').DataTable().ajax.reload();
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
                            url: "{{ url('admin/markets') }}/" + id,
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
        });
    </script>
@endsection
