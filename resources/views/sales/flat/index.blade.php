@extends('layouts.app')
@section('title','Flat Sales Orders')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('flat_sales_create'))
                    <a href="{{ route('flat-sales.create') }}" class="btn btn-info bg-gradient-info btn-sm">Flat Sales Create <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="customer" class="col-form-label">Customer <span
                                        class="text-danger">*</span></label>
                                <select name="customer" id="customer" class="form-control select2">
                                    <option value="">All Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date') }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="Enter End Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -4px;" type="button" id="search_btn" name="search"
                                       class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">Order No.</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Project</th>
                                <th class="text-center">Floor</th>
                                <th class="text-center">Flat</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Flat Price</th>
                                <th class="text-center">Discount</th>
                                <th class="text-center">Booking Money</th>
                                <th class="text-center">Down Payment</th>
                                <th class="text-center">Installment Amount</th>
                                <th class="text-center">Installment</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Advance Adjust</th>
                                <th class="text-center">Due</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    <div class="modal fade" id="modal-pay" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Customer Installment Payment Receive</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-customer-payment-form" method="POST" action="{{ route('flat-sales.installment_payment_receive') }}">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="order_no">Order No. <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="order_no" name="order_no" class="form-control">
                                    <span id="order_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="date" name="date" value="{{ date('d-m-Y') }}" class="form-control date-picker">
                                    <span id="date-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="payment_mode">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-control select2">
                                        <option value="">Select Payment Mode</option>
                                        <option value="advance">Advance from Customer</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select>
                                    <span id="customer-advance"></span>
                                    <span id="payment_mode-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12" style="display: none" id="if_payment_bank_mode">
                                <div class="form-group {{ $errors->has('cheque_no') ? 'has-error' :'' }}">
                                    <label for="cheque_no">Cheque No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('cheque_no') }}" id="cheque_no"
                                           class="form-control"
                                           name="cheque_no" placeholder="Enter Cheque No.">
                                    @error('cheque_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total">Total</label>
                                    <input type="text" readonly id="total" class="form-control" name="total">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <label for="paid">Paid <span class="text-danger">*</span></label>
                                    <input type="number" step="any"  id="paid" class="form-control" name="paid">
                                    <span id="paid-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <input type="hidden" id="due_hidden" name="due_hidden">
                                    <label for="due">Due</label>
                                    <input type="text" readonly id="due" class="form-control" name="due">
                                    <span id="due-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="notes">Notes</label>
                                    <input type="text" id="notes" class="form-control" name="notes"
                                           placeholder="Enter Notes">
                                    <span id="notes-error" class="help-block error-message"></span>

                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <div type="button" class="btn btn-default"><span>Installment: <span id="paid_installment"></span>/<span id="installment"></span></span></div>
                    <button type="submit" id="customer-payment-btn" class="btn btn-info bg-gradient-info">Save</button>
                </div>
            </div>

        </div>

    </div>

@endsection
@section('script')
    <script>
        $(function () {
            calculate();
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
                            url: "{{ route('flat-sales.destroy', ['flat_sale' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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


            $('body').on('click', '.customer-pay', function () {
                let orderId = $(this).data('id');
                $("#order_no").val($(this).data('order_no'));
                let advance = $(this).data('advance');
                $("#customer-advance").html('<b class="'+(advance > 0 ? 'text-success' : 'text-danger')+'">Advance Outstanding Balance: '+advance+'</b>');
                $("#installment").text($(this).data('installment'));
                $("#paid_installment").text($(this).data('paid_installment'));
                $("#total").val($(this).data('total'));
                $("#due").val($(this).data('due'));
                $("#due_hidden").val($(this).data('due'));
                $("#order_id").val(orderId);
                $('#modal-pay').modal('show');
            })
            $('#payment_mode').change(function (){
                let paymentMode = $(this).val();
                var valuesArray = paymentMode.split('|'); // Split the selected value into an array
                var id = valuesArray[0];
                var mode = valuesArray[1];
                if (paymentMode != '' && mode == 1){
                    $("#if_payment_bank_mode").show();
                }else{
                    $("#if_payment_bank_mode").hide();
                }
            })
            $('#customer-payment-btn').click(function() {
                preloaderToggle(true);
                // Create a FormData object
                var formData = new FormData(document.getElementById('modal-customer-payment-form'));
                $.ajax({
                    type: 'POST',
                    url: $('#modal-customer-payment-form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        preloaderToggle(false);
                        if (response.status){
                            ajaxSuccessMessage(response.message)
                            window.location.href = response.redirect_url;
                        }else{
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function(xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            ajaxWarningMessage('Please fill up validate required fields.');

                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text('');
                            $('.form-group-has-error').removeClass('has-error');

                            // Update error messages for each field
                            $.each(errors, function(field, errorMessage) {
                                $('#'+field+'-error').text(errorMessage[0]);
                                $('#'+field+'-error').closest('.form-group-has-error').addClass('has-error')
                            });
                        }
                    }
                });
            });
            $('body').on('keyup', '#paid', function() {
                calculate();
            });

            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flat-sales.datatable') }}",
                    data: function (d) {
                        d.customer = $("#customer").val()
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'order_no', name: 'order_no'},
                    {data: 'date', name: 'date'},
                    {data: 'project_name', name: 'project.name'},
                    {data: 'floor_name', name: 'floor.name'},
                    {data: 'flat_flat_no', name: 'flat.flat_no'},
                    {data: 'customer_name', name: 'customer.name'},
                    {
                        data: 'flat_price',
                        name: 'flat_price',
                        render: function (data, type, row) {
                            if (row.flat_price > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getJvVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.flat_price).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.flat_price).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {
                        data: 'discount',
                        name: 'discount',
                        render: function (data, type, row) {
                            return jsNumberFormat(parseFloat(row.discount).toFixed(2));
                        },className:'text-right'
                    },
                    {
                        data: 'booking_money',
                        name: 'booking_money',
                        render: function (data, type, row) {
                            return jsNumberFormat(parseFloat(row.booking_money).toFixed(2));
                        },className:'text-right'
                    },
                    {
                        data: 'down_payment',
                        name: 'down_payment',
                        render: function (data, type, row) {
                            return jsNumberFormat(parseFloat(row.down_payment).toFixed(2));
                        },className:'text-right'
                    },
                    {
                        data: 'installment_amount',
                        name: 'installment_amount',
                        render: function (data, type, row) {
                            return jsNumberFormat(parseFloat(row.installment_amount).toFixed(2));
                        },className:'text-right'
                    },
                    {
                        data: null,
                        name: 'installment',
                        render: function (data, type, row) {
                            return `${row.paid_installment}/${row.installment}`;

                        },className:'text-center'
                    },
                    {
                        data: 'paid',
                        name: 'paid',
                        render: function (data, type, row) {
                            if (row.paid > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.paid).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.paid).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {
                        data: 'advance_adjust',
                        name: 'advance_adjust',
                        render: function (data, type, row) {
                            if (row.paid > 0) {
                                var order_id = row.customer_id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getAdjustJvVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.advance_adjust).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.advance_adjust).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {data: 'due', name: 'due',
                        render: function(data) {
                            return jsNumberFormat(parseFloat(data).toFixed(2));
                        },className:'text-right'
                    },
                    {data: 'notes', name: 'notes'},
                    {data: 'action', name: 'action', orderable: false},
                ],
                order: [[0, 'desc']],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('#start_date,#end_date,#search_btn,#customer').change(function () {
                table.ajax.reload();
            });
        });
        function calculate() {

            let total = parseFloat($('#total').val());
            total = (isNaN(total) || total < 0) ? 0 : total;

            let due_hidden = parseFloat($('#due_hidden').val());
            due_hidden = (isNaN(due_hidden) || due_hidden < 0) ? 0 : due_hidden;

            let paid = parseFloat($('#paid').val());
            paid = (isNaN(paid) || paid < 0) ? 0 : paid;

            $("#due").val(Math.ceil(due_hidden - paid).toFixed(2));
        }
        function getVoucherRoute(order_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER,'flat_sale_order_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
        function getAdjustJvVoucherRoute(order_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER,'advance_supplier_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
        function getJvVoucherRoute(order_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER,'flat_sale_order_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
    </script>
@endsection
