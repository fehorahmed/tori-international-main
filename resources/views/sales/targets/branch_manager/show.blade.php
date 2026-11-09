@extends('layouts.app')
@section('title', 'Branch Target Details')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fa fa-info-circle mr-2"></i> 
                        Target Details for {{ \Carbon\Carbon::parse($header->month)->format('F Y') }} ({{ $header->target_type }})
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('sales_targets.branch_manager.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('sales_targets.branch_manager.edit', ['month' => $header->month, 'type' => $header->target_type]) }}" class="btn btn-info btn-sm">
                            <i class="fa fa-edit"></i> Edit Targets
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm">
                                <span class="info-box-icon bg-info"><i class="fa fa-calendar"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Month</span>
                                    <span class="info-box-number">{{ \Carbon\Carbon::parse($header->month)->format('F, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm">
                                <span class="info-box-icon bg-purple" style="background-color: #6f42c1 !important; color: white;"><i class="fa fa-tag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Target Type</span>
                                    <span class="info-box-number">{{ $header->target_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-sm border">
                                <span class="info-box-icon bg-warning text-white"><i class="fa fa-building"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Branches</span>
                                    <span class="info-box-number">{{ $header->details->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="info-box bg-soft-blue border shadow-sm">
                                <span class="info-box-icon bg-primary"><i class="fa fa-arrow-down"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">My Assigned Target</span>
                                    <span class="info-box-number h4 font-weight-bold mb-0">৳{{ number_format($amTargetAmount ?? 0, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-soft-green border shadow-sm">
                                <span class="info-box-icon bg-success"><i class="fa fa-share-alt"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Distributed Target</span>
                                    <span class="info-box-number h4 font-weight-bold mb-0">৳{{ number_format($header->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="font-weight-bold mb-3">Branch-wise Allocation</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="bg-gradient-info text-white">
                                <tr>
                                    <th style="width: 50px;">S/L</th>
                                    <th>Sister Concern / Branch</th>
                                    <th>Branch Manager</th>
                                    <th class="text-right">Target Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($header->details as $detail)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <span class="font-weight-bold text-dark">{{ $detail->sisterConcern->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            {{ $detail->sisterConcern->branchManager->name ?? 'Not Assigned' }}
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            ৳{{ number_format($detail->amount, 2) }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-light">
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right">৳{{ number_format($header->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
