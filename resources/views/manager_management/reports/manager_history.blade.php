@extends('layouts.app')
@section('title', $title)
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
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $title }} - Filter</h3>
                </div>
                <div class="card-body">
                    @php
                        $currentRoute = Route::currentRouteName();
                    @endphp
                    <form action="{{ route($currentRoute) }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Select Manager</label>
                                    <select class="form-control select2" name="manager_id" required>
                                        <option value="">Select Manager</option>
                                        @foreach($managers as $manager)
                                            <option value="{{ $manager->id }}" {{ request()->get('manager_id') == $manager->id ? 'selected' : '' }}>
                                                {{ $manager->name }} ({{ $manager->mobile_no }})
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
                    <h3 class="card-title">
                        Active Branches: <span class="badge badge-success">{{ $assignments->where('status', 1)->count() }}</span>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h4>{{ $title }} for {{ $assignments[0]->manager->name }}</h4>
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead>
                        <tr>
                            <th>S/L</th>
                            <th>Branch (Sister Concern)</th>
                            <th>Assigned Date</th>
                            <th>Released Date</th>
                            <th>Status</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($assignments as $assignment)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $assignment->sisterConcern->name ?? 'N/A' }}</td>
                                <td>{{ \Carbon\Carbon::parse($assignment->assigned_date)->format('d-m-Y') }}</td>
                                <td>{{ $assignment->unassigned_date ? \Carbon\Carbon::parse($assignment->unassigned_date)->format('d-m-Y') : '--' }}</td>
                                <td>
                                    @if($assignment->status == 1)
                                        <span class="badge badge-success">Currently Active</span>
                                    @else
                                        <span class="badge badge-secondary">Transferred</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @elseif(request()->get('manager_id'))
                <div class="alert alert-info">No history found for this manager.</div>
            @endif
        </div>
    </div>
@endsection
