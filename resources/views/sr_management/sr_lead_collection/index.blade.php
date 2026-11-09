@extends('layouts.app')
@section('title','Leads')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('sr_lead_collection'))
                    <a href="{{ route('sr_lead_collection.create') }}" class="btn btn-info bg-gradient-info btn-sm">Lead Entry <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="text" id="start_date" class="form-control date-picker" autocomplete="off" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="text" id="end_date" class="form-control date-picker" autocomplete="off" placeholder="End Date">
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_user">Created By</label>
                                <select id="filter_user" class="form-control select2">
                                    <option value="">All Users</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Lead No.</th>
                                <th>Date</th>
                                <th>Image</th>
                                <th>Client</th>
                                <th>Contact No.</th>

                                <th>Location Info</th>
                                <th>Notes</th>
                                <th>Created By</th>
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

    <div class="modal fade" id="modal-feedback" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Feedback</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-feedback-form" action="{{ route('sr_lead_collection.feedback') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <input type="hidden" id="lead_id" name="lead_id">

                        </div>
                        <div class="form-group">
                            <label for="feedback">Feed Back <span class="text-danger">*</span></label>
                            <textarea id="feedback" placeholder="Enter Feedback" class="form-control"
                                      name="feedback"></textarea>
                            <span class="text-danger error-message" id="feedback-error"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-save-feedback" class="btn btn-info bg-gradient-info">Update</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Location Modal -->
    <div class="modal fade" id="modal-location" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h4 class="modal-title"><i class="fas fa-map-marker-alt"></i> Location Information</h4>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label>Client Address:</label>
                        <p id="modal-address-text" class="p-2 border rounded bg-light mb-0">N/A</p>
                    </div>
                    <div class="form-group mb-3">
                        <label>Live Address / Location:</label>
                        <p id="modal-loc-text" class="p-2 border rounded bg-light mb-0">N/A</p>
                    </div>
                    <div class="form-group text-center mt-3" id="modal-loc-map-container" style="display: none;">
                        <a href="#" id="modal-loc-map-btn" target="_blank" class="btn btn-primary">
                            <i class="fas fa-map-marked-alt mr-1"></i> Open in Google Maps
                        </a>
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

         $('body').on('click', '.btn-add-feedback', function(){
                preloaderToggle(true);
                $('#lead_id').val($(this).data('id'));
                $("#modal-feedback").modal('show');
                preloaderToggle(false);
            })
            $("#btn-save-feedback").click(function () {
                $('.error-message').text('');
                preloaderToggle(true);
                $.ajax({
                    type: 'POST',
                    url: $('#modal-feedback-form').attr('action'),
                    data: $('#modal-feedback-form').serialize(),
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            $("#modal-feedback").modal('hide');
                            ajaxSuccessMessage(response.message)
                            location.reload();
                        } else {
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function (xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            ajaxWarningMessage('Please fill up validate required fields.');
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text('');

                            // Update error messages for each field
                            $.each(errors, function (field, errorMessage) {
                                $('#' + field + '-error').text(errorMessage[0]);
                            });
                        }
                    }
                })
            })

            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('sr_lead_collection.datatable') }}',
                    data: function (d) {

                        d.user_id = $('#filter_user').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'order_no', name: 'order_no'},
                    {data: 'date', name: 'date'},
                    {data: 'image', name: 'image', orderable: false, className: 'text-center'},
                    {data: 'crm_client_name', name: 'crmClient.name'},
                    {data: 'crm_client_contact_no', name: 'crmClient.contact_no'},

                    {data: 'location', name: 'location', orderable: false, className: 'text-center'},
                    {data: 'notes', name: 'notes'},
                    {data: 'created_by', name: 'user.name', orderable: false, searchable: false},
                    {data: 'action', name: 'action', orderable: false,className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

            $('#filter_user, #start_date, #end_date').change(function () {
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
                            url: "{{ route('sr_lead_collection.destroy', ['sr_lead_collection' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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

            $('body').on('click', '.btn-view-location', function () {
                let address = $(this).data('address');
                let loc = $(this).data('loc');
                let lat = $(this).data('lat');
                let long = $(this).data('long');

                $('#modal-address-text').text(address || 'N/A');
                $('#modal-loc-text').text(loc && loc != '' ? loc : 'N/A');

                if (lat && long) {
                    $('#modal-loc-map-btn').attr('href', 'https://www.google.com/maps?q=' + lat + ',' + long);
                    $('#modal-loc-map-container').show();
                } else {
                    $('#modal-loc-map-container').hide();
                }

                $('#modal-location').modal('show');
            });
        });
    </script>
@endsection
