<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Price Book History Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Price_Book_History_Report_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header', ['report_title' => 'Price Book History Report'])
                        <div class="row mx-0 header-common-info">
                            <div class="col-12 text-center">
                                @if(request('start_date') && request('end_date'))
                                    <h5 class="m-0">Date: <b>{{ request('start_date') }} to {{ request('end_date') }}</b></h5>
                                @endif
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th>Effective Date</th>
                                <th>Product Name</th>
                                <th>Product Code</th>
                                <th class="text-right">Old DP Price</th>
                                <th class="text-right">New DP Price</th>
                                <th class="text-right">Old MRP Price</th>
                                <th class="text-right">New MRP Price</th>
                                <th>Updated By</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($histories as $history)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $history->effective_date ? \Carbon\Carbon::parse($history->effective_date)->format('d-m-Y') : '' }}</td>
                                        <td>{{ $history->product->name ?? '' }}</td>
                                        <td>{{ $history->product->code ?? '' }}</td>
                                        
                                        <td class="text-right text-muted"><del>{{ number_format($history->old_wholesale_price, 2) }}</del></td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($history->new_wholesale_price, 2) }}</td>
                                        
                                        <td class="text-right text-muted"><del>{{ number_format($history->old_selling_price, 2) }}</del></td>
                                        <td class="text-right font-weight-bold text-success">{{ number_format($history->new_selling_price, 2) }}</td>
                                        
                                        <td>{{ $history->creator->name ?? '' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center">No Price History Found</td>
                                    </tr>
                                @endforelse
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
