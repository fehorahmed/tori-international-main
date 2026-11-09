<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
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
                font-size: 16px !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Loan_Portfolio_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                <h3 class="text-center" style="font-size: 20px; margin-top: 10px; margin-bottom: 5px;"><u><b>{{ $pageTitle }}</b></u></h3>
                                <h5 class="text-center m-0" style="font-size: 16px"><b>Branch:</b> {{ auth()->user()->sisterConcern->name ?? auth()->user()->activeSisterConcern->name ?? '' }}</h5>
                            </div>
                        </div>

                        <div class="row mt-4 mb-4">
                            <div class="col-12">
                                <table class="table table-bordered mb-0">
                                    <tr>
                                        <th class="bg-light">Total Loan Applications</th>
                                        <td class="font-weight-bold">{{ $summary['total_loans'] }}</td>
                                        <th class="bg-light">Total Disbursed Amount</th>
                                        <td class="font-weight-bold text-success">৳ {{ number_format($summary['total_disbursed'], 2) }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">Active Loans</th>
                                        <td class="font-weight-bold text-primary">{{ $summary['active_loans'] }}</td>
                                        <th class="bg-light">Closed Loans</th>
                                        <td class="font-weight-bold text-secondary">{{ $summary['closed_loans'] }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead class="bg-light">
                                <tr>
                                    <th>Loan No</th>
                                    <th class="text-left">Member Info</th>
                                    <th>Product / Scheme</th>
                                    <th class="text-right">Applied Amount</th>
                                    <th class="text-right">Approved Amount</th>
                                    <th class="text-right">Disbursed Amount</th>
                                    <th class="text-right">Net Disbursed</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($loans as $loan)
                                    <tr>
                                        <td class="font-weight-bold text-primary">{{ $loan->loan_no }}</td>
                                        <td class="text-left">
                                            {{ $loan->member->name }} <br>
                                            <small class="text-muted">{{ $loan->member->member_no }}</small>
                                        </td>
                                        <td>{{ $loan->product->name }} ({{ $loan->product->interest_rate }}%)</td>
                                        <td class="text-right">৳ {{ number_format($loan->applied_amount, 2) }}</td>
                                        <td class="text-right">৳ {{ number_format($loan->approved_amount, 2) }}</td>
                                        <td class="text-right">৳ {{ number_format($loan->disbursed_amount, 2) }}</td>
                                        <td class="font-weight-bold text-right">৳ {{ number_format($loan->net_disbursed_amount, 2) }}</td>
                                        <td>
                                            @php
                                                $labels = [1 => 'Pending', 2 => 'Approved', 3 => 'Active', 4 => 'Closed', 5 => 'Rejected'];
                                            @endphp
                                            {{ $labels[$loan->status] ?? 'Unknown' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">No loan applications found matching the search criteria.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
