<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Customer Due Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Customer_Due_Report_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        <div class="row mx-0 header-common-info" style="border-bottom: 1.5px solid #000;">
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
                            <div class="col-12 text-center">
                                <h3 class="text-center" style="font-size: 25px; margin-top: 10px; margin-bottom: 5px;"><u><b>Customer Due Report</b></u></h3>
                                <h5 class="text-center m-0" style="font-size: 18px"><b>Branch:</b> {{ auth()->user()->sisterConcern->name ?? auth()->user()->activeSisterConcern->name ?? '' }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                                <th class="text-center" style="width: 5%">S/L</th>
                                <th class="text-center" style="width: 25%">Customer Name</th>
                                <th class="text-center" style="width: 15%">Address</th>
                                <th class="text-center" style="width: 12%">Opening Due</th>
                                <th class="text-center" style="width: 15%">Total Sale</th>
                                <th class="text-center" style="width: 15%">Total Paid</th>
                                <th class="text-center" style="width: 13%">Current Due</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalOpening = 0;
                                    $totalSale = 0;
                                    $totalPaid = 0;
                                    $totalCurrent = 0;
                                @endphp
                                @forelse($customers as $customer)
                                    @php
                                        // Filter only customers that have some due or transactions
                                        if ($customer->opening_balance == 0 && $customer->outstanding_balance == 0 && $customer->total_sales == 0) {
                                            // Optional: Continue if you only want to show due > 0
                                            // continue;
                                        }
                                        $totalOpening += $customer->real_opening_balance ?? 0;
                                        $totalSale += $customer->total_sales ?? 0;
                                        $totalPaid += $customer->total_paid ?? 0;
                                        $totalCurrent += $customer->real_current_balance;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>
                                            <b>{{ $customer->name }}</b><br>
                                            <small>{{ $customer->mobile_no ?? '-' }}</small>
                                        </td>
                                        <td>{{ $customer->village ? $customer->village->name . ', ' : '' }}{{ $customer->upazila->name ?? '' }}</td>
                                        <td class="text-right">{{ number_format($customer->real_opening_balance ?? 0, 2) }}</td>
                                        <td class="text-right">{{ number_format($customer->total_sales ?? 0, 2) }}</td>
                                        <td class="text-right">{{ number_format($customer->total_paid ?? 0, 2) }}</td>
                                        <td class="text-right">
                                            @if($customer->real_current_balance < 0)
                                                <span class="text-success">Adv: {{ number_format(abs($customer->real_current_balance), 2) }}</span>
                                            @else
                                                <span class="text-danger">{{ number_format($customer->real_current_balance, 2) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-danger">No customers found.</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <th colspan="3" class="text-right">Total:</th>
                                    <th class="text-right">{{ number_format($totalOpening, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalSale, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalPaid, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalCurrent, 2) }}</th>
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
