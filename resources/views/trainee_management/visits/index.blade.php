@extends('layouts.app')
@section('title', 'Trainee Visit Log')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Visit History</h3>
                    <div class="card-tools">
                        @if(auth()->user()->role == 'Trainee')
                        <a href="{{ route('trainee.visits.create') }}" class="btn btn-success btn-sm">
                            <i class="fas fa-plus-circle mr-1"></i> Log New Visit
                        </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Filter by Trainee</label>
                                <select id="trainee_filter" class="form-control select2">
                                    <option value="">All Trainees</option>
                                    @foreach($trainees as $trainee)
                                        <option value="{{ $trainee->id }}">{{ $trainee->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Filter by Type</label>
                                <select id="type_filter" class="form-control select2">
                                    <option value="">All Categories</option>
                                    @foreach($visitTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="visitTable" class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>Date</th>
                                <th>Trainee</th>
                                <th>Category</th>
                                <th>Client Details</th>
                                <th>Address</th>
                                <th>Live Address</th>
                                <th>Photo</th>
                                <th>GPS</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog" aria-labelledby="viewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="viewModalLabel font-weight-bold">Visit Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modal_body">
                    <div class="text-center p-5">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                    </div>
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
            let table = $('#visitTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('trainee.visits.datatable') }}',
                    data: function(d) {
                        d.trainee_id = $('#trainee_filter').val();
                        d.trainee_visit_type_id = $('#type_filter').val();
                    }
                },
                columns: [
                    {data: 'date', name: 'date'},
                    {data: 'trainee_name', name: 'trainee.name'},
                    {data: 'visit_type', name: 'visitType.name'},
                    {data: 'customer_name', name: 'visitClient.name'},
                    {data: 'address', name: 'address', orderable: false, searchable: false},
                    {data: 'location', name: 'location'},
                    {data: 'image', name: 'image', orderable: false, searchable: false},
                    {data: 'gps', name: 'gps', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ],
                order: [[0, 'desc']]
            });

            $('#trainee_filter, #type_filter').change(function() {
                table.draw();
            });

            // View Details Modal Logic
            $('body').on('click', '.btn-view', function() {
                let id = $(this).data('id');
                $('#modal_body').html('<div class="text-center p-5"><i class="fas fa-spinner fa-spin fa-2x"></i></div>');
                $('#viewModal').modal('show');
                
                $.get("{{ url('admin/trainee/visits') }}/" + id, function(data) {
                    $('#modal_body').html(data);
                });
            });
        });
    </script>
@endsection
