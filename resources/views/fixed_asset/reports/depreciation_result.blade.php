<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fixed Asset Depreciation Schedule</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Fixed_Asset_Depreciation_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Fixed Asset Depreciation Schedule</b></u></h3>
                                <h4 class="text-center" style="font-size: 20px">Branch: {{ auth()->user()->activeSisterConcern->name ?? config('app.name') }}</h4>
                                <h5 class="text-center" style="font-size: 18px">Generated on: {{ now()->format('d-M-Y H:i A') }}</h5>
                            </div>
                        </div>
                        <table id="table" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Code</th>
                                    <th class="text-left">Asset Name</th>
                                    <th class="text-center">Category</th>
                                    <th class="text-center">Purchase Date</th>
                                    <th class="text-right">Accumulated Dep.</th>
                                    <th class="text-right">Purchase Cost</th>
                                    <th class="text-right">Net Book Value</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $totalCost = 0;
                                    $totalBookValue = 0;
                                    $totalDep = 0;
                                @endphp
                                @foreach($assets as $asset)
                                @php 
                                    $totalCost += $asset->total;
                                    $totalBookValue += $asset->book_value;
                                    $totalDep += $asset->accumulated_depreciation;
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $asset->asset_code }}</td>
                                    <td class="text-left">{{ $asset->name }}</td>
                                    <td class="text-center">{{ $asset->category->name ?? '-' }}</td>
                                    <td class="text-center">{{ $asset->date->format('d-m-Y') }}</td>
                                    <td class="text-right">{{ number_format($asset->accumulated_depreciation, 2) }}</td>
                                    <td class="text-right">{{ number_format($asset->total, 2) }}</td>
                                    <td class="text-right">{{ number_format($asset->book_value, 2) }}</td>
                                    <td class="text-center">{{ ucfirst($asset->status) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="4" class="text-right">Total:</th>
                                    <th class="text-right">{{ number_format($totalDep, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalCost, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalBookValue, 2) }}</th>
                                    <th></th>
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
