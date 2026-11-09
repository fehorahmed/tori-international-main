@extends('layouts.app')
@section('title','Corporate Sales Edit')
@section('style')
    <style>
        .table td, .table th { padding: 6px; vertical-align: middle; }
        body { overflow-x: hidden; }
        .content-wrapper { overflow-x: hidden; }
    </style>
@endsection
@section('content')
    <form enctype="multipart/form-data" action="{{ route('corporate-sales.update', ['corporate_sale' => $corporateSale->id]) }}" class="form-horizontal" id="sales-form" method="post">
        @csrf
        @method('PUT')
        <div class="row">
            <div class="col-md-12">
                <div class="card card-default">
                    <div class="card-header"><h3 class="card-title">Product Information</h3></div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_product">
                                        <option value="">Search Product</option>
                                        @foreach($products as $product)
                                            <option value="{{ $product->id }}" data-product_code="{{ $product->code }}">
                                                {{ $product->name }} - {{ $product->code }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Batch <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_batch">
                                        <option value="">Select Batch</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Stk <span class="text-danger">*</span></label>
                                    <input type="number" readonly class="form-control" id="add_stock">
                                    <input type="hidden" id="add_inventory_id">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Qty <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_quantity">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Price <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" style="margin-top: 31px;" id="add_new_btn" class="btn btn-info btn-sm btn-block"><i class="fa fa-plus"></i> Add Item</button>
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
                                            <th class="text-center" width="10%">Batch</th>
                                            <th class="text-center" width="10%">Stock</th>
                                            <th class="text-center" width="15%">Qty</th>
                                            <th class="text-center" width="15%">Unit Price</th>
                                            <th class="text-center" width="15%">Total</th>
                                            <th class="text-center" width="5%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="product-container">
                                        @if(old('product_id'))
                                            @foreach(old('product_id') as $key => $productId)
                                                <tr class="product-item">
                                                    <td class="text-center product_sl">{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="product_name_text">{{ old('product_name_text')[$key] }}</span>
                                                        <input type="hidden" name="product_name_text[]" value="{{ old('product_name_text')[$key] }}">
                                                        <input type="hidden" name="product_id[]" class="product_id" value="{{ $productId }}">
                                                        <input type="hidden" name="product_code[]" class="product_code" value="{{ old('product_code')[$key] }}">
                                                        <input type="hidden" name="inventory_id[]" class="inventory_id" value="{{ old('inventory_id')[$key] }}">
                                                    </td>
                                                    <td>
                                                        <span class="batch_text">{{ old('batch_text')[$key] }}</span>
                                                        <input type="hidden" name="batch_text[]" value="{{ old('batch_text')[$key] }}">
                                                    </td>
                                                    <td class="text-right">
                                                        <input type="number" readonly class="form-control text-right product_stock" name="product_stock[]" value="{{ old('product_stock')[$key] }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]" value="{{ old('product_quantity')[$key] }}">
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]" value="{{ old('product_unit_price')[$key] }}">
                                                    </td>
                                                    <td class="text-right total_row_price">{{ number_format(old('product_quantity')[$key] * old('product_unit_price')[$key], 2) }}</td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach($corporateSale->saleOrderItems as $item)
                                                <tr class="product-item">
                                                    <td class="text-center product_sl">{{ $loop->iteration }}</td>
                                                    <td>
                                                        <span class="product_name_text">{{ $item->product->name ?? '' }}</span>
                                                        <input type="hidden" name="product_name_text[]" value="{{ $item->product->name ?? '' }}">
                                                        <input type="hidden" name="product_id[]" class="product_id" value="{{ $item->product_id }}">
                                                        <input type="hidden" name="product_code[]" class="product_code" value="{{ $item->product_code }}">
                                                        <input type="hidden" name="inventory_id[]" class="inventory_id" value="{{ $item->inventory_id }}">
                                                    </td>
                                                    <td>
                                                        <span class="batch_text">Batch-{{ $item->inventory->batch_no ?? '' }}</span>
                                                        <input type="hidden" name="batch_text[]" value="Batch-{{ $item->inventory->batch_no ?? '' }}">
                                                    </td>
                                                    <td class="text-right"><input type="number" readonly class="form-control text-right product_stock" name="product_stock[]" value="{{ ($item->inventory->quantity ?? 0) + $item->quantity }}"></td>
                                                    <td><input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]" value="{{ $item->quantity }}"></td>
                                                    <td><input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]" value="{{ $item->selling_price }}"></td>
                                                    <td class="text-right total_row_price">{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tfoot>
                                        <tr><th colspan="6" class="text-right">Sub Total</th><th class="text-right"><input type="number" readonly name="sub_total" id="sub_total" value="{{ old('sub_total', $corporateSale->sub_total) }}" class="form-control text-right"></th><th></th></tr>
                                        <tr><th colspan="6" class="text-right">Discount(%)</th><th class="text-right"><input type="number" step="any" name="discount_percent" id="discount_percent" value="{{ old('discount_percent', $corporateSale->discount_percent) }}" class="form-control text-right"><span id="total_discount">{{ number_format(old('discount_amount', $corporateSale->discount), 2) }}</span></th><th></th></tr>
                                        <tr><th colspan="6" class="text-right">Total</th><th class="text-right"><input type="number" readonly id="total_amount" name="total_amount" value="{{ old('total_amount', $corporateSale->total) }}" class="form-control text-right"></th><th></th></tr>
                                        <tr><th colspan="6" class="text-right">Paid</th><th class="text-right"><input type="number" step="any" id="paid_amount" name="paid_amount" value="{{ old('paid_amount', $corporateSale->paid) }}" class="form-control text-right"></th><th></th></tr>
                                        <tr><th colspan="6" class="text-right">Due</th><th class="text-right"><input type="number" readonly id="due_amount" name="due_amount" value="{{ old('due_amount', $corporateSale->due) }}" class="form-control text-right"></th><th></th></tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <!-- Other Information Section inside the same card -->
                        <hr style="border-top: 2px solid #ddd; margin: 30px 0 20px 0;">
                        <h4 class="mb-4 text-info"><i class="fa fa-info-circle"></i> Information</h4>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Warehouse <span class="text-danger">*</span></label>
                                    <select name="warehouse" id="warehouse" class="form-control select2">
                                        @foreach($warehouses as $warehouse)
                                            <option value="{{ $warehouse->id }}" {{ old('warehouse', $corporateSale->warehouse_id) == $warehouse->id ? 'selected' : '' }}>{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="text" name="date" id="date" class="form-control business-date-picker" value="{{ old('date', \Carbon\Carbon::parse($corporateSale->date)->format('d-m-Y')) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Corporate Customer <span class="text-danger">*</span></label>
                                    <select name="customer" id="customer" class="form-control select2">
                                        <option value="">Select Corporate Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ old('customer', $corporateSale->customer_id) == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            @php
                                $isSr = auth()->user()->employee && auth()->user()->employee->isSR();
                                $loggedSrId = $isSr ? auth()->user()->employee_id : null;
                            @endphp
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('sr') ? 'has-error' :'' }}">
                                    <label for="sr">SR <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="sr" name="sr" {{ $loggedSrId ? 'disabled' : '' }}>
                                        <option value="">Select SR</option>
                                        @foreach($srs as $sr)
                                            <option {{ old('sr', $corporateSale->sr_id) == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
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
                                    <label>Payment Method</label>
                                    <select name="payment_method" id="payment_method" class="form-control select2">
                                        <option value="">Select Method</option>
                                        @foreach($paymentModes as $mode)
                                            <option value="{{ $mode->id }}" {{ old('payment_method', $corporateSale->vouchers->where('voucher_type', 3)->first()?->transactions->where('transaction_type', 1)->first()?->account_head_id ?? 0) == $mode->id ? 'selected' : '' }}>{{ $mode->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="mt-2" id="advance_adjust_container" style="display: none;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="advance_adjust" name="advance_adjust" value="1">
                                        <label class="custom-control-label text-success" for="advance_adjust">
                                            <i class="fa fa-check-circle"></i> Pay from Advance (<span id="advance_balance_text" class="font-weight-bold">0.00</span>)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('memo_no') ? 'has-error' :'' }}">
                                    <label for="memo_no">Memo No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('memo_no', $corporateSale->memo_no) }}" name="memo_no" id="memo_no" class="form-control" placeholder="Enter Memo No." required>
                                    @error('memo_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label>Notes</label>
                                    <input type="text" name="notes" id="notes" class="form-control" placeholder="Notes" value="{{ old('notes', $corporateSale->notes) }}">
                                </div>
                            </div>
                        </div>
                        <input type="hidden" name="vat_amount" value="0">
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="btn-save-sale" class="btn btn-info bg-gradient-info btn-sm">Update</button>
                        <a href="{{ route('corporate-sales.index') }}" class="btn btn-danger btn-sm float-right">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <template id="template-product">
        <tr class="product-item">
            <td class="text-center product_sl"></td>
            <td><span class="product_name_text"></span><input type="hidden" name="product_id[]" class="product_id"><input type="hidden" name="product_code[]" class="product_code"><input type="hidden" name="inventory_id[]" class="inventory_id"></td>
            <td><span class="batch_text"></span></td>
            <td class="text-right"><input type="number" readonly class="form-control text-right product_stock" name="product_stock[]"></td>
            <td><input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]"></td>
            <td><input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]"></td>
            <td class="text-right total_row_price"></td>
            <td class="text-center"><button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button></td>
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
                    <!-- Customer, SR, Warehouse, Date, Payment Method & Note Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px;">
                        <div class="col-md-4 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Corporate Customer:</strong>
                                <span id="ov-customer" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-map-marker-alt text-info mr-1"></i> Warehouse:</strong>
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
            $('body').on('change', '#select_product', function () {
                let productId = $(this).val();
                $('#select_batch').html('<option value="">Select Batch</option>').trigger('change');
                $('#add_stock').val('');
                $('#add_unit_price').val('');
                $('#add_inventory_id').val('');
                if (productId) {
                    $.ajax({
                        url: "{{ route('get_product_batch') }}",
                        data: { id: productId, warehouseId: $('#warehouse').val() },
                        success: function(res) {
                            res.forEach(i => $('#select_batch').append(`<option value="${i.id}" data-stock="${i.quantity}" data-price="${i.wholesale_price}">Batch-${i.batch_no}</option>`));
                            $('#select_batch').trigger('change');
                        }
                    });
                }
            });

            $('body').on('change', '#select_batch', function () {
                let opt = $(this).find(':selected');
                $('#add_stock').val(opt.data('stock') || '');
                $('#add_unit_price').val(opt.data('price') || '');
                $('#add_inventory_id').val($(this).val() || '');
            });

            // Customer Validation Check (Edit Mode - Skip current order due)
            var currentSaleCustomerId = '{{ $corporateSale->customer_id }}';
            var currentSaleId = '{{ $corporateSale->id }}';

            $('#customer').on('change', function () {
                let customerId = $(this).val();
                $('#advance_adjust_container').hide();
                $('#advance_adjust').prop('checked', false);

                if (customerId) {
                    preloaderToggle(true);
                    $.ajax({
                        url: "{{ route('customer_validation_check') }}",
                        method: "GET",
                        data: {
                            customer_id: customerId,
                            sale_type: '{{ \App\Enumeration\SaleType::$CORPORATE }}'
                        },
                        success: function (res) {
                            preloaderToggle(false);
                            if (res.has_pending_preorder) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Action Blocked',
                                    text: 'This customer has a pending pre-order. You cannot create a new sale until it is processed.',
                                    confirmButtonText: 'OK'
                                }).then(() => {
                                    $('#customer').val(null).trigger('change');
                                });
                            } else if (res.has_due && customerId != currentSaleCustomerId) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Outstanding Due!',
                                    text: 'This customer has an outstanding due balance. Please clear the due first.',
                                    confirmButtonText: 'Go to Payment',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "{{ route('customer-payment.index') }}?customer_id=" + customerId;
                                    }
                                });
                            } else if (res.has_advance) {
                                $('#advance_balance_text').text(parseFloat(res.advance_balance).toFixed(2));
                                $('#advance_adjust_container').fadeIn();
                            }
                        },
                        error: function (err) {
                            preloaderToggle(false);
                            console.error('Customer validation error:', err);
                        }
                    });
                }
            });

            // Trigger on load for advance display
            if ($('#customer').val()) {
                $('#customer').trigger('change');
            }

            $('#add_new_btn').click(function() {
                let prod = $('#select_product').find(':selected');
                let batch = $('#select_batch').find(':selected');
                let qty = $('#add_quantity').val();
                let price = $('#add_unit_price').val();
                let inventoryId = batch.val();

                if (!prod.val() || !batch.val() || !qty || !price) { alert('Fill all fields'); return; }

                let exists = false;
                $('.inventory_id').each(function() {
                    if ($(this).val() == inventoryId) { exists = true; }
                });

                if (exists) { alert('This batch is already added to the list.'); return; }

                let html = $('#template-product').html();
                let item = $(html);
                item.find('.product_name_text').text(prod.text());
                item.find('.product_id').val(prod.val());
                item.find('.product_code').val(prod.data('product_code'));
                item.find('.batch_text').text(batch.text());
                item.find('.inventory_id').val(batch.val());
                item.find('.product_stock').val(batch.data('stock'));
                item.find('.product_quantity').val(qty);
                item.find('.product_unit_price').val(price);
                $('#product-container').append(item);
                calculate();
                $('#select_batch').val('').trigger('change');
                $('#add_stock').val('');
                $('#add_quantity').val('');
                $('#add_unit_price').val('');
                $('#add_inventory_id').val('');
            });

            $('body').on('click', '.btn-remove', function() { $(this).closest('tr').remove(); calculate(); });
            $('body').on('keyup', 'input', function() { calculate(); });

            function calculate() {
                let subTotal = 0;
                $('.product-item').each(function(i) {
                    $(this).find('.product_sl').text(i + 1);
                    let q = $(this).find('.product_quantity').val() || 0;
                    let p = $(this).find('.product_unit_price').val() || 0;
                    let rowTotal = q * p;
                    $(this).find('.total_row_price').text(rowTotal.toFixed(2));
                    subTotal += rowTotal;
                });
                $('#sub_total').val(subTotal.toFixed(2));
                let discPerc = $('#discount_percent').val() || 0;
                let discAmt = (subTotal * discPerc) / 100;
                $('#total_discount').text(discAmt.toFixed(2));
                let total = subTotal - discAmt;
                $('#total_amount').val(total.toFixed(2));
                let paid = $('#paid_amount').val() || 0;
                $('#due_amount').val((total - paid).toFixed(2));
            }
            
            calculate(); // Initial calculation

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
                    let name = $(this).find('.product_name_text_val').val() || $(this).find('.product_name_text').text();
                    let batch = $(this).find('.batch_text_val').val() || $(this).find('.batch_text').text();
                    let qty = parseFloat($(this).find('.product_quantity').val() || 0);
                    let price = parseFloat($(this).find('.product_unit_price').val() || 0);
                    let total = qty * price;
                    
                    let html = `
                        <tr>
                            <td>${sl++}</td>
                            <td class="text-left">${name}</td>
                            <td class="text-left">${batch}</td>
                            <td>${qty}</td>
                            <td class="text-right">${price.toFixed(2)}</td>
                            <td class="text-right">${total.toFixed(2)}</td>
                        </tr>
                    `;
                    $('#overview-items-body').append(html);
                });

                // Populate Metadata
                let customerText = $('#customer option:selected').val() ? $('#customer option:selected').text().trim() : 'N/A';
                let warehouseText = $('#warehouse option:selected').val() ? $('#warehouse option:selected').text().trim() : 'N/A';
                let dateText = $('#date').val() || 'N/A';
                let paymentMethodText = $('#payment_method option:selected').val() ? $('#payment_method option:selected').text().trim() : 'N/A';
                let notesText = $('#notes').val() ? $('#notes').val().trim() : 'No notes added';

                $('#ov-customer').text(customerText);
                $('#ov-warehouse').text(warehouseText);
                $('#ov-date').text(dateText);
                $('#ov-payment-method').text(paymentMethodText);
                $('#ov-notes').text(notesText);

                // Financials
                let subTotal = parseFloat($('#sub_total').val() || 0);
                $('#ov-sub-total').text(subTotal.toFixed(2));
                
                let discPerc = parseFloat($('#discount_percent').val() || 0);
                let discAmt = (subTotal * discPerc) / 100;
                $('#ov-discount').text(discAmt.toFixed(2) + ' (' + discPerc.toFixed(2) + '%)');

                $('#ov-receivable').text(parseFloat($('#total_amount').val() || 0).toFixed(2));
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
        });
    </script>
@endsection
