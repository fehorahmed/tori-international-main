@extends('layouts.app')
@section('title', 'Contractor Bill Voucher')

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
                    <a href="{{ route('contractor_management.bills.index') }}" class="btn btn-default btn-sm float-right"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <div id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="row mt-3">
                            <div class="col-12 text-center">
                                <h3 class="font-weight-bold" style="font-size: 28px !important; text-decoration: underline;">
                                    Contractor Bill
                                </h3>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-md-12">
                                <table class="table table-bordered">
                                    <tr>
                                        <th width="20%">Voucher No.</th>
                                        <td width="30%"><b>{{ $voucher->voucher_no ?? '' }}</b></td>
                                        <th width="20%">Date</th>
                                        <td width="30%">{{ \Carbon\Carbon::parse($bill->bill_date)->format('d-M-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Bill No.</th>
                                        <td>{{ $bill->bill_no }}</td>
                                        <th>Contractor Name</th>
                                        <td>{{ $bill->contractor->name ?? '' }}</td>
                                    </tr>
                                </table>

                                <span><b>Voucher Details:</b></span>
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
                                        {{-- Debit Rows (Expense Heads) --}}
                                        @foreach($bill->details as $detail)
                                            <tr>
                                                <td>
                                                    {{ $detail->expenseHead->name ?? '' }} 
                                                    @if($detail->remarks)
                                                        <small>({{ $detail->remarks }})</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ $detail->expenseHead->code ?? '' }}
                                                </td>
                                                <td class="text-right">
                                                    {{ number_format($detail->amount, 2) }}
                                                </td>
                                                <td class="text-right"></td>
                                            </tr>
                                        @endforeach

                                        {{-- Credit Row (Contractor Payable) --}}
                                        <tr>
                                            <td>
                                                <b style="margin-left: 20px !important;">To, </b>{{ $bill->contractor->accountHead->name ?? $bill->contractor->name }}
                                            </td>
                                            <td class="text-center">{{ $bill->contractor->accountHead->code ?? '' }}</td>
                                            <td class="text-right"></td>
                                            <td class="text-right">{{ number_format($bill->total_amount, 2) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total:</th>
                                            <th class="text-right">{{ number_format($bill->total_amount, 2) }}</th>
                                            <th class="text-right">{{ number_format($bill->total_amount, 2) }}</th>
                                        </tr>
                                        <tr>
                                            @php
                                                $amountInWords = \SakibRahaman\DecimalToWords\DecimalToWords::convert($bill->total_amount, 'Taka', 'Poisa');
                                            @endphp
                                            <th colspan="4" class="text-left font-weight-bold">
                                                Total (in words) = {{ $amountInWords }} Only.
                                            </th>
                                        </tr>
                                    </tfoot>
                                </table>

                                @if($bill->note)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><b>Note:</b> {{ $bill->note }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Signatures -->
                        <div class="row signature-area" style="margin-top: 150px;">
                            <div class="col-4 text-center">
                                <span style="border-top: 1px solid #000;">Prepared By</span>
                            </div>
                            <div class="col-4 text-center">
                                <span style="border-top: 1px solid #000;">Checked By</span>
                            </div>
                            <div class="col-4 text-center">
                                <span style="border-top: 1px solid #000;">Authorized By</span>
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
