<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title') | {{ config('app.name', 'Laravel') }}</title>
    @include('layouts.partial.__favicon')
    @include('layouts.partial.__styles')
    {{-- <link rel="stylesheet" href="{{ asset('css/premium-theme.css') }}"> --}}
    @yield('style')
    <style>
        /* Global Bottom Navigation Bar Styles */
        .bottom-nav-menu {
            position: fixed;
            bottom: 0;
            left: 0;
            width: 100%;
            background-color: #ffffff;
            display: flex;
            justify-content: space-around;
            align-items: center;
            padding: 0;
            box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);
            z-index: 1040;
            border-top: 1px solid #dee2e6;
        }
        .dark-mode .bottom-nav-menu {
            background-color: #343a40;
            border-top: 1px solid #4b545c;
        }
        .bottom-nav-menu .nav-item {
            text-align: center;
            flex: 1;
        }
        .bottom-nav-menu .nav-item a {
            color: #6c757d;
            text-decoration: none;
            display: flex;
            flex-direction: column;
            font-size: 12px;
            transition: 0.3s;
        }
        .dark-mode .bottom-nav-menu .nav-item a {
            color: #adb5bd;
        }
        .bottom-nav-menu .nav-item a i {
            font-size: 20px;
        }
        .bottom-nav-menu .nav-item a:hover,
        .bottom-nav-menu .nav-item.active a {
            color: #17a2b8; /* Theme Color */
            font-weight: bold;
        }
        
        /* Fix footer on mobile */
        @media (max-width: 767px) {
            .main-footer {
                position: static !important;
                margin-bottom: 60px !important;
            }
        }
    </style>
</head>
@php
    $isModularDashboard = (Route::currentRouteName() == 'dashboard' && session('dashboard_layout', 'modular') == 'modular');
    $isPerformanceDashboard = in_array(Route::currentRouteName(), ['performance.sr', 'performance.bm', 'performance.am']);
    $sidebarBodyClass = (in_array(Route::currentRouteName(), ['pos', 'shomriddhi.gateway', 'purchase_workspace.dashboard', 'sales_workspace.dashboard', 'crm_workspace.dashboard', 'office_workspace.dashboard', 'hr_workspace.dashboard', 'accounts_workspace.dashboard']) || $isModularDashboard || $isPerformanceDashboard) ? 'sidebar-collapse' : 'sidebar-mini';
@endphp
<body class="layout-fixed layout-navbar-fixed layout-footer-fixed text-sm {{ $sidebarBodyClass }} {{ auth()->user()->theme_mode == 1 ? ' ' : 'dark-mode' }}">
<div class="wrapper">
    <!-- Navbar -->
@include('layouts.partial.__navbar')
<!-- /.navbar -->
    <!-- Main Sidebar Container -->
@include('layouts.partial.__main_sidebar')
<!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper" style="position: relative;">
        <div id="preloader">
            <div id="loader"></div>
        </div>
        <!-- Content Header (Page header) -->

        <div class="content-header">
            <div class="container-fluid">
                <div class="row justify-content-md-center">
                    @if(Route::currentRouteName() == 'dashboard' || in_array(Route::currentRouteName(), ['shomriddhi.gateway', 'purchase_workspace.dashboard', 'sales_workspace.dashboard', 'crm_workspace.dashboard', 'office_workspace.dashboard', 'hr_workspace.dashboard', 'accounts_workspace.dashboard']) || $isPerformanceDashboard)
                        <!-- <div class="col-sm-12">
                            <h1 class="m-0">
                                @yield('title')
                            </h1>
                        </div> -->
                    @else
                        <div class="col-sm-12">
                            <h1 class="m-0">
                                @yield('title')
                            </h1>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <div class="content">
            <div class="container-fluid">
                @yield('content')
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->
    <!-- Main Footer -->

 @include('layouts.partial.__footer')

    <!-- Global Bottom Mobile Shortcut Menu -->
    <div class="bottom-nav-menu d-flex d-md-none">
        <div class="nav-item {{ Route::currentRouteName() == 'dashboard' ? 'active' : '' }}">
            <a href="{{ route('dashboard') }}" class="nav-link">
                <i class="fas fa-home"></i>
                <span>Home</span>
            </a>
        </div>
        
        <div class="nav-item {{ Route::currentRouteName() == 'employees.attendance' ? 'active' : '' }}">
            <a href="{{ route('employees.attendance') }}" class="nav-link">
                <i class="fas fa-camera"></i>
                <span>Attendance</span>
            </a>
        </div>
        
        <div class="nav-item">
            <a href="#" class="nav-link" data-widget="pushmenu" role="button">
                <i class="fas fa-bars"></i>
                <span>Menu</span>
            </a>
        </div>
    </div>
