@extends('layouts.app')
@section('title','Update Service Work')
@section('style')
    <style>
        .table td, .table th {
            padding: 6px;
            vertical-align: middle;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-4">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Service Info</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered">
                        <tr>
                            <th>Service No</th>
                            <td>{{ $service->service_no }}</td>
                        </tr>
                        <tr>
                            <th>Customer</th>
                            <td>{{ $service->customer->name ?? '' }}</td>
                        </tr>
                        <tr>
                            <th>Order No</th>
                            <td>{{ $service->saleOrder->order_no ?? '' }}</td>
                        </tr>
                    </table>
                    <hr>
                    <h5>Reported Problems:</h5>
                    <ul class="list-group">
                        @foreach($service->serviceItems as $item)
                            <li class="list-group-item">
                                <strong>{{ $item->product->name ?? '' }}</strong><br>
                                <small>Cause: {{ $item->cause }}</small>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <form action="{{ route('service.panel.submit_work', ['service' => $service->id]) }}" method="post">
                @csrf
                <div class="card card-default">
                    <div class="card-header bg-gradient-info">
                        <h3 class="card-title">Work Done Summary & Spare Parts</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="service_charge">Service Charge <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="service_charge" id="service_charge" class="form-control" value="{{ old('service_charge', $service->service_charge) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="paid_amount">Paid Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="paid_amount" id="paid_amount" class="form-control" value="{{ old('paid_amount', $service->service_charge) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="payment_account_head_id">Payment Method <span class="text-danger">*</span></label>
                                    <select name="payment_account_head_id" id="payment_account_head_id" class="form-control select2" required>
                                        <option value="">Select Method</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}" {{ old('payment_account_head_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="notes">Technician Notes (Work Summary)</label>
                                    <textarea name="notes" id="notes" rows="2" class="form-control">{{ old('notes', $service->notes) }}</textarea>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h5>Add Spare Parts (If used)</h5>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Product</label>
                                    <select class="form-control select2" id="select_product">
                                        <option value="">Select Product</option>
                                        @foreach(\App\Models\Settings\Product::where('sister_concern_id', auth()->user()->sister_concern_id)->where('status', 1)->get() as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }}</option>
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Qty</label>
                                    <input type="number" step="any" class="form-control" id="add_quantity">
                                    <small id="stock_text" class="text-info"></small>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" style="margin-top: 31px;" id="add_item_btn" class="btn btn-info btn-sm btn-block"><i class="fa fa-plus"></i></button>
                            </div>
                        </div>

                        <div class="table-responsive mt-3">
                            <table class="table table-bordered">
                                <thead class="bg-light">
                                <tr>
                                    <th>Product (Batch)</th>
                                    <th width="15%">Qty</th>
                                    <th width="20%">Cost Price</th>
                                    <th width="20%">Total</th>
                                    <th width="10%">Action</th>
                                </tr>
                                </thead>
                                <tbody id="utilized-container">
                                    <!-- Dynamic Rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="submit" class="btn btn-success bg-gradient-success">Submit for Approval</button>
                        <a href="{{ route('service.panel.index') }}" class="btn btn-danger float-right">Back</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <template id="utilized-template">
        <tr class="utilized-item">
            <td>
                <span class="p-name"></span>
                <input type="hidden" name="product_id[]" class="p-id">
                <input type="hidden" name="inventory_id[]" class="inv-id">
            </td>
            <td>
                <input type="number" step="any" name="quantity[]" class="form-control p-qty text-right">
            </td>
            <td>
                <input type="number" step="any" name="unit_price[]" class="form-control p-price text-right" readonly>
            </td>
            <td class="text-right p-total">0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash"></i></button>
            </td>
        </tr>
    </template>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2();

            $('#select_product').change(function () {
                let productId = $(this).val();
                $('#select_batch').html('<option value="">Select Batch</option>');
                $('#stock_text').text('');

                if (productId) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_batch') }}",
                        data: { id: productId }
                    }).done(function (response) {
                        $.each(response, function (index, item) {
                            $('#select_batch').append(`<option value="${item.id}">Batch-${item.batch_no}</option>`);
                        });
                    });
                }
            });

            $('#select_batch').change(function () {
                let inventoryId = $(this).val();
                if (inventoryId) {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: { inventoryId: inventoryId }
                    }).done(function (response) {
                        if (response.inventory) {
                            $('#stock_text').text('Stock: ' + response.inventory.quantity);
                            $('#add_quantity').attr('max', response.inventory.quantity);
                            $('#add_quantity').data('price', response.inventory.unit_price);
                        }
                    });
                }
            });

            $('#add_item_btn').click(function () {
                let productId = $('#select_product').val();
                let productName = $('#select_product option:selected').text();
                let inventoryId = $('#select_batch').val();
                let batchName = $('#select_batch option:selected').text();
                let qty = parseFloat($('#add_quantity').val());
                let price = $('#add_quantity').data('price');

                if (!productId || !inventoryId || isNaN(qty) || qty <= 0) {
                    Swal.fire('Error', 'Please select product, batch and enter valid quantity.', 'error');
                    return;
                }

                let exists = false;
                $('.inv-id').each(function() {
                    if ($(this).val() == inventoryId) exists = true;
                });
                if (exists) {
                    Swal.fire('Error', 'This batch already added.', 'error');
                    return;
                }

                let template = $('#utilized-template').html();
                let item = $(template);
                item.find('.p-name').text(productName + ' (' + batchName + ')');
                item.find('.p-id').val(productId);
                item.find('.inv-id').val(inventoryId);
                item.find('.p-qty').val(qty);
                item.find('.p-price').val(price);
                
                $('#utilized-container').append(item);
                calculate();
                
                $('#add_quantity').val('');
                $('#select_batch').val('').trigger('change');
            });

            $('body').on('click', '.btn-remove', function () {
                $(this).closest('tr').remove();
                calculate();
            });

            $('body').on('keyup change', '.p-qty', function () {
                calculate();
            });

            function calculate() {
                $('.utilized-item').each(function () {
                    let qty = parseFloat($(this).find('.p-qty').val()) || 0;
                    let price = parseFloat($(this).find('.p-price').val()) || 0;
                    $(this).find('.p-total').text((qty * price).toFixed(2));
                });
            }
        });
    </script>
@endsection
