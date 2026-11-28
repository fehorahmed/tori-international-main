@extends('layouts.app')
@section('title', 'Branch General Ledger')
@section('content')
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">Branch General Ledger — Data Filter</h3></div>
    <form method="GET" action="{{ route('branch_report.ledger') }}" target="_blank">
        @include('layouts.partial.__report_branch_filter')
        <div class="card-body"><div class="row">
            <div class="col-md-4 form-group"><label for="account_head_id">Account Head</label>
                <select id="account_head_id" name="account_head_id" class="form-control select2"><option value="">All Account Heads</option>
                    @foreach($accountHeadsList as $head)<option value="{{ $head->id }}" @selected(old('account_head_id', request('account_head_id', request('account_head'))) == $head->id)>{{ $head->code }} — {{ $head->name }}</option>@endforeach
                </select>@error('account_head_id')<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            @foreach(['start_date' => 'From Date', 'end_date' => 'To Date'] as $name => $label)
            <div class="col-md-3 form-group"><label for="{{ $name }}">{{ $label }} <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                <input id="{{ $name }}" name="{{ $name }}" type="text" class="form-control date-picker" autocomplete="off" value="{{ old($name, request($name, now()->format('d-m-Y'))) }}" required>
                @error($name)<div class="text-danger">{{ $message }}</div>@enderror
            </div>@endforeach
        </div></div>
        <div class="card-footer"><button name="search" value="1" class="btn btn-primary">Generate Report</button> <a href="{{ route('branch_report.ledger') }}" class="btn btn-secondary">Reset</a></div>
    </form>
</div>
@endsection
