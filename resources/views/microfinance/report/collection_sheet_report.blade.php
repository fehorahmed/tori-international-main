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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Collection_Sheet_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                <h3 class="text-center" style="font-size: 20px; margin-top: 10px; margin-bottom: 5px;"><u><b>{{ $pageTitle }}</b></u></h3>
                                <h5 class="text-center m-0" style="font-size: 16px"><b>Branch:</b> {{ auth()->user()->sisterConcern->name ?? auth()->user()->activeSisterConcern->name ?? '' }}</h5>
                                <h5 class="text-center m-0" style="font-size: 16px"><b>Somiti:</b> {{ $somiti->name }} ({{ $somiti->code }})</h5>
                                <h5 class="text-center m-0" style="font-size: 15px"><b>Date:</b> {{ date('d-m-Y', strtotime($date)) }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th class="text-left" style="width: 25%">Member Info</th>
                                    <th style="width: 15%">Active Loans</th>
                                    <th style="width: 15%">Due Installments</th>
                                    <th class="text-right" style="width: 15%">Total Loan Due</th>
                                    <th style="width: 15%">Active Savings Accounts</th>
                                    <th class="text-right" style="width: 15%">Current Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($members as $m)
                                    <tr>
                                        <td class="text-left font-weight-bold">
                                            {{ $m->name }} <br>
                                            <small class="text-muted">{{ $m->member_no }} | {{ $m->mobile_no }}</small>
                                        </td>
                                        <td>
                                            @forelse($m->loans as $loan)
                                                <span class="badge badge-info mb-1">{{ $loan->loan_no }}</span> <br>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td>
                                            @forelse($m->loans as $loan)
                                                @foreach($loan->installments as $ins)
                                                    Installment #{{ $ins->installment_no }} <br>
                                                @endforeach
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td class="font-weight-bold text-danger text-right">
                                            @php
                                                $due = $m->loans->sum(fn($l) => $l->installments->sum('total_due'));
                                            @endphp
                                            ৳ {{ number_format($due, 2) }}
                                        </td>
                                        <td>
                                            @forelse($m->savingsAccounts as $sa)
                                                <span class="badge badge-success mb-1">{{ $sa->account_no }} ({{ $sa->product->name }})</span> <br>
                                            @empty
                                                <span class="text-muted">—</span>
                                            @endforelse
                                        </td>
                                        <td class="font-weight-bold text-success text-right">
                                            ৳ {{ number_format($m->savingsAccounts->sum('current_balance'), 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No active members found in this Somiti for the selected date.</td>
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
