@extends('layouts.app')
@section('title','Dashboard')

@section('style')
    <link rel="stylesheet" type="text/css" media="all" href="{{ asset('themes/backend/date-range/daterangepicker.css') }}" />
    <style>
        /* Premium Design Tokens */
        :root {
            --primary-blue: #3b82f6;
            --soft-blue: #eff6ff;
            --primary-orange: #f97316;
            --soft-orange: #fff7ed;
            --primary-green: #10b981;
            --soft-green: #ecfdf5;
            --primary-red: #ef4444;
            --soft-red: #fef2f2;
            --text-dark: #1e293b;
            --text-muted: #64748b;
        }

        .premium-card {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #f1f5f9;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.03);
            transition: all 0.3s ease;
            height: 100%;
        }
        .premium-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        /* KPI Card Styles */
        .kpi-icon {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.25rem;
        }

        /* Circular Progress - Premium Enhancement */
        .circle-progress {
            position: relative;
            width: 120px;
            height: 120px;
            filter: drop-shadow(0 4px 10px rgba(59, 130, 246, 0.15));
        }
        .circle-progress svg {
            width: 120px;
            height: 120px;
            transform: rotate(-90deg);
        }
        .circle-progress circle {
            fill: none;
            stroke-width: 10;
            stroke-linecap: round;
        }
        .circle-progress .bg { 
            stroke: #f1f5f9; 
        }
        .circle-progress .progress-circle {
            stroke: url(#gradient);
            stroke-dasharray: 345.57; /* 2 * PI * 55 */
            transition: stroke-dashoffset 1.5s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .circle-progress .percentage {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 1.5rem;
            font-weight: 800;
            color: var(--text-dark);
            font-family: 'Outfit', sans-serif;
        }
        .circle-progress .percentage-symbol {
            font-size: 0.8rem;
            vertical-align: super;
            margin-left: 2px;
            color: var(--text-muted);
        }

        .alert-item {
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 10px;
            border-left: 4px solid transparent;
        }
        .alert-low-stock { background: var(--soft-red); border-left-color: var(--primary-red); }
        .alert-pending { background: var(--soft-orange); border-left-color: var(--primary-orange); }

        .table-premium thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            color: var(--text-muted);
            border: none;
        }
        .badge-soft-blue { background: var(--soft-blue); color: var(--primary-blue); }
        .text-orange { color: var(--primary-orange); }
        .text-green { color: var(--primary-green); }
        .text-red { color: var(--primary-red); }
        .bg-soft-blue { background: var(--soft-blue); }
        .bg-soft-orange { background: var(--soft-orange); }
        .bg-soft-green { background: var(--soft-green); }
        .bg-soft-red { background: var(--soft-red); }
    </style>
@endsection

@section('content')
    @php
        if (request()->has('layout')) {
            session(['dashboard_layout' => request('layout')]);
        }
        $currentLayout = session('dashboard_layout', 'modular');
    @endphp

    <div class="container-fluid py-3">
        <!-- Layout Switcher Button for AdminLTE compatibility -->
        <div class="row mb-3 d-none">
            <div class="col-12 d-flex justify-content-end">
                @if($currentLayout == 'modular')
                    <a href="{{ route('dashboard', ['layout' => 'modular']) }}" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3">
                        <i class="fas fa-cubes mr-1"></i> Switch to Modular Dashboard
                    </a>
                @else
                    <a href="{{ route('dashboard', ['layout' => 'classic']) }}" class="btn btn-sm btn-outline-info shadow-sm rounded-pill px-3">
                        <i class="fas fa-chart-line mr-1"></i> Switch to Classic Dashboard
                    </a>
                @endif
            </div>
        </div>

        @if($currentLayout == 'modular')
            @include('dashboard_modular')
        @else
            <!-- Top Row: Global Context & Main Target -->
        <div class="row mb-4">
            <div class="col-md-12">
                <div class="premium-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-2 text-center">
                            <div class="circle-progress mx-auto">
                                <svg>
                                    <defs>
                                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                                            <stop offset="0%" stop-color="#3b82f6" />
                                            <stop offset="100%" stop-color="#6366f1" />
                                        </linearGradient>
                                    </defs>
                                    <circle class="bg" cx="60" cy="60" r="55"></circle>
                                    <circle class="progress-circle" cx="60" cy="60" r="55" 
                                            style="stroke-dashoffset: {{ 345.57 * (1 - min($achievementPercentage, 100) / 100) }};">
                                    </circle>
                                </svg>
                                <div class="percentage">{{ number_format($achievementPercentage, 0) }}<span class="percentage-symbol">%</span></div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3 mb-md-0 text-center text-md-left">
                            <h4 class="font-weight-bold text-dark mb-1">
                                @if(auth()->user()->role == 'Admin' && auth()->user()->activeConcernId() == 1)
                                    Global Enterprise Performance
                                @elseif(auth()->user()->role == 'SR')
                                    My Monthly Performance
                                @else
                                    {{ auth()->user()->activeSisterConcern->name ?? 'Branch Performance' }}
                                @endif
                            </h4>
                            <p class="text-muted small mb-0">Current month target tracking and achievement overview</p>
                        </div>
                        <div class="col-md-3 text-center border-md-left mb-3 mb-md-0">
                            <span class="text-muted small d-block mb-1">Monthly Target</span>
                            <h3 class="font-weight-bold text-primary mb-0">৳{{ number_format($targetAmount, 0) }}</h3>
                        </div>
                        <div class="col-md-3 text-center border-md-left">
                            <span class="text-muted small d-block mb-1">Actual Achievement</span>
                            <h3 class="font-weight-bold text-dark mb-0">৳{{ number_format($achievementAmount, 2) }}</h3>
                        </div>
                    </div>

                    @if(auth()->user()->role == 'SR' && count($srTasks) > 0)
                        <div class="row mt-4 pt-4 border-top">
                            @foreach($srTasks as $task)
                                <div class="col-md-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="text-muted small font-weight-bold">{{ $task['name'] }}</span>
                                        <span class="text-dark small font-weight-bold">{{ number_format($task['percentage'], 1) }}%</span>
                                    </div>
                                    <div class="progress" style="height: 6px; border-radius: 10px; background: #f0f2f5;">
                                        <div class="progress-bar" role="progressbar" 
                                             style="width: {{ min($task['percentage'], 100) }}%; border-radius: 10px; background: linear-gradient(90deg, #3b82f6, #6366f1);" 
                                             aria-valuenow="{{ $task['percentage'] }}" aria-valuemin="0" aria-valuemax="100">
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between mt-1">
                                        <span class="text-muted" style="font-size: 10px;">Target: {{ in_array($task['type'], [1, 3]) ? '৳' : '' }}{{ number_format($task['target'], 0) }}</span>
                                        <span class="text-primary font-weight-bold" style="font-size: 10px;">Ach: {{ in_array($task['type'], [1, 3]) ? '৳' : '' }}{{ number_format($task['achievement'], 0) }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- KPI Grid -->
        <div class="row mb-md-4">
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="premium-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon bg-soft-blue text-primary mr-3">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Total Sales</span>
                            <h4 class="font-weight-bold mb-0">৳{{ number_format($totalSales, 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="premium-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon bg-soft-orange text-orange mr-3">
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Collections</span>
                            <h4 class="font-weight-bold mb-0">৳{{ number_format($totalCollection, 0) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="premium-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon bg-soft-green text-green mr-3">
                            <i class="fas fa-hand-holding-heart"></i>
                        </div>
                        <div>
                            @if(auth()->user()->role == 'SR')
                                <span class="text-muted small d-block">Monthly Dues</span>
                                <h4 class="font-weight-bold mb-0">৳{{ number_format($totalDues, 0) }}</h4>
                            @else
                                <span class="text-muted small d-block">Stock Value</span>
                                <h4 class="font-weight-bold mb-0">৳{{ number_format($totalStockValue, 0) }}</h4>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-3 mb-md-0">
                <div class="premium-card p-3">
                    <div class="d-flex align-items-center">
                        <div class="kpi-icon bg-soft-red text-red mr-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <span class="text-muted small d-block">Pending Orders</span>
                            <h4 class="font-weight-bold mb-0">{{ $pendingOrdersCount }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts & Alerts -->
        <div class="row mb-4">
            <div class="col-md-8">
                <div class="premium-card p-4">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="font-weight-bold m-0 text-dark">Sales Performance Trend</h5>
                        <span class="badge badge-soft-blue text-primary px-3 py-2">Last 6 Months</span>
                    </div>
                    <div id="salesTrendChart" style="min-height: 300px;"></div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="premium-card p-4">
                    <h5 class="font-weight-bold mb-4 text-dark">Critical Alerts</h5>
                    
                    @if($pendingOrdersCount > 0)
                        <div class="alert-item alert-pending">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="font-weight-bold mb-0 text-orange">Pending Orders</h6>
                                    <small class="text-muted">{{ $pendingOrdersCount }} orders waiting for delivery</small>
                                </div>
                                <a href="{{ route('sales.index') }}" class="btn btn-sm btn-white rounded-pill"><i class="fas fa-arrow-right text-orange"></i></a>
                            </div>
                        </div>
                    @endif

                    @if(auth()->user()->role != 'SR')
                        <h6 class="text-muted small font-weight-bold mt-4 mb-3">LOW STOCK PRODUCTS</h6>
                        @forelse($lowStockProducts as $lp)
                            <div class="alert-item alert-low-stock">
                                <div class="d-flex align-items-center">
                                    <img src="{{ asset($lp->image ?? 'themes/backend/dist/img/default-150x150.png') }}" class="rounded mr-2" style="width: 32px; height: 32px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="small font-weight-bold mb-0 text-dark">{{ $lp->name }}</h6>
                                        <small class="text-red">Stock: {{ number_format($lp->quantity, 0) }} left</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <p class="text-center text-muted py-4 small">No low stock items detected.</p>
                        @endforelse
                    @endif
                </div>
            </div>
        </div>

        <!-- Branch/Area Manager Breakdown -->
        @if(count($branchPerformances) > 0)
        <div class="row">
            <div class="col-md-12">
                <div class="premium-card overflow-hidden">
                    <div class="card-header bg-white py-4 border-0">
                        <h5 class="font-weight-bold text-dark m-0">
                            <i class="fas fa-layer-group mr-2 text-primary"></i> 
                            {{ auth()->user()->role == 'Admin' && auth()->user()->activeConcernId() == 1 ? 'Area Manager Performance' : 'Branch Wise Performance' }}
                        </h5>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-premium align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Entity Name</th>
                                    <th>Target Allocation</th>
                                    <th>Achievement</th>
                                    <th class="pr-4" style="width: 250px;">Efficiency</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($branchPerformances as $branch)
                                <tr>
                                    <td class="pl-4 py-3">
                                        <div class="font-weight-bold text-dark">{{ $branch['name'] }}</div>
                                    </td>
                                    <td class="font-weight-bold text-muted">৳{{ number_format($branch['target'], 0) }}</td>
                                    <td class="font-weight-bold text-dark">৳{{ number_format($branch['sales'], 0) }}</td>
                                    <td class="pr-4">
                                        <div class="d-flex align-items-center">
                                            <div class="progress flex-grow-1 mr-3" style="height: 8px; border-radius: 4px; background: #f1f5f9;">
                                                <div class="progress-bar bg-primary" 
                                                     role="progressbar" style="width: {{ min($branch['percentage'], 100) }}%; border-radius: 4px;"></div>
                                            </div>
                                            <span class="small font-weight-bold text-dark">{{ number_format($branch['percentage'], 1) }}%</span>
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
        @endif
        @endif
    </div>
@endsection

@section('script')
    @if($currentLayout != 'modular')
        <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
        <script>
            $(function () {
                // Sales Trend Chart
                var options = {
                    series: [{
                        name: 'Total Sales',
                        data: @json($salesTrendData)
                    }],
                    chart: {
                        type: 'area',
                        height: 350,
                        toolbar: { show: false },
                        zoom: { enabled: false },
                        fontFamily: 'Outfit, sans-serif'
                    },
                    colors: ['#3b82f6'],
                    dataLabels: { enabled: false },
                    stroke: { curve: 'smooth', width: 3 },
                    fill: {
                        type: 'gradient',
                        gradient: {
                            shadeIntensity: 1,
                            opacityFrom: 0.4,
                            opacityTo: 0.1,
                            stops: [0, 90, 100]
                        }
                    },
                    xaxis: {
                        categories: @json($salesTrendLabels),
                        axisBorder: { show: false },
                        axisTicks: { show: false }
                    },
                    yaxis: {
                        labels: {
                            formatter: function (val) {
                                return "৳" + (val/1000).toFixed(0) + "k";
                            }
                        }
                    },
                    tooltip: {
                        y: {
                            formatter: function (val) {
                                return "৳" + val.toLocaleString();
                            }
                        }
                    },
                    grid: {
                        borderColor: '#f1f5f9',
                        strokeDashArray: 4
                    }
                };

                var chart = new ApexCharts(document.querySelector("#salesTrendChart"), options);
                chart.render();
            });
        </script>
    @else
        <script>
        $(document).ready(function() {
            const $searchInput = $('#globalSearchInput');
            const $suggestionsPanel = $('#searchSuggestionsPanel');
            let debounceTimer;

            // Focus search on Ctrl + K
            $(document).on('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                    e.preventDefault();
                    $searchInput.focus();
                }
            });

            // Handle typing with debouncing
            $searchInput.on('input', function() {
                clearTimeout(debounceTimer);
                const query = $(this).val().trim();

                if (query.length === 0) {
                    fetchSuggestions('');
                    return;
                }

                debounceTimer = setTimeout(() => {
                    fetchSuggestions(query);
                }, 200);
            });

            // Close panel when clicking outside
            $(document).on('click', function(e) {
                if (!$(e.target).closest('.global-search-wrapper').length) {
                    $suggestionsPanel.hide();
                }
            });

            // Show suggestions panel when focusing
            $searchInput.on('focus', function() {
                fetchSuggestions($(this).val().trim());
            });

            function fetchSuggestions(query) {
                $.ajax({
                    url: "{{ route('global.search') }}",
                    type: "GET",
                    data: { q: query },
                    dataType: "json",
                    success: function(data) {
                        renderSuggestions(data);
                    },
                    error: function(err) {
                        console.error("Search error:", err);
                    }
                });
            }

            function renderSuggestions(data) {
                $suggestionsPanel.empty();

                if (data.length === 0) {
                    $suggestionsPanel.html('<div class="no-results-item"><i class="fas fa-info-circle mr-2"></i>No matching results found</div>');
                    $suggestionsPanel.show();
                    return;
                }

                // Group by category
                const groups = {};
                data.forEach(item => {
                    if (!groups[item.category]) {
                        groups[item.category] = [];
                    }
                    groups[item.category].push(item);
                });

                // Generate HTML
                let html = '';
                for (const [category, items] of Object.entries(groups)) {
                    html += `<div class="suggestion-group-title">${category}</div>`;
                    items.forEach(item => {
                        html += `
                            <a href="${item.url}" class="suggestion-item">
                                <i class="${item.icon}"></i>
                                <div class="suggestion-info">
                                    <span class="suggestion-title">${item.title}</span>
                                    <span class="suggestion-category-badge">${category}</span>
                                </div>
                            </a>
                        `;
                    });
                }

                $suggestionsPanel.html(html);
                $suggestionsPanel.show();
            }

            // Keyboard Arrow Keys & Enter Navigation
            $searchInput.on('keydown', function(e) {
                const $items = $suggestionsPanel.find('.suggestion-item');
                if (!$items.length) return;

                let $active = $suggestionsPanel.find('.suggestion-item.active-item');

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (!$active.length) {
                        $items.first().addClass('active-item');
                    } else {
                        $active.removeClass('active-item');
                        const $next = $active.nextAll('.suggestion-item').first();
                        if ($next.length) {
                            $next.addClass('active-item');
                        } else {
                            $items.first().addClass('active-item');
                        }
                    }
                    scrollActiveIntoView();
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (!$active.length) {
                        $items.last().addClass('active-item');
                    } else {
                        $active.removeClass('active-item');
                        const $prev = $active.prevAll('.suggestion-item').first();
                        if ($prev.length) {
                            $prev.addClass('active-item');
                        } else {
                            $items.last().addClass('active-item');
                        }
                    }
                    scrollActiveIntoView();
                } else if (e.key === 'Enter') {
                    if ($active.length) {
                        e.preventDefault();
                        window.location.href = $active.attr('href');
                    }
                } else if (e.key === 'Escape') {
                    $suggestionsPanel.hide();
                    $searchInput.blur();
                }
            });

            function scrollActiveIntoView() {
                const $active = $suggestionsPanel.find('.suggestion-item.active-item');
                if (!$active.length) return;

                const panelHeight = $suggestionsPanel.height();
                const activeTop = $active.position().top + $suggestionsPanel.scrollTop();
                const activeHeight = $active.outerHeight();

                if (activeTop < $suggestionsPanel.scrollTop()) {
                    $suggestionsPanel.scrollTop(activeTop);
                } else if (activeTop + activeHeight > $suggestionsPanel.scrollTop() + panelHeight) {
                    $suggestionsPanel.scrollTop(activeTop + activeHeight - panelHeight);
                }
            }
        });
        </script>
    @endif
@endsection
