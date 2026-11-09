@extends('layouts.app')
@section('title', 'SR Profile - ' . $sr->name)

@section('style')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
            --success-gradient: linear-gradient(135deg, #22c55e 0%, #10b981 100%);
            --warning-gradient: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --danger-gradient: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        }

        .profile-header {
            background: var(--primary-gradient);
            height: 200px;
            border-radius: 20px;
            position: relative;
            margin-bottom: 80px;
            box-shadow: 0 10px 25px rgba(99, 102, 241, 0.2);
        }

        .profile-main-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            position: absolute;
            top: 100px;
            left: 30px;
            right: 30px;
            display: flex;
            align-items: center;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            border: 1px solid rgba(255,255,255,0.3);
        }

        .profile-avatar-wrapper {
            position: relative;
            margin-right: 30px;
        }

        .profile-avatar {
            width: 130px;
            height: 130px;
            border-radius: 30px;
            object-fit: cover;
            border: 5px solid white;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }

        .status-badge {
            position: absolute;
            bottom: -5px;
            right: -5px;
            background: #22c55e;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            border: 4px solid white;
        }

        .info-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }
        .info-pill:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
        .info-pill i {
            font-size: 1.2rem;
            width: 35px;
            color: #6366f1;
        }

        .score-card {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            border: none;
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }

        .task-item-card {
            background: white;
            border-radius: 18px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s ease;
        }
        .task-item-card:hover {
            box-shadow: 0 10px 20px rgba(0,0,0,0.05);
        }

        .task-icon-box {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-right: 15px;
        }

        .progress-premium {
            height: 12px;
            border-radius: 10px;
            background: #f1f5f9;
            overflow: hidden;
        }

        /* Circular Progress */
        .circular-progress-wrapper {
            width: 180px;
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto;
        }
        .circular-progress {
            width: 100%;
            height: 100%;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.8s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .inner-circle {
            width: 82%;
            height: 82%;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: inset 0 5px 15px rgba(0,0,0,0.05);
        }

        @media (max-width: 768px) {
            .profile-main-card {
                flex-direction: column;
                text-align: center;
                top: 50px;
            }
            .profile-avatar-wrapper {
                margin-right: 0;
                margin-bottom: 20px;
            }
            .profile-header {
                height: 150px;
                margin-bottom: 250px;
            }
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid p-4">
        {{-- Premium Header Section --}}
        <div class="profile-header">
            <div class="profile-main-card">
                <div class="profile-avatar-wrapper">
                    @if($sr->photo)
                        <img src="{{ asset($sr->photo) }}" class="profile-avatar" alt="SR Avatar">
                    @else
                        <img src="{{ asset('img/avatar.png') }}" class="profile-avatar" alt="SR Avatar">
                    @endif
                    <div class="status-badge"></div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="font-weight-bold text-dark mb-1">{{ $sr->name }}</h1>
                            <p class="text-muted mb-0"><i class="fas fa-id-badge mr-2"></i>Sales Representative (SR)</p>
                        </div>
                        <div class="text-right d-none d-md-block">
                            <span class="badge badge-soft-primary px-3 py-2 rounded-pill">Active Since {{ $sr->created_at->format('M Y') }}</span>
                        </div>
                    </div>
                    <div class="mt-3 d-flex flex-wrap">
                        <span class="mr-4 text-muted"><i class="fas fa-phone-alt mr-2 text-primary"></i> {{ $sr->mobile_no }}</span>
                        <span class="mr-4 text-muted"><i class="fas fa-envelope mr-2 text-primary"></i> {{ $sr->email ?? 'N/A' }}</span>
                        <span class="text-muted"><i class="fas fa-map-marker-alt mr-2 text-primary"></i> {{ $sr->currentUpazila->name ?? '' }}, {{ $sr->currentDistrict->name ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            {{-- Left Column: Stats & Location --}}
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-20 mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-4">Account Information</h5>
                        <div class="info-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-wallet"></i>
                                <div>
                                    <small class="text-muted d-block">Opening Balance</small>
                                    <span class="font-weight-bold text-dark">৳{{ number_format($sr->opening_balance, 2) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="info-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building"></i>
                                <div>
                                    <small class="text-muted d-block">Sister Concern</small>
                                    <span class="font-weight-bold text-dark">{{ $sr->sisterConcern->name ?? 'Main Branch' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="info-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marked-alt"></i>
                                <div>
                                    <small class="text-muted d-block">Operating Area</small>
                                    <span class="font-weight-bold text-dark">{{ $sr->currentUnion->name ?? '' }}, {{ $sr->currentUpazila->name ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Performance --}}
            <div class="col-lg-8">
                    @php
                        $totalPercent = 0;
                        foreach($tasks as $t) {
                            $p = $t->target_value > 0 ? ($t->actual_value / $t->target_value) * 100 : 0;
                            $totalPercent += min(100, $p);
                        }
                        $overallPercent = $tasks->count() > 0 ? $totalPercent / $tasks->count() : 0;
                        $gradCol = '#ef4444';
                        $textCol = 'text-danger';
                        if($overallPercent >= 80) { $gradCol = '#10b981'; $textCol = 'text-success'; }
                        elseif($overallPercent >= 50) { $gradCol = '#f59e0b'; $textCol = 'text-warning'; }
                    @endphp
                    
                    <div class="score-card mb-4">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <div class="position-relative d-inline-block">
                                    {{-- Circular Progress --}}
                                    <div class="circular-progress-wrapper">
                                        <div class="circular-progress" style="background: conic-gradient({{ $gradCol }} {{ $overallPercent * 3.6 }}deg, #f1f5f9 0deg);">
                                            <div class="inner-circle">
                                                <h1 class="display-4 font-weight-bold mb-0 {{ $textCol }}">{{ number_format($overallPercent, 1) }}%</h1>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-7 border-left">
                                <div class="px-4 text-left">
                                    <h5 class="text-muted mb-3 font-weight-bold text-uppercase">Monthly Achievement Score</h5>
                                    <div class="h3 font-weight-bold mb-2">{{ $overallPercent >= 80 ? 'Exceptional' : ($overallPercent >= 50 ? 'Steady' : 'Developing') }} Performance</div>
                                    <p class="text-muted mb-4">The score represents the average completion rate across all {{ $tasks->count() }} assigned tasks for this month.</p>
                                    
                                    <div class="d-flex align-items-center">
                                        <div class="mr-4">
                                            <small class="text-muted d-block">Period</small>
                                            <span class="font-weight-bold">{{ date('F 1 - t, Y') }}</span>
                                        </div>
                                        <div>
                                            <small class="text-muted d-block">Status</small>
                                            <span class="badge {{ str_replace('text-', 'badge-soft-', $textCol) }} px-3 rounded-pill">Active Evaluation</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                <h4 class="font-weight-bold mb-4 px-2">Detailed Task Breakdown</h4>
                <div class="row">
                    @forelse($tasks as $task)
                        @php
                            $percent = $task->target_value > 0 ? ($task->actual_value / $task->target_value) * 100 : 0;
                            $percent = min(100, $percent);
                            
                            $iconClass = 'bg-primary'; $icon = 'fa-chart-line';
                            if(str_contains($task->type->slug, 'visit')) { $iconClass = 'bg-success'; $icon = 'fa-map-marker-alt'; }
                            elseif(str_contains($task->type->slug, 'order')) { $iconClass = 'bg-danger'; $icon = 'fa-shopping-cart'; }
                            elseif(str_contains($task->type->slug, 'payment')) { $iconClass = 'bg-purple'; $icon = 'fa-money-bill-wave'; }
                            
                            $progCol = '#ef4444';
                            if($percent >= 100) $progCol = '#10b981';
                            elseif($percent >= 50) $progCol = '#f59e0b';
                        @endphp
                        <div class="col-md-6">
                            <div class="task-item-card">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="task-icon-box {{ $iconClass }} text-white shadow-sm">
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                    <div>
                                        <h6 class="font-weight-bold mb-0 text-dark">{{ $task->type->name }}</h6>
                                        <small class="text-muted">{{ $task->type->unit }} based goal</small>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between align-items-end mb-2">
                                    <div class="h5 mb-0 font-weight-bold text-dark">
                                        {{ $task->actual_value }} <span class="text-muted small">/ {{ $task->target_value }}</span>
                                    </div>
                                    <div class="font-weight-bold" style="color: {{ $progCol }}">
                                        {{ number_format($percent, 1) }}%
                                    </div>
                                </div>
                                <div class="progress-premium">
                                    <div class="progress-bar" style="width: {{ $percent }}%; background: {{ $progCol }};"></div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="alert alert-light text-center py-5 shadow-sm rounded-20" style="border-radius: 20px;">
                                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-5">
                                <h5 class="text-muted">No specific tasks assigned for this month.</h5>
                                <p class="text-muted small mb-0">Check back later or contact your supervisor.</p>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
