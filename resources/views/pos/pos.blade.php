@extends('layouts.app')
@section('title','Point of Sale')
@section('style')
    <style>
        .input-group.search-bar {
            position: relative;
        }
        .search-icon {
            position: absolute;
            right: 7px;
            bottom: 3px;
            font-size: 19px;
            z-index: 9;
        }
        .typeahead.dropdown-menu .dropdown-item:focus,.typeahead.dropdown-menu .dropdown-item:hover{
            color: #fff;
            text-decoration: none;
            background-color: #810ca8;
        }
        .typeahead.dropdown-menu .active .dropdown-item{
            color: #fff;
            text-decoration: none;
            background-color: #810ca8;
        }
        @media (min-width: 768px){
            .product-card-body {
                max-height: calc(100vh - 195px);
                min-height: calc(100vh - 195px);
            }
            }
            .customer-card-body{
                max-height: calc(100vh - 175px);
                min-height: calc(100vh - 175px);
            }
            body{
                overflow: hidden !important;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-title">Products Information</div>
                        </div>
                        <div class="card-header">
                            <div class="input-group search-bar">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fa fa-barcode"></i></span>
                                </div>
                                <input type="text" step="any"  class="form-control" id="barcode" placeholder="Search for product barcode...">
                                <div class="search-icon" id="barcode-search">
                                    <i id="search-icon-change" class="fa fa-search"></i>
                                </div>
                            </div>
                        </div>
                        <div class="card-body product-card-body" style="position: relative;padding-right: 6px;scrollbar-width: thin;overflow-y: auto !important;overflow-x: hidden !important;">
                            <form action="{{ $saleOrder->id ? route('sales.update',['sale'=>$saleOrder->id]) : route('sales.store') }}" method="post" id="form-sales">
                                @csrf
                                @if($saleOrder->id)
                                    @method('PUT')
                                @endif
                                <input type="hidden" name="is_pos" value="1">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th class="text-center">S/L</th>
                                        <th class="text-center">Product</th>
                                        <th class="text-center">Barcode</th>
                                        <th class="text-center">Unit</th>
                                        <th class="text-center text-warning">Stock</th>
                                        <th class="text-center" style="width: 10%">Quantity</th>
                                        <th class="text-center" style="width: 15%">Unit Price</th>
                                        <th class="text-center" style="width: 15%">Total Price</th>
                                        <th class="text-center"></th>
                                    </tr>
                                    </thead>
                                    <tbody id="product-container">
                                        @foreach($saleOrder->saleOrderItems as $saleOrderItem)
                                            @php
                                                $stockItem = \App\Models\Inventory\Inventory::where('sister_concern_id',$saleOrder->sister_concern_id)->where('id',$saleOrderItem->inventory_id)->first()->quantity ?? 0;
                                            @endphp
                                            <tr class="product-item">
                                                <td class="text-center sl_text">{{ $loop->iteration }}</td>
                                                <td class="text-center product_name_text">{{ $saleOrderItem->product->name ?? '' }}</td>
                                                <td class="text-center">
                                                    <input type="hidden" value="{{ $saleOrderItem->product_id }}" name="product_id[]" class="product_id">
                                                    <input type="hidden" value="{{ $saleOrderItem->inventory_id }}" name="inventory_id[]" class="inventory_id">
                                                    <input type="hidden" value="{{ $saleOrderItem->product->name ?? '' }}" name="product_name[]" class="product_name">
                                                    <input type="hidden" value="{{ $saleOrderItem->product_code }}" name="product_code[]" class="product_code">
                                                    <input type="hidden" value="{{ $saleOrderItem->product->unit->name ?? '' }}" name="product_unit[]" class="product_unit">
                                                    <input type="hidden" value="{{ $saleOrderItem->quantity }}" name="product_quantity[]" class="product_quantity">
                                                    <input type="hidden" value="{{ $saleOrderItem->selling_price }}" name="product_unit_price[]" class="product_unit_price">
                                                    <input type="hidden" value="{{ $saleOrderItem->quantity }}" name="prev_product_quantity[]" class="prev_product_quantity">
                                                    <span class="product_code_text">{{ $saleOrderItem->product_code }}</span>
                                                </td>
                                                <td class="text-center product_unit_text">{{ $saleOrderItem->product->unit->name ?? '' }}</td>
                                                <th class="text-center product_stock_text text-warning">{{ $stockItem + $saleOrderItem->quantity }}</th>
                                                <td class="text-center">
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-right product_quantity" value="{{ $saleOrderItem->quantity }}" name="product_quantity[]">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="form-group">
                                                        <input type="number" class="form-control text-right product_unit_price" value="{{ $saleOrderItem->selling_price }}" name="product_unit_price[]">
                                                    </div>
                                                </td>
                                                {{-- <td class="text-center product_quantity_text">{{ $saleOrderItem->quantity }}</td>
                                                <td class="text-right product_unit_price_text">{{ $saleOrderItem->selling_unit_price }}</td> --}}
                                                <td class="text-right total-sale-cost"></td>
                                                <td class="text-center"><button class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th class="text-center" colspan="5">Total</th>
                                        <th class="text-right" id="total-quantity"></th>
                                        <th></th>
                                        <th class="text-right" id="total-price"></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                                @include('layouts.partial.__inside_preloader')
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Billing Information</h3>
                </div>
                <div class="card-body">
                    <div class="customer-card-body"  style="padding-right: 6px;scrollbar-width: thin;overflow-y: auto;">
                        <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
                            <label for="warehouse">Warehouse <span class="text-danger">*</span></label>
                            @if ($saleOrder->id)
                                <input type="text" class="form-control" value="{{ $saleOrder->warehouse->name??'' }}" readonly/>   
                                <input type="hidden" class="form-control" id="warehouse" name="warehouse" value="{{ $saleOrder->warehouse_id }}"/>   
                            @else
                                <select class="form-control select2" id="warehouse" name="warehouse">
                                    @foreach($warehouses as $warehouse)
                                        <option {{ old('warehouse',request('warehouse')) == $warehouse->id ? 'selected' : ''  }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            @endif
                            
                            @error('warehouse')
                            <span class="help-block">{{ $message }}</span>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="customer">Search Customer <span class="text-danger">*</span> <button type="button" id="add-new-customer" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-plus"></i></button></label>
                            <select style="max-width: max-content !important;" name="customer" class="form-control select2" id="customer">
                                <option value="">Select Customer</option>
                                @if($saleOrder->customer)
                                <option selected value="{{ $saleOrder->customer_id }}">
                                    {{ $saleOrder->customer->name ?? '' }}
                                    @if($saleOrder->customer->mobile_no != '')
                                        - Mobile No. {{ $saleOrder->customer->mobile_no }}
                                    @endif
                                </option>
                                @endif
                            </select>
                        </div>
                        <div class="d-flex text-white justify-content-between align-items-center">
                            <h5>Total: <span id="total-checkout-price"></span></h5>
                        </div>
                        <div class="payment-buttons">
                            <button disabled type="button" class="btn btn-info bg-gradient-info btn-block" id="btn-checkout">
                                Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Payment Modal -->
    <div class="modal fade" id="paymentModal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="paymentModalLabel">Select Payment Method</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form>
                        <span class="text-danger error-message" id="customer-error"></span>
                        <div class="form-group">
                            <label for="date" class="text-dark">Date <span class="text-danger">*</span></label>
                            @if ($saleOrder->id)
                                <input type="text" autocomplete="off" value="{{ date('d-m-Y',strtotime($saleOrder->date)) }}" readonly class="form-control date-picker" placeholder="Enter Date" id="date" name="date">
                            @else
                                <input type="text" autocomplete="off" value="{{ date('d-m-Y') }}" readonly class="form-control date-picker" placeholder="Enter Date" id="date" name="date">
                            @endif
                            
                            <span class="text-danger error-message" id="date-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="payment_method" class="text-dark">Payment Method</label>
                            <select class="form-control select2" name="payment_method" id="payment_method">
                                <option value="">Select Payment Method</option>
                                @foreach($paymentModes as $paymentMode)
                                    <option value="{{ $paymentMode->id }}">{{ $paymentMode->name }}</option>
                                @endforeach
                            </select>
                            <span class="text-danger error-message" id="payment_method-error"></span>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sub_total" class="text-dark">Total</label>
                                    <input type="text" class="form-control" name="sub_total" id="sub_total" readonly>
                                    <span class="text-danger error-message" id="sub_total-error"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="discount_amount" class="text-dark">Discount</label>
                                    <input type="number" step="any" class="form-control" id="discount_amount">
                                    <span class="text-danger error-message" id="discount_amount-error"></span>
                                </div>
                            </div>
                            <input type="hidden" id="vat_amount" name="vat_amount" value="0">
                            <input type="hidden" name="total_amount" id="total_amount">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="paid_amount" class="text-dark">Paid</label>
                                    <input type="number" step="any" class="form-control" id="paid_amount">
                                    <span class="text-danger error-message" id="paid_amount-error"></span>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="due_amount" class="text-dark">Due</label>
                                    <input type="text" class="form-control" id="due_amount" readonly>
                                    <span class="text-danger error-message" id="due_amount-error"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="notes" class="text-dark">Notes</label>
                            <textarea class="form-control" placeholder="Enter notes" id="notes" name="notes">{{ $saleOrder->id ? $saleOrder->notes : '' }}</textarea>
                            <span class="text-danger error-message" id="notes-error"></span>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="submit-payment" class="btn btn-info bg-gradient-info">Submit Payment</button>
                </div>
            </div>
        </div>
    </div>
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
                            <label for="customer_name" class="text-dark">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" id="customer_name" placeholder="Enter Customer Name" class="form-control" name="customer_name">
                            <span class="text-danger error-message" id="customer_name-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="mobile_no" class="text-dark">Mobile No.</label>
                            <input type="text" id="mobile_no" placeholder="Enter Mobile No." class="form-control" name="mobile_no">
                            <span class="text-danger error-message" id="mobile_no-error"></span>
                        </div>
                        <div class="form-group">
                            <label for="address" class="text-dark">Address</label>
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
            <td class="text-center sl_text"></td>
            <td class="text-center product_name_text"></td>
            <td class="text-center">
                <input type="hidden" name="product_id[]" class="product_id">
                <input type="hidden" name="inventory_id[]" class="inventory_id">
                <input type="hidden" name="product_name[]" class="product_name">
                <input type="hidden" name="product_code[]" class="product_code">
                <input type="hidden" name="product_unit[]" class="product_unit">
                <input type="hidden" value="0" name="prev_product_quantity[]" class="prev_product_quantity">
                <span class="product_code_text"></span>
            </td>
            <td class="text-center product_unit_text"></td>
            <th class="text-center product_stock_text text-warning"></th>
            <td class="text-center">
                <div class="form-group">
                    <input type="number" class="form-control text-right product_quantity" name="product_quantity[]">
                </div>
            </td>
            <td>
                <div class="form-group">
                    <input type="number" class="form-control text-right product_unit_price" name="product_unit_price[]">
                </div>
            </td>
            <td class="text-right total-sale-cost"></td>
            <td class="text-center"><button class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
        </tr>
    </template>
@endsection
@section('script')
    <script>
        var isProcessing = false;
        var productCodes = [];
        $(function (){
            calculate();
            barcodeAutoComplete('barcode', "{{ route('search-barcode-autocomplete') }}", $('#warehouse').val());

            $( ".product_code" ).each(function( index ) {
                if ($(this).val() != '') {
                    productCodes.push($(this).val());
                }
            });
            $('body').on('keypress', '#barcode', function (e) {
                if (e.keyCode == 13) {
                    if (isProcessing) return false; 
                    isProcessing = true;

                    let input = $(this).val().trim();
                    let productCode = "";
                    let productBatchText = "";
                    let productBatch = "";
                    if(input.includes('|')){
                        productCode = input.split('|')[1].trim();
                        productBatchText = input.split('|')[2].trim();
                        productBatch = productBatchText.split('-')[1].trim();

                        appendProduct(productCode, productBatch);
                    }
                    return false;
                }
            });
            $("#barcode-search").click(function () {
                if (isProcessing) return false; 
                isProcessing = true;

                let input = $("#barcode").val().trim();
                let productCode = "";
                let productBatchText = "";
                let productBatch = "";
                if(input.includes('|')){
                    productCode = input.split('|')[1].trim();
                    productBatchText = input.split('|')[2].trim();
                    productBatch = productBatchText.split('-')[1].trim();

                    appendProduct(productCode, productBatch);
                }
            });
            $('body').on('click', '.btn-remove', function () {
                if (confirm('Are you sure?')){
                    let product_code = $(this).closest('li').find('.product_code').val();
                    let removeItem = document.getElementById("remove_sound");
                    removeItem.play();
                    $(this).closest('.product-item').remove();
                    calculate();
                    productCodes = $.grep(productCodes, function(value) {
                        return value != product_code;
                    });
                }

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
            $("#btn-checkout").click(function (){
                preloaderToggle(true);
                $("#paymentModal").modal('show');
                preloaderToggle(false);
            })
            $("#submit-payment").click(function (){
                $('.error-message').text('');
                preloaderToggle(true);
                var formData = new FormData($('#form-sales')[0]);
                // Append additional data
                formData.append('date', $("#date").val());
                formData.append('warehouse', $("#warehouse").val());
                formData.append('customer', $("#customer").val());
                formData.append('payment_method', $("#payment_method").val());
                formData.append('sub_total', $("#sub_total").val());
                formData.append('total_amount', $("#total_amount").val());
                formData.append('vat_amount', $("#vat_amount").val());
                formData.append('discount_amount', $("#discount_amount").val());
                formData.append('paid_amount', $("#paid_amount").val());
                formData.append('due_amount', $("#due_amount").val());
                formData.append('notes', $("#notes").val());
                $.ajax({
                    type: 'POST',
                    url: $('#form-sales').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            ajaxSuccessMessage(response.message)
                            location.href =response.redirect_url;
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


            $('body').on('keyup', '#paid_amount,#discount_amount', function() {
                calculatePaid();
            });
            $('body').on('keyup', '.product_quantity,product_unit_price', function () {
                calculate();
            });
            $('body').on('change', '.product_quantity,product_unit_price', function () {
                calculate();
            });
        });
        function appendProduct(productCode, productBatch){
            if (productCode == ''){
                ajaxWarningMessage('Please, Enter Barcode !');
                isProcessing = false;
                return false;
            }
            if (productBatch == ''){
                ajaxWarningMessage('Please, Enter Batch !');
                isProcessing = false;
                return false;
            }
            let isDuplicate = false;
            $("#product-container .product-item").each(function() {
                let existingProduct = $(this).find('.product_code').val();

                if (existingProduct === productCode) {
                    isDuplicate = true;
                    return false; // Exit loop
                }
            });

            if (isDuplicate) {
                ajaxWarningMessage('Already exist in list: '+productCode);
                $('#barcode').val('');
                isProcessing = false;
                return false;
            }
            // if($.inArray(productCode, productCodes) != -1) {
            //     ajaxWarningMessage('Already exist in list: '+productCode);
            //     return false;
            // }

            if (productCode != '' && productBatch != '') {
                insidePreloaderToggle(true);
                $.ajax({
                    method: "GET",
                    url: "{{ route('pos_product_search') }}",
                    data: {productCode: productCode, productBatch: productBatch}
                }).done(function( response ) {
                    if (response.status) {
                        let addMoreSound = document.getElementById("add_more_sound");
                        addMoreSound.play();
                        let html = $('#template-product').html();
                        let itemHtml = $(html);
                        $('#product-container').append(itemHtml);
                        let item = $('.product-item').last();
                        item.hide();
                        item.closest('tr').find('.product_name_text').text(response.inventory.product.name);
                        item.closest('tr').find('.product_name').val(response.inventory.product.name);
                        item.closest('tr').find('.product_code_text').text(response.inventory.product_code);
                        item.closest('tr').find('.product_code').val(response.inventory.product_code);
                        item.closest('tr').find('.product_unit_text').text(response.inventory.product.unit.name);
                        item.closest('tr').find('.product_unit').val(response.inventory.product.unit.name);
                        item.closest('tr').find('.product_stock_text').text(response.inventory.quantity);
                        item.closest('tr').find('.product_quantity_text').text(1);
                        item.closest('tr').find('.product_quantity').val(1);
                        item.closest('tr').find('.product_unit_price_text').text(response.inventory.selling_price);
                        item.closest('tr').find('.product_unit_price').val(response.inventory.selling_price);
                        item.closest('tr').find('.product_id').val(response.inventory.product_id);
                        item.closest('tr').find('.inventory_id').val(response.inventory.id);

                        productCodes.push(productCode);
                        item.show();
                        calculate();
                        $('#barcode').val('');
                        insidePreloaderToggle(false);
                    }else if (response.status == false) {
                        $('#barcode').val('');
                        ajaxErrorMessage(response.message);
                        insidePreloaderToggle(false);
                    }
                }).always(function () {
                    isProcessing = false;
                });
            }
            return false; // prevent the button click from happening
        }
        function calculate(){
            let sl_text = 1;
            let rows = $("#product-container .product-item");
            rows.each(function() {
                var productSlElement = $(this).find('.sl_text');
                productSlElement.text(sl_text);
                sl_text++;
            });

            var total_price = 0;
            var total_quantity = 0;
            $('.product-item').each(function(i, obj) {
                
                let product_qty = parseFloat($('.product_quantity:eq('+i+')').val());
                product_qty = (isNaN(product_qty) || product_qty < 0) ? 0 : product_qty;
  
                let product_unit_price = parseFloat($('.product_unit_price:eq('+i+')').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $('.total-sale-cost:eq(' + i + ')').text((product_qty * product_unit_price).toFixed(2));
                total_quantity += product_qty;
                total_price += (product_qty * product_unit_price);
            });

            $('#total-quantity').text(total_quantity);
            $('#total-price').text(total_price);
            $('#total-checkout-price').text(total_price);
            $('#sub_total').val(total_price);
            $('#total_amount').val(total_price);
            // let paid_amount = $('#paid_amount').val(total_price);
            $('#due_amount').val(total_price);
            if (total_price > 0){
                $('#btn-checkout').attr('disabled',false);
            }else{
                $('#btn-checkout').attr('disabled',true);
            }
        }
        function calculatePaid(){
            let total_amount = parseFloat($('#sub_total').val());
            total_amount = (isNaN(total_amount) || total_amount < 0) ? 0 : total_amount;

            let discount_amount = parseFloat($('#discount_amount').val());
            discount_amount = (isNaN(discount_amount) || discount_amount < 0) ? 0 : discount_amount;

            $('#total_amount').val(total_amount-discount_amount);

            let paid_amount = parseFloat($('#paid_amount').val());
            paid_amount = (isNaN(paid_amount) || paid_amount < 0) ? 0 : paid_amount;
            
            let due_amount = (total_amount - discount_amount) - paid_amount;
            $('#due_amount').val(due_amount);
        }

    </script>
@endsection
