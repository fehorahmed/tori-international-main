@extends('layouts.app')
@section('title','Attendance Reminder Report')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;;
        }
        .customer-area-border {
            border: 1.5px solid #000 !important;;
            padding: 3px;
            min-height: 128px;
            margin-top: 10px;
            margin-bottom: 50px;
        }
        .customer-area-border p {
            font-size: 20px !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        
        .badge-responded {
            background: #d4edda; color: #155724;
            padding: 4px 10px; border-radius: 12px; font-weight: 600;
        }
        .badge-missed {
            background: #f8d7da; color: #721c24;
            padding: 4px 10px; border-radius: 12px; font-weight: 600;
        }
        .badge-pending {
            background: #fff3cd; color: #856404;
            padding: 4px 10px; border-radius: 12px; font-weight: 600;
        }
        .response-rate-bar {
            height: 8px;
            border-radius: 4px;
            background: #f1f5f9;
            overflow: hidden;
        }
        .response-rate-bar .fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease;
        }

        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }
            .signature-area{
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
            .no-print { display: none !important; }
        }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <div class="card-body">
                    {{-- Filter Form --}}
                    <form action="{{ route('report.attendance-reminder') }}" class="form-horizontal no-print mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date">Date</label>
                                    <input type="text" name="date" class="form-control date-picker"
                                           value="{{ request('date', \Carbon\Carbon::parse($date)->format('d-m-Y')) }}"
                                           autocomplete="off" placeholder="Select Date">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-info bg-gradient-info btn-block">
                                        <i class="fas fa-search mr-1"></i> Search
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    @if(request('date') != '')
                        </div>
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <a href="#" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-print"></i></a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="table-responsive" id="printArea">
                                        @include('layouts.partial.__report_header')
                                        <div class="row">
                                            <div class="col-12">
                                                <h3 class="text-center" style="font-size: 25px"><u><b>Attendance Reminder Report</b></u></h3>
                                                <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('date', $date))->format('d-m-Y') }}</h5>
                                            </div>
                                        </div>

                                        @if(count($report) > 0)
                                        <table class="table table-bordered table-hover">
                                            <thead>
                                                <tr>
                                                    <th class="text-center">#</th>
                                                    <th class="text-left">Employee Name</th>
                                                    <th class="text-left">Designation</th>
                                                    <th class="text-center">Total Sent</th>
                                                    <th class="text-center">Responded ✅</th>
                                                    <th class="text-center">Missed ❌</th>
                                                    <th class="text-center">Pending ⏳</th>
                                                    <th class="text-center">Response Rate</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($report as $key => $item)
                                                    @php
                                                        $resolved = $item['responded'] + $item['missed'];
                                                        $rate = $resolved > 0 ? round(($item['responded'] / $resolved) * 100, 1) : 0;
                                                        $barColor = $rate >= 80 ? '#10b981' : ($rate >= 50 ? '#f59e0b' : '#ef4444');
                                                    @endphp
                                                    <tr>
                                                        <td class="text-center">{{ $key + 1 }}</td>
                                                        <td>
                                                            <strong>{{ $item['employee']->name ?? 'N/A' }}</strong>
                                                            <br><small class="text-muted">{{ $item['employee']->id_no ?? '' }}</small>
                                                        </td>
                                                        <td>{{ $item['employee']->designation->name ?? '' }}</td>
                                                        <td class="text-center"><span class="font-weight-bold">{{ $item['total_sent'] }}</span></td>
                                                        <td class="text-center"><span class="badge-responded">{{ $item['responded'] }}</span></td>
                                                        <td class="text-center"><span class="badge-missed">{{ $item['missed'] }}</span></td>
                                                        <td class="text-center"><span class="badge-pending">{{ $item['pending'] }}</span></td>
                                                        <td>
                                                            <div class="d-flex align-items-center">
                                                                <div class="response-rate-bar flex-grow-1 mr-2">
                                                                    <div class="fill" style="width: {{ $rate }}%; background: {{ $barColor }};"></div>
                                                                </div>
                                                                <span class="small font-weight-bold">{{ $rate }}%</span>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        @else
                                            <div class="text-center py-5">
                                                <i class="fas fa-bell-slash fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">No reminder data found for this date.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column,.print-remove').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
