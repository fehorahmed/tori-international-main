<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Transfer Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
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
        .table th, .table td {
            font-size: 13px;
            padding: 0.4rem;
        }
        @media print {
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Stock_Transfer_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Stock Transfer Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date Range: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                                @if(request()->filled('sister_concern'))
                                    @php
                                        $sc = \App\Models\BranchManagement\SisterConcern::find(request('sister_concern'));
                                    @endphp
                                    <p class="text-center" style="margin: 0;font-size: 16px;">Filtered By Branch: {{ $sc->name ?? '' }}</p>
                                @endif
                            </div>
                        </div>

                        @if(count($transfers) > 0)
                        <table class="table table-bordered table-sm mt-3">
                            <thead>
                            <tr class="bg-light">
                                <th class="text-center" style="width: 5%">S/L</th>
                                <th class="text-center" style="width: 10%">Date</th>
                                <th class="text-center" style="width: 15%">Challan/Order No</th>

                                <th class="text-center" style="width: 18%">From Branch</th>
                                <th class="text-center" style="width: 18%">To Branch</th>
                                <th class="text-center" style="width: 12%">Transfer Total</th>
                                <th class="text-center" style="width: 12%">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach($transfers as $transfer)
                                    @php 
                                        $subtotal = $transfer->stockTransferItems->sum(function($item) {
                                            $qty = $item->status == 'pending' ? $item->quantity : $item->received_quantity;
                                            return $qty * ($item->transfer_price ?? $item->unit_price);
                                        });
                                        $grandTotal += $subtotal;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($transfer->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">
                                            <a target="_blank" href="{{ route('stock-transfers.details', ['stock_transfer' => $transfer->id]) }}">
                                                {{ $transfer->order_no }}
                                            </a>
                                        </td>

                                        <td class="text-left">{{ $transfer->fromSisterConcern->name ?? ($transfer->fromWarehouse->name ?? 'N/A') }}</td>
                                        <td class="text-left">{{ $transfer->toSisterConcern->name ?? ($transfer->toWarehouse->name ?? 'N/A') }}</td>
                                        <td class="text-right"><strong>{{ number_format($subtotal, 2) }}</strong></td>
                                        <td class="text-center">
                                            @if($transfer->status == 1)
                                                <span class="badge badge-secondary">Pending</span>
                                            @elseif($transfer->status == 2)
                                                <span class="badge badge-primary">In Transit</span>
                                            @elseif($transfer->status == 3)
                                                <span class="badge badge-warning">Partially Received</span>
                                            @elseif($transfer->status == 4)
                                                <span class="badge badge-success">Received</span>
                                            @elseif($transfer->status == 5)
                                                <span class="badge badge-danger">Rejected</span>
                                            @else
                                                <span class="badge badge-dark">Completed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light">
                                    <th colspan="5" class="text-right">Grand Total</th>
                                    <th class="text-right" style="font-size: 16px;">{{ number_format($grandTotal, 2) }}</th>
                                    <th></th>
                                </tr>
                            </tbody>
                        </table>
                        @else
                            <h5 class="text-center mt-5 text-danger">No Stock Transfers found for the selected criteria!</h5>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
