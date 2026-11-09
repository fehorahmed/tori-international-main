@extends('layouts.app')
@section('title', 'Create Purchase Return')

@section('style')
<style>
    .table th { background-color: #f4f6f9; color: #333; font-weight: bold; }
    .summary-card { background-color: #f8f9fa; border-radius: 8px; padding: 20px; box-shadow: inset 0 0 5px rgba(0,0,0,0.05); }
    .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1.1rem; }
    .summary-total { font-size: 1.3rem; font-weight: bold; border-top: 2px solid #ddd; padding-top: 10px; margin-top: 10px; color: #28a745; }
    .table td { vertical-align: middle !important; }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h4 class="card-title">Create Purchase Return (From Order)</h4>
            </div>
            <div class="card-body">
                <form action="{{ route('returns-management.purchase-returns.store') }}" method="POST" id="purchase_return_form">
                    @csrf
                    
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label for="supplier_id" class="form-label">Supplier <span class="text-danger">*</span></label>
                            <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                <option value="">Select Supplier</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}" {{ old('supplier_id') == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->member_no ? $supplier->member_no . ' - ' : '' }}{{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="purchase_order_id" class="form-label">Purchase Order No <span class="text-danger">*</span></label>
                            <select name="purchase_order_id" id="purchase_order_id" class="form-control select2" required>
                                <option value="">Select Order</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="return_warehouse_id" class="form-label">Return Warehouse (From) <span class="text-danger">*</span></label>
                            <select name="return_warehouse_id" id="return_warehouse_id" class="form-control select2" required>
                                <!-- <option value="">Select Warehouse</option> -->
                                @foreach($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}" {{ old('return_warehouse_id') == $warehouse->id ? 'selected' : '' }}>
                                        {{ $warehouse->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="text" name="date" id="date" class="form-control business-date-picker" value="{{ old('date', date('d-m-Y')) }}" readonly required>
                        </div>
                    </div>

                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">Order Items Details</h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped m-0" id="return_items_table">
                                    <thead>
                                        <tr>
                                            <th style="width: 20%;">Product Name</th>
                                            <th style="width: 10%; text-align: center;">Batch</th>
                                            <th style="width: 10%; text-align: center;">Invoiced Qty</th>
                                            <th style="width: 10%; text-align: center;">Already Returned</th>
                                            <th style="width: 10%; text-align: center;">Available Stock</th>
                                            <th style="width: 10%; text-align: center;">Returnable Qty</th>
                                            <th style="width: 15%; text-align: center;">Return Qty <span class="text-danger">*</span></th>
                                            <th style="width: 12%; text-align: right;">Unit Price <span class="text-danger">*</span></th>
                                            <th style="width: 12%; text-align: right;">Total Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center text-muted">Please select a Purchase Order to load products.</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                        <hr class="mt-4">
                        <h5 class="text-info">2. Financial Summary</h5>
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-bordered text-right">
                                    <tbody>
                                        <tr>
                                            <th class="align-middle">Total Return Value</th>
                                            <td>
                                                <input type="text" id="summary_total_return_value" name="total_return_value" class="form-control text-right font-weight-bold text-success" readonly value="0.00">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="button" id="btn-save-return" class="btn btn-success bg-gradient-success px-5">Preview & Save Return</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Purchase Return Overview Modal -->
    <div class="modal fade" id="modal-return-overview" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fa fa-undo"></i> Purchase Return Summary & Overview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px;">
                        <div class="col-md-6 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-truck text-info mr-1"></i> Supplier:</strong>
                                <span id="ov-supplier" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-map-marker-alt text-info mr-1"></i> Return Warehouse:</strong>
                                <span id="ov-warehouse" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 pl-md-3">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-calendar-alt text-info mr-1"></i> Date:</strong>
                                <span id="ov-date" class="text-dark ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-file-invoice text-info mr-1"></i> Purchase Order:</strong>
                                <span id="ov-order" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Return Items -->
                    <h6 class="text-success font-weight-bold"><i class="fa fa-arrow-up"></i> Returned Items</h6>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="bg-success text-white">
                            <tr>
                                <th>#</th>
                                <th class="text-left">Product</th>
                                <th>Batch</th>
                                <th>Return Qty</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody id="ov-return-items-body"></tbody>
                    </table>

                    <!-- Financial Summary -->
                    <div class="row mt-4">
                        <div class="col-md-5 offset-md-7">
                            <table class="table table-bordered table-sm text-right">
                                <tbody>
                                    <tr class="bg-warning">
                                        <th class="text-dark">Total Return Value:</th>
                                        <td id="ov-total-return" class="font-weight-bold text-dark">0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center mt-2 font-weight-bold text-lg text-success">Supplier Account will be Debited (Adjusted)</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Return</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-submit"><i class="fa fa-check-circle"></i> Confirm & Save Return</button>
                </div>
            </div>
        </div>
    </div>

<!-- Template for return rows -->
<template id="return-row-template">
    <tr>
        <td>
            <input type="hidden" class="template-return-product-id">
            <input type="hidden" class="template-return-batch-no">
            <span class="template-return-product-name font-weight-bold"></span>
        </td>
        <td class="text-center template-batch-no"></td>
        <td class="text-center template-invoiced-qty">0</td>
        <td class="text-center template-returned-qty">0</td>
        <td class="text-center template-available-stock">0</td>
        <td class="text-center template-returnable-qty font-weight-bold text-success">0</td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control text-center template-return-qty-input return_qty" placeholder="0.00">
        </td>
        <td>
            <input type="number" step="0.01" min="0" class="form-control text-right template-return-price-input return_price" placeholder="0.00">
        </td>
        <td>
            <input type="text" class="form-control text-right return_total" value="0.00" readonly>
        </td>
    </tr>
</template>

@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        // 1. Fetch Orders when Supplier is selected
        $('#supplier_id').change(function () {
            let supplierId = $(this).val();
            let orderSelect = $('#purchase_order_id');
            let tbody = $('#return_items_table tbody');

            orderSelect.html('<option value="">Select Order</option>');
            tbody.html('<tr><td colspan="9" class="text-center text-muted">Please select a Purchase Order to load products.</td></tr>');
            calculateTotals();

            if (supplierId) {
                $.ajax({
                    url: '{{ route("returns-management.getSupplierOrders") }}',
                    type: 'GET',
                    data: { supplier_id: supplierId },
                    success: function (data) {
                        $.each(data, function (key, value) {
                            let selected = (value.id == "{{ old('purchase_order_id') }}") ? 'selected' : '';
                            orderSelect.append('<option value="' + value.id + '" ' + selected + '>' + value.order_no + '</option>');
                        });
                        
                        // If there is an old order ID, trigger the change to fetch items
                        if ("{{ old('purchase_order_id') }}") {
                            orderSelect.trigger('change');
                        }
                    },
                    error: function () {
                        alert("Failed to fetch orders.");
                    }
                });
            }
        });

        // Re-fetch orders items if warehouse changes
        $('#return_warehouse_id').change(function() {
            if ($('#purchase_order_id').val()) {
                $('#purchase_order_id').trigger('change');
            }
        });

        // 2. Fetch Order Items when Order is selected
        $('#purchase_order_id').change(function () {
            let orderId = $(this).val();
            let warehouseId = $('#return_warehouse_id').val();
            let tbody = $('#return_items_table tbody');
            tbody.empty();

            if (!warehouseId) {
                alert("Please select a return warehouse first.");
                tbody.append('<tr><td colspan="9" class="text-center text-muted">Please select a Return Warehouse.</td></tr>');
                return;
            }

            if (orderId) {
                tbody.append('<tr><td colspan="9" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading order items...</td></tr>');

                $.ajax({
                    url: '{{ route("returns-management.getOrderItems") }}',
                    type: 'GET',
                    data: { purchase_order_id: orderId, warehouse_id: warehouseId },
                    success: function (data) {
                        tbody.empty();
                        if (data.length === 0) {
                            tbody.append('<tr><td colspan="9" class="text-center text-danger">No items found for this order.</td></tr>');
                            calculateTotals();
                            return;
                        }

                        let hasReturnableItems = false;

                        $.each(data, function (index, item) {
                            let invoicedQty = parseFloat(item.quantity) || 0;
                            let alreadyReturnedQty = parseFloat(item.return_quantity) || 0;
                            let remainingInvoiced = invoicedQty - alreadyReturnedQty;
                            let availableStock = parseFloat(item.available_stock) || 0;
                            let returnableQty = Math.min(remainingInvoiced, availableStock);
                            let unitPrice = parseFloat(item.unit_price) || 0;

                            if (remainingInvoiced > 0) {
                                hasReturnableItems = true;
                                let html = $('#return-row-template').html();
                                let row = $($.parseHTML($.trim(html)));

                                row.find('.template-return-product-id').attr('name', 'return_items[' + index + '][product_id]').val(item.product_id);
                                row.find('.template-return-batch-no').attr('name', 'return_items[' + index + '][batch_no]').val(item.batch_no);
                                row.find('.template-return-product-name').text(item.product ? item.product.name : 'Unknown Product');
                                row.find('.template-batch-no').text(item.batch_no ? item.batch_no : '-');
                                row.find('.template-invoiced-qty').text(invoicedQty);
                                row.find('.template-returned-qty').text(alreadyReturnedQty);
                                row.find('.template-available-stock').text(availableStock);
                                row.find('.template-returnable-qty').text(returnableQty);

                                if (returnableQty <= 0) {
                                    row.find('.template-returnable-qty').removeClass('text-success').addClass('text-danger');
                                }

                                row.find('.template-return-qty-input').attr('name', 'return_items[' + index + '][quantity]').attr('max', returnableQty);
                                row.find('.template-return-price-input').attr('name', 'return_items[' + index + '][unit_price]').val(unitPrice);

                                tbody.append(row);
                            }
                        });

                        if (!hasReturnableItems) {
                            tbody.append('<tr><td colspan="9" class="text-center text-danger">All items in this order have already been completely returned.</td></tr>');
                        }
                        calculateTotals();
                    },
                    error: function () {
                        tbody.empty();
                        tbody.append('<tr><td colspan="9" class="text-center text-danger">Failed to load order items.</td></tr>');
                        calculateTotals();
                    }
                });
            } else {
                tbody.append('<tr><td colspan="8" class="text-center text-muted">Please select a Purchase Order to load products.</td></tr>');
                calculateTotals();
            }
        });

        // 3. Real-time Calculation
        $(document).on('input', '.return_qty, .return_price', function() {
            let row = $(this).closest('tr');
            let qty = parseFloat(row.find('.return_qty').val()) || 0;
            let price = parseFloat(row.find('.return_price').val()) || 0;
            
            // Validate Max Return Qty
            let maxQty = parseFloat(row.find('.return_qty').attr('max')) || 0;
            if (qty > maxQty) {
                alert("Return quantity cannot exceed returnable quantity (" + maxQty + ")");
                row.find('.return_qty').val(maxQty);
                qty = maxQty;
            }

            let total = qty * price;
            row.find('.return_total').val(total.toFixed(2));
            calculateTotals();
        });

        // 4. Grand Total function
        function calculateTotals() {
            let totalReturnValue = 0;
            
            $('#return_items_table tbody tr').each(function() {
                let total = parseFloat($(this).find('.return_total').val()) || 0;
                totalReturnValue += total;
            });

            $('#summary_total_return_value').val(totalReturnValue.toFixed(2));
        }

        // Overview Modal Logic
        $('#btn-save-return').click(function(e) {
            e.preventDefault();

            let form = document.getElementById('purchase_return_form');
            if (form && !form.checkValidity()) {
                form.reportValidity();
                return;
            }

            // Validation: at least one return item
            let totalReturnQty = 0;
            $('.return_qty').each(function() {
                totalReturnQty += parseFloat($(this).val()) || 0;
            });

            if (totalReturnQty <= 0) {
                alert("Please provide return quantity for at least one item.");
                return;
            }

            // Populate Meta Info
            $('#ov-supplier').text($('#supplier_id option:selected').text());
            $('#ov-order').text($('#purchase_order_id option:selected').text());
            $('#ov-warehouse').text($('#return_warehouse_id option:selected').text());
            $('#ov-date').text($('#date').val());

            // Populate Return Items
            $('#ov-return-items-body').empty();
            let sl = 1;
            $('#return_items_table tbody tr').each(function () {
                let qty = parseFloat($(this).find('.return_qty').val()) || 0;
                if (qty > 0) {
                    let product = $(this).find('.template-return-product-name').text().trim();
                    let batch = $(this).find('.template-batch-no').text().trim();
                    let price = parseFloat($(this).find('.return_price').val()) || 0;
                    let total = qty * price;
                    let html = `
                            <tr>
                                <td>${sl++}</td>
                                <td class="text-left">${product}</td>
                                <td>${batch}</td>
                                <td>${qty}</td>
                                <td>${price.toFixed(2)}</td>
                                <td>${total.toFixed(2)}</td>
                            </tr>
                        `;
                    $('#ov-return-items-body').append(html);
                }
            });

            $('#ov-total-return').text($('#summary_total_return_value').val());

            $('#modal-return-overview').modal('show');
        });

        $('#btn-confirm-submit').click(function() {
            $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
            $('#purchase_return_form').submit();
        });

        // Trigger change if there is an old supplier selected
        if ("{{ old('supplier_id') }}") {
            $('#supplier_id').trigger('change');
        }
    });
</script>
@endsection
