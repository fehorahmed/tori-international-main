@extends('layouts.app')
@section('title', $title)
@section('content')
<div class="container-fluid"><div class="card">
    <div class="card-header bg-gradient-info text-white"><h3 class="card-title">{{ $title }}</h3></div>
    <div class="card-body">
        <p>Branch: <strong>{{ $branch->code }} — {{ $branch->name }}</strong></p>
        <p class="text-muted">Date filter: {{ $dateLabel }}. Leave dates blank for all dates.</p>
        <form id="membership-report-filters" method="GET" target="_blank" action="{{ route('shomriddhi-advance.reports.'.$kind) }}">
                    @include('layouts.partial.__report_branch_filter')
            <div class="row">
                @foreach(['start_date' => 'From', 'end_date' => 'To'] as $field => $label)
                <div class="col-md-3 form-group">
                    <label for="{{ $field }}">{{ $dateLabel }} — {{ $label }} <small class="text-muted font-weight-normal">(Default Today)</small></label>
                    <input type="date" id="{{ $field }}" name="{{ $field }}" class="form-control" value="{{ old($field, request($field, now()->toDateString())) }}">
                    @error($field)<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                @endforeach
                @foreach($options as $field => [$label, $values])
                <div class="col-md-3 form-group">
                    <label for="{{ $field }}">{{ $label }}</label>
                    <select id="{{ $field }}" name="{{ $field }}" class="form-control report-select">
                        <option value="">All</option>
                        @foreach($values as $value => $text)
                        <option value="{{ $value }}" @selected((string) old($field, request($field)) === (string) $value)>{{ $text }}</option>
                        @endforeach
                    </select>
                    @error($field)<div class="text-danger">{{ $message }}</div>@enderror
                </div>
                @endforeach
            </div>
            <button type="submit" name="search" value="1" class="btn btn-info"><i class="fas fa-filter"></i> Generate Report</button>
            <a href="{{ route('shomriddhi-advance.reports.'.$kind) }}" class="btn btn-secondary">Reset</a>
        </form>
    </div>
</div></div>
@endsection
@section('script')
<script>
$(function () {
    $('.report-select').select2({width: '100%'});
    $('#membership-report-filters').on('submit', function (event) {
        const from = $('#start_date').val(), to = $('#end_date').val();
        if (from && to && to < from) {
            event.preventDefault();
            Swal.fire({icon: 'error', title: 'Invalid date range', text: 'To date must be on or after from date.'});
        }
    });
});
</script>
@endsection
