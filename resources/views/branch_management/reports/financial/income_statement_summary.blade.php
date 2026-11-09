<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Income Statement Summary</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            text-align: center;
            padding: 0.5rem !important;
            font-size: 16px !important;
        }
        
        .table-bordered {
            border: 1px solid #000;
        }

       td a:hover{
            color: #000 !important;
            text-decoration: underline;
        }

        @page { margin: .3in .5in .5in .5in; }
        body { background-color: #f4f6f9; }
        .report-container {
            background: #fff; padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1); min-height: 100vh;
        }
        @media print {
            .report-container { margin: 0; padding: 0; box-shadow: none; }
            .extra-remove { display: none !important; }
            body { background-color: #fff; }
            td a {
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Income_Statement_Summary_' . date('d_m_Y')])
                    
                    <div class="table-responsive-sm" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info print-heading">
                            <div class="col-12">
                                <p class="text-center m-0" style="font-size: 20px !important;">
                                    INCOME STATEMENT SUMMARY (ALL BRANCHES)</p>
                                <p class="text-center" style="font-size: 18px !important;">FOR THE
                                    PERIOD ENDED {{ date('d',strtotime(request('end_date'))) }}
                                    th {{ date('F',strtotime(request('end_date'))) }},
                                    {{ date('Y',strtotime(request('end_date'))) }}</p>
                            </div>
                        </div>

                        <table class="table table-bordered w-100 mt-4">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center">Branch Name</th>
                                    <th class="text-center">Total Expenditure (Tk)</th>
                                    <th class="text-center">Total Income (Tk)</th>
                                    <th class="text-center">Profit / (Loss)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandTotalIncome = 0;
                                    $grandTotalExpense = 0;
                                    $grandTotalNetProfit = 0;
                                @endphp

                                @foreach($sister_concerns as $concern)
                                    @php
                                        $totalIncome = 0;
                                        $totalExpense = 0;

                                        // Income Calculations
                                        if ($operatingRevenue) {
                                            $totalIncome += abs(accountGroupBalanceIncomeStatement($operatingRevenue->id, request('start_date'), request('end_date'), 1, $concern->id));
                                        }
                                        if ($otherIncome) {
                                            $totalIncome += abs(accountGroupBalanceIncomeStatement($otherIncome->id, request('start_date'), request('end_date'), 1, $concern->id));
                                        }

                                        // Expense Calculations
                                        if ($costOfGoodsSold) {
                                            $totalExpense += abs(accountGroupBalanceIncomeStatement($costOfGoodsSold->id, request('start_date'), request('end_date'), 1, $concern->id));
                                        }
                                        if ($operatingExpenses) {
                                            $totalExpense += abs(accountGroupBalanceIncomeStatement($operatingExpenses->id, request('start_date'), request('end_date'), 1, $concern->id));
                                        }
                                        if ($financialExpenses) {
                                            $totalExpense += abs(accountGroupBalanceIncomeStatement($financialExpenses->id, request('start_date'), request('end_date'), 1, $concern->id));
                                        }
                                        if ($taxExpenses) {
                                            $totalExpense += abs(accountGroupBalanceIncomeStatement($taxExpenses->id, request('start_date'), request('end_date'), 1, $concern->id));
                                        }

                                        $netProfit = $totalIncome - $totalExpense;

                                        $grandTotalIncome += $totalIncome;
                                        $grandTotalExpense += $totalExpense;
                                        $grandTotalNetProfit += $netProfit;
                                    @endphp
                                    <tr>
                                        <td class="text-left">
                                            <a href="{{ route('branch_report.income_statement',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'sister_concern'=>$concern->id, 'search' => 'Search']) }}">{{ $concern->name }}</a>
                                        </td>
                                        <td class="text-right">{{ number_format($totalExpense, 2) }}</td>
                                        <td class="text-right">{{ number_format($totalIncome, 2) }}</td>
                                        <td class="text-right">
                                            @if($netProfit >= 0)
                                                {{ number_format($netProfit, 2) }}
                                            @else
                                                ({{ number_format(abs($netProfit), 2) }})
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f4f6f9; font-weight: bold;">
                                    <td class="text-right">Grand Total</td>
                                    <td class="text-right">{{ number_format($grandTotalExpense, 2) }}</td>
                                    <td class="text-right">{{ number_format($grandTotalIncome, 2) }}</td>
                                    <td class="text-right">
                                        @if($grandTotalNetProfit >= 0)
                                            {{ number_format($grandTotalNetProfit, 2) }}
                                        @else
                                            ({{ number_format(abs($grandTotalNetProfit), 2) }})
                                        @endif
                                    </td>
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
