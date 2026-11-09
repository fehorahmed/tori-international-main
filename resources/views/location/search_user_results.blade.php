@if ($users->isEmpty())
    <p>No User found.</p>
@else
    @foreach ($users as $user)
        <div class="card mb-2 p-2 shadow-sm position-relative">
            <!-- Online/Offline Indicator -->
            <div class="status-indicator position-absolute" style="top: 8px; right: 8px;">
            <span class="dot bg-danger"></span>
            </div>
        
            <!-- User Details -->
            <div>
            <div class="d-flex justify-content-start align-items-center user-name">
                <div class="avatar-wrapper">
                <div class="avatar avatar-sm me-4">
                    <span class="avatar-initial rounded-circle bg-label-primary">{{ shortName($user->name) }}</span>
                </div>
                </div>
                <div class="d-flex flex-column">
                <a href="#" class="text-heading text-truncate fw-medium">{{ $user->name }}</a>
                <small>ID NO: ID480</small>
                </div>
            </div>
            {{-- <p class="mb-1 mt-3">Designation: {{ $client->designation }}</p> --}}
            {{-- <p class="mb-1">Last Updated: 12 hours ago</p> --}}
            </div>
        
            <!-- Focus Button -->
            <button class="btn btn-sm btn-outline-primary mt-2 w-100" onclick="focusOnMap({{ $user->latest_latitude }}, {{ $user->latest_longitude }}, '{{ $user->name }}')">
            Focus on Map
            </button>
        </div>
    @endforeach
@endif
