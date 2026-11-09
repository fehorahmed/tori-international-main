@extends('layouts.app')

@section('title', 'Attendance & Leave')

@section('content')
<div class="row">
    <!-- Leave Balance -->
    <div class="col-md-6 mb-4">
        <div class="card card-primary card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">My Leave Balance</h3>
                <div class="card-tools">
                    <button class="btn btn-sm btn-primary">Apply Leave</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-4 border-right">
                        <h4 class="mb-0 text-success font-weight-bold">0</h4>
                        <small class="text-muted">Casual</small>
                    </div>
                    <div class="col-4 border-right">
                        <h4 class="mb-0 text-warning font-weight-bold">0</h4>
                        <small class="text-muted">Sick</small>
                    </div>
                    <div class="col-4">
                        <h4 class="mb-0 text-info font-weight-bold">0</h4>
                        <small class="text-muted">Annual</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Overview -->
    <div class="col-md-6 mb-4">
        <div class="card card-info card-outline h-100">
            <div class="card-header">
                <h3 class="card-title">Recent Attendance</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class="table table-hover table-striped">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Check In</th>
                            <th>Check Out</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- This will be dynamic when integrated with Attendance -->
                        <tr><td colspan="4" class="text-center text-muted py-4">No recent attendance records found.</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
