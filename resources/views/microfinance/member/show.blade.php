@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .member-profile-container .profile-card {
        border: none;
        border-radius: 20px;
        background: #fff;
        transition: all 0.3s ease;
        overflow: visible !important;
    }
    .member-profile-container .profile-header {
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
        height: 120px;
        border-radius: 20px 20px 0 0;
        position: relative;
        z-index: 1;
    }
    .member-profile-container .profile-img-container {
        margin-top: -65px;
        position: relative;
        display: inline-block;
        z-index: 99;
    }
    .member-profile-container .profile-img-container img {
        width: 130px;
        height: 130px;
        object-fit: cover;
        border: 5px solid #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        background: #fff;
    }
    .member-profile-container .stat-card {
        border: none;
        border-radius: 15px;
        transition: transform 0.3s;
        overflow: hidden;
    }
    .member-profile-container .stat-card:hover {
        transform: translateY(-5px);
    }
    .member-profile-container #profileTabs .nav-link {
        border-radius: 10px;
        padding: 10px 20px;
        color: #666;
        font-weight: 500;
    }
    .member-profile-container #profileTabs .nav-link.active {
        background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%);
        box-shadow: 0 4px 15px rgba(23, 162, 184, 0.3);
        color: #fff !important;
    }
    .member-profile-container .info-label {
        color: #999;
        font-size: 0.85rem;
        margin-bottom: 2px;
        display: block;
    }
    .member-profile-container .info-value {
        color: #333;
        font-weight: 600;
        font-size: 1rem;
    }
    .member-profile-container .doc-card {
        border: 2px dashed #ddd;
        border-radius: 15px;
        padding: 10px;
        background: #f9f9f9;
        transition: border-color 0.3s;
    }
    .member-profile-container .doc-card:hover {
        border-color: #17a2b8;
    }
</style>
@endsection

