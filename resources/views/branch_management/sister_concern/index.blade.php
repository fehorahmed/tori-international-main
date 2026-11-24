@extends('layouts.app')
@section('title',$typeName)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if(auth()->user()->can('branch_create'))
                    <a href="{{ route('sister-concerns.create') }}" class="btn btn-info bg-gradient-info">Create {{ $typeName }}</a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-4">
                            <select name="area_manager_id" id="area_manager_id" class="form-control select2" data-placeholder="Select Area Manager" style="width: 100%;">
                                <option value="">Select Area Manager</option>
                                @foreach($areaManagers as $areaManager)
                                    <option value="{{ $areaManager->id }}">{{ $areaManager->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <button type="button" class="btn btn-info btn-sm">Search</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name</th>
                                <th>Code</th>
                                <th>Logo</th>
                                <th>Contact</th>
                                <th>Email</th>
                                <th>Area Manager</th>
                                <th>Branch Manager</th>
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
@endsection
@section('script')
    <script>
        $(function () {

            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('sister-concerns.datatable') }}",
                    data: function (d) {
                        d.area_manager_id = $('#area_manager_id').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false,className:'text-center'},
                    {data: 'name', name: 'name'},
                    {data: 'code', name: 'code'},
                    {data: 'logo', name: 'logo', orderable: false, searchable: false},
                    {data: 'contact', name: 'contact'},
                    {data: 'email', name: 'email'},
                    {data: 'areaManager', name: 'areaManager', orderable: false, searchable: false},
                    {data: 'branchManager', name: 'branchManager', orderable: false, searchable: false},
                    {data: 'status', name: 'status', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                "drawCallback": function(settings) {
                    $('[data-toggle="tooltip"]').tooltip();
                }
            });
            $('#area_manager_id').change(function(){
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
                            url: "{{ route('sister-concerns.destroy', ['sister_concern' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
