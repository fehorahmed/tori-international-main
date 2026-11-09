@extends('layouts.app')
@section('title', 'Area Manager Sales Targets')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="{{ route('sales_targets.area_manager.create') }}" class="btn btn-info bg-gradient-info btn-sm">Set New Targets <i class="fa fa-plus"></i></a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Month</th>
                                <th>Target Type</th>
                                <th>Manager Count</th>
                                <th>Total Target Amount</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($targets as $target)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ \Carbon\Carbon::parse($target->month)->format('F Y') }}</td>
                                    <td>{{ $target->target_type }}</td>
                                    <td>{{ $target->details_count }}</td>
                                    <td>{{ number_format($target->total_amount, 2) }} TK</td>
                                    <td>
                                        <a href="{{ route('sales_targets.area_manager.show', $target->id) }}" class="btn btn-primary btn-sm">
                                            <i class="fa fa-eye"></i> View
                                        </a>
                                        <a href="{{ route('sales_targets.area_manager.edit', ['month' => $target->month, 'type' => $target->target_type]) }}" class="btn btn-info btn-sm">
                                            <i class="fa fa-edit"></i> Edit
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
