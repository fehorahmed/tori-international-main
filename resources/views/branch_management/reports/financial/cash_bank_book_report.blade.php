<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cash/Bank Book Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Cash_Bank_Book_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                    <div class="row header-common-info">
                        <div class="col-12">
                            <h3 class="text-center" style="font-size: 25px"><u><b>Cash/ Bank Book</b></u></h3>
                            <h3 class="text-center" style="font-size: 25px">Account Name: {{ $selectAccount->name }} - {{ $selectAccount->code }}</h3>
                            <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                        </div>
                    </div>
                    <table id="table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">Date</th>
                                <th class="text-center">Particulars</th>
                                <th class="text-center">Voucher No</th>
                                <th class="text-center">Party</th>
                                <th class="text-center">Cheque No.</th>
                                <th class="text-center">Break Down</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">Receipt</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-center">{{ request('start_date') }}</td>
                                <td class="text-left" colspan="5">Opening Balance</td>
                                <td></td>
                                <td></td>
                                <td class="text-right">{{ number_format($openingBalance,2) }}</td>
                            </tr>
                            @foreach($transactions as $key => $transaction)
                                @php

                                    if($transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT){
                                            $openingBalance += $transaction->amount;
                                    }elseif($transaction->transaction_type == \App\Enumeration\TransactionType::$CREDIT){
                                            $openingBalance -= $transaction->amount;
                                    }else{
                                            $openingBalance += 0;
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                    <td class="text-left">
                                        @if($transaction->sourceAccountHead)
                                            {{ $transaction->sourceAccountHead->name ?? '' }} to {{ $transaction->accountHead->name ?? '' }} (Transferred In)
                                        @endif
                                        @if($transaction->targetAccountHead)
                                                {{ $transaction->accountHead->name ?? '' }} to {{ $transaction->targetAccountHead->name ?? '' }} (Transferred Out)
                                        @endif
                                        {{ $transaction->notes }}
                                    </td>
                                    <td>
                                        <a target="_blank" href="{{ route('voucher.details', ['voucher' => $transaction->voucher_id]) }}">
                                            {{ $transaction->voucher_no }}
                                        </a>
                                    </td>
                                    <td class="text-left">{{ $transaction->payeeDepositorHead->name ?? $transaction->party_name }}</td>
                                    <td class="text-center">{{ $transaction->cheque_no }}</td>
                                    <td class="text-center">
                                        @php
                                            $mainTransactions = $transaction->voucher->transactions ? $transaction->voucher->transactions
                                                   ->whereNull('source_account_head_id')
                                                   ->where('except_edit',0)
                                                    ->whereNull('parent_account_head_id')->where('account_head_id','!=',$transaction->payment_account_head_id) : [];

                                            $otherDeductionTransactions = $transaction->voucher->transactions ? $transaction->voucher->transactions->where('other_deduct',1) : [];
                                        $totalMainTransactionAmount = 0;
                                        @endphp
                                        @foreach($mainTransactions as $key => $mainTransaction)
                                            @php
                                                $mainTransactionAmount = $mainTransaction->amount + getParentBaseAmount($mainTransaction);
                                                $totalMainTransactionAmount += $mainTransactionAmount;
                                            @endphp
                                            <div
                                                class="text-right">{{ number_format($mainTransactionAmount,2)}}</div>
                                            @if(count($mainTransactions) > 1)
                                                @if(!$loop->last)
                                                    <br>
                                                @endif
                                            @endif
                                        @endforeach
                                        <hr style="border-top: 1px solid #000;margin:0">
                                        <div class="text-right">Sub Total:
                                            <b>{{number_format($totalMainTransactionAmount,2)}}</b></div>
                                        @if(count($otherDeductionTransactions) > 0)
                                            <br>
                                            @foreach($otherDeductionTransactions as $key => $otherDeductionTransaction)
                                                <div
                                                    class="text-right">{{ number_format($otherDeductionTransaction->amount ,2)}}</div>
                                                @if(!$loop->last)
                                                    <br>
                                                @endif
                                            @endforeach
                                                <hr style="border-top: 1px solid #000;margin:0">
                                            <div class="text-right">Deduction
                                                Total:<b> {{number_format($otherDeductionTransactions->sum('amount'),2)}}</b>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-right">
                                        @if($transaction->transaction_type == \App\Enumeration\TransactionType::$CREDIT)
                                            {{ number_format($transaction->amount,2) }}
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        @if($transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT)
                                            {{ number_format($transaction->amount,2) }}
                                        @endif
                                    </td>
                                    <td class="text-right">{{ wrapNegative($openingBalance) }}</td>
                                </tr>
                            @endforeach

                            <tr>
                                <th colspan="6" class="text-right">Total</th>
                                <th colspan=""
                                    class="text-right">{{ number_format($transactions->where('transaction_type',\App\Enumeration\TransactionType::$CREDIT)->sum('amount'),2) }}</th>
                                <th colspan=""
                                    class="text-right">{{ number_format($transactions->where('transaction_type',\App\Enumeration\TransactionType::$DEBIT)->sum('amount'),2) }}</th>
                                <th colspan="" class="text-right">{{ wrapNegative($openingBalance) }}</th>
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