@section('content')
<div class="container-fluid member-profile-container">
    <div class="row">
        <!-- Sidebar / Basic Info -->
        <div class="col-xl-4 col-lg-5">
            <div class="card profile-card shadow-sm mb-4">
                <div class="profile-header"></div>
                <div class="card-body text-center pt-0" style="overflow: visible !important;">
                    <div class="profile-img-container">
                        <img src="{{ $member->photo ? asset($member->photo) : asset('img/no-image.webp') }}" class="rounded-circle" alt="Member Photo">
                        <span class="badge {{ $member->status == 1 ? 'badge-success' : 'badge-danger' }}" style="position: absolute; bottom: 10px; right: 10px; padding: 6px 12px; border-radius: 20px; border: 2px solid #fff;">
                            {{ $member->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <h3 class="mt-3 mb-0 font-weight-bold">{{ $member->name }}</h3>
                    <p class="text-muted mb-2">{{ $member->name_bn }}</p>
                    <div class="d-flex justify-content-center mb-3">
                        <span class="badge badge-light px-3 py-2" style="border-radius: 10px; border: 1px solid #eee;">
                            <i class="fa fa-id-badge text-info"></i> {{ $member->member_no }}
                        </span>
                        <span class="badge badge-light px-3 py-2 ml-2" style="border-radius: 10px; border: 1px solid #eee;">
                            <i class="fa fa-tint text-danger"></i> {{ $member->blood_group ?? 'N/A' }}
                        </span>
                    </div>
                    
                    <div class="row text-left mt-4 px-3">
                        <div class="col-6 mb-3">
                            <span class="info-label">Mobile</span>
                            <span class="info-value">{{ $member->mobile_no }}</span>
                        </div>
                        <div class="col-6 mb-3">
                            <span class="info-label">Branch</span>
                            <span class="info-value text-truncate d-block">{{ $member->sisterConcern->name }}</span>
                        </div>
                        <div class="col-12">
                            <span class="info-label">Present Address</span>
                            <span class="info-value" style="font-weight: 400;">{{ $member->full_present_address }}</span>
                        </div>
                    </div>

                    <div class="mt-4 pb-2">
                        <a href="{{ route('microfinance.member.edit', $member->id) }}" class="btn btn-outline-info px-4" style="border-radius: 10px;">
                            <i class="fa fa-edit"></i> Edit Profile
                        </a>
                    </div>
                </div>
            </div>

            <!-- Nominees Section -->
            <div class="card member-profile-container shadow-sm mt-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="font-weight-bold mb-0">Nominee(s) Information</h5>
                </div>
                <div class="card-body px-4">
                    @forelse($member->nominees as $nominee)
                        <div class="mb-4 p-3 bg-light" style="border-radius: 15px; border-left: 5px solid #17a2b8;">
                            <div class="d-flex align-items-center mb-3">
                                @if($nominee->photo)
                                    <img src="{{ asset($nominee->photo) }}" width="60" height="60" class="rounded-circle shadow-sm mr-3" style="object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 60px; height: 60px;">
                                        <i class="fa fa-user text-white"></i>
                                    </div>
                                @endif
                                <div>
                                    <h6 class="mb-0 font-weight-bold">{{ $nominee->name }}</h6>
                                    <span class="text-muted small">{{ $nominee->relation }}</span>
                                </div>
                            </div>
                            <div class="small">
                                <p class="mb-1"><strong>Phone:</strong> {{ $nominee->phone }}</p>
                                <p class="mb-1"><strong>NID:</strong> {{ $nominee->nid_no }}</p>
                                <p class="mb-1"><strong>Address:</strong> {{ $nominee->full_address }}</p>
                                <div class="mt-2">
                                    @if($nominee->signature_photo)
                                        <a href="{{ asset($nominee->signature_photo) }}" target="_blank" class="badge badge-primary mr-1">Signature</a>
                                    @endif
                                    @if($nominee->nid_photo_front)
                                        <a href="{{ asset($nominee->nid_photo_front) }}" target="_blank" class="badge badge-info mr-1">NID Front</a>
                                    @endif
                                    @if($nominee->nid_photo_back)
                                        <a href="{{ asset($nominee->nid_photo_back) }}" target="_blank" class="badge badge-info">NID Back</a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">No nominee information added.</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Main Details -->
        <div class="col-xl-8 col-lg-7">
            <!-- Stats Boxes -->
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card stat-card shadow-sm bg-gradient-success text-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block small">Total Savings</span>
                                    <h4 class="mb-0 font-weight-bold">৳ 0.00</h4>
                                </div>
                                <i class="fa fa-piggy-bank fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card shadow-sm bg-gradient-warning text-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block small">Active Loan</span>
                                    <h4 class="mb-0 font-weight-bold">৳ 0.00</h4>
                                </div>
                                <i class="fa fa-hand-holding-usd fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="card stat-card shadow-sm bg-gradient-info text-white">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <span class="d-block small">Total Accounts</span>
                                    <h4 class="mb-0 font-weight-bold">{{ $member->accounts->count() }}</h4>
                                </div>
                                <i class="fa fa-university fa-2x opacity-50"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div class="card profile-card shadow-sm">
                <div class="card-header bg-white border-0 p-3">
                    <ul class="nav nav-pills" id="profileTabs">
                        <li class="nav-item">
                            <a class="nav-link active" data-toggle="pill" href="#personal"><i class="fa fa-info-circle mr-2"></i>Personal Details</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#documents"><i class="fa fa-file-invoice mr-2"></i>Documents</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-toggle="pill" href="#activity"><i class="fa fa-history mr-2"></i>Recent Activity</a>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">
                        <!-- Personal Tab -->
                        <div class="tab-pane fade show active" id="personal">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <h6 class="text-info font-weight-bold mb-3">Family Information</h6>
                                    <div class="mb-2">
                                        <span class="info-label">Father's Name</span>
                                        <span class="info-value">{{ $member->father_name }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="info-label">Mother's Name</span>
                                        <span class="info-value">{{ $member->mother_name }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="info-label">Spouse Name</span>
                                        <span class="info-value">{{ $member->spouse_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="info-label">Total Family Members</span>
                                        <span class="info-value">{{ $member->total_family_members }}</span>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <h6 class="text-info font-weight-bold mb-3">Other Details</h6>
                                    <div class="mb-2">
                                        <span class="info-label">Date of Birth</span>
                                        <span class="info-value">{{ \Carbon\Carbon::parse($member->dob)->format('d M, Y') }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="info-label">Religion</span>
                                        <span class="info-value">{{ $member->religion }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="info-label">Profession</span>
                                        <span class="info-value">{{ $member->profession }}</span>
                                    </div>
                                    <div class="mb-2">
                                        <span class="info-label">Monthly Income</span>
                                        <span class="info-value text-success">৳ {{ number_format($member->monthly_income, 2) }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <h6 class="text-info font-weight-bold mb-3">Address Information</h6>
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <div class="p-3 bg-light rounded" style="border-left: 4px solid #17a2b8;">
                                                <span class="info-label">Present Address</span>
                                                <span class="info-value" style="font-weight: 400;">{{ $member->full_present_address }}</span>
                                            </div>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <div class="p-3 bg-light rounded" style="border-left: 4px solid #6c757d;">
                                                <span class="info-label">Permanent Address</span>
                                                <span class="info-value" style="font-weight: 400;">{{ $member->full_permanent_address }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Documents Tab -->
                        <div class="tab-pane fade" id="documents">
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <div class="doc-card text-center">
                                        <span class="info-label mb-2">Member Signature</span>
                                        @if($member->signature_photo)
                                            <img src="{{ asset($member->signature_photo) }}" class="img-fluid rounded shadow-sm" style="max-height: 120px;">
                                        @else
                                            <div class="py-4 text-muted"><i class="fa fa-pen-nib fa-2x mb-2"></i><br>No signature uploaded</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-12 mb-4 text-center">
                                    @if($member->document)
                                        <div class="p-3 bg-light rounded d-inline-block shadow-sm">
                                            <i class="fa fa-file-alt fa-2x text-info mb-2"></i><br>
                                            <span class="font-weight-bold d-block">Other Documents</span>
                                            <a href="{{ asset($member->document) }}" target="_blank" class="btn btn-outline-info btn-sm mt-2"><i class="fa fa-eye mr-1"></i> View/Download Document</a>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="doc-card text-center">
                                        <span class="info-label mb-2">National ID (Front)</span>
                                        @if($member->nid_photo_front)
                                            <img src="{{ asset($member->nid_photo_front) }}" class="img-fluid rounded shadow-sm" style="max-height: 180px;">
                                        @else
                                            <div class="py-5 text-muted"><i class="fa fa-image fa-3x mb-2"></i><br>No image uploaded</div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6 mb-4">
                                    <div class="doc-card text-center">
                                        <span class="info-label mb-2">National ID (Back)</span>
                                        @if($member->nid_photo_back)
                                            <img src="{{ asset($member->nid_photo_back) }}" class="img-fluid rounded shadow-sm" style="max-height: 180px;">
                                        @else
                                            <div class="py-5 text-muted"><i class="fa fa-image fa-3x mb-2"></i><br>No image uploaded</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Activity Tab (Placeholder) -->
                        <div class="tab-pane fade text-center py-5" id="activity">
                            <i class="fa fa-calendar-check fa-4x text-light mb-3"></i>
                            <h5 class="text-muted">No recent activity found</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
