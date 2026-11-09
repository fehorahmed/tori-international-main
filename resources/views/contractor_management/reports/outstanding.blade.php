@extends('layouts.app')
@section('title', 'Contractor Outstanding Report')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Generate Outstanding Report</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('contractor_management.reports.outstanding') }}" method="GET">
                        <div class="row justify-content-center">
                            <div class="col-md-3 mt-2 text-center">
                                <button type="submit" name="search" value="1" class="btn btn-info btn-block bg-gradient-info">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
