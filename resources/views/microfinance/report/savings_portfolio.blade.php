@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header bg-dark text-white">
                    <h3 class="card-title font-weight-bold mb-0"><i class="fa fa-filter mr-2"></i> Filter Savings Portfolio</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('microfinance.report.savings-portfolio') }}" method="get" class="row align-items-end">
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Product Type:</label>
                            <select name="product_type" class="form-control select2">
                                <option value="">All Types</option>
                                <option value="1" {{ request('product_type') == 1 ? 'selected' : '' }}>General Savings (GS)</option>
                                <option value="2" {{ request('product_type') == 2 ? 'selected' : '' }}>DPS (Monthly Savings)</option>
                                <option value="3" {{ request('product_type') == 3 ? 'selected' : '' }}>FDR (Fixed Deposit)</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label font-weight-bold">Account Status:</label>
                            <select name="status" class="form-control select2">
                                <option value="">All Statuses</option>
                                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Pending</option>
                                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active</option>
                                <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <button type="submit" name="search" class="btn btn-dark btn-block font-weight-bold"><i class="fa fa-search mr-1"></i> Apply Filters</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
