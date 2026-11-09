@extends('layouts.app')
@section('title', 'Stock Transfer Delivery Challan')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 6px;
            font-size: 16px !important;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
        }
        .challan-header {
            border-bottom: 2px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .signature-area {
            margin-top: 80px;
        }
        @media print {
            .btn-print-area {
                display: none !important;
            }
            .main-footer {
                display: none !important;
            }
            .content-header {
                display: none !important;
            }
            .navbar {
                display: none !important;
            }
            .main-sidebar {
                display: none !important;
            }
            .table-bordered td, .table-bordered th {
                font-size: 16px !important;
            }
            body {
                background: #fff;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row btn-print-area">
        <div class="col-12 mb-3">
            <button onclick="window.print()" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i> Print Challan</button>
            <a href="{{ route('stock-transfers.details', ['stock_transfer' => $stockTransfer->id]) }}" class="btn btn-secondary bg-gradient-secondary btn-sm float-right"><i class="fa fa-arrow-left"></i> Back to Invoice Details</a>
        </div>
    </div>

    <div class="card card-default">
        <div class="card-body">
            <div id="printAreaChallan">
                @include('layouts.partial.__report_header')
                
                <div class="text-center challan-header">
                    <h3 style="font-weight: bold; text-transform: uppercase; letter-spacing: 2px; margin-top: 5px;">Delivery Challan & Gate Pass</h3>
                </div>

                <div class="row pt-2 pb-2">
                    <div class="col-6">
                        <p class="m-0"><strong>From Branch/Warehouse:</strong> {{ $stockTransfer->fromSisterConcern->name ?? ($stockTransfer->fromWarehouse->name ?? 'N/A') }}</p>
                        <p class="m-0"><strong>To Branch/Warehouse:</strong> {{ $stockTransfer->toSisterConcern->name ?? ($stockTransfer->toWarehouse->name ?? 'N/A') }}</p>
                    </div>
                    <div class="col-6 text-right">
                        <p class="m-0"><strong>Challan / Order No:</strong> {{ $stockTransfer->order_no }}</p>
                        <p class="m-0"><strong>Date:</strong> {{ \Carbon\Carbon::parse($stockTransfer->date)->format('d-m-Y') }}</p>
                        <p class="m-0"><strong>Status:</strong> <span class="badge badge-success">Dispatched</span></p>
                    </div>
                </div>

                <!-- Driver & Vehicle Details for Logistics Gate Pass -->
                <div class="row pb-3 mb-2" style="border-top: 1px dashed #000; border-bottom: 1px dashed #000; padding-top: 8px;">
                    <div class="col-4">
                        <p class="m-0"><strong>Vehicle No:</strong> {{ $stockTransfer->vehicle_no ?? 'N/A' }}</p>
                    </div>
                    <div class="col-4 text-center">
                        <p class="m-0"><strong>Driver Name:</strong> {{ $stockTransfer->driver_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-4 text-right">
                        <p class="m-0"><strong>Driver Mobile:</strong> {{ $stockTransfer->driver_phone ?? 'N/A' }}</p>
                    </div>
                </div>

                <table class="table table-bordered w-100 mt-2">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 5%">S/L</th>
                            <th class="text-left" style="width: 60%">Product Description & Batch No.</th>
                            <th class="text-center" style="width: 15%">Product Code</th>
                            <th class="text-center" style="width: 20%">Quantity (Dispatched)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stockTransfer->stockTransferItems as $item)
                            <tr>
                                <td class="text-center">{{ $loop->iteration }}</td>
                                <td>
                                    <strong>{{ $item->product->name ?? '' }}</strong>
                                    @if($item->inventory && $item->inventory->batch_no)
                                        <br><small class="text-muted">Batch: {{ $item->inventory->batch_no }}</small>
                                    @endif
                                </td>
                                <td class="text-center">{{ $item->product_code }}</td>
                                <td class="text-center" style="font-weight: bold;">
                                    {{ number_format($item->quantity) }} {{ $item->product->unit->name ?? '' }}
                                </td>
                            </tr>
                        @endforeach
                        <tr>
                            <th colspan="3" class="text-right">Total Dispatched Qty:</th>
                            <th class="text-center">{{ number_format($stockTransfer->stockTransferItems->sum('quantity')) }} Items</th>
                        </tr>
                    </tbody>
                </table>

                @if($stockTransfer->notes)
                    <div class="row mt-4">
                        <div class="col-12">
                            <p class="m-0"><strong>Remarks/Notes:</strong> {{ $stockTransfer->notes }}</p>
                        </div>
                    </div>
                @endif

                <div class="row signature-area" style="margin-top: 80px;">
                    <div class="col text-center">
                        <span style="border-top: 1px solid #000; display: inline-block; padding-top: 5px; width: 60%; font-weight: bold;">
                            Prepared By (Store Keeper)
                        </span>
                    </div>
                    <div class="col text-center">
                        <span style="border-top: 1px solid #000; display: inline-block; padding-top: 5px; width: 60%; font-weight: bold;">
                            Checked By (Security Gate)
                        </span>
                    </div>
                    <div class="col text-center">
                        <span style="border-top: 1px solid #000; display: inline-block; padding-top: 5px; width: 60%; font-weight: bold;">
                            Received By (Receiver's Signature)
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function () {
            window.print();
        });
    </script>
@endsection
