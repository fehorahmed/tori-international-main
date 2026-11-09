@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div>
                        <a href="{{ route('microfinance.member.create') }}" class="btn btn-info bg-gradient-info btn-sm">Add Member <i class="fa fa-plus"></i></a>
                    </div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-4">
                            <label class="mb-0 mr-2 text-muted small">Filter by Somiti:</label>
                            <select id="somiti_id" class="form-control form-control-sm select2" style="width: 200px;">
                                <option value="">All Somiti</option>
                                @foreach($somitis as $somiti)
                                    <option value="{{ $somiti->id }}">{{ $somiti->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Member No</th>
                                <th>Photo</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>NID No</th>
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
            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('microfinance.member.datatable') }}',
                    data: function (d) {
                        d.somiti_id = $('#somiti_id').val();
                    }
                },
                columns: [
                    {data: 'member_no', name: 'member_no'},
                    {data: 'photo', name: 'photo', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'nid_no', name: 'nid_no'},
                    {data: 'status', name: 'status'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,
            });

            $('#somiti_id').on('change', function() {
                table.draw();
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
                        $.ajax({
                            method: "DELETE",
                            url: "{{ route('microfinance.member.destroy', ['member' => 'REPLACE_ID']) }}".replace('REPLACE_ID', id),
                            data: { _token: '{{ csrf_token() }}' }
                        }).done(function( response ) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success');
                                $('#table').DataTable().ajax.reload();
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        });
                    }
                })
            });
        });
    </script>
@endsection
