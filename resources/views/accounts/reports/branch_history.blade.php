@extends('layouts.app')
@section('title','Branch Manager History Report')
@section('style')
    <style>
        @media print {
            .no-print { display: none; }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-primary no-print">
                <div class="card-header">
                    <h3 class="card-title">Filter</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.branch_manager_history') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Branch (Sister Concern)</label>
                                    <select class="form-control select2" name="sister_concern_id" required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ request()->get('sister_concern_id') == $branch->id ? 'selected' : '' }}>
                                                {{ $branch->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-primary btn-block">Search</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(count($assignments) > 0)
            <div class="card">
                <div class="card-header">
                    <button onclick="window.print()" class="btn btn-default btn-sm float-right"><i class="fa fa-print"></i> Print</button>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>Management History of Branch: {{ $assignments[0]->sisterConcern->name }}</h4>
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>S/L</th>
                            <th>Manager Name</th>
                            <th>Manager Type</th>
                            <th>Mobile No</th>
                            <th>Assigned Date</th>
                            <th>Released Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $assignment->manager->name ?? 'N/A' }}</td>
                                <td>{{ $assignment->manager_type }}</td>
                                <td>{{ $assignment->manager->mobile_no ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($assignment->assigned_date)->format('d-m-Y') }}</td>
                                <td>{{ $assignment->unassigned_date ? \Carbon\Carbon::parse($assignment->unassigned_date)->format('d-m-Y') : '--' }}</td>
                                <td>
                                    @if($assignment->status == 1)
                                        <span class="badge badge-success">Current Manager</span>
                                    @else
                                        <span class="badge badge-secondary">Past Manager</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(request()->get('sister_concern_id'))
                <div class="alert alert-info">No management history found for this branch.</div>
            @endif
        </div>
    </div>
@endsection
