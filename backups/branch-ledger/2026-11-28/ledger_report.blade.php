<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ledger Report</title>
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
            .table-bordered td, .table-bordered th {
                font-size: 20px !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Ledger_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info m-0">
                            <div class="col-12">
                                <h3 class="text-center m-0 mb-2 mt-2" style="font-size: 23px !important;"><b>General Ledger</b></h3>
                                @php
                                    $branchName = 'All Branches';
                                    if(request('sister_concern')) {
                                        $branch = \App\Models\BranchManagement\SisterConcern::find(request('sister_concern'));
                                        $branchName = $branch ? ($branch->display_name ?? $branch->name) : 'All Branches';
                                    }
                                @endphp
                                <h3 class="text-center m-0 mb-2" style="font-size: 19px !important;">Branch: {{ $branchName }}</h3>
                                <h3 class="text-center m-0 mb-2 mt-2" style="font-size: 19px !important;"><b>Date : {{ request('start_date') }} to {{ request('end_date') }}</b></h3>
                            </div>
                        </div>

                        @foreach($accountHeads as $accountHead)
                            <?php

                            $firstDebitTotal = 0;
                            $firstCreditTotal = 0;
                            //3=Asset,5=Liability,4=Equity/Capital

                            $debitOpening = $firstDebitTotal + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_debit : 0);
                            $creditOpening = $firstCreditTotal + (count($accountHead->openingTransactions) > 0 ? $accountHead->openingTransactions[0]->previous_credit : 0);

                            $rangeDebitOpening = 0;
                            $rangeCreditOpening = 0;

                            ?>

                            <table class="table table-bordered" style="">
                                    <tr>
                                        <th colspan="5" style="font-size: 20px">
                                            A/C Name :
                                            @if(($accountHead->accountGroup->name ?? '') != ($accountHead->name ?? ''))
                                                {{$accountHead->accountGroup->name ?? '' }}: {{ $accountHead->name ?? '' }}
                                            @else
                                                {{ $accountHead->name ?? '' }}
                                            @endif

                                            -({{ $accountHead->code ?? '' }})
                                        </th>
                                    </tr>
                                    <tr>
                                        <th width="10%" class="text-center">Date</th>
                                        <th width="10%" class="text-center">Voucher#</th>
                                        <th width="50%" class="text-left">Particular</th>
                                        <th width="15%" class="text-center">Debit</th>
                                        <th width="15%" class="text-center">Credit</th>
                                    </tr>
                                    @if(($debitOpening - $creditOpening) > 0)
                                        @php
                                            $rangeDebitOpening = $debitOpening - $creditOpening;
                                        @endphp
                                    @else
                                        @php
                                            $rangeCreditOpening = $debitOpening - $creditOpening;
                                        @endphp
                                    @endif
                                    @if(abs(($debitOpening - $creditOpening))  > 0)
                                        <tr>
                                            <td>{{ request('start_date') }}</td>
                                            <td></td>
                                            <td class="text-left">Opening Balance</td>
                                            @if(($debitOpening - $creditOpening) > 0)
                                                <td class="text-right">{{ number_format($debitOpening - $creditOpening,2) }}</td>
                                                <td class="text-right"></td>
                                            @else
                                                <td class="text-right"></td>
                                                <td class="text-right">{{ number_format(abs($debitOpening - $creditOpening),2) }}</td>
                                            @endif

                                        </tr>
                                    @endif
                                    <?php
                                        $accountHeadDebitTotal = 0;
                                        $accountHeadCreditTotal = 0;
                                        $transactions = $accountHead->transactions;
                                        $monthlyDebitTotal = 0;
                                        $monthlyCreditTotal = 0;
                                        $previousMonthDate = null;
                                    ?>
                                    @if(count($transactions) > 0)

                                        @foreach($transactions as $transaction)
                                            @php
                                                // Check if the date changed
                                                $dateChanged = $previousMonthDate !== \Carbon\Carbon::parse($transaction->date)->format('m-Y');
                                                if ($dateChanged && $previousMonthDate !== null) {
                                                    // Display the subtotal for the previous date
                                                    echo '<tr><td colspan="3" class="text-right">Monthly Total</td><td class="text-right">' . number_format($monthlyDebitTotal, 2) . '</td><td class="text-right">' . number_format($monthlyCreditTotal, 2) . '</td></tr>';
                                                    echo '<tr><td style="padding-bottom: 30px!important;" colspan="4" class="text-right"><b>Monthly Balance:</b></td><td style="padding-bottom: 30px!important;" class="text-right"><b>'.number_format(abs($monthlyDebitTotal - $monthlyCreditTotal),2).'</b></td></tr>';

                                                    // Reset the total amount for the new date
                                                    $monthlyDebitTotal = 0;
                                                    $monthlyCreditTotal = 0;
                                                }

                                                // Accumulate the total amount for the current date
                                                $previousMonthDate = \Carbon\Carbon::parse($transaction->date)->format('m-Y');
                                            @endphp
                                            <tr>
                                                <td> {{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                                <td>
                                                    @if($transaction->voucher)
                                                        <a target="_blank" href="{{ route('voucher.details', ['voucher' => $transaction->voucher->id, 'voucher_type' => $transaction->voucher->voucher_type]) }}">{{ $transaction->voucher_no }}</a>
                                                    @else
                                                        {{ $transaction->voucher_no }}
                                                    @endif
                                                </td>
                                                <td class="text-left">
                                                    @if($transaction->payeeDepositorHead)
                                                        {{ $transaction->payeeDepositorHead->name ?? '' }}
                                                    @endif
                                                    @if($transaction->notes)
                                                        - {{ $transaction->notes }}
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if(in_array($transaction->transaction_type,[1]))
                                                        <?php
                                                            $monthlyDebitTotal += $transaction->amount;
                                                            $accountHeadDebitTotal += $transaction->amount;
                                                        ?>
                                                        {{ number_format($transaction->amount,2) }}
                                                    @else

                                                    @endif

                                                </td>
                                                <td class="text-right">
                                                    @if(in_array($transaction->transaction_type,[2]))
                                                        <?php
                                                            $monthlyCreditTotal += $transaction->amount;
                                                            $accountHeadCreditTotal += $transaction->amount;
                                                        ?>
                                                        {{ number_format(abs($transaction->amount),2) }}
                                                    @else

                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach


                                    @if ($previousMonthDate !== null)
                                        <tr>
                                            <td colspan="3" class="text-right">Monthly Total:</td>
                                            <td class="text-right">{{ number_format($monthlyDebitTotal, 2) }}</td>
                                            <td class="text-right">{{ number_format(abs($monthlyCreditTotal), 2) }}</td>
                                        </tr>
                                        <tr>
                                             <td style="padding-bottom: 30px!important;" colspan="3" class="text-right"><b>Monthly Balance:</b></td>
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ ($monthlyDebitTotal - $monthlyCreditTotal) > 0 ? number_format($monthlyDebitTotal - $monthlyCreditTotal,2) : '' }}</b></td>
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ ($monthlyDebitTotal - $monthlyCreditTotal) < 0 ? number_format(abs($monthlyDebitTotal - $monthlyCreditTotal),2) : '' }}</b></td>
                                        </tr>
                                    @endif
                                    @endif
                                    <tr>
                                        <td colspan="3" class="text-right">Account Total:</td>
                                        <td class="text-right">{{ number_format(abs($accountHeadDebitTotal + $rangeDebitOpening),2) }}</td>
                                        <td class="text-right">{{ number_format(abs($accountHeadCreditTotal + $rangeCreditOpening),2) }}</td>
                                    </tr>
                                    <tr>
                                        <td style="padding-bottom: 30px!important;" colspan="3" class="text-right"><b>Account Balance:</b></td>
                                        @php
                                            $balance = ($accountHeadDebitTotal + $debitOpening) - ($accountHeadCreditTotal + $creditOpening);
                                        @endphp
                                        @if($balance == 0)
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ number_format(0,2) }}</b></td>
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ number_format(0,2) }}</b></td>
                                        @else
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ $balance > 0 ? number_format($balance,2) : '' }}</b></td>
                                            <td style="padding-bottom: 30px!important;" class="text-right"><b>{{ $balance < 0 ? number_format(abs($balance),2) : '' }}</b></td>
                                        @endif

                                    </tr>
                                </table>

                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
