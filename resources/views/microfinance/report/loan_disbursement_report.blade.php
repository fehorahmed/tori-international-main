<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 16px !important;
        }
        td a:hover{
            color: #000 !important;
            text-decoration: underline;
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
            .table-bordered td, .table-bordered th {
                font-size: 16px !important;
            }
            td a{
                color: #000 !important;
                text-decoration: none !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Loan_Disbursement_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                <h3 class="text-center" style="font-size: 20px; margin-top: 10px; margin-bottom: 5px;"><u><b>{{ $pageTitle }}</b></u></h3>
                                <h5 class="text-center m-0" style="font-size: 16px"><b>Branch:</b> {{ auth()->user()->sisterConcern->name ?? auth()->user()->activeSisterConcern->name ?? '' }}</h5>
                                <h5 class="text-center m-0" style="font-size: 15px"><b>Date Range:</b> {{ date('d-m-Y', strtotime($startDate)) }} to {{ date('d-m-Y', strtotime($endDate)) }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 5%">SL</th>
                                    <th class="text-left" style="width: 25%">Member Details</th>
                                    <th class="text-left" style="width: 15%">Somiti</th>
                                    <th class="text-center" style="width: 15%">Loan Account No.</th>
                                    <th class="text-center" style="width: 15%">Product</th>
                                    <th class="text-center" style="width: 10%">Disbursement Date</th>
                                    <th class="text-right" style="width: 15%">Disbursed Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $i = 1; 
                                    $totalDisbursed = 0;
                                @endphp
                                @forelse($loans as $loan)
                                    @php
                                        $totalDisbursed += $loan->approved_amount;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $i++ }}</td>
                                        <td class="text-left">
                                            <b>{{ $loan->member->name ?? 'N/A' }}</b><br>
                                            <small>A/C: {{ $loan->member->member_no ?? '' }}</small>
                                        </td>
                                        <td class="text-left">
                                            {{ $loan->member->somiti->name ?? 'N/A' }}<br>
                                            <small>{{ $loan->member->somiti->somiti_no ?? '' }}</small>
                                        </td>
                                        <td class="text-center">{{ $loan->loan_account_no }}</td>
                                        <td class="text-center">{{ $loan->product->name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ date('d-m-Y', strtotime($loan->disbursement_date)) }}</td>
                                        <td class="text-right font-weight-bold">
                                            {{ number_format($loan->approved_amount, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No loan disbursements found in this date range.</td>
                                    </tr>
                                @endforelse
                                @if($loans->count() > 0)
                                    <tr>
                                        <th colspan="6" class="text-right">Total Disbursed:</th>
                                        <th class="text-right text-success font-weight-bold">{{ number_format($totalDisbursed, 2) }}</th>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
