<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Batch-wise Stock Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Batch_Stock_Report_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header',['report_title' => 'Batch-wise Stock Report'])
                        
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                @if(isset($selectedWh))
                                    <h5 class="m-0">Warehouse: <b>{{ $selectedWh->name ?? '' }}</b></h5>
                                @endif
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Batch</th>
                                <th class="text-center">Warehouse</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Code</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-center">DP Price</th>
                                <th class="text-center">MRP Price</th>
                                <th class="text-center">Total DP Value</th>
                                <th class="text-center">Total MRP Value</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                    $totalQty = 0;
                                    $totalDpVal = 0;
                                    $totalMrpVal = 0;
                                @endphp
                                @forelse($inventories as $inv)
                                    @if($inv->product)
                                        @php
                                            $dpVal = $inv->quantity * $inv->wholesale_price;
                                            $mrpVal = $inv->quantity * $inv->selling_price;
                                            
                                            $totalQty += $inv->quantity;
                                            $totalDpVal += $dpVal;
                                            $totalMrpVal += $mrpVal;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $loop->iteration }}</td>
                                            <td class="text-center">Batch-{{ $inv->batch_no }}</td>
                                            <td class="text-center">{{ $inv->warehouse->name ?? '' }}</td>
                                            <td>{{ $inv->product->name ?? '' }}</td>
                                            <td class="text-center">{{ $inv->product_code ?? '' }}</td>
                                            <td class="text-center">{{ $inv->product->unit->name ?? '' }}</td>
                                            <td class="text-center"><strong>{{ number_format($inv->quantity) }}</strong></td>
                                            <td class="text-right">{{ number_format($inv->wholesale_price, 2) }}</td>
                                            <td class="text-right">{{ number_format($inv->selling_price, 2) }}</td>
                                            <td class="text-right">{{ number_format($dpVal, 2) }}</td>
                                            <td class="text-right">{{ number_format($mrpVal, 2) }}</td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center">No Stock Found</td>
                                    </tr>
                                @endforelse
                                <tr>
                                    <th colspan="6" class="text-right">Total</th>
                                    <th class="text-center">{{ number_format($totalQty) }}</th>
                                    <th colspan="2" class="text-right"></th>
                                    <th class="text-right">{{ number_format($totalDpVal, 2) }}</th>
                                    <th class="text-right">{{ number_format($totalMrpVal, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
