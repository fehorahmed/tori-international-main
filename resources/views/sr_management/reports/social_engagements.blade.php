@extends('layouts.app')
@section('title','SR Social Engagements Report')
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
        .visit-img {
            max-width: 80px;
            border-radius: 4px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
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
                    <form action="{{ route('report.sr_social_engagements') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sr_id">SR</label>
                                    <select name="sr_id" id="sr_id" class="form-control select2">
                                        <option value="">All SR</option>
                                        @foreach($srs as $sr)
                                            <option {{ request('sr_id') == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="platform">Platform</label>
                                    <select name="platform" id="platform" class="form-control select2">
                                        <option value="">All Platforms</option>
                                        <option {{ request('platform') == 'youtube' ? 'selected' : '' }} value="youtube">YouTube</option>
                                        <option {{ request('platform') == 'facebook' ? 'selected' : '' }} value="facebook">Facebook</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="start_date">Start Date</label>
                                    <input type="text" name="start_date" id="start_date" class="form-control date-picker" value="{{ request('start_date', date('d-m-Y')) }}" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="end_date">End Date</label>
                                    <input type="text" name="end_date" id="end_date" class="form-control date-picker" value="{{ request('end_date', date('d-m-Y')) }}" readonly>
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

        @if(count($engagements) > 0)
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>SR Social Engagements Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Period: {{ request('start_date') }} to {{ request('end_date') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">SR Name</th>
                                <th class="text-center">Platform</th>
                                <th class="text-center">Customer Name</th>
                                <th class="text-center">Customer Phone</th>
                                <th class="text-center" style="width: 100px;">Screenshot</th>
                                <th class="text-center">Location/GPS</th>
                                <th class="text-center">Created By</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($engagements as $engagement)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($engagement->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">{{ $engagement->sr->name ?? 'N/A' }}</td>
                                        <td class="text-center text-capitalize">{{ $engagement->platform }}</td>
                                        <td class="text-center">{{ $engagement->customer_name ?? 'N/A' }}</td>
                                        <td class="text-center">{{ $engagement->customer_phone ?? 'N/A' }}</td>
                                        <td class="text-center">
                                            @if($engagement->screenshot)
                                                <img src="{{ asset($engagement->screenshot) }}" class="visit-img" alt="Screenshot">
                                            @else
                                                <small>No Photo</small>
                                            @endif
                                        </td>
                                        <td>
                                            <small>
                                                {{ $engagement->location ?? 'N/A' }}<br>
                                                @if($engagement->lat && $engagement->long)
                                                    <a href="https://www.google.com/maps?q={{ $engagement->lat }},{{ $engagement->long }}" target="_blank">Map</a>
                                                @endif
                                            </small>
                                        </td>
                                        <td class="text-center">{{ $engagement->user->name ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @elseif(request('search'))
            <div class="col-12">
                <div class="alert alert-info">No engagement data found for the selected criteria.</div>
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            $('.date-picker').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy',
                todayHighlight: true
            });
            $('.select2').select2();
        });

        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "SR_Social_Engagements_Report_{{ request('start_date') }}_to_{{ request('end_date') }}";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
