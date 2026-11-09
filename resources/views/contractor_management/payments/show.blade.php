@extends('layouts.app')
@section('title', 'Contractor Payment Voucher')

@section('style')
    <style>
        table, .table, table td, .table-bordered {
            border: 1px solid #000000;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
        }
        .table.body-table td, .table.body-table th {
            padding: 5px 10px;
            font-size: 16px;
        }
        .table-bordered td, .table-bordered th {
            font-size: 16px;
        }
        .fs-style {
            font-size: 20px !important;
            color: #555;
        }
        @media print {
            body {
                background: #fff !important;
            }
            .no-print {
                display: none !important;
            }
            .content-wrapper, .main-footer {
                margin-left: 0 !important;
                background-color: #fff !important;
            }
            .signature-area {
                position: fixed;
                bottom: 50px;
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header no-print">
                    <button class="btn btn-primary btn-sm" onclick="window.print()"><i class="fa fa-print"></i> Print Voucher</button>
                    <a href="{{ route('contractor_management.payments.index') }}" class="btn btn-default btn-sm float-right"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <h3 class="font-weight-bold" style="font-size: 28px !important; text-decoration: underline;">
                                    {{ $payment->payment_type == 1 ? 'Contractor Payment Voucher' : 'Contractor Advance Voucher' }}
                                </h3>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-6">
                                <h4 class="font-weight-bold" style="font-size: 18px!important;">Voucher No: {{ $voucher->voucher_no ?? 'N/A' }}</h4>
                                <h6 class="font-weight-bold" style="font-size: 15px!important; color: #555;">Receipt No: {{ $payment->receipt_no }}</h6>
                            </div>
                            <div class="col-6 text-right">
                                <h4 class="font-weight-bold" style="font-size: 18px!important;">Date: {{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</h4>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-12">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="24%">Payment Method</th>
                                        <th width="2%" class="text-center">:</th>
                                        <td>{{ $payment->paymentMethod->name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <th width="24%">Payee Name (Contractor)</th>
                                        <th width="2%" class="text-center">:</th>
                                        <td>{{ $payment->contractor->name ?? '' }} ({{ $payment->contractor->mobile_no ?? 'N/A' }})</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-12">
                                <table class="table body-table table-bordered">
                                    <thead>
                                        <tr>
                                            <th class="text-center">Brief Description</th>
                                            <th class="text-center" width="20%">Account Code</th>
                                            <th class="text-right" width="15%">Debit (Tk)</th>
                                            <th class="text-right" width="15%">Credit (Tk)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <strong>Payment For:</strong> {{ $payment->payment_type == 1 ? 'Contractor Bill Payment' : 'Advance Payment' }}
                                            </td>
                                            <td class="text-center"></td>
                                            <td class="text-right"></td>
                                            <td class="text-right"></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                {{ $payment->contractor->accountHead->name ?? $payment->contractor->name }}
                                            </td>
                                            <td class="text-center">
                                                {{ $payment->contractor->accountHead->code ?? '' }}
                                            </td>
                                            <td class="text-right">
                                                {{ number_format($payment->amount, 2) }}
                                            </td>
                                            <td class="text-right"></td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <b style="margin-left: 20px !important;">To, </b>{{ $payment->paymentMethod->name ?? '' }}
                                            </td>
                                            <td class="text-center">{{ $payment->paymentMethod->code ?? '' }}</td>
                                            <td class="text-right"></td>
                                            <td class="text-right">{{ number_format($payment->amount, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($payment->amount, 2) }}</th>
                                            <th class="text-right">{{ number_format($payment->amount, 2) }}</th>
                                        </tr>
                                        <tr>
                                            @php
                                                $amountInWords = \SakibRahaman\DecimalToWords\DecimalToWords::convert($payment->amount, 'Taka', 'Poisa');
                                            @endphp
                                            <th colspan="4" class="text-left font-weight-bold">
                                                Total (in words) = {{ $amountInWords }} Only.
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>
                                
                                @if($payment->note)
                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <p style="font-size: 16px;"><b>Note:</b> {{ $payment->note }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="row signature-area" style="margin-top: 120px;">
                            <div class="col-4 text-center">
                                <span style="border-top: 1px dashed #000 !important; display: block; padding: 5px; font-size: 18px; font-weight: bold;">Prepared By</span>
                            </div>
                            <div class="col-4 text-center">
                                <span style="border-top: 1px dashed #000 !important; display: block; padding: 5px; font-size: 18px; font-weight: bold;">Checked By</span>
                            </div>
                            <div class="col-4 text-center">
                                <span style="border-top: 1px dashed #000 !important; display: block; padding: 5px; font-size: 18px; font-weight: bold;">Authorized Signature</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
            <!-- /.card -->
        </div>
    </div>
@endsection
