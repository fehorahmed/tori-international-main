@extends('layouts.app')
@section('title', 'Edit Stock Conversion')
@section('style')
    <style>
        .table-responsive {
            overflow-x: visible !important;
        }
        .form-control[readonly] {
            background-color: #f4f6f9;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-edit mr-2"></i> Edit Stock Conversion: {{ $conversion->order_no }}</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('stock-conversions.update', $conversion->id) }}" method="POST" id="conversion-form">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="text" name="date" class="form-control date-picker" value="{{ \Carbon\Carbon::parse($conversion->date)->format('d-m-Y') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Warehouse <span class="text-danger">*</span></label>
                                    <select name="warehouse_id" id="warehouse_id" class="form-control select2" required>
                                        <option value="">Select Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ $conversion->warehouse_id == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->sisterConcern->name ?? '' }} - {{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <input type="text" name="notes" class="form-control" value="{{ $conversion->notes }}" placeholder="Enter notes here">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>From Product</label>
                                    <select id="select_product" class="form-control select2">
                                        <option value="">Select Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}"
                                                data-brand="{{ $product->brand->name ?? '' }}"
                                                data-category="{{ $product->category->name ?? '' }}"
                                            >{{ $product->name }}{{ $product->brand ? ' | Brand: '.$product->brand->name : '' }}{{ $product->category ? ' | Cat: '.$product->category->name : '' }} </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>To Product</label>
                                    <select id="to_product_select" class="form-control select2">
                                        <option value="">Target Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}{{ $product->brand ? ' | Brand: '.$product->brand->name : '' }}{{ $product->category ? ' | Cat: '.$product->category->name : '' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Batch / Inventory</label>
                                    <select id="select_batch" class="form-control select2">
                                        <option value="">Select Batch</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Quantity</label>
                                    <input type="number" id="add_quantity" class="form-control text-right" min="0.01" step="any">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Unit Conversion Cost</label>
                                    <input type="number" id="add_conversion_cost" class="form-control text-right" value="0" min="0" step="any">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" id="btn-add-product" class="btn btn-primary bg-gradient-primary btn-block">Add to List</button>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12 table-responsive">
                                <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>From Product</th>
                                            <th>Quantity</th>
                                            <th>From Unit Price</th>
                                            <th>To Product</th>
                                            <th>Unit Conversion Cost</th>
                                            <th>New Unit Price</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="product-container">
                                        @foreach($conversion->items as $item)
                                            <tr>
                                                <td>
                                                    {{ $item->fromProduct->name }} (Batch: {{ $item->fromInventory->batch_no ?? 'N/A' }})
                                                    <input type="hidden" name="from_product_id[]" value="{{ $item->from_product_id }}">
                                                    <input type="hidden" name="from_inventory_id[]" value="{{ $item->from_inventory_id }}">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" name="quantity[]" class="form-control text-right qty_input" value="{{ $item->quantity }}" readonly>
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control text-right old_unit_price" value="{{ $item->from_unit_price }}" readonly>
                                                </td>
                                                <td>
                                                    {{ $item->toProduct->name }}
                                                    <input type="hidden" name="to_product_id[]" value="{{ $item->to_product_id }}">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" name="unit_conversion_cost[]" class="form-control text-right conv_cost_input" value="{{ $item->unit_conversion_cost }}">
                                                </td>
                                                <td>
                                                    <input type="number" step="any" class="form-control text-right new_unit_price" value="{{ $item->to_unit_price }}" readonly>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm btn-remove" data-batch="{{ $item->from_inventory_id }}"><i class="fa fa-trash"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <button type="button" class="btn btn-success bg-gradient-success" id="btn-preview-save"><i class="fas fa-eye mr-2"></i> Preview & Update</button>
                            </div>
                        </div>
                    </form>

                    <!-- Conversion Overview Modal -->
                    <div class="modal fade" id="conversionOverviewModal" tabindex="-1" role="dialog" aria-labelledby="conversionOverviewModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-xl" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-success">
                                    <h5 class="modal-title text-white" id="conversionOverviewModalLabel"><i class="fas fa-exchange-alt mr-2"></i> Stock Conversion Overview</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="row mb-3 pb-3 border-bottom">
                                        <div class="col-md-3"><strong>Warehouse:</strong> <span class="text-primary" id="ov-warehouse">N/A</span></div>
                                        <div class="col-md-3"><strong>Date:</strong> <span class="text-primary" id="ov-date">N/A</span></div>
                                        <div class="col-md-6"><strong>Notes:</strong> <span class="text-muted" id="ov-notes">N/A</span></div>
                                    </div>
                                    <div class="table-responsive mb-3">
                                        <table class="table table-bordered table-sm table-striped">
                                            <thead class="bg-light">
                                                <tr>
                                                    <th>From Product (Batch)</th>
                                                    <th width="8%">Qty</th>
                                                    <th width="11%">Old Unit Price</th>
                                                    <th>To Product</th>
                                                    <th width="11%">Conv. Cost</th>
                                                    <th width="11%">New Unit Price</th>
                                                </tr>
                                            </thead>
                                            <tbody id="ov-items-body"></tbody>
                                        </table>
                                    </div>
                                </div>
                                <div class="modal-footer bg-light">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Review Again</button>
                                    <button type="button" class="btn btn-success bg-gradient-success" id="confirm-conversion-submit"><i class="fas fa-check-circle mr-1"></i> Confirm & Update</button>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            var productIds = [
                @foreach($conversion->items as $item)
                    '{{ $item->from_inventory_id }}',
                @endforeach
            ];

            $('#select_product').on('change', function() {
                var productId = $(this).val();
                if (productId) {
                    $.ajax({
                        url: "{{ route('get_product_batch') }}",
                        type: "GET",
                        data: { id: productId, warehouseId: $('#warehouse_id').val() },
                        success: function(response) {
                            $('#select_batch').empty().append('<option value="">Select Batch</option>');
                            if (response.length > 0) {
                                $.each(response, function(key, value) {
                                    $('#select_batch').append('<option value="' + value.id + '">Batch: ' + value.batch_no + ' (Stock: ' + value.quantity + ') Price: ' + value.unit_price + '</option>');
                                });
                            }
                        }
                    });
                } else {
                    $('#select_batch').empty().append('<option value="">Select Batch</option>');
                }
            });

            $('#btn-add-product').on('click', function() {
                let fromProduct = $('#select_product').val();
                let fromProductName = $('#select_product option:selected').text();
                let fromBatch = $('#select_batch').val();
                let fromBatchText = $('#select_batch option:selected').text();
                let qty = parseFloat($('#add_quantity').val());
                let toProduct = $('#to_product_select').val();
                let toProductName = $('#to_product_select option:selected').text();
                let conversionCost = parseFloat($('#add_conversion_cost').val()) || 0;

                if (!fromProduct || !fromBatch || !qty || !toProduct) {
                    Swal.fire('Error', 'Please fill all fields to add product', 'error');
                    return;
                }

                if (fromProduct === toProduct) {
                    Swal.fire('Error', 'From Product and To Product cannot be the same', 'error');
                    return;
                }

                if (productIds.includes(fromBatch)) {
                    Swal.fire('Error', 'This batch is already added to the list', 'error');
                    return;
                }

                $.ajax({
                    url: "{{ route('get_product_stock_details') }}",
                    type: "GET",
                    data: { inventoryId: fromBatch },
                    success: function(response) {
                        if (response.status) {
                            let availableQuantity = parseFloat(response.inventory.quantity);
                            if (qty > availableQuantity) {
                                Swal.fire('Error', 'Insufficient stock. Available: ' + availableQuantity, 'error');
                                return;
                            }

                            let oldUnitPrice = parseFloat(response.inventory.unit_price) || 0;
                            let newUnitPrice = oldUnitPrice + conversionCost;

                            let html = `
                                <tr>
                                    <td>
                                        ${fromProductName} (${fromBatchText})
                                        <input type="hidden" name="from_product_id[]" value="${fromProduct}">
                                        <input type="hidden" name="from_inventory_id[]" value="${fromBatch}">
                                    </td>
                                    <td>
                                        <input type="number" step="any" name="quantity[]" class="form-control text-right qty_input" value="${qty}" max="${availableQuantity}" readonly>
                                    </td>
                                    <td>
                                        <input type="number" step="any" class="form-control text-right old_unit_price" value="${oldUnitPrice}" readonly>
                                    </td>
                                    <td>
                                        ${toProductName}
                                        <input type="hidden" name="to_product_id[]" value="${toProduct}">
                                    </td>
                                    <td>
                                        <input type="number" step="any" name="unit_conversion_cost[]" class="form-control text-right conv_cost_input" value="${conversionCost}">
                                    </td>
                                    <td>
                                        <input type="number" step="any" class="form-control text-right new_unit_price" value="${newUnitPrice}" readonly>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-danger btn-sm btn-remove" data-batch="${fromBatch}"><i class="fa fa-trash"></i></button>
                                    </td>
                                </tr>
                            `;

                            $('#product-container').append(html);
                            productIds.push(fromBatch);

                            // Clear fields
                            $('#add_quantity').val('');
                            $('#add_conversion_cost').val('0');
                            $('#select_batch').val('').trigger('change');
                            $('#to_product_select').val('').trigger('change');
                        }
                    }
                });
            });

            $(document).on('keyup change', '.conv_cost_input', function() {
                let row = $(this).closest('tr');
                let oldPrice = parseFloat(row.find('.old_unit_price').val()) || 0;
                let convCost = parseFloat($(this).val()) || 0;
                row.find('.new_unit_price').val(oldPrice + convCost);
            });

            $(document).on('click', '.btn-remove', function() {
                let batch = $(this).data('batch');
                productIds = productIds.filter(item => item != batch);
                $(this).closest('tr').remove();
            });
        });

        function confirmSave() {
            if ($('#product-container tr').length === 0) {
                Swal.fire('Error', 'Please add at least one product to convert', 'error');
                return;
            }

            // Populate modal
            let warehouseText = $('#warehouse_id option:selected').text().trim();
            let dateText = $('input[name="date"]').val() || 'N/A';
            let notesText = $('input[name="notes"]').val().trim() || 'No notes';

            $('#ov-warehouse').text(warehouseText);
            $('#ov-date').text(dateText);
            $('#ov-notes').text(notesText);

            let ovBody = $('#ov-items-body');
            ovBody.empty();

            $('#product-container tr').each(function(index) {
                let fromProduct = $(this).find('td:eq(0)').clone().children('input').remove().end().text().trim();
                let qty = $(this).find('.qty_input').val() || $(this).find('input[name="quantity[]"]').val() || '0';
                let oldUnitPrice = parseFloat($(this).find('.old_unit_price').val() || 0).toFixed(2);
                let toProduct = $(this).find('td:eq(3)').clone().children('input').remove().end().text().trim();
                let convCost = parseFloat($(this).find('.conv_cost_input').val() || 0).toFixed(2);
                let newUnitPrice = parseFloat($(this).find('.new_unit_price').val() || 0).toFixed(2);

                let rowHtml = `
                    <tr>
                        <td class="font-weight-bold">${fromProduct}</td>
                        <td class="font-weight-bold">${qty}</td>
                        <td>${oldUnitPrice}</td>
                        <td class="font-weight-bold">${toProduct}</td>
                        <td>${convCost}</td>
                        <td class="text-success font-weight-bold">${newUnitPrice}</td>
                    </tr>
                `;
                ovBody.append(rowHtml);
            });

            $('#conversionOverviewModal').modal('show');
        }

        $(document).on('click', '#confirm-conversion-submit', function() {
            $('#conversionOverviewModal').modal('hide');
            $('#conversion-form').submit();
        });

        $('#btn-preview-save').on('click', function() {
            confirmSave();
        });
    </script>
@endsection
