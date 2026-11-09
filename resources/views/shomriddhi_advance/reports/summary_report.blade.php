<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Shomriddhi Summary Report</title>
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
        .info-box {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin-bottom: 20px;
        }
        .info-box-number {
            font-size: 1.3em;
            font-weight: bold;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Shomriddhi_Summary_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="text-center mb-4 mt-3">
                            <h3 style="font-size: 20px; font-weight: bold; text-decoration: underline; margin-bottom: 15px;">Shomriddhi Summary Report</h3>
                            @if(request('start_date') || request('end_date'))
                                <h5>Period: {{ request('start_date') ?? 'Start' }} to {{ request('end_date') ?? 'End' }}</h5>
                            @else
                                <h5>Period: All Time</h5>
                            @endif
                        </div>

                        {{-- Truncated Warning --}}
                        @if($truncated)
                            <div class="alert alert-warning text-center extra-remove">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Large Dataset!</strong> Showing <b>{{ number_format($rowLimit) }}</b> of <b>{{ number_format($count) }}</b> records.
                                Please narrow your date range to see all records.
                            </div>
                        @endif

                        {{-- Summary Cards --}}
                        <div class="row mb-4">
                            <div class="col-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-success"><b>Total Deposits</b></span><br>
                                        <span class="info-box-number text-success">৳ {{ number_format($totalDeposit, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-danger"><b>Total Withdrawals</b></span><br>
                                        <span class="info-box-number text-danger">৳ {{ number_format($totalWithdraw, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="info-box bg-light">
                                    <div class="info-box-content">
                                        <span class="info-box-text text-info"><b>Total Interest</b></span><br>
                                        <span class="info-box-number text-info">৳ {{ number_format($totalInterest, 2) }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Table --}}
                        <table class="table table-bordered table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th>SL</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th>Type</th>
                                    <th class="text-right">Amount</th>
                                    <th>Note</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $i => $txn)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($txn->date)->format('d-M-Y') }}</td>
                                        <td class="text-left">
                                            {{ $txn->customer ? $txn->customer->name : 'N/A' }}
                                            <br><small class="text-muted">{{ $txn->customer->member_no ?? '' }}</small>
                                        </td>
                                        <td>
                                            @if($txn->type == 'deposit')
                                                <span class="text-success font-weight-bold">Deposit</span>
                                            @elseif($txn->type == 'interest_capitalization')
                                                <span class="text-info font-weight-bold">Interest</span>
                                            @else
                                                <span class="text-danger font-weight-bold">Withdraw</span>
                                            @endif
                                        </td>
                                        <td class="text-right font-weight-bold">৳ {{ number_format($txn->amount, 2) }}</td>
                                        <td class="text-left">{{ $txn->note ?? '-' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">No approved transactions found for this period.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="4" class="text-right">Page Total (Deposit):</td>
                                    <td class="text-right text-success">৳ {{ number_format($transactions->where('type','deposit')->sum('amount'), 2) }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right">Page Total (Withdraw):</td>
                                    <td class="text-right text-danger">৳ {{ number_format($transactions->where('type','withdraw')->sum('amount'), 2) }}</td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <td colspan="4" class="text-right">Page Total (Interest):</td>
                                    <td class="text-right text-info">৳ {{ number_format($transactions->where('type','interest_capitalization')->sum('amount'), 2) }}</td>
                                    <td></td>
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
