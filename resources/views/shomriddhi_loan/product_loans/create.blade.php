@extends('layouts.app')
@section('title','EMI Sale Create')
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
          action="{{ route('shomriddhi-loan.product-loans.store') }}"
          class="form-horizontal" id="sales-form" method="post">
        @csrf
        <div class="row">
            <!-- left column -->
            <div class="col-md-12">
                <!-- jquery validation -->
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Product Information (EMI Sale) </h3>
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
                                                {{ $product->name }}-
                                                @if($product->brand)
                                                    Brand: {{ $product->brand->name ?? '' }} -
                                                @endif
                                                @if($product->productCategory)
                                                    Category: {{ $product->productCategory->name ?? '' }} -
                                                @endif
                                                {{ $product->unit->name ?? '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2">
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
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_unit_price">Unit Price <span
                                             class="text-danger">*</span></label>
                                    <input type="number" step="any" class="form-control" id="add_unit_price"
                                           placeholder="Unit Price">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="add_interest_rate">Interest (%)</label>
                                    <input type="number" step="any" class="form-control" id="add_interest_rate"
                                           placeholder="Int %" value="0">
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
                                        <th class="text-center text-warning" width="10%">Stock</th>
                                        <th class="text-center" width="10%">Qty</th>
                                        <th class="text-center" width="12%">Unit Price</th>
                                        <th class="text-center" width="10%">Interest %</th>
                                        <th class="text-center" width="12%">Interest Amt</th>
                                        <th class="text-center" width="12%">Total Price</th>
                                        <th class="text-center" width="5%"></th>
                                    </tr> 
                                    </thead>
                                    <tbody id="product-container">
                                    @if(old('product_id'))
                                        @foreach(old('product_id') as $key => $productId)
                                            <tr class="product-item">
                                                <td class="text-center">
                                                    <span class="product_sl">{{ $loop->iteration }}</span>
                                                </td>
                                                <td class="text-left">
                                                    <span class="product_name">{{ old('product_name_val')[$key] }}</span>
                                                    <input type="hidden" name="product_name_val[]" class="product_name_val" value="{{ old('product_name_val')[$key] }}">
                                                    <input type="hidden" name="product_id[]" class="product_id" value="{{ $productId }}">
                                                    <input type="hidden" name="product_code[]" class="product_code" value="{{ old('product_code')[$key] }}">
                                                    <input type="hidden" name="inventory_id[]" class="inventory_id" value="{{ old('inventory_id')[$key] }}">
                                                </td>
                                                <td class="text-left">
                                                    <span class="product_batch_name">{{ old('product_batch_name_val')[$key] }}</span>
                                                    <input type="hidden" name="product_batch_name_val[]" class="product_batch_name_val" value="{{ old('product_batch_name_val')[$key] }}">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" class="form-control text-right product_stock" name="product_stock[]" value="{{ old('product_stock')[$key] }}" readonly>
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]" value="{{ old('product_quantity')[$key] }}">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]" value="{{ old('product_unit_price')[$key] }}">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" class="form-control text-right product_interest_rate" name="product_interest_rate[]" value="{{ old('product_interest_rate')[$key] ?? 0 }}">
                                                </td>
                                                <td class="text-right">
                                                    <input type="number" step="any" class="form-control text-right product_interest_amount" name="product_interest_amount[]" value="{{ old('product_interest_amount')[$key] ?? 0 }}" readonly>
                                                </td>
                                                <td class="text-right total-sale-cost">{{ number_format(old('product_quantity')[$key] * (old('product_unit_price')[$key] + (old('product_interest_amount')[$key] ?? 0)), 2) }}</td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    </tbody>
                                    <tfoot>
                                    <tr class="footer_area">
                                        <th colspan="4"></th>
                                        <th class="text-right" id="total_quantity"></th>
                                        <th colspan="3" class="text-right">Sub Total</th>
                                        <th class="text-right">
                                            <input type="number" step="any" readonly name="sub_total" id="sub_total"
                                                           class="form-control text-right" value="{{ old('sub_total', 0) }}">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                         <th colspan="8" class="text-right">
                                             <div class="d-flex justify-content-end align-items-center">
                                                 <span class="mr-2 font-weight-bold">Discount</span>
                                                 <select name="discount_type" id="discount_type" class="form-control form-control-sm" style="width: 150px; display: inline-block;">
                                                     <option value="2" {{ old('discount_type', '2') == '2' ? 'selected' : '' }}>Percentage (%)</option>
                                                     <option value="1" {{ old('discount_type') == '1' ? 'selected' : '' }}>Flat (টাকা)</option>
                                                 </select>
                                             </div>
                                         </th>
                                         <th class="text-right">
                                             <input type="number" value="{{ old('discount_percent') }}" step="any"
                                                        name="discount_percent"
                                                        id="discount_percent" class="form-control text-right">
                                                 <input type="hidden" name="discount_amount" id="discount_amount" value="{{ old('discount_amount') }}">
                                                 <span id="total_discount" class="text-muted small"></span>
                                         </th>
                                         <th></th>
                                     </tr>
                                    <input type="hidden" id="vat_amount" name="vat_amount">
                                    <tr class="footer_area">
                                        <th colspan="8" class="text-right">Total Receivable</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="total_receivable"
                                                       name="total_receivable" class="form-control text-right" value="{{ old('total_receivable', 0) }}">
                                                <input type="hidden" name="total_amount" id="total_amount" value="{{ old('total_amount', 0) }}">       
                                        </th>
                                        <th></th>
                                    </tr>

                                    <tr class="footer_area">
                                        <th colspan="8" class="text-right">Down Payment</th>
                                        <th class="text-right">
                                            <input type="number" step="any" id="paid_amount"
                                                       name="paid_amount" value="{{ old('paid_amount', 0) }}" class="form-control text-right">
                                        </th>
                                        <th></th>
                                    </tr>
                                    <tr class="footer_area">
                                        <th colspan="8" class="text-right">Due (EMI Amount)</th>
                                        <th class="text-right">
                                            <input type="number" readonly step="any" id="due_amount"
                                                       name="due_amount" class="form-control text-right" value="{{ old('due_amount', 0) }}">
                                        </th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>

                        <!-- EMI Information -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="text-info">EMI Information</h5>
                                <hr>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>No. of Installments <span class="text-danger">*</span></label>
                                    <input type="number" name="no_of_installment" id="no_of_installment" value="{{ old('no_of_installment', 1) }}" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Installment Amount</label>
                                    <input type="number" readonly name="installment_amount" id="installment_amount" class="form-control" value="{{ old('installment_amount', 0) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Installment Start Date <span class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" name="installment_start_date" id="installment_start_date" value="{{ old('installment_start_date', date('d-m-Y')) }}" class="form-control date-picker">
                                </div>
                            </div>
                        </div>

                        <!-- Customer, Guarantor & Security Documents Information -->
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <h5 class="text-success font-weight-bold">Customer, Guarantor & Security Documents (গ্রাহক, জামিনদার ও আইনি ডকুমেন্টস)</h5>
                                <hr>
                            </div>
                            
                            <!-- Customer Documents -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_nid_attachment">Customer NID Scan (গ্রাহকের এনআইডি স্ক্যান কপি)</label>
                                    <div class="custom-file">
                                        <input type="file" name="customer_nid_attachment" id="customer_nid_attachment" class="custom-file-input">
                                        <label class="custom-file-label" for="customer_nid_attachment">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="customer_photo_attachment">Customer Photo (গ্রাহকের ছবি)</label>
                                    <div class="custom-file">
                                        <input type="file" name="customer_photo_attachment" id="customer_photo_attachment" class="custom-file-input">
                                        <label class="custom-file-label" for="customer_photo_attachment">Choose file</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="guarantor_name">Guarantor Name (জামিনদারের নাম)</label>
                                    <input type="text" name="guarantor_name" id="guarantor_name" class="form-control" placeholder="Enter guarantor name" value="{{ old('guarantor_name') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="guarantor_phone">Guarantor Phone (জামিনদারের মোবাইল নম্বর)</label>
                                    <input type="text" name="guarantor_phone" id="guarantor_phone" class="form-control" placeholder="Enter guarantor phone" value="{{ old('guarantor_phone') }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="guarantor_nid">Guarantor NID (জামিনদারের এনআইডি)</label>
                                    <input type="text" name="guarantor_nid" id="guarantor_nid" class="form-control" placeholder="Enter guarantor NID" value="{{ old('guarantor_nid') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="guarantor_nid_attachment">Guarantor NID Scan (এনআইডি স্ক্যান কপি)</label>
                                    <div class="custom-file">
                                        <input type="file" name="guarantor_nid_attachment" id="guarantor_nid_attachment" class="custom-file-input">
                                        <label class="custom-file-label" for="guarantor_nid_attachment">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="security_cheque_attachment">Security Cheque Scan (নিরাপত্তা চেক স্ক্যান কপি)</label>
                                    <div class="custom-file">
                                        <input type="file" name="security_cheque_attachment" id="security_cheque_attachment" class="custom-file-input">
                                        <label class="custom-file-label" for="security_cheque_attachment">Choose file</label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="agreement_attachment">Agreement Stamp Scan (চুক্তিপত্র স্ক্যান কপি)</label>
                                    <div class="custom-file">
                                        <input type="file" name="agreement_attachment" id="agreement_attachment" class="custom-file-input">
                                        <label class="custom-file-label" for="agreement_attachment">Choose file</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Late Penalty Configuration -->
                        <div class="row mt-4">
                            <div class="col-12">
                                <h5 class="text-danger font-weight-bold">Late Penalty Configuration (বিলম্ব জরিমানা নির্ধারণ)</h5>
                                <hr>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="late_fee_type">Late Penalty Type (জরিমানার ধরন)</label>
                                    <select name="late_fee_type" id="late_fee_type" class="form-control">
                                        <option value="1" {{ old('late_fee_type', 1) == 1 ? 'selected' : '' }}>Fixed Amount per Day (ফ্ল্যাট প্রতিদিন)</option>
                                        <option value="2" {{ old('late_fee_type') == 2 ? 'selected' : '' }}>Percentage of Installment per Day (শতকরা প্রতিদিন)</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="late_fee_rate">Late Penalty Rate (জরিমানার হার) <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="late_fee_rate" id="late_fee_rate" class="form-control" min="0" value="{{ old('late_fee_rate', 10) }}" required>
                                </div>
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
                                        @foreach($warehouses as $warehouse)
                                            <option
                                                {{ old('warehouse',request('warehouse')) == $warehouse->id ? 'selected' : ''  }} value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
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
                                    <input type="text" autocomplete="off"
                                           name="date" id="date" class="form-control business-date-picker"
                                           placeholder="Enter date" readonly>
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
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
                                            <option {{ old('sr', $loggedSrId) == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
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
                                            class="text-danger">*</span></label>
                                    <select class="form-control select2" id="customer" name="customer">
                                        <option value="">Search Name of Customer</option>
                                    </select>
                                    @error('customer')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_method">Payment Method (for Down Payment)</label>
                                    <select class="form-control select2" name="payment_method" id="payment_method">
                                        <option value="">Select Payment Method</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ $paymentMode->id }}">{{ $paymentMode->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div id="advance_adjust_container" style="display: none; margin-top: 10px;">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="advance_adjust" name="advance_adjust" value="1">
                                        <label for="advance_adjust" class="custom-control-label text-success">
                                            Pay Down Payment from Advance (Bal: <span id="advance_amount_text">0.00</span>)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('memo_no') ? 'has-error' :'' }}">
                                    <label for="memo_no">Memo No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('memo_no') }}" name="memo_no" id="memo_no" class="form-control" placeholder="Enter Memo No." required>
                                    @error('memo_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="notes">Notes</label>
                                    <input type="text" name="notes" id="notes" value="{{ old('notes') }}" class="form-control" placeholder="Enter Notes">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button type="button" id="btn-save-sale" class="btn btn-info bg-gradient-info btn-sm">Save EMI Sale</button>
                        <a href="{{ route('shomriddhi-loan.product-loans.index') }}"
                           class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                </div>
            </div>
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
                <input type="hidden" name="product_code[]" class="product_code">
                <input type="hidden" name="inventory_id[]" class="inventory_id">
            </td>
            <td class="text-left">
                <span class="product_batch_name"></span>
                <input type="hidden" name="product_batch_name_val[]" class="product_batch_name_val">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_stock" name="product_stock[]" readonly>
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_quantity" name="product_quantity[]">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_unit_price" name="product_unit_price[]">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_interest_rate" name="product_interest_rate[]">
            </td>
            <td class="text-right">
                <input type="number" step="any" class="form-control text-right product_interest_amount" name="product_interest_amount[]" readonly>
            </td>
            <td class="text-right total-sale-cost"></td>
            <td class="text-center">
                <button type="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button>
            </td>
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
                    <!-- Customer, SR, Installments, Date, Payment Method & Note Meta Info -->
                    <div class="row mb-3 p-3 bg-light rounded border text-sm" style="font-size: 14px;">
                        <div class="col-md-4 border-right">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-user text-info mr-1"></i> Customer Name:</strong>
                                <span id="ov-customer" class="text-dark font-weight-bold ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-user-tie text-info mr-1"></i> SR Name:</strong>
                                <span id="ov-sr" class="text-dark ml-1">-</span>
                            </div>
                        </div>
                        <div class="col-md-4 border-right pl-md-3">
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-calendar-alt text-info mr-1"></i> Date:</strong>
                                <span id="ov-date" class="text-dark ml-1">-</span>
                            </div>
                            <div class="mb-2">
                                <strong class="text-secondary"><i class="fa fa-credit-card text-info mr-1"></i> Payment Method:</strong>
                                <span id="ov-payment-method" class="text-dark ml-1">-</span>
                            </div>
                            <div>
                                <strong class="text-secondary"><i class="fa fa-calendar-check text-info mr-1"></i> EMI:</strong>
                                <span id="ov-emi-details" class="text-dark ml-1">-</span>
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
                                    <th>Interest %</th>
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
                                    <th class="text-right">Down Payment:</th>
                                    <td class="text-right font-weight-bold" id="ov-paid" style="font-size: 16px;">0.00</td>
                                </tr>
                                <tr class="table-danger">
                                    <th class="text-right">Due Amount (EMI):</th>
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
            calculate();

            $('#warehouse').on('change', function () {
                let warehouse = $(this).val();
                let currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set('warehouse', warehouse);
                window.location.href = currentUrl.toString();
            });

            $('#customer').select2({
                ajax: {
                    url: "{{ route('party_json') }}",
                    type: "get",
                    dataType: 'json',
                    delay: 250,
                    data: function (params) {
                        return { type:['Customer'], searchTerm: params.term };
                    },
                    processResults: function (response) {
                        return { results: response };
                    },
                    cache: true
                }
            });

            $('#customer').on('change', function () {
                let customerId = $(this).val();
                
                $('#advance_adjust_container').hide();
                $('#advance_amount_text').text('0.00');
                window.availableAdvance = 0;
                
                if (customerId) {
                    preloaderToggle(true);
                    $.ajax({
                        url: '{{ route("customer_validation_check") }}',
                        type: 'GET',
                        data: { customer_id: customerId },
                        success: function(response) {
                            preloaderToggle(false);
                            if(response.has_pending_preorder) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Action Blocked',
                                    text: 'This customer has an unresolved Pre-Order. Please deliver or cancel it first.',
                                    allowOutsideClick: false
                                }).then(() => {
                                    $('#customer').val(null).trigger('change');
                                });
                            } else if(response.current_balance > 0) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Previous Due Found',
                                    text: 'This customer has an unpaid due of ' + parseFloat(response.current_balance).toFixed(2) + '. They cannot make a new order until it is cleared.',
                                    showCancelButton: true,
                                    confirmButtonText: 'Go to Payment',
                                    cancelButtonText: 'Cancel',
                                    allowOutsideClick: false
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        window.location.href = "{{ route('customer-payment.index') }}?customer_id=" + customerId;
                                    } else {
                                        $('#customer').val(null).trigger('change');
                                    }
                                });
                            } else if(response.current_balance < 0) {
                                let advanceAmount = Math.abs(response.current_balance);
                                window.availableAdvance = advanceAmount;
                                $('#advance_amount_text').text(parseFloat(advanceAmount).toFixed(2));
                                $('#advance_adjust_container').show();
                            }
                        },
                        error: function() {
                            preloaderToggle(false);
                        }
                    });
                }
            });

            $('body').on('change', '#select_product', function (e) {
                let warehouseId = $('#warehouse').val();
                let selectProductId = $(this).val();
                $('#select_batch').html('<option value="">Select Batch</option>');
                if (selectProductId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_batch') }}",
                        data: {id: selectProductId,warehouseId: warehouseId}
                    }).done(function (response) {
                        if (Array.isArray(response)) {
                            response.forEach(function (inventory) {
                                $('#select_batch').append(`<option value="${inventory.id}">Batch-${inventory.batch_no}</option>`);
                            });
                        }
                    });
                }
            });

            $('body').on('change', '#select_batch', function (e) {
                let selectBatchId = $('#select_batch').val();
                if (selectBatchId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_product_stock_details') }}",
                        data: { inventoryId: selectBatchId }
                    }).done(function (response) {
                        $('#add_stock').val(response.inventory.quantity ?? 0);
                        $('#add_inventory_id').val(response.inventory.id ?? 0);
                        $('#add_unit_price').val(response.inventory.selling_price ?? '');
                        $('#add_interest_rate').val(response.inventory.product.emi_interest ?? 0);
                    });
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectProduct = $('#select_product').val();
                let selectBatch = $('#select_batch').val();
                let addStock = $('#add_stock').val();
                let addInventoryId = $('#add_inventory_id').val();
                let addQuantity = $('#add_quantity').val();
                let addUnitPrice = $('#add_unit_price').val();
                let addInterestRate = $('#add_interest_rate').val() || 0;

                if (selectProduct == '') {
                    ajaxErrorMessage('Please, select product !');
                    return false;
                }
                if (selectBatch == '') {
                    ajaxErrorMessage('Please, select batch !');
                    return false;
                }
                if (addQuantity == '' || addQuantity <= 0) {
                    ajaxErrorMessage('Please, type valid product quantity !');
                    return false;
                }
                if (parseFloat(addQuantity) > parseFloat(addStock)) {
                    ajaxErrorMessage('Quantity cannot be greater than stock !');
                    return false;
                }
                if (addUnitPrice == '' || addUnitPrice <= 0) {
                    ajaxErrorMessage('Please, type valid product unit price !');
                    return false;
                }

                // Duplicate Batch Check - Auto Merge Quantities
                let existingItem = null;
                $('.product-item').each(function() {
                    if ($(this).find('.inventory_id').val() == addInventoryId) {
                        existingItem = $(this);
                    }
                });

                if (existingItem) {
                    let currentQty = parseFloat(existingItem.find('.product_quantity').val()) || 0;
                    let newQty = currentQty + parseFloat(addQuantity);
                    
                    if (newQty > parseFloat(addStock)) {
                        ajaxErrorMessage('Combined quantity cannot be greater than stock !');
                        return false;
                    }
                    
                    existingItem.find('.product_quantity').val(newQty);
                    calculate();
                    $('#select_product').val('').change();
                    $('#select_batch').html('<option value="">Select Batch</option>');
                    $('#add_quantity').val('');
                    $('#add_stock').val('');
                    $('#add_unit_price').val('');
                    $('#add_interest_rate').val('0');
                    $('#add_inventory_id').val('');
                    return false;
                }

                if (selectProduct != '' && addQuantity != '' && addUnitPrice != '') {
                    var html = $('#template-product').html();
                    var itemHtml = $(html);
                    $('#product-container').prepend(itemHtml);
                    var item = $('.product-item').first();
                    item.find('.product_name').text($("#select_product option:selected").text());
                    item.find('.product_name_val').val($("#select_product option:selected").text());
                    item.find('.product_batch_name').text($("#select_batch option:selected").text());
                    item.find('.product_batch_name_val').val($("#select_batch option:selected").text());
                    item.find('.product_id').val($("#select_product option:selected").val());
                    item.find('.product_code').val($("#select_product option:selected").data('product_code'));
                    item.find('.inventory_id').val(addInventoryId);
                    item.find('.product_stock').val(addStock);
                    item.find('.product_quantity').val(addQuantity);
                    item.find('.product_unit_price').val(addUnitPrice);
                    item.find('.product_interest_rate').val(addInterestRate);
                    calculate();
                    $('#select_product').val('').change();
                    $('#select_batch').html('<option value="">Select Batch</option>');
                    $('#add_quantity').val('');
                    $('#add_stock').val('');
                    $('#add_unit_price').val('');
                    $('#add_interest_rate').val('0');
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

            // File input label update
            $('body').on('change', '.custom-file-input', function() {
                let fileName = $(this).val().split('\\').pop();
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            });

            $('body').on('change', '#discount_type', function () {
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
            var sub_total = 0;
            var paid_amount = parseFloat($('#paid_amount').val()) || 0;
            $('.product-item').each(function () {
                let qty = parseFloat($(this).find('.product_quantity').val()) || 0;
                let price = parseFloat($(this).find('.product_unit_price').val()) || 0;
                let interestRate = parseFloat($(this).find('.product_interest_rate').val()) || 0;
                let interestAmount = (price * interestRate) / 100;
                $(this).find('.product_interest_amount').val(interestAmount.toFixed(2));

                let total = qty * (price + interestAmount);
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
            
            let due_amount = total_receivable - paid_amount;
            $('#due_amount').val(due_amount.toFixed(2));

            // EMI Calculation
            let noOfInstallment = parseInt($('#no_of_installment').val()) || 1;
            if (noOfInstallment < 1) noOfInstallment = 1;
            let installmentAmount = Math.round(due_amount / noOfInstallment);
            $('#installment_amount').val(installmentAmount);

            if (rows.length > 0) {
                $(".footer_area").show();
            } else {
                $(".footer_area").hide();
            }
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
                    let name = $(this).find('.product_name_val').val() || $(this).find('.product_name').text();
                    let batch = $(this).find('.product_batch_name_val').val() || $(this).find('.product_batch_name').text();
                    let qty = parseFloat($(this).find('.product_quantity').val() || 0);
                    let price = parseFloat($(this).find('.product_unit_price').val() || 0);
                    let interestRate = parseFloat($(this).find('.product_interest_rate').val() || 0);
                    let rowTotal = qty * (price + (price * interestRate / 100));
                    
                    let html = `
                        <tr>
                            <td>${sl++}</td>
                            <td class="text-left">${name}</td>
                            <td class="text-left">${batch}</td>
                            <td>${qty}</td>
                            <td class="text-right">${price.toFixed(2)}</td>
                            <td>${interestRate.toFixed(2)}%</td>
                            <td class="text-right">${rowTotal.toFixed(2)}</td>
                        </tr>
                    `;
                    $('#overview-items-body').append(html);
                });

                // Populate Metadata
                let customerText = $('#customer option:selected').val() ? $('#customer option:selected').text().trim() : 'N/A';
                let srText = $('#sr option:selected').val() ? $('#sr option:selected').text().trim() : 'N/A';
                let dateText = $('#date').val() || 'N/A';
                let paymentMethodText = $('#payment_method option:selected').val() ? $('#payment_method option:selected').text().trim() : 'N/A';
                let notesText = $('#notes').val() ? $('#notes').val().trim() : 'No notes added';

                let installmentsText = $('#no_of_installment').val() + ' installments of ' + $('#installment_amount').val() + ' BDT';

                $('#ov-customer').text(customerText);
                $('#ov-sr').text(srText);
                $('#ov-date').text(dateText);
                $('#ov-payment-method').text(paymentMethodText);
                $('#ov-emi-details').text(installmentsText);
                $('#ov-notes').text(notesText);

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
        }
    </script>
@endsection
