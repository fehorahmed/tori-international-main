<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Market / Area Report</title>
    
    @include('layouts.partial.__styles')

    <style>
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
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
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
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Market_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        <div class="row header-common-info" style="border-bottom: 1.5px solid #000;">
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
                        <div class="row header-common-info mt-3">
                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>Market / Area Report</b></u></h3>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr class="bg-light">
                                <th class="text-center" style="width: 10%">S/L</th>
                                <th class="text-left">Market Name</th>
                                <th class="text-left">Upazila</th>
                                <th class="text-center" style="width: 20%">Status</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($markets as $market)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-left">{{ $market->name }}</td>
                                        <td class="text-left">{{ $market->upazila->name ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($market->status == 1)
                                                Active
                                            @else
                                                Inactive
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-danger">No markets found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var businessDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y') }}';
    </script>
    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
