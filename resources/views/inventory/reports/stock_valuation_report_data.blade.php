<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Stock Valuation Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Stock_Valuation_Report_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header',['report_title' => 'Stock Valuation Report'])
                        
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
                                <th class="text-center" width="5%">SL</th>
                                <th class="text-center">Product Details</th>
                                <th class="text-center" width="10%">Closing</th>
                                <th class="text-center" width="15%">Avg. Unit Price</th>
                                <th class="text-center" width="15%">Total Price</th>
                                <th class="text-center" width="15%">Remark</th>
                            </tr>
                            </thead>
                            <tbody>
                                @php
                                    $grandTotalQty = 0;
                                    $grandTotalValuation = 0;
                                @endphp
                                @forelse($inventories as $categoryName => $items)
                                    <!-- Category Row -->
                                    <tr style="background-color: #dcedc8;">
                                        <th colspan="6" class="text-center">{{ $categoryName }}</th>
                                    </tr>
                                    
                                    @php
                                        $categoryTotalQty = 0;
                                        $categoryTotalValuation = 0;
                                    @endphp
                                    
                                    @foreach($items as $inv)
                                        @if($inv->product)
                                            @php
                                                $valuation = $inv->total_valuation ?? 0;
                                                $categoryTotalQty += $inv->total_quantity;
                                                $categoryTotalValuation += $valuation;
                                                
                                                $avgUnitPrice = $inv->total_quantity > 0 ? ($valuation / $inv->total_quantity) : 0;
                                            @endphp
                                            <tr>
                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                <td>{{ $inv->product->name ?? '' }}</td>
                                                <td class="text-center">{{ number_format($inv->total_quantity) }}</td>
                                                <td class="text-center">{{ number_format($avgUnitPrice, 2) }}</td>
                                                <td class="text-right">{{ number_format($valuation, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                    
                                    <!-- Category Total Row -->
                                    <tr>
                                        <th colspan="2" class="text-center text-danger">TOTAL:</th>
                                        <th class="text-center text-danger">{{ number_format($categoryTotalQty) }}</th>
                                        <th class="text-center"></th>
                                        <th class="text-right text-danger">{{ number_format($categoryTotalValuation, 2) }}</th>
                                        <th></th>
                                    </tr>
                                    
                                    @php
                                        $grandTotalQty += $categoryTotalQty;
                                        $grandTotalValuation += $categoryTotalValuation;
                                    @endphp
                                    
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No Stock Found</td>
                                    </tr>
                                @endforelse
                                
                                @if(count($inventories) > 0)
                                <!-- Grand Total Row -->
                                <tr style="background-color: #f8f9fa;">
                                    <th colspan="2" class="text-right">GRAND TOTAL:</th>
                                    <th class="text-center">{{ number_format($grandTotalQty) }}</th>
                                    <th class="text-center"></th>
                                    <th class="text-right">{{ number_format($grandTotalValuation, 2) }}</th>
                                    <th></th>
                                </tr>
                                @endif
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
