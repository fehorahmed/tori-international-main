@extends('layouts.app')
@section('title','Trainee Task Details')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-info-circle mr-2 text-primary"></i> Task Details: {{ $task->supplier->name }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('trainee.tasks.distribute', ['month' => \Carbon\Carbon::parse($task->date)->format('m-Y')]) }}" class="btn btn-info btn-sm">
                            <i class="fas fa-edit mr-1"></i> Edit Tasks
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-sm-6 text-center text-sm-left">
                            <h5 class="text-primary font-weight-bold mb-1">{{ \Carbon\Carbon::parse($task->date)->format('F, Y') }}</h5>
                            <p class="text-muted mb-0">Assigned Month</p>
                        </div>
                        <div class="col-sm-6 text-center text-sm-right mt-3 mt-sm-0">
                            <h5 class="mb-1">{{ $task->created_at->format('d M, Y') }}</h5>
                            <p class="text-muted mb-0">Assignment Date</p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="bg-light">
                            <tr>
                                <th>Task Type</th>
                                <th class="text-center">Target Value</th>
                                <th class="text-center">Actual Value</th>
                                <th class="text-center">Progress</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($task->items as $item)
                                @php
                                    $progress = $item->target_value > 0 ? ($item->actual_value / $item->target_value) * 100 : 0;
                                    $progress = min($progress, 100);
                                    $color = $progress >= 100 ? 'success' : ($progress >= 50 ? 'info' : 'warning');
                                @endphp
                                <tr>
                                    <td class="font-weight-bold">{{ $item->type->name ?? 'N/A' }}</td>
                                    <td class="text-center font-weight-bold text-primary">
                                        {{ $item->type && $item->type->unit == 'Amount' ? '৳' . number_format($item->target_value, 2) : number_format($item->target_value, 0) . ' ' . ($item->type->unit ?? '') }}
                                    </td>
                                    <td class="text-center font-weight-bold text-success">
                                        {{ $item->type && $item->type->unit == 'Amount' ? '৳' . number_format($item->actual_value, 2) : number_format($item->actual_value, 0) . ' ' . ($item->type->unit ?? '') }}
                                    </td>
                                    <td class="align-middle" width="200">
                                        <div class="progress progress-xs mb-1">
                                            <div class="progress-bar bg-{{ $color }}" style="width: {{ $progress }}%"></div>
                                        </div>
                                        <small class="text-{{ $color }} font-weight-bold">{{ number_format($progress, 1) }}% Completed</small>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-white border-top-0 text-center py-3">
                    <a href="{{ route('trainee.tasks.index') }}" class="btn btn-secondary px-5">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
