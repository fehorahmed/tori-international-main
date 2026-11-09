<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Profile - {{ $employee->name }}</title>
    
    @include('layouts.partial.__styles')

    <style>
        @page {
            margin: .3in .5in .5in .5in;
        }
        body {
            background-color: #f4f6f9;
        }
        .report-container {
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }
        
        .cv-header {
            background: transparent;
            color: #333;
            padding: 20px 20px 15px;
            position: relative;
        }
        .cv-profile-img {
            width: 180px;
            height: 180px;
            border-radius: 50%;
            border: 4px solid #e9ecef;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            object-fit: cover;
            margin-bottom: 15px;
        }
        .cv-name {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 0px;
            letter-spacing: 1px;
        }
        .cv-designation {
            font-size: 1.1rem;
            font-weight: 400;
            color: #6c757d;
            margin-bottom: 15px;
        }

        .cv-body {
            padding: 20px;
        }
        .cv-section {
            margin-bottom: 30px;
        }
        .cv-section-title {
            color: #17a2b8;
            font-size: 1.25rem;
            font-weight: 700;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 8px;
            margin-bottom: 15px;
            position: relative;
        }
        .cv-section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 60px;
            height: 3px;
            background: #17a2b8;
        }
        .cv-item {
            margin-bottom: 10px;
            display: flex;
            align-items: center;
        }
        .cv-label {
            flex: 0 0 200px;
            color: #6c757d;
            font-weight: 600;
            font-size: 1rem;
        }
        .cv-value {
            flex: 1;
            color: #343a40;
            font-weight: 500;
            font-size: 1rem;
        }
        .cv-value i {
            color: #17a2b8;
            width: 20px;
            text-align: center;
            margin-right: 8px;
        }
        .cv-docs {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        .doc-btn {
            padding: 10px 25px;
            border-radius: 30px;
            font-weight: 600;
            transition: all 0.3s;
            border-width: 2px;
        }
        .doc-btn:hover:not(.disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
        }
        
        @media print {
            .report-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .extra-remove {
                display: none !important;
            }
            body {
                background-color: #fff;
            }
            .table-responsive-md {
                overflow-x: visible !important;
            }
        }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Employee_Profile_' . $employee->id_no])
                    
                    <div class="table-responsive-md" id="printArea">
                
                        <!-- Header Section -->
                        <div class="cv-header border-bottom mb-4 pb-4">
                            <div class="row align-items-center">
                                <div class="col-3 text-center text-md-left">
                                    <img src="{{ file_exists($employee->photo) ? asset($employee->photo) : 'https://placehold.co/200x200/png?text=User' }}" alt="Employee Photo" class="cv-profile-img m-0">
                                </div>
                                <div class="col-9 text-left">
                                    <h1 class="cv-name mb-1">{{ $employee->name }}</h1>
                                    <h3 class="cv-designation mb-3">{{ $employee->designation->name ?? 'No Designation' }} | {{ $employee->department->name ?? 'No Department' }}</h3>
                                    
                                    <table class="table-sm table-borderless" style="font-size: 1rem; color: #495057;">
                                        <tr>
                                            <td style="width: 130px; font-weight: 600;">ID No.</td>
                                            <td style="font-weight: 500;">: {{ $employee->id_no }}</td>
                                        </tr>

                                        <tr>
                                            <td style="font-weight: 600;">Branch Name</td>
                                            <td style="font-weight: 500;">: {{ $employee->sisterConcern->name ?? 'N/A' }}</td>
                                        </tr>
                                        <tr>
                                            <td style="font-weight: 600;">Branch Code</td>
                                            <td style="font-weight: 500;">: {{ $employee->sisterConcern->code ?? 'N/A' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Body Section -->
                        <div class="cv-body mt-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Personal Info -->
                                    <div class="cv-section">
                                        <h4 class="cv-section-title"><i class="fas fa-user-circle mr-2"></i> Personal Information</h4>
                                        <div class="cv-item">
                                            <div class="cv-label">Status</div>
                                            <div class="cv-value">
                                                <span class="badge badge-{{ ($employee->employeeStatus->name ?? '') == 'Active' ? 'success' : 'secondary' }} px-3 py-1 text-sm">
                                                    {{ $employee->employeeStatus->name ?? 'Unknown' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Gender</div>
                                            <div class="cv-value"><i class="fas fa-venus-mars"></i> {{ $employee->gender_text }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Date of Birth</div>
                                            <div class="cv-value"><i class="fas fa-calendar-alt"></i> {{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d F, Y') : 'N/A' }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Marital Status</div>
                                            <div class="cv-value"><i class="fas fa-ring"></i> {{ $employee->marital_status_text }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Email</div>
                                            <div class="cv-value"><i class="fas fa-envelope"></i> {{ $employee->email ?: 'N/A' }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Mobile No</div>
                                            <div class="cv-value"><i class="fas fa-phone-alt"></i> {{ $employee->mobile_no ?: 'N/A' }}</div>
                                        </div>
                                        
                                        <div class="cv-item">
                                            <div class="cv-label">Current Address</div>
                                            <div class="cv-value"><i class="fas fa-map-marker-alt"></i> {{ $employee->current_full_address ?: 'Not provided' }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Permanent Address</div>
                                            <div class="cv-value"><i class="fas fa-home"></i> {{ $employee->permanent_full_address ?: 'Not provided' }}</div>
                                        </div>
                                    </div>

                                    <!-- Office Info -->
                                    <div class="cv-section">
                                        <h4 class="cv-section-title"><i class="fas fa-briefcase mr-2"></i> Office & Shift Details</h4>
                                        <div class="cv-item">
                                            <div class="cv-label">Shift Name</div>
                                            <div class="cv-value"><i class="fas fa-clock"></i> {{ $employee->shift->name ?? 'N/A' }}</div>
                                        </div>
                                        @if($employee->shift)
                                        <div class="cv-item">
                                            <div class="cv-label">Working Hours</div>
                                            <div class="cv-value"><i class="fas fa-business-time"></i> {{ \Carbon\Carbon::parse($employee->shift->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($employee->shift->end_time)->format('h:i A') }}</div>
                                        </div>
                                        @endif
                                    </div>

                                    <!-- Financial Info -->
                                    <div class="cv-section">
                                        <h4 class="cv-section-title"><i class="fas fa-file-invoice-dollar mr-2"></i> Financial & Bank Information</h4>
                                        <div class="row mb-4">
                                            <div class="col-sm-12">
                                                <div class="p-3 border rounded bg-light text-center border-left border-primary shadow-sm" style="border-left-width: 4px !important;">
                                                    <div class="text-muted text-sm font-weight-bold text-uppercase mb-2">Gross Salary</div>
                                                    <div class="font-weight-bold text-primary" style="font-size: 1.8rem;">৳ {{ number_format($employee->gross_salary, 2) }}</div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="cv-item">
                                            <div class="cv-label">Bank Name</div>
                                            <div class="cv-value"><i class="fas fa-university"></i> {{ $employee->bank_name ?: 'N/A' }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Branch Name</div>
                                            <div class="cv-value"><i class="fas fa-code-branch"></i> {{ $employee->branch_name ?: 'N/A' }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Bank Account No</div>
                                            <div class="cv-value"><i class="fas fa-money-check-alt"></i> {{ $employee->bank_account_no ?: 'N/A' }}</div>
                                        </div>
                                        <div class="cv-item">
                                            <div class="cv-label">Routing Number</div>
                                            <div class="cv-value"><i class="fas fa-route"></i> {{ $employee->routing_number ?: 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <!-- Documents Section -->
                                    <div class="cv-section mb-0 extra-remove">
                                        <h4 class="cv-section-title"><i class="fas fa-folder-open mr-2"></i> Attached Documents</h4>
                                        <div class="cv-docs mt-4">
                                            <a target="_blank" class="btn btn-outline-info doc-btn {{ file_exists($employee->cv) ? '' : 'disabled' }}" href="{{ file_exists($employee->cv) ? asset($employee->cv) : '#' }}"><i class="fas fa-file-pdf mr-2"></i> Curriculum Vitae</a>
                                            <a target="_blank" class="btn btn-outline-info doc-btn {{ file_exists($employee->nid) ? '' : 'disabled' }}" href="{{ file_exists($employee->nid) ? asset($employee->nid) : '#' }}"><i class="fas fa-id-card mr-2"></i> NID Copy</a>
                                            <a target="_blank" class="btn btn-outline-info doc-btn {{ file_exists($employee->passport) ? '' : 'disabled' }}" href="{{ file_exists($employee->passport) ? asset($employee->passport) : '#' }}"><i class="fas fa-passport mr-2"></i> Passport</a>
                                            <a target="_blank" class="btn btn-outline-info doc-btn {{ file_exists($employee->certificate) ? '' : 'disabled' }}" href="{{ file_exists($employee->certificate) ? asset($employee->certificate) : '#' }}"><i class="fas fa-graduation-cap mr-2"></i> Certificate</a>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        var businessDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y') }}';
    </script>
    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
