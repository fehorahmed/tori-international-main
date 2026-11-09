@extends('layouts.app')

@section('title', 'DPS Account Ledger')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-dark text-white">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice mr-2"></i> DPS Account Ledger</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.ledger') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-6">
                        <label for="account_id">Select DPS Account <span class="text-danger">*</span></label>
                        <select name="account_id" id="account_id" class="form-control select2" required>
                            <option value="">-- Choose Account --</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}">{{ $account->dps_no }} - {{ $account->customer->name ?? '' }} ({{ $account->status }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-dark btn-block"><i class="fas fa-search"></i> Generate Ledger</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
