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
                            <h3 class="text-center" style="font-size: 25px"><u><b>Cash Transaction Details</b></u></h3>
                            <h3 class="text-center" style="font-size: 25px">Account Name: {{ $selectAccount->name }} - {{ $selectAccount->code }}</h3>
                            <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                        </div>
                    </div>
                    <table id="table" class="table table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center">SL</th>
                                <th class="text-center">Date</th>
                                <th class="text-left">Transaction Details</th>
                                <th class="text-center">Amount Out (Pay. Exp.)</th>
                                <th class="text-center">Amount In (Sales, Orders)</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $initialOpeningBalance = $openingBalance;
                            @endphp
                            <tr>
                                <td class="text-center">1</td>
                                <td class="text-center">{{ request('start_date') }}</td>
                                <td class="text-left">Opening Balance</td>
                                <td class="text-right">
                                    @if($initialOpeningBalance < 0)
                                        {{ number_format(abs($initialOpeningBalance), 2) }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($initialOpeningBalance > 0)
                                        {{ number_format($initialOpeningBalance, 2) }}
                                    @else
                                        0
                                    @endif
                                </td>
                                <td class="text-right">{{ wrapNegative($initialOpeningBalance) }}</td>
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
                                    <td class="text-center">{{ $loop->iteration + 1 }}</td>
                                    <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                    <td class="text-left">
                                        @if($transaction->sourceAccountHead)
                                            {{ $transaction->sourceAccountHead->name ?? '' }} to {{ $transaction->accountHead->name ?? '' }} (Transferred In)
                                        @endif
                                        @if($transaction->targetAccountHead)
                                                {{ $transaction->accountHead->name ?? '' }} to {{ $transaction->targetAccountHead->name ?? '' }} (Transferred Out)
                                        @endif
                                        {{ $transaction->notes }}
                                        @php
                                            $partyName = $transaction->payeeDepositorHead->name ?? $transaction->party_name;
                                            if (isset($transaction->payeeDepositorHead->supplier) && $transaction->payeeDepositorHead->supplier->member_no) {
                                                $partyName .= ' - ' . $transaction->payeeDepositorHead->supplier->member_no;
                                            }
                                        @endphp
                                        @if($partyName && !str_contains($transaction->notes ?? '', $partyName))
                                           - {{ $partyName }}
                                        @endif
                                        
                                        @if($transaction->voucher_no)
                                            <br><small><a target="_blank" href="{{ route('voucher.details', ['voucher' => $transaction->voucher_id]) }}"><strong>Voucher No:</strong> {{ $transaction->voucher_no }}</a></small>
                                        @endif
                                    </td>
                                    
                                    <td class="text-right">
                                        @if($transaction->transaction_type == \App\Enumeration\TransactionType::$CREDIT)
                                            {{ number_format($transaction->amount,2) }}
                                        @else
                                            0
                                        @endif
                                    </td>

                                    <td class="text-right">
                                        @if($transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT)
                                            {{ number_format($transaction->amount,2) }}
                                        @else
                                            0
                                        @endif
                                    </td>
                                    <td class="text-right">{{ wrapNegative($openingBalance) }}</td>
                                </tr>
                            @endforeach

                            @php
                                $totalCredit = $transactions->where('transaction_type',\App\Enumeration\TransactionType::$CREDIT)->sum('amount') + ($initialOpeningBalance < 0 ? abs($initialOpeningBalance) : 0);
                                $totalDebit = $transactions->where('transaction_type',\App\Enumeration\TransactionType::$DEBIT)->sum('amount') + ($initialOpeningBalance > 0 ? $initialOpeningBalance : 0);
                            @endphp
                            <tr>
                                <th colspan="3" class="text-right">Total :</th>
                                <th class="text-right">{{ number_format($totalCredit,2) }}</th>
                                <th class="text-right">{{ number_format($totalDebit,2) }}</th>
                                <th class="text-right">{{ wrapNegative($openingBalance) }}</th>
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
