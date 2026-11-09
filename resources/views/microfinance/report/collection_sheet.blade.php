@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fa fa-filter mr-2"></i> Collection Sheet Filter</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('microfinance.report.collection-sheet') }}" method="get" class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Select Somiti (সমিতি):</label>
                            <select name="somiti_id" class="form-control select2" required>
                                <option value="">Select Somiti</option>
                                @foreach($somitis as $s)
                                    <option value="{{ $s->id }}" {{ request('somiti_id') == $s->id ? 'selected' : '' }}>{{ $s->name }} ({{ $s->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold font-weight-bold">Date:</label>
                            <input type="date" name="date" class="form-control" value="{{ request('date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" name="search" class="btn btn-primary btn-block font-weight-bold"><i class="fa fa-search mr-1"></i> Generate Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
