@extends('layouts.app')
@section('title', 'Branch Ledger')
@section('content')
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Branch Ledger — Data Filter</h3></div>
    <form method="GET" action="{{ route('branch_report.branch_ledger') }}" target="_blank">
        @include('layouts.partial.__report_branch_filter')
        <div class="card-body"><p class="text-muted">Branch-wise total Debit, Credit and Balance across all account postings. Click a branch in the summary for its detailed ledger. Dates default to Today.</p><div class="row">
            @foreach(['start_date' => 'From Date', 'end_date' => 'To Date'] as $name => $label)
            <div class="col-md-3 form-group"><label for="{{ $name }}">{{ $label }} <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                <input id="{{ $name }}" name="{{ $name }}" type="text" class="form-control date-picker" autocomplete="off" value="{{ old($name, request($name, now()->format('d-m-Y'))) }}" required>
                @error($name)<div class="text-danger">{{ $message }}</div>@enderror
            </div>@endforeach
        </div></div>
        <div class="card-footer"><button name="search" value="1" class="btn btn-primary">Generate Report</button> <a href="{{ route('branch_report.branch_ledger') }}" class="btn btn-secondary">Reset</a></div>
    </form>
</div>
@endsection
