<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Transaction Details</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Transaction_Details_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Transaction Details: {{ $purposeName }}</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 5%">S/L</th>
                                            <th>Date</th>
                                            <th>Voucher No</th>
                                            <th>Account Head</th>
                                            <th>Notes / Remarks</th>
                                            <th class="text-right">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $sl = 1;
                                            $totalAmount = 0;
                                        @endphp
                                        @foreach($cashBankTransactions as $tx)
                                            @php
                                                $totalAmount += $tx->amount;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $sl++ }}</td>
                                                <td class="text-center">{{ \Carbon\Carbon::parse($tx->date)->format('d-m-Y') }}</td>
                                                <td class="text-center">
                                                    @if($tx->voucher)
                                                        <a href="{{ route('voucher.details', ['voucher' => $tx->voucher->id]) }}" target="_blank">
                                                            {{ $tx->voucher->voucher_no }}
                                                        </a>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>{{ $tx->accountHead->name ?? '-' }}</td>
                                                <td>{{ $tx->notes ?? '-' }}</td>
                                                <td class="text-right">{{ number_format($tx->amount, 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="5" class="text-right">Total</th>
                                            <th class="text-right">{{ number_format($totalAmount, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
