@extends('layouts.app')
@section('title', 'Trainee Profile - ' . $trainee->name)

@section('style')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        }

        .profile-header {
            background: var(--primary-gradient);
            height: 200px;
            border-radius: 20px;
            position: relative;
            margin-bottom: 80px;
            box-shadow: 0 10px 25px rgba(23, 162, 184, 0.2);
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
            color: #17a2b8;
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
                    @if($trainee->photo)
                        <img src="{{ asset($trainee->photo) }}" class="profile-avatar" alt="Trainee Avatar">
                    @else
                        <img src="{{ asset('img/avatar.png') }}" class="profile-avatar" alt="Trainee Avatar">
                    @endif
                    <div class="status-badge"></div>
                </div>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h1 class="font-weight-bold text-dark mb-1">{{ $trainee->name }}</h1>
                            <p class="text-muted mb-0"><i class="fas fa-id-badge mr-2"></i>Trainee Officer</p>
                        </div>
                        <div class="text-right d-none d-md-block">
                            <span class="badge badge-soft-primary px-3 py-2 rounded-pill" style="background-color: #e3f2fd; color: #0d47a1;">Active Member</span>
                        </div>
                    </div>
                    <div class="mt-3 d-flex flex-wrap">
                        <span class="mr-4 text-muted"><i class="fas fa-phone-alt mr-2 text-info"></i> {{ $trainee->mobile_no }}</span>
                        <span class="mr-4 text-muted"><i class="fas fa-envelope mr-2 text-info"></i> {{ $trainee->email ?? 'N/A' }}</span>
                        <span class="text-muted"><i class="fas fa-map-marker-alt mr-2 text-info"></i> {{ $trainee->currentUpazila->name ?? '' }}, {{ $trainee->currentDistrict->name ?? '' }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-5">
            {{-- Left Column: Stats & Location --}}
            <div class="col-lg-4 mt-4 mt-lg-0">
                <div class="card border-0 shadow-sm rounded-20 mb-4" style="border-radius: 20px;">
                    <div class="card-body p-4">
                        <h5 class="font-weight-bold mb-4">Trainee Information</h5>
                        <div class="info-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-building"></i>
                                <div>
                                    <small class="text-muted d-block">Sister Concern</small>
                                    <span class="font-weight-bold text-dark">{{ $trainee->sisterConcern->name ?? 'Main Branch' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="info-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marked-alt"></i>
                                <div>
                                    <small class="text-muted d-block">Operating Area</small>
                                    <span class="font-weight-bold text-dark">{{ $trainee->currentUnion->name ?? '' }}, {{ $trainee->currentUpazila->name ?? '' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="info-pill">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-clock"></i>
                                <div>
                                    <small class="text-muted d-block">Joined On</small>
                                    <span class="font-weight-bold text-dark">{{ $trainee->created_at->format('d M, Y') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Right Column: Activity --}}
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-20" style="border-radius: 20px;">
                    <div class="card-header bg-white border-0 pt-4 px-4">
                        <h5 class="font-weight-bold mb-0"><i class="fas fa-clipboard-list text-info mr-2"></i> Recent Activity</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="mb-3 opacity-5">
                            <h5 class="text-muted">No recent activity found.</h5>
                            <p class="text-muted small mb-0">Task distribution and tracking data will appear here.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
