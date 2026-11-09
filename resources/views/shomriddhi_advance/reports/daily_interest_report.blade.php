<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Accrued Interest Logs</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Daily_Interest_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="text-center mb-4 mt-3">
                            <h3 style="font-size: 20px; font-weight: bold; text-decoration: underline; margin-bottom: 15px;">Daily Accrued Interest Logs</h3>
                            @if(request('start_date') || request('end_date'))
                                <h5>Period: {{ request('start_date') ?? 'Start' }} to {{ request('end_date') ?? 'End' }}</h5>
                            @endif
                            @if(request('status'))
                                <h5>Status: {{ request('status') }}</h5>
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

                        <table class="table table-bordered table-striped table-hover">
                            <thead class="bg-light">
                                <tr>
                                    <th width="5%">SL</th>
                                    <th>Date</th>
                                    <th>Customer</th>
                                    <th class="text-right">Principal Bal.</th>
                                    <th class="text-center">Rate</th>
                                    <th class="text-right">Earned Amount</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($logs as $i => $log)
                                    <tr>
                                        <td>{{ $i + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($log->date)->format('d-M-Y') }}</td>
                                        <td class="text-left">
                                            {{ $log->customer ? $log->customer->name : 'N/A' }}
                                            <br><small class="text-muted">{{ $log->customer->member_no ?? '' }}</small>
                                        </td>
                                        <td class="text-right">৳ {{ number_format($log->principal_balance, 2) }}</td>
                                        <td class="text-center">{{ $log->interest_rate }}%</td>
                                        <td class="text-right text-success font-weight-bold">৳ {{ number_format($log->earned_interest, 4) }}</td>
                                        <td class="text-center">
                                            @if($log->status == 'Pending')
                                                <span class="text-warning font-weight-bold">Pending</span>
                                            @else
                                                <span class="text-success font-weight-bold">Capitalized</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-3">No interest logs found for this filter.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="bg-light font-weight-bold">
                                <tr>
                                    <td colspan="5" class="text-right">Total Earned Interest:</td>
                                    <td class="text-right text-success">৳ {{ number_format($logs->sum('earned_interest'), 4) }}</td>
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
