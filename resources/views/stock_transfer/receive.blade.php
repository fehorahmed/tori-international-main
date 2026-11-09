@extends('layouts.app')
@section('title','Stock Transfer — Receive')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            padding: 4px 6px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000 !important;
        }
        .received-input {
            width: 90px;
            text-align: center;
            border: 1px solid #28a745;
            border-radius: 4px;
            padding: 2px 4px;
        }
        .return-badge {
            background: #ffc107;
            color: #000;
            border-radius: 4px;
            padding: 2px 8px;
            font-weight: bold;
            min-width: 60px;
            display: inline-block;
            text-align: center;
        }
        .table-bordered td, .table-bordered th { font-size: 14px !important; }
        @media print {
            .no-print { display: none !important; }
            .table-bordered td, .table-bordered th { font-size: 13px !important; }
            .signature-area { position: fixed; bottom: 0; left: 0; width: 100%; }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header no-print">
                    <a href="#" onclick="getprintChallan('printAreaChallan')" class="btn btn-info bg-gradient-info btn-sm">
                        <i class="fa fa-print"></i> Print
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        @include('layouts.partial.__report_header')

                        {{-- Transfer Info --}}
                        <div class="row pt-3 pb-2">
                            <div class="col-3">
                                <h5 class="m-0">From: <strong>{{ $stockTransfer->fromSisterConcern->name ?? ($stockTransfer->fromWarehouse->name ?? '') }}</strong></h5>
                            </div>
                            <div class="col-3">
                                <h5 class="m-0">To: <strong>{{ $stockTransfer->toSisterConcern->name ?? ($stockTransfer->toWarehouse->name ?? '') }}</strong></h5>
                            </div>
                            <div class="col-3">
                                <h5 class="m-0">Order No: <strong>{{ $stockTransfer->order_no }}</strong></h5>
                                @if($stockTransfer->challan_no)
                                    <small>Challan: {{ $stockTransfer->challan_no }}</small>
                                @endif
                            </div>
                            <div class="col-3">
                                <h5 class="m-0">Date: <strong>{{ \Carbon\Carbon::parse($stockTransfer->date)->format('d-m-Y') }}</strong></h5>
                                @if($stockTransfer->vehicle_no)
                                    <small>Vehicle: {{ $stockTransfer->vehicle_no }}
                                        @if($stockTransfer->driver_name) | Driver: {{ $stockTransfer->driver_name }} @endif
                                    </small>
                                @endif
                            </div>
                        </div>

                        {{-- Info Banner --}}
                            <div class="alert alert-info py-2 no-print">
                                <i class="fa fa-info-circle"></i>
                                <strong>Receive Mode:</strong> You must receive the exact quantity that was dispatched. If there is a discrepancy, please click <strong>Reject Transfer</strong>.
                            </div>
                        <form action="{{ route('stock-transfers.receive_store', ['stock_transfer' => $stockTransfer->id]) }}" method="post" id="receiveForm" style="display:inline;">
                            @csrf
                        </form>
                        <form action="{{ route('stock-transfers.reject', ['stock_transfer' => $stockTransfer->id]) }}" method="post" id="rejectForm" style="display:inline;">
                            @csrf
                        </form>

                            <table class="table table-bordered mt-3">
                                <thead>
                                <tr>
                                    <th colspan="12" class="text-left no-print">
                                        @if($stockTransfer->stockTransferItems->where('status', 'pending')->count() > 0)
                                            <button type="button" id="receive-btn" class="btn btn-success bg-gradient-success mr-2">
                                                <i class="fa fa-check-circle"></i> Receive All Items
                                            </button>
                                            <button type="button" id="reject-btn" class="btn btn-danger bg-gradient-danger">
                                                <i class="fa fa-times-circle"></i> Reject Transfer
                                            </button>
                                        @else
                                            <span class="text-success font-weight-bold"><i class="fa fa-check-circle"></i> All items have been received</span>
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-right">Cost Price</th>
                                    <th class="text-right">Transfer Price</th>
                                    <th class="text-right">Transfer Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $totalTransfer = 0; @endphp
                                @foreach($stockTransfer->stockTransferItems as $item)
                                    @if($item->status == 'pending')
                                        <input type="hidden" name="stock_transfer_item[]" value="{{ $item->id }}">
                                    @endif
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product_code }}</td>
                                        <td class="text-center"><strong>{{ number_format($item->quantity, 0) }} {{ $item->product->unit->name ?? '' }}</strong></td>

                                        @if($item->status == 'pending')
                                            <input type="hidden" form="receiveForm" name="stock_transfer_item[]" value="{{ $item->id }}">
                                        @endif
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->transfer_price ?? $item->unit_price, 2) }}</td>
                                        <td class="text-right item-total">
                                            @php 
                                                $displayQty = $item->quantity;
                                            @endphp
                                            {{ number_format($displayQty * ($item->transfer_price ?? $item->unit_price), 2) }}
                                        </td>
                                    </tr>
                                    @php 
                                        $calcQty = $item->status == 'pending' ? $item->quantity : $item->received_quantity;
                                        $totalTransfer += $calcQty * ($item->transfer_price ?? $item->unit_price); 
                                    @endphp
                                @endforeach
                                    <th class="text-right" colspan="3">Total</th>
                                    <th class="text-center">
                                        {{ number_format($stockTransfer->stockTransferItems->sum('quantity')) }}
                                    </th>
                                    <th colspan="2"></th>
                                    <th class="text-right">{{ number_format($totalTransfer, 2) }}</th>
                                </tr>
                                </tbody>
                            </table>

                        {{-- Signature Area --}}
                        <div class="row signature-area mt-5">
                            <div class="col text-center">
                                <span style="display:block;padding:18px;font-size:16px;font-weight:bold;border-top:1px solid #000">
                                    {{ $stockTransfer->user->name ?? '' }}<br>Created By
                                </span>
                            </div>
                            <div class="col text-center">
                                <span style="display:block;padding:18px;font-size:16px;font-weight:bold;border-top:1px solid #000">
                                    <br>Received By
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
$(function () {
    // Confirm receive button
    $('#receive-btn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Confirm Receive?',
            html: 'Are you sure you want to fully receive all these items?',
            icon: 'success',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Confirm Receive!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#receiveForm').submit();
            }
        });
    });

    // Confirm reject button
    $('#reject-btn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Reject Transfer?',
            html: 'Are you sure you want to reject this entire transfer? The stock will be returned to the sender.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject Transfer!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#rejectForm').submit();
            }
        });
    });

    // Print function
    var APP_URL = '{!! url()->full() !!}';
    window.getprintChallan = function(printId) {
        document.title = "{{ $stockTransfer->order_no }}_receive";
        $('body').html($('#' + printId).html());
        window.print();
        window.location.replace(APP_URL);
    };
});
</script>
@endsection
