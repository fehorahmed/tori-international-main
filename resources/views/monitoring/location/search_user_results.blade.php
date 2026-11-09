@if ($users->isEmpty())
    <p class="p-3 text-center">No User found.</p>
@else
    @foreach ($users as $user)
        <div class="card mb-2 p-2 shadow-sm position-relative user-card-item" id="card_user_{{ $user->id }}">
            <!-- Online/Offline Indicator -->
            <div class="status-indicator position-absolute" style="top: 8px; right: 8px;">
                <span class="dot {{ $user->is_online ? 'bg-success' : 'bg-danger' }}"></span>
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
                        <small>ID NO: {{ $user->id }}</small>
                    </div>
                </div>
            </div>
        
            <!-- Focus Button -->
            <button class="btn btn-sm btn-outline-primary mt-2 w-100 focus-btn" 
                id="user_id_{{ $user->id }}"
                data-user-id="{{ $user->id }}" 
                data-latitude="{{ $user->latest_latitude }}" 
                data-longitude="{{ $user->latest_longitude }}" 
                data-name="{{ $user->name }}" 
                onclick="focusOnMap(this)">
                Focus on Map
            </button>
        </div>
    @endforeach
@endif
