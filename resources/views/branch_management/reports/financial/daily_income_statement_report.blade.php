<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Income Statement</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            padding: 0.3rem !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Daily_Income_Statement_' . date('d_m_Y')])
                    
                    <div class="table-responsive-sm" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info print-heading">
                            <div class="col-12">
                                <p class="text-center m-0" style="font-size: 20px !important;">
                                    <b>DAILY INCOME STATEMENT (MANAGEMENT P&L)</b> </p>
                                <p class="text-center" style="font-size: 16px !important;">Branch: {{ $branch->name ?? 'All Branches' }}</p>
                                <p class="text-center" style="font-size: 16px !important;">
                                    Period: {{ date('d-M-Y',strtotime(request('start_date'))) }} to {{ date('d-M-Y',strtotime(request('end_date'))) }}
                                </p>
                            </div>
                        </div>

                        @php
                            $incomes = [];
                            $expenses = [];
                            
                            if ($operatingRevenue) {
                                $link = route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$operatingRevenue->id,'sister_concern'=>request('sister_concern')]);
                                $incomes[] = ['name' => '<a href="'.$link.'">' . $operatingRevenue->name . '</a>', 'amount' => $operatingRevBal];
                            }
                            if ($otherIncome) {
                                $link = route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$otherIncome->id,'sister_concern'=>request('sister_concern')]);
                                $incomes[] = ['name' => '<a href="'.$link.'">' . $otherIncome->name . '</a>', 'amount' => $otherIncBal];
                            }

                            $expenses[] = ['name' => '<b>Actual Operating Expenses</b>', 'amount' => null, 'is_bold' => true];
                            if ($costOfGoodsSold) {
                                $link = route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$costOfGoodsSold->id,'sister_concern'=>request('sister_concern')]);
                                $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$link.'">' . $costOfGoodsSold->name . '</a>', 'amount' => $cogsBal];
                            }
                            if ($operatingExpenses) {
                                $link = route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$operatingExpenses->id,'sister_concern'=>request('sister_concern')]);
                                $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$link.'">' . $operatingExpenses->name . '</a>', 'amount' => $opExpBal];
                            }
                            if ($financialExpenses) {
                                $link = route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$financialExpenses->id,'sister_concern'=>request('sister_concern')]);
                                $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$link.'">' . $financialExpenses->name . '</a>', 'amount' => $finExpBal];
                            }
                            if ($taxExpenses) {
                                $link = route('branch_report.trial_balance',['start_date'=>request('start_date'),'end_date'=>request('end_date'),'account_group_id'=>$taxExpenses->id,'sister_concern'=>request('sister_concern')]);
                                $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;<a href="'.$link.'">' . $taxExpenses->name . '</a>', 'amount' => $taxExpBal];
                            }

                            $expenses[] = ['name' => '<b>Allocated Fixed Costs ('.$daysCount.' days)</b>', 'amount' => null, 'is_bold' => true];
                            $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;Office Rent', 'amount' => $allocatedRent];
                            $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;Electricity Bill', 'amount' => $allocatedElectricity];
                            $expenses[] = ['name' => '&nbsp;&nbsp;&nbsp;&nbsp;Employee Salary', 'amount' => $allocatedSalary];

                            // To properly format the T-table bottom lines, we adjust total variables here.
                            $displayTotalExpense = $totalExpense;
                            $displayTotalIncome = $totalIncome;

                            if ($netProfit > 0) {
                                $displayTotalExpense += $netProfit;
                            } elseif ($netProfit < 0) {
                                $displayTotalIncome += abs($netProfit);
                            }

                            $maxRows = max(count($expenses), count($incomes));
                        @endphp

                        <table class="table table-bordered mt-4 w-100">
                            <thead>
                                <tr class="bg-light">
                                    <th colspan="2" class="text-center" width="50%" style="font-size: 18px !important;">Expenditure</th>
                                    <th colspan="2" class="text-center border-left" width="50%" style="font-size: 18px !important;">Income</th>
                                </tr>
                                <tr>
                                    <th class="text-center" width="35%">Particulars</th>
                                    <th class="text-center" width="15%">Amount (Tk)</th>
                                    
                                    <th class="text-center border-left" width="35%">Particulars</th>
                                    <th class="text-center" width="15%">Amount (Tk)</th>
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
                                            <td class="text-left">{!! $exp['name'] !!}</td>
                                            <td class="text-right">
                                                @if(isset($exp['is_bold']))
                                                    
                                                @else
                                                    {{ $exp['amount'] != 0 ? number_format($exp['amount'], 2) : '-' }}
                                                @endif
                                            </td>
                                        @else
                                            <td></td><td></td>
                                        @endif

                                        {{-- Income Side --}}
                                        @if($inc)
                                            <td class="text-left border-left">{!! $inc['name'] !!}</td>
                                            <td class="text-right">
                                                @if(isset($inc['is_bold']))
                                                    
                                                @else
                                                    {{ $inc['amount'] != 0 ? number_format($inc['amount'], 2) : '-' }}
                                                @endif
                                            </td>
                                        @else
                                            <td class="border-left"></td><td></td>
                                        @endif
                                    </tr>
                                @endfor
                                
                                {{-- Balancing Row --}}
                                <tr>
                                    @if($netProfit > 0)
                                        <td class="text-left"><b>Estimated Net Profit</b></td>
                                        <td class="text-right"><b>{{ number_format($netProfit, 2) }}</b></td>
                                        <td class="border-left"></td><td></td>
                                    @elseif($netProfit < 0)
                                        <td></td><td></td>
                                        <td class="text-left border-left"><b>Estimated Net Loss</b></td>
                                        <td class="text-right"><b>{{ '(' . number_format(abs($netProfit), 2) . ')' }}</b></td>
                                    @else
                                        <td></td><td></td><td class="border-left"></td><td></td>
                                    @endif
                                </tr>

                                {{-- Totals Row --}}
                                <tr>
                                    <th class="text-right">Total</th>
                                    <th class="text-right double-single-border">{{ number_format($displayTotalExpense, 2) }}</th>
                                    
                                    <th class="text-right border-left">Total</th>
                                    <th class="text-right double-single-border">{{ number_format($displayTotalIncome, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="row mt-5">
                            <div class="col-12 text-center text-muted">
                                <small><i>* Note: The Operating Expenses fetched from the ledger in section (B) currently include all recorded expenses. To strictly prevent double-counting, you may need to adjust the database queries to explicitly exclude the specific account heads corresponding to Rent, Electricity, and Salary.</i></small>
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
