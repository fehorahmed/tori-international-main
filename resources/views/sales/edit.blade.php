@extends('layouts.app')
@section('title','Sales Edit')
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
          action="{{ route('sales.update',['sale'=>$sale->id]) }}"
          class="form-horizontal" id="orderEditForm" method="post">  
        @csrf
        @method('PUT')
        <input type="hidden" name="is_pos" value="0">
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Product Information </h3>
                    </div>
                    <div class="card-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="select_product">Product <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="select_product" name="select_product">
                                        <option value="">Search Name of Product</option>
                                        @foreach($products as $product)
                                            <option
                                                {{ old('product') == $product->id ? 'selected' : ''  }} value="{{ $product->id }}" data-product_code="{{ $product->code }}">
                                                @if($product->brand)
                                                    Brand: {{ $product->brand->name ?? '' }} -
                                                @endif
                                                @if($product->productCategory)
                                                    Category: {{ $product->productCategory->name ?? '' }} -
                                                @endif
                                                {{ $product->name }}
                                                - {{ $product->unit->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
                                    <label for="add_stoct">Stk <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_stock"
                                           placeholder="Stock" readonly>
                                    <input type="hidden" name="add_inventory_id" id="add_inventory_id">       
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_quantity">Qty <span class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_quantity"
                                           placeholder="Qty">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="add_unit_price">Unit Price <span
                                            class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price"
                                           placeholder="Unit Price">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <button type="button" style="margin-top: 31px;" id="add_new_btn"
                                            class="btn btn-info bg-gradient-info btn-sm btn-block"><i
                                            class="fa fa-plus"></i> Add Item</button>
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
                                        <th class="text-center" width="10%">Batch</th>
                                        <th class="text-center text-warning" width="10%">(Stk+Qty)</th>
                                        <th class="text-center" width="15%">Qty</th>
                                        <th class="text-center" width="15%">Unit Price</th>
                                        <th class="text-center" width="15%">Total Price</th>
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
                                                    <input type="hidden" name="prev_product_quantity[]"
                                                           value="{{ old('prev_product_quantity.'.$loop->index) }}"
                                                           class="prev_product_quantity">

                                                </td>
                                                <td class="text-left">
                                                    <span class="product_batch_name">{{ old('product_batch_name_val.'.$loop->index) }}</span>
                                                    <input type="hidden" name="product_batch_name_val[]"
                                                           value="{{ old('product_batch_name_val.'.$loop->index) }}"
                                                           class="product_batch_name_val">
                                                </td>
                                                <td class="text-right">
                                                    <div
                                                        class="form-group mb-0  {{ $errors->has('product_stock.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any"
                                                               value="{{ old('product_stock.'.$loop->index) }}"
                                                               class="form-control text-right product_stock"
                                                               name="product_stock[]" readonly>
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div
                                                        class="form-group mb-0  {{ $errors->has('product_quantity.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any"
                                                               value="{{ old('product_quantity.'.$loop->index) }}"
                                                               class="form-control text-right product_quantity"
                                                               name="product_quantity[]">
                                                    </div>
                                                </td>
                                                <td class="text-right">
                                                    <div
                                                        class="form-group mb-0  {{ $errors->has('product_unit_price.'.$loop->index) ? 'has-error' :'' }}">
                                                        <input type="number" step="any"
                                                               value="{{ old('product_unit_price.'.$loop->index) }}"
                                                               class="form-control text-right product_unit_price"
                                                               name="product_unit_price[]">
                                                    </div>
                                                </td>
                                                <td class="text-right total-sale-cost"></td>
                                                <td class="text-center">
                                                    <button type="button"
                                                            class="btn btn-danger bg-gradient-danger btn-sm btn-remove">
                                                        <i class="fa fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @if ($sale->saleOrderItems && count($sale->saleOrderItems) > 0)
                                            @foreach ($sale->saleOrderItems as $item)
                                                @php
                                                    $stock = \App\Models\Inventory\Inventory::where('sister_concern_id',$sale->sister_concern_id)->where('id',$item->inventory_id)->first();
                                                @endphp
                                                <tr class="product-item">
                                                    <td class="text-center">
                                                        <span class="product_sl"></span>
                                                    </td>
                                                    <td class="text-left">
                                                        <span class="product_name">{{ $item->product->name }}</span>
                                                        <input type="hidden" value="{{ $item->product->name }}" name="product_name_val[]" class="product_name_val">
                                                        <input type="hidden" value="{{ $item->product_id }}" name="product_id[]" class="product_id">
                                                        <input type="hidden" value="{{ $item->product_code }}" name="product_code[]" class="product_code">
                                                        <input type="hidden" value="{{ $item->inventory_id }}" name="inventory_id[]" class="inventory_id">
                                                        <input type="hidden" value="{{ $item->quantity }}" name="prev_product_quantity[]" class="prev_product_quantity">
                                                    </td>
                                                    <td class="text-left">
                                                        <span class="product_batch_name">{{ 'Batch-'.$stock->batch_no }}</span>
                                                        <input type="hidden" name="product_batch_name_val[]"
                                                            value="{{ 'Batch-'.$stock->batch_no }}"
                                                            class="product_batch_name_val">
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="any" value="{{ $stock->quantity+$item->quantity }}" class="form-control text-right product_stock" name="product_stock[]" readonly>
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="any" value="{{ $item->quantity }}" class="form-control text-right product_quantity" name="product_quantity[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0">
                                                            <input type="number" step="any" value="{{ $item->selling_price }}" class="form-control text-right product_unit_price"
                                                                name="product_unit_price[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-right total-sale-cost"></td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i
                                                                class="fa fa-trash-alt"></i></button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif    
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr class="footer_area">
                                        <th colspan="3"></th>
                                        <th class="text-right"></th>
                                        <th class="text-right" id="total_quantity"></th>
                                        <th class="text-right">Sub Total</th>
                                        <th class="text-right">
                                            <div class="form-group">
                                                <div
                                                    class="form-group mb-0  {{ $errors->has('sub_total') ? 'has-error' :'' }}">
                                                    <input type="number" step="any" readonly name="sub_total" id="sub_total"
                                                           class="form-control text-right">
                                                </div>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="6" class="text-right">
                                            <div class="d-flex justify-content-end align-items-center">
                                                <span class="mr-2 font-weight-bold">Discount</span>
                                                <select name="discount_type" id="discount_type" class="form-control form-control-sm" style="width: 150px; display: inline-block;">
                                                    <option value="2" {{ old('discount_type', $sale->discount_type ?? '2') == '2' ? 'selected' : '' }}>Percentage (%)</option>
                                                    <option value="1" {{ old('discount_type', $sale->discount_type ?? '2') == '1' ? 'selected' : '' }}>Flat (টাকা)</option>
                                                </select>
                                            </div>
                                        </th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('discount_percent') ? 'has-error' :'' }}">
                                                <input type="number" value="{{ old('discount_percent', $sale->discount_percent) }}" step="any"
                                                       name="discount_percent"
                                                       id="discount_percent" class="form-control text-right">
                                                <input type="hidden" name="discount_amount" id="discount_amount" value="{{ old('discount_amount', $sale->discount) }}">
                                                <span id="total_discount" class="text-muted small"></span>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <input type="hidden" id="vat_amount" name="vat_amount">
                                    {{-- <tr class="footer_area">
                                        <th colspan="5" class="text-right">VAT(%)</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('vat_percent') ? 'has-error' :'' }}">
                                                <input type="number" value="{{ old('vat_percent') }}" step="any"
                                                       id="vat_percent" name="vat_percent"
                                                       class="form-control text-right">
                                                <input type="hidden" id="vat_amount" name="vat_amount">
                                                <span id="total_vat"></span>
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr> --}}

                                    <tr class="footer_area">
                                        <th colspan="6" class="text-right">Total Receivable</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('total_receivable') ? 'has-error' :'' }}">
                                                <input type="number" readonly step="any" id="total_receivable"
                                                       name="total_receivable" class="form-control text-right">
                                                <input type="hidden" name="total_amount" id="total_amount">       
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr class="footer_area">
                                        <th colspan="6" class="text-right">Paid</th>
                                        <th class="text-right">
                                            <div
                                                class="form-group mb-0  {{ $errors->has('paid_amount') ? 'has-error' :'' }}">
                                                <input type="number" step="any" id="paid_amount"
                                                       name="paid_amount" value="{{ old('paid_amount', $sale->paid) }}" class="form-control text-right">
                                            </div>
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="6" class="text-right">Due</th>
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
                        
                        <!-- Other Information Section inside the same card -->
                        <hr style="border-top: 2px solid #ddd; margin: 30px 0 20px 0;">
                        <h4 class="mb-4 text-info"><i class="fa fa-info-circle"></i> Other Information</h4>
                        <div class="row"> 
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('warehouse') ? 'has-error' :'' }}">
                                    <label for="warehouse">Warehouse <span
                                            class="text-danger">*</span></label>
                                    <select class="form-control select2" id="warehouse" name="warehouse">
                                        <option value="">Search Warehouse</option>
                                        @foreach($warehouses as $warehouse)
                                            <option
                                                {{ old('warehouse',request('warehouse') ?? $sale->warehouse_id) == $warehouse->id ? 'selected' : ''  }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('warehouse')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('date',date('d-m-Y',strtotime($sale->date))) }}"
                                           name="date" id="date" class="form-control date-picker"
                                           placeholder="Enter date">
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                @php
                                    $isSr = auth()->user()->employee && auth()->user()->employee->isSR();
                                    $loggedSrId = $isSr ? auth()->user()->employee_id : null;
                                @endphp
                                <div class="form-group {{ $errors->has('sr') ? 'has-error' :'' }}">
                                    <label for="sr">SR <span class="text-danger">*</span></label>
                                    <select class="form-control select2" id="sr" name="sr" {{ $loggedSrId ? 'disabled' : '' }}>
                                        <option value="">Select SR</option>
                                        @foreach($srs as $sr)
                                            <option {{ old('sr', $sale->sr_id) == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    </select>
                                    @if($loggedSrId)
                                        <input type="hidden" name="sr" value="{{ $loggedSrId }}">
                                    @endif
                                    @error('sr')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('customer') ? 'has-error' :'' }}">
                                    <label for="customer">Customer <span
                                            class="text-danger">*</span> <button type="button" id="add-new-customer" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-plus"></i></button></label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option value="">Search Name of Customer</option>
                                        @if($selectedCustomer)
                                            <option value="{{ $selectedCustomer->id }}" selected>{{ $selectedCustomer->name }} - Mobile No. {{ $selectedCustomer->mobile_no }}</option>
                                        @else
                                            <option value="{{ $sale->customer->id }}" selected>{{ $sale->customer->name }} - Mobile No. {{ $sale->customer->mobile_no }}</option>
                                        @endif
                                    </select>
                                    @error('customer')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
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
                                <div class="mt-2" id="advance_adjust_container" style="display: none;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="advance_adjust" name="advance_adjust" value="1">
                                        <label class="custom-control-label text-success" for="advance_adjust">
                                            <i class="fa fa-check-circle"></i> Pay from Advance (<span id="advance_balance_text" class="font-weight-bold">0.00</span>)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-9">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes</label>
                                    <input type="text" value="{{ old('notes',$sale->notes) }}" name="notes" id="notes"
                                           class="form-control" placeholder="Enter Notes">
                                    @error('notes')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="order_edit_submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('sales.index') }}"
                           class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>

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
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Customer Name:</strong>
                                <span id="ov-customer" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-map-marker-alt text-info mr-1"></i> Warehouse:</strong>
                                <span id="ov-warehouse" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-4 border-right pl-md-3">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user-tie text-info mr-1"></i> SR Name:</strong>
                                <span id="ov-sr" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
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
                                    <th class="text-right font-weight-bold">Total Receivable:</th>
                                    <td class="text-right font-weight-bold" id="ov-receivable">0.00</td>
                                </tr>
                                <tr>
                                    <th class="bg-light text-right">Paid Amount:</th>
                                    <td class="text-right text-success font-weight-bold" id="ov-paid">0.00</td>
                                </tr>
                                <tr class="table-danger">
                                    <th class="text-right font-weight-bold">Due Amount:</th>
                                    <td class="text-right font-weight-bold" id="ov-due">0.00</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Form / Go Back</button>
                    <button type="button" id="btn-confirm-submit" class="btn btn-success btn-sm"><i class="fa fa-check-circle"></i> Confirm & Save / Approve</button>
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
                <input type="hidden" value="0" name="prev_product_quantity[]" class="prev_product_quantity">
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
            $('body').on('click', '#order_edit_submit', function(e) {
                e.preventDefault();
                
                let rows = $("#product-container .product-item");
                if (rows.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please add at least one product before saving.',
                    });
                    return;
                }

                // Check form validity before showing overview
                let form = document.getElementById('orderEditForm');
                if (form && !form.checkValidity()) {
                    form.reportValidity();
                    return;
                }

                let html = '';
                let sl = 1;
                
                rows.each(function(index) {
                    let name = $(this).find('.product_name').first().text() || $(this).find('input.product_name_val').val();
                    let batch = $(this).find('.product_batch_name').first().text() || $(this).find('input.product_batch_name_val').val();
                    let qty = parseFloat($(this).find('.product_quantity').val()) || 0;
                    let price = parseFloat($(this).find('.product_unit_price').val()) || 0;
                    let total = qty * price;
                    
                    html += `
                        <tr>
                            <td>${sl++}</td>
                            <td class="text-left">${name}</td>
                            <td class="text-left">${batch}</td>
                            <td>${qty}</td>
                            <td class="text-right">${price.toFixed(2)}</td>
                            <td class="text-right">${total.toFixed(2)}</td>
                        </tr>
                    `;
                });

                $('#overview-items-body').html(html);

                // Populate Customer, SR, Warehouse & Date in the modal
                let customerText = $('#customer option:selected').val() ? $('#customer option:selected').text().trim() : 'N/A';
                let srText = $('#sr option:selected').val() ? $('#sr option:selected').text().trim() : 'N/A';
                let warehouseText = $('#warehouse option:selected').val() ? $('#warehouse option:selected').text().trim() : 'N/A';
                let dateText = $('#date').val() || 'N/A';
                let paymentMethodText = $('#payment_method option:selected').val() ? $('#payment_method option:selected').text().trim() : 'N/A';
                let notesText = $('#notes').val() ? $('#notes').val().trim() : 'No notes added';

                $('#ov-customer').text(customerText);
                $('#ov-sr').text(srText);
                $('#ov-warehouse').text(warehouseText);
                $('#ov-date').text(dateText);
                $('#ov-payment-method').text(paymentMethodText);
                $('#ov-notes').text(notesText);

                // Financial Overview Info
                $('#ov-sub-total').text(parseFloat($('#sub_total').val() || 0).toFixed(2));
                
                // Show discount formatted
                let discAmount = parseFloat($('#discount_amount').val() || 0);
                let discLabel = $('#total_discount').text();
                $('#ov-discount').text(discAmount.toFixed(2) + (discLabel ? ' ' + discLabel : ''));
                
                $('#ov-receivable').text(parseFloat($('#total_receivable').val() || 0).toFixed(2));
                $('#ov-paid').text(parseFloat($('#paid_amount').val() || 0).toFixed(2));
                $('#ov-due').text(parseFloat($('#due_amount').val() || 0).toFixed(2));

                $('#modal-sale-overview').modal('show');
            });

            $('#btn-confirm-submit').click(function() {
                $(this).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
                $('#orderEditForm').submit();
            });

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

            // Customer Validation Check (Edit Mode - Skip current order due)
            var currentSaleCustomerId = '{{ $sale->customer_id }}';
            var currentSaleId = '{{ $sale->id }}';

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
                            sale_type: '{{ \App\Enumeration\SaleType::$GENERAL }}'
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
                $('#select_batch').html('<option value="">Select Batch</option>');
                $('#add_unit_price').val(' ');
                $('#add_stock').val(' ');
                $('#add_inventory_id').val(' ');
                if (selectProductId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_batch') }}",
                        data: {id: selectProductId,warehouseId: warehouseId}
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
                let warehouseId = $('#warehouse').val();
                let productId = $('#select_product').val();
                let selectBatchId = $('#select_batch').val();
                $('#add_unit_price').val(' ');
                $('#add_stock').val(' ');
                $('#add_inventory_id').val(' ');
                if (selectBatchId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: { inventoryId: selectBatchId }
                    }).done(function (response) {
                        console.log(response);
                        $('#add_stock').val(response.inventory && response.inventory.quantity ? response.inventory.quantity : 0);
                        $('#add_inventory_id').val(response.inventory.id ?? 0);
                        $('#add_unit_price').val(response.inventory.selling_price ?? ' ');
                    });
                }
            });

            // $(".product_id").each(function (index) {
            //     let warehouseId = $('#warehouse').val();
            //     let productId = $(this).closest('tr').find('.product_id').val();
            //     let thisItem = $(this);

            //     if (productId != '') {
            //         $.ajax({
            //             method: "GET",
            //             url: "{{ route('get_product_stock_details') }}",
            //             data: {id: productId,warehouseId: warehouseId}
            //         }).done(function (response) {
            //             console.log(response);
            //             let stockItem = (response.inventory && response.inventory.quantity ? response.inventory.quantity : 0) + parseFloat(thisItem.closest('tr').find('.product_quantity').val());
            //             // thisItem.closest('tr').find('.product_stock').val(stockItem)
            //         });
            //     }
            // });

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

                if(parseFloat(addStock) < parseFloat(addQuantity)){
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
            $('body').on('keyup change', 'input[type="number"]', function () {
                calculate();
            });
            $('body').on('change', '#discount_type', function () {
                calculate();
            });
        })

        function calculate() {
            var productSl = 1;

            var rows = $("#product-container .product-item");
            rows.each(function () {
                let product_stock1 = parseFloat($('.product_stock:eq(' + (productSl-1) + ')').val());
                product_stock1 = (isNaN(product_stock1) || product_stock1 < 0) ? 0 : product_stock1;

                let product_quantity1 = parseFloat($('.product_quantity:eq(' + (productSl-1) + ')').val());
                product_quantity1 = (isNaN(product_quantity1) || product_quantity1 < 0) ? 0 : product_quantity1;

                if(product_stock1 < product_quantity1){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: ' insufficient stock.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }

                    $('.product_quantity:eq(' + (productSl-1) + ')').val(1);
                }

                var productSlElement = $(this).find('.product_sl');
                productSlElement.text(productSl);
                productSl++;
            });

            var total_quantity = 0;
            var sub_total = 0;
            var paid_amount = $('#paid_amount').val();
            $('.product-item').each(function (i, obj) {

                let product_stock = parseFloat($('.product_stock:eq(' + i + ')').val());
                product_stock = (isNaN(product_stock) || product_stock < 0) ? 0 : product_stock;

                let product_quantity = parseFloat($('.product_quantity:eq(' + i + ')').val());
                product_quantity = (isNaN(product_quantity) || product_quantity < 0) ? 0 : product_quantity;

                let product_unit_price = parseFloat($('.product_unit_price:eq(' + i + ')').val());
                product_unit_price = (isNaN(product_unit_price) || product_unit_price < 0) ? 0 : product_unit_price;

                $('.total-sale-cost:eq(' + i + ')').text((product_quantity * product_unit_price).toFixed(2));
                total_quantity += product_quantity;
                sub_total += product_quantity * product_unit_price;

            });

            $('#total_quantity').text(total_quantity);
            $('#sub_total').val(sub_total.toFixed(2));

            let discountType = $('#discount_type').val() || '2';
            let discountInput = parseFloat($('#discount_percent').val()) || 0;
            let discountAmount = 0;

            if (discountType == '2') {
                // Percentage (%)
                discountAmount = (sub_total * discountInput) / 100;
                $('#total_discount').text('(Amt: ' + discountAmount.toFixed(2) + ' BDT)');
            } else {
                // Flat BDT
                discountAmount = discountInput;
                $('#total_discount').text('');
            }

            let vat_percent = parseFloat($('#vat_percent').val());
            vat_percent = (isNaN(vat_percent) || vat_percent < 0) ? 0 : vat_percent;

            let tax_percent = parseFloat($('#tax_percent').val());
            tax_percent = (isNaN(tax_percent) || vat_percent < 0) ? 0 : tax_percent;

            let vatAmount = (sub_total / 100) * vat_percent;
            let taxAmount = (sub_total / 100) * tax_percent;

            let total_receivable = (sub_total - discountAmount) + (vatAmount + taxAmount);

            $('#discount_amount').val(discountAmount);
            $('#total_vat').text(vatAmount.toFixed(2));
            $('#vat_amount').val(vatAmount);
            $('#total_tax').text(taxAmount.toFixed(2));
            $('#tax_amount').val(taxAmount);
            $('#total_receivable').val(total_receivable.toFixed(2));
            $('#total_amount').val(total_receivable.toFixed(2));
            let due_amount = total_receivable - paid_amount;
            $('#due_amount').val(due_amount.toFixed(2));

            if (rows.length > 0) {
                $(".footer_area").show();
            } else {
                $(".footer_area").hide();
            }
        }
    </script>
@endsection
