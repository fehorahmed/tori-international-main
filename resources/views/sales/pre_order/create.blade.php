@extends('layouts.app')
@section('title','Pre-Order Create')
@section('style')
    <style>
        .table td, .table th {
            padding: 6px;
            vertical-align: middle;
        }

    </style>
@endsection
@section('content')

    <!-- form start -->
    <form enctype="multipart/form-data"
          action="{{ route('pre-orders.store') }}"
          class="form-horizontal" id="sales-form" method="post">
        @csrf
        <input type="hidden" name="is_pos" value="0">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Product Information (Pre-Order) </h3>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="select_product">Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_product" name="select_product">
                                        <option value="">Search Name of Product</option>
                                        @foreach($products as $product)
                                            <option
                                                {{ old('product') == $product->id ? 'selected' : ''  }} value="{{ $product->id }}" data-product_code="{{ $product->code }}" data-price="{{ $product->selling_price }}">
                                                {{ $product->name }}-
                                                @if($product->brand)
                                                    Brand: {{ $product->brand->name ?? '' }} -
                                                @endif
                                                @if($product->productCategory)
                                                    Category: {{ $product->productCategory->name ?? '' }} -
                                                @endif
                                                {{ $product->unit->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="add_quantity">Qty <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_quantity"
                                           placeholder="Qty">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="add_unit_price">Unit Price <span
                                             class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price"
                                           placeholder="Unit Price">
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <button type="button" style="margin-top: 31px;" id="add_new_btn"
                                            class="btn btn-info bg-gradient-info btn-sm btn-block"><i
                                            class="fa fa-plus"></i> Add Item</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th class="text-center" width="5%">S/L</th>
                                        <th class="text-center">Item Details</th>
                                        <th class="text-center" width="15%">Qty</th>
                                        <th class="text-center" width="15%">Unit Price</th>
                                        <th class="text-center" width="15%">Total Price</th>
                                        <th class="text-center" width="5%"></th>
                                    </tr> 
                                    </thead>
                                    <tbody id="product-container">
                                    @if(old('product_id'))
                                        @foreach(old('product_id') as $key => $productId)
                                            <tr class="product-item">
                                                <td class="text-center">
                                                    <span class="product_sl">{{ $loop->iteration }}</span>
                                                </td>
                                                <td class="text-left">
                                                    <span class="product_name">{{ old('product_name_val')[$key] }}</span>
                                                    <input type="hidden" name="product_name_val[]" class="product_name_val" value="{{ old('product_name_val')[$key] }}">
                                                    <input type="hidden" name="product_id[]" class="product_id" value="{{ $productId }}">
                                                    <input type="hidden" name="product_code[]" class="product_code" value="{{ old('product_code')[$key] }}">
                                                </td>
                                                <td class="text-right">
                                                    <div class="form-group mb-0">
                                                        <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]" value="{{ old('product_quantity')[$key] }}">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div class="form-group mb-0">
                                                        <input type="number" step="any" class="form-control text-right product_unit_price"
                                                               name="product_unit_price[]" value="{{ old('product_unit_price')[$key] }}">
                                                    </div>
                                                </td>
                                                <td class="text-right total-sale-cost">{{ number_format(old('product_quantity')[$key] * old('product_unit_price')[$key], 2) }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i
                                                            class="fa fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr class="footer_area">
                                        <th colspan="2"></th>
                                        <th class="text-right" id="total_quantity"></th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right">
                                            <div class="form-group">
                                                <div
                                                    class="form-group mb-0  {{ $errors->has('sub_total') ? 'has-error' :'' }}">
                                                    <input type="number" step="any" readonly name="sub_total" id="sub_total"
                                                           class="form-control text-right" value="{{ old('sub_total', 0) }}">
                                                </div>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">
                                            <div class="d-flex justify-content-end align-items-center">
                                                <span class="mr-2 font-weight-bold">Discount</span>
                                                <select name="discount_type" id="discount_type" class="form-control form-control-sm" style="width: 150px; display: inline-block;">
                                                    <option value="1" {{ old('discount_type', '1') == '1' ? 'selected' : '' }}>Flat (৳)</option>
                                                    <option value="2" {{ old('discount_type') == '2' ? 'selected' : '' }}>Percentage (%)</option>
                                                </select>
                                            </div>
                                        </th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('discount_percent') ? 'has-error' :'' }}">
                                                <input type="number" value="{{ old('discount_percent') }}" step="any"
                                                       name="discount_percent"
                                                       id="discount_percent" class="form-control text-right">
                                                <input type="hidden" name="discount_amount" id="discount_amount" value="{{ old('discount_amount') }}">
                                                <span id="total_discount" class="text-muted small"></span>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <input type="hidden" id="vat_amount" name="vat_amount">
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Total Receivable</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('total_receivable') ? 'has-error' :'' }}">
                                                <input type="number" readonly step="any" id="total_receivable"
                                                       name="total_receivable" class="form-control text-right" value="{{ old('total_receivable', 0) }}">
                                                <input type="hidden" name="total_amount" id="total_amount">       
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>

                                     <tr class="footer_area" id="advance_row">
                                        <th colspan="4" class="text-right text-success">Current Advance Balance</th>
                                        <th class="text-right">
                                            <div class="form-group mb-0">
                                                <input type="text" readonly id="advance_amount_text_field" class="form-control text-right text-success font-weight-bold" value="0.00">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">New Advance Payment (Cash/Bank)</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('paid_amount') ? 'has-error' :'' }}">
                                                <input type="number" step="any" id="paid_amount"
                                                       name="paid_amount" value="{{ old('paid_amount', 0) }}" class="form-control text-right">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <!-- Other Information Section inside the same card -->
                        <hr style="border-top: 2px solid #ddd; margin: 30px 0 20px 0;">
                        <h4 class="mb-4 text-info"><i class="fa fa-info-circle"></i> Other Information</h4>
                        <div class="row"> 
                            <input type="hidden" id="warehouse" name="warehouse" value="1">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off"
                                           name="date" id="date" class="form-control business-date-picker"
                                           placeholder="Enter date" value="{{ old('date') }}" readonly>
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                @php
                                    $isSr = auth()->user()->employee && auth()->user()->employee->isSR();
                                    $loggedSrId = $isSr ? auth()->user()->employee_id : null;
                                @endphp
                                <div class="form-group {{ $errors->has('sr') ? 'has-error' :'' }}">
                                    <label for="sr">SR <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="sr" name="sr" {{ $loggedSrId ? 'disabled' : '' }}>
                                        <option value="">Select SR</option>
                                        @foreach($srs as $sr)
                                            <option {{ old('sr', $loggedSrId) == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($loggedSrId)
                                        <input type="hidden" name="sr" value="{{ $loggedSrId }}">
                                    @endif
                                    @error('sr')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <select class="form-control select2" name="payment_method" id="payment_method">
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option {{ old('payment_method') == $paymentMode->id ? 'selected' : '' }} value="{{ $paymentMode->id }}">{{ $paymentMode->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Client Type</label>
                                    <select class="form-control select2" id="customer_type" name="customer_type">
                                        <option value="Customer">General Customer</option>
                                        <option value="Corporate_Customer">Corporate Customer</option>
                                        <option value="Dealer">Dealer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group {{ $errors->has('customer') ? 'has-error' :'' }}">
                                    <label for="customer">Customer <span class="text-danger">*</span> 
                                        <!-- <button type="button" id="add-new-customer" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-plus"></i></button> -->
                                    </label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option value="">Search Name of Customer</option>
                                        @if($selectedCustomer)
                                            <option value="{{ $selectedCustomer->id }}" selected>{{ $selectedCustomer->name }}</option>
                                        @endif
                                    </select>
                                    <div id="advance-alert-container" class="mt-2"></div>
                                    @error('customer')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('rm_no') ? 'has-error' :'' }}">
                                    <label for="rm_no">RM No. (if paid)</label>
                                    <input type="text" value="{{ old('rm_no') }}" name="rm_no" id="rm_no"
                                           class="form-control" placeholder="Enter RM No.">
                                    @error('rm_no')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes</label>
                                    <input type="text" value="{{ old('notes') }}" name="notes" id="notes"
                                           class="form-control" placeholder="Enter Notes">
                                    @error('notes')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="btn-save-sale" class="btn btn-info bg-gradient-info btn-sm">Save Pre-Order</button>
                        <a href="{{ route('pre-orders.index') }}"
                           class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
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
                            <input type="text" id="customer_name" placeholder="Enter Customer Name" class="form-control" name="customer_name">
                            <span class="text-danger error-message" id="customer_name-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="mobile_no">Mobile No.</label>
                            <input type="text" id="mobile_no" placeholder="Enter Mobile No." class="form-control" name="mobile_no">
                            <span class="text-danger error-message" id="mobile_no-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" placeholder="Enter Address" class="form-control" name="address">
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

    <template id="template-product">
        <tr class="product-item">
            <td class="text-center">
                <span class="product_sl"></span>
            </td>
            <td class="text-left">
                <span class="product_name"></span>
                <input type="hidden" name="product_name_val[]" class="product_name_val">
                <input type="hidden" name="product_id[]" class="product_id">
                <input type="hidden" name="product_code[]" class="product_code">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_unit_price"
                           name="product_unit_price[]">
                </div>
            </td>
            <td class="text-right total-sale-cost"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i
                        class="fa fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>
    <!-- Sale Overview Modal -->
    <div class="modal fade" id="modal-sale-overview" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fa fa-shopping-cart"></i> Sale Order Summary & Overview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Customer, SR, Date, Payment Method & Note Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px;">
                        <div class="col-md-4 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Customer Name:</strong>
                                <span id="ov-customer" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-user-tie text-info mr-1"></i> SR Name:</strong>
                                <span id="ov-sr" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-4 border-right pl-md-3">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-calendar-alt text-info mr-1"></i> Date:</strong>
                                <span id="ov-date" class="text-dark ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-credit-card text-info mr-1"></i> Payment Method:</strong>
                                <span id="ov-payment-method" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-4 pl-md-3">
                            <strong><i class="fa fa-sticky-note text-info mr-1"></i> Notes:</strong>
                            <div id="ov-notes" class="text-dark mt-1 p-2 bg-white rounded border" style="min-height: 50px; font-style: italic; font-size: 13px;">
                                -
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-bordered text-center">
                            <thead>
                                <tr class="bg-light">
                                    <th>#</th>
                                    <th>Product Name</th>
                                    <th>Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody id="overview-items-body">
                                <!-- Dynamic Rows Go Here -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Summary -->
                    <div class="row mt-4">
                        <div class="col-md-6 offset-md-6">
                            <table class="table table-sm table-bordered">
                                <tr>
                                    <th class="bg-light text-right">Sub Total:</th>
                                    <td class="text-right font-weight-bold" id="ov-sub-total">0.00</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-right">Discount:</th>
                                    <td class="text-right text-danger font-weight-bold" id="ov-discount">0.00</td>
                                </tr>
                                <tr class="table-success">
                                    <th class="text-right">Receivable Amount:</th>
                                    <td class="text-right font-weight-bold" id="ov-receivable" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-info" id="ov-advance-row">
                                    <th class="text-right">Current Advance Balance:</th>
                                    <td class="text-right font-weight-bold" id="ov-advance" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-primary">
                                    <th class="text-right">New Advance Payment (Cash/Bank):</th>
                                    <td class="text-right font-weight-bold" id="ov-paid" style="font-size: 16px;">0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Order</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-submit"><i class="fa fa-check-circle"></i> Confirm & Approve</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            calculate();

            $('#customer_type').change(function(){
                $('#customer').val('').trigger('change');
                $('#advance-alert-container').empty();
            });

            $('#customer').select2({
                ajax: {
                    url: "{{ route('party_json') }}",
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            type: [$('#customer_type').val()],
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
                let customerId = e.params.data.id;
                $('#advance-alert-container').empty();
                $('#btn-save-sale').prop('disabled', false);

                $.ajax({
                    url: "{{ route('customer_validation_check') }}",
                    type: "GET",
                    data: { customer_id: customerId },
                    success: function (response) {
                        if (response.current_balance > 0) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Previous Due Found',
                                text: 'This client has previous dues. You cannot generate a new order until it is paid.',
                                showCancelButton: true,
                                confirmButtonText: 'Go to Payment',
                                cancelButtonText: 'Cancel',
                                allowOutsideClick: false
                            }).then((result) => {
                                if (result.isConfirmed) {
                                    window.location.href = "{{ route('customer-payment.index') }}?customer_id=" + customerId;
                                } else {
                                    $('#customer').val('').trigger('change');
                                    $('#btn-save-sale').prop('disabled', true);
                                }
                            });
                        } else if (response.has_pending_preorder) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Unresolved Pre-Order',
                                text: 'This client has an unresolved Pre-Order. Please deliver or cancel it first.',
                                allowOutsideClick: false
                            });
                            $('#btn-save-sale').prop('disabled', true);
                            $('#customer').val('').trigger('change');
                        } else if (response.current_balance < 0) {
                            let advanceAmount = Math.abs(response.current_balance);
                            window.availableAdvance = advanceAmount;
                            $('#advance_amount_text_field').val(parseFloat(advanceAmount).toFixed(2));
                            calculate();
                        } else {
                            window.availableAdvance = 0;
                            $('#advance_amount_text_field').val('0.00');
                            calculate();
                        }
                    }
                });
            });

            $("#add-new-customer").click(function (){
                preloaderToggle(true);
                $("#modal-customer").modal('show');
                preloaderToggle(false);
            })
            $("#btn-save-customer").click(function (){
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

                            ajaxSuccessMessage(response.message)
                        } else {
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function (xhr) {
                        preloaderToggle(false);
                        if (xhr.status === 422) {
                            ajaxWarningMessage('Please fill up validate required fields.');
                            let errors = xhr.responseJSON.errors;
                            $('.error-message').text('');
                            $.each(errors, function (field, errorMessage) {
                                $('#' + field + '-error').text(errorMessage[0]);
                            });
                        }
                    }
                })
            })

            $('body').on('change', '#select_product', function (e) {
                let price = $(this).find(':selected').data('price');
                $('#add_unit_price').val(price);
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectProduct = $('#select_product').val();
                let selectProductName = $("#select_product option:selected").text();
                let addQuantity = $('#add_quantity').val();
                let addUnitPrice = $('#add_unit_price').val();
                let productCode = $("#select_product option:selected").data('product_code');

                if (selectProduct == '') {
                    ajaxErrorMessage('Please, select product !');
                    return false;
                }
                if (addQuantity == '') {
                    ajaxErrorMessage('Please, type product quantity !');
                    return false;
                }
                if (addUnitPrice == '') {
                    ajaxErrorMessage('Please, type product unit price !');
                    return false;
                }

                if (selectProduct != '' && addQuantity != '' && addUnitPrice != '') {
                    // Check if already exists
                    let exists = false;
                    $('.product_id').each(function() {
                        if ($(this).val() == selectProduct) {
                            exists = true;
                            return false;
                        }
                    });

                    if (exists) {
                        ajaxErrorMessage('Product already added!');
                        return false;
                    }

                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.hide();
                    item.closest('tr').find('.product_name').text($("#select_product option:selected").text());
                    item.closest('tr').find('.product_name_val').val($("#select_product option:selected").text());
                    item.closest('tr').find('.product_id').val($("#select_product option:selected").val());
                    item.closest('tr').find('.product_code').val(productCode);
                    item.closest('tr').find('.product_quantity').val(addQuantity);
                    item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                    item.show();
                    calculate();
                    $('#select_product').val('').change();
                    $('#add_quantity').val('');
                    $('#add_unit_price').val('');
                }
                
                return false; 
            });

            $('body').on('click', '.btn-remove', function () {
                $(this).closest('.product-item').remove();
                calculate();
            });
            $('body').on('keyup change', 'input[type="number"]', function () {
                calculate();
            });

            $('body').on('change', '#discount_type', function () {
                calculate();
            });

            $('#advance_adjust').change(function() {
                if ($(this).is(':checked')) {
                    $('#payment_method').val('').trigger('change');
                    $('#payment_method').prop('disabled', true);
                } else {
                    $('#payment_method').prop('disabled', false);
                }
                calculate();
            });
        })

        function calculate() {
            var productSl = 1;
            var rows = $("#product-container .product-item");
            rows.each(function () {
                var productSlElement = $(this).find('.product_sl');
                productSlElement.text(productSl);
                productSl++;
            });

            var total_quantity = 0;
            var sub_total = 0;
            var paid_amount = parseFloat($('#paid_amount').val()) || 0;
            $('.product-item').each(function (i, obj) {
                let product_quantity = parseFloat($(this).find('.product_quantity').val()) || 0;
                let product_unit_price = parseFloat($(this).find('.product_unit_price').val()) || 0;
                let total = product_quantity * product_unit_price;
                $(this).find('.total-sale-cost').text(total.toFixed(2));
                total_quantity += product_quantity;
                sub_total += total;
            });

            $('#total_quantity').text(total_quantity);
            $('#sub_total').val(sub_total.toFixed(2));

            let discountType = $('#discount_type').val() || '1';
            let discountInput = parseFloat($('#discount_percent').val()) || 0;
            let discountAmount = 0;

            if (discountType == '2') {
                // Percentage (%)
                discountAmount = (sub_total / 100) * discountInput;
                $('#total_discount').text('(Amt: ' + discountAmount.toFixed(2) + ' ৳)');
            } else {
                // Flat BDT
                discountAmount = discountInput;
                $('#total_discount').text('');
            }

            let total_receivable = sub_total - discountAmount;

            $('#discount_amount').val(discountAmount);
            $('#total_receivable').val(total_receivable.toFixed(2));
            $('#total_amount').val(total_receivable.toFixed(2));
            // Auto Adjust Advance - Removed for Pre Orders as requested

            paid_amount = parseFloat($('#paid_amount').val());
            paid_amount = (isNaN(paid_amount) || paid_amount < 0) ? 0 : paid_amount;

            if (rows.length > 0) {
                $(".footer_area").show();
            } else {
                $(".footer_area").hide();
            }
            // Trigger Overview Modal on Save
            $('#btn-save-sale').click(function(e) {
                e.preventDefault();

                // Validate form first
                let form = document.getElementById('sales-form');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Check if at least one product added
                if ($('.product-item').length === 0) {
                    alert('Please add at least one product.');
                    return;
                }

                // Clear previous rows
                $('#overview-items-body').empty();

                // Populate product table in modal
                let sl = 1;
                $('.product-item').each(function() {
                    let name = $(this).find('.product_name_val').val() || $(this).find('.product_name').text();
                    let qty = parseFloat($(this).find('.product_quantity').val() || 0);
                    let price = parseFloat($(this).find('.product_unit_price').val() || 0);
                    let rowTotal = qty * price;
                    
                    let html = `
                        <tr>
                            <td>${sl++}</td>
                            <td class="text-left">${name}</td>
                            <td>${qty}</td>
                            <td class="text-right">${price.toFixed(2)}</td>
                            <td class="text-right">${rowTotal.toFixed(2)}</td>
                        </tr>
                    `;
                    $('#overview-items-body').append(html);
                });

                // Populate Metadata
                let customerText = $('#customer option:selected').val() ? $('#customer option:selected').text().trim() : 'N/A';
                let srText = $('#sr option:selected').val() ? $('#sr option:selected').text().trim() : 'N/A';
                let dateText = $('#date').val() || 'N/A';
                let paymentMethodText = $('#payment_method option:selected').val() ? $('#payment_method option:selected').text().trim() : 'N/A';
                let notesText = $('#notes').val() ? $('#notes').val().trim() : 'No notes added';

                $('#ov-customer').text(customerText);
                $('#ov-sr').text(srText);
                $('#ov-date').text(dateText);
                $('#ov-payment-method').text(paymentMethodText);
                $('#ov-notes').text(notesText);

                // Financials
                let subTotal = parseFloat($('#sub_total').val() || 0);
                $('#ov-sub-total').text(subTotal.toFixed(2));
                
                let discountType = $('#discount_type').val() || '2';
                let discountInput = parseFloat($('#discount_percent').val()) || 0;
                let discountText = '';
                let discountAmount = parseFloat($('#discount_amount').val() || 0);
                if (discountType === '2') {
                    discountText = discountAmount.toFixed(2) + ' (' + discountInput.toFixed(2) + '%)';
                } else {
                    discountText = discountAmount.toFixed(2) + ' (Flat)';
                }
                $('#ov-discount').text(discountText);

                $('#ov-receivable').text(parseFloat($('#total_receivable').val() || 0).toFixed(2));

                let advanceAdjusted = parseFloat($('#advance_amount_text_field').val() || 0);
                $('#ov-advance-row').show();
                $('#ov-advance').text(advanceAdjusted.toFixed(2));

                $('#ov-paid').text(parseFloat($('#paid_amount').val() || 0).toFixed(2));

                // Show modal
                $('#modal-sale-overview').modal('show');
            });

            // Confirm & submit form
            $('#btn-confirm-submit').click(function() {
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $('#sales-form').submit();
            });
        }
    </script>
@endsection
