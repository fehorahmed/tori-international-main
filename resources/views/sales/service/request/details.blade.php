@extends('layouts.app')
@section('title','Service Request Details')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header bg-gradient-info">
                    <h3 class="card-title">Service Details: {{ $service->service_no }}</h3>
                    <div class="card-tools">
                        @if($service->status == 3 && auth()->user()->hasPermissionTo('service_approval'))
                            <form action="{{ route('service.request.approve', ['service' => $service->id]) }}" method="post" style="display: inline;">
                                @csrf
                                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Are you sure you want to approve this service?')">Approve & Finalize</button>
                            </form>
                        @endif
                        <button onclick="window.print()" class="btn btn-default btn-sm"><i class="fa fa-print"></i> Print</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Service No</th>
                                    <td>{{ $service->service_no }}</td>
                                </tr>
                                <tr>
                                    <th>Date</th>
                                    <td>{{ $service->date }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @php
                                            $status = [
                                                1 => '<span class="badge badge-warning">Pending</span>',
                                                2 => '<span class="badge badge-info">In Progress</span>',
                                                3 => '<span class="badge badge-primary">Waiting for Approval</span>',
                                                4 => '<span class="badge badge-success">Completed</span>',
                                                5 => '<span class="badge badge-secondary">Delivered</span>',
                                            ];
                                            echo $status[$service->status] ?? '';
                                        @endphp
                                    </td>
                                </tr>
                                <tr>
                                    <th>Service Charge</th>
                                    <td>Tk {{ number_format($service->service_charge, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>Customer</th>
                                    <td>{{ $service->customer->name ?? '' }} ({{ $service->customer->mobile_no ?? '' }})</td>
                                </tr>
                                <tr>
                                    <th>Original Order</th>
                                    <td>{{ $service->saleOrder->order_no ?? '' }} ({{ $service->sale_type }})</td>
                                </tr>
                                <tr>
                                    <th>Technician</th>
                                    <td>{{ $service->technician->name ?? '' }}</td>
                                </tr>
                                <tr>
                                    <th>Creator</th>
                                    <td>{{ $service->user->name ?? '' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Service Items (Reported Problems)</h5>
                            <table class="table table-bordered table-striped">
                                <thead class="bg-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Problem (Cause)</th>
                                    <th>Warranty</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($service->serviceItems as $item)
                                    <tr>
                                        <td>{{ $item->product->name ?? '' }} ({{ $item->product->code ?? '' }})</td>
                                        <td>{{ $item->cause }}</td>
                                        <td><span class="badge badge-success">Active</span></td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <h5>Utilized Products (Spare Parts)</h5>
                            <table class="table table-bordered table-striped">
                                <thead class="bg-light">
                                <tr>
                                    <th>Product</th>
                                    <th>Quantity</th>
                                    <th>Unit Price</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $totalParts = 0; @endphp
                                @forelse($service->serviceUtilizedItems as $item)
                                    @php $totalParts += ($item->quantity * $item->unit_price); @endphp
                                    <tr>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>Tk {{ number_format($item->unit_price, 2) }}</td>
                                        <td>Tk {{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">No spare parts used.</td>
                                    </tr>
                                @endforelse
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total Parts Cost:</th>
                                    <th>Tk {{ number_format($totalParts, 2) }}</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-md-12">
                            <div class="alert alert-secondary">
                                <strong>Notes:</strong><br>
                                {{ $service->notes ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    @if($service->status == 4)
                        <div class="row mt-2">
                            <div class="col-md-12">
                                <p class="text-muted">Approved by {{ $service->approver->name ?? '' }} at {{ $service->approved_at }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
