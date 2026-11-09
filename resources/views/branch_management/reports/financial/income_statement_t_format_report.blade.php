<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Income Statement (T-Format)</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            padding: 0.2rem !important;
            font-size: 15px !important;
        }
        .double-single-border {
            border-top: 1px solid #000 !important;
            border-bottom: 3px double #000 !important;
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Income_Statement_T_Format_' . date('d_m_Y')])
                    
                    <div class="table-responsive-sm" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info print-heading m-0">
                            <div class="col-12">
                                <p class="text-center m-0" style="font-size: 20px !important;">
                                    INCOME STATEMENT (T-FORMAT) </p>
                                @php
                                    $branchName = 'All Branches';
                                    if(request('sister_concern')) {
                                        $branch = \App\Models\BranchManagement\SisterConcern::find(request('sister_concern'));
                                        $branchName = $branch ? ($branch->display_name ?? $branch->name) : 'All Branches';
                                    }
                                @endphp
                                <p class="text-center m-0" style="font-size: 18px !important;">Branch: {{ $branchName }}</p>
                                <p class="text-center" style="font-size: 18px !important;">FOR THE
                                    PERIOD ENDED {{ date('d',strtotime(request('end_date'))) }}
                                    th {{ date('F',strtotime(request('end_date'))) }},
                                    {{ date('Y',strtotime(request('end_date'))) }}</p>
                            </div>
                        </div>

                        @php
                            $incomes = [];
                            $expenses = [];
                            
                            $totalIncome = 0;
                            $previousTotalIncome = 0;
                            
                            $totalExpense = 0;
                            $previousTotalExpense = 0;

                            // Incomes (Right Side)
                            if ($operatingRevenue) {
                                $revBal = accountGroupBalanceIncomeStatement($operatingRevenue->id, request('start_date'), request('end_date'), 1, request('sister_concern'));
                                $prevRevBal = accountGroupBalanceIncomeStatement($operatingRevenue->id, request('start_date'), request('end_date'), 2, request('sister_concern'));
                                $incomes[] = ['name' => $operatingRevenue->name, 'id' => $operatingRevenue->id, 'note' => $operatingRevenue->note_no, 'current' => abs($revBal), 'previous' => abs($prevRevBal)];
                                $totalIncome += abs($revBal);
                                $previousTotalIncome += abs($prevRevBal);
                            }
                            if ($otherIncome) {
                                $othBal = accountGroupBalanceIncomeStatement($otherIncome->id, request('start_date'), request('end_date'), 1, request('sister_concern'));
                                $prevOthBal = accountGroupBalanceIncomeStatement($otherIncome->id, request('start_date'), request('end_date'), 2, request('sister_concern'));
                                $incomes[] = ['name' => $otherIncome->name, 'id' => $otherIncome->id, 'note' => $otherIncome->note_no, 'current' => abs($othBal), 'previous' => abs($prevOthBal)];
                                $totalIncome += abs($othBal);
                                $previousTotalIncome += abs($prevOthBal);
                            }

                            // Expenses (Left Side)
                            if ($costOfGoodsSold) {
                                $cogsBal = accountGroupBalanceIncomeStatement($costOfGoodsSold->id, request('start_date'), request('end_date'), 1, request('sister_concern'));
                                $prevCogsBal = accountGroupBalanceIncomeStatement($costOfGoodsSold->id, request('start_date'), request('end_date'), 2, request('sister_concern'));
                                $expenses[] = ['name' => $costOfGoodsSold->name, 'id' => $costOfGoodsSold->id, 'note' => $costOfGoodsSold->note_no, 'current' => abs($cogsBal), 'previous' => abs($prevCogsBal)];
                                $totalExpense += abs($cogsBal);
                                $previousTotalExpense += abs($prevCogsBal);
                            }
                            if ($operatingExpenses) {
                                $opExpBal = accountGroupBalanceIncomeStatement($operatingExpenses->id, request('start_date'), request('end_date'), 1, request('sister_concern'));
                                $prevOpExpBal = accountGroupBalanceIncomeStatement($operatingExpenses->id, request('start_date'), request('end_date'), 2, request('sister_concern'));
                                $expenses[] = ['name' => $operatingExpenses->name, 'id' => $operatingExpenses->id, 'note' => $operatingExpenses->note_no, 'current' => abs($opExpBal), 'previous' => abs($prevOpExpBal)];
                                $totalExpense += abs($opExpBal);
                                $previousTotalExpense += abs($prevOpExpBal);
                            }
                            if ($financialExpenses) {
                                $finExpBal = accountGroupBalanceIncomeStatement($financialExpenses->id, request('start_date'), request('end_date'), 1, request('sister_concern'));
                                $prevFinExpBal = accountGroupBalanceIncomeStatement($financialExpenses->id, request('start_date'), request('end_date'), 2, request('sister_concern'));
                                $expenses[] = ['name' => $financialExpenses->name, 'id' => $financialExpenses->id, 'note' => $financialExpenses->note_no, 'current' => abs($finExpBal), 'previous' => abs($prevFinExpBal)];
                                $totalExpense += abs($finExpBal);
                                $previousTotalExpense += abs($prevFinExpBal);
                            }
                            if ($taxExpenses) {
                                $taxExpBal = accountGroupBalanceIncomeStatement($taxExpenses->id, request('start_date'), request('end_date'), 1, request('sister_concern'));
                                $prevTaxExpBal = accountGroupBalanceIncomeStatement($taxExpenses->id, request('start_date'), request('end_date'), 2, request('sister_concern'));
                                $expenses[] = ['name' => $taxExpenses->name, 'id' => $taxExpenses->id, 'note' => $taxExpenses->note_no, 'current' => abs($taxExpBal), 'previous' => abs($prevTaxExpBal)];
                                $totalExpense += abs($taxExpBal);
                                $previousTotalExpense += abs($prevTaxExpBal);
                            }

                            // Balancing Net Profit/Loss Variables
                            $currentNetProfit = $totalIncome - $totalExpense;
                            $prevNetProfit = $previousTotalIncome - $previousTotalExpense;

                            // We will not push them to the arrays, but keep them separate to render at the bottom.
                            // We still need to add them to the total to balance the bottom line.
                            if ($currentNetProfit > 0) {
                                $totalExpense += $currentNetProfit;
                            } elseif ($currentNetProfit < 0) {
                                $totalIncome += abs($currentNetProfit);
                            }

                            if ($prevNetProfit > 0) {
                                $previousTotalExpense += $prevNetProfit;
                            } elseif ($prevNetProfit < 0) {
                                $previousTotalIncome += abs($prevNetProfit);
                            }

                            $maxRows = max(count($expenses), count($incomes));
                        @endphp

                        <table class="table table-bordered w-100 mt-4">
                            <thead>
                                <tr class="bg-light">
                                    <th colspan="4" class="text-center" width="50%" style="font-size: 18px !important;">Expenditure</th>
                                    <th colspan="4" class="text-center" width="50%" style="font-size: 18px !important;">Income</th>
                                </tr>
                                <tr>
                                    <th class="text-center" width="20%">Particulars</th>
                                    <th class="text-center" width="5%">Note</th>
                                    <th class="text-center" width="12.5%">Amount (Tk)<br><small>{{ date('d-m-Y',strtotime(request('end_date'))) }}</small></th>
                                    <th class="text-center" width="12.5%">Amount (Tk)<br><small>{{ date('d-m-Y',strtotime('-1 day', strtotime(request('start_date')))) }}</small></th>
                                    
                                    <th class="text-center" width="20%">Particulars</th>
                                    <th class="text-center" width="5%">Note</th>
                                    <th class="text-center" width="12.5%">Amount (Tk)<br><small>{{ date('d-m-Y',strtotime(request('end_date'))) }}</small></th>
                                    <th class="text-center" width="12.5%">Amount (Tk)<br><small>{{ date('d-m-Y',strtotime('-1 day', strtotime(request('start_date')))) }}</small></th>
                                </tr>
                            </thead>
                            <tbody>
                                @for($i = 0; $i < $maxRows; $i++)
                                    @php
                                        $exp = $expenses[$i] ?? null;
                                        $inc = $incomes[$i] ?? null;
                                    @endphp
                                    <tr>
                                        {{-- Expense Side --}}
                                        @if($exp)
                                            <td class="text-left">
                                                @if(isset($exp['is_bold']) && $exp['is_bold'])
                                                    <b>{{ $exp['name'] }}</b>
                                                @elseif(isset($exp['id']))
                                                    <a href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$exp['id'],'sister_concern'=>request('sister_concern'),'search'=>'Search']) }}">{{ $exp['name'] }}</a>
                                                @else
                                                    {{ $exp['name'] }}
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $exp['note'] }}</td>
                                            <td class="text-right">
                                                @if(isset($exp['is_bold']) && $exp['is_bold'])
                                                    <b>{{ $exp['current'] != 0 ? number_format($exp['current'], 2) : '-' }}</b>
                                                @else
                                                    {{ $exp['current'] != 0 ? number_format($exp['current'], 2) : '-' }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if(isset($exp['is_bold']) && $exp['is_bold'])
                                                    <b>{{ $exp['previous'] != 0 ? number_format($exp['previous'], 2) : '-' }}</b>
                                                @else
                                                    {{ $exp['previous'] != 0 ? number_format($exp['previous'], 2) : '-' }}
                                                @endif
                                            </td>
                                        @else
                                            <td></td><td></td><td></td><td></td>
                                        @endif

                                        {{-- Income Side --}}
                                        @if($inc)
                                            <td class="text-left">
                                                @if(isset($inc['is_bold']) && $inc['is_bold'])
                                                    <b>{{ $inc['name'] }}</b>
                                                @elseif(isset($inc['id']))
                                                    <a href="{{ route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$inc['id'],'sister_concern'=>request('sister_concern'),'search'=>'Search']) }}">{{ $inc['name'] }}</a>
                                                @else
                                                    {{ $inc['name'] }}
                                                @endif
                                            </td>
                                            <td class="text-center">{{ $inc['note'] }}</td>
                                            <td class="text-right">
                                                @if(isset($inc['is_bold']) && $inc['is_bold'])
                                                    <b>{{ $inc['current'] != 0 ? number_format($inc['current'], 2) : '-' }}</b>
                                                @else
                                                    {{ $inc['current'] != 0 ? number_format($inc['current'], 2) : '-' }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if(isset($inc['is_bold']) && $inc['is_bold'])
                                                    <b>{{ $inc['previous'] != 0 ? number_format($inc['previous'], 2) : '-' }}</b>
                                                @else
                                                    {{ $inc['previous'] != 0 ? number_format($inc['previous'], 2) : '-' }}
                                                @endif
                                            </td>
                                        @else
                                            <td></td><td></td><td></td><td></td>
                                        @endif
                                    </tr>
                                @endfor
                                
                                {{-- Balancing Row for Net Profit / Net Loss --}}
                                <tr>
                                    {{-- Expense Side (Net Profit goes here to balance) --}}
                                    @if($currentNetProfit > 0 || $prevNetProfit > 0)
                                        <td class="text-left"><b>Net Profit (Transferred to BS)</b></td>
                                        <td class="text-center"></td>
                                        <td class="text-right"><b>{{ $currentNetProfit > 0 ? number_format($currentNetProfit, 2) : '-' }}</b></td>
                                        <td class="text-right"><b>{{ $prevNetProfit > 0 ? number_format($prevNetProfit, 2) : '-' }}</b></td>
                                    @else
                                        <td></td><td></td><td></td><td></td>
                                    @endif

                                    {{-- Income Side (Net Loss goes here to balance) --}}
                                    @if($currentNetProfit < 0 || $prevNetProfit < 0)
                                        <td class="text-left border-left"><b>Net Loss (Transferred to BS)</b></td>
                                        <td class="text-center"></td>
                                        <td class="text-right"><b>{{ $currentNetProfit < 0 ? '(' . number_format(abs($currentNetProfit), 2) . ')' : '-' }}</b></td>
                                        <td class="text-right"><b>{{ $prevNetProfit < 0 ? '(' . number_format(abs($prevNetProfit), 2) . ')' : '-' }}</b></td>
                                    @else
                                        <td class="border-left"></td><td></td><td></td><td></td>
                                    @endif
                                </tr>

                                {{-- Totals Row --}}
                                <tr>
                                    <th colspan="2" class="text-right">Total</th>
                                    <th class="text-right double-single-border">{{ number_format($totalExpense, 2) }}</th>
                                    <th class="text-right double-single-border">{{ number_format($previousTotalExpense, 2) }}</th>
                                    
                                    <th colspan="2" class="text-right border-left">Total</th>
                                    <th class="text-right double-single-border">{{ number_format($totalIncome, 2) }}</th>
                                    <th class="text-right double-single-border">{{ number_format($previousTotalIncome, 2) }}</th>
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
