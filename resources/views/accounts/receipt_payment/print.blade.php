<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $voucherTitle }} - {{ $voucher->voucher_no }}</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered td,
        .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }

        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;
            ;
        }

        .table-bordered td,
        .table-bordered th {
            font-size: 18px !important;
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        @media print {

            .table-bordered td,
            .table-bordered th {
                font-size: 18px !important;
            }

            .signature-area {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }

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
        }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Voucher_' . $voucher->voucher_no])
                    <div class="table-responsive-sm" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row">
                            <div class="col-12">
                                <h3 class="text-center m-0" style="font-size: 30px !important;">{{ $voucherTitle }}</h3>
                                <h3 class="text-center m-0 fs-style" style="font-size: 30px !important;">FY :
                                    {{ getFiscalYear($voucher->date) }}</h3>

                            </div>
                        </div>
                        <div class="row" style="margin-top: 10px;">
                            <div class="col-6">
                                <h4 style="margin: 0;font-size: 20px!important;">Voucher No: {{ $voucher->voucher_no }}
                                </h4>
                            </div>
                            <div class="col-6 text-right">
                                <h4 style="margin: 0;font-size: 20px!important;">Date:
                                    {{ \Carbon\Carbon::parse($voucher->date)->format('d-m-Y') }}</h4>
                            </div>
                        </div>
                        @if($voucher->voucher_type != \App\Enumeration\VoucherType::$CONTRA_VOUCHER)
                            <div class="row" style="margin-top: 15px">
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        @if($voucher->voucher_type != \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                            <tr>
                                                <th width="24%">
                                                    @if($voucher->payment_type_id == 1)
                                                        Bank
                                                    @elseif($voucher->payment_type_id == 2)
                                                        Cash
                                                    @elseif($voucher->payment_type_id == 3)
                                                        Mobile Banking
                                                    @endif
                                                </th>
                                                <th width="2%" class="text-center">:</th>
                                                <td width="" colspan="2">{{ $voucher->paymentAccountHead->name ?? '' }}</td>
                                            </tr>
                                            @if($voucher->payment_type_id == 1)
                                                <tr>
                                                    <th width="24%">Cheque No</th>
                                                    <th width="2%" class="text-center">:</th>
                                                    <td width="" colspan="2">{{ $voucher->cheque_no ?? '' }}</td>
                                                </tr>
                                            @endif
                                        @endif
                                        <tr>
                                            <th width="24%">{{ $partyLabel }} Name</th>
                                            <th width="2%" class="text-center">:</th>
                                            <td width="">{{ $voucher->payeeDepositorAccountHead->name ?? '' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        @else
                            <div class="row" style="margin-top: 15px"></div>
                        @endif
                        <div class="row">
                            @if($voucher->voucher_type == \App\Enumeration\VoucherType::$CONTRA_VOUCHER)
                                <div class="col-12">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="20%">Debit Account</th>
                                                <th class="text-center">Dr. Amount</th>
                                                <th class="text-center" width="20%">Credit Account</th>
                                                <th class="text-center">Cr. Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($voucher->transactions->chunk(2) as $key => $chunks)
                                                <tr>
                                                    @foreach($chunks as $transaction)
                                                        <td>{{ $transaction->accountHead->name ?? '' }} Code:
                                                            {{ $transaction->accountHead->code ?? '' }}</td>
                                                        <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-left" colspan=""></th>
                                                <th class="text-right">{{ number_format($voucher->amount, 2) }}</th>
                                                <th colspan=""></th>
                                                <th class="text-right">{{ number_format($voucher->amount, 2) }}</th>
                                            </tr>
                                            <tr>
                                                <th colspan="4">Total(in word) = {{ $voucher->amount_in_word }} Only.</th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><b>Note:</b> {{ $voucher->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="col-12">
                                    @if($voucher->voucher_type == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                        <table class="table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Brief Description</th>
                                                    <th class="text-center" width="15%">Account Code</th>
                                                    <th class="text-center" width="15%">Debit</th>
                                                    <th class="text-center" width="15%">Credit</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($voucher->transactions->where('transaction_type', \App\Enumeration\TransactionType::$DEBIT) as $key => $transaction)
                                                    <tr>
                                                        <td>
                                                            {{ $transaction->accountHead->name ?? '' }}
                                                        </td>
                                                        <td class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                                        <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                                                        <td class="text-right"></td>
                                                    </tr>
                                                @endforeach
                                                @foreach($voucher->transactions->where('transaction_type', \App\Enumeration\TransactionType::$CREDIT) as $key => $transaction)
                                                    <tr>
                                                        <td><b style="margin-left: 20px !important;">To,
                                                            </b>{{ $transaction->accountHead->name ?? '' }}</td>
                                                        <td class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                                        <td class="text-right"></td>
                                                        <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <th class="text-left" colspan="2">Total(in word) =
                                                        {{ $voucher->amount_in_word }} Only.</th>
                                                    <th class="text-right">{{ number_format($voucher->amount, 2) }}</th>
                                                    <th class="text-right">{{ number_format($voucher->amount, 2) }}</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    @else
                                        <span><b>{{ $voucherTitleLabel }} Details:</b></span>
                                        <table class="table body-table table-bordered">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">Brief Description</th>
                                                    <th class="text-center" width="15%">Account Code</th>
                                                    <th class="text-right" width="15%">Debit (Tk)</th>
                                                    <th class="text-right" width="15%">Credit (Tk)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td><b>{{ $voucherTitleLabelType }}:</b></td>
                                                    <td class="text-center"></td>
                                                    <td class="text-right"></td>
                                                    <td class="text-right"></td>
                                                </tr>
                                                @php
                                                    $isPayment = $voucher->voucher_type == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER;
                                                    $mainTransactionType = $isPayment ? \App\Enumeration\TransactionType::$DEBIT : \App\Enumeration\TransactionType::$CREDIT;
                                                    $deductionAmount = $voucher->transactions->where('other_deduct', 1)->sum('amount');
                                                    $mainTransactions = $voucher->transactions->where('except_edit', 0)->where('transaction_type', $mainTransactionType)->where('pf_account_head_id', '!=', $voucher->payment_account_head_id);
                                                    $deductionTransactions = $voucher->transactions->where('other_deduct', 1);
                                                @endphp

                                                @if($isPayment)
                                                    {{-- PAYMENT VOUCHER: DEBIT FIRST, THEN CREDIT --}}
                                                    
                                                    {{-- DEBITS --}}
                                                    @foreach($mainTransactions as $transaction)
                                                        <tr>
                                                            <td>{{ $transaction->accountHead->name ?? '' }}</td>
                                                            <td class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                                            <td class="text-right">{{ number_format($transaction->amount + getParentBaseAmount($transaction), 2) }}</td>
                                                            <td class="text-right"></td>
                                                        </tr>
                                                    @endforeach

                                                    {{-- CREDITS --}}
                                                    @if($deductionAmount > 0)
                                                        <tr>
                                                            <td><b>Deductions:</b></td>
                                                            <td class="text-center"></td><td class="text-right"></td><td class="text-right"></td>
                                                        </tr>
                                                        @foreach($deductionTransactions as $transaction)
                                                            <tr>
                                                                <td><b style="margin-left: 20px !important;">To, </b>{{ $transaction->accountHead->name ?? '' }}</td>
                                                                <td class="text-center">{{ $transaction->accountHead->code }}</td>
                                                                <td class="text-right"></td>
                                                                <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                                                            </tr>
                                                        @endforeach
                                                    @endif

                                                    <tr>
                                                        <td><b style="margin-left: 20px !important;">To, </b>{{ $voucher->paymentAccountHead->name ?? '' }}</td>
                                                        <td class="text-center">{{ $voucher->paymentAccountHead->code ?? '' }}</td>
                                                        <td class="text-right"></td>
                                                        <td class="text-right">{{ number_format($voucher->amount, 2) }}</td>
                                                    </tr>

                                                @else
                                                    {{-- RECEIPT VOUCHER: DEBIT FIRST, THEN CREDIT --}}

                                                    {{-- DEBITS --}}
                                                    <tr>
                                                        <td>{{ $voucher->paymentAccountHead->name ?? '' }}</td>
                                                        <td class="text-center">{{ $voucher->paymentAccountHead->code ?? '' }}</td>
                                                        <td class="text-right">{{ number_format($voucher->amount, 2) }}</td>
                                                        <td class="text-right"></td>
                                                    </tr>

                                                    @if($deductionAmount > 0)
                                                        <tr>
                                                            <td><b>Deductions:</b></td>
                                                            <td class="text-center"></td><td class="text-right"></td><td class="text-right"></td>
                                                        </tr>
                                                        @foreach($deductionTransactions as $transaction)
                                                            <tr>
                                                                <td>{{ $transaction->accountHead->name ?? '' }}</td>
                                                                <td class="text-center">{{ $transaction->accountHead->code }}</td>
                                                                <td class="text-right">{{ number_format($transaction->amount, 2) }}</td>
                                                                <td class="text-right"></td>
                                                            </tr>
                                                        @endforeach
                                                    @endif

                                                    {{-- CREDITS --}}
                                                    @foreach($mainTransactions as $transaction)
                                                        <tr>
                                                            <td><b style="margin-left: 20px !important;">To, </b>{{ $transaction->accountHead->name ?? '' }}</td>
                                                            <td class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                                            <td class="text-right"></td>
                                                            <td class="text-right">{{ number_format($transaction->amount + getParentBaseAmount($transaction), 2) }}</td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    @php
                                                        $totalDebit = $voucher->voucher_type == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? ($voucher->amount + $deductionAmount) : ($voucher->amount + $deductionAmount);
                                                        $totalCredit = $totalDebit;
                                                    @endphp
                                                    <th colspan="2" class="text-right">Total:</th>
                                                    <th class="text-right">{{ number_format($totalDebit, 2) }}</th>
                                                    <th class="text-right">{{ number_format($totalCredit, 2) }}</th>
                                                </tr>
                                                <tr>
                                                    <th colspan="4" class="text-left font-weight-bold">
                                                        Total(in word) = {{ $voucher->amount_in_word }} Only.
                                                    </th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    @endif
                                    <div class="row">
                                        <div class="col-md-12">
                                            <p><b>Note:</b> {{ $voucher->notes }}</p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                        <div class="row signature-area" style="margin-top: 30px">
                            <div class="col-3 offset-9 text-center"><span
                                    style="border-top: 1px dotted #000 !important;display: block;padding: 5px;font-size: 20px;font-weight: bold">Authorized
                                    Signature</span></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
    </div>
    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>

</html>