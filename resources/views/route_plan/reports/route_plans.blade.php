@extends('layouts.app')
@section('title','Weekly Route Plans')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 5px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        .route-badge {
            display: inline-block;
            margin-bottom: 4px;
            white-space: normal;
            text-align: left;
            line-height: 1.4;
            padding: 5px 8px;
            border: 1px solid #dee2e6;
            background-color: #f8f9fa;
            color: #333;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection

@section('content')
    <div class="row no-print">
        <div class="col-12">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Filter Route Plans</h3>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.route_plans') }}" method="GET">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Select SR <span class="text-muted text-sm">(Leave blank for All)</span></label>
                                    <select name="sr_id" class="form-control select2">
                                        <option value="">-- All SRs --</option>
                                        @foreach($srs as $sr)
                                            <option value="{{ $sr->id }}" {{ $selectedSrId == $sr->id ? 'selected' : '' }}>
                                                {{ $sr->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary btn-block bg-gradient-primary"><i class="fa fa-search"></i> Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>Weekly Route Plans (PJP)</b></u></h3>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3 text-center">
                            <thead class="text-center">
                                <tr>
                                    <th style="width: 15%">SR Name</th>
                                    <th style="width: 14%">Saturday</th>
                                    <th style="width: 14%">Sunday</th>
                                    <th style="width: 14%">Monday</th>
                                    <th style="width: 14%">Tuesday</th>
                                    <th style="width: 14%">Wednesday</th>
                                    <th style="width: 14%">Thursday</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php
                                    $days = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday'];
                                @endphp
                                
                                @forelse($reportData as $sr)
                                    @php
                                        // Group this SR's routes by day
                                        $plansByDay = $sr->routePlans->groupBy('day_of_week');
                                    @endphp
                                    <tr>
                                        <td>
                                            <strong>{{ $sr->name }}</strong><br>
                                            <small class="text-muted">{{ $sr->mobile_no }}</small>
                                        </td>
                                        
                                        @foreach($days as $day)
                                            <td style="vertical-align: top !important;">
                                                @if($plansByDay->has($day) && $plansByDay[$day]->count() > 0)
                                                    @foreach($plansByDay[$day] as $plan)
                                                        @php
                                                            $marketName = $plan->market->name ?? 'Unknown';
                                                            $upazilaName = $plan->market->upazila->name ?? 'Unknown';
                                                        @endphp
                                                        <span class="route-badge w-100">
                                                            <strong>{{ $marketName }}</strong>
                                                            <br><small class="text-muted">[{{ $upazilaName }}]</small>
                                                        </span>
                                                    @endforeach
                                                @else
                                                    <div class="text-center text-muted" style="font-size: 0.8rem; padding-top: 10px;">
                                                        <i>Off-day</i>
                                                    </div>
                                                @endif
                                            </td>
                                        @endforeach
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-danger">
                                            No route plans found for the selected criteria.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
<script>
    var APP_URL = '{!! url()->full()  !!}';
    function getprint(print) {
        document.title = "Weekly_Route_Plans_{{ date('Y_m_d') }}";
        $(".footer-print-area").show();
        $('body').html($('#' + print).html());
        window.print();
        window.location.replace(APP_URL)
    }
</script>
@endsection
