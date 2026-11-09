@extends('layouts.app')
@section('title', 'Pre-Order Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th { border: 1px solid #000000 !important; padding: 2px; }
        .table thead th { vertical-align: bottom; border-bottom: 2px solid #000000 !important; }
        .table-bordered td, .table-bordered th { font-size: 18px !important; }
        @media print {
            .table-bordered td, .table-bordered th { font-size: 18px !important; }
            @page { size: auto; margin: .5in !important; }
            .signature-area { position: fixed; bottom: 0; left: 0; width: 100%; }
            .btn-print { display: none; }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header d-none">
                    <button type="button" onclick="window.print()" class="btn btn-info btn-sm btn-print">Print <i class="fa fa-print"></i></button>
                    <a href="{{ route('pre-orders.print', ['pre_order' => $sale->id]) }}" target="_blank" class="btn btn-primary btn-sm btn-print">Customer Copy <i class="fa fa-print"></i></a>
                </div>
                <div class="card-header border-bottom-0">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Sales_Invoice_' . date('d_m_Y')])
                </div>
                <div class="card-body">
                    <div id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info pt-3 pb-3">
                            <div class="col-6"><h5>Customer: {{ $sale->customer->member_no ?? '' }} - {{ $sale->customer->name ?? '' }}</h5></div>
                            <div class="col-3 text-center"><h5>ORDER NO. {{ $sale->order_no }}</h5></div>
                            <div class="col-3 text-right"><h5>DATE: {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</h5></div>
                        </div>
                        @if($sale->is_delivered == 1)
                            <div class="row header-common-info pb-3">
                                <div class="col-6"><h5>Warehouse: {{ $sale->warehouse->name ?? '' }}</h5></div>
                                <div class="col-6 text-right"><h5>Delivery Date: {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</h5></div>
                            </div>
                        @endif
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">S/L</th>
                                    <th>Product</th>
                                    <th class="text-center">Barcode</th>
                                    <th class="text-center">Unit</th>
                                    @if($sale->is_delivered == 1)
                                        <th class="text-center">Batches</th>
                                    @endif
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">Unit Price</th>
                                    <th class="text-center">Total Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleOrderItems as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product_code }}</td>
                                        <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                        @if($sale->is_delivered == 1)
                                            <td class="text-center"><small>{{ $item->inventory->batch_no ?? '' }}</small></td>
                                        @endif
                                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                                        <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th colspan="{{ $sale->is_delivered == 1 ? 5 : 4 }}" class="text-right">Sub Total</th>
                                    <th class="text-center">{{ number_format($sale->saleOrderItems->sum('quantity')) }}</th>
                                    <th colspan="2" class="text-right">{{ number_format($sale->sub_total, 2) }}</th>
                                </tr>
                                <tr>
                                    <th colspan="{{ $sale->is_delivered == 1 ? 7 : 6 }}" class="text-right">
                                        @if(($sale->discount_type ?? 2) == 1)
                                            Discount (Flat)
                                        @else
                                            Discount ({{ number_format($sale->discount_percent, 2) }}%)
                                        @endif
                                    </th>
                                    <th class="text-right">{{ number_format($sale->discount, 2) }}</th>
                                </tr>
                                <tr><th colspan="{{ $sale->is_delivered == 1 ? 7 : 6 }}" class="text-right">Total</th><th class="text-right">{{ number_format($sale->total, 2) }}</th></tr>
                                <tr><th colspan="{{ $sale->is_delivered == 1 ? 7 : 6 }}" class="text-right">{{ $sale->is_delivered == 1 ? 'Paid' : 'Order Advance' }}</th><th class="text-right">{{ number_format($sale->paid, 2) }}</th></tr>
                                <!-- <tr><th colspan="{{ $sale->is_delivered == 1 ? 7 : 6 }}" class="text-right">Due</th><th class="text-right">{{ number_format($sale->due, 2) }}</th></tr> -->
                            </tbody>
                        </table>
                        
                        @if($sale->notes)
                            <div class="row pt-2 pb-2 header-common-info">
                                <div class="col-12">
                                    <p class="mb-0"><strong>Note:</strong> {{ $sale->notes }}</p>
                                </div>
                            </div>
                        @endif
                        <div class="row pt-5 signature-area">
                            <div class="col text-center"><strong>{{ $sale->user->name ?? '' }}</strong><br>Created By</div>
                            <div class="col text-center"><strong>_________________</strong><br>Authorized Signature</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('script')
    @include('layouts.partial.__export_scripts')
    <script>
        function getprintChallan(print) {
            document.title = "{{ $sale->order_no }}_dealer_details";
            let printContents = document.getElementById(print).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            location.reload();
        }
    </script>
@endsection