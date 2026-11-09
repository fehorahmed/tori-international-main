<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sub-Category Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 6px 8px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 6px 8px;
        }
        .category-group-header {
            background-color: #f4f6f9 !important;
            border-bottom: 2px solid #000000 !important;
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Sub_Category_Report_' . date('d_m_Y')])
                    <div id="printArea">
                        <!-- Common Company Header -->
                        <div class="row mx-0 header-common-info" style="border-bottom: 1.5px solid #000; padding-bottom: 10px; margin-bottom: 10px;">
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
                            <div class="col-12 text-center">
                                <h3 class="text-center" style="font-size: 25px; margin-top: 10px; margin-bottom: 5px;"><u><b>Sub-Category Report</b></u></h3>
                                @if(request('status') !== null && request('status') !== '')
                                    <h5 class="text-center" style="font-size: 16px">Status: {{ request('status') == 1 ? 'Active' : 'Inactive' }}</h5>
                                @endif
                            </div>
                        </div>

                        <!-- 1. Category Summary List -->
                        <div class="row mx-0 mt-4">
                            <div class="col-12">
                                <h4 class="mb-3 font-weight-bold">📁 Category-wise Sub-Categories Report</h4>
                                
                                @php $hasData = false; @endphp
                                @foreach($reportCategories as $reportCat)
                                    @if($reportCat->subCategories->isNotEmpty())
                                        @php $hasData = true; @endphp
                                        <div class="card card-outline card-secondary mb-4" style="border: 1px solid #000;">
                                            <div class="card-header category-group-header p-2">
                                                <h5 class="m-0 font-weight-bold">
                                                    Category: <span class="text-primary font-weight-bold">{{ $reportCat->name }}</span>
                                                    <span class="float-right text-dark" style="font-size: 15px;">
                                                        Total Sub-Categories: <b>{{ $reportCat->subCategories->count() }}</b>
                                                    </span>
                                                </h5>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-bordered m-0" style="width: 100%;">
                                                    <thead>
                                                    <tr class="bg-light text-center">
                                                        <th class="text-center" style="width: 8%">S/L</th>
                                                        <th>Sub-Category Name</th>
                                                        <th style="width: 25%">Products Count</th>
                                                        <th style="width: 20%">Status</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($reportCat->subCategories as $subCat)
                                                            <tr>
                                                                <td class="text-center">{{ $loop->iteration }}</td>
                                                                <td><b>{{ $subCat->name }}</b></td>
                                                                <td class="font-weight-bold text-secondary">{{ $subCat->products_count }}</td>
                                                                <td>
                                                                    <span class="badge {{ $subCat->status == 1 ? 'badge-success' : 'badge-danger' }}">
                                                                        {{ $subCat->status == 1 ? 'Active' : 'Inactive' }}
                                                                    </span>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach

                                @if(!$hasData)
                                    <div class="alert alert-warning text-center mt-3">
                                        No categories or sub-categories found matching the criteria.
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
