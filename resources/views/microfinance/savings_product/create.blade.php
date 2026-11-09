@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .product-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .product-header { background: linear-gradient(135deg, #007bff 0%, #0056b3 100%); border-radius: 20px 20px 0 0 !important; padding: 25px; }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(0,123,255,0.1); border-color: #007bff; }
    .btn-submit { border-radius: 12px; padding: 12px 30px; font-weight: 600; transition: all 0.3s; }
    .section-divider { border-top: 1px solid #e2e8f0; margin: 30px 0; position: relative; }
    .section-title-badge { position: absolute; top: -12px; left: 20px; background: #fff; padding: 0 15px; font-weight: 700; color: #007bff; text-transform: uppercase; font-size: 0.85rem; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card product-card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-plus-circle mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.savings-product.store') }}" method="POST">
                    @csrf
                    <div class="card-body p-5">
                        <!-- Identity Section -->
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="e.g. General Savings" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Product Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control bg-light" placeholder="Auto-generated" readonly required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Product Type <span class="text-danger">*</span></label>
                                <select name="product_type" id="product_type" class="form-control select2" required>
                                    {{-- <option value="1">General Savings (GS)</option> --}}
                                    <option value="2">DPS</option>
                                    <option value="3">FDR (Fixed Deposit)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Configuration Section -->
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="interest_rate" class="form-control" value="0.00" required>
                            </div>

                            <div class="col-md-4 mb-4 d-none" id="duration_div">
                                <label class="form-label">Duration (Months) <span class="text-danger">*</span></label>
                                <input type="number" name="duration_months" class="form-control" placeholder="Total months">
                            </div>

                            <div class="col-md-4 mb-4 d-none" id="installment_div">
                                <label class="form-label">Installment Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="installment_amount" class="form-control" placeholder="Monthly amount">
                            </div>

                            <div class="col-md-4 mb-4 d-none" id="frequency_div">
                                <label class="form-label">Installment Frequency <span class="text-danger">*</span></label>
                                <select name="installment_frequency" class="form-control select2">
                                    <option value="1">Daily</option>
                                    <option value="2">Weekly</option>
                                    <option value="3" selected>Monthly</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4 d-none" id="interest_posting_div">
                                <label class="form-label text-warning">Interest Posting Type (For FDR) <span class="text-danger">*</span></label>
                                <select name="interest_posting_type" class="form-control select2">
                                    <option value="1">At Maturity (মেয়াদ শেষে)</option>
                                    <option value="2">Monthly (প্রতি মাসে)</option>
                                    <option value="3">Quarterly (৩ মাস অন্তর)</option>
                                    <option value="4">Yearly (প্রতি বছর)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4" id="min_deposit_div">
                                <label class="form-label">Min Deposit Amount</label>
                                <input type="number" step="0.01" name="min_deposit" class="form-control" value="0.00">
                            </div>

                            <div class="col-md-4 mb-4" id="max_deposit_div">
                                <label class="form-label">Max Deposit Amount</label>
                                <input type="number" step="0.01" name="max_deposit" class="form-control">
                            </div>
                        </div>

                        <!-- Fees Section -->
                        <div class="section-divider">
                            <span class="section-title-badge"><i class="fa fa-money-bill-wave mr-2"></i> Fees Section</span>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Account Opening Fee <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text" style="border-radius: 12px 0 0 12px;">৳</span></div>
                                    <input type="number" step="0.01" name="opening_fee" class="form-control" value="0.00" style="border-radius: 0 12px 12px 0;" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label class="form-label">Account Closing Fee <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend"><span class="input-group-text" style="border-radius: 12px 0 0 12px;">৳</span></div>
                                    <input type="number" step="0.01" name="closing_fee" class="form-control" value="0.00" style="border-radius: 0 12px 12px 0;" required>
                                </div>
                            </div>
                            <!-- Installment Fine (Shows only for DPS) -->
                            <div class="col-md-4 mb-4 d-none" id="inst_fine_type_col">
                                <label class="form-label text-danger">Installment Fine Type</label>
                                <select name="installment_fine_type" class="form-control select2">
                                    <option value="flat">Flat Amount (৳)</option>
                                    <option value="percentage">Percentage (%)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4 d-none" id="inst_fine_amt_col">
                                <label class="form-label text-danger">Installment Fine Amount</label>
                                <input type="number" step="0.01" name="installment_fine_amount" class="form-control" value="0.00">
                            </div>

                            <!-- Early Closing Fine (Shows for DPS & FDR) -->
                            <div class="col-md-4 mb-4 d-none" id="early_fine_type_col">
                                <label class="form-label">Early Closing Fine Type</label>
                                <select name="early_closing_fine_type" class="form-control select2">
                                    <option value="flat">Flat Amount (৳)</option>
                                    <option value="percentage">Percentage (%)</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4 d-none" id="early_fine_amt_col">
                                <label class="form-label">Early Closing Fine Amount</label>
                                <input type="number" step="0.01" name="early_closing_fine_amount" class="form-control" value="0.00">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Scheme Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-right mt-4 pt-4 border-top">
                            <a href="{{ route('microfinance.savings-product.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-submit shadow-sm"><i class="fa fa-save mr-2"></i> Save Product Scheme</button>
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
            
            // Generate Dynamic Code
            if (type) {
                $.get("{{ route('microfinance.savings-product.get-next-code') }}", { type: type }, function(data) {
                    $('input[name="code"]').val(data.code);
                });
            }

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

        // Trigger on load
        $('#product_type').trigger('change');
    });
</script>
@endsection
