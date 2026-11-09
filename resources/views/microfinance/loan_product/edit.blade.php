@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .scheme-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .scheme-header { background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%); border-radius: 20px 20px 0 0 !important; padding: 25px; }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(40,167,69,0.1); border-color: #28a745; }
    .btn-submit { border-radius: 12px; padding: 12px 30px; font-weight: 600; transition: all 0.3s; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card scheme-card">
                <div class="card-header scheme-header text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-edit mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.loan-product.update', $loanProduct->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-5">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Scheme Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $loanProduct->name }}" placeholder="e.g. General Loan" required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Scheme Code <span class="text-danger">*</span></label>
                                <input type="text" name="code" class="form-control bg-light" value="{{ $loanProduct->code }}" placeholder="Auto-generated" readonly required>
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="form-label">Installment Frequency <span class="text-danger">*</span></label>
                                <select name="installment_frequency" id="installment_frequency" class="form-control select2" required>
                                    <option value="1" {{ $loanProduct->installment_frequency == 1 ? 'selected' : '' }}>Daily</option>
                                    <option value="2" {{ $loanProduct->installment_frequency == 2 ? 'selected' : '' }}>Weekly</option>
                                    <option value="3" {{ $loanProduct->installment_frequency == 3 ? 'selected' : '' }}>Monthly</option>
                                    <option value="4" {{ $loanProduct->installment_frequency == 4 ? 'selected' : '' }}>Quarterly (ত্রৈমাসিক)</option>
                                    <option value="5" {{ $loanProduct->installment_frequency == 5 ? 'selected' : '' }}>Half-Yearly (ষান্মাসিক)</option>
                                    <option value="6" {{ $loanProduct->installment_frequency == 6 ? 'selected' : '' }}>One-Time / At Maturity (এককালীন)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Interest Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="interest_rate" class="form-control" value="{{ $loanProduct->interest_rate }}" required>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Interest Type <span class="text-danger">*</span></label>
                                <select name="interest_type" class="form-control select2" required>
                                    <option value="1" {{ $loanProduct->interest_type == 1 ? 'selected' : '' }}>Flat</option>
                                    <option value="2" {{ $loanProduct->interest_type == 2 ? 'selected' : '' }}>Reducing Balance</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Insurance Fee Type</label>
                                <select name="insurance_fee_type" id="insurance_fee_type" class="form-control select2">
                                    <option value="percentage" {{ $loanProduct->insurance_fee_type == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="flat" {{ $loanProduct->insurance_fee_type == 'flat' ? 'selected' : '' }}>Flat Amount (৳)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label" id="insurance_label">Insurance Fee ({{ $loanProduct->insurance_fee_type == 'flat' ? '৳' : '%' }})</label>
                                <input type="number" step="0.01" name="insurance_fee_percentage" class="form-control" value="{{ $loanProduct->insurance_fee_percentage }}">
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="1" {{ $loanProduct->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $loanProduct->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label">Late Penalty Type</label>
                                <select name="late_penalty_type" id="late_penalty_type" class="form-control select2">
                                    <option value="percentage" {{ ($loanProduct->late_penalty_type ?? 'percentage') == 'percentage' ? 'selected' : '' }}>Percentage (%)</option>
                                    <option value="flat" {{ ($loanProduct->late_penalty_type ?? 'percentage') == 'flat' ? 'selected' : '' }}>Flat Amount (৳)</option>
                                </select>
                            </div>

                            <div class="col-md-4 mb-4">
                                <label class="form-label" id="penalty_label">Late Penalty Rate ({{ ($loanProduct->late_penalty_type ?? 'percentage') == 'flat' ? '৳' : '%' }}) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="late_penalty_rate" class="form-control" value="{{ $loanProduct->late_penalty_rate }}" required>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('microfinance.loan-product.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-success btn-submit shadow-sm"><i class="fa fa-save mr-2"></i> Update Scheme</button>
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

        var originalFrequency = "{{ $loanProduct->installment_frequency }}";
        var originalCode = "{{ $loanProduct->code }}";

        $('#installment_frequency').on('change', function() {
            var freq = $(this).val();
            if (freq && freq != originalFrequency) {
                $.get("{{ route('microfinance.loan-product.get-next-code') }}", { frequency: freq }, function(data) {
                    $('input[name="code"]').val(data.code);
                });
            } else if (freq == originalFrequency) {
                $('input[name="code"]').val(originalCode);
            }
        });

        $('#insurance_fee_type').on('change', function() {
            var type = $(this).val();
            if (type == 'percentage') {
                $('#insurance_label').text('Insurance Fee (%)');
            } else {
                $('#insurance_label').text('Insurance Fee (৳)');
            }
        });

        $('#late_penalty_type').on('change', function() {
            var type = $(this).val();
            if (type == 'percentage') {
                $('#penalty_label').text('Late Penalty Rate (%)');
            } else {
                $('#penalty_label').text('Late Penalty Rate (৳)');
            }
        });
    });
</script>
@endsection
