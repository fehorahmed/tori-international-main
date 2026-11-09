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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printAreaChallan', 'fileName' => 'Sales_Details_' . $sale->order_no, 'no_spacing' => true])
                    
                    <!-- <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-info bg-gradient-info btn-sm ml-2">XL Print <i class="fa fa-print"></i></a>
                    @if($sale->type == \App\Enumeration\SaleType::$GENERAL)
                        <a href="{{ route('sales.print', ['sale' => $sale->id]) }}" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm ml-2">Customer Copy <i class="fa fa-print"></i></a>
                    @elseif($sale->type == \App\Enumeration\SaleType::$PRE_ORDER)
                        <a href="{{ route('advance-sales.print', ['advance_sale' => $sale->id]) }}" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm ml-2">Customer Copy <i class="fa fa-print"></i></a>
                    @elseif($sale->type == \App\Enumeration\SaleType::$EMI)
                        <a href="{{ route('emi-sales.print', ['emi_sale' => $sale->id]) }}" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm ml-2">Customer Copy <i class="fa fa-print"></i></a>
                    @endif -->
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        @include('layouts.partial.__report_header')
                        <div class="row m-0 header-common-info pt-3 pb-3" >
                            <div class="col-6">
                                <h5 class="text-left m-0">Customer: {{ $sale->customer->member_no ?? '' }} - {{ $sale->customer->name ?? '' }}</h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">ORDER NO. {{ $sale->order_no }}</h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-right m-0">DATE: {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered" style="width: 100%;">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
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
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product_code }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->selling_price,2) }}</td>
                                    <td class="text-center">
                                        @if($item->product->has_warranty)
                                            <span class="badge {{ $item->hasActiveWarranty() ? 'badge-success' : 'badge-danger' }}">
                                                {{ $item->product->warranty_duration }} {{ $item->product->warranty_type }}(s) (Exp: {{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('d-m-Y') : 'N/A' }})
                                            </span>
                                        @else
                                            No Warranty
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->selling_price,2) }}</td>
                                </tr>
                                @php
                                    $totalSalesPrice += $item->quantity * $item->selling_price;
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="4">Sub Total</th>
                                    <th class="text-center">{{ number_format($sale->saleOrderItems->sum('quantity')) }}</th>
                                    <th class="text-right" colspan="3">{{ number_format($totalSalesPrice,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="6">
                                        @if($sale->is_pos == 1)
                                            Discount
                                        @elseif(($sale->discount_type ?? 2) == 1)
                                            Discount (Flat)
                                        @else
                                            Discount ({{ number_format($sale->discount_percent, 2) }}%)
                                        @endif
                                    </th>
                                    <th class="text-right" colspan="2">{{ number_format($sale->discount,2) }}</th>
                                </tr>
                                @if ($sale->advance_adjust > 0)
                                <tr>
                                    <th class="text-right" colspan="6">Advance Adjust</th>
                                    <th class="text-right" colspan="2">{{ number_format($sale->advance_adjust,2) }}</th>
                                </tr>
                                @endif
                                <tr>
                                    <th class="text-right" colspan="6">Paid</th>
                                    <th class="text-right" colspan="2">{{ number_format($sale->paid-$sale->advance_adjust,2) }}</th>
                                </tr>
                                <tr>
                                    <th class="text-right" colspan="6">Due</th>
                                    <th class="text-right" colspan="2">{{ number_format($sale->due,2) }}</th>
                                </tr>
                            </tbody>
                        </table>       
                        @if($sale->notes)
                            <div class="row m-0 pt-2 pb-2 header-common-info">
                                <div class="col-12">
                                    <p class="mb-0" style="font-size: 18px;"><strong>Note:</strong> {{ $sale->notes }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <div class="row m-0 header-common-info signature-area" >
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
    @include('layouts.partial.__export_scripts')
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
