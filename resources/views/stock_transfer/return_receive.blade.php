@extends('layouts.app')
@section('title','Receive Return Transfer')
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
                    <a href="{{ route('stock-transfers.pending_returns') }}" class="btn btn-default btn-sm">
                        <i class="fa fa-arrow-left"></i> Back to Pending Returns
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="printAreaReturn">
                        @include('layouts.partial.__report_header')

                        {{-- Transfer Info --}}
                        <div class="row pt-3 pb-2">
                            <div class="col-3">
                                <h5 class="m-0">From (Receiver returning): <strong>{{ $stock_transfer->fromSisterConcern->name ?? ($stock_transfer->fromWarehouse->name ?? '') }}</strong></h5>
                            </div>
                            <div class="col-3">
                                <h5 class="m-0">To Branch (You): <strong>{{ $stock_transfer->toSisterConcern->name ?? ($stock_transfer->toWarehouse->name ?? '') }}</strong></h5>
                            </div>
                            <div class="col-3">
                                <h5 class="m-0">Return Order: <strong>{{ $stock_transfer->order_no }}</strong></h5>
                                @if($stock_transfer->parentTransfer)
                                    <small>Ref Original Order: <strong>{{ $stock_transfer->parentTransfer->order_no }}</strong></small>
                                @endif
                            </div>
                            <div class="col-3">
                                <h5 class="m-0">Date: <strong>{{ \Carbon\Carbon::parse($stock_transfer->date)->format('d-m-Y') }}</strong></h5>
                            </div>
                        </div>

                        <div class="alert alert-warning py-2 no-print mt-2">
                            <i class="fa fa-exclamation-triangle"></i>
                            <strong>Manual Return Receive:</strong> Please count the physical quantity returned by the branch. Confirming this will adjust your Main Warehouse stock.
                        </div>

                        <form action="{{ route('stock-transfers.return_receive_store', ['stock_transfer' => $stock_transfer->id]) }}" method="post" id="returnReceiveForm">
                            @csrf
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th colspan="9" class="text-left no-print">
                                        @if($stock_transfer->stockTransferItems->where('status', 'pending')->count() > 0)
                                            <div class="d-flex align-items-center">
                                                <button type="button" id="confirm-return-btn" class="btn btn-warning bg-gradient-warning text-dark font-weight-bold mr-2">
                                                    <i class="fa fa-undo"></i> Confirm Return Receipt
                                                </button>
                                                <button type="button" id="reject-return-btn" class="btn btn-danger bg-gradient-danger font-weight-bold">
                                                    <i class="fa fa-times-circle"></i> Reject Return
                                                </button>
                                            </div>
                                        @else
                                            <span class="text-success font-weight-bold"><i class="fa fa-check-circle"></i> All return items have been received</span>
                                        @endif
                                    </th>
                                </tr>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th>Product</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Returning Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Transfer Price</th>
                                    <th class="text-right">Total (Transfer Price)</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php $totalReturn = 0; @endphp
                                @foreach($stock_transfer->stockTransferItems as $item)
                                    @if($item->status == 'pending')
                                        <input type="hidden" name="stock_transfer_item[]" value="{{ $item->id }}">
                                    @endif
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product_code }}</td>
                                        <td class="text-center"><strong>{{ number_format($item->quantity, 0) }} {{ $item->product->unit->name ?? '' }}</strong></td>
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->transfer_price ?? $item->unit_price, 2) }}</td>
                                        <td class="text-right">
                                            {{ number_format($item->quantity * ($item->transfer_price ?? $item->unit_price), 2) }}
                                        </td>
                                    </tr>
                                    @php $totalReturn += $item->quantity * ($item->transfer_price ?? $item->unit_price); @endphp
                                @endforeach
                                <tr>
                                    <th class="text-right" colspan="3">Total</th>
                                    <th class="text-center">
                                        {{ number_format($stock_transfer->stockTransferItems->sum('quantity')) }}
                                    </th>
                                    <th colspan="2"></th>
                                    <th class="text-right">{{ number_format($totalReturn, 2) }}</th>
                                </tr>
                                </tbody>
                            </table>
                        </form>
                        
                        <form action="{{ route('stock-transfers.reject', ['stock_transfer' => $stock_transfer->id]) }}" method="post" id="rejectForm" style="display: none;">
                            @csrf
                        </form>

                        {{-- Signature Area --}}
                        <div class="row signature-area mt-5">
                            <div class="col text-center">
                                <span style="display:block;padding:18px;font-size:16px;font-weight:bold;border-top:1px solid #000">
                                    Returned By (Receiver)
                                </span>
                            </div>
                            <div class="col text-center">
                                <span style="display:block;padding:18px;font-size:16px;font-weight:bold;border-top:1px solid #000">
                                    Received By (You)
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
    $('#confirm-return-btn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Confirm Return Receipt?',
            text: 'Are you sure you have physically received these returned items? Stock will be added back to your Main Warehouse.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ffc107',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, Receive Return!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#returnReceiveForm').submit();
            }
        });
    });

    $('#reject-return-btn').on('click', function (e) {
        e.preventDefault();
        Swal.fire({
            title: 'Reject Return Transfer?',
            text: 'Are you sure you want to reject this return? The items will be sent back to the sender\'s warehouse.',
            icon: 'error',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Reject Return!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#rejectForm').submit();
            }
        });
    });
});
</script>
@endsection
