<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    <title>{{ $sale->order_no }}</title>
    <style>
        table.table-bordered {
            border: 1px solid black !important;
        }
        table.table-bordered th {
            border: 1px solid black !important;
        }

        table.table-bordered td {
            border: 1px solid black !important;
        }

        .product-table th, .table-summary th {
            padding: 2px !important;
            text-align: center !important;
        }

        .product-table td, .table-summary td {
            padding: 2px !important;
            text-align: center !important;
        }
        .row {
            margin-right: 0;
            margin-left: 0;
        }
        @page {
            size: 3in;
            margin: 0;
        }

        .table-bordered > tbody > tr > td, .table-bordered > tbody > tr > th, .table-bordered > tfoot > tr > td, .table-bordered > tfoot > tr > th, .table-bordered > thead > tr > td, .table-bordered > thead > tr > th {
            border: 1px solid #ddd;
            font-size: 12px;
            padding: 1px;
            text-align: center;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="row">
        <div class="col-12">
            <h5 style="margin: 0;text-align: center">{{ config('app.name') }}</h5>
            <p style="margin: 0;font-size: 11px;text-align: center">
                {{ auth()->user()->sisterConcern->address ?? config('app.address') }}<br>
                {{ auth()->user()->sisterConcern->contact ?? config('app.contact') }}<br>
                {{ auth()->user()->sisterConcern->email ?? config('app.email') }}<br>
            </p>
        </div>

    </div>
    <div class="row" style="padding-top: 10px;width: 100%;clear: both;">
        <div style="border: 1px solid #000;height: 27px;padding: 2px">
            @if($sale->customer_id)
                <div class="col-12" style="width: 50%;float: left">
                    <p style="font-size: 11px;margin: 0"><strong>Customer: </strong>{{ $sale->customer->name }}</p>
                    <p style="font-size: 11px;margin: 0"><strong>Mobile No. </strong>{{ $sale->customer->mobile_no }}</p>
                </div>
            @endif
            <div class="col-12" style="width: 50%;float: right">
                <p style="margin: 0;font-size: 11px;text-align: right"><b>Invoice No:</b> {{ $sale->order_no }}</p>
                <p style="margin: 0;font-size: 11px;text-align: right"><b>Date:</b> {{ \Carbon\Carbon::parse($sale->date)->format('d-m-Y') }}</p>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center" style="font-size: 16px;text-align: center">
            <p style="margin-bottom: 0"><b>Invoice</b></p>
        </div>
    </div>
    <div class="row">
        <div class="col-12" style="overflow: hidden !important;">
            @if(count($sale->saleOrderItems) > 0)
                <table class="table table-bordered"  style="width: 100% !important;border-collapse: collapse;">
                    <tr>
                        <th width="2%">#</th>
                        <th>Name</th>
                        <th>Barcode</th>
                        <th width="5%">Qty.</th>
                        <th width="8%">Price</th>
                        <th width="8%">Amount</th>
                    </tr>
                    @php
                        $totalSalesPrice = 0;
                    @endphp
                    @foreach($sale->saleOrderItems as $saleOrderItem)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td  style="text-align: left">
                                {{ $saleOrderItem->product->name ?? '' }}
                                @if($saleOrderItem->product->has_warranty)
                                    <br><small>Warranty: {{ $saleOrderItem->product->warranty_duration }} {{ $saleOrderItem->product->warranty_type }}(s) (Exp: {{ $saleOrderItem->warranty_expiry_date ? $saleOrderItem->warranty_expiry_date->format('d-m-Y') : 'N/A' }})</small>
                                @endif
                            </td>
                            <td  style="text-align: center">{{ $saleOrderItem->product_code }}</td>
                            <td>{{  round($saleOrderItem->quantity) }}</td>
                            <td style="text-align: right">{{ number_format($saleOrderItem->selling_price,2) }}</td>
                            <td style="text-align: right">{{ number_format($saleOrderItem->selling_price * $saleOrderItem->quantity,2) }}</td>
                        </tr>
                        @php
                            $totalSalesPrice += $saleOrderItem->quantity * $saleOrderItem->selling_price;
                        @endphp
                    @endforeach
                    <tr>
                        <th colspan="3" style="text-align: right !important;">Sub Total</th>
                        <th colspan="" style="text-align: center !important;" class="text-center">
                            {{ ($sale->saleOrderItems->sum('quantity')) }}
                        </th>
                        <th colspan="2" style="text-align: right !important;">{{ number_format($totalSalesPrice,2) }}</th>
                    </tr>
                    @if ($sale->is_return == 0)
                        @if($sale->discount > 0)
                            <tr>
                                <th class="text-right" style="text-align: right !important;" colspan="4">
                                    @if($sale->is_pos == 1)
                                        Discount
                                    @elseif(($sale->discount_type ?? 2) == 1)
                                        Discount (Flat)
                                    @else
                                        Discount ({{ number_format($sale->discount_percent, 2) }}%)
                                    @endif
                                </th>
                                <th colspan="2" style="text-align: right !important;" class="text-right">
                                    {{ number_format($sale->discount,2) }}
                                </th>
                            </tr>
                        @endif
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="4">Grand Total</th>
                            <th colspan="2" style="text-align: right !important;"
                                class="text-right">{{ number_format($sale->total,2) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="4">Paid</th>
                            <th colspan="2" style="text-align: right !important;"
                                class="text-right">{{ number_format($sale->paid,2) }}</th>
                        </tr>
                        @if($sale->due > 0)
                            <tr>
                                <th class="text-right" style="text-align: right !important;;font-size: 20px" colspan="4">Due</th>
                                <th colspan="2" style="text-align: right !important;font-size: 20px"
                                    class="text-right" >{{ number_format($sale->due,2) }}</th>
                            </tr>
                        @endif
                    @else
                        <tr>
                            <th colspan="4" style="text-align: right !important;">
                                @if($sale->is_pos == 1)
                                    Discount
                                @elseif(($sale->discount_type ?? 2) == 1)
                                    Discount (Flat)
                                @else
                                    Discount ({{ number_format($sale->discount_percent, 2) }}%)
                                @endif
                            </th>
                            <th colspan="2" style="text-align: right !important;">{{ number_format($sale->discount,2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align: right !important;">Total</th>
                            <th colspan="2" style="text-align: right !important;">{{ number_format($sale->total,2) }}</th>
                        </tr>
                    @endif
                    
                </table>
            @endif
            @if ($sale->is_return == 1)
                <div class="col-12 text-center" style="font-size: 16px;text-align: center">
                    <p style="margin-bottom: 0"><b>Return Items:</b></p>
                </div>
                @if(count($sale->saleOrderItems) > 0)
                    @php
                        $totalReturnPrice = 0;
                    @endphp
                    <table class="table table-bordered"  style="width: 100% !important;border-collapse: collapse;">
                        <tr>
                            <th width="2%">#</th>
                            <th>Name</th>
                            <th>Barcode</th>
                            <th width="5%">Return Qty.</th>
                            <th width="8%">Price</th>
                            <th width="8%">Amount</th>
                        </tr>
                        @foreach($sale->saleOrderItems as $saleOrderItem)
                            @if ($saleOrderItem->return_quantity > 0)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td  style="text-align: left">{{ $saleOrderItem->product->name ?? '' }}</td>
                                    <td  style="text-align: center">{{ $saleOrderItem->product_code }}</td>
                                    <td>{{  round($saleOrderItem->return_quantity) }}</td>
                                    <td style="text-align: right">{{ number_format($saleOrderItem->selling_unit_price,2) }}</td>
                                    <td style="text-align: right">{{ number_format($saleOrderItem->selling_unit_price * $saleOrderItem->return_quantity,2) }}</td>
                                </tr>
                                @php
                                    $totalReturnPrice += $saleOrderItem->return_quantity * $saleOrderItem->selling_unit_price;
                                @endphp
                            @endif
                        @endforeach
                        <tr>
                            <th colspan="3" style="text-align: right !important;">Sub Total</th>
                            <th colspan="" style="text-align: center !important;" class="text-center">
                                {{ ($sale->saleOrderItems->sum('return_quantity')) }}
                            </th>
                            <th colspan="2" style="text-align: right !important;">{{ number_format($totalReturnPrice,2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align: right !important;">Discount</th>
                            <th colspan="2" style="text-align: right !important;">{{ number_format($sale->return_discount,2) }}</th>
                        </tr>
                        <tr>
                            <th colspan="4" style="text-align: right !important;">Total</th>
                            <th colspan="2" style="text-align: right !important;">{{ number_format($sale->return_total,2) }}</th>
                        </tr>
                        
                    </table>
                @endif
                <br/>
                <table class="table table-bordered"  style="width: 100% !important;border-collapse: collapse;">
                    <tbody>
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="7">Invoice Total:</th>
                            <th class="text-right" style="text-align: right !important;" colspan="2">{{ number_format($sale->total,2) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="7">Return Total:</th>
                            <th class="text-right" style="text-align: right !important;" colspan="2">{{ number_format($sale->return_total,2) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="7">=</th>
                            <th class="text-right" style="text-align: right !important;" colspan="2">{{ number_format($sale->total-$sale->return_total,2) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="7">Invoice Paid</th>
                            <th class="text-right" style="text-align: right !important;" colspan="2">{{ number_format($sale->paid,2) }}</th>
                        </tr>
                        <tr>
                            <th class="text-right" style="text-align: right !important;" colspan="7">Refund</th>
                            <th class="text-right" style="text-align: right !important;" colspan="2">{{ number_format($sale->refund,2) }}</th>
                        </tr>
                        @if ($sale->due < 0)
                            <tr>
                                <th class="text-right" style="text-align: right !important;;font-size: 20px" colspan="7">Refund Due</th>
                                <th colspan="2" style="text-align: right !important;font-size: 20px" class="text-right" >{{ number_format(abs($sale->due),2) }}</th>
                            </tr>
                        @else
                            <tr>
                                <th class="text-right" style="text-align: right !important;;font-size: 20px" colspan="7">Due</th>
                                <th colspan="2" style="text-align: right !important;font-size: 20px" class="text-right" >{{ number_format($sale->due,2) }}</th>
                            </tr>
                        @endif
                    </tbody>
                </table>
            @endif
            
            @if($sale->notes)
                <div class="row pt-2 pb-2">
                    <div class="col-12 text-center" style="font-size: 11px;">
                        <p class="mb-0"><strong>Note:</strong> {{ $sale->notes }}</p>
                    </div>
                </div>
            @endif
            
        </div>
    </div>
    <div class="row">
        <div class="col-12 text-center">
            @if($sale->user)
                <p style="margin-bottom: 0;font-size: 11px">Serve By: {{ $sale->user->name ?? '' }}</p>
            @endif
            <p style="margin-bottom: 0;font-size: 11px">In Word: {{ $sale->amount_in_word }} Only</p>
        </div>
    </div>
</div>

<script>
    window.print();
    window.onafterprint = function () {
        window.close()
    };
</script>
</body>
</html>
