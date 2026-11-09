@extends('layouts.app')

@section('title', 'SR Route Plans')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Assigned SR Route Plans (PJP)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>S/L</th>
                                    <th>SR Name</th>
                                    <th>Total Assigned Markets</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- PJP Modal -->
    <div class="modal fade" id="pjpModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="pjpModalLabel">Weekly PJP</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="pjpModalBody">
                    <div class="text-center"><i class="fa fa-spinner fa-spin fa-3x text-muted"></i></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
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
                ajax: '{{ route('sr_route_plans.datatable') }}',
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'sr_name', name: 'name'},
                    {data: 'total_routes', name: 'total_routes', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false},
                ],
                order: [[1, 'asc']]
            });

            $(document).on('click', '.btn-view-pjp', function() {
                var srId = $(this).data('id');
                $('#pjpModalBody').html('<div class="text-center"><i class="fa fa-spinner fa-spin fa-3x text-muted"></i></div>');
                $('#pjpModal').modal('show');

                $.ajax({
                    url: '{{ route('sr_route_plans.view-pjp') }}',
                    type: 'GET',
                    data: { sr_id: srId },
                    success: function(res) {
                        $('#pjpModalLabel').text(res.title);
                        $('#pjpModalBody').html(res.html);
                    },
                    error: function() {
                        $('#pjpModalBody').html('<div class="alert alert-danger">Failed to load data.</div>');
                    }
                });
            });
        });
    </script>
@endsection
