<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contractor Ledger Report</title>
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Contractor_Ledger_' . date('d_m_Y')])
                    
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="header-common-info mb-3">
                            <div>
                                <h3 class="text-center" style="font-size: 25px"><u><b>Contractor Ledger</b></u></h3>
                                <h4 class="text-center" style="font-size: 20px">Contractor Name: {{ $contractor->name }}</h4>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse($start_date)->format('d-m-Y') }} to {{ \Carbon\Carbon::parse($end_date)->format('d-m-Y') }}</h5>
                            </div>
                        </div>
                        
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center">Date</th>
                                    <th>Voucher No</th>
                                    <th>Particulars</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Credit</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-right"><strong>Opening Balance</strong></td>
                                    <td class="text-right"><strong>{{ number_format($openingBalance, 2) }}</strong></td>
                                </tr>
                                @php
                                    $balance = $openingBalance;
                                    $totalDebit = 0;
                                    $totalCredit = 0;
                                @endphp
                                @foreach($transactions as $transaction)
                                    @php
                                        if($transaction->transaction_type == 1) { // Debit (Reduces Payable)
                                            $balance -= $transaction->amount;
                                            $totalDebit += $transaction->amount;
                                        } else { // Credit (Increases Payable)
                                            $balance += $transaction->amount;
                                            $totalCredit += $transaction->amount;
                                        }
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                        <td>
                                            @if($transaction->show_url)
                                                <a href="{{ $transaction->show_url }}" target="_blank" class="text-primary text-decoration-none" title="View Details">
                                                    {{ $transaction->voucher_no }}
                                                </a>
                                            @else
                                                {{ $transaction->voucher_no }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($transaction->show_url)
                                                <a href="{{ $transaction->show_url }}" target="_blank" class="text-primary text-decoration-none" title="View Details">
                                                    {{ $transaction->notes }}
                                                </a>
                                            @else
                                                {{ $transaction->notes }}
                                            @endif
                                        </td>
                                        <td class="text-right">{{ $transaction->transaction_type == 1 ? number_format($transaction->amount, 2) : '-' }}</td>
                                        <td class="text-right">{{ $transaction->transaction_type == 2 ? number_format($transaction->amount, 2) : '-' }}</td>
                                        <td class="text-right">{{ number_format($balance, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-right">Total</th>
                                    <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
                                    <th class="text-right">{{ number_format($balance, 2) }}</th>
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
