<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Income Statement Report</title>
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
        .double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 3px double #000 !important;
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
            .table-responsive-sm {
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Income_Statement_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-sm" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info print-heading">
                            <div class="col-12">
                                <p class="text-center m-0" style="font-size: 20px !important;">
                                    STATEMENT OF PROFIT OR LOSS AND OTHER COMPREHENSIVE INCOME </p>
                                <p class="text-center" style="font-size: 20px !important;">FOR THE
                                    PERIOD ENDED {{ date('d',strtotime(request('end_date'))) }}
                                    th {{ date('F',strtotime(request('end_date'))) }},
                                    {{ date('Y',strtotime(request('end_date'))) }} (Provisional)</p>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th colspan="" width="50%" class="text-center" rowspan="2">Particulars</th>
                                <th width="3%" class="text-center" rowspan="2">Note</th>
                                <th colspan="4" class="text-center">Amount in Taka</th>
                            </tr>
                            <tr>
                                <th class="text-center">{{ date('d-m-Y',strtotime(request('end_date'))) }}</th>
                                <th width="2%"></th>
                                <th class="text-center">{{ date('d-m-Y',strtotime('-1 day', strtotime(request('start_date')))) }}</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td colspan="5">&nbsp;</td>
                            </tr>
                            @php
                                $grossProfitBalance = 0;
                                $previousGrossProfitBalance = 0;
                            @endphp

                            {{-- 1. Operating Revenue --}}
                            @if($operatingRevenue)
                                @php
                                    $revenueBalance = accountGroupBalanceIncomeStatement($operatingRevenue->id,request('start_date'),request('end_date'),1,request('sister_concern'));
                                    $previousRevenueBalance = accountGroupBalanceIncomeStatement($operatingRevenue->id,request('start_date'),request('end_date'),2,request('sister_concern'));
                                    $grossProfitBalance += $revenueBalance;
                                    $previousGrossProfitBalance += $previousRevenueBalance;
                                @endphp

                                <tr>
                                    <td class="text-left"><b>Revenue</b></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td class="text-left"><a target="_blank" href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$operatingRevenue->id,'sister_concern'=>request('sister_concern')]) }}">{{ $operatingRevenue->name}}</a></td>
                                    <td>{{ $operatingRevenue->note_no }}</td>
                                    <td class="text-right border-bottom">{{ wrapPositive($revenueBalance) }}</td>
                                    <td></td>
                                    <td class="text-right border-bottom">{{ wrapPositive($previousRevenueBalance) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="2" class="text-left"><b>Net Revenue</b></td>
                                <td class="text-right"><b>{{ wrapPositive($grossProfitBalance) }}</b></td>
                                <td></td>
                                <td class="text-right"><b>{{ wrapPositive($previousGrossProfitBalance) }}</b></td>
                            </tr>

                            {{-- 2. Cost of Goods Sold --}}
                            @if($costOfGoodsSold)
                                @php
                                    $costOfGoodsBalance = accountGroupBalanceIncomeStatement($costOfGoodsSold->id,request('start_date'),request('end_date'),1,request('sister_concern'));
                                    $previousCostOfGoodsBalance = accountGroupBalanceIncomeStatement($costOfGoodsSold->id,request('start_date'),request('end_date'),2,request('sister_concern'));
                                    $grossProfitBalance += $costOfGoodsBalance;
                                    $previousGrossProfitBalance += $previousCostOfGoodsBalance;
                                @endphp
                                <tr>
                                    <td class="text-left"><a target="_blank" href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$costOfGoodsSold->id,'sister_concern'=>request('sister_concern')]) }}">Less: {{ $costOfGoodsSold->name }}</a></td>
                                    <td>{{ $costOfGoodsSold->note_no }}</td>
                                    <td class="text-right border-bottom">{{ wrapNegative($costOfGoodsBalance) }}</td>
                                    <td></td>
                                    <td class="text-right border-bottom">{{ wrapNegative($previousCostOfGoodsBalance) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="2" class="text-left"><b>Gross Profit/(Loss)</b></td>
                                <td class="text-right"><b>{{ wrapPositive($grossProfitBalance) }}</b></td>
                                <td></td>
                                <td class="text-right"><b>{{ wrapPositive($previousGrossProfitBalance) }}</b></td>
                            </tr>

                            <tr>
                                <td colspan="5">&nbsp;</td>
                            </tr>

                            {{-- 3. Operating Expenses --}}
                            @php
                                $operatingProfitBalance = $grossProfitBalance;
                                $previousOperatingProfitBalance = $previousGrossProfitBalance;
                            @endphp

                            @if($operatingExpenses)
                                @php
                                    $opExpBalance = accountGroupBalanceIncomeStatement($operatingExpenses->id,request('start_date'),request('end_date'),1,request('sister_concern'));
                                    $previousOpExpBalance = accountGroupBalanceIncomeStatement($operatingExpenses->id,request('start_date'),request('end_date'),2,request('sister_concern'));
                                    $operatingProfitBalance += $opExpBalance;
                                    $previousOperatingProfitBalance += $previousOpExpBalance;
                                @endphp
                                <tr>
                                    <td class="text-left"><a target="_blank" href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$operatingExpenses->id,'sister_concern'=>request('sister_concern')]) }}">Less: {{ $operatingExpenses->name }}</a></td>
                                    <td>{{ $operatingExpenses->note_no }}</td>
                                    <td class="text-right border-bottom">{{ wrapNegative($opExpBalance) }}</td>
                                    <td></td>
                                    <td class="text-right border-bottom">{{ wrapNegative($previousOpExpBalance) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="2" class="text-left"><b>Operating Profit/(Loss)</b></td>
                                <td class="text-right"><b>{{ wrapPositive($operatingProfitBalance) }}</b></td>
                                <td></td>
                                <td class="text-right"><b>{{ wrapPositive($previousOperatingProfitBalance) }}</b></td>
                            </tr>

                            <tr>
                                <td colspan="5">&nbsp;</td>
                            </tr>

                            {{-- 4. Financial & Other Income/Expense --}}
                            @php
                                $netProfitBeforeTax = $operatingProfitBalance;
                                $previousNetProfitBeforeTax = $previousOperatingProfitBalance;
                            @endphp

                            @if($otherIncome)
                                @php
                                    $otherIncBalance = accountGroupBalanceIncomeStatement($otherIncome->id,request('start_date'),request('end_date'),1,request('sister_concern'));
                                    $previousOtherIncBalance = accountGroupBalanceIncomeStatement($otherIncome->id,request('start_date'),request('end_date'),2,request('sister_concern'));
                                    $netProfitBeforeTax += $otherIncBalance;
                                    $previousNetProfitBeforeTax += $previousOtherIncBalance;
                                @endphp
                                <tr>
                                    <td class="text-left"><a target="_blank" href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$otherIncome->id,'sister_concern'=>request('sister_concern')]) }}">Add: {{ $otherIncome->name }}</a></td>
                                    <td>{{ $otherIncome->note_no }}</td>
                                    <td class="text-right">{{ wrapPositive($otherIncBalance) }}</td>
                                    <td></td>
                                    <td class="text-right">{{ wrapPositive($previousOtherIncBalance) }}</td>
                                </tr>
                            @endif

                            @if($financialExpenses)
                                @php
                                    $finExpBalance = accountGroupBalanceIncomeStatement($financialExpenses->id,request('start_date'),request('end_date'),1,request('sister_concern'));
                                    $previousFinExpBalance = accountGroupBalanceIncomeStatement($financialExpenses->id,request('start_date'),request('end_date'),2,request('sister_concern'));
                                    $netProfitBeforeTax += $finExpBalance;
                                    $previousNetProfitBeforeTax += $previousFinExpBalance;
                                @endphp
                                <tr>
                                    <td class="text-left"><a target="_blank" href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$financialExpenses->id,'sister_concern'=>request('sister_concern')]) }}">Less: {{ $financialExpenses->name }}</a></td>
                                    <td>{{ $financialExpenses->note_no }}</td>
                                    <td class="text-right border-bottom">{{ wrapNegative($finExpBalance) }}</td>
                                    <td></td>
                                    <td class="text-right border-bottom">{{ wrapNegative($previousFinExpBalance) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="2" class="text-left"><b>Net Profit /(Loss) before Tax</b></td>
                                <td class="text-right"><b>{{ wrapPositive($netProfitBeforeTax) }}</b></td>
                                <td></td>
                                <td class="text-right"><b>{{ wrapPositive($previousNetProfitBeforeTax) }}</b></td>
                            </tr>

                            {{-- 5. Tax Expenses --}}
                            @php
                                $netProfitAfterTax = $netProfitBeforeTax;
                                $previousNetProfitAfterTax = $previousNetProfitBeforeTax;
                            @endphp

                            @if($taxExpenses)
                                @php
                                    $taxExpBalance = accountGroupBalanceIncomeStatement($taxExpenses->id,request('start_date'),request('end_date'),1,request('sister_concern'));
                                    $previousTaxExpBalance = accountGroupBalanceIncomeStatement($taxExpenses->id,request('start_date'),request('end_date'),2,request('sister_concern'));
                                    $netProfitAfterTax += $taxExpBalance;
                                    $previousNetProfitAfterTax += $previousTaxExpBalance;
                                @endphp
                                <tr>
                                    <td class="text-left"><a target="_blank" href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$taxExpenses->id,'sister_concern'=>request('sister_concern')]) }}">Less: {{ $taxExpenses->name }}</a></td>
                                    <td>{{ $taxExpenses->note_no }}</td>
                                    <td class="text-right border-bottom">{{ wrapNegative($taxExpBalance) }}</td>
                                    <td></td>
                                    <td class="text-right border-bottom">{{ wrapNegative($previousTaxExpBalance) }}</td>
                                </tr>
                            @endif

                            <tr>
                                <td colspan="5">&nbsp;</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="text-left"><b>Net Profit/(Loss) After Tax Transferred to BS</b></td>
                                <td class="text-right double-single-border"><b>{{ wrapPositive($netProfitAfterTax) }}</b></td>
                                <td></td>
                                <td class="text-right double-single-border"><b>{{ wrapPositive($previousNetProfitAfterTax) }}</b></td>
                            </tr>
                            <tr>
                                <td colspan="5">&nbsp;</td>
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
