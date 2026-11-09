<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Trial Balance Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Trial_Balance_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-sm" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info print-heading m-0">
                            <div class="col-12">
                                <h3 class="text-center m-0 mt-2" style="font-size: 25px !important;">Trial
                                    Balance</h3>
                                @php
                                    $branchName = 'All Branches';
                                    if(request('sister_concern')) {
                                        $branch = \App\Models\BranchManagement\SisterConcern::find(request('sister_concern'));
                                        $branchName = $branch ? ($branch->display_name ?? $branch->name) : 'All Branches';
                                    }
                                @endphp
                                <h3 class="text-center m-0 mb-2" style="font-size: 19px !important;">Branch: {{ $branchName }}</h3>
                                <h3 class="text-center m-0 mb-2" style="font-size: 19px !important;">
                                    Date: {{ request('start_date') }} to {{ request('end_date') }}</h3>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Code</th>
                                <th>Account Head</th>
                                <th>Debit(Taka)</th>
                                <th>Credit(Taka)</th>
                                <th>Balance(Taka)</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php

                                $totalBalance = 0;
                                $totalDebit = 0;
                                $totalCredit = 0;
                            @endphp
                            @foreach($accountHeads as $accountHead)

                                @php


                                     $openingDebit = 0;
                                     $openingCredit = 0;
                                     //3=Asset,5=Liability,4=Equity/Capital


                                $debitOpeningTotal = $openingDebit + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_debit : 0);
                                $creditOpeningTotal = $openingCredit + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_credit : 0);

                                $currentDebitTotal = (count($accountHead->transactions) > 0 ? $accountHead->transactions[0]->debit : 0);
                                $currentCreditTotal = (count($accountHead->transactions) > 0 ? $accountHead->transactions[0]->credit : 0);


                                $debit = $currentDebitTotal + $debitOpeningTotal;
                                $credit = $currentCreditTotal + $creditOpeningTotal;
                                $balance = $debit - $credit;
                                $totalDebit += $debit;
                                $totalCredit += $credit;
                                $totalBalance += $balance;

                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $accountHead->code }}</td>
                                    <td class="text-left"><a href="{{ route('branch_report.ledger',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_head_id'=>$accountHead->id,'sister_concern'=>request('sister_concern'), 'search'=>'Search']) }}">{{ $accountHead->name }}</a></td>
                                    <td class="text-right">{{ number_format($debit,2) }}</td>
                                    <td class="text-right">{{ number_format(abs($credit),2) }}</td>
                                    <td class="text-right">{{ number_format(abs($balance),2)  }}</td>
                                </tr>

                            @endforeach
                            <tr>
                                <th colspan="3" class="text-right">Total Balance</th>
                                <th colspan="" class="text-right">{{ number_format($totalDebit,2) }}</th>
                                <th colspan="" class="text-right">{{ number_format(abs($totalCredit),2) }}</th>
                                <th colspan="" class="text-right">{{ number_format(abs($totalBalance),2) }}</th>
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
