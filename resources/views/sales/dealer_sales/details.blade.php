@extends('layouts.app')
@section('title','Dealer Sales Order Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th { border: 1px solid #000000 !important; padding: 2px; }
        .table-bordered td, .table-bordered th { font-size: 18px !important; }
        @media print {
            .table-bordered td, .table-bordered th { font-size: 18px !important; }
            @page { size: auto; margin: .5in !important; }
            .signature-area { position: fixed; bottom: 0; left: 0; width: 100%; }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header d-none">
                    <a href="#" role="button" onclick="getprintChallan('printArea')" class="btn btn-info btn-sm">Print <i class="fa fa-print"></i></a>
                    <a href="{{ route('dealer-sales.print', ['dealer_sale' => $sale->id]) }}" target="_blank" class="btn btn-success btn-sm">Customer Copy <i class="fa fa-print"></i></a>
                </div>
                <div class="card-header border-bottom-0">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Sales_Invoice_' . date('d_m_Y')])
                </div>
                <div class="card-body">
                    <div id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info pt-3 pb-3">
                            <div class="col-6"><h5>Dealer: {{ $sale->customer->member_no ?? '' }} - {{ $sale->customer->name ?? '' }}</h5></div>
                            <div class="col-3 text-center"><h5>ORDER NO. {{ $sale->order_no }}</h5></div>
                            <div class="col-3 text-right"><h5>DATE: {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</h5></div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>S/L</th><th>Product</th><th>Barcode</th><th>Unit</th><th>Quantity</th><th>Unit Price</th><th>Warranty</th><th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->saleOrderItems as $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->product_code }}</td>
                                        <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                        <td class="text-center">{{ number_format($item->quantity) }}</td>
                                        <td class="text-right">{{ number_format($item->selling_price, 2) }}</td>
                                        <td class="text-center">
                                            @if($item->product->has_warranty)
                                                <span class="badge {{ $item->hasActiveWarranty() ? 'badge-success' : 'badge-danger' }}">
                                                    {{ $item->product->warranty_duration }} {{ $item->product->warranty_type }}(s) (Exp: {{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('d-m-Y') : 'N/A' }})
                                                </span>
                                            @else
                                                No Warranty
                                            @endif
                                        </td>
                                        <td class="text-right">{{ number_format($item->quantity * $item->selling_price, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr><th colspan="4" class="text-right">Sub Total</th><th class="text-center">{{ number_format($sale->saleOrderItems->sum('quantity')) }}</th><th colspan="3" class="text-right">{{ number_format($sale->sub_total, 2) }}</th></tr>
                                <tr>
                                    <th colspan="7" class="text-right">
                                        @if(($sale->discount_type ?? 2) == 1)
                                            Discount (Flat)
                                        @else
                                            Discount ({{ number_format($sale->discount_percent, 2) }}%)
                                        @endif
                                    </th>
                                    <th class="text-right">{{ number_format($sale->discount, 2) }}</th>
                                </tr>
                                <tr><th colspan="7" class="text-right">Total</th><th class="text-right">{{ number_format($sale->total, 2) }}</th></tr>
                                <tr><th colspan="7" class="text-right">Paid</th><th class="text-right">{{ number_format($sale->paid, 2) }}</th></tr>
                                <tr><th colspan="7" class="text-right">Due</th><th class="text-right">{{ number_format($sale->due, 2) }}</th></tr>
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
