@extends('layouts.app')
@section('title','Route-wise Leads & Shops')
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
                    <h3 class="card-title">Filter by Route</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.route_leads') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4 form-group">
                                <label for="market_id">Select Route:</label>
                                <select name="market_id" id="market_id" class="form-control select2">
                                    <option value="">-- All Routes --</option>
                                    @foreach($markets as $market)
                                        <option value="{{ $market->id }}" {{ $selectedMarketId == $market->id ? 'selected' : '' }}>
                                            {{ $market->name }} ({{ $market->upazila->name ?? '' }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <button type="submit" class="btn btn-primary bg-gradient-primary"><i class="fa fa-search"></i> View Leads</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if($selectedMarketId)
            @php
                $selectedMarketName = $markets->firstWhere('id', $selectedMarketId)->name ?? 'Unknown';
            @endphp
            <div class="card card-default mt-3">
                <div class="card-header">
                    <a role="button" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-print"></i> Print</a>
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Route-wise Leads & Shops List</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Route: <strong>{{ $selectedMarketName }}</strong></h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">S/L</th>
                                    <th class="text-center">Name</th>
                                    <th class="text-center">Contact No</th>
                                    <th class="text-center">Address</th>
                                    <th class="text-center">Source</th>
                                    <th class="text-center">Created At</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clients as $index => $client)
                                <tr>
                                    <td class="text-center">{{ $index + 1 }}</td>
                                    <td>{{ $client->name }}</td>
                                    <td class="text-center">{{ $client->contact_no ?? 'N/A' }}</td>
                                    <td>
                                        <small>
                                        {{ $client->village->name ?? '' }}
                                        {{ $client->union->name ?? '' }}
                                        {{ $client->upazila->name ?? '' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        @if($client->source == 1)
                                            <span>CRM/Office</span>
                                        @else
                                            <span>SR Field</span>
                                        @endif
                                    </td>
                                    <td class="text-center">{{ $client->created_at->format('d-m-Y h:i A') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No leads or shops found on this route.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
@endsection
@section('script')
<script>
    var APP_URL = '{!! url()->full()  !!}';
    function getprint(print) {
        document.title = "Route_Wise_Leads_{{ date('Y_m_d') }}";
        $(".extra-remove").remove();
        $(".footer-print-area").show();
        $('body').html($('#' + print).html());
        window.print();
        window.location.replace(APP_URL)
    }
</script>
@endsection
