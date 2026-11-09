@extends('layouts.app')
@section('title','SR Route Adherence & Coverage')
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
                    <h3 class="card-title">Filter Coverage Data</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.route_coverage') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-3 form-group">
                                <label for="date">Date:</label>
                                <input type="text" name="date" id="date" class="form-control date-picker" value="{{ $date }}" readonly autocomplete="off">
                            </div>
                            <div class="col-md-4 form-group">
                                <label for="sr_id">Select SR (Optional):</label>
                                <select name="sr_id" id="sr_id" class="form-control select2">
                                    <option value="">-- All SRs --</option>
                                    @foreach($srs as $sr)
                                        <option value="{{ $sr->id }}" {{ $selectedSrId == $sr->id ? 'selected' : '' }}>
                                            {{ $sr->name }} ({{ $sr->mobile_no }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 form-group">
                                <button type="submit" class="btn btn-primary bg-gradient-primary"><i class="fa fa-chart-line"></i> Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            @if(isset($reportData))
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>SR Route Adherence & Coverage</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Date: <strong>{{ \Carbon\Carbon::parse($date)->format('d-M-Y') }} ({{ $dayOfWeek }})</strong></h5>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3 text-center">
                            <thead class="bg-light">
                                <tr>
                                    <th style="width: 50px;">S/L</th>
                                    <th>SR Name</th>
                                    <th>Assigned Market</th>
                                    <th>Total Shops (Market)</th>
                                    <th>Visits in Market</th>
                                    <th>Coverage %</th>
                                    <th>Visits Out of Market</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($reportData as $index => $data)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td class="font-weight-bold text-left">{{ $data['sr_name'] }}</td>
                                    <td class="text-left text-info font-weight-bold">{{ $data['market_name'] }}</td>
                                    <td>{{ $data['total_shops'] }}</td>
                                    <td>
                                        <span class="badge badge-success" style="font-size: 14px;">{{ $data['in_route_visits'] }}</span>
                                    </td>
                                    <td>
                                        @if($data['coverage_percent'] >= 80)
                                            <span class="text-success font-weight-bold">{{ $data['coverage_percent'] }}%</span>
                                        @elseif($data['coverage_percent'] >= 40)
                                            <span class="text-warning font-weight-bold">{{ $data['coverage_percent'] }}%</span>
                                        @else
                                            <span class="text-danger font-weight-bold">{{ $data['coverage_percent'] }}%</span>
                                        @endif
                                        <div class="progress progress-sm mt-1 extra-remove">
                                            <div class="progress-bar {{ $data['coverage_percent'] >= 80 ? 'bg-success' : ($data['coverage_percent'] >= 40 ? 'bg-warning' : 'bg-danger') }}" 
                                                 style="width: {{ $data['coverage_percent'] }}%"></div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($data['out_of_route_visits'] > 0)
                                            <span class="badge badge-danger" title="SR visited shops outside assigned route" style="font-size: 14px;">
                                                {{ $data['out_of_route_visits'] }} <i class="fas fa-exclamation-triangle"></i>
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">0</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">No route plans found for the selected date.</td>
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
    $(function() {
        $('.date-picker').datepicker({
            autoclose: true,
            format: 'dd-mm-yyyy',
            todayHighlight: true
        });
    });

    var APP_URL = '{!! url()->full()  !!}';
    function getprint(print) {
        document.title = "Route_Coverage_Report_{{ date('Y_m_d') }}";
        $(".extra-remove").remove();
        $(".footer-print-area").show();
        $('body').html($('#' + print).html());
        window.print();
        window.location.replace(APP_URL)
    }
</script>
@endsection
