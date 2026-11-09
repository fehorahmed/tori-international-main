<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Receipt & Payment Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Receipt_And_Payment_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info m-0">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Receipt & Payment Report</b></u></h3>
                                @php
                                    $branchName = 'All Branches';
                                    if(request('sister_concern')) {
                                        $branch = \App\Models\BranchManagement\SisterConcern::find(request('sister_concern'));
                                        $branchName = $branch ? ($branch->display_name ?? $branch->name) : 'All Branches';
                                    }
                                @endphp
                                <h3 class="text-center m-0 mb-2" style="font-size: 19px !important;">Branch: {{ $branchName }}</h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Particulars</th>
                                <th class="text-center">Payment Mode</th>
                                <th class="text-center">Receipt Amount</th>
                                <th class="text-center">Payment Amount</th>
                                <th class="text-center">Profit</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                $profit = 0;
                            @endphp
                            @foreach($logs as $log)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($log->date)->format('d-m-Y') }}</td>
                                    <td class="text-left">{{ $log->accountHead->name ?? '' }}
                                        @if($log->notes != '')
                                            ({{ $log->notes }})
                                        @endif
                                    </td>
                                    <td class="text-left">{{ $log->paymentAccountHead->name}}</td>
                                    <td class="text-right">
                                        @if($log->voucher_type == 3)
                                            @php
                                                $profit += $log->amount;
                                            @endphp
                                            {{ wrapNegative($log->amount) }}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($log->voucher_type == 2)
                                            @php
                                                $profit -= $log->amount;
                                            @endphp
                                        {{ wrapNegative($log->amount) }}
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        {{ wrapNegative($profit) }}
                                    </td>
                                </tr>
                            @endforeach
                            <tr>
                                <th colspan="4" class="text-left">
                                    Amount in Words: {{ SakibRahaman\DecimalToWords\DecimalToWords::convert(abs($profit), 'Taka','Poisa') }} Only.
                                </th>
                                <th  class="text-right">Net Receipt</th>
                                <th colspan="3" class="text-right"> {{ wrapNegative($profit) }}</th>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
