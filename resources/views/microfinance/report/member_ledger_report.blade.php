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
        .bg-gray-light {
            background-color: #f8f9fa !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Member_Ledger_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                <h3 class="text-center" style="font-size: 20px; margin-top: 10px; margin-bottom: 5px;"><u><b>{{ $pageTitle }}</b></u></h3>
                                <h5 class="text-center m-0" style="font-size: 16px"><b>Branch:</b> {{ auth()->user()->sisterConcern->name ?? auth()->user()->activeSisterConcern->name ?? '' }}</h5>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <table class="table table-bordered mb-4">
                                    <tr>
                                        <th class="text-left" style="width: 25%; background-color: #f4f6f9;">Member Name:</th>
                                        <td class="text-left" style="width: 25%;"><b>{{ $member->name }}</b></td>
                                        <th class="text-left" style="width: 25%; background-color: #f4f6f9;">Member No:</th>
                                        <td class="text-left" style="width: 25%;">{{ $member->member_no }}</td>
                                    </tr>
                                    <tr>
                                        <th class="text-left" style="background-color: #f4f6f9;">Phone:</th>
                                        <td class="text-left">{{ $member->phone }}</td>
                                        <th class="text-left" style="background-color: #f4f6f9;">Somiti:</th>
                                        <td class="text-left">{{ $member->somiti->name ?? 'N/A' }} ({{ $member->somiti->somiti_no ?? '' }})</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <h4 class="mt-2 mb-2 font-weight-bold"><u>Savings Accounts (সঞ্চয়)</u></h4>
                        <table class="table table-bordered mb-4">
                            <thead>
                            <tr class="bg-gray-light">
                                <th style="width: 15%">Acc No</th>
                                <th style="width: 20%">Product</th>
                                <th style="width: 15%">Type</th>
                                <th style="width: 20%">Current Balance</th>
                                <th style="width: 15%">Maturity Date</th>
                                <th style="width: 15%">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($savings as $s)
                                @php $pt = $s->product->product_type; @endphp
                                <tr>
                                    <td>{{ $s->account_no }}</td>
                                    <td>{{ $s->product->name }}</td>
                                    <td>{{ $pt==1?'GS':($pt==2?'DPS':'FDR') }}</td>
                                    <td class="text-right"><strong>৳ {{ number_format($s->current_balance, 2) }}</strong></td>
                                    <td>{{ $s->maturity_date ? \Carbon\Carbon::parse($s->maturity_date)->format('d-m-Y') : '—' }}</td>
                                    <td>
                                        @if($s->status == 0) Pending
                                        @elseif($s->status == 1) Active
                                        @else Closed
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6">No savings account found.</td></tr>
                            @endforelse
                            </tbody>
                        </table>

                        <h4 class="mt-4 mb-2 font-weight-bold"><u>Loans (ঋণ)</u></h4>
                        @forelse($loans as $l)
                            <div style="border: 1px solid #000; padding: 10px; margin-bottom: 5px; background-color: #f8f9fa;">
                                <strong>Loan No:</strong> {{ $l->loan_no }} | 
                                <strong>Product:</strong> {{ $l->product->name }} |
                                <strong>Approved Amount:</strong> ৳ {{ number_format($l->approved_amount, 2) }} |
                                <strong>Interest Rate:</strong> {{ $l->product->interest_rate }}%
                            </div>
                            <table class="table table-bordered mb-4">
                                <thead>
                                <tr class="bg-gray-light">
                                    <th style="width: 10%">Inst. #</th>
                                    <th style="width: 20%">Due Date</th>
                                    <th style="width: 20%" class="text-right">Payable</th>
                                    <th style="width: 20%" class="text-right">Paid</th>
                                    <th style="width: 15%">Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($l->installments as $ins)
                                    <tr>
                                        <td>{{ $ins->installment_no }}</td>
                                        <td>{{ \Carbon\Carbon::parse($ins->due_date)->format('d-m-Y') }}</td>
                                        <td class="text-right">{{ number_format($ins->total_due, 2) }}</td>
                                        <td class="text-right">{{ number_format($ins->paid_amount, 2) }}</td>
                                        <td>{{ $ins->status == 2 ? 'Paid' : 'Due' }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        @empty
                            <div class="text-center p-3 border">No loans found.</div>
                        @endforelse
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
