<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Repayment Schedule - {{ $loan->loan_no }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            margin: 0;
            padding: 30px;
            font-size: 14px;
            line-height: 1.5;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #007bff;
            padding-bottom: 20px;
        }
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        .title {
            font-size: 18px;
            font-weight: 600;
            color: #555;
            margin: 5px 0;
        }
        .loan-no {
            font-size: 14px;
            color: #777;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .info-table td {
            padding: 8px 12px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            color: #555;
            width: 180px;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .schedule-table th {
            background-color: #007bff;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
            padding: 12px;
            border: 1px solid #ddd;
        }
        .schedule-table td {
            padding: 10px 12px;
            border: 1px solid #ddd;
            text-align: center;
        }
        .schedule-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .amount {
            text-align: right !important;
            font-weight: 500;
        }
        .total-row {
            font-weight: bold;
            background-color: #e9ecef !important;
        }
        .badge {
            display: inline-block;
            padding: 4px 8px;
            font-size: 11px;
            font-weight: 600;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-warning { background-color: #ffc107; color: #212529; }
        .badge-success { background-color: #28a745; color: white; }
        .badge-danger { background-color: #dc3545; color: white; }
        .badge-info { background-color: #17a2b8; color: white; }
        .footer {
            margin-top: 50px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .signature-section {
            margin-top: 60px;
            display: flex;
            justify-content: space-between;
        }
        .signature-box {
            text-align: center;
            width: 200px;
            border-top: 1px solid #333;
            padding-top: 5px;
            font-size: 13px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
        .btn-print {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
            float: right;
            margin-bottom: 20px;
        }
        .btn-print:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>

    <button class="btn-print no-print" onclick="window.print()">Print Schedule</button>

    <div class="header">
        <div class="company-name">{{ auth()->user()->sisterConcern?->name ?? 'MK Microfinance' }}</div>
        <div class="title">Loan Repayment Schedule</div>
        <div class="loan-no">Loan Agreement No: <strong>{{ $loan->loan_no }}</strong></div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">Member Name:</td>
            <td>{{ $loan->member->name }} ({{ $loan->member->member_no }})</td>
            <td class="info-label">Loan Product:</td>
            <td>{{ $loan->product->name }}</td>
        </tr>
        <tr>
            <td class="info-label">Somiti / Branch:</td>
            <td>{{ $loan->somiti->name ?? 'N/A' }}</td>
            <td class="info-label">Interest Rate:</td>
            <td>{{ $loan->product->interest_rate }}% ({{ $loan->product->interest_type == 2 ? 'Reducing' : 'Flat' }})</td>
        </tr>
        <tr>
            <td class="info-label">Disbursement Date:</td>
            <td>{{ $loan->disbursement_date ? $loan->disbursement_date->format('d M, Y') : 'N/A' }}</td>
            <td class="info-label">First Installment:</td>
            <td>{{ $loan->first_installment_date ? $loan->first_installment_date->format('d M, Y') : 'N/A' }}</td>
        </tr>
        <tr>
            <td class="info-label">Approved Amount:</td>
            <td class="amount">৳ {{ number_format($loan->approved_amount, 0) }}</td>
            <td class="info-label">Installments:</td>
            <td>{{ $loan->total_installments }} ({{ $loan->product->installment_frequency == 1 ? 'Daily' : ($loan->product->installment_frequency == 2 ? 'Weekly' : 'Monthly') }})</td>
        </tr>
        <tr>
            <td class="info-label">Processing Fee:</td>
            <td class="amount">৳ {{ number_format($loan->processing_fee, 0) }}</td>
            <td class="info-label">Insurance Fee:</td>
            <td class="amount">৳ {{ number_format($loan->insurance_amount, 0) }}</td>
        </tr>
        <tr>
            <td class="info-label">Net Disbursed:</td>
            <td class="amount" style="font-weight: bold; color: #007bff;">৳ {{ number_format($loan->net_disbursed_amount, 0) }}</td>
            <td class="info-label">Grace Period:</td>
            <td>{{ $loan->grace_period_days }} Days</td>
        </tr>
    </table>

    <table class="schedule-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 22%;">Due Date</th>
                <th style="width: 18%;">Principal Amount</th>
                <th style="width: 18%;">Interest Amount</th>
                <th style="width: 18%;">Total Installment</th>
                <th style="width: 16%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalPrincipal = 0;
                $totalInterest = 0;
                $totalDue = 0;
            @endphp
            @foreach($loan->installments as $inst)
                @php
                    $totalPrincipal += $inst->principal_amount;
                    $totalInterest += $inst->interest_amount;
                    $totalDue += $inst->total_due;
                @endphp
                <tr>
                    <td>{{ $inst->installment_no }}</td>
                    <td>{{ date('d M, Y', strtotime($inst->due_date)) }}</td>
                    <td class="amount">৳ {{ number_format($inst->principal_amount, 0) }}</td>
                    <td class="amount">৳ {{ number_format($inst->interest_amount, 0) }}</td>
                    <td class="amount" style="font-weight: bold; color: #007bff;">৳ {{ number_format($inst->total_due, 0) }}</td>
                    <td>
                        @if($inst->status == 1)
                            <span class="badge badge-warning">Pending</span>
                        @elseif($inst->status == 2)
                            <span class="badge badge-success">Paid</span>
                        @elseif($inst->status == 3)
                            <span class="badge badge-info">Partial</span>
                        @else
                            <span class="badge badge-danger">Overdue</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="2" style="text-align: right;">TOTAL PAYABLE:</td>
                <td class="amount">৳ {{ number_format($totalPrincipal, 0) }}</td>
                <td class="amount">৳ {{ number_format($totalInterest, 0) }}</td>
                <td class="amount" style="color: #007bff;">৳ {{ number_format($totalDue, 0) }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>

    <div class="signature-section">
        <div class="signature-box">Member's Signature</div>
        <div class="signature-box">Officer's Signature</div>
        <div class="signature-box">Authorized Signature</div>
    </div>

    <div class="footer">
        Generated automatically by MK-ERP System on {{ date('d M, Y h:i A') }}
    </div>

</body>
</html>
