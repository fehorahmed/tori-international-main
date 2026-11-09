@extends('layouts.app')

@section('title', 'Create Customer Return')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Initiate Customer Return/Replace</h4>
                </div>
                <div class="card-body">
                    <form id="sales-return-form" action="{{ route('returns-management.sales-returns.store') }}" method="POST">
                        @csrf

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="customer_id" class="form-label">Customer <span
                                        class="text-danger">*</span></label>
                                <select name="customer_id" id="customer_id" class="form-control select2" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->member_no }} - {{ $customer->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="sale_order_id" class="form-label">Invoice No <span
                                        class="text-danger">*</span></label>
                                <select name="sale_order_id" id="sale_order_id" class="form-control select2" required>
                                    <option value="">Select Invoice</option>
                                    <!-- Will be loaded via AJAX based on customer -->
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="date" class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="text" name="date" id="date" class="form-control business-date-picker" required
                                    readonly value="{{ old('date', date('d-m-Y')) }}">
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="return_warehouse_id" class="form-label">Return to Warehouse <span
                                        class="text-danger">*</span></label>
                                <select name="return_warehouse_id" id="return_warehouse_id" class="form-control select2"
                                    required>
                                    <option value="">Select Warehouse</option>
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}" {{ old('return_warehouse_id') == $warehouse->id ? 'selected' : ($warehouse->type == 'damage' && !old('return_warehouse_id') ? 'selected' : '') }}>
                                            {{ $warehouse->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <hr>
                        <hr>
                        <h5 class="text-primary">1. Items to Return</h5>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="return_items_table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product</th>
                                        <th width="10%">Invoiced Qty</th>
                                        <th width="12%">Already Returned</th>
                                        <th width="12%">Returnable Qty</th>
                                        <th width="12%">Ret. Qty <span class="text-danger">*</span></th>
                                        <th width="15%">Ret. Unit Price</th>
                                        <th width="15%">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">Please select an Invoice No to load
                                            products.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <hr class="mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="text-success">2. Replacement Items (Optional)</h5>
                            <button type="button" class="btn btn-success btn-sm" id="add_replacement_row"><i
                                    class="fa fa-plus"></i> Add Replacement Item</button>
                        </div>
                        <div class="table-responsive mt-3">
                            <table class="table table-bordered" id="replacement_items_table">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Product <span class="text-danger">*</span></th>
                                        <th width="15%">Batch <span class="text-danger">*</span></th>
                                        <th width="12%">Repl. Qty <span class="text-danger">*</span></th>
                                        <th width="15%">Repl. Unit Price <span class="text-danger">*</span></th>
                                        <th width="15%">Total Value</th>
                                        <th width="5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>

                        <hr class="mt-4">
                        <h5 class="text-info">3. Financial Summary</h5>
                        <div class="row mt-3">
                            <div class="col-md-6 offset-md-6">
                                <table class="table table-bordered text-right">
                                    <tbody>
                                        <tr>
                                            <th class="align-middle">Total Return Value (A)</th>
                                            <td><input type="text" id="total_return_value" name="total_return_amount"
                                                    class="form-control text-right" readonly value="0.00"></td>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Return Deduction (B) <br><small class="text-danger"
                                                    id="original_discount_text" style="display: none;"></small></th>
                                            <td><input type="number" id="return_deduction" name="return_deduction"
                                                    class="form-control text-right calculate-total" value="{{ old('return_deduction', 0) }}" min="0"
                                                    step="0.01"></td>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Net Return Value (C = A - B)</th>
                                            <td><input type="text" id="net_return_value" class="form-control text-right"
                                                    readonly value="0.00"></td>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Total Replacement Value (D)</th>
                                            <td><input type="text" id="total_replacement_value"
                                                    name="total_replacement_amount" class="form-control text-right" readonly
                                                    value="0.00"></td>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Current Transaction Net (D - C)</th>
                                            <td><input type="text" id="current_transaction_net"
                                                    class="form-control text-right" readonly value="0.00"></td>
                                        </tr>
                                        <tr>
                                            <th class="align-middle">Customer Prev. Balance <br><small class="text-info"
                                                    id="prev_balance_text"></small></th>
                                            <td>
                                                <input type="hidden" id="customer_prev_balance_raw" value="0">
                                                <input type="text" id="customer_prev_balance"
                                                    class="form-control text-right" readonly value="0.00">
                                            </td>
                                        </tr>
                                        <tr class="bg-light">
                                            <th class="align-middle">Final Payable / Receivable <br><small
                                                    class="text-danger" id="net_status_text"></small></th>
                                            <td><input type="text" id="net_payable_receivable"
                                                    name="net_payable_or_receivable"
                                                    class="form-control text-right font-weight-bold" readonly value="0.00">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="mt-3 text-right">
                            <button type="button" id="btn-save-return" class="btn btn-success bg-gradient-success">Preview &
                                Save Return</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Return Overview Modal -->
    <div class="modal fade" id="modal-return-overview" tabindex="-1" role="dialog" aria-hidden="true"
        data-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-gradient-info text-white">
                    <h5 class="modal-title"><i class="fa fa-undo"></i> Sales Return Summary & Overview</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px;">
                        <div class="col-md-6 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Customer:</strong>
                                <span id="ov-customer" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-map-marker-alt text-info mr-1"></i>
                                    Warehouse:</strong>
                                <span id="ov-warehouse" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-6 pl-md-3">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-calendar-alt text-info mr-1"></i>
                                    Date:</strong>
                                <span id="ov-date" class="text-dark ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-file-invoice text-info mr-1"></i> Return
                                    Invoice:</strong>
                                <span id="ov-invoice" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                    </div>

                    <!-- Return Items -->
                    <h6 class="text-success font-weight-bold"><i class="fa fa-arrow-down"></i> Returned Items</h6>
                    <table class="table table-bordered table-sm text-center">
                        <thead class="bg-success text-white">
                            <tr>
                                <th>#</th>
                                <th class="text-left">Product</th>
                                <th>Return Qty</th>
                                <th>Unit Price</th>
                                <th>Total Price</th>
                            </tr>
                        </thead>
                        <tbody id="ov-return-items-body"></tbody>
                    </table>

                    <!-- Replacement Items -->
                    <div id="ov-replacement-section">
                        <h6 class="text-info font-weight-bold mt-4"><i class="fa fa-arrow-up"></i> Replacement Items</h6>
                        <table class="table table-bordered table-sm text-center">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th>#</th>
                                    <th class="text-left">Product</th>
                                    <th>Batch</th>
                                    <th>Repl. Qty</th>
                                    <th>Unit Price</th>
                                    <th>Total Price</th>
                                </tr>
                            </thead>
                            <tbody id="ov-replacement-items-body"></tbody>
                        </table>
                    </div>

                    <!-- Financial Summary -->
                    <div class="row mt-4">
                        <div class="col-md-5 offset-md-7">
                            <table class="table table-bordered table-sm text-right">
                                <tbody>
                                    <tr>
                                        <th>Total Return Value:</th>
                                        <td id="ov-total-return">0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Total Replacement Value:</th>
                                        <td id="ov-total-replace">0.00</td>
                                    </tr>
                                    <tr class="bg-light">
                                        <th class="text-dark">Current Transaction Net:</th>
                                        <td id="ov-transaction-net" class="font-weight-bold">0.00</td>
                                    </tr>
                                    <tr>
                                        <th>Customer Prev. Balance:</th>
                                        <td id="ov-prev-balance">0.00</td>
                                    </tr>
                                    <tr class="bg-warning">
                                        <th class="text-dark">Final Payable / Receivable:</th>
                                        <td id="ov-final-net" class="font-weight-bold text-dark">0.00</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="text-center mt-2 font-weight-bold text-lg" id="ov-status-message"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><i class="fa fa-edit"></i> Edit
                        Return</button>
                    <button type="button" class="btn btn-success" id="btn-confirm-submit"><i class="fa fa-check-circle"></i>
                        Confirm & Save Return</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Return Row Template -->
    <template id="return-row-template">
        <tr>
            <td>
                <input type="hidden" class="template-return-product-id">
                <input type="hidden" class="template-return-inventory-id">
                <span class="template-return-product-name"></span>
            </td>
            <td class="text-center template-invoiced-qty"></td>
            <td class="text-center text-danger template-returned-qty"></td>
            <td class="text-center text-success font-weight-bold template-returnable-qty"></td>
            <td><input type="number" step="0.01" min="0"
                    class="form-control text-center return_qty calculate-total template-return-qty-input" value="0"
                    required></td>
            <td><input type="number" step="0.01"
                    class="form-control text-right return_price calculate-total template-return-price-input" value="0"
                    required readonly></td>
            <td><input type="number" class="form-control text-right return_row_total" readonly value="0"></td>
        </tr>
    </template>

    <!-- Replacement Row Template -->
    <template id="replacement-row-template">
        <tr>
            <td>
                <select class="form-control select2-replacement replacement-product-select template-product-select"
                    required>
                    <option value="">Select Replacement</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ str_replace("'", "\'", $product->name) }}</option>
                    @endforeach
                </select>
            </td>
            <td>
                <select class="form-control replacement-batch-select template-batch-select" required>
                    <option value="">Select Batch</option>
                </select>
                <small class="text-info stock-info" style="display:none;">Stock: <span>0</span></small>
                <input type="hidden" class="replacement_stock" value="0">
            </td>
            <td><input type="number" step="0.01" min="0.01"
                    class="form-control text-center replacement_qty calculate-total template-qty" value="1" required></td>
            <td><input type="number" step="0.01" min="0"
                    class="form-control text-right replacement_price calculate-total template-price" value="0" required>
            </td>
            <td><input type="number" class="form-control text-right replacement_row_total" readonly value="0"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm remove-replacement-row"><i
                        class="fa fa-times"></i></button></td>
        </tr>
    </template>

@endsection

@section('script')
    <script>
        $(document).ready(function () {
            $('.select2').select2();

            function formatAccounting(num) {
                if (num < 0) {
                    return '(' + Math.abs(num).toFixed(2) + ')';
                }
                return num.toFixed(2);
            }

            $('#customer_id').change(function () {
                let customerId = $(this).val();
                let invoiceSelect = $('#sale_order_id');
                invoiceSelect.empty().append('<option value="">Select Invoice</option>');
                $('#return_items_table tbody').empty().append('<tr><td colspan="10" class="text-center text-muted">Please select an Invoice No to load products.</td></tr>');

                $('#customer_prev_balance_raw').val(0);
                $('#customer_prev_balance').val('0.00');
                $('#prev_balance_text').text('');

                if (customerId) {
                    // Fetch invoices
                    $.ajax({
                        url: '{{ route("returns-management.sales-returns.get-customer-invoices") }}',
                        type: 'GET',
                        data: { customer_id: customerId },
                        success: function (data) {
                            if (data.length > 0) {
                                $.each(data, function (index, invoice) {
                                    let discount = parseFloat(invoice.discount) || 0;
                                    invoiceSelect.append('<option value="' + invoice.id + '" data-discount="' + discount + '">' + invoice.order_no + '</option>');
                                });
                                
                                if (oldSaleOrderId) {
                                    invoiceSelect.val(oldSaleOrderId).trigger('change');
                                    oldSaleOrderId = null; // reset so it only triggers on load
                                }
                            }
                        },
                        error: function () {
                            alert('Failed to load invoices. Please try again.');
                        }
                    });

                    // Fetch Customer Balance
                    $.ajax({
                        url: '{{ route("customer_validation_check") }}',
                        type: 'GET',
                        data: { customer_id: customerId },
                        success: function (res) {
                            let balance = parseFloat(res.current_balance) || 0;
                            $('#customer_prev_balance_raw').val(balance);
                            $('#customer_prev_balance').val(formatAccounting(balance));
                            if (balance > 0) {
                                $('#prev_balance_text').text('(Due)');
                            } else if (balance < 0) {
                                $('#prev_balance_text').text('(Advance)');
                            }
                            calculateTotals();
                        }
                    });
                }

                $('#original_discount_text').hide();
                $('#return_deduction').val(0);
                calculateTotals();
            });

            $('#sale_order_id').change(function () {
                let saleOrderId = $(this).val();
                let invoiceDiscount = $(this).find(':selected').data('discount') || 0;

                if (saleOrderId && invoiceDiscount > 0) {
                    $('#original_discount_text').text('(Invoice Discount: ৳ ' + invoiceDiscount.toFixed(2) + ')').show();
                } else {
                    $('#original_discount_text').hide();
                }

                let tbody = $('#return_items_table tbody');
                tbody.empty();

                if (saleOrderId) {
                    tbody.append('<tr><td colspan="7" class="text-center"><i class="fa fa-spinner fa-spin"></i> Loading invoice items...</td></tr>');

                    $.ajax({
                        url: '{{ route("returns-management.sales-returns.get-invoice-items") }}',
                        type: 'GET',
                        data: { sale_order_id: saleOrderId },
                        success: function (data) {
                            tbody.empty();
                            if (data.length === 0) {
                                tbody.append('<tr><td colspan="7" class="text-center text-danger">No items found for this invoice.</td></tr>');
                                calculateTotals();
                                return;
                            }

                            let hasReturnableItems = false;

                            $.each(data, function (index, item) {
                                let invoicedQty = parseFloat(item.quantity) || 0;
                                let alreadyReturnedQty = parseFloat(item.return_quantity) || 0;
                                let returnableQty = invoicedQty - alreadyReturnedQty;
                                let unitPrice = parseFloat(item.selling_price) || 0;

                                if (returnableQty > 0) {
                                    hasReturnableItems = true;
                                    let html = $('#return-row-template').html();
                                    let row = $($.parseHTML($.trim(html)));

                                    row.find('.template-return-product-id').attr('name', 'return_items[' + index + '][product_id]').val(item.product_id);
                                    row.find('.template-return-inventory-id').attr('name', 'return_items[' + index + '][inventory_id]').val(item.inventory_id);
                                    row.find('.template-return-product-name').text(item.product ? item.product.name : 'Unknown Product');
                                    row.find('.template-invoiced-qty').text(invoicedQty);
                                    row.find('.template-returned-qty').text(alreadyReturnedQty);
                                    row.find('.template-returnable-qty').text(returnableQty);

                                    row.find('.template-return-qty-input').attr('name', 'return_items[' + index + '][quantity]').attr('max', returnableQty);
                                    row.find('.template-return-price-input').attr('name', 'return_items[' + index + '][unit_price]').val(unitPrice);

                                    tbody.append(row);
                                }
                            });

                            if (!hasReturnableItems) {
                                tbody.append('<tr><td colspan="7" class="text-center text-danger">All items in this invoice have already been completely returned.</td></tr>');
                            } else {
                                // Repopulate old return items
                                let oldReturnItems = @json(old('return_items', []));
                                if (oldReturnItems && oldReturnItems.length > 0) {
                                    $('#return_items_table tbody tr').each(function(index, tr) {
                                        let pId = $(tr).find('.template-return-product-id').val();
                                        let match = oldReturnItems.find(item => item.product_id == pId);
                                        if (match && match.quantity) {
                                            $(tr).find('.return_qty').val(match.quantity);
                                        }
                                    });
                                }
                            }
                            calculateTotals();
                        },
                        error: function () {
                            tbody.empty();
                            tbody.append('<tr><td colspan="7" class="text-center text-danger">Failed to load invoice items.</td></tr>');
                            calculateTotals();
                        }
                    });
                } else {
                    tbody.append('<tr><td colspan="7" class="text-center text-muted">Please select an Invoice No to load products.</td></tr>');
                    calculateTotals();
                }
            });

            // Add Replacement Row Logic
            let replacementIndex = 0;

            $('#add_replacement_row').click(function () {
                let html = $('#replacement-row-template').html();
                let row = $($.parseHTML($.trim(html)));

                row.find('.template-product-select').attr('name', 'replacement_items[' + replacementIndex + '][product_id]');
                row.find('.template-batch-select').attr('name', 'replacement_items[' + replacementIndex + '][inventory_id]');
                row.find('.template-qty').attr('name', 'replacement_items[' + replacementIndex + '][quantity]');
                row.find('.template-price').attr('name', 'replacement_items[' + replacementIndex + '][unit_price]');

                $('#replacement_items_table tbody').append(row);
                $('#replacement_items_table tbody tr:last').find('.select2-replacement').select2();

                replacementIndex++;
                calculateTotals();
            });

            $(document).on('click', '.remove-replacement-row', function () {
                $(this).closest('tr').remove();
                calculateTotals();
            });

            let defaultWarehouseId = "{{ \App\Models\Settings\Warehouse::where('status', 1)->where('type', 'main')->where('sister_concern_id', auth()->user()->activeConcernId())->first()->id ?? '' }}";

            $(document).on('change', '.replacement-product-select', function () {
                let row = $(this).closest('tr');
                let productId = $(this).val();
                let batchSelect = row.find('.replacement-batch-select');

                batchSelect.html('<option value="">Select Batch</option>');
                row.find('.replacement_price').val(0);
                row.find('.stock-info').hide();
                row.find('.replacement_stock').val(0);
                calculateTotals();

                if (productId) {
                    $.ajax({
                        url: "{{ route('get_product_batch') }}",
                        type: "GET",
                        data: { id: productId, warehouseId: defaultWarehouseId },
                        success: function (res) {
                            if (Array.isArray(res) && res.length > 0) {
                                res.forEach(function (inventory) {
                                    batchSelect.append('<option value="' + inventory.id + '">Batch-' + inventory.batch_no + '</option>');
                                });
                            }
                        }
                    });
                }
            });

            $(document).on('change', '.replacement-batch-select', function () {
                let row = $(this).closest('tr');
                let inventoryId = $(this).val();

                if (inventoryId) {
                    $.ajax({
                        url: "{{ route('get_product_stock_details') }}",
                        type: "GET",
                        data: { inventoryId: inventoryId },
                        success: function (res) {
                            if (res.status && res.inventory) {
                                let stock = parseFloat(res.inventory.quantity) || 0;
                                let price = parseFloat(res.inventory.selling_price) || 0;
                                row.find('.replacement_price').val(price);
                                row.find('.replacement_stock').val(stock);
                                row.find('.replacement_qty').attr('max', stock);
                                row.find('.stock-info').show().find('span').text(stock);
                                calculateTotals();
                            }
                        }
                    });
                } else {
                    row.find('.replacement_price').val(0);
                    row.find('.stock-info').hide();
                    row.find('.replacement_stock').val(0);
                    calculateTotals();
                }
            });

            // Calculation & Validation Logic
            $(document).on('input', '.return_qty', function () {
                let max = parseFloat($(this).attr('max')) || 0;
                let val = parseFloat($(this).val()) || 0;
                if (val > max) {
                    alert('Return quantity cannot exceed returnable quantity (' + max + ')');
                    $(this).val(max);
                }
                calculateTotals();
            });

            $(document).on('input', '.replacement_qty', function () {
                let max = parseFloat($(this).attr('max')) || 0;
                let val = parseFloat($(this).val()) || 0;
                if (max > 0 && val > max) {
                    alert('Replacement quantity cannot exceed available stock (' + max + ')');
                    $(this).val(max);
                }
                calculateTotals();
            });

            $(document).on('input', '.calculate-total', function () {
                if (!$(this).hasClass('return_qty') && !$(this).hasClass('replacement_qty')) {
                    calculateTotals();
                }
            });

            function calculateTotals() {
                let totalReturn = 0;
                let totalReplace = 0;

                // Return items
                $('#return_items_table tbody tr').each(function () {
                    let qty = parseFloat($(this).find('.return_qty').val()) || 0;
                    let price = parseFloat($(this).find('.return_price').val()) || 0;
                    let rowTotal = qty * price;
                    $(this).find('.return_row_total').val(rowTotal.toFixed(2));
                    totalReturn += rowTotal;
                });

                // Replace items
                $('#replacement_items_table tbody tr').each(function () {
                    let qty = parseFloat($(this).find('.replacement_qty').val()) || 0;
                    let price = parseFloat($(this).find('.replacement_price').val()) || 0;
                    let rowTotal = qty * price;
                    $(this).find('.replacement_row_total').val(rowTotal.toFixed(2));
                    totalReplace += rowTotal;
                });

                let deduction = parseFloat($('#return_deduction').val()) || 0;
                let netReturn = totalReturn - deduction;

                let transactionNet = totalReplace - netReturn;
                let prevBalance = parseFloat($('#customer_prev_balance_raw').val()) || 0;

                let finalBalance = prevBalance + transactionNet;

                $('#total_return_value').val(formatAccounting(totalReturn));
                $('#net_return_value').val(formatAccounting(netReturn));
                $('#total_replacement_value').val(formatAccounting(totalReplace));
                $('#current_transaction_net').val(formatAccounting(transactionNet));
                $('#net_payable_receivable').val(formatAccounting(Math.abs(finalBalance)));

                if (finalBalance > 0) {
                    $('#net_status_text').text('Customer needs to Pay');
                    $('#net_status_text').removeClass('text-success text-muted').addClass('text-danger');
                } else if (finalBalance < 0) {
                    $('#net_status_text').text('We need to Pay / Adjust');
                    $('#net_status_text').removeClass('text-danger text-muted').addClass('text-success');
                } else {
                    $('#net_status_text').text('Settled');
                    $('#net_status_text').removeClass('text-danger text-success').addClass('text-muted');
                }
            }

            // Overview Modal Logic
            $('#btn-save-return').click(function (e) {
                e.preventDefault();

                let form = document.getElementById('sales-return-form');
                if (form && !form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                // Meta info
                $('#ov-customer').text($('#customer_id option:selected').text());
                $('#ov-warehouse').text($('#return_warehouse_id option:selected').text());
                $('#ov-date').text($('input[name="date"]').val());
                $('#ov-invoice').text($('#sale_order_id option:selected').text());

                // Return items
                $('#ov-return-items-body').empty();
                let sl = 1;
                let hasReturn = false;
                $('#return_items_table tbody tr').each(function () {
                    let qty = parseFloat($(this).find('.return_qty').val()) || 0;
                    if (qty > 0) {
                        hasReturn = true;
                        let product = $(this).find('td:eq(0)').text().trim();
                        let price = parseFloat($(this).find('.return_price').val()) || 0;
                        let total = qty * price;
                        let html = `
                                <tr>
                                    <td>${sl++}</td>
                                    <td class="text-left">${product}</td>
                                    <td>${qty}</td>
                                    <td>${price.toFixed(2)}</td>
                                    <td>${total.toFixed(2)}</td>
                                </tr>
                            `;
                        $('#ov-return-items-body').append(html);
                    }
                });

                if (!hasReturn) {
                    alert('Please return at least one product with quantity > 0');
                    return;
                }

                // Replacement items
                $('#ov-replacement-items-body').empty();
                sl = 1;
                let hasReplace = false;
                $('#replacement_items_table tbody tr').each(function () {
                    let product = $(this).find('.replacement-product-select option:selected').text().trim();
                    let batch = $(this).find('.replacement-batch-select option:selected').text().trim();
                    let qty = parseFloat($(this).find('.replacement_qty').val()) || 0;
                    if (product && qty > 0 && product != 'Select Replacement') {
                        hasReplace = true;
                        let price = parseFloat($(this).find('.replacement_price').val()) || 0;
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
                        $('#ov-replacement-items-body').append(html);
                    }
                });

                if (hasReplace) {
                    $('#ov-replacement-section').show();
                } else {
                    $('#ov-replacement-section').hide();
                }

                // Financials
                $('#ov-total-return').text($('#total_return_value').val());
                $('#ov-total-replace').text($('#total_replacement_value').val());
                $('#ov-transaction-net').text($('#current_transaction_net').val());
                $('#ov-prev-balance').text($('#customer_prev_balance').val() + ' ' + $('#prev_balance_text').text());
                $('#ov-final-net').text($('#net_payable_receivable').val());

                let netStatus = $('#net_status_text').text();
                $('#ov-status-message').text(netStatus).attr('class', 'text-center mt-2 font-weight-bold text-lg ' + $('#net_status_text').attr('class'));

                $('#modal-return-overview').modal('show');
            });

            // Confirm Submit
            $('#btn-confirm-submit').click(function () {
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Processing...');
                $('#sales-return-form').submit(); // Assuming the form id is sales-return-form
                // Fallback to submitting the first form in the page if id differs
                if ($('#sales-return-form').length === 0) {
                    $('form').first().submit();
                }
            });

            // Handle Old Values on initial load
            let oldCustomerId = "{{ old('customer_id') }}";
            let oldSaleOrderId = "{{ old('sale_order_id') }}";
            
            if (oldCustomerId) {
                $('#customer_id').trigger('change');
            }

            let oldReplacementItems = @json(old('replacement_items', []));
            if (oldReplacementItems) {
                let itemsArray = Array.isArray(oldReplacementItems) ? oldReplacementItems : Object.values(oldReplacementItems);
                
                itemsArray.forEach(function(item, idx) {
                    if(item && item.product_id) {
                        $('#add_replacement_row').click();
                        let lastRow = $('#replacement_items_table tbody tr:last');
                        lastRow.find('.replacement-product-select').val(item.product_id).trigger('change');
                        
                        let checkExist = setInterval(function() {
                            if (lastRow.find('.replacement-batch-select option[value="'+item.inventory_id+'"]').length) {
                                lastRow.find('.replacement-batch-select').val(item.inventory_id).trigger('change');
                                
                                setTimeout(function() {
                                    if(item.quantity) lastRow.find('.replacement_qty').val(item.quantity);
                                    if(item.unit_price) lastRow.find('.replacement_price').val(item.unit_price);
                                    calculateTotals();
                                }, 500);
                                
                                clearInterval(checkExist);
                            }
                        }, 200);
                        
                        setTimeout(() => clearInterval(checkExist), 5000);
                    }
                });
            }

        });
    </script>
@endsection