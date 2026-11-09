<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Outlet Setup Report</title>
    
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
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Outlet_Setup_Report_' . date('d_m_Y')])
                    
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Outlet Setup Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr class="bg-light">
                                <th class="text-center">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">Lead No.</th>
                                <th class="text-center">Image</th>
                                <th>Client</th>
                                <th class="text-center">Contact No.</th>
                                <th>Outlet Type</th>
                                <th>Market</th>
                                <th style="width: 20%">Address</th>
                                <th>Created By</th>
                                <th>Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                                @forelse($leads as $lead)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($lead->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">{{ $lead->order_no }}</td>
                                        <td class="text-center">
                                            {!! renderZoomGalleryImage($lead->crmClient->image ?? null, $lead->crmClient->name ?? '') !!}
                                        </td>
                                        <td>{{ $lead->crmClient->name??'' }}</td>
                                        <td class="text-center">{{ $lead->crmClient->contact_no??'' }}</td>
                                        <td>{{ $lead->crmClient->customerType->name ?? 'N/A' }}</td>
                                        <td>{{ $lead->crmClient->market->name ?? 'N/A' }}</td>
                                        <td>
                                            @php
                                                $client = $lead->crmClient;
                                                $addr = [];
                                                if ($client) {
                                                    if ($client->market) $addr[] = $client->market->name;
                                                    if ($client->village) $addr[] = $client->village->name;
                                                    if ($client->union) $addr[] = $client->union->name;
                                                    if ($client->pourashava) $addr[] = $client->pourashava->name;
                                                    if ($client->upazila) $addr[] = $client->upazila->name;
                                                    if ($client->district) $addr[] = $client->district->name;
                                                    if ($client->division) $addr[] = $client->division->name;
                                                }
                                            @endphp
                                            {{ !empty($addr) ? implode(', ', array_filter($addr)) : 'N/A' }}
                                            @if($client && $client->lat && $client->long)
                                                <br>
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $client->lat }},{{ $client->long }}" target="_blank" class="btn btn-xs btn-outline-danger mt-1 extra-remove" title="View on Map">
                                                    <i class="fas fa-map-marker-alt"></i> Map
                                                </a>
                                            @endif
                                        </td>
                                        <td>{{ $lead->user->name ?? 'N/A' }}</td>
                                        <td>{{ $lead->notes }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-danger">No leads found</td>
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
