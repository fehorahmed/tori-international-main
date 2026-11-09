<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Report</title>
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Product_Report_' . date('d_m_Y')])
                    <div class="table-responsive" id="printArea">
                        <div class="row mx-0 header-common-info" style="border-bottom: 1.5px solid #000;">
                            <div class="col-4">
                                <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
                                    <img height="110px" src="{{ asset('img/logo.png') }}" alt="">
                                </h1>
                            </div>
                            <div class="col-8 text-right">
                                <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
                                <h5 class="m-0">{{ config('app.address') }}</h5>
                                <h5 class="m-0">{{ config('app.contact') }}</h5>
                                <h5>{{ config('app.email') }}</h5>
                            </div>
                        </div>
                        <div class="row mx-0 header-common-info">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px; margin-top: 10px;"><u><b>Product Report</b></u></h3>
                                @if(request('status') !== null && request('status') !== '')
                                    <h5 class="text-center" style="font-size: 16px">Status: {{ request('status') == 1 ? 'Active' : 'Inactive' }}</h5>
                                @endif
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 5%">S/L</th>
                                <th style="width: 15%">Product Code</th>
                                <th style="width: 25%">Product Name</th>
                                <th style="width: 15%">Category</th>
                                <th style="width: 15%">Sub-Category</th>
                                <th style="width: 10%">Brand</th>
                                <th style="width: 8%">Unit</th>
                                <th style="width: 7%">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="font-weight-bold">{{ $product->code }}</td>
                                        <td>{{ $product->name }}</td>
                                        <td>{{ $product->category->name ?? '-' }}</td>
                                        <td>{{ $product->subcategory->name ?? '-' }}</td>
                                        <td>{{ $product->brand->name ?? '-' }}</td>
                                        <td>{{ $product->unit->name ?? '-' }}</td>
                                        <td>
                                            <span class="badge {{ $product->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                {{ $product->status == 1 ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No products found.</td>
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
