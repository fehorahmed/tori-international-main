@extends('layouts.app')
@section('title', 'Sales Return Report')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-outline card-primary">
            <div class="card-header">
                <h3 class="card-title">Sales Return Report</h3>
            </div>
            <div class="card-body">
                <form target="_blank" action="{{ route('returns-management.report.sales_returns') }}">
                    @include('layouts.partial.__report_branch_filter')
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Start Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                <input type="text" class="form-control date-picker" autocomplete="off" name="start_date" id="start_date" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>End Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                <input type="text" class="form-control date-picker" autocomplete="off" name="end_date" id="end_date" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}" required>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Customer</label>
                                <select class="form-control select2" name="customer_id" id="customer_id">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-md-3" style="margin-top: 30px;">
                            <button type="submit" name="search" value="1" class="btn btn-info px-5">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $('.select2').select2({
            theme: 'bootstrap4'
        });
    });
</script>
@endsection
