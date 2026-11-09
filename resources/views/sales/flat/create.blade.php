@extends('layouts.app')
@section('title','Flat Sales Create')
@section('style')
@endsection
@section('content')
    @foreach ($errors->all() as $error)
        <li class="alert alert-danger">{{ $error }}</li>
    @endforeach

    <form action="{{ route('flat-sales.store') }}" method="post" id="form-sales">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header">
                                <div class="card-title">Flat & Sales Information Information</div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('project') ? 'has-error' :'' }}">
                                            <label for="project">Project <span class="text-danger">*</span></label>
                                            <select name="project" id="project" class="form-control select2">
                                                <option value="">Select Project</option>
                                                @foreach($projects as $project)
                                                    <option
                                                        {{ old('project') == $project->id ? 'selected' : '' }} value="{{ $project->id }}">{{ $project->name }}
                                                        -{{ $project->id_no }}</option>
                                                @endforeach
                                            </select>
                                            @error('project')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('floor') ? 'has-error' :'' }}">
                                            <label for="floor">Floor Level <span class="text-danger">*</span></label>
                                            <select name="floor" id="floor" class="form-control select2">
                                                <option value="">Select Floor</option>
                                            </select>
                                            @error('floor')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('flat') ? 'has-error' :'' }}">
                                            <label for="flat">Flat No. <span class="text-danger">*</span></label>
                                            <select name="flat" id="flat" class="form-control select2">
                                                <option value="">Select Flat</option>
                                            </select>
                                            @error('flat')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('flat_price') ? 'has-error' :'' }}">
                                            <label for="flat_price">Flat Price <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="any" value="{{ old('flat_price') }}"
                                                   placeholder="Enter Flat Price" name="flat_price" id="flat_price"
                                                   class="form-control">
                                            @error('flat_price')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('parking') ? 'has-error' :'' }}">
                                            <label for="parking">Car Parking <span
                                                    class="text-danger is_parking" style="display: none">*</span></label>
                                            <input type="number" step="any" value="{{ old('parking') }}"
                                                   placeholder="Enter Parking" name="parking" id="parking"
                                                   class="form-control">
                                            <input type="hidden" id="is_parking" name="is_parking">
                                            @error('parking')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('utility') ? 'has-error' :'' }}">
                                            <label for="utility">Utility</label>
                                            <input type="number" step="any" value="{{ old('utility') }}"
                                                   placeholder="Enter Utility" name="utility" id="utility"
                                                   class="form-control">
                                            @error('utility')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('sub_total') ? 'has-error' :'' }}">
                                            <label for="sub_total">Sub Total</label>
                                            <input type="number" readonly step="any" value="{{ old('sub_total') }}"
                                                   placeholder="Enter Sub Total" name="sub_total" id="sub_total"
                                                   class="form-control">
                                            @error('sub_total')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('discount') ? 'has-error' :'' }}">
                                            <label for="discount">Discount Amount</label>
                                            <input type="number" step="any" value="{{ old('discount') }}"
                                                   placeholder="Enter Discount Amount" name="discount" id="discount"
                                                   class="form-control">
                                            @error('discount')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('total') ? 'has-error' :'' }}">
                                            <label for="total">Total</label>
                                            <input type="number" readonly step="any" value="{{ old('total') }}"
                                                   placeholder="Enter Total" name="total" id="total"
                                                   class="form-control">
                                            @error('total')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('booking_money') ? 'has-error' :'' }}">
                                            <label for="booking_money">Booking Money <span class="text-danger">*</span></label>
                                            <input type="number" step="any" value="{{ old('booking_money') }}"
                                                   placeholder="Enter Booking Money" name="booking_money"
                                                   id="booking_money" class="form-control">
                                            @error('booking_money')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('down_payment') ? 'has-error' :'' }}">
                                            <label for="down_payment">Down Payment <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="any" value="{{ old('down_payment') }}"
                                                   placeholder="Enter Down Payment" name="down_payment"
                                                   id="down_payment" class="form-control">
                                            @error('down_payment')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('due_amount') ? 'has-error' :'' }}">
                                            <label for="due_amount">Due Amount <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="any" readonly value="{{ old('due_amount',0) }}"
                                                   placeholder="Enter Due Amount" name="due_amount" id="due_amount"
                                                   class="form-control">
                                            @error('due_amount')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div
                                            class="form-group {{ $errors->has('installment_type') ? 'has-error' :'' }}">
                                            <label for="installment_type">Installment Type <span
                                                    class="text-danger">*</span></label>
                                            <select name="installment_type" class="form-control select2"
                                                    id="installment_type">
                                                <option value="">Select Installment Type</option>
                                                <option {{ old('installment_type') == 1 ? 'selected' : '' }} value="1">
                                                    Monthly
                                                </option>
                                                <option {{ old('installment_type') == 2 ? 'selected' : '' }} value="2">
                                                    Quarterly
                                                </option>
                                                <option {{ old('installment_type') == 3 ? 'selected' : '' }} value="3">
                                                    Half Yearly
                                                </option>
                                                <option {{ old('installment_type') == 4 ? 'selected' : '' }} value="4">
                                                    Yearly
                                                </option>
                                            </select>
                                            @error('installment_type')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('installment') ? 'has-error' :'' }}">
                                            <label for="installment">Installment <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="any" value="{{ old('installment') }}"
                                                   placeholder="Enter Installment" class="form-control"
                                                   name="installment" id="installment">
                                            @error('installment')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('installment_start_date') ? 'has-error' :'' }}">
                                            <label for="installment_start_date">Installment Start Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" autocomplete="off" value="{{ old('installment_start_date') }}"
                                                   placeholder="Enter Installment Start Date" class="form-control date-picker"
                                                   name="installment_start_date" id="installment_start_date">
                                            @error('installment_start_date')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div
                                            class="form-group {{ $errors->has('installment_amount') ? 'has-error' :'' }}">
                                            <label for="installment_amount">Installment Amount <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="any" readonly
                                                   value="{{ old('installment_amount',0) }}"
                                                   placeholder="Enter Installment Amount" class="form-control"
                                                   name="installment_amount" id="installment_amount">
                                            @error('installment_amount')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div
                                            class="form-group {{ $errors->has('installment_fine_percent') ? 'has-error' :'' }}">
                                            <label for="installment_fine_percent">Installment Failed Fine(%) <span
                                                    class="text-danger">*</span></label>
                                            <input type="number" step="any"
                                                   value="{{ old('installment_fine_percent',0) }}"
                                                   placeholder="Enter Installment Fine (%)" class="form-control"
                                                   name="installment_fine_percent" id="installment_fine_percent">
                                            @error('installment_fine_percent')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group {{ $errors->has('handover_date') ? 'has-error' :'' }}">
                                            <label for="handover_date">Handover Date <span
                                                    class="text-danger">*</span></label>
                                            <input type="text" autocomplete="off" value="{{ old('handover_date') }}"
                                                   placeholder="Enter Handover Date" class="form-control date-picker"
                                                   name="handover_date" id="handover_date">
                                            @error('handover_date')
                                            <span class="help-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                                <a href="{{ route('flat-sales.index') }}"
                                   class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Other Information</div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('date') }}" autocomplete="off" placeholder="Enter Date" class="form-control date-picker"
                                           name="date" id="date">
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12 ">
                                <div class="form-group {{ $errors->has('customer') ? 'has-error' :'' }}">
                                    <label for="customer">Customer <span class="text-danger">*</span>
                                        <button type="button" id="add-new-customer"
                                                class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-plus"></i>
                                        </button>
                                    </label>
                                    <select style="max-width: max-content !important;" name="customer"
                                            class="form-control select2" id="customer">
                                        <option value="">Select Customer</option>
                                        @if (old('customer'))
                                            <option value="{{ old('customer') }}"
                                                    selected>{{ old('customer_select_name') }}</option>
                                        @endif
                                    </select>
                                    <input type="hidden" name="customer_select_name" id="customer_select_name"
                                           value="{{ old('customer_select_name') }}">

                                    @error('customer')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('payment_mode') ? 'has-error' :'' }}">
                                    <label for="payment_mode">Receipt Mode <span
                                            class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-control select2">
                                        <option value="">Select Receipt Mode</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option
                                                {{ old('payment_mode') == ($paymentMode->id.'|'.$paymentMode->payment_mode) ? 'selected' : '' }} value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}
                                                - {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_mode')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12" style="display: none" id="if_payment_bank_mode">
                                <div class="form-group {{ $errors->has('cheque_no') ? 'has-error' :'' }}">
                                    <label for="cheque_no">Cheque No.</label>
                                    <input type="text" value="{{ old('cheque_no') }}" id="cheque_no"
                                           class="form-control"
                                           name="cheque_no" placeholder="Enter Cheque No.">
                                    @error('cheque_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes</label>
                                    <textarea placeholder="Enter Notes" class="form-control" name="notes"
                                              id="notes">{{ old('notes') }}</textarea>
                                    @error('notes')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- customer add Modal -->
    <div class="modal fade" id="modal-customer" data-backdrop="static">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add Customer</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-customer-form" action="{{ route('customer.store-direct') }}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="customer_name">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="customer_name" placeholder="Enter Customer Name" class="form-control"
                                   name="customer_name">
                            <span class="text-danger error-message" id="customer_name-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="mobile_no">Mobile No.</label>
                            <input type="text" id="mobile_no" placeholder="Enter Mobile No." class="form-control"
                                   name="mobile_no">
                            <span class="text-danger error-message" id="mobile_no-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" placeholder="Enter Address" class="form-control"
                                   name="address">
                            <span class="text-danger error-message" id="address-error"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-save-customer" class="btn btn-info bg-gradient-info">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>

        $(function () {
            calculate();

            $('#payment_mode').change(function () {
                let paymentMode = $(this).val();
                if (paymentMode != '') {
                    var valuesArray = paymentMode.split('|'); // Split the selected value into an array
                    var id = valuesArray[0];
                    var mode = valuesArray[1];

                    $('#payment_mode_label').text($("#payment_mode option:selected").text());

                    if (paymentMode != '' && mode == 1) {
                        $("#if_payment_bank_mode").show();
                    } else {
                        $("#if_payment_bank_mode").hide();
                    }
                }
            })
            $('#payment_mode').trigger('change');

            var floorSelected = '{{ old('floor') }}';

            $("#project").change(function () {
                let projectId = $(this).val();
                $('#floor').html('<option value="">Select Floor</option>');
                $("#floor").trigger('change');
                if (projectId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_project_wise_flats_floors') }}",
                        data: {projectId: projectId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            if (floorSelected == item.floor.id)
                                $('#floor').append('<option value="' + item.floor.id + '" selected>' + item.floor.name + '</option>');
                            else
                                $('#floor').append('<option value="' + item.floor.id + '">' + item.floor.name + '</option>');
                        });
                        $("#floor").trigger('change');
                    });
                }
            })
            $("#project").trigger('change');

            var flatSelected = '{{ old('flat') }}';

            $("#floor").change(function () {
                let floorId = $(this).val();
                let projectId = $('#project').val();
                $('#flat').html('<option value="">Select Flat</option>');
                $("#flat").trigger('change');
                if (floorId != '' && projectId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_project_wise_floor_flats') }}",
                        data: {projectId: projectId, floorId: floorId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            if (flatSelected == item.id)
                                $('#flat').append('<option value="' + item.id + '" selected>' + item.flat_no + '</option>');
                            else
                                $('#flat').append('<option value="' + item.id + '">' + item.flat_no + '</option>');
                        });
                        $("#flat").trigger('change');
                    });
                }
            })
            $("#floor").trigger('change');
            $("#flat").change(function () {
                let flatId = $(this).val();
                $('#flat_price').val('');
                $('.is_parking').hide();
                calculate();
                if (flatId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_flat_details') }}",
                        data: {flatId: flatId}
                    }).done(function (data) {
                        $('#flat_price').val(data.price);
                        $('#is_parking').val(data.parking_availability);
                        if (data.parking_availability == 1){
                            $('.is_parking').show();
                        }else{
                            $('.is_parking').hide();
                        }
                        calculate();
                    });
                }
            })
            $("#flat").trigger('change');


            $('#customer').select2({
                ajax: {
                    url: "{{ route('party_json') }}",
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            type: [2],
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });
            $('#customer').on('select2:select', function (e) {
                let data = e.params.data;
                $('#customer_select_name').val(data.text);
            });


            $("#add-new-customer").click(function () {
                preloaderToggle(true);
                $("#modal-customer").modal('show');
                preloaderToggle(false);
            })
            $("#btn-save-customer").click(function () {
                $('.error-message').text('');
                preloaderToggle(true);
                $.ajax({
                    type: 'POST',
                    url: $('#modal-customer-form').attr('action'),
                    data: $('#modal-customer-form').serialize(),
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            $("#modal-customer").modal('hide');
                            $('#customer').prepend('<option selected value="' + response.customer.id + '">' + response.customer.customer_info + '</option>');
                            $('#customer_select_name').val(response.customer.customer_info);
                            ajaxSuccessMessage(response.message)
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

            $('body').on('change', 'input[type="number"]', function () {
                calculate();
            });
            $('body').on('keyup', 'input[type="number"]', function () {
                calculate();
            });
        })

        function calculate() {
            let flat_price = parseFloat($('#flat_price').val());
            flat_price = (isNaN(flat_price) || flat_price < 0) ? 0 : flat_price;

            let utility = parseFloat($('#utility').val());
            utility = (isNaN(utility) || utility < 0) ? 0 : utility;

            let parking = parseFloat($('#parking').val());
            parking = (isNaN(parking) || parking < 0) ? 0 : parking;

            let discount = parseFloat($('#discount').val());
            discount = (isNaN(discount) || discount < 0) ? 0 : discount;

            let booking_money = parseFloat($('#booking_money').val());
            booking_money = (isNaN(booking_money) || booking_money < 0) ? 0 : booking_money;

            let down_payment = parseFloat($('#down_payment').val());
            down_payment = (isNaN(down_payment) || down_payment < 0) ? 0 : down_payment;

            let installment = parseFloat($('#installment').val());
            installment = (isNaN(installment) || installment <= 0) ? 1 : installment; // Prevent division by zero

            // Calculate sub total
            let sub_total = (flat_price + parking + utility);
            $('#sub_total').val(sub_total.toFixed(2)); // Set due amount with 2 decimal places

            // Calculate sub total
            let total = sub_total - discount;
            $('#total').val(total.toFixed(2)); // Set due amount with 2 decimal places

            // Calculate due amount
            let due_amount = total - (booking_money + down_payment);
            due_amount = due_amount < 0 ? 0 : due_amount; // Ensure due amount is not negative

            $('#due_amount').val(due_amount.toFixed(2)); // Set due amount with 2 decimal places

            // Calculate installment amount
            let installment_amount = due_amount / installment;
            $('#installment_amount').val(Math.round(installment_amount.toFixed(2))); // Set installment amount with 2 decimal places
        }

    </script>
@endsection
