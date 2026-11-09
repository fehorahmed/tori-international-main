@extends('layouts.app')
@section('title')
{{ $voucherTitle }}: {{ $voucher->voucher_no }}
@endsection
@section('style')
    <style>

        table,.table,table td,.table-bordered{
            border: 1px solid #000000;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
        }
        .table.body-table td,.table.body-table th {
            padding: 2px 7px;
        }
        .table.body-table td, .table.body-table th {
            font-size: 18px;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px;
        }
        @media print {
            .signature-area{
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }
    </style>
@endsection
@section('content')
    <div class="card">
        <div class="card-header">
            <a href="{{ route('voucher.print', ['voucher' => $voucher->id]) }}" target="_blank" class="btn btn-info bg-gradient-info btn-sm" role="button"><i class="fa fa-print"></i> Print</a>
        </div>
        <div class="card-body">
            <div class="table-responsive-sm" id="printArea">
                @include('layouts.partial.__report_header')
                <div class="row">
                    <div class="col-12">
                        <h3 class="text-center m-0" style="font-size: 30px !important;">{{ $voucherTitle }}</h3>
                        <h3 class="text-center m-0 fs-style" style="font-size: 30px !important;">FY : {{ getFiscalYear($voucher->date) }}</h3>

                    </div>
                </div>
                <div class="row" style="margin-top: 10px;">
                    <div class="col-6">
                        <h4 style="margin: 0;font-size: 20px!important;">Voucher No: {{ $voucher->voucher_no }}</h4>
                    </div>
                    <div class="col-6 text-right">
                        <h4 style="margin: 0;font-size: 20px!important;">Date: {{ \Carbon\Carbon::parse($voucher->date)->format('d-m-Y') }}</h4>
                    </div>
                </div>
                @if($voucher->voucher_type != \App\Enumeration\VoucherType::$CONTRA_VOUCHER)
                <div class="row"  style="margin-top: 15px">
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
                    <div class="row"  style="margin-top: 15px"></div>
                @endif
                <div class="row">
                    @if($voucher->voucher_type == \App\Enumeration\VoucherType::$CONTRA_VOUCHER)
                        <div class="col-12">
                            <table class="table table-bordered">
                                <thead>
                                <tr>
                                    <th  class="text-center" width="20%">Debit Account</th>
                                    <th class="text-center">Dr. Amount</th>
                                    <th  class="text-center" width="20%">Credit Account</th>
                                    <th class="text-center">Cr. Amount</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($voucher->transactions->chunk(2) as $key => $chunks)
                                    <tr>
                                        @foreach($chunks as $transaction)
                                            <td>{{ $transaction->accountHead->name ?? '' }} Code: {{ $transaction->accountHead->code ?? '' }}</td>
                                            <td class="text-right">{{ number_format($transaction->amount,2) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th class="text-left" colspan=""></th>
                                    <th class="text-right">{{ number_format($voucher->amount,2) }}</th>
                                    <th colspan=""></th>
                                    <th class="text-right">{{ number_format($voucher->amount,2) }}</th>
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
                                        <th  class="text-center">Brief Description</th>
                                        <th class="text-center" width="15%">Account Code</th>
                                        <th class="text-center" width="15%">Debit</th>
                                        <th class="text-center" width="15%">Credit</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($voucher->transactions->where('transaction_type',\App\Enumeration\TransactionType::$DEBIT) as $key => $transaction)
                                        <tr>
                                            <td>
                                                {{ $transaction->accountHead->name ?? '' }}
                                            </td>
                                            <td class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                            <td class="text-right">{{ number_format($transaction->amount,2) }}</td>
                                            <td class="text-right"></td>
                                        </tr>
                                    @endforeach
                                    @foreach($voucher->transactions->where('transaction_type',\App\Enumeration\TransactionType::$CREDIT) as $key => $transaction)
                                        <tr>
                                            <td ><b style="margin-left: 20px !important;">To, </b>{{ $transaction->accountHead->name ?? '' }}</td>
                                            <td class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                            <td class="text-right"></td>
                                            <td  class="text-right">{{ number_format($transaction->amount,2) }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <th class="text-left" colspan="2">Total(in word) = {{ $voucher->amount_in_word }} Only.</th>
                                        <th class="text-right">{{ number_format($voucher->amount,2) }}</th>
                                        <th class="text-right">{{ number_format($voucher->amount,2) }}</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            @else
                                <span><b>{{ $voucherTitleLabel }} Details:</b></span>
                                <table class="table body-table table-bordered">
                                    <tr>
                                        <th  class="text-center">Brief Description</th>
                                        <th class="text-center" width="14%">Account Code</th>
                                        <th class="text-center" width="2%"></th>
                                        <th class="text-center" width="17%">Amount(TK)</th>
                                    </tr>

                                    <tr>
                                        <td style="border-bottom: 1px solid transparent !important;"><b>{{ $voucherTitleLabelType }}:</b></td>
                                        <td style="border-bottom: 1px solid transparent !important;" class="text-center"></td>
                                        <td style="border-bottom: 1px solid transparent !important;"></td>
                                        <td style="border-bottom: 1px solid transparent !important;" class="text-right"></td>
                                    </tr>
                                    @php
                                        $deductionAmount = null;
                                       if (request('voucher_type') == 2){
                                          $transactionType = \App\Enumeration\TransactionType::$DEBIT;
                                            $deductionAmount = $voucher->transactions->where('other_deduct',1)->sum('amount');
                                       }else{
                                           $transactionType = \App\Enumeration\TransactionType::$CREDIT;
                                           $deductionAmount = $voucher->transactions->where('other_deduct',1)->sum('amount');

                                       }
                                       $totalDetailsAmount = $voucher->transactions->where('transaction_type',$transactionType)->where('pf_account_head_id','!=',$voucher->payment_account_head_id)->sum('amount');

                                    @endphp
                                    @foreach($voucher->transactions->where('except_edit',0)->where('transaction_type',$transactionType)->where('pf_account_head_id','!=',$voucher->payment_account_head_id) as $key => $transaction)
                                        <tr>
                                            <td style="border-bottom: 1px solid transparent !important;">
                                                {{ $transaction->accountHead->name ?? '' }}
                                            </td>
                                            <td style="border-bottom: 1px solid transparent !important;" class="text-center">{{ $transaction->accountHead->code ?? '' }}</td>
                                            <td style="{{ count($voucher->transactions) == $key + 1 ? 'border-bottom: 1px solid #000 !important' : 'border-bottom: 1px solid transparent !important'  }};"></td>
                                            <td style="{{ count($voucher->transactions) == $key + 1 ? 'border-bottom: 1px solid #000 !important' : 'border-bottom: 1px solid transparent !important'  }};" class="text-right">{{ number_format($transaction->amount + getParentBaseAmount($transaction),2) }}</td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td style="border-bottom: 1px solid {{ $deductionAmount ? 'transparent' : '#000' }} !important;"></td>
                                        <td style="border-bottom: 1px solid {{ $deductionAmount ? 'transparent' : '#000' }}  !important;" class="text-center"></td>
                                        <td class="text-center" style="border-top: 2px solid #000 !important;">{{ $voucher->voucher_type == 2 ? 'Dr.' : 'Cr.' }}</td>
                                        <th class="text-right" style="border-top: 2px solid #000 !important;">{{ number_format($totalDetailsAmount,2) }}</th>
                                    </tr>
                                    @if($deductionAmount)
                                        <tr>
                                            <td style="border-bottom: 1px solid transparent !important;"><b>Deductions:</b></td>
                                            <td style="border-bottom: 1px solid transparent !important;" class="text-center"></td>
                                            <td style="border-bottom: 1px solid transparent !important;"></td>
                                            <td style="border-bottom: 1px solid transparent !important;" class="text-right"></td>
                                        </tr>
                                        @foreach($voucher->transactions->where('other_deduct',1) as $key => $transaction)
                                            <tr>
                                                <td style="border-bottom: 1px solid transparent !important;">{{ $transaction->accountHead->name ?? '' }}</td>
                                                <td style="border-bottom: 1px solid transparent !important;" class="text-center">{{ $transaction->accountHead->code }}</td>
                                                <td style="{{ count($voucher->transactions) == $key + 1 ? 'border-bottom: 1px solid #000 !important' : 'border-bottom: 1px solid transparent !important'  }};"></td>
                                                <td style="{{ count($voucher->transactions) == $key + 1 ? 'border-bottom: 1px solid #000 !important' : 'border-bottom: 1px solid transparent !important'  }};" class="text-right">{{ number_format($transaction->amount,2) }}</td>
                                            </tr>
                                        @endforeach

                                        <tr>
                                            <td ></td>
                                            <td  class="text-center"></td>
                                            <td  class="text-center" style="border-top: 2px solid #000 !important;">{{ $voucher->voucher_type == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER ? 'Cr.' : 'Dr.' }}</td>
                                            <th  class="text-right" style="border-top: 2px solid #000 !important;">{{ number_format($deductionAmount,2) }}</th>
                                        </tr>
                                    @endif

                                    <tr>
                                        <th class="text-center"></th>
                                        <th class="text-center">{{ $voucher->paymentAccountHead->code ?? '' }}</th>
                                        <th class="text-center">{{ $voucher->voucher_type == 2 ? 'Cr.' : 'Dr.' }}</th>
                                        <th class="text-right">{{ number_format($voucher->amount,2) }}</th>
                                    </tr>
                                    <tr>
                                        <th colspan="4" class="text-left">Total(in word) = {{ $voucher->amount_in_word }} Only.</th>
                                    </tr>
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
                    <div class="col-3 offset-9 text-center"><span style="border-top: 1px dotted #000 !important;display: block;padding: 5px;font-size: 20px;font-weight: bold">Authorized Signature</span></div>
                </div>
            </div>

        </div>
    </div>


@endsection
