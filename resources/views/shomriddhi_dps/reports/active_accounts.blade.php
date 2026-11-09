@extends('layouts.app')

@section('title', 'DPS Active Accounts Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-success text-white">
            <h5 class="card-title mb-0"><i class="fas fa-check-circle mr-2"></i> DPS Active Accounts Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.active-accounts') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="scheme_id">DPS Scheme</label>
                        <select name="scheme_id" id="scheme_id" class="form-control select2">
                            <option value="">All Schemes</option>
                            @foreach($schemes as $scheme)
                                <option value="{{ $scheme->id }}">{{ $scheme->name }} ({{ $scheme->duration_months }} Months)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
