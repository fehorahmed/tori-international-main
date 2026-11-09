@extends('layouts.app')
@section('title', 'Product Utilize Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title"><i class="fa fa-filter text-info mr-1"></i> Report Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('product-utilizes.report') }}" method="GET">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date</label>
                                    <input autocomplete="off" type="text" value="{{ request('start_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date</label>
                                    <input autocomplete="off" type="text" value="{{ request('end_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        @if(request()->filled('start_date') && count($reports) > 0)
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-primary bg-gradient-primary btn-sm"><i class="fa fa-print"></i> Print Report</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="row header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Product Utilize Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date Range: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered table-sm mt-3">
                            <thead>
                                <tr class="bg-light">
                                    <th class="text-center" style="width: 5%">S/L</th>
                                    <th class="text-center" style="width: 15%">Date</th>
                                    <th class="text-center" style="width: 20%">Order No</th>
                                    <th class="text-center" style="width: 20%">Warehouse</th>
                                    <th class="text-center" style="width: 20%">Products Utilized</th>
                                    <th class="text-right" style="width: 10%">Total Quantity</th>
                                    <th class="text-right" style="width: 10%">Total Cost</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotalCost = 0; $grandTotalQty = 0; @endphp
                                @foreach($reports as $report)
                                    @php
                                        $totalQty = $report->productUtilizeItems->sum('quantity');
                                        $totalCost = $report->productUtilizeItems->sum(function($item) {
                                            return $item->quantity * $item->unit_price;
                                        });
                                        $grandTotalQty += $totalQty;
                                        $grandTotalCost += $totalCost;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($report->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">
                                            <a target="_blank" href="{{ route('product-utilizes.details', ['product_utilize' => $report->id]) }}">
                                                {{ $report->order_no }}
                                            </a>
                                        </td>
                                        <td class="text-center">{{ $report->warehouse->name ?? 'N/A' }}</td>
                                        <td class="text-left">
                                            @foreach($report->productUtilizeItems as $item)
                                                <div>- {{ $item->product->name ?? 'N/A' }} ({{ $item->quantity }})</div>
                                            @endforeach
                                        </td>
                                        <td class="text-right">{{ $totalQty }}</td>
                                        <td class="text-right">{{ number_format($totalCost, 2) }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-light">
                                    <th colspan="5" class="text-right">Grand Total</th>
                                    <th class="text-right">{{ $grandTotalQty }}</th>
                                    <th class="text-right">{{ number_format($grandTotalCost, 2) }}</th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @elseif(request()->filled('start_date'))
        <div class="col-12">
            <div class="card card-default">
                <div class="card-body text-center py-4">
                    <h5 class="text-muted m-0"><i class="fa fa-info-circle"></i> No records found matching the selected filters.</h5>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "Product_Utilize_Report_{{ request('start_date') }}_to_{{ request('end_date') }}";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL);
        }
    </script>
@endsection
