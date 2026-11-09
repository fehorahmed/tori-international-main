@extends('layouts.app')
@section('title', 'Sales Invoice')
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
                    <div id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info pt-3 pb-3">
                            <div class="col-4"><h5>Customer: {{ $sale->customer->name ?? '' }}</h5></div>
                            <div class="col-4 text-center"><h5>INVOICE NO. {{ $sale->order_no }}</h5></div>
                            <div class="col-4 text-right"><h5>DATE: {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</h5></div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">S/L</th>
                                    <th>Product</th>
                                    <th class="text-center">Barcode</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Batches</th>
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
                                        <td class="text-center"><small>{{ $item->batches }}</small></td>
                                        <td class="text-center">{{ number_format($item->total_quantity) }}</td>
                                        <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th colspan="5" class="text-right">Sub Total</th>
                                    <th class="text-center">{{ number_format($groupedItems->sum('total_quantity')) }}</th>
                                    <th colspan="2" class="text-right">{{ number_format($sale->sub_total, 2) }}</th>
                                </tr>
                                <tr><th colspan="7" class="text-right">Discount</th><th class="text-right">{{ number_format($sale->discount, 2) }}</th></tr>
                                <tr><th colspan="7" class="text-right">Total</th><th class="text-right">{{ number_format($sale->total, 2) }}</th></tr>
                                <tr><th colspan="7" class="text-right">Paid</th><th class="text-right">{{ number_format($sale->paid, 2) }}</th></tr>
                                <tr><th colspan="7" class="text-right">Due</th><th class="text-right">{{ number_format($sale->due, 2) }}</th></tr>
                            </tbody>
                        </table>
                        @if($sale->notes)
                            <div class="row pt-2 pb-2">
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
    <script>
        $(function() {
            window.print();
            window.onafterprint = function() {
                window.close();
            };
        });
    </script>
@endsection
