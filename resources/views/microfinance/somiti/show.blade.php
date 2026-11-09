@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .somiti-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .info-group-custom { background: #ffffff; border-radius: 15px; padding: 20px; margin-bottom: 25px; border: 1px solid #f1f5f9; box-shadow: 0 4px 6px rgba(0,0,0,0.02); transition: all 0.3s ease; height: 100%; }
    .info-group-custom:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.05); border-color: #6610f2; }
    .info-label { font-size: 0.75rem; color: #6c757d; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .info-value { font-size: 1.1rem; font-weight: 700; color: #343a40; }
    .member-table thead th { background: #f8fafc; border: none; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; padding: 15px; }
    .member-table tbody td { padding: 15px; vertical-align: middle; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card somiti-card">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title mb-0" style="font-weight: 700;"><i class="fa fa-users mr-2"></i> {{ $somiti->name }}</h3>
                    <div class="card-tools">
                        <a href="{{ route('microfinance.somiti.index') }}" class="btn btn-light btn-sm shadow-sm" style="border-radius: 10px; font-weight: 600;">
                            <i class="fa fa-arrow-left mr-1"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <!-- Quick Info Grid -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="info-group-custom">
                                <div class="info-label">Somiti Code</div>
                                <div class="info-value text-purple" style="color: #6610f2;">#{{ $somiti->somiti_code }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-custom">
                                <div class="info-label">Meeting Day</div>
                                <div class="info-value text-primary">{{ $somiti->meeting_day }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-custom">
                                <div class="info-label">Field Officer</div>
                                <div class="info-value">{{ $somiti->fieldOfficer ? $somiti->fieldOfficer->name : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-group-custom">
                                <div class="info-label">Total Members</div>
                                <div class="info-value">{{ $somiti->members->count() }} Persons</div>
                            </div>
                        </div>
                    </div>

                    <!-- Member Table Section -->
                    <div class="mt-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="font-weight-bold mb-0"><i class="fa fa-list mr-2 text-purple"></i> Member Records</h4>
                            <span class="badge badge-info px-3 py-2" style="border-radius: 10px;">{{ $somiti->members->count() }} Members Registered</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover member-table border">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Member No</th>
                                        <th>Member Name</th>
                                        <th>Mobile</th>
                                        <th>Active Accounts</th>
                                        <th class="text-right">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($somiti->members as $key => $member)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td><span class="badge badge-secondary" style="background: #e2e8f0; color: #475569;">{{ $member->member_no }}</span></td>
                                        <td>
                                            <div class="font-weight-bold">{{ $member->name }}</div>
                                            <small class="text-muted">Father: {{ $member->father_name }}</small>
                                        </td>
                                        <td>{{ $member->mobile_no }}</td>
                                        <td>
                                            @foreach($member->accounts as $account)
                                                <span class="badge badge-outline-primary border border-primary text-primary px-2 py-1 mr-1" style="font-size: 0.7rem; border-radius: 5px;">{{ $account->product->code }}</span>
                                            @endforeach
                                            @if($member->accounts->count() == 0)
                                                <small class="text-muted">No accounts</small>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ route('microfinance.member.show', $member->id) }}" class="btn btn-outline-info btn-sm px-3" style="border-radius: 8px;">
                                                <i class="fa fa-user mr-1"></i> Profile
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="fa fa-users fa-3x mb-3 opacity-25"></i><br>
                                            No members have been added to this somiti yet.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Field Officer Assignment History -->
                    <div class="mt-5 border-top pt-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h4 class="font-weight-bold mb-0 text-danger"><i class="fa fa-history mr-2"></i> Field Officer History (মাঠ কর্মী পরিবর্তনের ইতিহাস)</h4>
                            <span class="badge badge-danger px-3 py-2" style="border-radius: 10px;">{{ $somiti->fieldOfficerAssignments->count() }} Assignments Recorded</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover member-table border">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Field Officer (মাঠ কর্মী)</th>
                                        <th>From Date (শুরুর তারিখ)</th>
                                        <th>To Date (শেষের তারিখ)</th>
                                        <th>Status</th>
                                        <th>Assigned By</th>
                                        <th>Change Note (মন্তব্য)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($somiti->fieldOfficerAssignments as $key => $assignment)
                                    <tr>
                                        <td>{{ $key + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold text-dark">{{ $assignment->fieldOfficer ? $assignment->fieldOfficer->name : 'Unknown' }}</div>
                                            <small class="text-muted">Type: Field Officer</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-light border px-2 py-1"><i class="far fa-calendar-alt text-success mr-1"></i> {{ $assignment->from_date ? $assignment->from_date->format('d-m-Y') : 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($assignment->to_date)
                                                <span class="badge badge-light border px-2 py-1"><i class="far fa-calendar-alt text-danger mr-1"></i> {{ $assignment->to_date->format('d-m-Y') }}</span>
                                            @else
                                                <span class="badge badge-success px-2 py-1"><i class="fa fa-check mr-1"></i> Currently Active (বর্তমান)</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($assignment->status == 1)
                                                <span class="badge badge-success">Active</span>
                                            @else
                                                <span class="badge badge-secondary">Past / Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="small font-weight-bold">{{ $assignment->assignedBy ? $assignment->assignedBy->name : 'System' }}</div>
                                        </td>
                                        <td>
                                            <span class="text-muted small">{{ $assignment->change_reason ?? 'N/A' }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="fa fa-history fa-2x mb-2 opacity-25"></i><br>
                                            No assignment records found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="text-right mt-5 pt-3 border-top">
                        <a href="{{ route('microfinance.somiti.index') }}" class="btn btn-secondary px-5 mr-2" style="border-radius: 12px; font-weight: 700;">
                            <i class="fa fa-arrow-left mr-2"></i> Back to List
                        </a>
                        @if($somiti->members->count() == 0)
                        <a href="{{ route('microfinance.somiti.edit', $somiti->id) }}" class="btn btn-primary px-5" style="border-radius: 12px; font-weight: 700;">
                            <i class="fa fa-edit mr-2"></i> Edit Somiti
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
