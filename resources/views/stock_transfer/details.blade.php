@extends('layouts.app')
@section('title','Stock Transfer Order Details')
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
                <div class="card-header d-flex align-items-center">
                    <a href="#" role="button" onclick="getprintChallan('printAreaChallan')" class="btn btn-info bg-gradient-info btn-sm" title="Print Invoice with Prices for Accounts"> Print Invoice <i class="fa fa-print"></i></a>
                    <a href="{{ route('stock-transfers.print_challan', ['stock_transfer' => $stockTransfer->id]) }}" target="_blank" class="btn btn-primary bg-gradient-primary btn-sm ml-2" title="Print Delivery Challan & Gate Pass (No Prices)"> Print Delivery Challan <i class="fa fa-file-invoice"></i></a>
                    
                    @if($stockTransfer->status == 1)
                        <form action="{{ route('stock-transfers.dispatch', ['stock_transfer' => $stockTransfer->id]) }}" method="POST" class="d-inline ml-2" id="dispatch-form">
                            @csrf
                            <input type="hidden" name="vehicle_no" id="dispatch_vehicle_no" value="{{ $stockTransfer->vehicle_no }}">
                            <input type="hidden" name="driver_name" id="dispatch_driver_name" value="{{ $stockTransfer->driver_name }}">
                            <input type="hidden" name="driver_phone" id="dispatch_driver_phone" value="{{ $stockTransfer->driver_phone }}">
                            <button type="button" id="btn-dispatch-stock" class="btn btn-success bg-gradient-success btn-sm">
                                Dispatch & Issue Gate Pass <i class="fa fa-truck"></i>
                            </button>
                        </form>
                    @else
                        <span class="badge badge-success ml-2 py-2 px-3"><i class="fa fa-check-circle"></i> Dispatched & Gate Pass Issued</span>
                    @endif
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div class="table-responsive" id="printAreaChallan">
                        @include('layouts.partial.__report_header')
                        <div class="row pt-3 pb-3" >
                            <div class="col-3">
                                <h5 class="text-center m-0">
                                    From Branch:
                                    {{ $stockTransfer->fromSisterConcern->name ?? ($stockTransfer->fromWarehouse->name ?? '') }}
                                </h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">
                                    To Branch:
                                    {{ $stockTransfer->toSisterConcern->name ?? ($stockTransfer->toWarehouse->name ?? '') }}
                                </h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">Order No. {{ $stockTransfer->order_no }}</h5>
                            </div>
                            <div class="col-3">
                                <h5 class="text-center m-0">Date: {{ \Carbon\Carbon::parse($stockTransfer->date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        
                        <!-- Driver & Vehicle Details for Logistics -->
                        <div class="row pb-3 mb-2 border-bottom">
                            <div class="col-4">
                                <p class="m-0 text-center"><strong>Vehicle No:</strong> {{ $stockTransfer->vehicle_no ?? 'N/A' }}</p>
                            </div>
                            <div class="col-4">
                                <p class="m-0 text-center"><strong>Driver Name:</strong> {{ $stockTransfer->driver_name ?? 'N/A' }}</p>
                            </div>
                            <div class="col-4">
                                <p class="m-0 text-center"><strong>Driver Mobile:</strong> {{ $stockTransfer->driver_phone ?? 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Qty</th>
                                <th class="text-center">Cost Price</th>
                                <th class="text-center">Transfer Price</th>
                                <th class="text-center">Transfer Total</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $totalSalesPrice = 0;
                            @endphp
                            @foreach($stockTransfer->stockTransferItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product_code }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }} {{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->transfer_price ?? $item->unit_price, 2) }}</td>
                                    <td class="text-right">{{ number_format($item->quantity * ($item->transfer_price ?? $item->unit_price), 2) }}</td>
                                </tr>
                                @php
                                    $totalSalesPrice += $item->quantity * ($item->transfer_price ?? $item->unit_price);
                                @endphp
                            @endforeach
                                <tr>
                                    <th class="text-right" colspan="3">Total</th>
                                    <th class="text-center">{{ number_format($stockTransfer->stockTransferItems->sum('quantity')) }}</th>
                                    <th class="text-right" colspan="3">{{ number_format($totalSalesPrice, 2) }}</th>
                                </tr>

                            </tbody>
                        </table>
                        <div class="row signature-area" >
                            <div class="col text-center">
                                    <span style="border: 1px solid transparent !important;display: block;padding: 18px;font-size: 20px;font-weight: bold">
                                        {{ $stockTransfer->user->name ?? '' }}<br>
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
            document.title = "{{ $stockTransfer->order_no }}_stock_transfer_details";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }

        $('#btn-dispatch-stock').click(function(e) {
            e.preventDefault();
            
            let currentVehicle = $('#dispatch_vehicle_no').val() || '';
            let currentDriver = $('#dispatch_driver_name').val() || '';
            let currentPhone = $('#dispatch_driver_phone').val() || '';

            Swal.fire({
                title: 'Dispatch Stock & Issue Gate Pass',
                html:
                    '<div class="text-left mb-3"><i class="fa fa-info-circle text-info"></i> <i>Fill or update vehicle/driver details before gate exit.</i></div>' +
                    '<div class="form-group text-left">' +
                    '   <label for="swal-vehicle" style="font-weight:bold;">Vehicle / Truck No</label>' +
                    '   <input id="swal-vehicle" class="form-control" placeholder="e.g. Dhaka Metro-T 11-2233" value="' + currentVehicle + '">' +
                    '</div>' +
                    '<div class="form-group text-left">' +
                    '   <label for="swal-driver" style="font-weight:bold;">Driver Name</label>' +
                    '   <input id="swal-driver" class="form-control" placeholder="e.g. Robin Mia" value="' + currentDriver + '">' +
                    '</div>' +
                    '<div class="form-group text-left">' +
                    '   <label for="swal-phone" style="font-weight:bold;">Driver Mobile No.</label>' +
                    '   <input id="swal-phone" class="form-control" placeholder="e.g. 017XXXXXXXX" value="' + currentPhone + '">' +
                    '</div>',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirm & Dispatch Stock <i class="fa fa-check"></i>',
                cancelButtonText: 'Cancel',
                focusConfirm: false,
                preConfirm: () => {
                    return {
                        vehicle: document.getElementById('swal-vehicle').value,
                        driver: document.getElementById('swal-driver').value,
                        phone: document.getElementById('swal-phone').value
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Update hidden input fields with user values from Swal
                    $('#dispatch_vehicle_no').val(result.value.vehicle);
                    $('#dispatch_driver_name').val(result.value.driver);
                    $('#dispatch_driver_phone').val(result.value.phone);

                    // Submit form
                    preloaderToggle(true);
                    $('#dispatch-form').submit();
                }
            });
        });
    </script>
@endsection
