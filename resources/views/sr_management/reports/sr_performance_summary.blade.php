@extends('layouts.app')
@section('title','SR Performance Summary')
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
        .overall-progress-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px;
        }
        .circle-progress {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#17a2b8 calc(var(--progress) * 3.6deg), #e9ecef 0deg);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }
        .circle-progress::before {
            content: "";
            position: absolute;
            width: 95px;
            height: 95px;
            border-radius: 50%;
            background: white;
        }
        .circle-progress .percent-text {
            position: relative;
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        .overall-label {
            margin-bottom: 10px;
            font-size: 20px;
            font-weight: bold;
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
                    <form action="{{ route('report.sr_performance_summary') }}">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="sr_id">SR <span class="text-danger">*</span></label>
                                    <select required name="sr_id" id="sr_id" class="form-control select2">
                                        <option value="">Select SR</option>
                                        @foreach($srs as $sr)
                                            <option {{ request('sr_id') == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="date" class="col-form-label">Target Month <span class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('date', date('m-Y')) }}"
                                           name="date" class="form-control month-picker1" id="date"
                                           placeholder="Select Month" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Start Date <span class="text-danger">*</span></label>
                                    <input required type="text" name="start_date" id="start_date" class="form-control date-picker" value="{{ request('start_date', date('01-m-Y')) }}" autocomplete="off" readonly>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>End Date <span class="text-danger">*</span></label>
                                    <input required type="text" name="end_date" id="end_date" class="form-control date-picker" value="{{ request('end_date', date('t-m-Y')) }}" autocomplete="off" readonly>
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

        @if(count($performanceData) > 0)
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
                                <h3 class="text-center" style="font-size: 25px"><u><b>SR Performance Summary</b></u></h3>
                                <h5 class="text-center" style="font-size: 18px">SR: {{ $assignment->employee->name }} | Mobile: {{ $assignment->employee->mobile_no }}</h5>
                                <h5 class="text-center" style="font-size: 16px">Range: {{ request('start_date') }} to {{ request('end_date') }}</h5>
                            </div>
                        </div>

                        @php
                            $totals = [];
                            foreach($assignment->items as $item) {
                                $val = 0;
                                if($item->type->slug == 'sales_target') {
                                    $val = collect($performanceData)->sum('sales');
                                } elseif($item->type->slug == 'order_count') {
                                    $val = collect($performanceData)->sum('orders');
                                } elseif($item->type->slug == 'payment_collection') {
                                    $val = collect($performanceData)->sum('payments');
                                } elseif($item->type->slug == 'visit_count') {
                                    $val = collect($performanceData)->sum('visits');
                                }
                                $totals[$item->type->slug] = $val;
                            }
                        @endphp

                        <div class="row mb-3 mt-3">
                            <div class="col-12">
                                <table class="table table-bordered table-sm">
                                    <thead>
                                        <tr class="bg-light">
                                            <th colspan="{{ $assignment->items->count() }}" class="text-center">Monthly Target ({{ \Carbon\Carbon::parse($assignment->date)->format('F, Y') }})</th>
                                        </tr>
                                        <tr>
                                            @foreach($assignment->items as $item)
                                                <th class="text-center">{{ $item->type->name }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $totalPercent = 0;
                                            $taskCount = $assignment->items->count();
                                        @endphp
                                        <tr>
                                            @foreach($assignment->items as $item)
                                                @php
                                                    $actual = $totals[$item->type->slug];
                                                    $percent = $item->target_value > 0 ? ($actual / $item->target_value) * 100 : 0;
                                                    $cappedPercent = min(100, $percent);
                                                    $totalPercent += $cappedPercent;
                                                @endphp
                                                <td class="text-center">
                                                    <strong>
                                                        @if(in_array($item->type->slug, ['sales_target', 'payment_collection']))
                                                            {{ number_format($actual, 2) }}
                                                        @else
                                                            {{ $actual }}
                                                        @endif
                                                        / {{ $item->target_value }} {{ $item->type->unit }}
                                                    </strong>
                                                    <div class="progress progress-xs mt-1 mb-0" style="height: 10px;">
                                                        <div class="progress-bar bg-info" style="width: {{ $cappedPercent }}%"></div>
                                                    </div>
                                                    <small>Progress: {{ number_format($percent, 1) }}%</small>
                                                </td>
                                            @endforeach
                                        </tr>
                                        @php
                                            $avgPercent = $taskCount > 0 ? $totalPercent / $taskCount : 0;
                                        @endphp
                                        <tr>
                                            <th colspan="{{ $taskCount }}" class="text-center p-3">
                                                <div class="overall-progress-container">
                                                    <div class="overall-label">Overall Progress:</div>
                                                    <div class="circle-progress" style="--progress: {{ $avgPercent }};">
                                                        <div class="percent-text">{{ number_format($avgPercent, 1) }}%</div>
                                                    </div>
                                                </div>
                                            </th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <table class="table table-bordered">
                            <thead>
                            <tr class="bg-light">
                                <th class="text-center">Date</th>
                                @foreach($assignment->items as $item)
                                    <th class="text-center">{{ $item->type->name }}</th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                                @foreach($performanceData as $date => $data)
                                    <tr>
                                        <td class="text-center">{{ \Carbon\Carbon::parse($date)->format('d-m-Y (l)') }}</td>
                                        @foreach($assignment->items as $item)
                                            @php
                                                $val = 0;
                                                if($item->type->slug == 'sales_target') $val = $data['sales'];
                                                elseif($item->type->slug == 'order_count') $val = $data['orders'];
                                                elseif($item->type->slug == 'payment_collection') $val = $data['payments'];
                                                elseif($item->type->slug == 'visit_count') $val = $data['visits'];
                                                
                                                $dayPercent = $item->target_value > 0 ? ($val / $item->target_value) * 100 : 0;
                                                $dayPercent = min(100, $dayPercent);
                                            @endphp
                                            <td class="text-center" style="vertical-align: top;">
                                                @if(in_array($item->type->slug, ['sales_target', 'payment_collection']))
                                                    {{ number_format($val, 2) }}
                                                @else
                                                    {{ $val }}
                                                @endif
                                                <div class="progress progress-xs mt-1 mb-0" style="height: 5px;">
                                                    <div class="progress-bar bg-info" style="width: {{ $dayPercent }}%"></div>
                                                </div>
                                            </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light">
                                    <td class="text-center">Total (Live)</td>
                                    @foreach($assignment->items as $item)
                                        <td class="text-center">
                                            @if(in_array($item->type->slug, ['sales_target', 'payment_collection']))
                                                {{ number_format($totals[$item->type->slug], 2) }}
                                            @else
                                                {{ $totals[$item->type->slug] }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                                <tr class="font-weight-bold">
                                    <td class="text-center">Progress (%)</td>
                                    @foreach($assignment->items as $item)
                                        @php
                                            $actual = $totals[$item->type->slug];
                                            $percent = $item->target_value > 0 ? ($actual / $item->target_value) * 100 : 0;
                                            $percent = min(100, $percent);
                                        @endphp
                                        <td class="text-center" style="vertical-align: top;">
                                            <div class="progress progress-xs mb-1" style="height: 10px;">
                                                <div class="progress-bar bg-info" style="width: {{ $percent }}%"></div>
                                            </div>
                                            <small>{{ number_format($percent, 1) }}%</small>
                                        </td>
                                    @endforeach
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @elseif(request('search'))
            <div class="col-12">
                <div class="alert alert-info">No assignment or data found for the selected criteria.</div>
            </div>
        @endif
    </div>
@endsection

@section('script')
    <script>
        $(function() {
            $('.month-picker1').MonthPicker({
                Button: false,
                MonthFormat: 'mm-yy',
                OnAfterChooseMonth: function() {
                    var val = $(this).val();
                    if (val) {
                        var p = val.split('-');
                        var mm = parseInt(p[0]) - 1;
                        var yy = p[1].length === 2 ? parseInt(p[1]) + 2000 : parseInt(p[1]);
                        
                        var fDay = new Date(yy, mm, 1);
                        var lDay = new Date(yy, mm + 1, 0);
                        
                        updateDateRange(fDay, lDay);
                    }
                }
            });

            function updateDateRange(firstDay, lastDay) {
                var d1 = ("0" + firstDay.getDate()).slice(-2);
                var m1 = ("0" + (firstDay.getMonth() + 1)).slice(-2);
                var y1 = firstDay.getFullYear();

                var d2 = ("0" + lastDay.getDate()).slice(-2);
                var m2 = ("0" + (lastDay.getMonth() + 1)).slice(-2);
                var y2 = lastDay.getFullYear();

                $('#start_date').val(d1 + "-" + m1 + "-" + y1);
                $('#end_date').val(d2 + "-" + m2 + "-" + y2);

                // Clear restrictions first
                $('.date-picker').datepicker('option', 'maxDate', null);
                $('.date-picker').datepicker('option', 'minDate', null);

                // Set new restrictions
                $('.date-picker').datepicker('option', 'minDate', firstDay);
                $('.date-picker').datepicker('option', 'maxDate', lastDay);
            }

            // Initial setup - Only set restrictions, don't overwrite values from query params
            var initialVal = $('.month-picker1').val();
            if (initialVal) {
                var p = initialVal.split('-');
                var mm = parseInt(p[0]) - 1;
                var yy = p[1].length === 2 ? parseInt(p[1]) + 2000 : parseInt(p[1]);
                
                var f = new Date(yy, mm, 1);
                var l = new Date(yy, mm + 1, 0);
                
                $('.date-picker').datepicker('option', 'minDate', f);
                $('.date-picker').datepicker('option', 'maxDate', l);
            }
        });

        var APP_URL = '{!! url()->full()  !!}';
        function getprint(print) {
            document.title = "SR_Performance_Summary_{{ request('start_date') }}_to_{{ request('end_date') }}";
            $(".extra-remove").remove();
            $(".footer-print-area").show();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
