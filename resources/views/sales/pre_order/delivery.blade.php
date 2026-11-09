@extends('layouts.app')
@section('title','Pre-Order Delivery')
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
    <form enctype="multipart/form-data" id="delivery-form"
          action="{{ route('pre-orders.delivery_store', ['pre_order' => $sale->id]) }}"
          class="form-horizontal" method="post">
        @csrf
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Delivery Information (Pre-Order) </h3>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="select_product">Add More Product</label>
                                    <select class="form-control select2" id="select_product">
                                        <option value="">Search Name of Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-product_code="{{ $product->code }}" data-price="{{ $product->selling_price }}">
                                                {{ $product->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Batch</label>
                                    <select class="form-control select2" id="select_batch">
                                        <option value="">Select Batch</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Stk</label>
                                    <input type="number" step="any" class="form-control" id="add_stock" readonly>
                                    <input type="hidden" id="add_inventory_id">       
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Qty</label>
                                    <input type="number" step="any" class="form-control" id="add_quantity">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price">
                                </div>
                            </div>
                            <div class="col-md-1">
                                <div class="form-group">
                                    <button type="button" style="margin-top: 31px;" id="add_new_btn"
                                            class="btn btn-info bg-gradient-info btn-sm btn-block"><i
                                            class="fa fa-plus"></i></button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                    <thead>
                                    <tr>
                                        <th class="text-center" width="5%">S/L</th>
                                        <th class="text-center">Item Details</th>
                                        <th class="text-center" width="20%">Select Batch <span class="text-danger">*</span></th>
                                        <th class="text-center" width="10%">Qty</th>
                                        <th class="text-center" width="15%">Unit Price</th>
                                        <th class="text-center" width="15%">Total Price</th>
                                        <th class="text-center" width="5%"></th>
                                    </tr> 
                                    </thead>
                                    <tbody id="product-container">
                                    @if (old('product_id') != null && sizeof(old('product_id')) > 0)
                                        @foreach(old('product_id') as $key => $productId)
                                            <tr class="product-item">
                                                <td class="text-center">
                                                    <span class="product_sl">{{ $loop->iteration }}</span>
                                                </td>
                                                <td class="text-left">
                                                    <span class="product_name">{{ old('product_name')[$key] ?? '' }}</span>
                                                    <input type="hidden" name="item_id[]" value="{{ old('item_id')[$key] ?? '' }}">
                                                    <input type="hidden" name="product_id[]" value="{{ $productId }}" class="product_id">
                                                    <input type="hidden" name="product_name[]" value="{{ old('product_name')[$key] ?? '' }}">
                                                    <input type="hidden" name="product_code[]" value="{{ old('product_code')[$key] ?? '' }}" class="product_code">
                                                </td>
                                                <td>
                                                    <select name="inventory_id[]" class="form-control select2 select_batch_row" data-selected-id="{{ old('inventory_id')[$key] ?? '' }}" required>
                                                        <option value="">Select Batch</option>
                                                    </select>
                                                    <span class="text-info stock_info_row" style="font-size: 11px;"></span>
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" value="{{ old('product_quantity')[$key] ?? 0 }}" class="form-control text-right product_quantity" name="product_quantity[]">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" value="{{ old('product_unit_price')[$key] ?? 0 }}" class="form-control text-right product_unit_price" name="product_unit_price[]">
                                                </td>
                                                <td class="text-right total-sale-cost">{{ number_format((old('product_quantity')[$key] ?? 0) * (old('product_unit_price')[$key] ?? 0), 2) }}</td>
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
                                                    <input type="hidden" name="product_id[]" value="{{ $item->product_id }}" class="product_id">
                                                    <input type="hidden" name="product_name[]" value="{{ $item->product->name }}">
                                                    <input type="hidden" name="product_code[]" value="{{ $item->product_code }}" class="product_code">
                                                </td>
                                                <td>
                                                    <select name="inventory_id[]" class="form-control select2 select_batch_row" required>
                                                        <option value="">Select Batch</option>
                                                    </select>
                                                    <span class="text-info stock_info_row" style="font-size: 11px;"></span>
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
                                        <th colspan="3"></th>
                                        <th class="text-right" id="total_quantity"></th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right">
                                            <input type="number" step="any" readonly name="sub_total" id="sub_total" value="{{ old('sub_total', $sale->sub_total) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="5" class="text-right">
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
                                    <input type="hidden" id="vat_amount" name="vat_amount" value="{{ old('vat_amount', $sale->vat_amount) }}">
                                    <tr class="footer_area">
                                        <th colspan="5" class="text-right">Total Receivable</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="total_receivable" name="total_receivable" value="{{ old('total_receivable', $sale->total) }}" class="form-control text-right">
                                            <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_amount', $sale->total) }}">       
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr class="footer_area" id="advance_row" style="display: {{ $availableAdvance > 0 ? '' : 'none' }};">
                                        <th colspan="5" class="text-right text-success">
                                            <div class="d-inline-block">
                                                <input type="hidden" name="advance_adjust" id="advance_adjust" value="1">
                                                <label class="cursor-pointer text-success">Advance Adjusted (Bal: <span id="advance_amount_text">{{ number_format($availableAdvance, 2) }}</span>)</label>
                                            </div>
                                        </th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="advance_amount" name="advance_amount" class="form-control text-right text-success font-weight-bold" value="{{ old('advance_amount', 0) }}">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="5" class="text-right">New Payment</th>
                                        <th class="text-right">
                                            <input type="number" step="any" id="new_payment" name="new_payment" value="{{ old('new_payment', 0) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="5" class="text-right">Total Paid</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="total_paid" name="total_paid" value="{{ old('total_paid', $sale->paid) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="5" class="text-right">Due</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="due_amount" name="due_amount" value="{{ old('due_amount', $sale->due) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row"> 
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Warehouse <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="warehouse" name="warehouse" required>
                                        @foreach($warehouses as $warehouse)
                                            <option {{ old('warehouse', $sale->warehouse_id) == $warehouse->id ? 'selected' : '' }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Delivery Date</label>
                                    <input type="text" autocomplete="off" value="{{ old('date', date('d-m-Y')) }}" name="date" class="form-control date-picker">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Customer</label>
                                    <input type="text" readonly value="{{ $sale->customer->name }}" class="form-control">
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
                                <div class="form-group {{ $errors->has('memo_no') ? 'has-error' :'' }}">
                                    <label for="memo_no">Memo No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('memo_no') }}" name="memo_no" id="memo_no" class="form-control" placeholder="Enter Memo No.">
                                    @error('memo_no')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <input type="text" value="{{ old('notes') }}" name="notes" id="notes" class="form-control" placeholder="Enter Notes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" id="btn-save-delivery" class="btn btn-success bg-gradient-success btn-sm">Confirm Delivery</button>
                        <a href="{{ route('pre-orders.index') }}" class="btn btn-danger btn-sm float-right">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
        
    </form>

    <template id="template-product">
        <tr class="product-item">
            <td class="text-center"><span class="product_sl"></span></td>
            <td class="text-left">
                <span class="product_name"></span>
                <input type="hidden" name="item_id[]" value="">
                <input type="hidden" name="product_id[]" class="product_id">
                <input type="hidden" name="product_name[]" class="product_name_input">
                <input type="hidden" name="product_code[]" class="product_code">
            </td>
            <td>
                <select name="inventory_id[]" class="form-control select_batch_row" required>
                    <option value="">Select Batch</option>
                </select>
                <span class="text-info stock_info_row" style="font-size: 11px;"></span>
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]">
            </td>
            <td class="text-right total-sale-cost"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>

    <!-- Delivery Overview Modal -->
    <div class="modal fade" id="modal-sale-overview" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fa fa-shopping-cart"></i> Delivery Summary & Overview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px;">
                        <div class="col-md-4 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Customer Name:</strong>
                                <span class="text-dark font-weight-bold ml-1">{{ $sale->customer->name ?? '-' }}</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-user-tie text-info mr-1"></i> SR Name:</strong>
                                <span class="text-dark ml-1">{{ $sale->sr->name ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4 border-right pl-md-3">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-building text-info mr-1"></i> Warehouse:</strong>
                                <span id="ov-warehouse" class="text-dark ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-credit-card text-info mr-1"></i> Payment Method:</strong>
                                <span id="ov-payment-method" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-4 pl-md-3">
                            <strong><i class="fa fa-calendar-alt text-info mr-1"></i> Delivery Date:</strong>
                            <div id="ov-date" class="text-dark mt-1 font-weight-bold">
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
                                    <th>Batch</th>
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
                                    <th class="text-right">Advance Adjusted:</th>
                                    <td class="text-right font-weight-bold" id="ov-advance" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-primary">
                                    <th class="text-right">New Payment:</th>
                                    <td class="text-right font-weight-bold" id="ov-new-payment" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-warning">
                                    <th class="text-right">Due Amount:</th>
                                    <td class="text-right font-weight-bold" id="ov-due" style="font-size: 16px;">0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Delivery</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-submit"><i class="fa fa-check-circle"></i> Confirm Delivery</button>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('script')
    <script>
        $(function () {
            calculate();

            // Load batches for existing rows
            $('.product-item').each(function() {
                let productId = $(this).find('.product_id').val();
                let batchSelect = $(this).find('.select_batch_row');
                let selectedId = batchSelect.data('selected-id');
                loadBatches(productId, batchSelect, selectedId);
            });

            $('body').on('change', '#warehouse', function () {
                $('.product-item').each(function() {
                    let productId = $(this).find('.product_id').val();
                    let batchSelect = $(this).find('.select_batch_row');
                    let selectedId = batchSelect.data('selected-id');
                    loadBatches(productId, batchSelect, selectedId);
                });
                $('#select_batch').html('<option value="">Select Batch</option>');
                $('#add_stock').val('');
                $('#add_inventory_id').val('');
            });

            $('body').on('change', '#select_product', function (e) {
                let productId = $(this).val();
                let price = $(this).find(':selected').data('price');
                $('#add_unit_price').val(price);
                loadBatches(productId, $('#select_batch'));
            });

            $('body').on('change', '#select_batch', function (e) {
                let inventoryId = $(this).val();
                if (inventoryId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: { inventoryId: inventoryId }
                    }).done(function (response) {
                        $('#add_stock').val(response.inventory.quantity ?? 0);
                        $('#add_inventory_id').val(response.inventory.id ?? 0);
                        $('#add_unit_price').val(response.inventory.selling_price ?? 0);
                    });
                }
            });

            $('body').on('change', '.select_batch_row', function (e) {
                let inventoryId = $(this).val();
                let currentSelect = $(this);
                let stockInfoSpan = $(this).closest('td').find('.stock_info_row');

                if (inventoryId != '') {
                    // Duplicate Batch Check
                    let exists = false;
                    $('.select_batch_row').not(currentSelect).each(function() {
                        if ($(this).val() == inventoryId) {
                            exists = true;
                        }
                    });

                    if (exists) {
                        ajaxErrorMessage('This batch is already selected in another row.');
                        currentSelect.val('').trigger('change');
                        return false;
                    }

                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: { inventoryId: inventoryId }
                    }).done(function (response) {
                        stockInfoSpan.text('Stock: ' + (response.inventory.quantity ?? 0));
                    });
                } else {
                    stockInfoSpan.text('');
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let productId = $('#select_product').val();
                let inventoryId = $('#add_inventory_id').val();
                let qty = $('#add_quantity').val();
                let stock = $('#add_stock').val();
                let price = $('#add_unit_price').val();
                let productCode = $('#select_product option:selected').data('product_code');

                if (productId == '') {
                    ajaxErrorMessage('Please, select product !');
                    return false;
                }
                if (inventoryId == '') {
                    ajaxErrorMessage('Please, select batch !');
                    return false;
                }
                if (qty == '' || qty <= 0) {
                    ajaxErrorMessage('Please, type valid product quantity !');
                    return false;
                }
                if (parseFloat(qty) > parseFloat(stock)) {
                    ajaxErrorMessage('Quantity cannot be greater than stock !');
                    return false;
                }
                if (price == '' || price < 0) {
                    ajaxErrorMessage('Please, enter valid product price !');
                    return false;
                }

                // Duplicate Batch Check
                let exists = false;
                $('.select_batch_row').each(function() {
                    if ($(this).val() == inventoryId) {
                        exists = true;
                    }
                });

                if (exists) {
                    ajaxErrorMessage('This batch is already added to the list.');
                    return false;
                }

                if (productId != '' && inventoryId != '' && qty != '') {
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.find('.product_name').text($("#select_product option:selected").text());
                    item.find('.product_name_input').val($("#select_product option:selected").text());
                    item.find('.product_id').val(productId);
                    item.find('.product_code').val(productCode);
                    item.find('.product_quantity').val(qty);
                    item.find('.product_unit_price').val(price);
                    
                    let batchSelect = item.find('.select_batch_row');
                    batchSelect.select2();
                    loadBatches(productId, batchSelect, inventoryId);

                    calculate();
                    $('#select_product').val('').change();
                    $('#select_batch').html('<option value="">Select Batch</option>');
                    $('#add_quantity').val('');
                    $('#add_unit_price').val('');
                    $('#add_stock').val('');
                    $('#add_inventory_id').val('');
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
        })

        function loadBatches(productId, selectElement, selectedId = null) {
            let warehouseId = $('#warehouse').val();
            if (productId != '') {
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_product_batch') }}",
                    data: {id: productId, warehouseId: warehouseId}
                }).done(function (response) {
                    selectElement.html('<option value="">Select Batch</option>');
                    if (Array.isArray(response)) {
                        response.forEach(function (inventory) {
                            let selected = (selectedId == inventory.id) ? 'selected' : '';
                            selectElement.append(`<option ${selected} value="${inventory.id}">Batch-${inventory.batch_no} (Stock: ${inventory.quantity})</option>`);
                        });
                        if (selectedId) {
                           selectElement.trigger('change');
                        }
                    }
                });
            }
        }

        function calculate() {
            var productSl = 1;
            var rows = $("#product-container .product-item");
            rows.each(function () {
                $(this).find('.product_sl').text(productSl++);
            });
            var total_quantity = 0;
            var sub_total = 0;
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
            
            let advance_paid = 0;
            if ({{ $availableAdvance }} > 0) {
                advance_paid = Math.min({{ $availableAdvance }}, total_receivable);
                $('#advance_amount').val(advance_paid.toFixed(2));
            } else {
                $('#advance_amount').val(0);
            }
            
            let new_payment = parseFloat($('#new_payment').val()) || 0;
            let total_paid = advance_paid + new_payment;
            
            $('#total_paid').val(total_paid.toFixed(2));
            
            let due_amount = total_receivable - total_paid;
            $('#due_amount').val(due_amount.toFixed(2));
        }

        // Trigger Overview Modal on Save
        $('#btn-save-delivery').click(function(e) {
            e.preventDefault();

            // Validate form first
            let form = document.getElementById('delivery-form');
            if (!form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Check if at least one product added
            if ($('.product-item').length === 0) {
                alert('Please add at least one product.');
                return;
            }

            // Verify all items have a selected batch
            let valid = true;
            $('.product-item').each(function() {
                let batch = $(this).find('.select_batch_row').val();
                if (!batch) {
                    alert('Please select a batch for all products.');
                    valid = false;
                    return false;
                }
            });
            if(!valid) return;

            // Clear previous rows
            $('#overview-items-body').empty();

            // Populate product table in modal
            let sl = 1;
            $('.product-item').each(function() {
                let name = $(this).find('input[name="product_name[]"]').val() || $(this).find('.product_name').text();
                let batchText = $(this).find('.select_batch_row option:selected').text().split(' ')[0] || 'N/A';
                let qty = parseFloat($(this).find('.product_quantity').val() || 0);
                let price = parseFloat($(this).find('.product_unit_price').val() || 0);
                let rowTotal = qty * price;
                
                let html = `
                    <tr>
                        <td>${sl++}</td>
                        <td class="text-left">${name}</td>
                        <td>${batchText}</td>
                        <td>${qty}</td>
                        <td class="text-right">${price.toFixed(2)}</td>
                        <td class="text-right">${rowTotal.toFixed(2)}</td>
                    </tr>
                `;
                $('#overview-items-body').append(html);
            });

            // Populate Metadata
            let warehouseText = $('#warehouse option:selected').text() || 'N/A';
            let dateText = $('input[name="date"]').val() || 'N/A';
            let paymentMethodText = $('#payment_method option:selected').val() ? $('#payment_method option:selected').text().trim() : 'N/A';

            $('#ov-warehouse').text(warehouseText);
            $('#ov-date').text(dateText);
            $('#ov-payment-method').text(paymentMethodText);

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

            let advanceAdjusted = parseFloat($('#advance_amount').val() || 0);
            $('#ov-advance').text(advanceAdjusted.toFixed(2));

            $('#ov-new-payment').text(parseFloat($('#new_payment').val() || 0).toFixed(2));
            $('#ov-due').text(parseFloat($('#due_amount').val() || 0).toFixed(2));

            // Show modal
            $('#modal-sale-overview').modal('show');
        });

        // Confirm & submit form
        $('#btn-confirm-submit').click(function() {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            $('#delivery-form').submit();
        });


    </script>
@endsection
