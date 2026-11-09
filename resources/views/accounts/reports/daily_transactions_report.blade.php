<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daily Transactions Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td,
        .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000 !important;
            padding: 4px;
        }

        .table-bordered td,
        .table-bordered th {
            border: 1px solid #000000 !important;
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
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            min-height: 100vh;
        }

        .table th,
        .table td {
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Daily_Transactions_' . date('d_m_Y')])

                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Daily Transactions Report</b></u>
                                </h3>
                                <h5 class="text-center" style="font-size: 18px">Date:
                                    {{ \Carbon\Carbon::parse($startDate)->format('d-m-Y') }} to
                                    {{ \Carbon\Carbon::parse($endDate)->format('d-m-Y') }}
                                </h5>
                            </div>
                        </div>
                        <div class="row mt-4">
                            <div class="col-md-12">
                                <table class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 5%">S/L</th>
                                            <th>Particulars</th>
                                            <th class="text-right">Sales Value</th>
                                            <th class="text-right">Purchase Value</th>
                                            <th class="text-right">Stock Transfer</th>
                                            <th class="text-right">Payment (Out)</th>
                                            <th class="text-right">Receipt (In)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $sl = 1;
                                            $totalSales = 0;
                                            $totalPurchase = 0;
                                            $totalTransfer = 0;
                                            $totalReceipt = 0;
                                            $totalPayment = 0;
                                        @endphp
                                        @foreach($summary as $row)
                                            @php
                                                $totalSales += $row['sales'] ?? 0;
                                                $totalPurchase += $row['purchase'] ?? 0;
                                                $totalTransfer += $row['transfer'] ?? 0;
                                                $totalReceipt += $row['receipt'] ?? 0;
                                                $totalPayment += $row['payment'] ?? 0;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $sl++ }}</td>
                                                <td>
                                                    <a
                                                        href="{{ route('report.daily_transactions.details', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'branch_id' => request('branch_id'), 'payment_purpose' => $row['id']]) }}">
                                                        {{ $row['name'] }} <span
                                                            class="badge badge-info">{{ $row['count'] }}</span>
                                                    </a>
                                                    @if(isset($row['orders']) && count($row['orders']) > 0)
                                                        <ul class="list-unstyled mb-0 mt-2" style="font-size: 11px;">
                                                            @foreach($row['orders'] as $order)
                                                                <li>- {!! $order !!}</li>
                                                            @endforeach
                                                        </ul>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    {{ ($row['sales'] ?? 0) > 0 ? number_format($row['sales'], 2) : '-' }}
                                                </td>
                                                <td class="text-right">
                                                    {{ ($row['purchase'] ?? 0) > 0 ? number_format($row['purchase'], 2) : '-' }}
                                                </td>
                                                <td class="text-right">
                                                    {{ ($row['transfer'] ?? 0) > 0 ? number_format($row['transfer'], 2) : '-' }}
                                                </td>
                                                <td class="text-right">
                                                    {{ ($row['payment'] ?? 0) > 0 ? number_format($row['payment'], 2) : '-' }}
                                                </td>
                                                <td class="text-right">
                                                    {{ ($row['receipt'] ?? 0) > 0 ? number_format($row['receipt'], 2) : '-' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th colspan="2" class="text-right">Total</th>
                                            <th class="text-right">{{ number_format($totalSales, 2) }}</th>
                                            <th class="text-right">{{ number_format($totalPurchase, 2) }}</th>
                                            <th class="text-right">{{ number_format($totalTransfer, 2) }}</th>
                                            <th class="text-right">{{ number_format($totalPayment, 2) }}</th>
                                            <th class="text-right">{{ number_format($totalReceipt, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>

</html>