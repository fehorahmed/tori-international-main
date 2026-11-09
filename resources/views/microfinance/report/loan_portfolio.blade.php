@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fa fa-filter mr-2"></i> Filter Loan Portfolio</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('microfinance.report.loan-portfolio') }}" method="get" class="row align-items-end">
                        <div class="col-md-6 mb-3">
                            <label class="form-label font-weight-bold">Loan Status:</label>
                            <select name="status" class="form-control select2">
                                <option value="">All Statuses</option>
                                <option value="1" {{ request('status') == 1 ? 'selected' : '' }}>Pending</option>
                                <option value="2" {{ request('status') == 2 ? 'selected' : '' }}>Approved</option>
                                <option value="3" {{ request('status') == 3 ? 'selected' : '' }}>Active</option>
                                <option value="4" {{ request('status') == 4 ? 'selected' : '' }}>Closed</option>
                                <option value="5" {{ request('status') == 5 ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <button type="submit" name="search" class="btn btn-dark btn-block font-weight-bold"><i class="fa fa-search mr-1"></i> Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
