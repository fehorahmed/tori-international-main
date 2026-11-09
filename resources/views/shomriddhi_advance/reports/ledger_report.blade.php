<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Passbook (Ledger)</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 16px !important;
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
            font-size: 14px;
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
                font-size: 18px !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Customer_Passbook_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="text-center mb-4 mt-3">
                            <h3 style="font-size: 20px; font-weight: bold; text-decoration: underline; margin-bottom: 15px;">Customer Passbook (Ledger)</h3>
                            <h4>{{ $customer->name }}</h4>
                            <p class="mb-1 text-muted">Member No: <b>{{ $customer->member_no ?? 'N/A' }}</b> | Mobile: <b>{{ $customer->mobile_no ?? 'N/A' }}</b></p>
                            <p class="mb-0 text-info" style="font-size: 1.1em;">
                                Total Deposited Balance: <b>৳ {{ number_format($shomriddhiAccount->principal_balance ?? 0, 2) }}</b> | 
                                Pending Accrued Interest: 
                                <a href="{{ route('shomriddhi-advance.reports.daily_interest', ['status' => 'Pending', 'customer_id' => $customer->id, 'search' => 1]) }}" class="text-warning" style="text-decoration: underline;">
                                    <b>৳ {{ number_format($shomriddhiAccount->accrued_interest ?? 0, 4) }}</b>
                                </a>
                            </p>
                            <hr>
                        </div>

                        <table class="table table-bordered table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Particulars / Note</th>
                                    <th>Voucher No</th>
                                    <th class="text-right text-success">Deposit (Credit)</th>
                                    <th class="text-right text-danger">Withdraw (Debit)</th>
                                    <th class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $runningBalance = $openingBalance ?? 0;
                                @endphp

                                <!-- Opening Balance Row -->
                                <tr class="bg-light font-weight-bold">
                                    <td>-</td>
                                    <td colspan="4">Opening Balance</td>
                                    <td class="text-right text-primary">৳ {{ number_format($runningBalance, 2) }}</td>
                                </tr>

                                @forelse($transactions as $txn)
                                    @php
                                        $credit = in_array($txn->type, ['deposit', 'interest_capitalization']) ? $txn->amount : 0;
                                        $debit = $txn->type == 'withdraw' ? $txn->amount : 0;
                                        $runningBalance += ($credit - $debit);
                                    @endphp
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                                        <td class="text-left">{{ $txn->note ?? 'Savings Transaction' }}</td>
                                        <td>-</td>
                                        <td class="text-right text-success">{{ $credit > 0 ? number_format($credit, 2) : '-' }}</td>
                                        <td class="text-right text-danger">{{ $debit > 0 ? number_format($debit, 2) : '-' }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No transactions found for the selected period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($transactions->count() > 0)
                                <tfoot class="bg-light font-weight-bold">
                                    <tr>
                                        <td colspan="3" class="text-right">Closing Balance</td>
                                        <td colspan="3" class="text-center text-primary" style="font-size: 1.2em;">৳ {{ number_format($runningBalance, 2) }}</td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
