@extends('layouts.app')
@section('title','Pre-Order Edit')
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
          action="{{ route('pre-orders.update', ['pre_order' => $sale->id]) }}"
          class="form-horizontal" id="sales-form" method="post">
        @csrf
        @method('PUT')
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
                                            <option value="{{ $product->id }}" data-product_code="{{ $product->code }}" data-price="{{ $product->selling_price }}">
                                                {{ $product->name }}
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
                                                    <input type="hidden" name="item_id[]" value="{{ old('item_id')[$key] ?? '' }}">
                                                    <input type="hidden" name="product_name_val[]" class="product_name_val" value="{{ old('product_name_val')[$key] }}">
                                                    <input type="hidden" name="product_id[]" class="product_id" value="{{ $productId }}">
                                                    <input type="hidden" name="product_code[]" class="product_code" value="{{ old('product_code')[$key] }}">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" value="{{ old('product_quantity')[$key] }}" class="form-control text-right product_quantity" name="product_quantity[]">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" value="{{ old('product_unit_price')[$key] }}" class="form-control text-right product_unit_price" name="product_unit_price[]">
                                                </td>
                                                <td class="text-right total-sale-cost">{{ number_format(old('product_quantity')[$key] * old('product_unit_price')[$key], 2) }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @foreach($sale->saleOrderItems as $item)
                                            <tr class="product-item">
                                                <td class="text-center">
                                                    <span class="product_sl">{{ $loop->iteration }}</span>
                                                </td>
                                                <td class="text-left">
                                                    <span class="product_name">{{ $item->product->name }}</span>
                                                    <input type="hidden" name="item_id[]" value="{{ $item->id }}">
                                                    <input type="hidden" name="product_name_val[]" class="product_name_val" value="{{ $item->product->name }}">
                                                    <input type="hidden" name="product_id[]" value="{{ $item->product_id }}" class="product_id">
                                                    <input type="hidden" name="product_code[]" value="{{ $item->product_code }}" class="product_code">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" value="{{ $item->quantity }}" class="form-control text-right product_quantity" name="product_quantity[]">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" value="{{ $item->selling_price }}" class="form-control text-right product_unit_price" name="product_unit_price[]">
                                                </td>
                                                <td class="text-right total-sale-cost">{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr class="footer_area">
                                        <th colspan="2"></th>
                                        <th class="text-right" id="total_quantity">{{ $sale->saleOrderItems->sum('quantity') }}</th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right">
                                            <input type="number" step="any" readonly name="sub_total" id="sub_total" value="{{ old('sub_total', $sale->sub_total) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                     <tr class="footer_area">
                                         <th colspan="4" class="text-right">
                                             <div class="d-flex justify-content-end align-items-center">
                                                 <span class="mr-2 font-weight-bold">Discount</span>
                                                 <select name="discount_type" id="discount_type" class="form-control form-control-sm" style="width: 150px; display: inline-block;">
                                                     <option value="2" {{ old('discount_type', $sale->discount_type ?? '2') == '2' ? 'selected' : '' }}>Percentage (%)</option>
                                                     <option value="1" {{ old('discount_type', $sale->discount_type ?? '2') == '1' ? 'selected' : '' }}>Flat (টাকা)</option>
                                                 </select>
                                             </div>
                                         </th>
                                         <th class="text-right">
                                             <input type="number" value="{{ old('discount_percent', $sale->discount_percent) }}" step="any" name="discount_percent" id="discount_percent" class="form-control text-right">
                                             <input type="hidden" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $sale->discount) }}">
                                             <span id="total_discount" class="text-muted small"></span>
                                         </th>
                                         <th></th>
                                     </tr>
                                    <input type="hidden" id="vat_amount" name="vat_amount" value="{{ $sale->vat_amount }}">
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Total Receivable</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="total_receivable" name="total_receivable" value="{{ old('total_receivable', $sale->total) }}" class="form-control text-right">
                                            <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_receivable', $sale->total) }}">       
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Paid</th>
                                        <th class="text-right">
                                            <input type="number" step="any" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $sale->paid) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Due</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="due_amount" name="due_amount" value="{{ old('due_amount', $sale->due) }}" class="form-control text-right">
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
                            <input type="hidden" id="warehouse" name="warehouse" value="{{ $sale->warehouse_id }}">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}"
                                           name="date" id="date" class="form-control date-picker">
                                </div>
                            </div>
                            <div class="col-md-3">
                                @php
                                    $isSr = auth()->user()->employee && auth()->user()->employee->isSR();
                                    $loggedSrId = $isSr ? auth()->user()->employee_id : null;
                                @endphp
                                <div class="form-group {{ $errors->has('sr') ? 'has-error' :'' }}">
                                    <label for="sr">SR <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="sr" name="sr" {{ $loggedSrId ? 'disabled' : '' }}>
                                        <option value="">Select SR</option>
                                        @foreach($srs as $sr)
                                            <option {{ old('sr', $sale->sr_id) == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($loggedSrId)
                                        <input type="hidden" name="sr" value="{{ $loggedSrId }}">
                                    @endif
                                    @error('sr')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customer">Customer <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option value="{{ $sale->customer_id }}" selected>{{ $sale->customer->name }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method</label>
                                    <select class="form-control select2" name="payment_method" id="payment_method">
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option {{ (old('payment_method', $selectedPaymentMethod) == $paymentMode->id) ? 'selected' : '' }} value="{{ $paymentMode->id }}">{{ $paymentMode->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div id="advance_adjust_container" style="display: none; margin-top: 10px;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="advance_adjust" name="advance_adjust" value="1" {{ old('advance_adjust') ? 'checked' : '' }}>
                                        <label class="custom-control-label text-success" for="advance_adjust">Pay from Advance</label>
                                    </div>
                                    <input type="hidden" id="available_advance_amount" value="0">
                                </div>
                                <div id="advance-alert-container" class="mt-2"></div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('rm_no') ? 'has-error' :'' }}">
                                    <label for="rm_no">RM No. (if paid)</label>
                                    <input type="text" value="{{ old('rm_no', $oldPaymentTransaction->voucher->rm_no ?? '') }}" name="rm_no" id="rm_no"
                                           class="form-control" placeholder="Enter RM No.">
                                    @error('rm_no')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <input type="text" value="{{ $sale->notes }}" name="notes" id="notes" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="btn-save-sale" class="btn btn-info bg-gradient-info btn-sm">Update Pre-Order</button>
                        <a href="{{ route('pre-orders.index') }}"
                           class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template id="template-product">
        <tr class="product-item">
            <td class="text-center">
                <span class="product_sl"></span>
            </td>
            <td class="text-left">
                <span class="product_name"></span>
                <input type="hidden" name="item_id[]" value="">
                <input type="hidden" name="product_id[]" class="product_id">
                <input type="hidden" name="product_code[]" class="product_code">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]">
            </td>
            <td class="text-right total-sale-cost"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
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
                                <tr class="table-primary">
                                    <th class="text-right">Paid Amount:</th>
                                    <td class="text-right font-weight-bold" id="ov-paid" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-danger">
                                    <th class="text-right">Due Amount:</th>
                                    <td class="text-right font-weight-bold" id="ov-due" style="font-size: 16px;">0.00</td>
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
            
            $('#customer').select2({
                ajax: {
                    url: "{{ route('party_json') }}",
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { type:['Customer'], searchTerm: params.term };
                    },
                    processResults: function (response) {
                        return { results: response };
                    },
                    cache: true
                }
            });

            function checkCustomerBalance(customerId) {
                $('#advance-alert-container').empty();
                $.ajax({
                    url: "{{ route('customer_validation_check') }}",
                    type: "GET",
                    data: { customer_id: customerId },
                    success: function (response) {
                        if (response.current_balance < 0) {
                            let advanceAmount = Math.abs(response.current_balance);
                            $('#advance-alert-container').html('<span class="badge badge-success" style="font-size:14px;"><i class="fa fa-money-bill-wave"></i> Available Advance: ' + advanceAmount.toFixed(2) + ' BDT</span>');
                            $('#available_advance_amount').val(advanceAmount);
                            $('#advance_adjust_container').slideDown();
                        } else {
                            $('#advance_adjust_container').slideUp();
                            $('#available_advance_amount').val(0);
                            $('#advance_adjust').prop('checked', false);
                        }
                    }
                });
            }

            // Check initial customer on edit page
            let initialCustomer = $('#customer').val();
            if (initialCustomer) {
                checkCustomerBalance(initialCustomer);
            }

            $('#customer').on('select2:select', function (e) {
                checkCustomerBalance(e.params.data.id);
            });

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
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Product already added!'
                        });
                        return false;
                    }

                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.find('.product_name').text($("#select_product option:selected").text());
                    item.find('.product_id').val($("#select_product option:selected").val());
                    item.find('.product_code').val(productCode);
                    item.find('.product_quantity').val(addQuantity);
                    item.find('.product_unit_price').val(addUnitPrice);
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
                $(this).find('.product_sl').text(productSl++);
            });

            var total_quantity = 0;
            var sub_total = 0;
            var paid_amount = parseFloat($('#paid_amount').val()) || 0;
            $('.product-item').each(function () {
                let qty = parseFloat($(this).find('.product_quantity').val()) || 0;
                let price = parseFloat($(this).find('.product_unit_price').val()) || 0;
                let total = qty * price;
                $(this).find('.total-sale-cost').text(total.toFixed(2));
                total_quantity += qty;
                sub_total += total;
            });

            $('#total_quantity').text(total_quantity);
            $('#sub_total').val(sub_total.toFixed(2));

            let discountType = $('#discount_type').val() || '2';
            let discountInput = parseFloat($('#discount_percent').val()) || 0;
            let discountAmount = 0;

            if (discountType == '2') {
                // Percentage (%)
                discountAmount = (sub_total / 100) * discountInput;
                $('#total_discount').text('(Amt: ' + discountAmount.toFixed(2) + ' BDT)');
            } else {
                // Flat BDT
                discountAmount = discountInput;
                $('#total_discount').text('');
            }

            let total_receivable = sub_total - discountAmount;

            $('#discount_amount').val(discountAmount);
            $('#total_receivable').val(total_receivable.toFixed(2));
            $('#total_amount').val(total_receivable.toFixed(2));
            let due_amount = total_receivable - paid_amount;
            $('#due_amount').val(due_amount.toFixed(2));

            // Validate against advance
            if ($('#advance_adjust').is(':checked')) {
                let availAdvance = parseFloat($('#available_advance_amount').val()) || 0;
                if (paid_amount > availAdvance) {
                    Swal.fire('Error', 'Paid amount cannot exceed available advance (' + availAdvance.toFixed(2) + ' BDT)!', 'error');
                    $('#paid_amount').val(availAdvance);
                    calculate();
                    return;
                }
                $('#payment_method').val('').trigger('change');
                $('#payment_method').prop('disabled', true);
            } else {
                $('#payment_method').prop('disabled', false);
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
                $('#ov-paid').text(parseFloat($('#paid_amount').val() || 0).toFixed(2));
                $('#ov-due').text(parseFloat($('#due_amount').val() || 0).toFixed(2));

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
