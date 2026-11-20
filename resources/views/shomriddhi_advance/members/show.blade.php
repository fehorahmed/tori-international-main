@extends('layouts.app')
@section('title', 'Member Profile')
@section('style')
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap');

        * {
            font-family: 'Inter', sans-serif;
        }

        .member-hero {
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            border-radius: 20px;
            padding: 40px 35px 30px;
            color: #fff;
            position: relative;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .member-hero::before {
            content: '';
            position: absolute;
            top: -60px;
            right: -60px;
            width: 220px;
            height: 220px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
        }

        .member-hero::after {
            content: '';
            position: absolute;
            bottom: -80px;
            left: -40px;
            width: 280px;
            height: 280px;
            background: rgba(255, 255, 255, 0.04);
            border-radius: 50%;
        }

        .hero-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .hero-avatar-placeholder {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            font-weight: 700;
            color: #fff;
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            flex-shrink: 0;
        }

        .hero-status-dot {
            width: 14px;
            height: 14px;
            border-radius: 50%;
            border: 2px solid #fff;
            display: inline-block;
            vertical-align: middle;
            margin-right: 6px;
        }

        .badge-member-no {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff;
            border-radius: 20px;
            padding: 4px 14px;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            backdrop-filter: blur(6px);
        }

        /* Stat Cards */
        .stat-card {
            border-radius: 14px;
            padding: 20px 22px;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.12);
            transition: transform 0.25s, box-shadow 0.25s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.18);
        }

        .stat-card .stat-icon {
            position: absolute;
            right: 18px;
            top: 18px;
            font-size: 32px;
            opacity: 0.25;
        }

        .stat-card .stat-label {
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.85;
        }

        .stat-card .stat-value {
            font-size: 22px;
            font-weight: 800;
            margin-top: 4px;
        }

        .stat-card .stat-sub {
            font-size: 11px;
            margin-top: 2px;
            opacity: 0.7;
        }

        .stat-blue {
            background: linear-gradient(135deg, #1a73e8, #0d47a1);
        }

        .stat-green {
            background: linear-gradient(135deg, #0f9d58, #1b5e20);
        }

        .stat-orange {
            background: linear-gradient(135deg, #f4511e, #bf360c);
        }

        .stat-purple {
            background: linear-gradient(135deg, #9c27b0, #4a148c);
        }

        /* Info section */
        .info-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 18px rgba(0, 0, 0, 0.07);
            overflow: hidden;
            margin-bottom: 22px;
        }

        .info-card .info-card-header {
            padding: 16px 22px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .info-card .info-card-header .icon-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }

        .info-card .info-card-header h6 {
            margin: 0;
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            color: #333;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 13px 22px;
            border-bottom: 1px solid #f7f7f7;
            gap: 12px;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-row .info-key {
            font-size: 12px;
            font-weight: 600;
            color: #888;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            min-width: 130px;
            flex-shrink: 0;
        }

        .info-row .info-val {
            font-size: 14px;
            font-weight: 600;
            color: #1a1a2e;
        }

        .info-row .info-icon {
            width: 28px;
            height: 28px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            flex-shrink: 0;
        }

        /* Action buttons */
        .btn-action {
            border-radius: 10px;
            padding: 10px 20px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.25s;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .btn-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.2);
        }

        .btn-edit {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: #fff;
        }

        .btn-back {
            background: #f0f4f8;
            color: #555;
        }

        .btn-back:hover {
            background: #e2e8f0;
            color: #333;
        }

        /* Balance indicator */
        .balance-chip {
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 700;
        }

        .balance-positive {
            background: #fff3e0;
            color: #e65100;
        }

        .balance-negative {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .balance-zero {
            background: #f3f4f6;
            color: #6b7280;
        }
    </style>
@endsection

@section('content')

    {{-- Hero Banner --}}
    <div class="member-hero">
        <div class="d-flex align-items-center" style="gap: 24px; position: relative; z-index: 2;">
            @if ($member->image)
                <img src="{{ asset($member->image) }}" alt="Profile" class="hero-avatar">
            @else
                <div class="hero-avatar-placeholder">{{ strtoupper(substr($member->name, 0, 1)) }}</div>
            @endif

            <div class="flex-grow-1">
                <div class="d-flex align-items-center flex-wrap" style="gap: 10px; margin-bottom: 6px;">
                    <h3 class="mb-0 font-weight-bold" style="font-size: 22px;">{{ $member->name }}</h3>
                    <span class="badge-member-no">{{ $member->member_no ?? 'No ID' }}</span>
                </div>
                <div class="d-flex align-items-center flex-wrap" style="gap: 16px; margin-bottom: 12px;">
                    <span style="font-size: 13px; opacity: 0.8;">
                        <i class="fas fa-phone-alt mr-1"></i> {{ $member->mobile_no }}
                    </span>
                    @if ($member->email)
                        <span style="font-size: 13px; opacity: 0.8;">
                            <i class="fas fa-envelope mr-1"></i> {{ $member->email }}
                        </span>
                    @endif
                </div>
                <span>
                    <span class="hero-status-dot"
                        style="background: {{ $member->status == 1 ? '#4caf50' : '#f44336' }};"></span>
                    <span style="font-size: 12px; font-weight: 600; opacity: 0.9;">
                        {{ $member->status == 1 ? 'Active Member' : 'Inactive Member' }}
                    </span>
                </span>
            </div>

            <div class="d-flex flex-column" style="gap: 8px; position: relative; z-index: 2;">
                @if (auth()->user()->can('customer_edit'))
                    <a href="{{ route('shomriddhi-advance.members.edit', ['member' => $member->id]) }}"
                        class="btn btn-action btn-edit">
                        <i class="fas fa-edit"></i> Edit Profile
                    </a>
                @endif
                <a href="{{ route('shomriddhi-advance.members.index') }}" class="btn btn-action btn-back">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>
    </div>

    {{-- Stat Cards --}}
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card stat-blue">
                <i class="fas fa-id-badge stat-icon"></i>
                <div class="stat-label">Member No</div>
                <div class="stat-value" style="font-size: 16px;">{{ $member->member_no ?? 'N/A' }}</div>
                <div class="stat-sub">Unique ID</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card stat-green">
                <i class="fas fa-wallet stat-icon"></i>
                <div class="stat-label">Savings Balance</div>
                <div class="stat-value">৳{{ number_format($savingsBalance, 2) }}</div>
                <div class="stat-sub">Available: ৳{{ number_format($availableBalance, 2) }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card stat-purple">
                <i class="fas fa-map-marker-alt stat-icon"></i>
                <div class="stat-label">District</div>
                <div class="stat-value" style="font-size: 15px;">{{ $member->district->name ?? 'N/A' }}</div>
                <div class="stat-sub">{{ $member->division->name ?? '' }}</div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="stat-card stat-orange">
                <i class="fas fa-calendar-alt stat-icon"></i>
                <div class="stat-label">Joined</div>
                <div class="stat-value" style="font-size: 15px;">
                    {{ $member->created_at ? $member->created_at->format('d M') : 'N/A' }}</div>
                <div class="stat-sub">{{ $member->created_at ? $member->created_at->format('Y') : '' }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Personal Info --}}
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="icon-circle" style="background:#ede7f6;">
                        <i class="fas fa-user" style="color:#7b1fa2;"></i>
                    </div>
                    <h6>Personal Information</h6>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#e3f2fd;"><i class="fas fa-user"
                            style="color:#1565c0;font-size:11px;"></i></div>
                    <div class="info-key">Full Name</div>
                    <div class="info-val">{{ $member->name }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fce4ec;"><i class="fas fa-phone-alt"
                            style="color:#c62828;font-size:11px;"></i></div>
                    <div class="info-key">Mobile No.</div>
                    <div class="info-val">{{ $member->mobile_no }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e8f5e9;"><i class="fas fa-envelope"
                            style="color:#2e7d32;font-size:11px;"></i></div>
                    <div class="info-key">Email</div>
                    <div class="info-val">{{ $member->email ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fff3e0;"><i class="fas fa-male"
                            style="color:#e65100;font-size:11px;"></i></div>
                    <div class="info-key">Father's Name</div>
                    <div class="info-val">{{ $member->father_name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fce4ec;"><i class="fas fa-female"
                            style="color:#ad1457;font-size:11px;"></i></div>
                    <div class="info-key">Mother's Name</div>
                    <div class="info-val">{{ $member->mother_name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e0f7fa;"><i class="fas fa-circle"
                            style="color:#00838f;font-size:11px;"></i></div>
                    <div class="info-key">Status</div>
                    <div class="info-val">
                        @if ($member->status == 1)
                            <span class="badge badge-pill"
                                style="background:#dcfce7; color:#15803d; font-size:12px; padding:5px 12px;">Active</span>
                        @else
                            <span class="badge badge-pill"
                                style="background:#fee2e2; color:#dc2626; font-size:12px; padding:5px 12px;">Inactive</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Address Info --}}
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="icon-circle" style="background:#e8f5e9;">
                        <i class="fas fa-map-marked-alt" style="color:#2e7d32;"></i>
                    </div>
                    <h6>Address Information</h6>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#e3f2fd;"><i class="fas fa-globe"
                            style="color:#1565c0;font-size:11px;"></i></div>
                    <div class="info-key">Division</div>
                    <div class="info-val">{{ $member->division->name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#ede7f6;"><i class="fas fa-city"
                            style="color:#6a1b9a;font-size:11px;"></i></div>
                    <div class="info-key">District</div>
                    <div class="info-val">{{ $member->district->name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e8eaf6;"><i class="fas fa-map"
                            style="color:#283593;font-size:11px;"></i></div>
                    <div class="info-key">Upazila</div>
                    <div class="info-val">{{ $member->upazila->name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e0f7fa;"><i class="fas fa-map-pin"
                            style="color:#00695c;font-size:11px;"></i></div>
                    <div class="info-key">Union / P-Shava</div>
                    <div class="info-val">
                        {{ $member->union->name ?? ($member->pourashava->name ?? '—') }}
                        @if ($member->union)
                            <small class="text-muted">(U)</small>
                        @endif
                        @if ($member->pourashava)
                            <small class="text-muted">(P)</small>
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fff8e1;"><i class="fas fa-home"
                            style="color:#f57f17;font-size:11px;"></i></div>
                    <div class="info-key">Village / Road</div>
                    <div class="info-val">{{ $member->village->name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fce4ec;"><i class="fas fa-map-marker-alt"
                            style="color:#b71c1c;font-size:11px;"></i></div>
                    <div class="info-key">Full Address</div>
                    <div class="info-val" style="font-size:12px; line-height:1.5;">
                        @php
                            $addr = [];
                            if ($member->village) {
                                $addr[] = $member->village->name;
                            }
                            if ($member->union) {
                                $addr[] = $member->union->name . ' (U)';
                            }
                            if ($member->pourashava) {
                                $addr[] = $member->pourashava->name . ' (P)';
                            }
                            if ($member->upazila) {
                                $addr[] = $member->upazila->name;
                            }
                            if ($member->district) {
                                $addr[] = $member->district->name;
                            }
                            if ($member->division) {
                                $addr[] = $member->division->name;
                            }
                            echo implode(', ', $addr) ?: '—';
                        @endphp
                    </div>
                </div>
            </div>
        </div>
        {{-- Nominee Info --}}
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="icon-circle" style="background:#fff3e0;">
                        <i class="fas fa-users" style="color:#e65100;"></i>
                    </div>
                    <h6>Nominee Information</h6>
                </div>

                <div class="info-row">
                    <div class="info-icon" style="background:#e3f2fd;"><i class="fas fa-user"
                            style="color:#1565c0;font-size:11px;"></i></div>
                    <div class="info-key">Name</div>
                    <div class="info-val">{{ $nominee->name ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fce4ec;"><i class="fas fa-heart"
                            style="color:#c62828;font-size:11px;"></i></div>
                    <div class="info-key">Relation</div>
                    <div class="info-val">{{ $nominee->relation ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e8f5e9;"><i class="fas fa-id-card"
                            style="color:#2e7d32;font-size:11px;"></i></div>
                    <div class="info-key">NID Number</div>
                    <div class="info-val">{{ $nominee->nid_number ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#fff3e0;"><i class="fas fa-phone-alt"
                            style="color:#e65100;font-size:11px;"></i></div>
                    <div class="info-key">Phone</div>
                    <div class="info-val">{{ $nominee->phone ?? '—' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e8eaf6;"><i class="fas fa-map-marker-alt"
                            style="color:#283593;font-size:11px;"></i></div>
                    <div class="info-key">Full Address</div>
                    <div class="info-val" style="font-size:12px; line-height:1.5;">
                        @if ($nominee)
                            @php
                                $n_addr = [];
                                $n_village = \App\Models\Village::find($nominee->village_id);
                                $n_union = \App\Models\Union::find($nominee->union_id);
                                $n_pourashava = \App\Models\Pourashava::find($nominee->pourashava_id);
                                $n_upazila = \App\Models\Upazila::find($nominee->upazila_id);
                                $n_district = \App\Models\District::find($nominee->district_id);
                                $n_division = \App\Models\Division::find($nominee->division_id);

                                if ($n_village) {
                                    $n_addr[] = $n_village->name;
                                }
                                if ($n_union) {
                                    $n_addr[] = $n_union->name . ' (U)';
                                }
                                if ($n_pourashava) {
                                    $n_addr[] = $n_pourashava->name . ' (P)';
                                }
                                if ($n_upazila) {
                                    $n_addr[] = $n_upazila->name;
                                }
                                if ($n_district) {
                                    $n_addr[] = $n_district->name;
                                }
                                if ($n_division) {
                                    $n_addr[] = $n_division->name;
                                }

                                echo implode(', ', $n_addr) ?: '—';
                            @endphp
                        @else
                            —
                        @endif
                    </div>
                </div>
                <div class="info-row">
                    <div class="info-icon" style="background:#e0f7fa;"><i class="fas fa-image"
                            style="color:#00838f;font-size:11px;"></i></div>
                    <div class="info-key">Image</div>
                    <div class="info-val">
                        @if ($nominee && $nominee->image)
                            <img src="{{ asset($nominee->image) }}" width="60" class="img-thumbnail rounded"
                                alt="Nominee Image">
                        @else
                            <span class="text-muted">No Image</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="icon-circle" style="background:#e8f5e9;"><i class="fas fa-user-friends text-success"></i>
                    </div>
                    <h6>Referral Information</h6>
                </div>
                <div class="p-3">
                    <dl class="row mb-0" style="overflow-wrap:anywhere;">
                        <dt class="col-sm-5">Own Referral ID</dt>
                        <dd class="col-sm-7">{{ $member->member_no ?: '—' }}</dd>
                        <dt class="col-sm-5">Members Referred</dt>
                        <dd class="col-sm-7">{{ $referralCount }}</dd>
                        <dt class="col-sm-5">Available Points</dt>
                        <dd class="col-sm-7">
                            {{ rtrim(rtrim(number_format($referralPoints, 6, '.', ''), '0'), '.') ?: '0' }}
                        </dd>
                        @if ($referredBy)
                            <dt class="col-sm-5">Referred By</dt>
                            <dd class="col-sm-7">{{ $referredBy['name'] ?: 'Unavailable' }}</dd>
                            <dt class="col-sm-5">Referrer Type</dt>
                            <dd class="col-sm-7">{{ ucfirst($referredBy['type']) }}</dd>
                            <dt class="col-sm-5">Code at Registration</dt>
                            <dd class="col-sm-7">{{ $referredBy['code'] }}</dd>
                            <dt class="col-sm-5">Referrer Bonus</dt>
                            <dd class="col-sm-7">
                                @if (!$referralReward)
                                    Not awarded yet
                                @elseif($referralReward->referrer_type === 'member')
                                    {{ $referralReward->points }} points awarded
                                @else
                                    Tk {{ number_format($referralReward->amount, 2) }} —
                                    {{ $referralReward->paid_at ? 'Paid' : 'Payment pending' }}
                                @endif
                            </dd>
                        @else
                            <dt class="col-sm-5">Referred By</dt>
                            <dd class="col-sm-7">No referral recorded</dd>
                        @endif
                        <dt class="col-sm-5">Registration Fee</dt>
                        <dd class="col-sm-7">
                            @if ($registrationFee)
                                Tk {{ number_format($registrationFee->amount, 2) }} —
                                {{ $registrationFee->paid_at ? 'Paid' : 'Pending' }}
                            @else
                                No fee record
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="icon-circle" style="background:#e3f2fd;"><i class="fas fa-id-card text-primary"></i>
                    </div>
                    <h6>KYC Information</h6>
                </div>
                <div class="p-3">
                    <dl class="row mb-0" style="overflow-wrap:anywhere;">
                        <dt class="col-sm-5">KYC Status</dt>
                        <dd class="col-sm-7">{{ $kycStatus === 'Verified' ? 'Approved' : $kycStatus }}</dd>
                        @if ($kyc)
                            <dt class="col-sm-5">Submitted Name</dt>
                            <dd class="col-sm-7">{{ $kyc->submitted_name }}</dd>
                            <dt class="col-sm-5">NID Number</dt>
                            <dd class="col-sm-7">{{ $kyc->nid_number }}</dd>
                            <dt class="col-sm-5">Submitted At</dt>
                            <dd class="col-sm-7">{{ $kyc->created_at }}</dd>
                            <dt class="col-sm-5">Reviewed At</dt>
                            <dd class="col-sm-7">{{ $kyc->reviewed_at ?: '—' }}</dd>
                            <dt class="col-sm-5">Review Note</dt>
                            <dd class="col-sm-7" style="white-space:pre-wrap;">{{ $kyc->reviewer_message ?: '—' }}
                            </dd>
                        @endif
                    </dl>
                    @if ($kyc && $canViewKycDocuments)
                        <div class="row mt-2">
                            @foreach (['selfie' => 'Selfie', 'nid_front' => 'NID Front', 'nid_back' => 'NID Back'] as $kind => $label)
                                <div class="col-sm-4 mb-3">
                                    <a href="{{ route('card-management.kyc.document', [$kyc, $kind]) }}" target="_blank"
                                        rel="noopener">
                                        <img src="{{ route('card-management.kyc.document', [$kyc, $kind]) }}"
                                            alt="{{ $label }}" loading="lazy" class="img-thumbnail"
                                            style="width:100%;height:110px;object-fit:contain;">
                                        {{ $label }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <a class="btn btn-sm btn-outline-primary" href="{{ route('card-management.kyc.index') }}">KYC
                            Review
                            List</a>
                    @endif
                </div>
            </div>
        </div>


        {{-- Quick Actions --}}
        <div class="col-md-6">
            <div class="info-card">
                <div class="info-card-header">
                    <div class="icon-circle" style="background:#fff3e0;">
                        <i class="fas fa-bolt" style="color:#e65100;"></i>
                    </div>
                    <h6>Quick Actions</h6>
                </div>
                <div class="p-3 d-flex flex-wrap" style="gap: 10px;">
                    @if (auth()->user()->can('customer_edit'))
                        <a href="{{ route('shomriddhi-advance.members.edit', ['member' => $member->id]) }}"
                            class="btn btn-sm"
                            style="background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;border-radius:8px;padding:8px 16px;font-weight:600;font-size:12px;">
                            <i class="fas fa-edit mr-1"></i> Edit Profile
                        </a>
                    @endif
                    <a href="{{ route('shomriddhi-advance.members.index') }}" class="btn btn-sm"
                        style="background:#f1f5f9;color:#475569;border-radius:8px;padding:8px 16px;font-weight:600;font-size:12px;">
                        <i class="fas fa-list mr-1"></i> All Members
                    </a>
                </div>
            </div>
        </div>
    </div>

@endsection
