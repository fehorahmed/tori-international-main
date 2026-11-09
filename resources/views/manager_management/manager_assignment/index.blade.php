@extends('layouts.app')
@section('title', $title)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('manager-assignments.create', ['type' => $type]) }}" class="btn btn-info bg-gradient-info btn-sm">New Assignment/Transfer</a>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Status</label>
                                <select id="status_filter" class="form-control select2">
                                    <option value="">All Status</option>
                                    <option value="1" selected>Active Only</option>
                                    <option value="0">Inactive/Past</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" id="type_filter" value="{{ $type }}">
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Manager Name</th>
                                <th>Branch </th>
                                <th>Assigned Date</th>
                                <th>Unassigned Date</th>
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

    <!-- Release Modal -->
    <div class="modal fade" id="releaseModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Release Manager</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="releaseForm">
                    @csrf
                    <input type="hidden" name="id" id="assignment_id">
                    <div class="modal-body">
                        <p>Are you sure you want to release <strong id="manager_name_text"></strong> from <strong id="branch_name_text"></strong>?</p>
                        <div class="form-group">
                            <label>Release/Transfer Date <span class="text-danger">*</span></label>
                            <input type="text" name="unassigned_date" class="form-control date-picker" value="{{ date('d-m-Y') }}" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Confirm Release</button>
                    </div>
                </form>
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
                    url: "{{ route('manager-assignments.datatable') }}",
                    data: function (d) {
                        d.status = $('#status_filter').val();
                        d.manager_type = $('#type_filter').val();
                    }
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
                    {data: 'manager_name', name: 'manager.name'},
                    {data: 'branch_name', name: 'sisterConcern.name'},
                    {data: 'assigned_date', name: 'assigned_date'},
                    {data: 'unassigned_date', name: 'unassigned_date'},
                    {data: 'status', name: 'status', className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center'},
                ],
                order: [[4, 'desc']], // Sort by assigned_date
                dom: 'lBfrtip',
                buttons: datatableButtons(),
                responsive: true,
                autoWidth: false,
            });

            $('#status_filter, #type_filter').change(function() {
                table.ajax.reload();
            });

            // Handle Release Button Click
            $(document).on('click', '.btn-release', function() {
                var id = $(this).data('id');
                var name = $(this).data('name');
                var branch = $(this).data('branch');

                $('#assignment_id').val(id);
                $('#manager_name_text').text(name);
                $('#branch_name_text').text(branch);
                $('#releaseModal').modal('show');
            });

            // Handle Release Form Submission
            $('#releaseForm').submit(function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('manager-assignments.release') }}",
                    method: "POST",
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            $('#releaseModal').modal('hide');
                            Swal.fire('Success', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Something went wrong!', 'error');
                    }
                });
            });
        });
    </script>
@endsection
