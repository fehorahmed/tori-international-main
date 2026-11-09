<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contractor Outstanding Report</title>
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Outstanding_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="header-common-info mb-3">
                            <div>
                                <h3 class="text-center" style="font-size: 25px"><u><b>Contractor Outstanding Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 16px">As of: {{ date('d-M-Y h:i A') }}</h5>
                            </div>
                        </div>
                        
                        <table class="table table-bordered table-striped" style="font-size: 14px;">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">S/L</th>
                                    <th>Contractor Name</th>
                                    <th>Mobile No</th>
                                    <th>Type</th>
                                    <th class="text-right">Total Bills</th>
                                    <th class="text-right">Total Payments</th>
                                    <th class="text-right">Balance</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $totalBills = 0; 
                                    $totalPays = 0; 
                                    $totalBalance = 0; 
                                @endphp
                                @foreach($contractors as $index => $contractor)
                                    @php
                                        $bill = $contractor->total_credit ?? 0;
                                        $paid = $contractor->total_debit ?? 0;
                                        $balance = $bill - $paid;
                                        $totalBills += $bill;
                                        $totalPays += $paid;
                                        $totalBalance += $balance;
                                        
                                        $statusText = '';
                                        $statusColor = '';
                                        if ($balance > 0) {
                                            $statusText = number_format($balance, 2) . ' (Due)';
                                            $statusColor = 'color: red;';
                                        } elseif ($balance < 0) {
                                            $statusText = number_format(abs($balance), 2) . ' (Adv)';
                                            $statusColor = 'color: green;';
                                        } else {
                                            $statusText = '0.00';
                                            $statusColor = 'color: gray;';
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ $contractor->name }}</td>
                                        <td>{{ $contractor->mobile_no }}</td>
                                        <td>{{ $contractor->contractorType->name ?? 'N/A' }}</td>
                                        <td class="text-right">{{ number_format($bill, 2) }}</td>
                                        <td class="text-right">{{ number_format($paid, 2) }}</td>
                                        <td class="text-right" style="{{ $statusColor }} font-weight: bold;">{{ $statusText }}</td>
                                        <td class="text-center">
                                            @if($balance > 0)
                                                <span class="badge badge-danger">Receivable</span>
                                            @elseif($balance < 0)
                                                <span class="badge badge-success">Advance Paid</span>
                                            @else
                                                <span class="badge badge-secondary">Settled</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Grand Total:</th>
                                    <th class="text-right">{{ number_format($totalBills, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalPays, 2) }}</th>
                                    <th class="text-right">
                                        @if($totalBalance > 0)
                                            <span style="color: red;">{{ number_format($totalBalance, 2) }} (Due)</span>
                                        @elseif($totalBalance < 0)
                                            <span style="color: green;">{{ number_format(abs($totalBalance), 2) }} (Adv)</span>
                                        @else
                                            0.00
                                        @endif
                                    </th>
                                    <th></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
