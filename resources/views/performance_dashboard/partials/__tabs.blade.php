<div class="row mb-1">
    <div class="col-12 d-flex justify-content-between align-items-center flex-wrap mb-1">
        <h4 class="font-weight-bold m-0 text-dark d-none d-md-block" style="font-family: 'Outfit', sans-serif;">Performance Analytics</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-white border shadow-sm rounded-pill px-4 font-weight-bold text-secondary ml-auto">
            <i class="fas fa-th text-primary mr-2"></i> Apps Workspace
        </a>
    </div>
    <div class="col-12">
        <div class="premium-tabs-wrapper">
            <ul class="nav nav-pills custom-pills premium-tabs">
                <li class="nav-item">
                    <a class="nav-link {{ Route::currentRouteName() == 'performance.sr' ? 'active' : '' }}" href="{{ route('performance.sr') }}">
                        <i class="fas fa-user-tie mr-2"></i> SR Performance
                    </a>
                </li>
                <li class="nav-item d-none">
                    <a class="nav-link {{ Route::currentRouteName() == 'performance.bm' ? 'active' : '' }}" href="{{ route('performance.bm') }}">
                        <i class="fas fa-building mr-2"></i> Branch Manager Performance
                    </a>
                </li>
                <li class="nav-item d-none">
                    <a class="nav-link {{ Route::currentRouteName() == 'performance.am' ? 'active' : '' }}" href="{{ route('performance.am') }}">
                        <i class="fas fa-sitemap mr-2"></i> Area Manager Performance
                    </a>
                </li>
            </ul>
        </div>
    </div>
</div>

<style>
    .premium-tabs-wrapper {
        width: 100%;
        overflow-x: auto;
        -webkit-overflow-scrolling: touch;
        padding-bottom: 5px; /* For scrollbar breathing room */
        scrollbar-width: none; /* Firefox */
    }
    .premium-tabs-wrapper::-webkit-scrollbar {
        display: none; /* Chrome, Safari */
    }
    .premium-tabs {
        background: #fff;
        padding: 8px;
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        display: inline-flex;
        flex-wrap: nowrap;
        white-space: nowrap;
    }
    .premium-tabs .nav-item {
        margin-right: 5px;
    }
    .premium-tabs .nav-link {
        color: #64748b;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 8px;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
    }
    .premium-tabs .nav-link:hover {
        background: #f8fafc;
        color: #3b82f6;
    }
    .premium-tabs .nav-link.active {
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        color: #fff;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }
</style>
