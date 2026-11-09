@extends('layouts.app')

@section('title', 'FDR Scheme Summary Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-chart-pie mr-2"></i> FDR Scheme Summary Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-fdr.reports.scheme-summary') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
