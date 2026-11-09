@extends('layouts.app')
@section('title','Sales Orders')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    @if (auth()->user()->can('sales_create'))
                    <a href="{{ route('sales.create') }}" class="btn btn-info bg-gradient-info btn-sm">Create Sales <i class="fa fa-plus"></i></a>
                    @endif
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="sr" class="col-form-label">SR</label>
                                <select name="sr" id="sr" class="form-control select2">
                                    @if(auth()->user()->sr_id)
                                        @foreach($srs as $sr)
                                            @if(auth()->user()->sr_id == $sr->id)
                                                <option value="{{ $sr->id }}" selected>{{ $sr->name }}</option>
                                            @endif
                                        @endforeach
                                    @else
                                        <option value="">All SR</option>
                                        @foreach($srs as $sr)
                                            <option value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
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
                        
                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date', auth()->user()->business_date ? \Carbon\Carbon::parse(auth()->user()->business_date)->format('d-m-Y') : '') }}"
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
                                <th class="text-center">SR</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Discount</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Due</th>
                                <th class="text-center">Notes</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tfoot>
                            <tr>
                                <th colspan="4" class="text-center">Total</th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th class="text-right"></th>
                                <th colspan="2"></th>
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
                    <h4 class="modal-title">Customer Payment Receive</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modal-customer-payment-form" method="POST" action="{{ route('sales.customer_payment_receive') }}">
                        @csrf
                        <input type="hidden" id="order_id" name="order_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="order_no" class="text-dark">Order No. <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="order_no" name="order_no" class="form-control">
                                    <span id="order_no-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="date" class="text-dark">Date <span class="text-danger">*</span></label>
                                    <input type="text" readonly id="date" name="date" value="{{ date('d-m-Y') }}" class="form-control date-picker">
                                    <span id="date-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="payment_mode" class="text-dark">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-control select2">
                                        <option value="">Select Payment Mode</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select>
                                    <span id="payment_mode-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="total" class="text-dark">Total</label>
                                    <input type="text" readonly id="total" class="form-control" name="total">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <label for="paid" class="text-dark">Paid <span class="text-danger">*</span></label>
                                    <input type="number" step="any"  id="paid" class="form-control" name="paid">
                                    <span id="paid-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group form-group-has-error">
                                    <input type="hidden" id="due_hidden" name="due_hidden">
                                    <label for="due" class="text-dark">Due</label>
                                    <input type="text" readonly id="due" class="form-control" name="due">
                                    <span id="due-error" class="help-block error-message"></span>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group form-group-has-error">
                                    <label for="notes" class="text-dark">Notes</label>
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
                            url: "{{ route('sales.destroy', ['sale' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
                    url: "{{ route('sales.datatable') }}",
                    data: function (d) {
                        d.customer = $("#customer").val()
                        d.sr = $("#sr").val()
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
                    {data: 'sr_name', name: 'sr.name'},
                    {data: 'customer_name', name: 'customer.name'},
                    {
                        data: 'total',
                        name: 'total',
                        render: function (data, type, row) {
                            if (row.total > 0) {
                                var order_id = row.id; // Assuming the type is in the 'voucher_type' field of your data
                                var voucherRoute = getJvVoucherRoute(order_id);
                                return '<a href="' + voucherRoute + '">' + jsNumberFormat(parseFloat(row.total).toFixed(2)) + '</a>';
                            } else {
                                return jsNumberFormat(parseFloat(row.total).toFixed(2));
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
                "autoWidth": false,"colReorder": true,
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
                    var pageTotalTotal = api.column(4, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    var pageTotalDiscount = api.column(5, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    var pageTotalPaid = api.column(6, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);
                    var pageTotalDue = api.column(7, { page: 'current' }).data().reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                    // Update footer
                    $(api.column(4).footer()).html(jsNumberFormat(pageTotalTotal.toFixed(2)));
                    $(api.column(5).footer()).html(jsNumberFormat(pageTotalDiscount.toFixed(2)));
                    $(api.column(6).footer()).html(jsNumberFormat(pageTotalPaid.toFixed(2)));
                    $(api.column(7).footer()).html(jsNumberFormat(pageTotalDue.toFixed(2)));
                }
            });
            $('#start_date,#end_date,#search_btn,#customer,#sr').change(function () {
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
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$COLLECTION_VOUCHER,'sale_order_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
        function getJvVoucherRoute(order_id) {
            return "{{ route('voucher.index', ['voucher_type'=>\App\Enumeration\VoucherType::$JOURNAL_VOUCHER,'sale_order_id'=>'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', order_id);
        }
    </script>
@endsection
