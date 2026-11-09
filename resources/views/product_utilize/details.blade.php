@extends('layouts.app')
@section('title','Product Utilize Details')
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
                    @if (auth()->user()->hasPermissionTo('product_utilize_edit'))
                    <a href="{{ route('product-utilizes.edit',['product_utilize' => $product_utilize->id]) }}" class="btn btn-info bg-gradient-info btn-sm">Edit <i class="fa fa-edit"></i></a>
                    @endif
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-info bg-gradient-info btn-sm">Print <i class="fa fa-print"></i></a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                         @include('layouts.partial.__report_header',['report_title'=>'Product Utilize Details'])
                        <div class="row pb-3" >
                            <div class="col-12">
                                <!-- <h4 class="text-center m-0">Warehouse: {{ $product_utilize->warehouse->name ?? '' }}</h4> -->
                                <h6 class="text-center m-0">Order No. {{ $product_utilize->order_no }}</h6>
                            </div>
                            <div class="col-6 offset-md-6">
                                <h5 class="text-right m-0">Date: {{ \Carbon\Carbon::parse($product_utilize->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Cost Unit Price</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalPurchasePrice = 0;
                            @endphp
                            @foreach($product_utilize->productUtilizeItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-left">{{ $item->product->brand->name ?? '' }}</td>
                                    <td class="text-left">{{ $item->product->productCategory->name ?? '' }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->code ?? '' }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price,2) }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price,2) }}</td>
                                </tr>
                                @php
                                    $totalPurchasePrice += $item->quantity * $item->unit_price;
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="7">Total</th>
                                    <th class="text-center">{{ number_format($product_utilize->productUtilizeItems->sum('quantity')) }}</th>
                                    <th class="text-right">{{ number_format($totalPurchasePrice,2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row signature-area" >
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        {{ $product_utilize->user->name ?? '' }}<br>
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
            document.title = "{{ $product_utilize->order_no }}_product_utilize_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
