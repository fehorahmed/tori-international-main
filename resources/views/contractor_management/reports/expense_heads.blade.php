@extends('layouts.app')
@section('title', 'Expense Heads Report')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Generate Report</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('contractor_management.reports.expense_heads') }}" method="GET">
                    @include('layouts.partial.__report_branch_filter')
                        <div class="row">
                            <div class="col-md-4 offset-md-2">
                                <div class="form-group">
                                    <label>Start Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                    <input type="text" class="form-control date-picker" name="start_date" value="{{ old('start_date', request('start_date', now()->format('d-m-Y'))) }}" required autocomplete="off">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>End Date <small class="text-muted font-weight-normal">(Default Today)</small></label>
                                    <input type="text" class="form-control date-picker" name="end_date" value="{{ old('end_date', request('end_date', now()->format('d-m-Y'))) }}" required autocomplete="off">
                                </div>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-md-3 mt-4 text-center">
                                <button type="submit" name="search" value="1" class="btn btn-info btn-block bg-gradient-info">Generate Report</button>
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
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy'
        });
    });
</script>
@endsection
