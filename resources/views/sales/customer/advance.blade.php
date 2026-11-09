@extends('layouts.app')
@section('title','Advance from Clients')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                   <div class="card-title">Advance from Clients List</div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Name</th>
                                <th>Mobile No.</th>
                                <th>Email</th>
                                <th>Address</th>
                                <th>Advance Opening</th>
                                <th>Advance Received</th>
                                <th>Amount Settled</th>
                                <th>Outstanding Balance</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="5" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th></th>
                            </tr>
                            </tfoot>
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
                    <h4 class="modal-title">Advance from Client Receive</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-customer-advance-payment-form" method="POST" action="{{ route('customer.advance_payment') }}">
                        @csrf
                        <input type="hidden" id="customer_id" name="customer_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="customer_name">Client Name <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="customer_name" name="customer_name" class="form-control">
                                    <span id="supplier_name-error" class="help-block error-message"></span>
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
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select>
                                    <span id="payment_mode-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12" style="display: none" id="if_payment_bank_mode">
                                <div class="form-group form-group-has-error">
                                    <label for="cheque_no">Cheque No.</label>
                                    <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Enter Cheque No.">
                                    <span id="cheque_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total">Total Advance</label>
                                    <input type="text" readonly id="total" class="form-control" name="total">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <label for="payment">Payment Receive <span class="text-danger">*</span></label>
                                    <input type="number" id="payment" class="form-control" name="payment">
                                    <span id="payment-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <input type="hidden" id="due_hidden" name="due_hidden">
                                    <label for="due">Advance Due</label>
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
                    <button type="submit" id="customer-advance-payment-btn" class="btn btn-info bg-gradient-info">Save</button>
                </div>
            </div>

        </div>

    </div>

@endsection
@section('script')
    <script>
        $(function () {

            $('body').on('click', '.btn-advance-customer', function () {
                let customerId = $(this).data('id');
                $("#customer_name").val($(this).data('name'));
                $("#total").val($(this).data('total'));
                $("#due").val($(this).data('due'));
                $("#due_hidden").val($(this).data('due'));
                $("#customer_id").val(customerId);
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
            $('#customer-advance-payment-btn').click(function() {
                preloaderToggle(true);
                // Create a FormData object
                var formData = new FormData(document.getElementById('modal-customer-advance-payment-form'));
                $.ajax({
                    type: 'POST',
                    url: $('#modal-customer-advance-payment-form').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        preloaderToggle(false);
                        if (response.status){
                            ajaxSuccessMessage(response.message)
                            window.location.href = response.redirect_url;
                        }else{
                            console.log(response.message);
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: response.message
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById('notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
                        }
                    },
                    error: function(xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            $(document).Toasts('create', {
                                icon: 'fas fa-envelope fa-lg',
                                class: 'bg-warning',
                                title: 'Error',
                                autohide: true,
                                delay: 2000,
                                body: 'Please fill up validate required fields.'
                            })
                            // Play the notification sound
                            let notificationSound = document.getElementById('notification-error-audio');
                            if (notificationSound) {
                                notificationSound.play();
                            }
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
            $('body').on('keyup', '#payment', function() {
                calculate();
            });

            $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('customer.datatable') }}',
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]
                ],
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'mobile_no', name: 'mobile_no'},
                    {data: 'email', name: 'email'},
                    {data: 'address', name: 'address'},
                    {
                        data: 'advance_opening_balance',
                        name: 'advance_opening_balance',
                        render: function (data, type, row) {
                            // Format the opening_balance as a number with commas and 2 decimal places
                            return parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2});

                        },className:'text-right'
                    },
                    {
                        data: 'advance_payment',
                        name: 'advance_payment',
                        render: function (data, type, row) {
                            if (row.advance_payment > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.advance_payment).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.advance_payment).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {
                        data: 'advance_settled',
                        name: 'advance_settled',
                        render: function (data, type, row) {
                            if (row.advance_settled > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getJvVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.advance_settled).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.advance_settled).toFixed(2));
                            }
                        },className:'text-right'
                    },
                    {
                        data: 'outstanding_balance',
                        name: 'outstanding_balance',
                        render: function (data, type, row) {
                            // Format the opening_balance as a number with commas and 2 decimal places
                            return parseFloat(data).toLocaleString(undefined, {minimumFractionDigits: 2});

                        },className:'text-right'
                    },
                    {data: 'advance_action', name: 'advance_action', orderable: false},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
                footerCallback: function (row, data, start, end, display) {
                    var api = this.api();

                    // Helper function to sum the column values
                    var intVal = function (i) {
                        return typeof i === 'string' ?
                            i.replace(/[\$,]/g, '') * 1 :
                            typeof i === 'number' ?
                                i : 0;
                    };

                    // Total over this page for each column
                    var pageTotalOpeningBalance = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    var pageTotal= api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    var pageTotalSettled = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    var pageTotalOutstanding = api.column(8, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Update footer
                    $(api.column(5).footer()).html(jsNumberFormat(pageTotalOpeningBalance.toFixed(2)));
                    $(api.column(6).footer()).html(jsNumberFormat(pageTotal.toFixed(2)));
                    $(api.column(7).footer()).html(jsNumberFormat(pageTotalSettled.toFixed(2)));
                    $(api.column(8).footer()).html(jsNumberFormat(pageTotalOutstanding.toFixed(2)));
                }
            });

            function calculate() {

                let total = parseFloat($('#total').val());
                total = (isNaN(total) || total < 0) ? 0 : total;

                let due_hidden = parseFloat($('#due_hidden').val());
                due_hidden = (isNaN(due_hidden) || due_hidden < 0) ? 0 : due_hidden;

                let payment = parseFloat($('#payment').val());
                payment = (isNaN(payment) || payment < 0) ? 0 : payment;

                $("#total").val(Math.ceil(payment).toFixed(2));
                $("#due").val(Math.ceil(due_hidden + payment).toFixed(2));
            }
            function getVoucherRoute(order_id) {
                return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER,'advance_supplier_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
            }
            function getJvVoucherRoute(order_id) {
                return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER,'advance_supplier_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
            }

        });
    </script>
@endsection
