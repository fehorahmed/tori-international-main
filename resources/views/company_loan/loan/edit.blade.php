@extends('layouts.app')
@section('title','Edit Loan Request')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Edit Loan Request</h3>
                </div>
                <form action="{{ route('company_loan.update', ['loan' => $loan->id]) }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="lender_concern_id">Lender (Sister Concern) <span class="text-danger">*</span></label>
                                    <select name="lender_concern_id" id="lender_concern_id" class="form-control select2" required>
                                        <option value="">Select Lender</option>
                                        @foreach($sisterConcerns as $concern)
                                            <option value="{{ $concern->id }}" {{ old('lender_concern_id', $loan->lender_concern_id) == $concern->id ? 'selected' : '' }}>{{ $concern->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="borrower_account_head_id">Receiving Account <span class="text-danger">*</span></label>
                                    <select name="borrower_account_head_id" id="borrower_account_head_id" class="form-control select2" required>
                                        <option value="">Select Account</option>
                                        @foreach($receivingAccounts as $account)
                                            <option value="{{ $account->id }}" {{ old('borrower_account_head_id', $loan->borrower_account_head_id) == $account->id ? 'selected' : '' }}>
                                                {{ $account->name }} ({{ $account->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('borrower_account_head_id')
                                    <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="amount">Loan Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="amount" id="amount" class="form-control" placeholder="Enter Amount" value="{{ old('amount', $loan->amount) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="interest_rate">Interest Rate (%) <span class="text-danger">*</span></label>
                                    <input type="number" step="any" name="interest_rate" id="interest_rate" class="form-control" placeholder="e.g. 10" value="{{ old('interest_rate', $loan->interest_rate) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="duration">Duration (Total Installments) <span class="text-danger">*</span></label>
                                    <input type="number" name="duration" id="duration" class="form-control" placeholder="e.g. 12" value="{{ old('duration', $loan->duration) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="calculation_method">Calculation Method <span class="text-danger">*</span></label>
                                    <select name="calculation_method" id="calculation_method" class="form-control select2" required>
                                        <option value="Flat" {{ old('calculation_method', $loan->calculation_method) == 'Flat' ? 'selected' : '' }}>Flat Rate</option>
                                        <option value="Reducing" {{ old('calculation_method', $loan->calculation_method) == 'Reducing' ? 'selected' : '' }}>Reducing Balance (EMI)</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="loan_type">Loan Type <span class="text-danger">*</span></label>
                                    <select name="loan_type" id="loan_type" class="form-control select2" required>
                                        <option value="Daily" {{ old('loan_type', $loan->loan_type) == 'Daily' ? 'selected' : '' }}>Daily</option>
                                        <option value="Weekly" {{ old('loan_type', $loan->loan_type) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                        <option value="Monthly" {{ old('loan_type', $loan->loan_type) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                                        <option value="Yearly" {{ old('loan_type', $loan->loan_type) == 'Yearly' ? 'selected' : '' }}>Yearly</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" name="start_date" id="start_date" class="form-control date-picker" value="{{ old('start_date', \Carbon\Carbon::parse($loan->start_date)->format('d-m-Y')) }}" autocomplete="off" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2" placeholder="Additional details...">{{ old('notes', $loan->notes) }}</textarea>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info bg-gradient-info">Update Request</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            $('.select2').select2();
            $('.date-picker').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                todayHighlight: true
            });
        });
    </script>
@endsection
