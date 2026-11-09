@extends('layouts.app')
@section('title','SR Performance Report')
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
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
        .circle-progress {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: conic-gradient(#17a2b8 calc(var(--progress) * 3.6deg), #e9ecef 0deg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            margin: 0 auto;
        }
        .circle-progress::before {
            content: "";
            position: absolute;
            width: 65px;
            height: 65px;
            border-radius: 50%;
            background: white;
        }
        .circle-progress .percent-text {
            position: relative;
            font-size: 14px;
            font-weight: bold;
            color: #333;
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
                    <form action="{{ route('report.sr_performance') }}">
                        <div class="row">
                            <div class="col-md-4">
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
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label for="date" class="col-form-label">Month <span class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('date', date('m-Y')) }}"
                                           name="date" class="form-control month-picker1" id="date"
                                           placeholder="Select Month" readonly>
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

        @if(count($assignments) > 0)
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>SR Performance Report</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">Month: {{ \Carbon\Carbon::parse('01-'.str_replace('/', '-', request('date')))->format('F, Y') }}</h5>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">SR Name</th>
                                <th class="text-center">Task Details</th>
                                <th class="text-center">Overall Progress</th>
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($assignments as $assignment)
                                    @php
                                        $totalPercent = 0;
                                        $taskCount = $assignment->items->count();
                                        foreach($assignment->items as $item) {
                                            $p = $item->target_value > 0 ? ($item->actual_value / $item->target_value) * 100 : 0;
                                            $totalPercent += min(100, $p);
                                        }
                                        $avgPercent = $taskCount > 0 ? $totalPercent / $taskCount : 0;
                                    @endphp
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td class="text-center">
                                            <strong>{{ $assignment->employee->name }}</strong><br>
                                            <small>{{ $assignment->employee->mobile_no }}</small>
                                        </td>
                                        <td>
                                            <table class="table table-sm table-borderless mb-0">
                                                @foreach($assignment->items as $item)
                                                    @php
                                                        $itemPercent = $item->target_value > 0 ? ($item->actual_value / $item->target_value) * 100 : 0;
                                                        $itemPercent = min(100, $itemPercent);
                                                    @endphp
                                                    <tr>
                                                        <td width="40%">{{ $item->type->name }}</td>
                                                        <td width="30%">{{ $item->actual_value }} / {{ $item->target_value }} {{ $item->type->unit }}</td>
                                                        <td width="30%">
                                                            <div class="progress progress-xs mb-0">
                                                                <div class="progress-bar bg-info" style="width: {{ $itemPercent }}%"></div>
                                                            </div>
                                                            <small>{{ number_format($itemPercent, 1) }}%</small>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </table>
                                        </td>
                                        <td class="text-center">
                                            <div class="circle-progress" style="--progress: {{ $avgPercent }};">
                                                <div class="percent-text">{{ number_format($avgPercent, 1) }}%</div>
                                            </div>
                                        </td>
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
                <div class="alert alert-info">No data found for the selected month and SR.</div>
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            $('.month-picker1').MonthPicker({
                Button: false,
                MonthFormat: 'mm-yy'
            });
        });

        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "SR_Performance_Report_{{ request('date') }}";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
