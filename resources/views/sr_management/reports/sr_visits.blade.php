@extends('layouts.app')
@section('title','SR Visits Report')
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
                    <form action="{{ route('report.sr_visits') }}">
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

        @if(count($visits) > 0)
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>SR Field Visit Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Period: {{ request('start_date') }} to {{ request('end_date') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3">
                            <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">S/L</th>
                                <th class="text-center">Date</th>
                                <th class="text-center">SR Name</th>
                                <th class="text-center">Customer/Shop</th>
                                <th class="text-center">Location</th>
                                <th class="text-center" style="width: 100px;">Photo</th>
                                <th class="text-center">Notes</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($visits as $visit)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($visit->date)->format('d-m-Y') }}</td>
                                        <td class="text-center">{{ $visit->sr->name ?? 'N/A' }}</td>
                                        <td>
                                            @if($visit->supplier)
                                                <strong>{{ $visit->supplier->name }}</strong><br>
                                                <small>{{ $visit->supplier->mobile_no }}</small>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td><small>{{ $visit->location ?? 'N/A' }}</small></td>
                                        <td class="text-center">
                                            @if($visit->image)
                                                <img src="{{ asset($visit->image) }}" class="visit-img" alt="Visit Image">
                                            @else
                                                <small>No Photo</small>
                                            @endif
                                        </td>
                                        <td><small>{{ $visit->notes }}</small></td>
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
                <div class="alert alert-info">No visit data found for the selected criteria.</div>
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
        });

        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "SR_Visits_Report_{{ request('start_date') }}_to_{{ request('end_date') }}";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
