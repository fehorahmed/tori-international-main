@extends('layouts.app')
@section('title', 'Loan Ledger')
@section('content')
<div class="card card-default">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-book mr-2"></i> Loan Ledger Report
        </h5>
    </div>
    <div class="card-body">
        <form target="_blank" action="" method="GET">
            <input type="hidden" name="search" value="1">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Select Loan Account <span class="text-danger">*</span></label>
                        <select name="loan_id" class="form-control select2" required>
                            <option value="">-- Select Loan --</option>
                            @foreach($loans as $loan)
                                <option value="{{ $loan->id }}">
                                    {{ $loan->loan_no }} - {{ $loan->customer->name ?? 'N/A' }} 
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate Ledger</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>


@endsection
