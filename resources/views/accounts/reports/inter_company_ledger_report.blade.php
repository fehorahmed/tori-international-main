<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inter-Company Ledger Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Inter_Company_Ledger_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        @if($selectedBranch)
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Inter-Company Ledger</b></u></h3>
                                <h3 class="text-center" style="font-size: 20px">Inter-Company Account : {{ $selectedBranch->name }} @if($selectedBranch->code)-({{ $selectedBranch->code }})@endif</h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        @php
                            $rangeDebitOpening = 0;
                            $rangeCreditOpening = 0;
                            
                            if ($openingBalance > 0) {
                                $rangeDebitOpening = $openingBalance;
                            } else {
                                $rangeCreditOpening = abs($openingBalance);
                            }
                        @endphp

                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th width="10%" class="text-center">Date</th>
                                    <th width="10%" class="text-center">Voucher#</th>
                                    <th width="50%" class="text-center">Particulars</th>
                                    <th width="15%" class="text-center">Debit</th>
                                    <th width="15%" class="text-center">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(abs($rangeDebitOpening - $rangeCreditOpening) > 0)
                                    <tr>
                                        <td class="text-center">{{ request('start_date') }}</td>
                                        <td></td>
                                        <td class="text-left">Opening Balance</td>
                                        @if($rangeDebitOpening > 0)
                                            <td class="text-right">{{ number_format($rangeDebitOpening, 2) }}</td>
                                            <td class="text-right"></td>
                                        @else
                                            <td class="text-right"></td>
                                            <td class="text-right">{{ number_format($rangeCreditOpening, 2) }}</td>
                                        @endif
                                    </tr>
                                @endif

                                <?php
                                    $accountHeadDebitTotal = 0;
                                    $accountHeadCreditTotal = 0;
                                    
                                    $monthlyDebitTotal = 0;
                                    $monthlyCreditTotal = 0;
                                    $previousMonthDate = null;
                                ?>

                                @if(count($transactions) > 0)
                                    @foreach($transactions as $transaction)
                                        @php
                                            $isReceivable = $receivableHead && $transaction->account_head_id == $receivableHead->id;
                                            $isPayable = $payableHead && $transaction->account_head_id == $payableHead->id;
                                            
                                            $debitAmount = 0;
                                            $creditAmount = 0;

                                            if ($isReceivable) {
                                                if ($transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT) {
                                                    $debitAmount = $transaction->amount;
                                                } else {
                                                    $creditAmount = $transaction->amount;
                                                }
                                            } elseif ($isPayable) {
                                                if ($transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT) {
                                                    $debitAmount = $transaction->amount;
                                                } else {
                                                    $creditAmount = $transaction->amount;
                                                }
                                            }

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
                                            <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                            <td class="text-center">
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
                                                @if($debitAmount > 0)
                                                    <?php
                                                        $monthlyDebitTotal += $debitAmount;
                                                        $accountHeadDebitTotal += $debitAmount;
                                                    ?>
                                                    {{ number_format($debitAmount, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($creditAmount > 0)
                                                    <?php
                                                        $monthlyCreditTotal += $creditAmount;
                                                        $accountHeadCreditTotal += $creditAmount;
                                                    ?>
                                                    {{ number_format($creditAmount, 2) }}
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
                                    <th colspan="3" class="text-right">Account Total:</th>
                                    <th class="text-right">{{ number_format(abs($accountHeadDebitTotal + $rangeDebitOpening), 2) }}</th>
                                    <th class="text-right">{{ number_format(abs($accountHeadCreditTotal + $rangeCreditOpening), 2) }}</th>
                                </tr>
                                <tr>
                                    <th style="padding-bottom: 30px!important;" colspan="3" class="text-right"><b>Account Balance:</b></th>
                                    @php
                                        $balance = ($accountHeadDebitTotal + $rangeDebitOpening) - ($accountHeadCreditTotal + $rangeCreditOpening);
                                    @endphp
                                    @if($balance == 0)
                                        <th style="padding-bottom: 30px!important;" class="text-right"><b>{{ number_format(0,2) }}</b></th>
                                        <th style="padding-bottom: 30px!important;" class="text-right"><b>{{ number_format(0,2) }}</b></th>
                                    @else
                                        <th style="padding-bottom: 30px!important;" class="text-right"><b>{{ $balance > 0 ? number_format($balance,2) : '' }}</b></th>
                                        <th style="padding-bottom: 30px!important;" class="text-right"><b>{{ $balance < 0 ? number_format(abs($balance),2) : '' }}</b></th>
                                    @endif
                                </tr>
                            </tbody>
                        </table>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
