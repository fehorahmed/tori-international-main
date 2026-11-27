@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="card card-outline card-primary">
    <div class="card-header"><h3 class="card-title">{{ $title }} — Data Filter</h3></div>
    <form method="GET" action="{{ route($route) }}" target="_blank">
                    @include('layouts.partial.__report_branch_filter')
        <div class="card-body"><div class="row">
            <div class="col-md-4 form-group">
                <label for="{{ $field }}">{{ ucfirst($kind) }} <span class="text-danger">*</span></label>
                <select id="{{ $field }}" name="{{ $field }}" class="form-control select2" required>
                    <option value="">Select {{ ucfirst($kind) }}</option>
                    @foreach($parties as $party)<option value="{{ $party->id }}" @selected(old($field, request($field)) == $party->id)>{{ $party->name }} / {{ $party->member_no ?: $party->id }} / {{ $party->mobile_no }}</option>@endforeach
                </select>
                @error($field)<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            @foreach(['start_date' => 'From Date', 'end_date' => 'To Date'] as $name => $label)
            <div class="col-md-3 form-group"><label for="{{ $name }}">{{ $label }} <span class="text-danger">*</span> <small class="text-muted font-weight-normal">(Default Today)</small></label>
                <input type="text" id="{{ $name }}" name="{{ $name }}" class="form-control date-picker" autocomplete="off" value="{{ old($name, request($name, now()->format('d-m-Y'))) }}" required>
                @error($name)<div class="text-danger">{{ $message }}</div>@enderror
            </div>
            @endforeach
        </div></div>
        <div class="card-footer"><button class="btn btn-primary" name="search" value="1">Generate Report</button> <a href="{{ route($route) }}" class="btn btn-secondary">Reset</a></div>
    </form>
</div>
@endsection
