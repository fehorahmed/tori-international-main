@extends('layouts.app')
@section('title')
    Preliminary Flat Sale Agreement
@endsection
@section('style')
    <style>
        .company_name {
            color: #008f4d;
            font-size: 46px;
            font-weight: 600;
            letter-spacing: 6px;
        }

        h1.details-title {
            font-size: 28px;
            border-bottom: 2px solid #000;
            display: inline-block;
            font-weight: 600;
            margin-top: 28px;
            margin-bottom: 28px;
        }

        .table.project-details td, .table.project-details th {
            border: none !important;
            padding: 2px 11px;
            font-size: 18px;
        }

        .table-bordered.payment-schedule td, .table-bordered.payment-schedule th {
            border: 1px solid #000000 !important;
            padding: 0 5px;
            font-size: 18px;
        }

        .footer-address {
            font-size: 20px;
            margin-top: 70px;
        }

        @media print {
            .footer-address {
                font-size: 23px;
            }

            .contact-info {
                font-size: 21px;
            }

            .table.project-details td, .table.project-details th {
                font-size: 21px;
            }


            .form-logo {
                height: 150px !important;
            }

            .company_name {
                font-size: 70px;
            }

            .signature-bottom {
                position: fixed;
                bottom: 0;
                width: 100%;
            }
        }

    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <button class="btn btn-info bg-gradient-info btn-xs" onclick="getprint('prinarea')">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
                <div class="card-body">
                    <div id="prinarea">
                        <div class="row">
                            <div class="col-3 text-center">
                                <img height="100px" class="form-logo" src="{{ asset('img/logo.png') }}">
                            </div>
                            <div class="col-9">
                                <h1 class="company_name">{{ config('app.name') }}</h1>
                            </div>
                            <div class="col-12 text-center">
                                <h1 class="details-title">Preliminary Flat Sale Agreement</h1>
                            </div>
                            <div class="col-10 text-center">
                                <h4 class="text-right">
                                    Date: {{ \Carbon\Carbon::parse($flat_sale->date)->format('d F, Y') }}</h4>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12">
                                <table class="table project-details">
                                    <tr>
                                        <td width="20%">Name of Project</td>
                                        <td width="1%" class="text-center">:</td>
                                        <th class="text-left">{{ $flat_sale->project->name ?? '' }}</th>
                                    </tr>
                                    <tr>
                                        <td>Project Address</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->project->address ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Client Name</th>
                                        <th class="text-center">:</th>
                                        <th class="text-left">{{ $flat_sale->customer->name ?? '' }}</th>
                                    </tr>
                                    <tr>
                                        <td>Contact No.</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->customer->mobile_no ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Father's Name</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->customer->father_name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Mother's Name</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->customer->mother_name ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Present Address</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->customer->address ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Permanent Address</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->customer->permanent_address ?? '' }}</td>
                                    </tr>
                                </table>
                                <h5 class="ml-2 mt-3"><u><b>FLAT DETAILS</b></u></h5>
                                <table class="table project-details">
                                    <tr>
                                        <td width="20%">Flat No.</td>
                                        <td width="1%" class="text-center">:</td>
                                        <td class="text-left" width="22%">{{ $flat_sale->flat->flat_no ?? '' }}</td>
                                        <td width="1%"></td>
                                    </tr>
                                    <tr>
                                        <td>Flat Size</td>
                                        <td class="text-center">:</td>
                                        <td colspan="2" class="text-left">{{ $flat_sale->flat->flat_size ?? '' }}</td>
                                    </tr>
                                    <tr>
                                        <td>Flat Price</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left"> x {{ $flat_sale->flat->flat_size ?? '' }}</td>
                                        <td width="1%" class="text-center">=</td>
                                        <td class="text-left">{{ number_format($flat_sale->flat_price) }}/-</td>
                                    </tr>
                                    <tr>
                                        <td>Car Parking</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left">{{ $flat_sale->flat->parking_availability ?? '' }}</td>
                                        <td class="text-center">=</td>
                                        <td class="text-left">{{ number_format($flat_sale->parking) }}/-</td>
                                    </tr>
                                    <tr>
                                        <td>Utility Charge</td>
                                        <td class="text-center">:</td>
                                        <td class="text-left"></td>
                                        <td class="text-center">=</td>
                                        <td class="text-left">{{ number_format($flat_sale->utility) }}/-</td>
                                    </tr>
                                    @if($flat_sale->discount > 0)
                                        <tr>
                                            <td>Discount</td>
                                            <td class="text-center">:</td>
                                            <td class="text-left"></td>
                                            <td class="text-center">=</td>
                                            <td class="text-left">{{ number_format($flat_sale->discount) }}/-</td>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="3" class="text-right"></th>
                                        <th class="text-left" colspan="2">_________________</th>
                                    </tr>
                                    <tr>
                                        <th colspan="3" class="text-right">Total: =</th>
                                        <th class="text-left" colspan="2">{{ number_format($flat_sale->total) }}/-
                                        </th>
                                    </tr>
                                </table>
                                <h5 class="text-center mt-4"><i><b>Taka (in word): {{ $flat_sale->amount_in_word }}
                                            Only.</b></i></h5>
                                <table class="table project-details">
                                    <tr>
                                        <td width="20%">Handover Policy</td>
                                        <td width="1%" class="text-center">:</td>
                                        <td class="text-left">After Full Payment</td>
                                    </tr>
                                    <tr>
                                        <td width="20%">Handover Time</td>
                                        <td width="1%" class="text-center">:</td>
                                        <td class="text-left">{{ \Carbon\Carbon::parse($flat_sale->handover_date)->format('F Y') }}</td>
                                    </tr>
                                    <tr>
                                        <td width="20%">All Materials</td>
                                        <td width="1%" class="text-center">:</td>
                                        <td class="text-left">Company Standard</td>
                                    </tr>
                                </table>

                                <h5 class="ml-2 mt-3 text-center"><u><b>PAYMENT SCHEDULE</b></u></h5>
                                <table class="table table-bordered payment-schedule">
                                    <thead>
                                    <tr>
                                        <th class="text-center">Sl.</th>
                                        <th class="text-center">Date</th>
                                        <th class="text-center">Particulars</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Due Amount</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @php
                                        $beforeInstallmentPayment = ($flat_sale->booking_money + $flat_sale->down_payment);
                                    @endphp
                                    <tr>
                                        <td class="text-center">1.</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($flat_sale->date)->format('d.m.Y') }}</td>
                                        <td class="text-center">Booking Money</td>
                                        <td class="text-right ">{{ number_format($flat_sale->booking_money,2) }}/-</td>
                                        <td class="text-right ">{{ number_format($flat_sale->total - $flat_sale->booking_money,2) }}
                                            /-
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">2.</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($flat_sale->date)->format('d.m.Y') }}</td>
                                        <td class="text-center">Down Payment</td>
                                        <td class="text-right ">{{ number_format($flat_sale->down_payment,2) }}/-</td>
                                        <td class="text-right ">{{ number_format($flat_sale->total - $beforeInstallmentPayment,2) }}
                                            /-
                                        </td>
                                    </tr>

                                    @php
                                        // Set the installment start date
                                        $installmentStartDate = \Carbon\Carbon::parse($flat_sale->installment_start_date); // Example start date
                                        $installment = $flat_sale->installment; // Total number of installments
                                        $installmentEndDate = $installmentStartDate->copy()->addMonths($installment);

                                    @endphp

                                    <tr>
                                        <td class="text-center">3.</td>
                                        <td class="text-center" colspan="2">
                                            {{ $flat_sale->installment }} Installment from
                                            {{ \Carbon\Carbon::parse($flat_sale->installment_start_date)->format('M') }}
                                            '
                                            {{ \Carbon\Carbon::parse($flat_sale->installment_start_date)->format('Y') }}
                                            to
                                            {{ \Carbon\Carbon::parse($installmentEndDate)->format('M') }}'
                                            {{ \Carbon\Carbon::parse($installmentEndDate)->format('Y') }} <br>
                                            {{ $flat_sale->installment }} x {{ $flat_sale->installment_amount }}/-
                                        </td>
                                        <td class="text-right ">{{ number_format($flat_sale->installment * $flat_sale->installment_amount,2) }}
                                            /-
                                        </td>
                                        <td class="text-right ">{{ number_format(($flat_sale->installment * $flat_sale->installment_amount) - ($flat_sale->paid - $beforeInstallmentPayment),2) }}
                                            /-
                                        </td>

                                    </tr>


                                    <tr>
                                        <td class="text-center">4.</td>
                                        <td class="text-center"></td>
                                        <td class="text-center">Rest Amount before Registration</td>
                                        <td class="text-right ">{{ number_format($flat_sale->due,2) }}/-</td>
                                        <td class="text-right ">Nil</td>
                                    </tr>
                                    </tbody>
                                </table>
                                
                                @if($flat_sale->notes)
                                    <div class="row mt-3">
                                        <div class="col-12">
                                            <p class="mb-0" style="font-size: 18px;"><strong>Note:</strong> {{ $flat_sale->notes }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        </div>

                        <div class="row signature-bottom" style="margin-top: 20px !important;">
                            <div class="col-3" style="margin-top: 25px;">
                                <div class="text-center">
                                    <h5 class="bottom-title" style="border-top: 1.8px dashed #000;display: inline;">
                                        Buyer's Signatures</h5>
                                </div>
                            </div>
                            <div class="col-3 offset-6" style="margin-top: 25px">
                                <div class="text-center">
                                    <h5 class="bottom-title" style="border-top: 1.8px dashed #000;display: inline;">
                                        Seller Signatures</h5>
                                </div>
                            </div>
                            <div class="col-12 text-center">
                                <h4 class="footer-address">
                                    <b>Corporate Office:</b> {{ config('app.address') }}
                                    <br>
                                    <span class="contact-info">{{ config('app.contact') }} {{ config('app.email') }}</span>
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';

        function getprint(prinarea) {
            $('body').html($('#' + prinarea).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