</div>
<!-- ./wrapper -->
@include('layouts.partial.__media_files')
<!-- REQUIRED SCRIPTS -->
@include('layouts.partial.__scripts')
<script>
    var businessDate = '{{ \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y') }}';
</script>
@include('layouts.partial.__autocomplete')
@include('layouts.partial.__location_tracking')
@yield('script')
@stack('script')
<script src="{{ asset('themes/backend/plugins/pace-progress/pace.min.js') }}"></script>
<!-- AdminLTE App -->
<script src="{{ asset('themes/backend/dist/js/adminlte.min.js') }}"></script>
<script>
    function createTaskAlert() {
        
    }
    function loadDataAndNotify(skip) {
        $('.notification-preloader').show();
        $.ajax({
            url: '{{ route('notification_scroll_data') }}', // The URL defined in your Laravel route
            method: 'GET',
            data: {skip: skip},
            success: function (data) {
                // Display the notification box with the received data
                if (data.is_data) {
                    setTimeout(function () {
                        $('.notification-preloader').hide();
                        $('#notification-list-area').append(data.html);
                    }, 300); // 200 milliseconds

                } else {
                    $('.notification-preloader').hide();
                }
            },
            error: function () {
                console.log('Error loading data.');
            },
        });
    }

    // Implement scroll event for the specific section
    $('.notification-area-custom').scroll(function () {
        // Check if we are near the bottom of the scrollable section
        if (
            $('.notification-area-custom').scrollTop() + $('.notification-area-custom').innerHeight() >=
            $('.notification-area-custom')[0].scrollHeight - 100
        ) {
            let initSkip = 10;
            let notification_skip = parseFloat($('#notification_skip').val());
            notification_skip = (isNaN(notification_skip) || notification_skip < 0) ? 0 : notification_skip;
            let skip = notification_skip + initSkip;
            loadDataAndNotify(skip);
            $('#notification_skip').val(skip)
        }
    });

    $(document).on('click', '.zoom-gallery', function (e) {
        e.preventDefault();
        $(this).magnificPopup({
            delegate: 'a',
            type: 'image',
            closeOnContentClick: false,
            closeBtnInside: false,
            mainClass: 'mfp-with-zoom mfp-img-mobile',
            allowHTMLInTemplate: true,
            image: {
                verticalFit: true,
                titleSrc: function(item) {
                    return item.el.attr('title') + ' &middot; <a class="image-source-link" href="'+item.el.attr('data-source')+'" target="_blank">image source</a>';
                }
            },
            gallery: {
                enabled: true
            },
            zoom: {
                enabled: true,
                duration: 300, // don't foget to change the duration also in CSS
                opener: function(element) {
                    return element.find('img');
                }
            }
        }).magnificPopup('open');
    });
    
</script>

<script>
    // Redirect console logs to Flutter
    (function() {

        const originalLog = console.log;
        const originalError = console.error;
        const originalWarn = console.warn;

        console.log = function() {
            if (window.ConsoleLog) {
                window.ConsoleLog.postMessage(Array.from(arguments).join(' '));
            }
            originalLog.apply(console, arguments);
        };
        console.error = function() {
            if (window.ConsoleLog) {
                window.ConsoleLog.postMessage("ERROR: " + Array.from(arguments).join(' '));
            }
            originalError.apply(console, arguments);
        };
        console.warn = function() {
            if (window.ConsoleLog) {
                window.ConsoleLog.postMessage("WARN: " + Array.from(arguments).join(' '));
            }
            originalWarn.apply(console, arguments);
        };
        
        window.onerror = function(message, source, lineno, colno, error) {
            if (window.ConsoleLog) {
                window.ConsoleLog.postMessage("RUNTIME ERROR: " + message + " at " + source + ":" + lineno);
            }
        };
    })();
</script>
<script src="{{ asset('themes/backend/plugins/tippyjs/popper.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/tippyjs/tippy.min.js') }}"></script>
</body>
</html>



