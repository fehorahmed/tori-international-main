@extends('layouts.app')
@section('title','Add Product Utilize')
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
          action="{{ route('product-utilizes.store',['project'=>request('project'),'segment'=>request('segment')]) }}"
          class="form-horizontal" method="post">
        @csrf
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
                                    <label for="warehouse">Warehouse <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="warehouse" name="warehouse">
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option {{ $warehouse->sister_concern_id == auth()->user()->sister_concern_id ? 'selected' : ''  }} value="{{ $warehouse->id }}">
                                                {{ $warehouse->name  }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="select_product">Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_product" name="select_product">
                                        <option value="">Select Product</option>
                                        @php
                                            $allProducts = \App\Models\Settings\Product::where('status', 1)->get();
                                        @endphp
                                        @foreach($allProducts as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="select_batch">Batch <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_batch" name="select_batch">
                                        <option value="">Select Batch</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_quantity">Qty <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_quantity"
                                           placeholder="Qty">
                                    <span id="add_quantity_text"></span>
                                    <input type="hidden" id="add_unit_price">
                                    <input type="hidden" name="add_inventory_id" id="add_inventory_id">
                                </div>
                            </div>


                            <div class="col-md-2">
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
                                                        <input readonly type="number" step="any"
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
                                    <tr class="footer_area" style="{{ (old('product_id') != null && sizeof(old('product_id'))) > 0 ? 'display: revert' : 'display: none' }}"
                                        id="footer_area">
                                        <th colspan="2" class="text-right">Total</th>
                                        <th class="text-right" id="total_quantity"></th>
                                        <th class="text-right" id="total_unit_price"></th>
                                        <th class="text-right" id="total_purchase_price"></th>
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
                        <a href="{{ route('product-utilizes.index') }}"
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
                    <input type="number" readonly step="any" class="form-control text-right product_unit_price"
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

            $('body').on('keypress', '#add_quantity', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('change', '#select_product', function (e) {
                let warehouseId = $('#warehouse').val();
                let selectProductId = $(this).val();
                $('#select_batch').html('<option value="">Select Batch</option>');
                $('#add_unit_price').val('');
                $('#add_quantity_text').text('');
                $('#add_inventory_id').val('');

                if (selectProductId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_batch') }}",
                        data: {id: selectProductId, warehouseId: warehouseId}
                    }).done(function (response) {
                        if (Array.isArray(response)) {
                            response.forEach(function (inventory) {
                                $('#select_batch').append(
                                    `<option value="${inventory.id}">Batch-${inventory.batch_no}</option>`
                                );
                            });
                        }
                    });
                }
            });

            $('body').on('change', '#select_batch', function (e) {
                let selectBatchId = $(this).val();
                $('#add_unit_price').val('');
                $('#add_quantity_text').text('');
                $('#add_inventory_id').val('');

                if (selectBatchId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: { inventoryId: selectBatchId }
                    }).done(function (response) {
                        if (response.inventory) {
                            $('#add_quantity_text').html('<b>Stock: </b>' + response.inventory.quantity);
                            $('#add_unit_price').val(response.inventory.unit_price ?? 0);
                            $('#add_inventory_id').val(response.inventory.id);
                        }
                    });
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectProduct = $('#select_product').val();
                let selectProductName = $("#select_product option:selected").text();
                let selectBatchId = $('#select_batch').val();
                let selectBatchName = $("#select_batch option:selected").text();
                let addQuantity = parseFloat($('#add_quantity').val());
                let addUnitPrice = $('#add_unit_price').val();
                let inventoryId = $('#add_inventory_id').val();

                if (selectProduct == '') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, select product !' });
                    return false;
                }
                if (selectBatchId == '') {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, select batch !' });
                    return false;
                }
                if (isNaN(addQuantity) || addQuantity <= 0) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: 'Please, type valid quantity !' });
                    return false;
                }

                // Check if this specific batch for this product already exists in the list
                let exists = false;
                $('.inventory_id').each(function() {
                    if ($(this).val() == inventoryId) {
                        exists = true;
                    }
                });

                if (exists) {
                    Swal.fire({ icon: 'error', title: 'Oops...', text: selectProductName + ' (' + selectBatchName + ') already exist in list.' });
                    return false;
                }

                if (selectProduct != '' && addQuantity != '') {
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    
                    item.find('.product_name').text(selectProductName + ' (' + selectBatchName + ')');
                    item.find('.product_name_val').val(selectProductName + ' (' + selectBatchName + ')');
                    item.find('.product_id').val(selectProduct);
                    item.find('.inventory_id').val(inventoryId);
                    item.find('.product_qty').val(addQuantity);
                    item.find('.product_unit_price').val(addUnitPrice);
                    
                    calculate();
                    $('#add_quantity').val('');
                    $('#select_batch').val('').trigger('change');
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
        })

        function calculate() {
            var productSl = 1;
            var rows = $("#product-container .product-item");
            rows.each(function () {
                $(this).find('.product_sl').text(productSl++);
            });

            var total_quantity = 0;
            var total_purchase_price = 0;
            $('.product-item').each(function (i, obj) {
                let product_qty = parseFloat($(this).find('.product_qty').val());
                product_qty = (isNaN(product_qty) || product_qty < 0) ? 0 : product_qty;

                let product_unit_price = parseFloat($(this).find('.product_unit_price').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $(this).find('.total-purchase-cost').text((product_qty * product_unit_price).toFixed(2));
                total_quantity += product_qty;
                total_purchase_price += product_qty * product_unit_price;
            });

            $('#total_quantity').text(total_quantity);
            $('#total_purchase_price').text(total_purchase_price.toFixed(2));

            if (rows.length > 0) {
                $(".footer_area").show();
            } else {
                $(".footer_area").hide();
            }
        }
    </script>
@endsection
