<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Purchase Return Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Purchase_Return_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="text-center mb-4 mt-2">
                            <h4 style="text-decoration: underline;">Purchase Return Report</h4>
                            <p class="text-muted">From {{ request('start_date') }} to {{ request('end_date') }}</p>
                        </div>

                        <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr class="bg-light">
                <th>S/L</th>
                <th>Date</th>
                <th>Return No</th>
                <th>Supplier</th>
                <th>Warehouse</th>

                <th class="text-right">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @php 
                $total_sum = 0; 
            @endphp
            @forelse($returns as $return)
                @php
                    $total_sum += $return->total_return_value;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d-m-Y') }}</td>
                    <td>{{ $return->return_no }}</td>
                    <td>{{ $return->supplier->name ?? 'N/A' }}</td>
                    <td>{{ $return->returnWarehouse->name ?? 'N/A' }}</td>

                    <td class="text-right font-weight-bold">৳ {{ number_format($return->total_return_value, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">No purchase return found for the selected criteria.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr class="bg-light">
                <th colspan="5" class="text-right">Total:</th>
                <th class="text-right font-weight-bold text-danger" style="font-size: 1.1rem;">৳ {{ number_format($total_sum, 2) }}</th>
            </tr>
        </tfoot>
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
