@extends('layouts.app')
@section('title', 'Purchase Return Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th { border: 1px solid #000000 !important; padding: 5px; }
        @media print {
            @page { size: auto; margin: .5in !important; }
            .btn-print { display: none; }
            .main-footer, .content-header, .card-header { display: none !important; }
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header text-right">
                <a href="{{ route('returns-management.purchase-returns.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" onclick="window.print()" class="btn btn-info btn-sm btn-print"><i class="fa fa-print"></i> Print</button>
            </div>
            
            <div class="card-body">
                <div id="printArea">
                    @include('layouts.partial.__report_header')
                    <div class="text-center mt-3 mb-4">
                        <h4 style="text-decoration: underline; font-weight: bold;">PURCHASE RETURN CHALLAN</h4>
                        @if($purchaseReturn->trashed())
                            <h4 class="text-danger font-weight-bold mt-2">-- CANCELLED / VOIDED --</h4>
                        @endif
                    </div>

                    <div class="row mb-4">
                        <div class="col-6">
                            <strong>Supplier:</strong> {{ $purchaseReturn->supplier->name ?? 'N/A' }}<br>
                            <strong>Phone:</strong> {{ $purchaseReturn->supplier->mobile ?? 'N/A' }}<br>
                            <strong>Address:</strong> {{ $purchaseReturn->supplier->address ?? 'N/A' }}
                        </div>
                        <div class="col-6 text-right">
                            <strong>Return No:</strong> {{ $purchaseReturn->return_no }}<br>
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($purchaseReturn->date)->format('d-m-Y') }}<br>
                            <strong>Defect Warehouse:</strong> {{ $purchaseReturn->warehouse->name ?? 'N/A' }}<br>
                            <strong>Status:</strong> 
                            @if($purchaseReturn->status == 1)
                                <span class="badge badge-warning">Draft</span>
                            @elseif($purchaseReturn->status == 2)
                                <span class="badge badge-success">Sent / Approved</span>
                            @else
                                <span class="badge badge-primary">Replaced</span>
                            @endif
                        </div>
                    </div>

                    <h5 class="font-weight-bold">Returned Items Details</h5>
                    <table class="table table-bordered mb-4">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-center">Sl</th>
                                <th>Product Name</th>
                                <th class="text-center">Returned Qty</th>
                                <th class="text-right">Unit Price</th>
                                <th class="text-right">Total Value</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchaseReturn->items as $index => $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ number_format($item->returned_qty, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->returned_qty * $item->unit_price, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr class="font-weight-bold bg-light">
                                <td colspan="4" class="text-right">Total Return Value:</td>
                                <td class="text-right">{{ number_format($purchaseReturn->total_return_value, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="row mt-5 pt-5 signature-area">
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid #000; width: 60%;">
                            <p>Store / Defect Warehouse In-charge</p>
                        </div>
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid #000; width: 60%;">
                            <p>Prepared By ({{ $purchaseReturn->createUser->name ?? '' }})</p>
                        </div>
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid #000; width: 60%;">
                            <p>Supplier / Receiver Signature</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
