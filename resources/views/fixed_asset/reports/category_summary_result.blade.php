<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fixed Asset Category-wise Summary Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Fixed_Asset_Category_Summary_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Fixed Asset Category-wise Summary Report</b></u></h3>
                                <h4 class="text-center" style="font-size: 20px">Branch: {{ auth()->user()->activeSisterConcern->name ?? config('app.name') }}</h4>
                                <h5 class="text-center" style="font-size: 18px">Generated on: {{ now()->format('d-M-Y H:i A') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Category Name</th>
                                    <th class="text-center">Total Quantity</th>
                                    <th class="text-right">Total Purchase Cost</th>
                                    <th class="text-right">Accumulated Dep.</th>
                                    <th class="text-right">Net Book Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $grandQty = 0;
                                    $grandCost = 0;
                                    $grandDep = 0;
                                    $grandBook = 0;
                                @endphp
                                @foreach($summary as $item)
                                @php 
                                    $grandQty += $item['total_count'];
                                    $grandCost += $item['total_cost'];
                                    $grandDep += $item['accumulated_depreciation'];
                                    $grandBook += $item['book_value'];
                                @endphp
                                <tr>
                                    <td class="text-left font-weight-bold">{{ $item['category_name'] }}</td>
                                    <td class="text-center">{{ $item['total_count'] }}</td>
                                    <td class="text-right">{{ number_format($item['total_cost'], 2) }}</td>
                                    <td class="text-right">{{ number_format($item['accumulated_depreciation'], 2) }}</td>
                                    <td class="text-right">{{ number_format($item['book_value'], 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th class="text-right">Grand Total:</th>
                                    <th class="text-center">{{ $grandQty }}</th>
                                    <th class="text-right">{{ number_format($grandCost, 2) }}</th>
                                    <th class="text-right">{{ number_format($grandDep, 2) }}</th>
                                    <th class="text-right">{{ number_format($grandBook, 2) }}</th>
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
