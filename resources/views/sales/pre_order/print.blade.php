@extends('layouts.app')
@section('title', 'Pre-Order Invoice')
@section('style')
    <style>
        .table-bordered td, .table-bordered th { border: 1px solid #000000 !important; padding: 2px; }
        .table-bordered td, .table-bordered th { font-size: 18px !important; }
        @media print {
            @page { size: auto; margin: .5in !important; }
            .signature-area { position: fixed; bottom: 10px; left: 0; width: 100%; }
            .btn-print { display: none; }
            .main-footer { display: none !important; }
            .content-header { display: none !important; }
            .card-header { display: none !important; }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <button type="button" onclick="window.print()" class="btn btn-info btn-sm btn-print">Print <i class="fa fa-print"></i></button>
                </div>
                <div class="card-body">
                    @php
                        $copies = ['Customer Copy', 'Office Copy'];
                    @endphp
                    @foreach($copies as $copy)
                        <div id="printArea" class="{{ !$loop->last ? 'mb-5 pb-5' : '' }}" style="{{ !$loop->last ? 'border-bottom: 1px dashed #000; page-break-after: always;' : '' }}">
                            <div class="text-right"><strong>{{ $copy }}</strong></div>
                            @include('layouts.partial.__report_header')
                            <div class="row header-common-info pt-3 pb-3">
                                <div class="col-4"><h5>Customer: {{ $pre_order->customer->name ?? '' }}</h5></div>
                                <div class="col-4 text-center"><h5>INVOICE NO. {{ $pre_order->order_no }}</h5></div>
                                <div class="col-4 text-right"><h5>DATE: {{ \Carbon\Carbon::parse($pre_order->date)->format('d-m-Y') }}</h5></div>
                            </div>
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">S/L</th>
                                        <th>Product</th>
                                        <th class="text-center">Barcode</th>
                                        <th class="text-center">Unit</th>
                                        @if($pre_order->is_delivered == 1)
                                            <th class="text-center">Batches</th>
                                        @endif
                                        <th class="text-center">Quantity</th>
                                        <th class="text-center">Unit Price</th>
                                        <th class="text-center">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($groupedItems as $item)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td>{{ $item->product->name ?? '' }}</td>
                                            <td class="text-center">{{ $item->product_code }}</td>
                                            <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                            @if($pre_order->is_delivered == 1)
                                                <td class="text-center"><small>{{ $item->batches }}</small></td>
                                            @endif
                                            <td class="text-center">{{ number_format($item->total_quantity) }}</td>
                                            <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                                            <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <th colspan="{{ $pre_order->is_delivered == 1 ? 5 : 4 }}" class="text-right">Sub Total</th>
                                        <th class="text-center">{{ number_format($groupedItems->sum('total_quantity')) }}</th>
                                        <th colspan="2" class="text-right">{{ number_format($pre_order->sub_total, 2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="{{ $pre_order->is_delivered == 1 ? 7 : 6 }}" class="text-right">
                                            @if(($pre_order->discount_type ?? 2) == 1)
                                                Discount (Flat)
                                            @else
                                                Discount ({{ number_format($pre_order->discount_percent, 2) }}%)
                                            @endif
                                        </th>
                                        <th class="text-right">{{ number_format($pre_order->discount, 2) }}</th>
                                    </tr>
                                    <tr><th colspan="{{ $pre_order->is_delivered == 1 ? 7 : 6 }}" class="text-right">Total</th><th class="text-right">{{ number_format($pre_order->total, 2) }}</th></tr>
                                    <tr><th colspan="{{ $pre_order->is_delivered == 1 ? 7 : 6 }}" class="text-right">{{ $pre_order->is_delivered == 1 ? 'Paid' : 'Advance' }}</th><th class="text-right">{{ number_format($pre_order->paid, 2) }}</th></tr>
                                    <tr><th colspan="{{ $pre_order->is_delivered == 1 ? 7 : 6 }}" class="text-right">Due</th><th class="text-right">{{ number_format($pre_order->due, 2) }}</th></tr>
                                </tbody>
                            </table>
                            <div class="row pt-5 signature-area">
                                <div class="col text-center"><strong>{{ $pre_order->user->name ?? '' }}</strong><br>Created By</div>
                                <div class="col text-center"><strong>_________________</strong><br>Authorized Signature</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        $(function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        });
    </script>
@endsection
