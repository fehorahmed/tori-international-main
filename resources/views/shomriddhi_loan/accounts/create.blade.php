@extends('layouts.app')
@section('title', 'Create Loan Account')
@section('content')
<div class="card card-default">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-plus-circle mr-2"></i> New Shomriddhi Loan (Cash)
        </h5>
        <div class="card-tools">
            <a href="{{ route('shomriddhi-loan.accounts.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>
    </div>
    <form action="{{ route('shomriddhi-loan.accounts.store') }}" method="POST">
        @csrf
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Customer *</label>
                        <select name="customer_id" class="form-control select2" required>
                            <option value="">Select Customer</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->phone }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Branch / Sister Concern *</label>
                        <select name="origin_sister_concern_id" class="form-control select2" required>
                            <option value="">Select Branch</option>
                            @foreach($sisterConcerns as $sc)
                                <option value="{{ $sc->id }}" {{ auth()->user()->activeConcernId() == $sc->id ? 'selected' : '' }}>{{ $sc->code }} - {{ $sc->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Loan Type *</label>
                        <select name="type" class="form-control" required readonly>
                            <option value="cash" selected>Cash Loan</option>
                        </select>
                        <small class="text-muted">Product (EMI) loans are created from Sales.</small>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Principal Amount (৳) *</label>
                        <input type="number" step="0.01" name="principal_amount" id="principal_amount" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Profit Rate (%) *</label>
                        <input type="number" step="0.01" name="profit_rate" id="profit_rate" class="form-control" value="0" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>Disbursement Date *</label>
                        <input type="text" name="disbursement_date" class="form-control date-picker" value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Installment Type *</label>
                        <select name="installment_type" class="form-control" required>
                            <option value="weekly">Weekly</option>
                            <option value="monthly">Monthly</option>
                            <option value="daily">Daily</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Total Installments *</label>
                        <input type="number" name="total_installments" id="total_installments" class="form-control" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Calculated Payable (৳)</label>
                        <input type="text" id="calculated_payable" class="form-control font-weight-bold" readonly>
                        <small class="text-success" id="installment_amount_display"></small>
                    </div>
                </div>
            </div>
        </div>
        <div class="card-footer text-right">
            <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Save & Generate Schedule</button>
        </div>
    </form>
</div>
@endsection
@section('script')
<script>
    $(function() {
        function calculate() {
            let principal = parseFloat($('#principal_amount').val()) || 0;
            let rate = parseFloat($('#profit_rate').val()) || 0;
            let totalInst = parseInt($('#total_installments').val()) || 1;

            let totalPayable = principal + (principal * rate / 100);
            $('#calculated_payable').val(totalPayable.toFixed(2));
            
            if(totalInst > 0) {
                let perInst = totalPayable / totalInst;
                $('#installment_amount_display').text('Per Installment: ৳ ' + perInst.toFixed(2));
            } else {
                $('#installment_amount_display').text('');
            }
        }

        $('#principal_amount, #profit_rate, #total_installments').on('input change', calculate);
    });
</script>
@endsection
