@extends('layouts.app')

@section('title', 'Daily Accrued Interest Logs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-history mr-2"></i> Daily Accrued Interest Logs
                    </h5>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('shomriddhi-advance.reports.daily_interest') }}" method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <label>Filter Status</label>
                                <select name="status" class="form-control">
                                    <option value="">All (Pending & Capitalized)</option>
                                    <option value="Pending">Pending Only</option>
                                    <option value="Capitalized">Capitalized Only</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label>Customer</label>
                                <select name="customer_id" class="form-control select2">
                                    <option value="">All Customers</option>
                                    @php
                                        $customers = \App\Models\ShomriddhiAdvance\ShomriddhiAccount::with('customer')
                                            ->where('origin_sister_concern_id', auth()->user()->activeConcernId())
                                            ->get()
                                            ->pluck('customer.name', 'customer_id');
                                    @endphp
                                    @foreach($customers as $id => $name)
                                        @if($name)
                                            <option value="{{ $id }}">{{ $name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label>Start Date</label>
                                <input type="text" name="start_date" class="form-control date-picker" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-md-2">
                                <label>End Date</label>
                                <input type="text" name="end_date" class="form-control date-picker" placeholder="DD-MM-YYYY">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="search" value="1" class="btn btn-primary btn-block"><i class="fa fa-filter"></i> Generate</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
$(function () {
    $('.select2').select2();
});
</script>
@endsection
