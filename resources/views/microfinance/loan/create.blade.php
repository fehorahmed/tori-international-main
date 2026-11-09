@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .loan-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .loan-header { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border-radius: 20px 20px 0 0 !important; padding: 25px; }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(40,167,69,0.1); border-color: #28a745; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #28a745; margin-bottom: 25px; display: flex; align-items: center; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: #eee; margin-left: 15px; }
    .btn-submit { border-radius: 12px; padding: 12px 30px; font-weight: 600; transition: all 0.3s; }
    .alert-guard { background-color: #f0fdf4; border: 1px dashed #28a745; border-radius: 12px; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card loan-card">
                <div class="card-header loan-header text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-file-invoice-dollar mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.loan.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body p-5">
                        
                        <!-- Section 1: Member & Scheme Selection -->
                        <div class="section-title"><i class="fa fa-user-tag mr-2"></i> Member & Scheme Selection</div>
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Select Member <span class="text-danger">*</span></label>
                                <select name="member_id" class="form-control select2" required>
                                    <option value="">Select Member</option>
                                    @foreach($members as $member)
                                        <option value="{{ $member->id }}" {{ old('member_id') == $member->id ? 'selected' : '' }}>
                                            {{ $member->name }} ({{ $member->member_no }}) - {{ $member->somiti->name ?? 'No Somiti' }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="mt-2 p-2 alert-guard small text-success">
                                    <i class="fa fa-info-circle mr-1"></i> ঋণ আবেদনের পূর্বে সদস্যের একটি সাধারণ সঞ্চয় হিসাব (GS Account) সচল থাকা বাধ্যতামূলক।
                                </div>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Select Loan Scheme <span class="text-danger">*</span></label>
                                <select name="mf_loan_product_id" id="mf_loan_product_id" class="form-control select2" required>
                                    <option value="">Select Scheme</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}" 
                                                data-insurance-type="{{ $product->insurance_fee_type }}" 
                                                data-insurance-rate="{{ $product->insurance_fee_percentage }}"
                                                data-interest-rate="{{ $product->interest_rate }}"
                                                data-interest-type="{{ $product->interest_type }}"
                                                data-frequency="{{ $product->installment_frequency }}"
                                                {{ old('mf_loan_product_id') == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }} ({{ $product->interest_rate }}%)
                                        </option>
                                    @endforeach
                                </select>
                                
                                <!-- Scheme Details Preview Card -->
                                <div id="scheme-details-card" class="mt-3 p-3 rounded shadow-sm d-none" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px !important;">
                                    <h6 class="font-weight-bold text-success mb-2" style="font-size: 0.9rem;"><i class="fa fa-info-circle mr-1"></i> স্কিম বিবরণী (Scheme Details)</h6>
                                    <div class="row text-muted small" style="font-size: 0.85rem;">
                                        <div class="col-6 mb-1">ফ্রিকোয়েন্সি: <strong id="preview-frequency" class="text-dark">-</strong></div>
                                        <div class="col-6 mb-1">সুদের হার: <strong id="preview-interest" class="text-dark">-</strong></div>
                                        <div class="col-6 mb-1">বীমা ফি: <strong id="preview-insurance" class="text-dark">-</strong></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Section 2: Loan Parameters -->
                        <div class="section-title mt-4"><i class="fa fa-coins mr-2"></i> Loan Parameters</div>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Applied Amount <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="applied_amount" class="form-control text-right" placeholder="0.00" value="{{ old('applied_amount') }}" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Total Installments <span class="text-danger">*</span></label>
                                <input type="number" name="total_installments" class="form-control" placeholder="e.g. 46" value="{{ old('total_installments') }}" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Grace Period (Installments)</label>
                                <input type="number" name="grace_period_days" class="form-control" value="{{ old('grace_period_days', 0) }}" min="0">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Application Date <span class="text-danger">*</span></label>
                                <input type="text" name="application_date" class="form-control date-picker" value="{{ old('application_date', date('d-m-Y')) }}" readonly required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Processing Fee (প্রসেসিং ফি)</label>
                                <input type="number" step="any" name="processing_fee" class="form-control text-right" value="{{ old('processing_fee', '0.00') }}">
                                <small class="text-muted">Specify the processing fee for this loan application.</small>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Insurance Amount (বীমা প্রিমিয়াম)</label>
                                <input type="number" step="any" name="insurance_amount" class="form-control text-right" value="{{ old('insurance_amount', '0.00') }}">
                                <small class="text-muted">Auto-calculated as per Scheme's Insurance Type (Flat or %).</small>
                            </div>
                        </div>

                        <!-- Section 3: Guarantor & Collateral -->
                        <div class="section-title mt-4"><i class="fa fa-shield-alt mr-2"></i> Guarantor & Collateral</div>
                        <div class="row">
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Guarantor Name</label>
                                <input type="text" name="guarantor_name" class="form-control" value="{{ old('guarantor_name') }}">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Guarantor Phone</label>
                                <input type="text" name="guarantor_phone" class="form-control" value="{{ old('guarantor_phone') }}">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Guarantor NID (এনআইডি)</label>
                                <input type="text" name="guarantor_nid" class="form-control" value="{{ old('guarantor_nid') }}">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Guarantor Photo</label>
                                <input type="file" name="guarantor_photo" class="form-control">
                            </div>
                            <div class="col-md-12 mb-4">
                                <label class="form-label">Collateral Description</label>
                                <textarea name="collateral_description" class="form-control" rows="2" placeholder="Describe items given as security...">{{ old('collateral_description') }}</textarea>
                            </div>
                        </div>

                        <!-- Submit Buttons -->
                        <div class="text-right mt-4">
                            <a href="{{ route('microfinance.loan.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-success btn-submit text-white">Submit Application</button>
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

        if ($.isFunction($.fn.datepicker)) {
            $('.date-picker').datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true
            });
        }

        function calculateFees() {
            var selectedOption = $('#mf_loan_product_id').find('option:selected');
            if (selectedOption.val()) {
                var insType = selectedOption.data('insurance-type') || 'percentage';
                var insRate = parseFloat(selectedOption.data('insurance-rate')) || 0;
                var appliedAmt = parseFloat($('input[name="applied_amount"]').val()) || 0;

                var interestRate = parseFloat(selectedOption.data('interest-rate')) || 0;
                var interestType = parseInt(selectedOption.data('interest-type')) || 1;
                var frequency = parseInt(selectedOption.data('frequency')) || 2;

                // Auto-fill Insurance Amount based on Type (flat or percentage)
                var insAmt = 0;
                if (insType === 'flat') {
                    insAmt = insRate; // flat rate is the absolute amount
                } else {
                    insAmt = (appliedAmt * insRate) / 100; // percentage of applied amount
                }
                $('input[name="insurance_amount"]').val(insAmt.toFixed(2));

                // 3. Populate and Show Scheme Preview Card
                var freqText = 'Weekly (সাপ্তাহিক)';
                if (frequency === 1) freqText = 'Daily (দৈনিক)';
                else if (frequency === 2) freqText = 'Weekly (সাপ্তাহিক)';
                else if (frequency === 3) freqText = 'Monthly (মাসিক)';
                else if (frequency === 4) freqText = 'Quarterly (ত্রৈমাসিক)';
                else if (frequency === 5) freqText = 'Half-Yearly (ষান্মাসিক)';
                else if (frequency === 6) freqText = 'One-Time (এককালীন)';

                var interestTypeText = interestType === 2 ? 'Reducing (হ্রাসমান)' : 'Flat (ফিক্সড)';
                var insLabel = insType === 'flat' ? '৳ ' + insRate.toFixed(2) + ' (ফিক্সড)' : insRate.toFixed(2) + '% (শতকরা)';

                $('#preview-frequency').text(freqText);
                $('#preview-interest').text(interestRate.toFixed(2) + '% (' + interestTypeText + ')');
                $('#preview-insurance').text(insLabel);
                
                $('#scheme-details-card').removeClass('d-none');
            } else {
                $('input[name="processing_fee"]').val('0.00');
                $('input[name="insurance_amount"]').val('0.00');
                $('#scheme-details-card').addClass('d-none');
            }
        }

        $('#mf_loan_product_id, input[name="applied_amount"]').on('change keyup', function() {
            calculateFees();
        });

        // Trigger initially if old input has selected scheme
        if ($('#mf_loan_product_id').val()) {
            calculateFees();
        }
    });
</script>
@endsection
