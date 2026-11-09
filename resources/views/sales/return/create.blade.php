@extends('layouts.app')
@section('title','Sales Return Create')
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
          action="{{ route('sales-return.store') }}"
          class="form-horizontal" method="post">
        @csrf
        <input type="hidden" name="is_pos" value="false">
        <div class="row">
            <!-- left column -->
            <div class="col-md-8">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Product Information </h3>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="select_order">Sales Order <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_order" name="select_order">
                                        <option value="">Search Name of Order</option>
                                        @foreach($orders as $order)
                                            <option {{ old('select_order') == $order->id ? 'selected' : ''  }} value="{{ $order->id }}">{{ $order->order_no }}</option>
                                        @endforeach
                                    </select>
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
                                        <th class="text-center" width="10%">Order Qty</th>
                                        <th class="text-center" width="15%">Return Qty</th>
                                        <th class="text-center" width="15%">Unit Price</th>
                                        <th class="text-center" width="15%">Order Total</th>
                                        <th class="text-center" width="15%">Return Total</th>
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
                                                    <input type="hidden" name="product_code[]"
                                                           value="{{ old('product_code.'.$loop->index) }}"
                                                           class="product_code">
                                                    <input type="hidden" name="inventory_id[]"
                                                           value="{{ old('inventory_id.'.$loop->index) }}"
                                                           class="inventory_id">
                                                    <input type="hidden" name="sale_order_item_id[]" value="{{ old('sale_order_item_id.'.$loop->index) }}" class="sale_order_item_id">       

                                                </td>
                                                <td class="text-right">
                                                    {{ old('order_quantity.'.$loop->index) }}
                                                    <input type="hidden" value="{{ old('order_quantity.'.$loop->index) }}" name="order_quantity[]" class="order_quantity">
                                                </td>
                                                <td class="text-right">
                                                    <div
                                                        class="form-group mb-0  {{ $errors->has('return_quantity.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any"
                                                               value="{{ old('return_quantity.'.$loop->index) }}"
                                                               class="form-control text-right return_quantity"
                                                               name="return_quantity[]">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    {{ old('product_unit_price.'.$loop->index) }}
                                                    <input type="hidden" value="{{ old('product_unit_price.'.$loop->index) }}" name="product_unit_price[]" class="product_unit_price">
                                                </td>
                                                <td class="text-right total-sale-cost"></td>
                                                <td class="text-right total-return-cost"></td>
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
                                        <th class="text-right"></th>
                                        <th class="text-right" id="total_quantity"></th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right" id="order_sub_total"></th>
                                        <th class="text-right">
                                            <div class="form-group">
                                                <div class="form-group mb-0  {{ $errors->has('sub_total') ? 'has-error' :'' }}">
                                                    <input type="number" step="any" readonly name="sub_total" id="sub_total" class="form-control text-right">
                                                </div>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="6" class="text-right">Return Discount(%)</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('discount_percent') ? 'has-error' :'' }}">
                                                <input type="number" value="{{ old('discount_percent') }}" step="any"
                                                       name="discount_percent"
                                                       id="discount_percent" readonly class="form-control text-right">
                                                <input type="hidden" name="discount_amount" id="discount_amount">
                                                <span id="total_discount"></span>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <input type="hidden" id="vat_amount" name="vat_amount">
                                    {{-- <tr class="footer_area">
                                        <th colspan="6" class="text-right">VAT(%)</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('vat_percent') ? 'has-error' :'' }}">
                                                <input type="number" value="{{ old('vat_percent') }}" step="any"
                                                       id="vat_percent" readonly name="vat_percent"
                                                       class="form-control text-right">
                                                <input type="hidden" id="vat_amount" name="vat_amount">
                                                <span id="total_vat"></span>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr> --}}

                                    <tr class="footer_area">
                                        <th colspan="6" class="text-right">Total Return Amount</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('total_returnable') ? 'has-error' :'' }}">
                                                <input type="number" readonly step="any" id="total_returnable"
                                                       name="total_returnable" class="form-control text-right">
                                                <input type="hidden" name="total_returnable_amount" id="total_returnable_amount">       
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>

                                    
                                    </tfoot>
                                </table>
                                <table class="table table-bordered">
                                    <tfoot>
                                        <tr class="footer_area">
                                            <th colspan="6" class="text-right">Invoice Sub Total</th>
                                            <th class="text-right">
                                                <span id="order_sub_total_text">{{ number_format(old('order_sub_total_amount'),2) }}</span>
                                                <input type="hidden" name="order_sub_total_amount" id="order_sub_total_amount">
                                            </th>
                                            <th></th>
                                        </tr>
                                        <tr class="footer_area">
                                            <th style="border-bottom: 3px solid #fff;" colspan="6" class="text-right">Invoice Discount</th>
                                            <th style="border-bottom: 3px solid #fff;" class="text-right">
                                                <span id="order_discount_text">{{ number_format(old('order_discount_amount'),2) }}</span>
                                                <input type="hidden" name="order_discount_amount" id="order_discount_amount">
                                            </th>
                                            <th style="border-bottom: 3px solid #fff;"></th>
                                        </tr>
                                        <tr class="footer_area">
                                            <th colspan="6" class="text-right">Invoice Total(=)</th>
                                            <th class="text-right">
                                                <span id="order_total">{{ number_format(old('order_total_amount'),2) }}</span>
                                                <input type="hidden" name="order_total_amount" value="{{ old('order_total_amount') }}" id="order_total_amount">
                                            </th>
                                            <th></th>
                                        </tr>
                                        <tr class="footer_area">
                                            <th colspan="6" class="text-right">Return Total(-)</th>
                                            <th class="text-right">
                                                <span id="return_total"></span>
                                                <input type="hidden" name="return_total_amount" id="return_total_amount">
                                            </th>
                                            <th></th>
                                        </tr>
    
                                        <tr class="footer_area">
                                            <th style="border-bottom: 3px solid #fff;" colspan="6" class="text-right">Previous Paid(-)</th>
                                            <th style="border-bottom: 3px solid #fff;" class="text-right">
                                                <span id="previous_paid">{{ old('previous_paid_amount') }}</span>
                                                <input type="hidden" name="previous_paid_amount" value="{{ old('previous_paid_amount') }}" id="previous_paid_amount">
                                            </th>
                                            <th style="border-bottom: 3px solid #fff;"></th>
                                        </tr>
                                        <tr class="footer_area">
                                            <th colspan="6" class="text-right">Adjustment(=)</th>
                                            <th class="text-right">
                                                <span id="adjustment"></span>
                                                <input type="hidden" name="adjustment_amount" id="adjustment_amount">
                                            </th>
                                            <th id="adjustment_remarks"></th>
                                        </tr>
                                        <tr class="footer_area">
                                            <th colspan="6" class="text-right">Adjustment Paid</th>
                                            <th class="text-right">
                                                <div
                                                    class="form-group mb-0  {{ $errors->has('paid_amount') ? 'has-error' :'' }}">
                                                    <input type="number" step="any" id="paid_amount"
                                                           name="paid_amount" value="{{ old('paid_amount',0) }}" class="form-control text-right">
                                                </div>
                                            </th>
                                            <th></th>
                                        </tr>
                                        <tr class="footer_area">
                                            <th colspan="6" class="text-right">Adjustment Due</th>
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
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('sales-return.index') }}"
                           class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->

                </div>
                <!-- /.card -->
            </div>
            <div class="col-md-4">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Other Information</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row"> 
                            {{-- <div class="col-md-12">
                                <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
                                    <label for="warehouse">Warehouse <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control select2" id="warehouse" name="warehouse">
                                        @foreach($warehouses as $warehouse)
                                            <option
                                                {{ old('warehouse',request('warehouse')) == $warehouse->id ? 'selected' : ''  }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('warehouse')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('date',date('d-m-Y')) }}"
                                           name="date" id="date" class="form-control date-picker"
                                           placeholder="Enter date">
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            {{-- <div class="col-md-12">
                                <div class="form-group {{ $errors->has('customer') ? 'has-error' :'' }}">
                                    <label for="customer">Customer <span
                                            class="text-danger">*</span> <button type="button" id="add-new-customer" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-plus"></i></button></label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option value="">Search Name of Customer</option>
                                    </select>
                                    @error('customer')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div> --}}

                            <div class="col-md-12">
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

                            <div class="col-md-12">
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
                    <div class="card-footer"></div>
                    <!-- /.card-footer -->
                </div>
                <!-- /.card -->
            </div>
            <!--/.col (left) -->
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
                <input type="hidden" name="inventory_id[]" class="inventory_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_stock" name="product_stock[]" readonly>
                </div>
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

@endsection
@section('script')
    <script>
        $(function () {
            calculate();

            $("#select_order").change(function () {
                let orderId = $(this).val();
                $('#product-container').html('');
                if (orderId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_sales_items') }}",
                        data: {orderId: orderId}
                    }).done(function (data) {
                        $('#product-container').html(data.saleItemsView);
                        $('#discount_percent').val(data.order.discount_percent);
                        $('#vat_percent').val(data.order.vat_percent);
                        $('#order_total').text((data.order.total).toFixed(2));
                        $('#order_total_amount').val(data.order.total);
                        $('#previous_paid').text((data.order.paid).toFixed(2));
                        $('#previous_paid_amount').val(data.order.paid);

                        $('#order_sub_total_text').text((data.order.sub_total).toFixed(2));
                        $('#order_sub_total_amount').val(data.order.sub_total);

                        $('#order_discount_text').text((data.order.discount).toFixed(2));
                        $('#order_discount_amount').val(data.order.discount);

                        calculate();
                    });
                }
            })
            $("#project").trigger('change');
            $('#warehouse').on('change', function () {
                let warehouse = $(this).val();
                let currentUrl = new URL(window.location.href);
                // Update or add the `requisition_type` parameter
                currentUrl.searchParams.set('warehouse', warehouse);
                // Redirect to the updated URL
                window.location.href = currentUrl.toString();
            });
            $('#customer').select2({
                ajax: {
                    url: "{{ route('party_json') }}",
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return {
                            type:[2],
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

            var productIds = [];

            $(".product_id").each(function (index) {
                if ($(this).val() != '') {
                    productIds.push($(this).val());
                }
            });
            $('body').on('keypress', '#add_quantity,#add_selling_price,#add_unit_price', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });
            $('body').on('change', '#select_product', function (e) {
                let warehouseId = $('#warehouse').val();
                let selectProductId = $(this).val();
                $('#add_unit_price').val(' ');
                if (selectProductId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: {id: selectProductId,warehouseId: warehouseId}
                    }).done(function (response) {
                        console.log(response);
                        $('#add_stock').val(response.inventory && response.inventory.quantity ? response.inventory.quantity : 0);
                        $('#add_inventory_id').val(response.inventory.id ?? 0);
                        $('#add_unit_price').val(response.inventory.selling_price ?? ' ');
                    });
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                $("#product-container").html();
                //not need requisition
                let selectProduct = $('#select_product').val();
                let selectProductName = $("#select_product option:selected").text();
                let addStock = $('#add_stock').val();
                let addInventoryId = $('#add_inventory_id').val();
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

                if(addStock < addQuantity){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectProductName + ' insufficient stock.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectProduct != '' && addQuantity != '' && addStock != '' && addUnitPrice != '') {
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
                    item.closest('tr').find('.product_code').val($("#select_product option:selected").data('product_code'));
                    item.closest('tr').find('.inventory_id').val(addInventoryId);
                    item.closest('tr').find('.product_stock').val(addStock);
                    item.closest('tr').find('.product_quantity').val(addQuantity);
                    item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                    productIds.push(selectProduct);
                    item.show();
                    calculate();
                    $('#add_quantity').val('');
                    $('#add_stock').val('');
                    $('#add_unit_price').val('');
                }
                
                return false; 
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
        })

        function calculate() {
            var productSl = 1;

            var rows = $("#product-container .product-item");
            rows.each(function () {
                let order_quantity1 = parseFloat($('.order_quantity:eq(' + (productSl-1) + ')').val());
                order_quantity1 = (isNaN(order_quantity1) || order_quantity1 < 0) ? 0 : order_quantity1;

                let return_quantity1 = parseFloat($('.return_quantity:eq(' + (productSl-1) + ')').val());
                return_quantity1 = (isNaN(return_quantity1) || return_quantity1 < 0) ? 0 : return_quantity1;

                if(order_quantity1 < return_quantity1){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Maximum return quantity will be '+order_quantity1,
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }

                    $('.return_quantity:eq(' + (productSl-1) + ')').val(1);
                }

                var productSlElement = $(this).find('.product_sl');
                productSlElement.text(productSl);
                productSl++;
            });

            var total_quantity = 0;
            var order_sub_total = 0;
            var sub_total = 0;
            var paid_amount = $('#paid_amount').val();
            $('.product-item').each(function (i, obj) {

                let order_quantity = parseFloat($('.order_quantity:eq(' + i + ')').val());
                order_quantity = (isNaN(order_quantity) || order_quantity < 0) ? 0 : order_quantity;

                let return_quantity = parseFloat($('.return_quantity:eq(' + i + ')').val());
                return_quantity = (isNaN(return_quantity) || return_quantity < 0) ? 0 : return_quantity;

                let product_unit_price = parseFloat($('.product_unit_price:eq(' + i + ')').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $('.total-return-cost:eq(' + i + ')').text((return_quantity * product_unit_price).toFixed(2));
                $('.total-sale-cost:eq(' + i + ')').text((order_quantity * product_unit_price).toFixed(2));
                total_quantity += return_quantity;
                order_sub_total += order_quantity * product_unit_price;
                sub_total += return_quantity * product_unit_price;

            });

            $('#total_quantity').text(total_quantity);
            $('#order_sub_total').text(order_sub_total.toFixed(2));
            $('#sub_total').val(sub_total.toFixed(2));

            let discount_percent = parseFloat($('#discount_percent').val());
            discount_percent = (isNaN(discount_percent) || discount_percent < 0) ? 0 : discount_percent;

            let vat_percent = parseFloat($('#vat_percent').val());
            vat_percent = (isNaN(vat_percent) || vat_percent < 0) ? 0 : vat_percent;

            let tax_percent = parseFloat($('#tax_percent').val());
            tax_percent = (isNaN(tax_percent) || vat_percent < 0) ? 0 : tax_percent;

            let discountAmount = (sub_total / 100) * discount_percent;
            let vatAmount = (sub_total / 100) * vat_percent;
            let taxAmount = (sub_total / 100) * tax_percent;

            let total_returnable = (sub_total - discountAmount) + (vatAmount + taxAmount);

            $('#total_discount').text(discountAmount.toFixed(2));
            $('#discount_amount').val(discountAmount);
            $('#total_vat').text(vatAmount.toFixed(2));
            $('#vat_amount').val(vatAmount);
            $('#total_tax').text(taxAmount.toFixed(2));
            $('#tax_amount').val(taxAmount);

            $('#total_returnable').val(total_returnable.toFixed(2));
            $('#total_returnable_amount').val(total_returnable.toFixed(2));
            $('#return_total').text(total_returnable.toFixed(2));
            $('#return_total_amount').val(total_returnable.toFixed(2));

            let order_total = $('#order_total_amount').val();
            let previous_paid_amount = $('#previous_paid_amount').val();

            let adjustment = (order_total - total_returnable) - previous_paid_amount;
            
            $('#adjustment_amount').val(adjustment);
            if(adjustment > 0){
                $('#adjustment').text(adjustment);
                $('#adjustment_remarks').text('(Due)');
            }else if(adjustment < 0){
                $('#adjustment').text('('+Math.abs(adjustment)+')');
                $('#adjustment_remarks').text('(Refund)');
            }else{
                $('#adjustment').text(adjustment);
                $('#adjustment_remarks').text('');
            }

            let due_amount = Math.abs(adjustment) - paid_amount;
            $('#due_amount').val(due_amount.toFixed(2));

            if (rows.length > 0) {
                $(".footer_area").show();
            } else {
                $(".footer_area").hide();
            }
        }
    </script>
@endsection
