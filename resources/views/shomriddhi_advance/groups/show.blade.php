@extends('layouts.app')
@section('title', 'Group Details')

@section('content')
<div class="row">
    <!-- Top: Group Information -->
    <div class="col-md-12">
        <div class="card card-info card-outline">
            <div class="card-header bg-light">
                <h3 class="card-title font-weight-bold text-primary">
                    <i class="fa fa-layer-group mr-2"></i>Group Information
                </h3>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-advance.groups.edit', $group->id) }}" class="btn btn-info btn-sm bg-gradient-info"><i class="fa fa-edit"></i> Edit Group</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row align-items-center">
                    <!-- Basic Info -->
                    <div class="col-md-4 text-center border-right">
                        <div class="d-inline-flex justify-content-center align-items-center rounded-circle bg-light shadow-sm mb-3" style="width: 80px; height: 80px;">
                            <i class="fa fa-users fa-3x text-primary"></i>
                        </div>
                        <h4 class="font-weight-bold mb-1">{{ $group->name_en }}</h4>
                        @if($group->name_bn)
                            <p class="text-muted mb-2">{{ $group->name_bn }}</p>
                        @endif
                        
                        <div class="mb-3">
                            <span class="badge badge-primary px-3 py-1" style="font-size: 14px;">Code: {{ $group->code }}</span>
                            @if($group->status == 1)
                                <span class="badge badge-success px-3 py-1" style="font-size: 14px;">Active</span>
                            @else
                                <span class="badge badge-danger px-3 py-1" style="font-size: 14px;">Inactive</span>
                            @endif
                        </div>
                    </div>

                    <!-- Statistics & Location Details -->
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-sm-4 mb-3">
                                <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 1px;">Total Members</h6>
                                <p class="font-weight-bold text-dark h5 mb-0">{{ $group->members->count() }} <i class="fa fa-user-check text-success ml-1" style="font-size: 16px;"></i></p>
                            </div>
                            <div class="col-sm-4 mb-3">
                                <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 1px;">Opening Date</h6>
                                <p class="font-weight-bold text-dark h5 mb-0">{{ $group->opening_date ? \Carbon\Carbon::parse($group->opening_date)->format('d M, Y') : 'N/A' }}</p>
                            </div>
                            <div class="col-sm-4 mb-3">
                                <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 1px;">Created At</h6>
                                <p class="font-weight-bold text-dark h5 mb-0">{{ $group->created_at->format('d M, Y') }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-6 mb-3">
                                <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 1px;">Collection Day</h6>
                                <p class="font-weight-bold text-info h5 mb-0">{{ ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'][$group->collection_day] ?? 'N/A' }}</p>
                            </div>
                            <div class="col-sm-6 mb-3">
                                <h6 class="text-uppercase text-muted font-weight-bold" style="font-size: 12px; letter-spacing: 1px;">Meeting Time</h6>
                                <p class="font-weight-bold text-dark h5 mb-0">{{ $group->meeting_time ? \Carbon\Carbon::parse($group->meeting_time)->format('h:i A') : 'N/A' }} <i class="far fa-clock text-primary ml-1" style="font-size: 14px;"></i></p>
                            </div>
                        </div>
                        
                        <hr class="mt-0 mb-3">
                        
                        <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fas fa-map-marker-alt mr-1"></i> Location Details</h6>
                        <div class="row">
                            <div class="col-sm-6 mb-2">
                                <span class="text-muted"><i class="fas fa-map-pin mr-1" style="width: 15px;"></i> Division:</span>
                                <strong class="ml-1">{{ $group->division->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <span class="text-muted"><i class="fas fa-map mr-1" style="width: 15px;"></i> District:</span>
                                <strong class="ml-1">{{ $group->district->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-2">
                                <span class="text-muted"><i class="fas fa-map-signs mr-1" style="width: 15px;"></i> Upazila:</span>
                                <strong class="ml-1">{{ $group->upazila->name ?? 'N/A' }}</strong>
                            </div>
                            <div class="col-sm-6 mb-2">
                                @if($group->union_id)
                                    <span class="text-muted"><i class="fas fa-city mr-1" style="width: 15px;"></i> Union:</span>
                                    <strong class="ml-1">{{ $group->union->name ?? 'N/A' }}</strong>
                                @elseif($group->pourashava_id)
                                    <span class="text-muted"><i class="fas fa-city mr-1" style="width: 15px;"></i> Pourashava:</span>
                                    <strong class="ml-1">{{ $group->pourashava->name ?? 'N/A' }}</strong>
                                @else
                                    <span class="text-muted"><i class="fas fa-city mr-1" style="width: 15px;"></i> Union/Pourashava:</span>
                                    <strong class="ml-1">N/A</strong>
                                @endif
                            </div>
                            <div class="col-sm-12">
                                <span class="text-muted"><i class="fas fa-road mr-1" style="width: 15px;"></i> Village/Road:</span>
                                <strong class="ml-1">{{ $group->village->name ?? 'N/A' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mt-2">
    <!-- Bottom: Members List -->
    <div class="col-md-12">
        <div class="card">
            <div class="card-header p-2 bg-gradient-info">
                <h3 class="card-title text-white ml-2 mt-1"><i class="fa fa-users mr-1"></i> Members List</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped" id="members-table">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th class="text-center">Photo</th>
                                <th>Member Name</th>
                                <th>Member No.</th>
                                <th>Mobile</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group->members as $index => $member)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td class="text-center">
                                    @if($member->image)
                                        <img src="{{ asset($member->image) }}" alt="Photo" class="img-thumbnail" style="width: 40px; height: 40px; object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded-circle d-inline-flex justify-content-center align-items-center" style="width: 40px; height: 40px;">
                                            <i class="fa fa-user text-secondary"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="font-weight-bold">{{ $member->name }}</td>
                                <td>{{ $member->member_no }}</td>
                                <td>{{ $member->mobile_no }}</td>
                                <td class="text-center">
                                    <a href="{{ route('shomriddhi-advance.members.show', $member->id) }}" class="btn btn-info btn-sm" target="_blank"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                            @endforeach
                            
                            @if($group->members->count() == 0)
                            <tr>
                                <td colspan="6" class="text-center text-danger font-weight-bold py-4">
                                    <i class="fa fa-info-circle mr-1"></i> No members are currently added to this group.
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(function () {
        $('#members-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "responsive": true,
        });
    });
</script>
@endsection
