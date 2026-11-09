@extends('layouts.app')
@section('title', 'Daily Transactions')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-default">
            <div class="card-header">
                <h3 class="card-title">Data Filter</h3>
            </div>
            <form target="_blank" action="{{ route('report.daily_transactions') }}" method="GET">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date <span class="text-danger">*</span></label>
                                <input type="text" name="start_date" class="form-control date-picker" value="{{ request('start_date', date('d-m-Y', strtotime(auth()->user()->business_date))) }}" required autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date <span class="text-danger">*</span></label>
                                <input type="text" name="end_date" class="form-control date-picker" value="{{ request('end_date', date('d-m-Y', strtotime(auth()->user()->business_date))) }}" required autocomplete="off">
                            </div>
                        </div>
                        @if(auth()->user()->activeConcernId() == null && isset($sister_concerns))
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Branch</label>
                                <select name="branch_id" class="form-control">
                                    <option value="">All Branches</option>
                                    @foreach($sister_concerns as $branch)
                                        <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        <div class="col-md-2" style="margin-top: 31px;">
                            <input type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
