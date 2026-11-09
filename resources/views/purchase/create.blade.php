@extends('layouts.app')
@section('title','Purchase Create')
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
          action="{{ route('purchase.store') }}"
          class="form-horizontal" method="post" id="purchase-form">
        @csrf
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Product Information </h3>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('supplier') ? 'has-error' :'' }}">
                                    <label for="supplier">Supplier <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control select2" id="supplier" name="supplier">
                                        <option value="">Search Name of Supplier</option>
                                        @foreach($suppliers as $supplier)
                                            <option
                                                {{ old('supplier') == $supplier->id ? 'selected' : ''  }} value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('supplier')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="select_product">Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_product" name="select_product">
                                        <option value="">Search Name or Code of Product</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="add_quantity">Qty <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_quantity"
                                           placeholder="Qty">
                                </div>
                            </div>
                            <div class="col-5">
                                <div class="form-group">
                                    <label for="add_unit_price">Unit Price <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price"
                                           placeholder="Unit Price">
                                    <small>Hint: <span id="add_unit_price_hint"></span></small>       
                                </div>
                            </div>
                            <div class="col-2">
                                <div class="form-group">
                                    <button type="button" style="margin-top: 31px;" id="add_new_btn"
                                            class="btn btn-info bg-gradient-info btn-sm btn-block"><i
                                            class="fa fa-plus"></i></button>
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
                                        <th class="text-center" width="10%">Qty</th>
                                        <th class="text-center" width="15%">Unit Price</th>
                                        <th class="text-center" width="20%">Total Price</th>
                                        <th class="text-center" width="5%"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="product-container">
                                    @if (old('product_id') != null && sizeof(old('product_id')) > 0)
                                        @foreach(old('product_id') as $key => $item)
                                            <tr class="product-item">
                                                <td class="text-center">
                                                    <span class="product_sl">{{ ++$key }}</span>
                                                </td>
                                                <td class="text-left {{ $errors->has('product_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                    <span
                                                        class="product_name">{{ old('product_name_val.'.$loop->index) }}</span>
                                                    <input type="hidden" name="product_name_val[]"
                                                           value="{{ old('product_name_val.'.$loop->index) }}"
                                                           class="product_name_val">
                                                    <input type="hidden" name="product_id[]"
                                                           value="{{ old('product_id.'.$loop->index) }}"
                                                           class="product_id">

                                                </td>
                                                <td class="text-right">
                                                    <div
                                                        class="form-group mb-0  {{ $errors->has('product_qty.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any"
                                                               value="{{ old('product_qty.'.$loop->index) }}"
                                                               class="form-control text-right product_qty"
                                                               name="product_qty[]">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div
                                                        class="form-group mb-0  {{ $errors->has('product_unit_price.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any"
                                                               value="{{ old('product_unit_price.'.$loop->index) }}"
                                                               class="form-control text-right product_unit_price"
                                                               name="product_unit_price[]">
                                                    </div>
                                                </td>
                                                <td class="text-right total-purchase-cost"></td>
                                                <td class="text-center">
                                                    <button type="button"
                                                            class="btn btn-danger bg-gradient-danger btn-sm btn-remove">
                                                        <i class="fa fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr class="footer_area">
                                        <th colspan="2"></th>
                                        <th class="text-center" id="total_quantity"></th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right">
                                            <div class="form-group">
                                                <div
                                                    class="form-group mb-0  {{ $errors->has('sub_total') ? 'has-error' :'' }}">
                                                    <input type="number" step="any" readonly name="sub_total" id="sub_total"
                                                           class="form-control text-right">
                                                </div>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right align-middle">
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
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Total Payable</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('total_payable') ? 'has-error' :'' }}">
                                                <input type="number" readonly step="any" id="total_payable"
                                                       name="total_payable" class="form-control text-right">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area" id="advance_row" style="display: none;">
                                        <th colspan="4" class="text-right text-success">Adjust From Advance (Bal: <span id="advance_amount_text">0.00</span>)</th>
                                        <th class="text-right">
                                            <div class="form-group mb-0">
                                                <input type="number" readonly step="any" id="advance_amount" name="advance_amount" class="form-control text-right text-success font-weight-bold" value="0">
                                                <input type="hidden" name="advance_adjust" id="advance_adjust" value="0">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Paid</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('paid_amount') ? 'has-error' :'' }}">
                                                <input type="number" step="any" id="paid_amount"
                                                       name="paid_amount" value="{{ old('paid_amount', 0) }}" class="form-control text-right">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="4" class="text-right">Due</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('due_amount') ? 'has-error' :'' }}">
                                                <input type="number" readonly step="any" id="due_amount"
                                                       name="due_amount" class="form-control text-right">
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
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off"
                                           name="date" id="date" class="form-control business-date-picker"
                                           placeholder="Enter date" readonly>
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
                                    <label for="warehouse">Warehouse <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control select2" id="warehouse" name="warehouse">
                                        @foreach($warehouses as $warehouse)
                                            <option
                                                {{ old('warehouse') == $warehouse->id ? 'selected' : ''  }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('warehouse')
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
                                            <option value="{{ $paymentMode->id }}">{{ $paymentMode->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
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
                        <button type="button" id="btn-save-purchase" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('purchase.index') }}"
                           class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->

                </div>
                <!-- /.card -->
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
                <input type="hidden" name="product_name_val[]" class="product_name_val">
                <input type="hidden" name="product_id[]" class="product_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_qty" name="product_qty[]">
                </div>
            </td>

            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_unit_price"
                           name="product_unit_price[]">
                </div>
            </td>
            <td class="text-right total-purchase-cost"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i
                        class="fa fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>

    <!-- Purchase Overview Modal -->
    <div class="modal fade" id="modal-purchase-overview" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-shopping-cart mr-2"></i> Purchase Order Summary & Overview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body text-sm">
                    <!-- Supplier, Warehouse, Date, Payment Method & Note Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px; margin-left: 0; margin-right: 0;">
                        <div class="col-md-4 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Supplier Name:</strong>
                                <span id="ov-supplier" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-warehouse text-info mr-1"></i> Warehouse:</strong>
                                <span id="ov-warehouse" class="text-dark ml-1">-</span>
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
                                    <th class="text-right">Total Payable:</th>
                                    <td class="text-right font-weight-bold" id="ov-payable" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-info" id="ov-advance-row" style="display: none;">
                                    <th class="text-right">Advance Adjusted:</th>
                                    <td class="text-right font-weight-bold" id="ov-advance" style="font-size: 16px;">0.00</td>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Purchase</button>
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

            var productIds = [];

            $(".product_id").each(function (index) {
                if ($(this).val() != '') {
                    productIds.push($(this).val());
                }
            });

            loadProducts();

            function loadProducts() {
                $('#select_product').empty().append('<option value="">Search Name or Code of Product</option>');
                preloaderToggle(true);
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_category_group_wise_product') }}",
                }).done(function (response) {
                    preloaderToggle(false);
                    $.each(response, function (index, product) {
                        $('#select_product').append('<option value="' + product.id + '">' + product.name + ' - [' + (product.code || '') + ']</option>');
                    });
                    $('#select_product').trigger('change.select2');
                });
            }

            $('body').on('keypress', '#add_quantity,#add_selling_price,#add_wholesale_price,#add_unit_price', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });
            $('body').on('change', '#select_product', function (e) {
                let selectProductId = $(this).val();
                $('#add_unit_price').val(' ');
                if (selectProductId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_details') }}",
                        data: {id: selectProductId}
                    }).done(function (response) {
                        $('#add_unit_price').val(response.inventory.unit_price ?? ' ');

                        $('#add_unit_price_hint').text(response.inventory.unit_price ?? '');
                    });
                }
            });

            $('#supplier').change(function() {
                let supplierId = $(this).val();
                window.availableAdvance = 0;
                $('#advance_amount_text').text('0.00');
                calculate();
                
                if(supplierId) {
                    $.ajax({
                        url: '{{ route("purchase.supplier_validation_check") }}',
                        type: 'GET',
                        data: { supplier_id: supplierId },
                        success: function(response) {
                            if(response.current_balance < 0) {
                                // They have advance, show advance adjust option
                                let advanceAmount = Math.abs(response.current_balance);
                                window.availableAdvance = advanceAmount;
                                $('#advance_amount_text').text(parseFloat(advanceAmount).toFixed(2));
                                calculate();
                            }
                        }
                    });
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                $("#product-container").html();

                let selectProduct = $('#select_product').val();
                let selectProductName = $("#select_product option:selected").text();
                let addQuantity = $('#add_quantity').val();
                let addUnitPrice = $('#add_unit_price').val();

                if (selectProduct == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select product !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addQuantity == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type product quantity !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (addUnitPrice == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type product unit price !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }


                if ($.inArray(selectProduct, productIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectProductName + ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectProduct != '' && addQuantity != '' && addUnitPrice != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.hide();
                    item.closest('tr').find('.product_name').text($("#select_product option:selected").text());
                    item.closest('tr').find('.product_name_val').val($("#select_product option:selected").text());
                    item.closest('tr').find('.product_id').val($("#select_product option:selected").val());
                    item.closest('tr').find('.product_qty').val(addQuantity);
                    item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                    productIds.push(selectProduct);
                    item.show();
                    calculate();
                    $('#select_product').val('').change();
                    $('#add_quantity').val('');
                    $('#add_unit_price').val('');

                    $('#add_unit_price_hint').text('');
                }



                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var product_id = $(this).closest('tr').find('.product_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.product-item').remove();
                calculate();
                productIds = $.grep(productIds, function (value) {
                    return value != product_id;
                });
            });
            $('body').on('keyup', 'input[type="number"]', function () {
                calculate();
            });
            $('body').on('change', 'input[type="number"]', function () {
                calculate();
            });
            $('body').on('change', '#discount_type', function () {
                calculate();
            });

            // Purchase Preview Modal Logic
            $('body').on('click', '#btn-save-purchase', function(e) {
                e.preventDefault();

                // Validate form first
                let form = document.getElementById('purchase-form');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }

                // Check if any product items exist
                let rows = $("#product-container .product-item");
                if (rows.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Empty Product List',
                        text: 'Please add at least one product item before saving.',
                    });
                    return false;
                }

                // Clear and Populate Modal Table Body
                let overviewBody = $('#overview-items-body');
                overviewBody.empty();

                rows.each(function(index) {
                    let productSl = $(this).find('.product_sl').text().trim();
                    let productName = $(this).find('.product_name_val').val() || $(this).find('.product_name').text().trim();
                    let qty = parseFloat($(this).find('.product_qty').val() || 0);
                    let unitPrice = parseFloat($(this).find('.product_unit_price').val() || 0);
                    let totalPrice = qty * unitPrice;

                    let rowHtml = `
                        <tr>
                            <td>${productSl}</td>
                            <td class="text-left font-weight-bold">${productName}</td>
                            <td class="font-weight-bold">${qty}</td>
                            <td>${unitPrice.toFixed(2)}</td>
                            <td class="text-right font-weight-bold">${totalPrice.toFixed(2)}</td>
                        </tr>
                    `;
                    overviewBody.append(rowHtml);
                });

                // Populate Metadata
                let supplierText = $('#supplier option:selected').val() ? $('#supplier option:selected').text().trim() : 'N/A';
                let warehouseText = $('#warehouse option:selected').val() ? $('#warehouse option:selected').text().trim() : 'N/A';
                let dateText = $('#date').val() || 'N/A';
                let paymentMethodText = $('#payment_method option:selected').val() ? $('#payment_method option:selected').text().trim() : 'N/A';
                let notesText = $('#notes').val() ? $('#notes').val().trim() : 'No notes added';

                $('#ov-supplier').text(supplierText);
                $('#ov-warehouse').text(warehouseText);
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

                $('#ov-payable').text(parseFloat($('#total_payable').val() || 0).toFixed(2));

                let advanceAdjusted = parseFloat($('#advance_amount').val() || 0);
                if (advanceAdjusted > 0) {
                    $('#ov-advance-row').show();
                    $('#ov-advance').text(advanceAdjusted.toFixed(2));
                } else {
                    $('#ov-advance-row').hide();
                    $('#ov-advance').text('0.00');
                }

                $('#ov-paid').text(parseFloat($('#paid_amount').val() || 0).toFixed(2));
                $('#ov-due').text(parseFloat($('#due_amount').val() || 0).toFixed(2));

                // Show modal
                $('#modal-purchase-overview').modal('show');
            });

            // Confirm & submit form
            $('body').on('click', '#btn-confirm-submit', function() {
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $('#purchase-form').submit();
            });
        })

        function calculate() {
            // Assuming you want to start the product_sl value from 1
            var productSl = 1;

            // Select all the table rows with the class .product-item
            var rows = $("#product-container .product-item");
            // Iterate over each row and update the product_sl value
            rows.each(function () {
                // Find the .product_sl element within the current row
                var productSlElement = $(this).find('.product_sl');
                // Update the text of the .product_sl element with the product_sl value
                productSlElement.text(productSl);
                // Increment the product_sl value for the next iteration
                productSl++;
            });

            var total_quantity = 0;
            var sub_total = 0;
            $('.product-item').each(function (i, obj) {

                let product_qty = parseFloat($('.product_qty:eq(' + i + ')').val());
                product_qty = (isNaN(product_qty) || product_qty < 0) ? 0 : product_qty;

                let product_unit_price = parseFloat($('.product_unit_price:eq(' + i + ')').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $('.total-purchase-cost:eq(' + i + ')').text((product_qty * product_unit_price).toFixed(2));
                total_quantity += product_qty;
                sub_total += product_qty * product_unit_price;

            });

            $('#total_quantity').text(total_quantity);
            $('#sub_total').val(sub_total.toFixed(2));

            let discountType = $('#discount_type').val() || '1';
            let discountInput = parseFloat($('#discount_percent').val()) || 0;
            let discountAmount = 0;

            if (discountType == '2') {
                discountAmount = (sub_total / 100) * discountInput;
                $('#total_discount').text('(Amt: ' + discountAmount.toFixed(2) + ' ৳)');
            } else {
                discountAmount = discountInput;
                $('#total_discount').text('');
            }

            let vat_percent = parseFloat($('#vat_percent').val());
            vat_percent = (isNaN(vat_percent) || vat_percent < 0) ? 0 : vat_percent;

            let tax_percent = parseFloat($('#tax_percent').val());
            tax_percent = (isNaN(tax_percent) || vat_percent < 0) ? 0 : tax_percent;

            let vatAmount = (sub_total / 100) * vat_percent;
            let taxAmount = (sub_total / 100) * tax_percent;

            let total_payable = Math.ceil((sub_total - discountAmount) + (vatAmount + taxAmount));

            $('#discount_amount').val(discountAmount);
            $('#total_vat').text(vatAmount.toFixed(2));
            $('#vat_amount').val(vatAmount);
            $('#total_tax').text(taxAmount.toFixed(2));
            $('#tax_amount').val(taxAmount);

            $('#total_payable').val(total_payable.toFixed(2));
            
            // Auto Adjust Advance
            let advance_adjusted = 0;
            if (window.availableAdvance && window.availableAdvance > 0) {
                $('#advance_adjust').val(1);
                $('#advance_row').show();
                advance_adjusted = Math.min(window.availableAdvance, total_payable);
                $('#advance_amount').val(advance_adjusted.toFixed(2));
            } else {
                $('#advance_adjust').val(0);
                $('#advance_row').hide();
                $('#advance_amount').val(0);
            }

            if (advance_adjusted >= total_payable && total_payable > 0) {
                $('#paid_amount').val(0).attr('readonly', true);
            } else {
                $('#paid_amount').attr('readonly', false);
            }

            let paid_amount = parseFloat($('#paid_amount').val());
            paid_amount = (isNaN(paid_amount) || paid_amount < 0) ? 0 : paid_amount;

            $('#due_amount').val((total_payable - advance_adjusted - paid_amount).toFixed(2));

            if (rows.length > 0) {
                $(".footer_area").show();
            } else {
                $(".footer_area").hide();
            }
        }
    </script>
@endsection
