<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Order Details - {{ $purchase->order_no }}</title>
    @include('layouts.partial.__styles')
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
        @page {
            margin: .3in .5in .5in .5in;
        }
        body {
            background-color: #f4f6f9;
        }
        .report-container {
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }
        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
            .signature-area{
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
            .report-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .extra-remove {
                display: none !important;
            }
            body {
                background-color: #fff;
            }
            .table-responsive-md {
                overflow-x: visible !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printAreaChallan', 'fileName' => 'Purchase_Details_' . $purchase->order_no])
                    
                    <div class="table-responsive-md" id="printAreaChallan">
                        @include('layouts.partial.__report_header',['report_title' => 'Purchase Order'])
                        
                        <div class="row pb-3 header-common-info" >
                            <div class="col-4">
                                <h5 class="text-left m-0">Supplier: {{ $purchase->supplier->name ?? '' }}</h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-center m-0">Order No. <b>{{ $purchase->order_no }}</b></h5>
                            </div>
                            <div class="col-4">
                                <h5 class="text-right m-0">Date: {{ \Carbon\Carbon::parse($purchase->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($purchase->purchaseItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product_code ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price,2) }}</td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price,2) }}</td>
                                </tr>
                            @endforeach
                                <tr>
                                    <td  colspan="4"></td>
                                    <td class="text-center">{{ number_format($purchase->purchaseItems->sum('quantity')) }}</td>
                                    <td class="text-right">Sub Total</td>
                                    <td class="text-right">{{ number_format($purchase->sub_total,2) }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="6">Discount({{ $purchase->discount_percent }}%)</td>
                                    <td class="text-right">{{ number_format($purchase->discount_amount,2) }}</td>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="6">Total Payable</th>
                                    <th class="text-right">{{ number_format($purchase->total,2) }}</th>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="6">Payment</th>
                                    <th class="text-right">{{ number_format($purchase->paid,2) }}</th>
                                </tr>
                                <tr>
                                    <th style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="6">Due</th>
                                    <th class="text-right">{{ number_format($purchase->due,2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                        
                        @if($purchase->notes)
                            <div class="row header-common-info mb-4">
                                <div class="col-12">
                                    <p class="mb-0"><strong>Note:</strong> {{ $purchase->notes }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <div class="row signature-area header-common-info" >
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        {{ $purchase->user->name ?? '' }}<br>
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
            </div>
        </div>
    </div>
    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
