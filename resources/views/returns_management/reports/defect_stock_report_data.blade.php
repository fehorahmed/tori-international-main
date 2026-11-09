<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Defect / Damage Stock Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th { vertical-align: middle; border: 1px solid #000!important; padding: 4px; }
        .table-bordered td, .table-bordered th { border: 1px solid #000000!important; vertical-align: middle; padding: 4px; }
        @page { margin: .3in .5in .5in .5in; }
        body { background-color: #f4f6f9; }
        .report-container { background: #fff; padding: 1.5rem; box-shadow: 0 0 10px rgba(0,0,0,0.1); min-height: 100vh; }
        .table th, .table td { font-size: 13px; padding: 0.4rem; }
        @media print {
            .report-container { margin: 0; padding: 0; box-shadow: none; }
            body { background-color: #fff; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Defect_Stock_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="text-center mb-4 mt-2">
                            <h4 style="text-decoration: underline;">Defect / Damage Stock Report</h4>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr class="bg-light">
                                        <th class="text-center">Sl</th>
                                        <th class="text-center">Product Name</th>
                                        <th class="text-center">Category</th>
                                        <th class="text-center">Warehouse</th>
                                        <th class="text-center">Batch No</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-right">Unit Price</th>
                                        <th class="text-right">Total Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $totalStock = 0;
                                        $totalValue = 0;
                                    @endphp
                                    @forelse($stocks as $index => $stock)
                                        @php
                                            $value = $stock->quantity * $stock->unit_price;
                                            $totalStock += $stock->quantity;
                                            $totalValue += $value;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>{{ $stock->product->name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $stock->product->category->name ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $stock->warehouse->name ?? 'N/A' }}</td>
                                            <td class="text-center">Batch-{{ $stock->batch_no }}</td>
                                            <td class="text-center font-weight-bold text-danger">{{ $stock->quantity }} <small>{{ $stock->product->unit->name ?? '' }}</small></td>
                                            <td class="text-right">{{ number_format($stock->unit_price, 2) }}</td>
                                            <td class="text-right">{{ number_format($value, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-danger">No defect stock found for the selected criteria.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(count($stocks) > 0)
                                <tfoot>
                                    <tr class="bg-light">
                                        <th colspan="5" class="text-right">Total:</th>
                                        <th class="text-center text-danger">{{ number_format($totalStock, 2) }}</th>
                                        <th></th>
                                        <th class="text-right text-danger">{{ number_format($totalValue, 2) }}</th>
                                    </tr>
                                </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
