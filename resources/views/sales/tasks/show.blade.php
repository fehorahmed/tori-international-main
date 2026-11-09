@extends('layouts.app')
@section('title', $context['child_label'] . ' Task Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card card-default shadow-lg border-0" style="border-radius: 20px;">
                <div class="card-header bg-light d-flex justify-content-between align-items-center" style="border-radius: 20px 20px 0 0;">
                    <h3 class="card-title font-weight-bold text-dark">
                        <i class="fas fa-info-circle mr-2 text-primary"></i> 
                        {{ $context['child_label'] }} Goal Breakdown
                    </h3>
                    <a href="{{ route('tasks.index', $role) }}" class="btn btn-default btn-sm rounded-pill px-3">
                        <i class="fa fa-arrow-left mr-1"></i> Back to List
                    </a>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="info-box bg-soft-blue shadow-sm border">
                                <span class="info-box-icon bg-primary"><i class="fa fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Month</span>
                                    <span class="info-box-number">{{ \Carbon\Carbon::parse($task->date)->format('F, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm border">
                                <span class="info-box-icon bg-info"><i class="fa fa-user-tie"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">{{ $context['child_label'] }} Name</span>
                                    <span class="info-box-number">{{ $task->employee->name ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm border">
                                <span class="info-box-icon bg-success text-white"><i class="fa fa-tasks"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Assigned Tasks</span>
                                    <span class="info-box-number">{{ $task->items->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover border">
                            <thead class="bg-light">
                                <tr>
                                    <th>Task Type</th>
                                    <th class="text-right">Target Value</th>
                                    <th class="text-right">Actual Achievement</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($task->items as $item)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold">{{ $item->type->name }}</div>
                                            <small class="text-muted">{{ $item->type->unit }} based goal</small>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            @if($item->type->unit == 'Amount')
                                                ৳{{ number_format($item->target_value, 2) }}
                                            @else
                                                {{ number_format($item->target_value, 0) }} {{ $item->type->unit }}
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($item->type->unit == 'Amount')
                                                ৳{{ number_format($item->actual_value, 2) }}
                                            @else
                                                {{ number_format($item->actual_value, 0) }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $percent = $item->target_value > 0 ? ($item->actual_value / $item->target_value) * 100 : 0;
                                                $color = $percent >= 100 ? 'success' : ($percent >= 50 ? 'warning' : 'danger');
                                            @endphp
                                            <div class="progress progress-xs mb-1" style="width: 100px; margin: 0 auto;">
                                                <div class="progress-bar bg-{{ $color }}" style="width: {{ min($percent, 100) }}%"></div>
                                            </div>
                                            <span class="badge badge-{{ $color }}">{{ round($percent) }}%</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white text-muted small">
                    <i class="fas fa-history mr-1"></i> Assignment released on {{ $task->created_at->format('d M, Y h:i A') }} 
                    by {{ $task->creator->name ?? 'System' }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
