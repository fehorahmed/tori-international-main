<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Ledger Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 13px !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Customer_Ledger_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                @if(isset($selectedCustomerInfo) && $selectedCustomerInfo)
                                    <h2 class="m-0 mb-1" style="font-size: 22px !important;"><b>{{ $selectedCustomerInfo->name }} - ({{ $selectedCustomerInfo->member_no ?? '' }})</b></h2>
                                @endif
                                <h3 class="m-0 mb-2 mt-2" style="font-size: 19px !important;"><b>Ledger for : {{ request('start_date') }} to {{ request('end_date') }}</b></h3>
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
                                        <th colspan="6" style="font-size: 20px">
                                            A/C Name :
                                            @if(($accountHead->accountGroup->name ?? '') != ($accountHead->name ?? ''))
                                                {{$accountHead->accountGroup->name ?? '' }}: {{ $accountHead->name ?? '' }}
                                            @else
                                                {{ $accountHead->name ?? '' }}
                                            @endif

                                            -({{ $accountHead->supplier->member_no ?? '' }})
                                        </th>
                                    </tr>
                                    <tr>
                                        <th width="5%" class="text-center">SL</th>
                                        <th width="10%" class="text-center">Date</th>
                                        <th width="35%" class="text-left">Transaction Details</th>
                                        <th width="15%" class="text-right">Amount Out</th>
                                        <th width="15%" class="text-right">Amount In</th>
                                        <th width="15%" class="text-right">Balance</th>
                                    </tr>

                                    @php
                                        $openingBalance = $debitOpening - $creditOpening;
                                        $runningBalance = $openingBalance;
                                        $totalAmountOut = 0;
                                        $totalAmountIn = 0;
                                        $sl = 1;
                                    @endphp

                                    <tr>
                                        <td class="text-center">{{ $sl++ }}</td>
                                        <td class="text-center">{{ request('start_date') }}</td>
                                        <td class="text-left">Opening Balance</td>
                                        <td class="text-right">
                                            @if($openingBalance < 0)
                                                {{ number_format(abs($openingBalance), 2) }}
                                                @php $totalAmountOut += abs($openingBalance); @endphp
                                            @else
                                                0
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if($openingBalance > 0)
                                                {{ number_format($openingBalance, 2) }}
                                                @php $totalAmountIn += $openingBalance; @endphp
                                            @else
                                                0
                                            @endif
                                        </td>
                                        <td class="text-right">{{ number_format($runningBalance, 2) }}</td>
                                    </tr>

                                    @foreach($accountHead->transactions as $transaction)
                                        @php
                                            $isCredit = in_array($transaction->transaction_type, [2]);
                                            $isDebit = in_array($transaction->transaction_type, [1]);
                                            $amount = abs($transaction->amount);

                                            if ($isDebit) {
                                                $runningBalance += $amount;
                                                $totalAmountIn += $amount;
                                            } elseif ($isCredit) {
                                                $runningBalance -= $amount;
                                                $totalAmountOut += $amount;
                                            }

                                            $partyName = $transaction->payeeDepositorHead ? $transaction->payeeDepositorHead->name : '';
                                            $notes = $transaction->notes ?? '';
                                            
                                            if ($partyName) {
                                                $cleanName = trim(preg_replace('/\s*\([^)]*\)/', '', $partyName));
                                                if ($cleanName) {
                                                    // Remove the name with its bracket suffix and any preceding dash
                                                    $notes = preg_replace('/[-—\s]*' . preg_quote($cleanName, '/') . '\s*\([^)]*\)/i', '', $notes);
                                                    // Remove just the clean name if it's still there
                                                    $notes = preg_replace('/[-—\s]*' . preg_quote($cleanName, '/') . '/i', '', $notes);
                                                }
                                                // Also try exact party name removal just in case
                                                $notes = str_ireplace($partyName, '', $notes);
                                            }
                                            
                                            $notes = trim(trim($notes, ' -—'), ' ');
                                            
                                            $particulars = '';
                                            if($partyName) {
                                                $particulars .= $partyName;
                                            }
                                            if($notes) {
                                                $particulars .= ($particulars ? ' - ' : '') . $notes;
                                            }
                                            if(!$particulars && $transaction->voucher) {
                                                $particulars = 'Voucher: ' . $transaction->voucher_no;
                                            }
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $sl++ }}</td>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                            <td class="text-left">
                                                @if($transaction->voucher)
                                                    <a target="_blank" href="{{ route('voucher.details', ['voucher' => $transaction->voucher->id, 'voucher_type' => $transaction->voucher->voucher_type]) }}">{{ $particulars }}</a>
                                                @else
                                                    {{ $particulars }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                {{ $isCredit ? number_format($amount, 2) : '0' }}
                                            </td>
                                            <td class="text-right">
                                                {{ $isDebit ? number_format($amount, 2) : '0' }}
                                            </td>
                                            <td class="text-right">{{ number_format($runningBalance, 2) }}</td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <th colspan="3" class="text-right">Total :</th>
                                        <th class="text-right">{{ number_format($totalAmountOut, 2) }}</th>
                                        <th class="text-right">{{ number_format($totalAmountIn, 2) }}</th>
                                        <th class="text-right">{{ number_format($runningBalance, 2) }}</th>
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
