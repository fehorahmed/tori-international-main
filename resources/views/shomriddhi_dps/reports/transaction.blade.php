@extends('layouts.app')

@section('title', 'DPS Transaction Report')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-list-alt mr-2"></i> DPS Transaction Report</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-dps.reports.transaction') }}" method="GET" class="mb-4">
                <input type="hidden" name="search" value="1">
                <div class="row align-items-end">
                    <div class="col-md-3">
                        <label for="type">Transaction Type</label>
                        <select name="type" id="type" class="form-control select2">
                            <option value="">All Types</option>
                            <option value="Deposit">Deposit</option>
                            <option value="Installment">Installment</option>
                            <option value="Penalty">Penalty</option>
                            <option value="Encashment">Encashment</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date">From Date</label>
                        <input type="text" name="start_date" id="start_date" class="form-control date-picker" autocomplete="off" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">To Date</label>
                        <input type="text" name="end_date" id="end_date" class="form-control date-picker" autocomplete="off" placeholder="dd-mm-yyyy">
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-info btn-block"><i class="fas fa-search"></i> Generate Report</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
