@extends('layouts.app')
@section('title','Sales Order Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;;
        }
        .customer-area-border {
            border: 1.5px solid #000 !important;;
            padding: 3px;
            min-height: 128px;
            margin-top: 10px;
            margin-bottom: 50px;
        }
        .customer-area-border p {
            font-size: 20px !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
            @page {
                size: auto;
                margin: .5in !important;
            }
            .signature-area{
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-info bg-gradient-info btn-sm">XL Print <i class="fa fa-print"></i></a>
                    <a href="{{ route('sales.sm_details',['sale'=>$sale->id]) }}" target="_blank" class="btn btn-info bg-gradient-info btn-sm">SM Print <i class="fa fa-print"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info pt-3 pb-3" >
                            <div class="col-4">
                                <h5 class="text-left m-0">Customer: {{ $sale->customer->name ?? '' }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">ORDER NO. {{ $sale->order_no }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">DATE: {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Barcode</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Warranty</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalSalesPrice = 0;
                            @endphp
                            @foreach($sale->saleOrderItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-left">{{ $item->product->brand->name ?? '' }}</td>
                                    <td class="text-left">{{ $item->product->productCategory->name ?? '' }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product_code }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->selling_unit_price,2) }}</td>
                                    <td class="text-center">
                                        @if($item->product->has_warranty)
                                            <span class="badge {{ $item->hasActiveWarranty() ? 'badge-success' : 'badge-danger' }}">
                                                {{ $item->product->warranty_duration }} {{ $item->product->warranty_type }}(s) (Exp: {{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('d-m-Y') : 'N/A' }})
                                            </span>
                                        @else
                                            No Warranty
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->selling_unit_price,2) }}</td>
                                </tr>
                                @php
                                    $totalSalesPrice += $item->quantity * $item->selling_unit_price;
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="6">Sub Total</th>
                                    <th class="text-center">{{ number_format($sale->saleOrderItems->sum('quantity')) }}</th>
                                    <th class="text-right" colspan="3">{{ number_format($totalSalesPrice,2) }}</th>
                                </tr>
                                @if ($sale->is_return == 0)
                                    <tr>
                                        <th class="text-right" colspan="8">Discount</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->discount,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="8">Paid</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->paid,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="8">Due</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->due,2) }}</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th class="text-right" colspan="8">Discount</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->discount,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="8">Total</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->total,2) }}</th>
                                    </tr>    
                                @endif
                            </tbody>
                        </table>
                        @if ($sale->is_return == 1)
                            <div class="row header-common-info pt-3 pb-3" >
                                <div class="col-8">
                                    <h5 class="text-left m-0">Return Items#</h5>
                                </div>
                                <div class="col-4">
                                    <h5 class="text-center m-0">RETURN DATE: {{ \Carbon\Carbon::parse($sale->return_date)->format('d-m-Y') }}</h5>
                                </div>
                            </div>
                            <table id="table" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th class="text-center">S/L</th>
                                    <th class="text-center">Brand</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Product</th>
                                    <th class="text-center">Barcode</th>
                                    <th class="text-center">Unit</th>
                                    <th class="text-center">Return Qty</th>
                                    <th class="text-center">Unit Price</th>
                                    <th class="text-center">Total Price</th>
                                </tr>
                                </thead>
                                <tbody>
                                @php
                                    $totalReturnPrice = 0;
                                @endphp
                                @foreach($sale->saleOrderItems as $item)
                                    @if ($item->return_quantity > 0)
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-left">{{ $item->product->brand->name ?? '' }}</td>
                                            <td class="text-left">{{ $item->product->productCategory->name ?? '' }}</td>
                                            <td>{{ $item->product->name ?? '' }}</td>
                                            <td class="text-center">{{ $item->product_code }}</td>
                                            <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                            <td class="text-center">{{ number_format($item->return_quantity) }}</td>
                                            <td class="text-right">{{ number_format($item->selling_unit_price,2) }}</td>
                                            <td class="text-right">{{ number_format($item->return_quantity * $item->selling_unit_price,2) }}</td>
                                        </tr>
                                        @php
                                            $totalReturnPrice += $item->return_quantity * $item->selling_unit_price;
                                        @endphp
                                    @endif
                                @endforeach
                                    <tr>
                                        <th class="text-right" colspan="6">Sub Total</th>
                                        <th class="text-center">{{ number_format($sale->saleOrderItems->sum('return_quantity')) }}</th>
                                        <th class="text-right" colspan="2">{{ number_format($totalReturnPrice,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="7">Discount</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->return_discount,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="7">Total</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->return_total,2) }}</th>
                                    </tr>
                                </tbody>
                            </table>
                            <table id="table" class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <th class="text-right" colspan="7">Invoice Total:</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->total,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="7">Return Total:</th>
                                        <th style="border-bottom: 2px solid #000 !important;" class="text-right" colspan="2">{{ number_format($sale->return_total,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="7">=</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->total-$sale->return_total,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="7">Invoice Paid</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->paid,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th class="text-right" colspan="7">Refund</th>
                                        <th class="text-right" colspan="2">{{ number_format($sale->refund,2) }}</th>
                                    </tr>
                                    @if ($sale->due < 0)
                                        <tr>
                                            <th class="text-right" colspan="7">Refund Due</th>
                                            <th class="text-right" colspan="2">{{ number_format(abs($sale->due),2) }}</th>
                                        </tr>
                                    @else
                                        <tr>
                                            <th class="text-right" colspan="7">Due</th>
                                            <th class="text-right" colspan="2">{{ number_format($sale->due,2) }}</th>
                                        </tr>
                                    @endif
            
                                </tbody>
                            </table>
                        @endif
                        
                        @if($sale->notes)
                            <div class="row pt-2 pb-2 header-common-info">
                                <div class="col-12">
                                    <p class="mb-0" style="font-size: 18px;"><strong>Note:</strong> {{ $sale->notes }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <div class="row header-common-info signature-area" >
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        {{ $sale->user->name ?? '' }}<br>
                                         Created By

                                    </span>
                            </div>
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        <br>
                                        Checked By
                                    </span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';
        function getprintChallan(print) {
            document.title = "{{ $sale->order_no }}_sales_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
