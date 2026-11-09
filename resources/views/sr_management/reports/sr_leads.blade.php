@extends('layouts.app')
@section('title','SR Lead Report')
@section('style')
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
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.sr_leads') }}">
                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="sr_id">SR</label>
                                    <select name="sr_id" id="sr_id" class="form-control select2">
                                        <option value="">All SRs</option>
                                        @foreach($srs as $sr)
                                            <option {{ request('sr_id') == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if(count($leads) > 0)
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-print"></i></a>
                </div>
                <div class="card-body">
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
                        <div class="row header-common-info">

                            <div class="col-12">
                                <h3 class="text-center" style="font-size: 25px"><u><b>SR Lead Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">SR</th>
                                <th class="text-center">Lead No.</th>
                                <th class="text-center">Image</th>
                                <th class="text-center">Client</th>
                                <th class="text-center">Contact No.</th>

                                <th class="text-center" style="width: 25%">Address</th>
                                <th class="text-center">Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($leads as $lead)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($lead->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">{{ $lead->sr->name??'' }}</td>
                                        <td class="text-center">{{ $lead->order_no }}</td>
                                        <td class="text-center">
                                            {!! renderZoomGalleryImage($lead->crmClient->image ?? null, $lead->crmClient->name ?? '') !!}
                                        </td>
                                        <td class="text-center">{{ $lead->crmClient->name??'' }}</td>
                                        <td class="text-center">{{ $lead->crmClient->contact_no??'' }}</td>

                                        <td class="text-left">
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
                                                <a href="https://www.google.com/maps/search/?api=1&query={{ $client->lat }},{{ $client->long }}" target="_blank" class="btn btn-xs btn-outline-danger mt-1" title="View on Map">
                                                    <i class="fas fa-map-marker-alt"></i> Map
                                                </a>
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $lead->notes }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
            document.title = "{{ request('start_date') }}_to_{{ request('end_date') }}_sr_lead_report";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
