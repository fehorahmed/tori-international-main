<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contractor Bill Summary Report</title>
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Bill_Summary_' . date('d_m_Y')])
                    
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="header-common-info mb-3">
                            <div>
                                <h3 class="text-center" style="font-size: 25px"><u><b>Contractor Bill Summary</b></u></h3>
                                <h5 class="text-center" style="font-size: 16px">From: {{ date('d-m-Y', strtotime($start_date)) }} To: {{ date('d-m-Y', strtotime($end_date)) }}</h5>
                            </div>
                        </div>
                        
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center" width="8%">S/L</th>
                                    <th>Date</th>
                                    <th>Bill No</th>
                                    <th>Contractor Name</th>
                                    <th>Note</th>
                                    <th class="text-right" width="15%">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotal = 0; @endphp
                                @foreach($bills as $index => $bill)
                                    @php $grandTotal += $bill->total_amount; @endphp
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ date('d-m-Y', strtotime($bill->bill_date)) }}</td>
                                        <td>{{ $bill->bill_no }}</td>
                                        <td>{{ $bill->contractor->name ?? 'N/A' }}</td>
                                        <td>{{ $bill->note }}</td>
                                        <td class="text-right">{{ number_format($bill->total_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="5" class="text-right">Grand Total:</th>
                                    <th class="text-right">{{ number_format($grandTotal, 2) }}</th>
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
