<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sale Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Sale_Report_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        <div class="row mx-0 header-common-info" style="border-bottom: 1.5px solid #000; margin-bottom: 10px;">
                            <div class="col-4">
                                <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
                                    <img height="110px" src="{{ asset('img/logo.png') }}" alt="">
                                </h1>
                            </div>
                            <div class="col-8 text-right">
                                <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                <h5 class="m-0">{{ config('app.address') }}</h5>
                                <h5 class="m-0">{{ config('app.contact') }}</h5>
                                <h5>{{ config('app.email') }}</h5>
                            </div>
                        </div>
                        <div class="row mx-0 header-common-info">
                            <div class="col-12 text-center mb-3">
                                <h3 style="font-size: 25px"><u><b>Sale Report</b></u></h3>
                                <h5><b>Branch:</b> {{ auth()->user()->activeSisterConcern->name ?? auth()->user()->sisterConcern->name ?? 'N/A' }}</h5>
                                <h5 style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr class="bg-light">
                                <th class="text-center">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Order No</th>
                                <th class="text-center">Customer</th>
                                <th class="text-center">Sub Total</th>
                                <th class="text-center">Discount</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Paid</th>
                                <th class="text-center">Due</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($orders as $order)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($order->date)->format('d-m-Y') }}</td>
                                        <td class="text-center"><a target="_blank" href="{{ route('sales.details',['sale'=>$order->id]) }}">{{ $order->order_no }}</a></td>
                                        <td class="text-center">{{ $order->customer->name??'' }}</td>
                                        <td class="text-right">{{ number_format($order->sub_total,2) }}</td>
                                        <td class="text-right">{{ number_format($order->discount,2) }}</td>
                                        <td class="text-right">{{ number_format($order->total,2) }}</td>
                                        <td class="text-right">{{ number_format($order->paid,2) }}</td>
                                        <td class="text-right">{{ number_format($order->due,2) }}</td>
                                    </tr>
                                @endforeach
                                <tr>
                                    <th colspan="4" class="text-right">Total</th>
                                    <th class="text-right">{{ number_format($orders->sum('sub_total'),2) }}</th>
                                    <th class="text-right">{{ number_format($orders->sum('discount'),2) }}</th>
                                    <th class="text-right">{{ number_format($orders->sum('total')-$orders->sum('vat_amount'),2) }}</th>
                                    <th class="text-right">{{ number_format($orders->sum('paid'),2) }}</th>
                                    <th class="text-right">{{ number_format($orders->sum('due'),2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var businessDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y') }}';
    </script>
    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
