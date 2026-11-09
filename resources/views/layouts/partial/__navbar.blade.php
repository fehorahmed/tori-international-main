<nav class="main-header navbar navbar-expand text-sm  {{ auth()->user()->theme_mode == 1 ? 'navbar-white' : 'navbar-dark' }}  navbar-light">
    <!-- Left navbar links -->
    <ul class="navbar-nav">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
        </li>
        <style>
            .navbar-branch-name { color: #007bff; font-size: 15px; }
            .navbar-business-date { font-size: 10px; }
            @media (max-width: 767px) {
                .navbar-nav .nav-link { padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                .branch-switcher-btn { margin-right: 0.25rem !important; padding-left: 0.5rem !important; padding-right: 0.5rem !important; }
                .navbar-branch-name { max-width: 130px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: inline-block; vertical-align: middle; }
                .navbar-branch-container { height: 16px !important; }
            }
            @media (min-width: 768px) {
                .navbar-branch-name { font-size: 20px; }
                .navbar-business-date { font-size: 13px; }
            }
        </style>
        <li class="nav-item">
            <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center py-1 pl-2" style="gap: 2px;">
                <h3 class="nav-link font-weight-bold active mb-0 pr-0 py-0 navbar-branch-name navbar-branch-container" style="padding-left: 0 !important; line-height: 1;">
                    {{ auth()->user()->activeSisterConcern->name ?? auth()->user()->sisterConcern->name ?? '' }}
                </h3>
                @php
                    $activeConcernId = auth()->user()->activeConcernId();
                    $activeSisterConcern = \App\Models\BranchManagement\SisterConcern::find($activeConcernId);
                    $businessDate = $activeSisterConcern->business_date ?? null;
                @endphp
                @if($businessDate)
                    <span class="badge bg-soft-blue text-primary ml-0 ml-md-3 px-1 navbar-business-date" style="border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 4px; padding-top: 1px; padding-bottom: 1px; margin-top: 0; line-height: 1.1;">
                        <i class="far fa-calendar-check mr-1"></i>
                        {{ \Carbon\Carbon::parse($businessDate)->format('d M, Y') }}
                    </span>
                @endif
            </div>
        </li>
    </ul>

    <!-- Right navbar links -->
    <ul class="navbar-nav ml-auto">
        <!-- Branch Switcher -->
        @if(auth()->user()->role == 'Admin' || auth()->user()->role == 'Area_Manager')
            @php
                if(auth()->user()->role == 'Admin') {
                    $sw_branches = \App\Models\BranchManagement\SisterConcern::where('status', 1)->get();
                } else {
                    $sw_branches = \App\Models\BranchManagement\SisterConcern::where('area_manager_id', auth()->user()->supplier_id)->where('status', 1)->get();
                }
                $activeConcernId = auth()->user()->activeConcernId();
                $activeConcern= \App\Models\BranchManagement\SisterConcern::find($activeConcernId);
            @endphp
            @if(count($sw_branches) > 0)
                <li class="nav-item dropdown">
                    <a class="nav-link bg-light border rounded px-3 mr-2 branch-switcher-btn" data-toggle="dropdown" href="#" aria-expanded="false" style="color: #495057; font-weight: 600;">
                        <i class="fas fa-store-alt mr-1 text-primary"></i> 
                        <span class="d-none d-md-inline-block text-truncate" style="max-width: 150px; vertical-align: middle;">{{ $activeConcern->code }} - {{ $activeConcern->name }}</span>
                        <i class="fa fa-angle-down ml-1"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right shadow border-0" style="border-radius: 10px;">
                        <span class="dropdown-header font-weight-bold bg-light">Switch Branch</span>
                        <div class="dropdown-divider"></div>
                        <div style="max-height: 300px; overflow-y: auto;">
                            @foreach($sw_branches as $sw_branch)
                                <form action="{{ route('branch.switch') }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="sister_concern_id" value="{{ $sw_branch->id }}">
                                    <button type="submit" class="dropdown-item py-2 {{ $activeConcernId == $sw_branch->id ? 'bg-soft-blue font-weight-bold text-primary' : '' }}">
                                        <i class="fas fa-chevron-right mr-2 text-muted"></i> {{ $sw_branch->code }} - {{ $sw_branch->name }}
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </li>
            @endif
        @endif

        <!-- <li class="nav-item">
            <a class="nav-link" data-widget="fullscreen" href="#" role="button">
                <i class="fas fa-expand-arrows-alt"></i>
            </a>
        </li> -->
        @if(auth()->user()->can('pos'))
            <li class="nav-item dropdown d-none">
                <a id="dropdownSubMenu1" href="#" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle">
                    <i class="fas fa-bolt d-inline d-md-none"></i>
                    <span class="d-none d-md-inline">Quick Link</span>
                </a>
                <ul aria-labelledby="dropdownSubMenu1" class="dropdown-menu border-0 shadow">
                    <li><a href="{{ route('purchase.create') }}" class="dropdown-item"><i class="fas fa-shopping-cart"></i> Purchase </a></li>
{{--                    <li><a href="{{ route('pos') }}" class="dropdown-item"><i class="fas fa-shopping-basket"></i> Sales </a></li>--}}
                    <li><a href="{{ route('stock-transfers.create') }}" class="dropdown-item"><i class="fas fa-exchange"></i> Stock Transfer </a></li>
                    {{-- <li><a href="{{ route('database-backups.index') }}" class="dropdown-item"><i class="fas fa-database"></i> DB Backup</a></li> --}}

                </ul>
            </li>
        @endif
        @include('layouts.partial.__quick_apps')
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                <i class="far fa-bell"></i>
                @if (count(auth()->user()->unreadNotifications) > 0)
                    <span class="badge badge-danger navbar-badge">
                                {{ count(auth()->user()->unreadNotifications) > 99 ? '99+' : count(auth()->user()->unreadNotifications) }}
                            </span>
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right notification-area-custom">
                <input type="hidden" id="notification_skip" value="0">
                <span class="dropdown-item dropdown-header"><a
                        href="{{ route('notification') }}" style="color: #810ca8;">{{ count(auth()->user()->unreadNotifications) }}
                                Notifications</a></span>
                <div class="dropdown-divider"></div>
                <div id="notification-list-area" style="max-height: 350px; overflow-y: auto; overflow-x: hidden;">
                    @foreach (auth()->user()->notifications->take(10) as $notification)
                        @php
                            $notificationUrl = route('notification.read', $notification->id);
                        @endphp
                        @php
                            $icon = 'fas fa-bell text-info';
                            if(isset($notification->data['type'])) {
                                if($notification->data['type'] == 'first_punch_missing') {
                                    $icon = 'fas fa-user-clock text-danger';
                                } elseif($notification->data['type'] == 'hourly_missing') {
                                    $icon = 'fas fa-map-marker-alt text-warning';
                                }
                            }
                            $isUnread = is_null($notification->read_at);
                        @endphp
                        <a href="{{ $notificationUrl }}"
                           class="dropdown-item px-3 py-2 {{ $isUnread ? 'bg-light' : '' }}" style="white-space: normal; line-height: 1.4;">
                            <div class="d-flex align-items-start">
                                <i class="{{ $icon }} mr-2 mt-1" style="font-size: 14px;"></i>
                                <div class="flex-grow-1">
                                    <div class="{{ $isUnread ? 'font-weight-bold text-dark' : 'text-muted' }}" style="font-size: 13px;">
                                        {{ Str::limit($notification->data['title'] ?? 'Notification', 55) }}
                                    </div>
                                    <div class="text-muted mt-1" style="font-size: 11px;">
                                        <i class="far fa-clock mr-1"></i>{{ $notification->created_at->diffForHumans() }}
                                    </div>
                                </div>
                            </div>
                        </a>
                        <div class="dropdown-divider m-0"></div>
                    @endforeach
                    <div class="notification-preloader">
                        <div class="skeleton"></div>
                    </div>
                </div>
                @if (count(auth()->user()->unreadNotifications) <= 1)
                    <div class="dropdown-divider"></div>
                @endif
                <a href="{{ route('notification') }}" class="dropdown-item dropdown-footer">See All
                    Notifications</a>
            </div>
        </li>
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false">
                <img height="30" class="img-circle" src="{{ asset(file_exists(auth()->user()->profile_photo) ? auth()->user()->profile_photo : 'themes/backend/dist/img/avatar.png') }}"
                     alt=""> <span class="d-none d-md-inline">{{ auth()->user()->name ?? '' }}</span> <i class="fa fa-angle-down"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="left: inherit; right: 0px;">
                <a class="dropdown-item d-md-none text-success" href="/apps/android/app-debug.apk">
                    <i class="fab fa-android mr-2"></i> Download Android App
                </a>
                <a href="{{ route('profile.edit') }}" class="dropdown-item">
                    <i class="fas fa-user-cog mr-2"></i> Profile
                </a>
                <a href="{{ route('profile.password_change') }}" class="dropdown-item">
                    <i class="fas fa-lock mr-2"></i> Change Password
                </a>

                <a class="dropdown-item" href="{{ route('dark_mode_change') }}">
                    <i class="fa-solid fa-circle-half-stroke "></i> {{ auth()->user()->theme_mode == 1 ? 'Dark Mode' : 'Light Mode' }}
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <a href="{{ route('logout') }}" class="dropdown-item" onclick="event.preventDefault();
                                                this.closest('form').submit();">
                        <i class="fas fa-sign-out-alt mr-2"></i> Log Out
                    </a>
                </form>

            </div>
        </li>
    </ul>
</nav>
