@extends('layouts.app')
@section('title','Outlet Setups')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('lead_entry'))
                    <a href="{{ route('leads.create') }}" class="btn btn-info bg-gradient-info btn-sm">Outlet Setup Entry <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="text" id="start_date" value="{{ request('start_date') }}" class="form-control date-picker" autocomplete="off" placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="text" id="end_date" value="{{ request('end_date') }}" class="form-control date-picker" autocomplete="off" placeholder="End Date">
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

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_customer_type">Customer Type</label>
                                <select id="filter_customer_type" class="form-control select2">
                                    <option value="">All Customer Types</option>
                                    @foreach($customerTypes as $customerType)
                                        <option value="{{ $customerType->id }}">{{ $customerType->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="filter_market">Market / Area</label>
                                <select id="filter_market" class="form-control select2">
                                    <option value="">All Markets</option>
                                    @foreach($markets as $market)
                                        <option value="{{ $market->id }}">{{ $market->name }}</option>
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
                    <form id="modal-feedback-form" action="{{ route('leads.feedback') }}" method="post">
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

    <!-- Convert Modal -->
    <div class="modal fade" id="modal-convert" data-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Convert To Customer</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-convert-form" action="{{ route('leads.convert') }}" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <input type="hidden" id="convert_lead_id" name="lead_id">
                                <label>Client Name <span class="text-danger">*</span></label>
                                <input type="text" id="convert_client_name" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Contact No. <span class="text-danger">*</span></label>
                                <input type="text" id="convert_client_contact" name="mobile_no" class="form-control" required>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Email</label>
                                <input type="email" id="convert_client_email" name="email" class="form-control">
                            </div>

                            <div class="col-md-6 form-group">
                                <label for="convert_customer_type_id">Outlet Type <span class="text-danger">*</span></label>
                                <select name="customer_type_id" id="convert_customer_type_id" class="form-control select2" required>
                                    <option value="">Select Type</option>
                                    @foreach($customerTypes as $type)
                                        <option value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 form-group">
                                <label>Division <span class="text-danger">*</span></label>
                                <select name="division" id="convert_division" class="form-control select2" required>
                                    <option value="">Select Division</option>
                                    @foreach(App\Models\Division::all() as $division)
                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>District <span class="text-danger">*</span></label>
                                <select name="district" id="convert_district" class="form-control select2" required>
                                    <option value="">Select District</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Upazila <span class="text-danger">*</span></label>
                                <select name="upazila" id="convert_upazila" class="form-control select2" required>
                                    <option value="">Select Upazila</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Union/Pourashava</label>
                                <select name="union" id="convert_union" class="form-control select2">
                                    <option value="">Select Union/Pourashava</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Market <span class="text-danger">*</span></label>
                                <select name="market_id" id="convert_market_id" class="form-control select2" required>
                                    <option value="">Select Market</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label>Village</label>
                                <select name="village_id" id="convert_village_id" class="form-control select2">
                                    <option value="">Select Village</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-save-convert" class="btn btn-primary bg-gradient-primary">Save & Convert</button>
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

            $('body').on('click', '.btn-convert', function(){
                let leadId = $(this).data('id');
                $('#convert_lead_id').val(leadId);
                
                preloaderToggle(true);
                $.ajax({
                    url: '{{ route('leads.client_info', ['lead' => ':leadId']) }}'.replace(':leadId', leadId),
                    method: 'GET',
                    success: function(response) {
                        if(response.status) {
                            let client = response.client;
                            $('#convert_client_name').val(client.name);
                            $('#convert_client_contact').val(client.contact_no);
                            $('#convert_client_email').val(client.email);
                            
                            if(client.customer_type_id) {
                                $('#convert_customer_type_id').val(client.customer_type_id).trigger('change.select2');
                            } else {
                                $('#convert_customer_type_id').val('').trigger('change.select2');
                            }
                            
                            // Load location data
                            if (client.market) {
                                $('#convert_market_id').html('<option value="'+client.market.id+'">'+client.market.name+'</option>').trigger('change.select2');
                            } else {
                                $('#convert_market_id').html('<option value="">Select Market</option>').trigger('change.select2');
                            }
                            $('#convert_division').val(client.division_id).trigger('change.select2');
                            
                            if (client.district) {
                                $('#convert_district').html('<option value="'+client.district.id+'">'+client.district.name+'</option>').trigger('change.select2');
                            } else {
                                $('#convert_district').html('<option value="">Select District</option>').trigger('change.select2');
                            }

                            if (client.upazila) {
                                $('#convert_upazila').html('<option value="'+client.upazila.id+'">'+client.upazila.name+'</option>').trigger('change.select2');
                            } else {
                                $('#convert_upazila').html('<option value="">Select Upazila</option>').trigger('change.select2');
                            }

                            if (client.union) {
                                $('#convert_union').html('<option value="union_'+client.union.id+'">'+client.union.name+'</option>').trigger('change.select2');
                            } else if (client.pourashava) {
                                $('#convert_union').html('<option value="pourashava_'+client.pourashava.id+'">'+client.pourashava.name+'</option>').trigger('change.select2');
                            } else {
                                $('#convert_union').html('<option value="">Select Union/Pourashava</option>').trigger('change.select2');
                            }

                            if (client.village) {
                                $('#convert_village_id').html('<option value="'+client.village.id+'">'+client.village.name+'</option>').trigger('change.select2');
                            } else {
                                $('#convert_village_id').html('<option value="">Select Village</option>').trigger('change.select2');
                            }

                            preloaderToggle(false);
                            $("#modal-convert").modal('show');
                        } else {
                            preloaderToggle(false);
                            ajaxErrorMessage('Failed to fetch client info');
                        }
                    },
                    error: function() {
                        preloaderToggle(false);
                        ajaxErrorMessage('Error fetching client info');
                    }
                });
            });

            // Location change events
            $('#convert_division').change(function () {
                var divisionId = $(this).val();
                $('#convert_district').html('<option value="">Select District</option>');
                $('#convert_upazila').html('<option value="">Select Upazila</option>');
                $('#convert_union').html('<option value="">Select Union/Pourashava</option>');
                $('#convert_village_id').html('<option value="">Select Village</option>');

                if (divisionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_districts') }}",
                        data: {divisionId: divisionId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            $('#convert_district').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            });

            $('#convert_district').change(function () {
                var districtId = $(this).val();
                $('#convert_upazila').html('<option value="">Select Upazila</option>');
                $('#convert_union').html('<option value="">Select Union/Pourashava</option>');
                $('#convert_village_id').html('<option value="">Select Village</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: {districtId: districtId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            $('#convert_upazila').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            });

            $('#convert_upazila').change(function () {
                var upazilaId = $(this).val();
                $('#convert_union').html('<option value="">Select Union/Pourashava</option>');
                $('#convert_village_id').html('<option value="">Select Village</option>');
                $('#convert_market_id').html('<option value="">Select Market</option>');

                if (upazilaId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_unions') }}",
                        data: {upazilaId: upazilaId}
                    }).done(function (data) {
                        $.each(data.unions, function (index, item) {
                            $('#convert_union').append('<option value="union_' + item.id + '">' + item.name + '</option>');
                        });
                        $.each(data.pourashavas, function (index, item) {
                            $('#convert_union').append('<option value="pourashava_' + item.id + '">' + item.name + '</option>');
                        });
                    });

                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_markets') }}",
                        data: {upazilaId: upazilaId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            $('#convert_market_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            });

            $('#convert_union').change(function () {
                var unionId = $(this).val();
                $('#convert_village_id').html('<option value="">Select Village</option>');

                if (unionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_villages') }}",
                        data: {unionId: unionId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            $('#convert_village_id').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            });

            $("#btn-save-convert").click(function () {
                let customerType = $('#convert_customer_type_id').val();
                if (!customerType) {
                    ajaxWarningMessage('Please select a Customer Type.');
                    return;
                }

                preloaderToggle(true);
                $.ajax({
                    type: 'POST',
                    url: $('#modal-convert-form').attr('action'),
                    data: $('#modal-convert-form').serialize(),
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            $("#modal-convert").modal('hide');
                            ajaxSuccessMessage(response.message);
                            table.ajax.reload();
                        } else {
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function (xhr) {
                        preloaderToggle(false);
                        ajaxErrorMessage('Something went wrong!');
                    }
                });
            });

            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('leads.datatable') }}',
                    data: function (d) {

                        d.user_id = $('#filter_user').val();
                        d.start_date = $('#start_date').val();
                        d.end_date = $('#end_date').val();
                        d.customer_type_id = $('#filter_customer_type').val();
                        d.market_id = $('#filter_market').val();
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

            $('#filter_user, #start_date, #end_date, #filter_customer_type, #filter_market').change(function () {
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
                            url: "{{ route('leads.destroy', ['lead' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
