@extends('layouts.app')
@section('title','Purchase Orders Details')
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
                <div class="card-header d-none">
                    <div class="d-flex justify-content-end">
                        <a href="{{ route('purchase.details.print', $purchase->id) }}" target="_blank" class="btn btn-info bg-gradient-info btn-sm text-white" title="Print">
                            <i class="fa fa-print"></i> Print
                        </a>
                    </div>
                </div>
                <div class="card-header border-bottom-0">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Purchase_Invoice_' . date('d_m_Y')])
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header',['report_title' => 'Purchase Order'])
                        <div class="row pb-3 header-common-info" >
                            <div class="col-6">
                                <h5 class="text-left m-0">Supplier: {{ $purchase->supplier->member_no ?? '' }} - {{ $purchase->supplier->name ?? '' }}</h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">Order No. <b>{{ $purchase->order_no }}</b></h5>
                            </div>
                            <div class="col-3">
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
                                {{-- <th class="text-center">Quantity(Received)</th> --}}
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
                                    {{-- <td class="text-center">{{ number_format($item->received_quantity) }}</td> --}}
                                    <td class="text-right">{{ number_format($item->unit_price,2) }}</td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->unit_price,2) }}</td>
                                </tr>
                            @endforeach
                                <tr>
                                    <td  colspan="4"></td>
                                    <td class="text-center">{{ number_format($purchase->purchaseItems->sum('quantity')) }}</td>
                                    {{-- <td class="text-center">{{ number_format($purchase->purchaseItems->sum('received_quantity')) }}</td> --}}
                                    <td class="text-right">Sub Total</td>
                                    <td class="text-right">{{ number_format($purchase->sub_total,2) }}</td>
                                </tr>
                                <tr>
                                    <td style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="6">Discount({{ $purchase->discount_percent }}%)</td>
                                    <td class="text-right">{{ number_format($purchase->discount_amount,2) }}</td>
                                </tr>
                                {{-- <tr>
                                    <td style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="8">VAT({{ $purchase->vat_percent }}%)</td>
                                    <td class="text-right">{{ number_format($purchase->vat_amount,2) }}</td>
                                </tr> --}}
                                {{-- <tr>
                                    <td style="border:1px solid #fff!important;border-right:1px solid #000!important;" class="text-right" colspan="9">TAX({{ $purchase->tax_percent }}%)</td>
                                    <td class="text-right">{{ number_format($purchase->tax_amount,2) }}</td>
                                </tr> --}}
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
                <!-- /.card-body -->
            </div>
        </div>
    </div>
@endsection
@section('script')
    @include('layouts.partial.__export_scripts')
    <script>
        function getprintChallan(print) {
            document.title = "{{ $purchase->order_no }}_supplier_details";
            let printContents = document.getElementById(print).innerHTML;
            let originalContents = document.body.innerHTML;
            document.body.innerHTML = printContents;
            window.print();
            location.reload();
        }
    </script>
@endsection