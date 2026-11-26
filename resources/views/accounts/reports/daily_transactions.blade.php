@extends('layouts.app')
@section('title', 'Daily Transactions')
@section('content')
<div class="card card-outline card-default">
    <div class="card-header"><h3 class="card-title">Daily Transactions — Filters</h3></div>
    <form action="{{ route('report.daily_transactions') }}" method="GET">
        <div class="card-body">
            <p class="text-muted">All posted income, expenses and ledger movements. Opening balances are excluded.</p>
            <div class="row">
                @foreach(['start_date' => 'Start Date', 'end_date' => 'End Date'] as $field => $label)
                <div class="col-md-3 form-group">
                    <label for="{{ $field }}">{{ $label }} <span class="text-danger">*</span></label>
                    <input id="{{ $field }}" type="text" name="{{ $field }}" class="form-control date-picker @error($field) is-invalid @enderror" value="{{ old($field, request($field, \Carbon\Carbon::parse($field === 'start_date' ? $startDate : $endDate)->format('d-m-Y'))) }}" required autocomplete="off">
                    @error($field)<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                @endforeach
                @if(auth()->user()->activeConcernId() === null)
                <div class="col-md-3 form-group">
                    <label for="branch_id">Branch</label>
                    <select id="branch_id" name="branch_id" class="form-control">
                        <option value="">All Branches</option>
                        @foreach($sister_concerns as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $branchId) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                @endif
                <div class="col-md-3 form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" class="form-control">
                        <option value="all">All Transactions</option>
                        @foreach($sectionNames as $key => $name)
                        <option value="{{ $key }}" @selected(old('category', $category) === $key)>{{ $name }}</option>
                        @endforeach
                    </select>
                    @error('category')<div class="text-danger">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" name="search" value="1" class="btn btn-info">Search</button>
            <a href="{{ route('report.daily_transactions') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div>
@endsection
