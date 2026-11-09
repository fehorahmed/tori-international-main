<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Samity Collection Sheet - {{ $group->name_en }}</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem 0.1rem !important;
            font-size: 11px !important;
        }
        @page {
            size: landscape;
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
        .header-table { width: 100%; border: none; margin-bottom: 10px; font-size: 14px;}
        .header-table td { border: none; padding: 2px; text-align: left; }
        .section-header { text-align: center; font-weight: bold; }
        
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Collection_Sheet_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        <div class="row mx-0 header-common-info" id="header-common-info" style="display: none">
                            <div class="col-6 text-left">
                                <p><b>{{ date('d-m-Y, h:i A') }}</b></p>
                            </div>
                            <div class="col-6 text-right">
                                <p><b>Printed by: {{ auth()->user()->name }}</b></p>
                            </div>
                        </div>
                        <div class="row mx-0 header-common-info">
                            <div class="col-12 text-center" style="display: flex; justify-content: center; align-items: center;">
                                <img height="60px" src="{{ asset('img/logo.png') }}" alt="Logo" style="margin-right: 12px; border-radius: 50%;">
                                <div style="text-align: left;">
                                    <h1 class="m-0" style="font-weight: bold; font-size: 32px; line-height: 1.1;">{{ config('app.name') }}</h1>
                                    <h5 class="m-0" style="font-size: 14px; color: #555; margin-top: 2px !important;">{{ config('app.address') }}</h5>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center mb-3 mt-3">
                            <h3 style="font-size: 20px; font-weight: bold; text-decoration: underline; margin-bottom: 15px;">Monthly Collection Sheet</h3>
                        </div>

                        <table class="header-table" style="font-size: 11px; margin-bottom: 5px;">
                            <tr>
                                <td width="20%" style="vertical-align: top;">
                                    <strong>Branch Name(Code)</strong> : {{ auth()->user()->active_sister_concern->name ?? 'Main Branch' }} ({{ auth()->user()->active_sister_concern->code ?? 'N/A' }})<br>
                                    <strong>Samity Name(Code)</strong> : {{ $group->name_en }} ({{ $group->code }})<br>
                                    <strong>Samity Opening Date</strong> : {{ $group->opening_date ? \Carbon\Carbon::parse($group->opening_date)->format('d/m/Y') : \Carbon\Carbon::parse($group->created_at)->format('d/m/Y') }}
                                </td>
                                <td width="20%" style="vertical-align: top;">
                                    <strong>Print Date</strong> : {{ date('d/m/Y') }}<br>
                                    <strong>Samity Day</strong> : {{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$group->collection_day] ?? 'N/A' }}<br>
                                    <strong>Samity Time</strong> : {{ $group->meeting_time ? \Carbon\Carbon::parse($group->meeting_time)->format('h:i A') : '-' }}
                                </td>
                                <td width="25%" style="vertical-align: top;">
                                    <strong>Field Officer Name</strong> : {{ $group->fieldOfficer ? $group->fieldOfficer->id_no . ' - ' . $group->fieldOfficer->name : 'N/A' }}<br>
                                    <strong>Month</strong> : {{ date('F, Y', strtotime($month . '-01')) }}
                                </td>
                                <td width="35%" style="vertical-align: top;">
                                    <table class="table table-bordered" style="margin-bottom: 0; font-size: 10px;">
                                        <tr>
                                            <th style="text-align: left; width: 45%;"></th>
                                            @foreach($collectionDates as $index => $date)
                                                <th style="padding: 1px !important; text-align: center;">{{ $index == 0 ? '1st' : ($index == 1 ? '2nd' : ($index == 2 ? '3rd' : ($index == 3 ? '4th' : '5th'))) }}</th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <th style="text-align: left; padding: 1px 4px !important;">Savings Depositor:</th>
                                            @foreach($collectionDates as $date)
                                                <th style="padding: 1px !important;"></th>
                                            @endforeach
                                        </tr>
                                        <tr>
                                            <th style="text-align: left; padding: 1px 4px !important;">No of Attendance:</th>
                                            @foreach($collectionDates as $date)
                                                <th style="padding: 1px !important;"></th>
                                            @endforeach
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>

                        <table class="table table-bordered">
                            <thead class="bg-light">
                                <tr>
                                    <th rowspan="2" width="2%">SL</th>
                                    <th rowspan="2" width="5%">Member No</th>
                                    <th rowspan="2" width="10%">Member Name</th>
                                    <th rowspan="2" width="10%">Spouse Name</th>
                                    <th rowspan="2" width="8%">Mobile No</th>
                                    <th rowspan="2" width="5%">Opening<br>Balance</th>
                                    <th colspan="{{ count($collectionDates) }}" class="section-header">This Month Savings Collection</th>
                                    <th colspan="2" class="section-header">Withdraw</th>
                                    <th colspan="6" class="section-header">Loan Information</th>
                                    <th colspan="{{ count($collectionDates) }}" class="section-header">This Month Loan Collection</th>
                                    <th rowspan="2" width="5%">Month<br>End Out.</th>
                                </tr>
                                <tr>
                                    <!-- Savings Weeks -->
                                    @foreach($collectionDates as $index => $date)
                                        <th width="4%">
                                            {{ $index == 0 ? '1st' : ($index == 1 ? '2nd' : ($index == 2 ? '3rd' : ($index == 3 ? '4th' : '5th'))) }} week<br>
                                            <span style="font-size: 10px;">{{ $date }}</span>
                                        </th>
                                    @endforeach
                                    <!-- Withdraw Info -->
                                    <th width="4%">Date</th>
                                    <th width="4%">Amt</th>
                                    <!-- Loan Info -->
                                    <th width="5%">Disb.<br>Date</th>
                                    <th width="5%">Loan<br>Amount</th>
                                    <th width="3%">Cycle</th>
                                    <th width="4%">Repay Amt<br>/ Type</th>
                                    <th width="5%">Opening<br>Outst.</th>
                                    <th width="5%">Opening<br>Overdue</th>
                                    <!-- Loan Weeks -->
                                    @foreach($collectionDates as $index => $date)
                                        <th width="4%">
                                            {{ $index == 0 ? '1st' : ($index == 1 ? '2nd' : ($index == 2 ? '3rd' : ($index == 3 ? '4th' : '5th'))) }} week<br>
                                            <span style="font-size: 10px;">{{ $date }}</span>
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalSavingsOpeningBalance = 0;
                                    $totalLoanAmount = 0;
                                    $totalLoanRepayAmount = 0;
                                    $totalLoanOpeningOutstanding = 0;
                                @endphp
                                @foreach($membersData as $index => $data)
                                @php
                                    $totalSavingsOpeningBalance += $data['savings_opening_balance'];
                                    
                                    foreach($data['loans'] as $loanData) {
                                        $totalLoanAmount += $loanData['loan']->principal_amount;
                                        $totalLoanRepayAmount += $loanData['loan']->installment_amount;
                                        $totalLoanOpeningOutstanding += $loanData['loan_opening_outstanding'];
                                    }
                                    
                                    $loansCount = count($data['loans']);
                                    $rowspan = $loansCount > 1 ? $loansCount : 1;
                                @endphp
                                <tr>
                                    <td class="text-center" rowspan="{{ $rowspan }}">{{ $index + 1 }}</td>
                                    <td class="text-center" rowspan="{{ $rowspan }}">{{ $data['member']->member_no }}</td>
                                    <td class="text-left" rowspan="{{ $rowspan }}">{{ $data['member']->name }}</td>
                                    <td class="text-left" rowspan="{{ $rowspan }}">{{ $data['member']->spouse_name }}</td>
                                    <td class="text-center" rowspan="{{ $rowspan }}">{{ $data['member']->mobile_no }}</td>
                                    <td class="text-right" rowspan="{{ $rowspan }}">{{ number_format($data['savings_opening_balance'], 2) }}</td>
                                    <!-- Savings Collection Blank Cells -->
                                    @foreach($collectionDates as $date)
                                        <td rowspan="{{ $rowspan }}"></td>
                                    @endforeach
                                    <td rowspan="{{ $rowspan }}"></td>
                                    <td rowspan="{{ $rowspan }}"></td>
                                    
                                    @if($loansCount > 0)
                                        <td class="text-center">{{ \Carbon\Carbon::parse($data['loans'][0]['loan']->disbursement_date)->format('d/m/y') }}</td>
                                        <td class="text-right">{{ number_format($data['loans'][0]['loan']->principal_amount, 2) }}</td>
                                        <td class="text-center">1</td> <!-- Cycle Placeholder -->
                                        <td class="text-center">
                                            {{ number_format($data['loans'][0]['loan']->installment_amount, 2) }}<br>
                                            <span style="font-size: 9px; color: #666;">{{ $data['loans'][0]['loan']->installment_type == 'Monthly' ? '(M)' : '(W)' }}</span>
                                        </td>
                                        <td class="text-right">{{ number_format($data['loans'][0]['loan_opening_outstanding'], 2) }}</td>
                                        <td class="text-right">0</td> <!-- Overdue Placeholder -->
                                    @else
                                        <td class="text-center">-</td><td class="text-center">-</td><td class="text-center">-</td>
                                        <td class="text-center">-</td><td class="text-center">-</td><td class="text-center">-</td>
                                    @endif
                                    
                                    <!-- Loan Collection Blank Cells -->
                                    @foreach($collectionDates as $date)
                                        <td></td>
                                    @endforeach
                                    <td></td>
                                </tr>
                                
                                <!-- Render additional loans if they exist -->
                                @if($loansCount > 1)
                                    @for($i = 1; $i < $loansCount; $i++)
                                        <tr>
                                            <td class="text-center">{{ \Carbon\Carbon::parse($data['loans'][$i]['loan']->disbursement_date)->format('d/m/y') }}</td>
                                            <td class="text-right">{{ number_format($data['loans'][$i]['loan']->principal_amount, 2) }}</td>
                                            <td class="text-center">1</td> <!-- Cycle Placeholder -->
                                            <td class="text-center">
                                                {{ number_format($data['loans'][$i]['loan']->installment_amount, 2) }}<br>
                                                <span style="font-size: 9px; color: #666;">{{ $data['loans'][$i]['loan']->installment_type == 'Monthly' ? '(M)' : '(W)' }}</span>
                                            </td>
                                            <td class="text-right">{{ number_format($data['loans'][$i]['loan_opening_outstanding'], 2) }}</td>
                                            <td class="text-right">0</td> <!-- Overdue Placeholder -->
                                            <!-- Loan Collection Blank Cells -->
                                            @foreach($collectionDates as $date)
                                                <td></td>
                                            @endforeach
                                            <td></td>
                                        </tr>
                                    @endfor
                                @endif
                                
                                @endforeach
                            </tbody>
                            <tfoot style="font-weight: bold; background: #f8f9fa;">
                                <tr>
                                    <td colspan="5" class="text-right">Total:</td>
                                    <td class="text-right">{{ number_format($totalSavingsOpeningBalance, 2) }}</td>
                                    @foreach($collectionDates as $date)
                                        <td></td>
                                    @endforeach
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($totalLoanAmount, 2) }}</td>
                                    <td></td>
                                    <td class="text-right">{{ number_format($totalLoanRepayAmount, 2) }}</td>
                                    <td class="text-right">{{ number_format($totalLoanOpeningOutstanding, 2) }}</td>
                                    <td class="text-right">0.00</td>
                                    @foreach($collectionDates as $date)
                                        <td></td>
                                    @endforeach
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
