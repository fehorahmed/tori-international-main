@extends('layouts.app')
@section('title', 'SR Performance Dashboard')

@section('style')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
<style>
    .filter-card {
        background: #fff;
        border-radius: 12px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        margin-bottom: 25px;
    }
    .table-bordered thead td, .table-bordered thead th {
        vertical-align: middle;
        border: 1px solid #dee2e6!important;
        padding: 8px;
    }
    .table-bordered td, .table-bordered th {
        border: 1px solid #dee2e6!important;
        vertical-align: middle;
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
    
    /* Premium Hover effects for KPI cards */
    .kpi-card {
        background: linear-gradient(145deg, #ffffff 0%, #f4f7fe 100%);
        border-radius: 20px !important;
        border: 1px solid rgba(226, 232, 240, 0.8) !important;
        box-shadow: 0 4px 20px rgba(148, 163, 184, 0.08) !important;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        z-index: 1;
        overflow: hidden;
    }
    .kpi-card::after {
        content: '';
        position: absolute;
        bottom: 0; left: 0; right: 0;
        height: 100%;
        background: linear-gradient(to top, rgba(59, 130, 246, 0.04) 0%, transparent 60%);
        z-index: -1;
        opacity: 0;
        transition: opacity 0.4s ease;
    }
    .kpi-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 0 20px 40px rgba(59, 130, 246, 0.12) !important;
        border-color: rgba(59, 130, 246, 0.3) !important;
    }
    .kpi-card:hover::after {
        opacity: 1;
    }
    
    .kpi-title {
        font-size: 12px;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 800;
        margin-bottom: 20px;
    }
    
    /* CSS for Speedometer Gauge */
    .speedometer-container {
        width: 120px;
        height: 60px;
        position: relative;
        overflow: hidden;
        margin: 15px auto 5px;
    }
    .speedometer-bg {
        position: absolute;
        top: 0; left: 0;
        width: 120px; height: 120px;
        border-radius: 50%;
        border: 14px solid #e2e8f0;
        border-bottom-color: transparent !important;
        border-right-color: transparent !important;
        transform: rotate(45deg);
    }
    .speedometer-fill {
        position: absolute;
        top: 0; left: 0;
        width: 120px; height: 120px;
        border-radius: 50%;
        border: 14px solid #3b82f6; /* Blue fill */
        border-bottom-color: transparent !important;
        border-right-color: transparent !important;
        /* 0% = -135deg (hidden), 100% = 45deg (full) */
        transform: rotate(calc(-135deg + (var(--percent) * 1.8deg)));
        transition: transform 1s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .speedometer-fill.success {
        border-color: #10b981; /* Green for 100% */
    }
    .speedometer-text {
        position: absolute;
        bottom: -2px;
        left: 0;
        width: 100%;
        text-align: center;
        font-size: 18px;
        font-weight: 800;
        color: #1e293b;
        font-family: 'Outfit', sans-serif;
    }
    
    /* Mobile Optimization */
    @media (max-width: 767.98px) {
        .kpi-grid {
            margin-left: -5px;
            margin-right: -5px;
        }
        .kpi-grid > [class*="col-"] {
            padding-left: 5px;
            padding-right: 5px;
        }
        .kpi-card .card-body {
            padding: 12px !important;
        }
        .speedometer-container {
            transform: scale(0.75);
            margin-top: 0 !important;
            margin-bottom: -15px !important;
        }
        .kpi-title {
            font-size: 10px;
            letter-spacing: 0px;
            margin-bottom: 0px;
        }
        .kpi-value {
            font-size: 15px !important;
        }
        .kpi-target {
            font-size: 10px !important;
        }
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    
    <!-- Shared Tabs -->
    @include('performance_dashboard.partials.__tabs')

    <!-- Filters -->
    <div class="filter-card border-top-office" style="border-top-color: #3b82f6;">
        <form action="{{ route('performance.sr') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-bold text-muted small">Select SR</label>
                    <select name="sr_id" class="form-control select2">
                        <option value="">All SRs</option>
                        @foreach($srs as $sr)
                            <option value="{{ $sr->id }}" {{ $selectedSrId == $sr->id ? 'selected' : '' }}>
                                {{ $sr->name }} - {{ $sr->id_no ?? $sr->employee_id }}
                            </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="col-md-3 mb-3 mb-md-0">
                    <label class="font-weight-bold text-muted small">Timeframe</label>
                    <select name="timeframe" id="timeframeSelect" class="form-control">
                        <option value="daily" {{ $timeframe == 'daily' ? 'selected' : '' }}>Daily (Today)</option>
                        <option value="weekly" {{ $timeframe == 'weekly' ? 'selected' : '' }}>Weekly (This Week)</option>
                        <option value="monthly" {{ $timeframe == 'monthly' ? 'selected' : '' }}>Monthly (This Month)</option>
                        <option value="custom" {{ $timeframe == 'custom' ? 'selected' : '' }}>Custom Month</option>
                    </select>
                </div>

                <div class="col-md-3 mb-3 mb-md-0 custom-month-container" style="display: {{ $timeframe == 'custom' ? 'block' : 'none' }};">
                    <label class="font-weight-bold text-muted small">Select Month</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-primary"></i></span>
                        </div>
                        <input type="text" name="month" id="monthPicker" class="form-control bg-white" value="{{ $monthStr }}" readonly>
                    </div>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-block rounded-pill shadow-sm">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Performance Report Grid -->
    <div class="row justify-content-center mb-3 mt-3 kpi-grid">
        <div class="col-12 mb-3">
            <h5 class="text-muted font-weight-bold mb-0 text-center text-md-left border-bottom pb-2">
                {{ $reportTitle }}
            </h5>
        </div>

        @php
            $totalPercent = 0;
            $validTaskCount = 0;
        @endphp

        @foreach($kpiBreakdowns as $kpi)
            @php
                $actual = $kpi['achieved'];
                $target = $kpi['target'];
                $percent = $target > 0 ? ($actual / $target) * 100 : 0;
                $cappedPercent = min(100, $percent);
                $totalPercent += $cappedPercent;
                if ($target > 0) {
                    $validTaskCount++;
                }
            @endphp
            <div class="col-6 col-md-4 col-lg-3 mb-3">
                <a href="javascript:void(0)" class="text-decoration-none d-block h-100">
                    <div class="card h-100 kpi-card">
                        <div class="card-body text-center p-4 d-flex flex-column align-items-center justify-content-between">
                            <div class="kpi-title">{{ $kpi['name'] }}</div>
                            
                            <div class="speedometer-container" style="--percent: {{ $cappedPercent }};">
                                <div class="speedometer-bg"></div>
                                <div class="speedometer-fill {{ $cappedPercent >= 100 ? 'success' : '' }}"></div>
                                <div class="speedometer-text">{{ number_format($percent, 1) }}%</div>
                            </div>

                            <div class="mt-3 w-100">
                                <div class="d-flex align-items-center justify-content-center">
                                    <span class="font-weight-bold kpi-value" style="font-size: 18px; color: #0f172a; font-family: 'Outfit', sans-serif;">
                                        @if($kpi['is_amount'])
                                            {{ number_format($actual, 2) }}
                                        @else
                                            {{ $actual }}
                                        @endif
                                    </span>
                                </div>
                                <div class="mt-1 pb-1 kpi-target" style="font-size: 12px; font-weight: 600; color: #94a3b8;">
                                    Target: <span style="color: #3b82f6;">{{ $target }} {{ $kpi['is_amount'] ? 'Amt' : 'Count' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach

        @php
            $avgPercent = $validTaskCount > 0 ? $totalPercent / $validTaskCount : 0;
        @endphp
        
        <!-- Overall Progress -->
        <div class="col-12 mt-2">
            <div class="card shadow-sm border-0 bg-light">
                <div class="card-body text-center p-4">
                    <div class="overall-progress-container">
                        <div class="overall-label text-dark">Overall Progress:</div>
                        <div class="circle-progress" style="--progress: {{ $avgPercent }};">
                            <div class="percent-text">{{ number_format($avgPercent, 1) }}%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Visits Table -->
    <div class="row mb-3 mt-4">
        <div class="col-12">
            <h5 class="text-muted font-weight-bold mb-3"><i class="fas fa-walking mr-2"></i>Recent Visits</h5>
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="py-3 text-center">Date</th>
                                    @if(empty($selectedSrId))
                                        <th class="py-3 text-center">SR Name</th>
                                    @endif
                                    <th class="py-3 text-center">Customer/Shop</th>
                                    <th class="py-3 text-center">Location</th>
                                    <th class="py-3 text-center" style="width: 80px;">Photo</th>
                                    <th class="py-3 text-center">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentVisits as $visit)
                                    <tr>
                                        <td class="align-middle text-center">{{ \Carbon\Carbon::parse($visit->date)->format('d-m-Y') }}</td>
                                        @if(empty($selectedSrId))
                                            <td class="align-middle text-center">{{ $visit->sr->name ?? 'N/A' }}</td>
                                        @endif
                                        <td class="align-middle">
                                            @if($visit->supplier)
                                                <strong>{{ $visit->supplier->name }}</strong><br>
                                                <small class="text-muted">{{ $visit->supplier->mobile_no }}</small>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="align-middle"><small>{{ $visit->location ?? 'N/A' }}</small></td>
                                        <td class="align-middle text-center">
                                            @if($visit->image)
                                                <img src="{{ asset($visit->image) }}" alt="Visit Image" style="max-width: 60px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.2);">
                                            @else
                                                <small class="text-muted">No Photo</small>
                                            @endif
                                        </td>
                                        <td class="align-middle"><small>{{ $visit->notes }}</small></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">
                                            <i class="fas fa-info-circle mr-2"></i> No visits found in this timeframe.
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
</div>
@endsection

@section('script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2();
        
        $('#monthPicker').datepicker({
            format: "mm-yyyy",
            viewMode: "months", 
            minViewMode: "months",
            autoclose: true,
            todayHighlight: true
        });

        // Toggle custom month picker
        $('#timeframeSelect').on('change', function() {
            if ($(this).val() === 'custom') {
                $('.custom-month-container').slideDown(200);
            } else {
                $('.custom-month-container').slideUp(200);
            }
        });
    });
</script>
@endsection
