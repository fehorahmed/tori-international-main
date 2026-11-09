@extends('layouts.app')
@section('title','Prospects')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                   <div class="card-title">Prospects List</div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>Prospect No.</th>
                                <th>Client</th>
                                <th>Contact No.</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Status</th>
                                <th>Employee</th>
                                <th>Notes</th>
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
                    <form id="modal-feedback-form" action="{{ route('prospects.feedback') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <input type="hidden" id="prospect_id" name="prospect_id">
                            <label for="status">Status <span class="text-danger">*</span></label>
                            <select name="status" id="status" class="form-control select2">
                                <option value="">Select Status</option>
                                @foreach($statuses as $status)
                                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-message" id="status-error"></span>
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
@endsection
@section('script')
    <script>
        $(function () {

         $('body').on('click', '.btn-add-feedback', function(){
                preloaderToggle(true);
                $('#prospect_id').val($(this).data('id'));
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
         $('body').on('click', '.btn-promoted-opportunity', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Promoted to opportunity it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "POST",
                            url: "{{ route('prospects.promote', ['prospect' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Promoted!',
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

          $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('prospects.datatable') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'order_no', name: 'order_no'},
                    {data: 'crm_client_name', name: 'crmClient.name'},
                    {data: 'crm_client_contact_no', name: 'crmClient.contact_no'},
                    {data: 'crm_client_email', name: 'crmClient.email'},
                    {data: 'crm_client_address', name: 'crmClient.address'},
                    {data: 'crm_status_name', name: 'crmStatus.name'},
                    {data: 'employee', name: 'employee.name'},
                    {data: 'notes', name: 'notes'},
                    {data: 'action', name: 'action', orderable: false,className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });

        });
    </script>
@endsection
