@extends('layouts.app')
@section('title', 'Stock Conversion Details')
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
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-info bg-gradient-info btn-sm"> Print <i class="fa fa-print"></i></a>
                    <div class="card-tools">
                        <a href="{{ route('stock-conversions.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back to List</a>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        @include('layouts.partial.__report_header')
                        <div class="row pt-3 pb-3" >
                            <div class="col-3">
                                <h5 class="text-center m-0">
                                    Warehouse:
                                    {{ $conversion->warehouse->name ?? 'N/A' }}
                                </h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">
                                    Status:
                                    @if($conversion->status == 1)
                                        Pending
                                    @elseif($conversion->status == 2)
                                        Approved
                                    @else
                                        Cancelled
                                    @endif
                                </h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">Order No. {{ $conversion->order_no }}</h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">Date: {{ \Carbon\Carbon::parse($conversion->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th>From Product</th>
                                <th>To Product</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-right">From Cost</th>
                                <th class="text-right">Unit Conv. Cost</th>
                                <th class="text-right">New Unit Cost</th>
                                <th class="text-right">Total Value</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalNewValue = 0;
                            @endphp
                            @foreach($conversion->items as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->fromProduct->name ?? 'N/A' }} (Batch: {{ $item->fromInventory->batch_no ?? 'N/A' }})</td>
                                    <td>{{ $item->toProduct->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->from_unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->unit_conversion_cost, 2) }}</td>
                                    <td class="text-right"><strong>{{ number_format($item->to_unit_price, 2) }}</strong></td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->to_unit_price, 2) }}</td>
                                </tr>
                                @php
                                    $totalNewValue += $item->quantity * $item->to_unit_price;
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="3">Total</th>
                                    <th class="text-center">{{ number_format($conversion->items->sum('quantity')) }}</th>
                                    <th class="text-right" colspan="3"></th>
                                    <th class="text-right">{{ number_format($totalNewValue, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                        <div class="row mt-3">
                            <div class="col-12">
                                <strong>Notes:</strong> {{ $conversion->notes }}
                            </div>
                        </div>
                        <div class="row signature-area" >
                            <div class="col text-center">
                                <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                    {{ $conversion->createdBy->name ?? '' }}<br>
                                     Created By
                                </span>
                            </div>
                            <div class="col text-center">
                                <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                    {{ $conversion->approvedBy->name ?? '' }}<br>
                                    Approved By
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    @if($conversion->status == 1 && auth()->user()->hasPermissionTo('stock_transfer_create'))
                        <div class="row mt-4">
                            <div class="col-12 text-center">
                                <form action="{{ route('stock-conversions.approve', $conversion->id) }}" method="POST" id="approve-form">
                                    @csrf
                                    <button type="button" class="btn btn-success btn-lg bg-gradient-success" onclick="confirmApproval()">
                                        <i class="fas fa-check-circle"></i> Approve Conversion
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
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
        document.title = "{{ $conversion->order_no }}_stock_conversion_details";
        $(".extra-remove").remove();
        $(".footer-print-area").show();
        $('body').html($('#' + print).html());
        window.print();
        window.location.replace(APP_URL)
    }

    function confirmApproval() {
        Swal.fire({
            title: 'Are you sure?',
            text: "Approving this will instantly update the inventory and post accounting ledgers. This action is critical!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, Approve it!'
        }).then((result) => {
            if (result.isConfirmed) {
                $('#approve-form').submit();
            }
        })
    }
</script>
@endsection
