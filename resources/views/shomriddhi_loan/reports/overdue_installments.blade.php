@extends('layouts.app')
@section('title', 'Overdue Installments')
@section('content')
<div class="card card-default">
    <div class="card-header">
        <h5 class="card-title mb-0">
            <i class="fas fa-exclamation-circle mr-2"></i> Overdue Installments
        </h5>
    </div>
    <div class="card-body">
        <form target="_blank" action="" method="GET" class="mb-4">
            <input type="hidden" name="search" value="1">
            <div class="row align-items-end">
                <div class="col-md-12">
                    <p class="text-muted">Click the button below to generate a report of all overdue installments as of today.</p>
                </div>
                <div class="col-md-3">
                    <button type="submit" class="btn btn-danger btn-block"><i class="fas fa-print"></i> Print Overdue Report</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
