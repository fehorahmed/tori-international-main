@extends('layouts.app')

@section('title', 'FDR Account Ledger')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice mr-2"></i> FDR Account Ledger (Passbook)</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-fdr.reports.ledger') }}" method="GET" class="mb-4">
                <div class="row align-items-end">
                    <div class="col-md-5">
                        <label for="account_id">Select FDR Account <span class="text-danger">*</span></label>
                        <select name="account_id" id="account_id" class="form-control select2" required style="width: 100%;">
                            <option value="">Select an Account</option>
                            @foreach($accounts as $account)
                                <option value="{{ $account->id }}" {{ request('account_id') == $account->id ? 'selected' : '' }}>
                                    {{ $account->fdr_no }} - {{ $account->customer ? $account->customer->name : 'N/A' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }
    });
</script>
@endpush
