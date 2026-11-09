@extends('layouts.app')
@section('title', 'Collect Loan Installment')
@section('style')
<style>
    body{
        overflow-x: hidden;
    }
</style>
@endsection
@section('content')
<div class="card card-default">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-hand-holding-usd mr-2"></i> Collect Installment
        </h5>
    </div>
    <form action="{{ route('shomriddhi-loan.collections.store') }}" method="POST">
        @csrf
        <div class="card-body">
                    
                    <div class="form-group">
                        <label>Select Loan Account *</label>
                        <select name="loan_account_id" id="loan_account_id" class="form-control select2" style="width: 100%;" required onchange="window.location.href='{{ route('shomriddhi-loan.collections.create') }}/' + this.value">
                            <option value="">-- Select Active Loan --</option>
                            @foreach($activeLoans as $active)
                                <option value="{{ $active->id }}" {{ (isset($loan) && $loan->id == $active->id) ? 'selected' : '' }}>
                                    {{ $active->loan_no }} - {{ $active->customer->name ?? '' }} 
                                    (Remaining: ৳ {{ number_format($active->total_payable - $active->paid_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    @if(isset($loan))
                    <div class="alert alert-info mt-3 d-flex justify-content-between">
                        <div>
                            <strong>Customer:</strong> {{ $loan->customer->name ?? 'N/A' }} <br>
                            <strong>Total Payable:</strong> ৳ {{ number_format($loan->total_payable, 2) }}
                        </div>
                        <div class="text-right">
                            <strong>Total Paid:</strong> ৳ {{ number_format($loan->paid_amount, 2) }} <br>
                            <strong class="text-danger">Total Due: ৳ {{ number_format($loan->total_payable - $loan->paid_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="form-group mt-3">
                        <label>Collection Amount (৳) *</label>
                        <input type="number" step="0.01" name="amount" class="form-control form-control-lg font-weight-bold text-success" 
                            max="{{ $loan->total_payable - $loan->paid_amount }}" 
                            value="{{ $loan->installments->first() ? ($loan->installments->first()->total_amount - $loan->installments->first()->paid_amount) : 0 }}" required>
                        <small class="text-muted">The amount will automatically distribute using FIFO among unpaid installments.</small>
                    </div>

                    <div class="form-row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Date *</label>
                                <input type="text" name="collection_date" class="form-control date-picker" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Payment Mode *</label>
                                <select name="payment_mode" class="form-control" required>
                                    @foreach($paymentModes as $pm)
                                        <option value="{{ $pm->id }}|{{ $pm->name }}">{{ $pm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Notes</label>
                        <textarea name="notes" class="form-control" rows="2"></textarea>
                    </div>

                    @endif
        </div>
        <div class="card-footer text-right">
            @if(isset($loan))
                <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Submit Collection</button>
            @endif
        </div>
    </form>
</div>
@endsection
