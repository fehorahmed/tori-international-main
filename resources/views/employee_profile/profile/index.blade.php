@extends('layouts.app')

@section('title', 'My Profile')

@section('style')
<style>
    .profile-cover {
        height: 150px;
        background: linear-gradient(135deg, #007bff 0%, #6610f2 100%);
        border-radius: 8px 8px 0 0;
        position: relative;
    }
    .profile-avatar-wrapper {
        position: absolute;
        bottom: -50px;
        left: 30px;
    }
    .profile-avatar {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 4px solid #fff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        object-fit: cover;
        background: #fff;
    }
    .profile-hero {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        margin-bottom: 24px;
        padding-bottom: 20px;
    }
    .profile-hero-info {
        margin-left: 170px;
        padding-top: 15px;
    }
    .hero-name {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 4px;
        color: #333;
    }
    .hero-designation {
        color: #6c757d;
        font-size: 1rem;
        font-weight: 500;
    }
    .hero-actions {
        position: absolute;
        right: 20px;
        top: 170px;
    }
    .info-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        padding: 20px;
        margin-bottom: 24px;
        border: none;
    }
    .info-card-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #333;
        margin-bottom: 20px;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 10px;
    }
    .info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
    }
    .info-item {
        display: flex;
        flex-direction: column;
    }
    .info-label {
        font-size: 0.85rem;
        color: #888;
        font-weight: 500;
        margin-bottom: 4px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .info-value {
        font-size: 1rem;
        color: #333;
        font-weight: 500;
    }
    .modern-tabs .nav-link {
        border: none;
        color: #6c757d;
        font-weight: 500;
        padding: 12px 20px;
        border-bottom: 2px solid transparent;
        transition: all 0.3s;
        border-radius: 0;
    }
    .modern-tabs .nav-link:hover {
        color: #007bff;
        border-color: transparent;
    }
    .modern-tabs .nav-link.active {
        color: #007bff;
        background: transparent;
        border-bottom: 2px solid #007bff;
    }
    .modern-tabs {
        border-bottom: 1px solid #f0f0f0;
        margin-bottom: 20px;
    }
    
    @media (max-width: 768px) {
        .profile-hero-info {
            margin-left: 0;
            padding-top: 60px;
            text-align: center;
        }
        .profile-avatar-wrapper {
            left: 50%;
            transform: translateX(-50%);
        }
        .hero-actions {
            position: relative;
            top: 0;
            right: 0;
            text-align: center;
            margin-top: 10px;
        }
    }
</style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="profile-hero">
            <div class="profile-cover">
                <div class="profile-avatar-wrapper">
                    <img class="profile-avatar" src="{{ file_exists($employee->image) ? asset($employee->image) : asset('assets/img/avatars/1.png') }}" alt="User profile picture">
                </div>
            </div>
            <div class="profile-hero-info">
                <h1 class="hero-name">{{ $employee->name }}</h1>
                <div class="hero-designation">{{ $employee->designation->name ?? 'N/A' }} &bull; {{ $employee->department->name ?? 'N/A' }}</div>
            </div>
            <div class="hero-actions">
                <span class="badge badge-success px-3 py-2" style="font-size: 13px; font-weight: 500;"><i class="fas fa-check-circle mr-1"></i> Active</span>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Left Column: About Me -->
    <div class="col-xl-4 col-lg-5">
        <div class="info-card">
            <h3 class="info-card-title"><i class="fas fa-user text-primary mr-2"></i> About Me</h3>
            <div class="info-grid" style="grid-template-columns: 1fr;">
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-id-badge text-muted mr-1"></i> Employee ID</span>
                    <span class="info-value">{{ $employee->id_no }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-phone-alt text-muted mr-1"></i> Mobile No</span>
                    <span class="info-value">{{ $employee->mobile_no }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-envelope text-muted mr-1"></i> Email</span>
                    <span class="info-value">{{ $employee->email ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-tint text-muted mr-1"></i> Blood Group</span>
                    <span class="info-value text-danger font-weight-bold">{{ $employee->blood_group ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="fas fa-calendar-alt text-muted mr-1"></i> Joining Date</span>
                    <span class="info-value">{{ $employee->joining_date ? \Carbon\Carbon::parse($employee->joining_date)->format('d M, Y') : 'N/A' }}</span>
                </div>
            </div>
        </div>

        <div class="info-card">
            <h3 class="info-card-title"><i class="fas fa-map-marker-alt text-primary mr-2"></i> Location</h3>
            <div class="info-item mb-3">
                <span class="info-label">Current Address</span>
                <span class="info-value">{{ $employee->current_full_address ?? 'Not provided' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Permanent Address</span>
                <span class="info-value">{{ $employee->permanent_full_address ?? 'Not provided' }}</span>
            </div>
        </div>
    </div>

    <!-- Right Column: Tabs -->
    <div class="col-xl-8 col-lg-7">
        <div class="info-card" style="padding-top: 10px;">
            <ul class="nav nav-tabs modern-tabs" id="custom-tabs" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="navs-education-tab" data-toggle="pill" href="#navs-education" role="tab" aria-selected="true"><i class="fas fa-graduation-cap mr-1"></i> Education</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="navs-experience-tab" data-toggle="pill" href="#navs-experience" role="tab" aria-selected="false"><i class="fas fa-briefcase mr-1"></i> Experience</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="navs-documents-tab" data-toggle="pill" href="#navs-documents" role="tab" aria-selected="false"><i class="fas fa-file-alt mr-1"></i> Documents</a>
                </li>
            </ul>

            <div class="tab-content" id="custom-tabsContent">
                <!-- Education Tab -->
                <div class="tab-pane fade show active" id="navs-education" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-dark font-weight-bold">Educational Qualifications</h5>
                        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#educationModal"><i class="fas fa-plus mr-1"></i> Add New</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless border-bottom">
                            <thead class="bg-light">
                                <tr>
                                    <th class="rounded-left">Degree</th>
                                    <th>Institute</th>
                                    <th>Passing Year</th>
                                    <th>Result</th>
                                    <th class="rounded-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->educations as $edu)
                                <tr class="border-bottom">
                                    <td class="font-weight-bold text-dark">{{ $edu->degree_name }}</td>
                                    <td>{{ $edu->institute }}</td>
                                    <td>{{ $edu->passing_year }}</td>
                                    <td><span class="badge badge-info">{{ $edu->result }}</span></td>
                                    <td>
                                        <form action="{{ route('employee_profile.profile.education.destroy', $edu->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center text-muted py-4">No education records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Experience Tab -->
                <div class="tab-pane fade" id="navs-experience" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-dark font-weight-bold">Work Experience</h5>
                        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#experienceModal"><i class="fas fa-plus mr-1"></i> Add New</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless border-bottom">
                            <thead class="bg-light">
                                <tr>
                                    <th class="rounded-left">Company</th>
                                    <th>Designation</th>
                                    <th>Duration</th>
                                    <th class="rounded-right">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($employee->experiences as $exp)
                                <tr class="border-bottom">
                                    <td class="font-weight-bold text-dark">{{ $exp->company_name }}</td>
                                    <td>{{ $exp->designation }}</td>
                                    <td><i class="far fa-calendar-alt text-muted mr-1"></i> {{ $exp->start_date }} to <span class="font-weight-bold {{ $exp->is_current ? 'text-success' : '' }}">{{ $exp->is_current ? 'Present' : $exp->end_date }}</span></td>
                                    <td>
                                        <form action="{{ route('employee_profile.profile.experience.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="4" class="text-center text-muted py-4">No experience records found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Documents Tab -->
                <div class="tab-pane fade" id="navs-documents" role="tabpanel">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 text-dark font-weight-bold">My Documents</h5>
                        <button class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#documentModal"><i class="fas fa-upload mr-1"></i> Upload</button>
                    </div>
                    <div class="row">
                        @forelse($employee->documents as $doc)
                        <div class="col-md-6 mb-3">
                            <div class="d-flex align-items-center p-3 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                <div class="mr-3 text-primary">
                                    <i class="fas fa-file-pdf fa-2x"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h6 class="mb-0 font-weight-bold text-dark">{{ $doc->document_type }}</h6>
                                    <small class="text-muted">Uploaded: {{ $doc->created_at->format('d M, Y') }}</small>
                                </div>
                                <div class="d-flex ml-2">
                                    <a href="{{ asset($doc->file_path) }}" target="_blank" class="btn btn-sm btn-light text-info mr-1"><i class="fas fa-eye"></i></a>
                                    <form action="{{ route('employee_profile.profile.document.destroy', $doc->id) }}" method="POST" onsubmit="return confirm('Are you sure?');" class="m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light text-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <div class="col-12 text-center text-muted py-4">No documents uploaded yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Education Modal -->
<div class="modal fade" id="educationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('employee_profile.profile.education.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Education</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Degree Name <span class="text-danger">*</span></label>
                        <input type="text" name="degree_name" class="form-control" required placeholder="e.g. B.Sc in CSE">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Institute <span class="text-danger">*</span></label>
                        <input type="text" name="institute" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Passing Year</label>
                            <input type="text" name="passing_year" class="form-control" placeholder="e.g. 2020">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Result / CGPA</label>
                            <input type="text" name="result" class="form-control" placeholder="e.g. 3.50">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Experience Modal -->
<div class="modal fade" id="experienceModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('employee_profile.profile.experience.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Add Work Experience</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Company Name <span class="text-danger">*</span></label>
                        <input type="text" name="company_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Designation <span class="text-danger">*</span></label>
                        <input type="text" name="designation" class="form-control" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control">
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_current" id="isCurrentExp" value="1">
                        <label class="form-check-label" for="isCurrentExp">I am currently working here (Internal Transfer etc.)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Document Modal -->
<div class="modal fade" id="documentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form action="{{ route('employee_profile.profile.document.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Upload Document</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Document Type <span class="text-danger">*</span></label>
                        <select name="document_type" class="form-select" required>
                            <option value="">Select Type</option>
                            <option value="NID Copy">NID Copy</option>
                            <option value="CV / Resume">CV / Resume</option>
                            <option value="Academic Certificate">Academic Certificate</option>
                            <option value="Experience Certificate">Experience Certificate</option>
                            <option value="Joining Letter">Joining Letter</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Select File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Max size: 5MB</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
