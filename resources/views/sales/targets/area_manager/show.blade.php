@extends('layouts.app')
@section('title', 'Area Manager Target Details')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title font-weight-bold">
                        <i class="fa fa-chart-line mr-2 text-primary"></i> 
                        Area Manager Targets: {{ \Carbon\Carbon::parse($header->month)->format('F Y') }}
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('sales_targets.area_manager.index') }}" class="btn btn-default btn-sm">
                            <i class="fa fa-arrow-left"></i> Back to List
                        </a>
                        <a href="{{ route('sales_targets.area_manager.edit', ['month' => $header->month, 'type' => $header->target_type]) }}" class="btn btn-info btn-sm">
                            <i class="fa fa-edit"></i> Edit Targets
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <div class="info-box bg-soft-blue shadow-none border">
                                <span class="info-box-icon bg-primary"><i class="fa fa-calendar-check"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Target Month</span>
                                    <span class="info-box-number text-primary h5 font-weight-bold mb-0">{{ \Carbon\Carbon::parse($header->month)->format('F, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-none border">
                                <span class="info-box-icon bg-purple" style="background-color: #6f42c1 !important; color: white;"><i class="fa fa-tag"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Target Type</span>
                                    <span class="info-box-number h5 font-weight-bold mb-0">{{ $header->target_type }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box bg-light shadow-none border">
                                <span class="info-box-icon bg-warning text-white"><i class="fa fa-users"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Targeted Managers</span>
                                    <span class="info-box-number h5 font-weight-bold mb-0">{{ $header->details->count() }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="info-box bg-soft-green shadow-none border">
                                <span class="info-box-icon bg-success"><i class="fa fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Total Enterprise Target</span>
                                    <span class="info-box-number text-success h4 font-weight-bold mb-0">৳{{ number_format($header->total_amount, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h5 class="font-weight-bold mb-3 text-dark">Manager-wise Distribution Breakdown</h5>
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 60px;" class="text-center">S/L</th>
                                    <th>Area Manager Name</th>
                                    <th>Contact Info</th>
                                    <th class="text-right">Assigned Target Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($header->details as $detail)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="font-weight-bold">{{ $detail->targetFor->name ?? 'N/A' }}</div>
                                            <small class="text-muted">ID: #{{ $detail->target_for_id }}</small>
                                        </td>
                                        <td>
                                            <div><i class="fa fa-phone-alt mr-1 small"></i> {{ $detail->targetFor->mobile_no ?? 'N/A' }}</div>
                                            <div><i class="fa fa-envelope mr-1 small"></i> {{ $detail->targetFor->email ?? 'N/A' }}</div>
                                        </td>
                                        <td class="text-right">
                                            <span class="h6 font-weight-bold">৳{{ number_format($detail->amount, 2) }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light">
                                    <th colspan="3" class="text-right font-weight-bold">Total Target Value:</th>
                                    <th class="text-right text-primary h5 font-weight-bold">৳{{ number_format($header->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
