@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .card { border-radius: 20px; border: none; box-shadow: 0 5px 25px rgba(0,0,0,0.05); }
    .dark-mode .card { background: #2a2d34; color: #fff; }
    .form-control { border-radius: 12px; padding: 10px 15px; border: 1px solid #e0e6ed; }
    .dark-mode .form-control { background: #1f2228; border-color: #3a3f48; color: #fff; }
    .section-divider { border-bottom: 2px solid #eef2ff; margin: 30px 0; position: relative; }
    .dark-mode .section-divider { border-color: #3a3f48; }
    .section-title-badge { position: absolute; top: -12px; left: 0; background: #fff; padding: 0 15px; font-weight: 700; color: #4f46e5; text-transform: uppercase; font-size: 0.8rem; }
    .dark-mode .section-title-badge { background: #2a2d34; color: #818cf8; }
    .dark-mode .input-group-text { background: #3a3f48; border-color: #3a3f48; color: #fff; }
    .dark-mode label { color: #e2e8f0; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card product-card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-edit mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.savings-product.update', $savingsProduct->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-5">
                        <!-- Identity Section -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $savingsProduct->name }}" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Product Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control" value="{{ $savingsProduct->code }}" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Product Type <span class="text-danger">*</span></label>
                                <select name="product_type" id="product_type" class="form-control select2" required>
                                    {{-- <option value="1" {{ $savingsProduct->product_type == 1 ? 'selected' : '' }}>General Savings (GS)</option> --}}
                                    <option value="2" {{ $savingsProduct->product_type == 2 ? 'selected' : '' }}>DPS</option>
                                    <option value="3" {{ $savingsProduct->product_type == 3 ? 'selected' : '' }}>FDR (Fixed Deposit)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Configuration Section -->
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="interest_rate" class="form-control" value="{{ $savingsProduct->interest_rate }}" required>
                            </div>

                            <div class="col-md-4 mb-4 {{ in_array($savingsProduct->product_type, [2,3]) ? '' : 'd-none' }}" id="duration_div">
                                <label class="form-label">Duration (Months) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_months" class="form-control" value="{{ $savingsProduct->duration_months }}">
                            </div>

                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type == 2 ? '' : 'd-none' }}" id="installment_div">
                                <label class="form-label">Installment Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="installment_amount" class="form-control" value="{{ $savingsProduct->installment_amount }}">
                            </div>

                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type == 2 ? '' : 'd-none' }}" id="frequency_div">
                                <label class="form-label">Installment Frequency <span class="text-danger">*</span></label>
                                <select name="installment_frequency" class="form-control select2">
                                    <option value="1" {{ $savingsProduct->installment_frequency == 1 ? 'selected' : '' }}>Daily</option>
                                    <option value="2" {{ $savingsProduct->installment_frequency == 2 ? 'selected' : '' }}>Weekly</option>
                                    <option value="3" {{ $savingsProduct->installment_frequency == 3 ? 'selected' : '' }}>Monthly</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type == 3 ? '' : 'd-none' }}" id="interest_posting_div">
                                <label class="form-label text-warning">Interest Posting Type (For FDR) <span class="text-danger">*</span></label>
                                <select name="interest_posting_type" class="form-control select2">
                                    <option value="1" {{ $savingsProduct->interest_posting_type == 1 ? 'selected' : '' }}>At Maturity (মেয়াদ শেষে)</option>
                                    <option value="2" {{ $savingsProduct->interest_posting_type == 2 ? 'selected' : '' }}>Monthly (প্রতি মাসে)</option>
                                    <option value="3" {{ $savingsProduct->interest_posting_type == 3 ? 'selected' : '' }}>Quarterly (৩ মাস অন্তর)</option>
                                    <option value="4" {{ $savingsProduct->interest_posting_type == 4 ? 'selected' : '' }}>Yearly (প্রতি বছর)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4 {{ in_array($savingsProduct->product_type, [1, 3]) ? '' : 'd-none' }}" id="min_deposit_div">
                                <label class="form-label">Min Deposit Amount</label>
                                <input type="number" step="0.01" name="min_deposit" class="form-control" value="{{ $savingsProduct->min_deposit }}">
                            </div>

                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type == 1 ? '' : 'd-none' }}" id="max_deposit_div">
                                <label class="form-label">Max Deposit Amount</label>
                                <input type="number" step="0.01" name="max_deposit" class="form-control" value="{{ $savingsProduct->max_deposit }}">
                            </div>
                        </div>

                        <!-- Fees Section -->
                        <div class="section-divider">
                            <span class="section-title-badge text-success"><i class="fa fa-money-bill-wave mr-2"></i> Fees Section</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Account Opening Fee <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text" style="border-radius: 12px 0 0 12px;">৳</span></div>
                                    <input type="number" step="0.01" name="opening_fee" class="form-control" value="{{ $savingsProduct->opening_fee }}" style="border-radius: 0 12px 12px 0;" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Account Closing Fee <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text" style="border-radius: 12px 0 0 12px;">৳</span></div>
                                    <input type="number" step="0.01" name="closing_fee" class="form-control" value="{{ $savingsProduct->closing_fee }}" style="border-radius: 0 12px 12px 0;" required>
                                </div>
                            </div>
                            <!-- Installment Fine (Shows only for DPS) -->
                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type == 2 ? '' : 'd-none' }}" id="inst_fine_type_col">
                                <label class="form-label text-danger">Installment Fine Type</label>
                                <select name="installment_fine_type" class="form-control select2">
                                    <option value="flat" {{ $savingsProduct->installment_fine_type == 'flat' ? 'selected' : '' }}>Flat Amount (৳)</option>
                                    <option value="percentage" {{ $savingsProduct->installment_fine_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type == 2 ? '' : 'd-none' }}" id="inst_fine_amt_col">
                                <label class="form-label text-danger">Installment Fine Amount</label>
                                <input type="number" step="0.01" name="installment_fine_amount" class="form-control" value="{{ $savingsProduct->installment_fine_amount }}">
                            </div>

                            <!-- Early Closing Fine (Shows for DPS & FDR) -->
                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type != 1 ? '' : 'd-none' }}" id="early_fine_type_col">
                                <label class="form-label">Early Closing Fine Type</label>
                                <select name="early_closing_fine_type" class="form-control select2">
                                    <option value="flat" {{ $savingsProduct->early_closing_fine_type == 'flat' ? 'selected' : '' }}>Flat Amount (৳)</option>
                                    <option value="percentage" {{ $savingsProduct->early_closing_fine_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4 {{ $savingsProduct->product_type != 1 ? '' : 'd-none' }}" id="early_fine_amt_col">
                                <label class="form-label">Early Closing Fine Amount</label>
                                <input type="number" step="0.01" name="early_closing_fine_amount" class="form-control" value="{{ $savingsProduct->early_closing_fine_amount }}">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Scheme Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="1" {{ $savingsProduct->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $savingsProduct->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-right mt-4 pt-4 border-top">
                            <a href="{{ route('microfinance.savings-product.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-success btn-submit shadow-sm"><i class="fa fa-save mr-2"></i> Update Product Scheme</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });

        $('#product_type').on('change', function() {
            var type = $(this).val();
            
            // 1. Hide all conditional fields
            $('#duration_div, #installment_div, #frequency_div, #min_deposit_div, #max_deposit_div, #interest_posting_div').addClass('d-none');
            $('#inst_fine_type_col, #inst_fine_amt_col, #early_fine_type_col, #early_fine_amt_col').addClass('d-none');
            
            // 2. Remove 'required' from all conditional inputs
            $('#duration_div input, #installment_div input').prop('required', false);

            if (type == '1') { // GS
                $('#min_deposit_div, #max_deposit_div').removeClass('d-none');
            } else if (type == '2') { // DPS
                $('#duration_div, #installment_div, #frequency_div').removeClass('d-none');
                $('#inst_fine_type_col, #inst_fine_amt_col, #early_fine_type_col, #early_fine_amt_col').removeClass('d-none');
                $('#duration_div input, #installment_div input').prop('required', true);
            } else if (type == '3') { // FDR
                $('#duration_div, #min_deposit_div, #interest_posting_div, #early_fine_type_col, #early_fine_amt_col').removeClass('d-none');
                $('#duration_div input').prop('required', true);
            }
        });
    });
</script>
@endsection
