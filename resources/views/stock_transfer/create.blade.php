@extends('layouts.app')
@section('title','Stock Transfer Create')
@section('style')
    <style>
        .input-group.search-bar {
            position: relative;
        }
        .search-icon {
            position: absolute;
            right: 7px;
            bottom: 3px;
            color: #7122f3;
            font-size: 19px;
            z-index: 9;
        }

    </style>
@endsection
@section('content')
    <form action="{{ route('stock-transfers.store') }}" method="post" id="form-stock-transfer">
        @csrf
    <div class="row">
        <!-- 1. Top Card: Transfer Information (Full Width) -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Transfer Information</div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Date -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date">Date <span class="text-danger">*</span></label>
                                <input type="text" autocomplete="off" value="{{ old('date',date('d-m-Y')) }}" name="date" id="date" class="form-control business-date-picker" placeholder="Enter date">
                                <span class="text-danger error-message" id="date-error"></span>
                            </div>
                        </div>
                        <!-- From Warehouse -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="from_branch">From Branch <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="from_branch" name="from_branch">
                                    <option value="">Select From Branch</option>
                                    @foreach($fromBranches as $fromBranch)
                                        <option {{ old('from_branch', request('from_branch')) == $fromBranch->id ? 'selected' : ''  }} value="{{ $fromBranch->id }}">{{ $fromBranch->sisterConcern->name ?? '' }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- To Warehouse -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="to_branch">To Branch <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="to_branch" name="to_branch">
                                    <option value="">Select To Branch</option>
                                    @foreach($toBranches as $toBranch)
                                        <option {{ old('to_branch') == $toBranch->id ? 'selected' : ''  }} value="{{ $toBranch->id }}">{{ $toBranch->sisterConcern->name ?? '' }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-message" id="to_branch-error"></span>
                            </div>
                        </div>
                        <!-- Vehicle No -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="vehicle_no">Vehicle No</label>
                                <input type="text" value="{{ old('vehicle_no') }}" name="vehicle_no" id="vehicle_no" class="form-control" placeholder="Enter Vehicle No">
                                <span class="text-danger error-message" id="vehicle_no-error"></span>
                            </div>
                        </div>
                        <!-- Driver Name -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="driver_name">Driver Name</label>
                                <input type="text" value="{{ old('driver_name') }}" name="driver_name" id="driver_name" class="form-control" placeholder="Enter Driver Name">
                                <span class="text-danger error-message" id="driver_name-error"></span>
                            </div>
                        </div>
                        <!-- Driver Phone -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="driver_phone">Driver Phone</label>
                                <input type="text" value="{{ old('driver_phone') }}" name="driver_phone" id="driver_phone" class="form-control" placeholder="Enter Driver Phone">
                                <span class="text-danger error-message" id="driver_phone-error"></span>
                            </div>
                        </div>
                        <!-- Notes -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="notes">Notes</label>
                                <input type="text" value="{{ old('notes') }}" name="notes" id="notes" class="form-control" placeholder="Enter Notes">
                                <span class="text-danger error-message" id="notes-error"></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Bottom Card: Stocks Information (Full Width) -->
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Stocks Information</div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <!-- Product Selection -->
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="select_product">Product <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="select_product" name="select_product">
                                    <option value="">Search Name of Product</option>
                                    @foreach($products as $product)
                                        <option {{ old('product') == $product->id ? 'selected' : ''  }} value="{{ $product->id }}">
                                            {{ $product->name }}    
                                            @if($product->brand)
                                                - Brand: {{ $product->brand->name ?? '' }}
                                            @endif
                                            @if($product->category)
                                                - Category: {{ $product->category->name ?? '' }}
                                            @endif
                                             - Stock: {{ $product->getStockInWarehouse(request('from_branch')) ?? 0 }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <!-- Batch Selection -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="select_batch">Batch <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="select_batch" name="select_batch">
                                    <option value="">Select Batch</option>
                                </select>
                                <div class="mt-1">
                                    <small id="product_total_stock" class="text-info d-block" style="font-weight: bold;"></small>
                                    <small id="available_stock_display" class="text-success d-block" style="font-weight: bold;"></small>
                                </div>
                            </div>
                        </div>
                        <!-- Cost Price (Readonly) -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="select_cost_price">Cost Price</label>
                                <input type="number" readonly class="form-control" id="select_cost_price" placeholder="Cost Price">
                            </div>
                        </div>
                        <!-- Transfer Cost -->
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="add_transfer_cost">Transfer Cost</label>
                                <input type="number" step="any" class="form-control" id="add_transfer_cost" placeholder="Transfer Cost" value="0">
                            </div>
                        </div>
                        <!-- Qty -->
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="add_quantity">Qty <span class="text-danger">*</span></label>
                                <input type="number" step="any" class="form-control" id="add_quantity" placeholder="Qty">
                            </div>
                        </div>
                        <!-- Add Button -->
                        <div class="col-md-1">
                            <div class="form-group">
                                <button type="button" style="margin-top: 31px;" id="add_new_btn" class="btn btn-info bg-gradient-info btn-sm btn-block">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center" width="4%">S/L</th>
                            <th class="text-center">Item Details</th>
                            <th class="text-center" width="8%">Qty</th>
                            <th class="text-center" width="10%">Cost Price</th>
                            <th class="text-center" width="12%">Transfer Price <span class="text-danger">*</span></th>
                            <th class="text-center" width="12%">Total (Transfer)</th>
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
                                        <span class="product_name">{{ old('product_name_val.'.$loop->index) }}</span>
                                        <input type="hidden" name="product_name_val[]" value="{{ old('product_name_val.'.$loop->index) }}" class="product_name_val">
                                        <input type="hidden" name="product_id[]" value="{{ old('product_id.'.$loop->index) }}" class="product_id">
                                        <input type="hidden" name="inventory_id[]" value="{{ old('inventory_id.'.$loop->index) }}" class="inventory_id">
                                    </td>
                                    <td class="text-right">
                                        <div class="form-group mb-0">
                                            <input type="number" step="any" value="{{ old('product_qty.'.$loop->index) }}" class="form-control text-right product_qty" name="product_qty[]">
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="form-group mb-0">
                                            <input type="number" readonly step="any" value="{{ old('product_unit_price.'.$loop->index) }}" class="form-control text-right product_unit_price" name="product_unit_price[]">
                                        </div>
                                    </td>
                                    <td class="text-right">
                                        <div class="form-group mb-0">
                                            <input type="number" step="any" value="{{ old('transfer_price.'.$loop->index) }}" class="form-control text-right transfer_price" name="transfer_price[]" placeholder="Transfer Price">
                                        </div>
                                    </td>
                                    <td class="text-right total-purchase-cost"></td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove">
                                            <i class="fa fa-trash-alt"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                        </tbody>
                        <tfoot>
                        <tr style="{{ (old('product_id') != null && sizeof(old('product_id'))) > 0 ? 'display: revert' : 'display: none' }}" id="footer_area">
                            <th colspan="2" class="text-right">Total</th>
                            <th class="text-right" id="total_quantity"></th>
                            <th class="text-right" id="total_unit_price"></th>
                            <th class="text-right" id="total_transfer_price"></th>
                            <th class="text-right" id="total_purchase_price"></th>
                            <th></th>
                        </tr>
                        </tfoot>
                    </table>
                </div>
                <div class="card-footer">
                    <button type="button" id="submit-stock-transfer" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                    <a href="{{ route('stock-transfers.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                </div>
            </div>
        </div>
    </div>
    </form>

    <!-- Stock Transfer Overview Modal -->
    <div class="modal fade" id="stockTransferOverviewModal" tabindex="-1" role="dialog" aria-labelledby="stockTransferOverviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title" id="stockTransferOverviewModalLabel"><i class="fa fa-list-alt"></i> Stock Transfer Overview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-3"><strong>From Warehouse:</strong> <span class="text-primary" id="ov-from-warehouse">N/A</span></div>
                        <div class="col-md-3"><strong>To Warehouse:</strong> <span class="text-primary" id="ov-to-warehouse">N/A</span></div>
                        <div class="col-md-2"><strong>Date:</strong> <span class="text-primary" id="ov-date">N/A</span></div>
                        <div class="col-md-4"><strong>Vehicle:</strong> <span class="text-primary" id="ov-vehicle">N/A</span></div>
                    </div>
                    <div class="row mb-3 pb-3 border-bottom">
                        <div class="col-md-3"><strong>Driver Name:</strong> <span class="text-primary" id="ov-driver-name">N/A</span></div>
                        <div class="col-md-3"><strong>Driver Phone:</strong> <span class="text-primary" id="ov-driver-phone">N/A</span></div>
                        <div class="col-md-6"><strong>Notes:</strong> <span class="text-muted" id="ov-notes">N/A</span></div>
                    </div>

                    <div class="table-responsive mb-3">
                        <table class="table table-bordered table-sm table-striped">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">S/L</th>
                                    <th>Item Details</th>
                                    <th width="10%">Qty</th>
                                    <th width="12%">Cost Price</th>
                                    <th width="12%">Transfer Price</th>
                                    <th width="15%" class="text-right">Total (Transfer)</th>
                                </tr>
                            </thead>
                            <tbody id="overview-items-body">
                                <!-- Dynamic Items -->
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Total Value:</th>
                                    <th class="text-right text-success font-weight-bold" id="ov-total-value">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Review Again</button>
                    <button type="button" class="btn btn-info bg-gradient-info" id="confirm-stock-transfer-submit"><i class="fa fa-check-circle"></i> Confirm & Save</button>
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
                <input type="hidden" name="inventory_id[]" class="inventory_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right product_qty" name="product_qty[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" readonly step="any" class="form-control text-right product_unit_price" name="product_unit_price[]">
                </div>
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right transfer_price" name="transfer_price[]" placeholder="Transfer Price">
                </div>
            </td>
            <td class="text-right total-purchase-cost"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i
                        class="fa fa-trash-alt"></i></button>
            </td>
        </tr>
    </template>
@endsection
@section('script')
    <script>

        $(function (){

            calculate();
            var productIds = [];

            $('#from_branch').on('change', function () {
                let from_branch = $(this).val();
                let currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('from_branch', from_branch);
                window.location.href = currentUrl.toString();
            });

            var productCodes = [];
            $( ".product_code" ).each(function( index ) {
                if ($(this).val() != '') {
                    productCodes.push($(this).val());
                }
            });

            $('body').on('keypress', '#add_quantity', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            // Fetch batches when product is selected
            $('#select_product').on('change', function () {
                let selectProduct = $(this).val();
                let warehouseId = $('#from_branch').val();
                let batchSelect = $('#select_batch');
                let stockDisplay = $('#available_stock_display');
                let totalStockDisplay = $('#product_total_stock');
                let costPriceInput = $('#select_cost_price');

                batchSelect.html('<option value="">Select Batch</option>');
                stockDisplay.text(''); // clear previous
                totalStockDisplay.text('');
                costPriceInput.val('');

                if (selectProduct != '' && warehouseId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_batch') }}", 
                        data: {id: selectProduct, warehouseId: warehouseId}
                    }).done(function (response) {
                        if (response.length > 0) {
                            let totalStock = 0;
                            $.each(response, function (index, item) {
                                let itemStock = parseFloat(item.quantity) || 0;
                                totalStock += itemStock;
                                batchSelect.append('<option data-price="'+item.unit_price+'" data-stock="'+item.quantity+'" value="' + item.id + '">Batch-' + (item.batch_no ? item.batch_no : 'N/A') + ' (Qty: ' + item.quantity + ')</option>');
                            });
                            totalStockDisplay.text('Product Total Stock: ' + totalStock);
                        } else {
                            totalStockDisplay.html('<span class="text-danger">No Stock Available</span>');
                        }
                    });
                }
            });

            // Show available stock and cost price when batch is selected
            $('#select_batch').on('change', function () {
                let selectedOption = $(this).find(':selected');
                let stock = selectedOption.data('stock');
                let price = selectedOption.data('price');
                let stockDisplay = $('#available_stock_display');
                let costPriceInput = $('#select_cost_price');
                if (stock !== undefined && $(this).val() !== '') {
                    stockDisplay.text('Selected Batch Stock: ' + stock);
                    stockDisplay.data('stock', stock);
                    costPriceInput.val(parseFloat(price).toFixed(2));
                } else {
                    stockDisplay.text('');
                    stockDisplay.data('stock', 0);
                    costPriceInput.val('');
                }
            });


            $('body').on('click', '#add_new_btn', function (e) {

                //not need requisition
                let projectId = $('#from_branch').val();
                let selectProduct = $('#select_product').val();
                let selectBatch = $('#select_batch').val(); // inventory_id
                let addQuantity = $('#add_quantity').val();
                let selectProductName = $("#select_product option:selected").text().trim();
                let batchName = $("#select_batch option:selected").text().trim();

                if (selectProduct == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select product !',
                    });
                    return false;
                }
                if (selectBatch == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select batch !',
                    });
                    return false;
                }
                if (addQuantity == '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type product quantity !',
                    });
                    return false;
                }

                // Use the inventory_id (selectBatch) to check duplicates instead of product_id
                if ($.inArray(selectBatch, productIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectProductName + ' (' + batchName + ') already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectProduct != '' && selectBatch != '' && addQuantity != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: {inventoryId : selectBatch}
                    }).done(function (response) {
                        if (response.status){
                            let availableQuantity = parseFloat(response.inventory.quantity);
                            let requestedQuantity = parseFloat(addQuantity);

                            // Check if quantity is available
                            if (requestedQuantity > availableQuantity) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Insufficient Stock',
                                    text: 'You cannot transfer more than ' + availableQuantity + ' units from this batch.',
                                });
                                let notificationSound = document.getElementById('notification-error-audio');
                                if (notificationSound) {
                                    notificationSound.play();
                                }
                                return false;
                            }

                            let addUnitPrice = parseFloat(response.inventory.unit_price) || 0;
                            let addTransferCost = parseFloat($('#add_transfer_cost').val()) || 0;
                            let calculatedTransferPrice = addUnitPrice + addTransferCost;
                            
                            var addMoreSound = document.getElementById("add_more_sound");
                            if (addMoreSound) addMoreSound.play();
                            var html = $('#template-product').html();
                            var itemHtml = $(html);
                            $('#product-container').prepend(itemHtml);
                            var item = $('.product-item').first();
                            item.hide();
                            item.closest('tr').find('.product_name').text(selectProductName + ' (' + batchName + ')');
                            item.closest('tr').find('.product_name_val').val(selectProductName + ' (' + batchName + ')');
                            item.closest('tr').find('.product_id').val(selectProduct);
                            item.closest('tr').find('.inventory_id').val(selectBatch);
                            item.closest('tr').find('.product_qty').val(addQuantity);
                            item.closest('tr').find('.product_unit_price').val(addUnitPrice);
                            item.closest('tr').find('.transfer_price').val(calculatedTransferPrice);
                            productIds.push(selectBatch);
                            item.show();
                            calculate();
                            $('#add_quantity').val('');
                            $('#add_transfer_cost').val('0');
                            $('#select_batch').val('').trigger('change');
                        }else{
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.message ?? 'Stock details not found',
                            });
                            return false;
                        }
                    });
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var inventory_id = $(this).closest('tr').find('.inventory_id').val();

                var removeItem = document.getElementById("remove_sound");
                if (removeItem) {
                    removeItem.play();
                }

                $(this).closest('tr').remove();
                calculate();
                productIds = $.grep(productIds, function (value) {
                    return value != inventory_id;
                });
            });
            $('body').on('keyup', 'input[type="number"]', function () {
                calculate();
            });
            $('body').on('change', 'input[type="number"]', function () {
                calculate();
            });

            $("#submit-stock-transfer").click(function (e){
                e.preventDefault();

                // Validate form first
                let form = document.getElementById('form-stock-transfer');
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return false;
                }

                // Validation: Ensure at least one product is added
                if ($('.product-item').length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Products Added',
                        text: 'Please add at least one product to the transfer list.',
                    });
                    return false;
                }

                // Pricing Validation: Ensure Transfer Price is not less than Cost Price
                let hasError = false;
                $('.product-item').each(function (i, obj) {
                    let transfer_price = parseFloat($('.transfer_price:eq(' + i + ')').val());
                    let unit_price = parseFloat($('.product_unit_price:eq(' + i + ')').val());
                    if (!isNaN(transfer_price) && !isNaN(unit_price) && transfer_price > 0 && transfer_price < unit_price) {
                        hasError = true;
                    }
                });

                if (hasError) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Pricing',
                        text: 'Transfer price cannot be less than cost price.',
                    });
                    return false;
                }

                // Clear and Populate Modal Table Body
                let overviewBody = $('#overview-items-body');
                overviewBody.empty();

                let rows = $("#product-container .product-item");
                rows.each(function(index) {
                    let productSl = $(this).find('.product_sl').text().trim();
                    let productName = $(this).find('.product_name_val').val() || $(this).find('.product_name').text().trim();
                    let qty = parseFloat($(this).find('.product_qty').val() || 0);
                    let unitPrice = parseFloat($(this).find('.product_unit_price').val() || 0);
                    let transferPrice = parseFloat($(this).find('.transfer_price').val() || 0);
                    let rowTotal = parseFloat($(this).find('.total-purchase-cost').text() || 0);

                    let rowHtml = `
                        <tr>
                            <td>${productSl}</td>
                            <td class="text-left font-weight-bold">${productName}</td>
                            <td class="font-weight-bold">${qty}</td>
                            <td>${unitPrice.toFixed(2)}</td>
                            <td>${transferPrice.toFixed(2)}</td>
                            <td class="text-right font-weight-bold">${rowTotal.toFixed(2)}</td>
                        </tr>
                    `;
                    overviewBody.append(rowHtml);
                });

                // Populate Metadata
                let fromWarehouseText = $('#from_branch option:selected').val() ? $('#from_branch option:selected').text().trim() : 'N/A';
                let toWarehouseText = $('#to_branch option:selected').val() ? $('#to_branch option:selected').text().trim() : 'N/A';
                let dateText = $('#date').val() || 'N/A';
                let vehicleText = $('#vehicle_no').val() ? $('#vehicle_no').val().trim() : 'N/A';
                let driverNameText = $('#driver_name').val() ? $('#driver_name').val().trim() : 'N/A';
                let driverPhoneText = $('#driver_phone').val() ? $('#driver_phone').val().trim() : 'N/A';
                let notesText = $('#notes').val() ? $('#notes').val().trim() : 'No notes added';

                $('#ov-from-warehouse').text(fromWarehouseText);
                $('#ov-to-warehouse').text(toWarehouseText);
                $('#ov-date').text(dateText);
                $('#ov-vehicle').text(vehicleText);
                $('#ov-driver-name').text(driverNameText);
                $('#ov-driver-phone').text(driverPhoneText);
                $('#ov-notes').text(notesText);

                // Financials
                let totalValue = parseFloat($('#total_purchase_price').text() || 0);
                $('#ov-total-value').text(totalValue.toFixed(2));

                // Show Modal
                $('#stockTransferOverviewModal').modal('show');
            });

            $('body').on('click', '#confirm-stock-transfer-submit', function() {
                $('#stockTransferOverviewModal').modal('hide');
                $('.error-message').text('');
                preloaderToggle(true);
                let formData = new FormData($('#form-stock-transfer')[0]);
                $.ajax({
                    type: 'POST',
                    url: $('#form-stock-transfer').attr('action'),
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            ajaxSuccessMessage(response.message)
                            location.href = response.redirect_url;
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
                        } else {
                            ajaxErrorMessage('Something went wrong. Please try again.');
                        }
                    }
                });
            });


        })
        function calculate() {
            var productSl = 1;
            var rows = $("#product-container .product-item");
            rows.each(function () {
                $(this).find('.product_sl').text(productSl++);
            });

            var total_quantity = 0;
            var total_cost_amount = 0;
            var total_transfer_amount = 0;
            
            $('.product-item').each(function (i, obj) {

                let product_qty = parseFloat($('.product_qty:eq(' + i + ')').val());
                product_qty = (isNaN(product_qty) || product_qty < 0) ? 0 : product_qty;

                let unit_price = parseFloat($('.product_unit_price:eq(' + i + ')').val());
                unit_price = (isNaN(unit_price) || unit_price < 0) ? 0 : unit_price;

                let transfer_price = parseFloat($('.transfer_price:eq(' + i + ')').val());
                transfer_price = (isNaN(transfer_price) || transfer_price < 0) ? 0 : transfer_price;

                // Warn if transfer price < cost price
                let $tpInput = $('.transfer_price:eq(' + i + ')');
                if (transfer_price > 0 && transfer_price < unit_price) {
                    $tpInput.addClass('border-danger').attr('title', 'Transfer price cannot be less than cost price!');
                } else {
                    $tpInput.removeClass('border-danger').removeAttr('title');
                }

                let row_transfer_total = product_qty * transfer_price;
                let row_cost_total = product_qty * unit_price;
                
                $('.total-purchase-cost:eq(' + i + ')').text(row_transfer_total.toFixed(2));
                
                total_quantity += product_qty;
                total_cost_amount += row_cost_total;
                total_transfer_amount += row_transfer_total;
            });

            $('#total_quantity').text(total_quantity);
            $('#total_unit_price').text(''); // Leave cost price column total blank or use total_cost_amount if needed. Let's just leave unit price blank.
            $('#total_transfer_price').text(''); // Leave transfer price column total blank
            $('#total_purchase_price').text(total_transfer_amount.toFixed(2)); // Total Transfer Amount

            if (rows.length > 0) {
                $("#footer_area").show();
            } else {
                $("#footer_area").hide();
            }
        }

    </script>
@endsection
