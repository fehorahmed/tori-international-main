@extends('layouts.app')

@section('title', 'Shomriddhi Summary Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-success text-white">
            <h5 class="card-title mb-0"><i class="fas fa-chart-bar mr-2"></i> Shomriddhi Summary Report</h5>
        </div>
        <div class="card-body">

            {{-- Filter Form --}}
            <form target="_blank" action="{{ route('shomriddhi-advance.reports.summary') }}" method="GET" class="mb-4">
                <div class="row align-items-end justify-content-center">
                    <div class="col-md-3">
                        <label>Start Date</label>
                        <input type="text" name="start_date" class="form-control date-picker" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label>End Date</label>
                        <input type="text" name="end_date" class="form-control date-picker" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" name="search" value="1" class="btn btn-success btn-block"><i class="fas fa-filter"></i> Generate</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('shomriddhi-advance.reports.summary') }}" class="btn btn-secondary btn-block"><i class="fas fa-redo"></i> Reset</a>
                    </div>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection


