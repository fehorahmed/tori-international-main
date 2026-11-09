<style>
    /* Odoo ERP Switcher Style */
    .odoo-container {
        /* padding: 40px 20px; */
        background: #f8fafc;
        border-radius: 24px;
        min-height: 550px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid rgba(0, 0, 0, 0.02);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
    }

    .dark-mode .odoo-container {
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.02);
    }

    .odoo-title-section {
        text-align: center;
    }

    .odoo-title-section h3 {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        color: #1e293b;
        margin-bottom: 8px;
    }

    .dark-mode .odoo-title-section h3 {
        color: #f1f5f9;
    }

    .odoo-title-section p {
        color: #64748b;
        font-size: 0.95rem;
    }

    .dark-mode .odoo-title-section p {
        color: #94a3b8;
    }

    .odoo-grid {
        display: flex;
        flex-wrap: wrap;
        gap: 60px 40px;
        justify-content: center;
        max-width: 800px;
        margin: 0 auto;
        width: 100%;
    }

    .odoo-app {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        text-decoration: none !important;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        width: 160px;
        cursor: pointer;
        margin-bottom: 10px;
    }

    .odoo-icon-wrapper {
        width: 100px;
        height: 100px;
        border-radius: 26px; /* iOS / Odoo Squircle Style */
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3.2rem;
        color: #ffffff;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-bottom: 16px;
        position: relative;
    }

    /* Soft Glow Effects on Hover matching the App Color */
    .bg-office { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .odoo-app:hover .bg-office { box-shadow: 0 15px 30px rgba(6, 182, 212, 0.35); }

    .bg-users { background: linear-gradient(135deg, #a855f7 0%, #6b21a8 100%); }
    .odoo-app:hover .bg-users { box-shadow: 0 15px 30px rgba(168, 85, 247, 0.35); }

    .bg-branches { background: linear-gradient(135deg, #f472b6 0%, #db2777 100%); }
    .odoo-app:hover .bg-branches { box-shadow: 0 15px 30px rgba(244, 114, 182, 0.35); }

    .bg-crm { background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); }
    .odoo-app:hover .bg-crm { box-shadow: 0 15px 30px rgba(236, 72, 153, 0.35); }

    .bg-hrm { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); }
    .odoo-app:hover .bg-hrm { box-shadow: 0 15px 30px rgba(59, 130, 246, 0.35); }

    .bg-payroll { background: linear-gradient(135deg, #0d9488 0%, #115e59 100%); }
    .odoo-app:hover .bg-payroll { box-shadow: 0 15px 30px rgba(13, 148, 136, 0.35); }

    .bg-managers { background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%); }
    .odoo-app:hover .bg-managers { box-shadow: 0 15px 30px rgba(20, 184, 166, 0.35); }

    .bg-settings { background: linear-gradient(135deg, #64748b 0%, #475569 100%); }
    .odoo-app:hover .bg-settings { box-shadow: 0 15px 30px rgba(100, 116, 139, 0.35); }

    .bg-purchase { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .odoo-app:hover .bg-purchase { box-shadow: 0 15px 30px rgba(245, 158, 11, 0.35); }

    .bg-inventory-app { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
    .odoo-app:hover .bg-inventory-app { box-shadow: 0 15px 30px rgba(2, 132, 199, 0.35); }

    .bg-sales { background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); }
    .odoo-app:hover .bg-sales { box-shadow: 0 15px 30px rgba(99, 102, 241, 0.35); }

    .bg-ecommerce { background: linear-gradient(135deg, #c084fc 0%, #7c3aed 100%); }
    .odoo-app:hover .bg-ecommerce { box-shadow: 0 15px 30px rgba(124, 58, 237, 0.35); }

    .bg-transfer { background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%); }
    .odoo-app:hover .bg-transfer { box-shadow: 0 15px 30px rgba(139, 92, 246, 0.35); }

    .bg-conversion { background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%); }
    .odoo-app:hover .bg-conversion { box-shadow: 0 15px 30px rgba(251, 146, 60, 0.35); }

    .bg-sr { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
    .odoo-app:hover .bg-sr { box-shadow: 0 15px 30px rgba(16, 185, 129, 0.35); }

    .bg-trainee { background: linear-gradient(135deg, #f43f5e 0%, #be123c 100%); }
    .odoo-app:hover .bg-trainee { box-shadow: 0 15px 30px rgba(244, 63, 94, 0.35); }

    .bg-microfinance { background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); }
    .odoo-app:hover .bg-microfinance { box-shadow: 0 15px 30px rgba(234, 179, 8, 0.35); }

    .bg-loan { background: linear-gradient(135deg, #84cc16 0%, #65a30d 100%); }
    .odoo-app:hover .bg-loan { box-shadow: 0 15px 30px rgba(132, 204, 22, 0.35); }

    .bg-monitoring { background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%); }
    .odoo-app:hover .bg-monitoring { box-shadow: 0 15px 30px rgba(6, 182, 212, 0.35); }

    .bg-utilize { background: linear-gradient(135deg, #475569 0%, #334155 100%); }
    .odoo-app:hover .bg-utilize { box-shadow: 0 15px 30px rgba(71, 85, 105, 0.35); }

    .bg-reports { background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%); }
    .odoo-app:hover .bg-reports { box-shadow: 0 15px 30px rgba(2, 132, 199, 0.35); }

    .bg-accounts { background: linear-gradient(135deg, #0284c7 0%, #1e3a8a 100%); }
    .odoo-app:hover .bg-accounts { box-shadow: 0 15px 30px rgba(2, 132, 199, 0.35); }

    .bg-cards { background: linear-gradient(135deg, #4f46e5 0%, #312e81 100%); }
    .odoo-app:hover .bg-cards { box-shadow: 0 15px 30px rgba(79, 70, 229, 0.35); }

    /* Scaling animation */
    .odoo-app:hover .odoo-icon-wrapper {
        transform: translateY(-6px) scale(1.06);
    }

    .odoo-label {
        font-size: 1.05rem;
        font-weight: 600;
        color: #475569;
        font-family: 'Outfit', 'Inter', sans-serif;
        transition: all 0.3s ease;
        letter-spacing: 0.1px;
        line-height: 1.2;
    }

    .dark-mode .odoo-label {
        color: #cbd5e1;
    }

    .odoo-app:hover .odoo-label {
        color: #0f172a;
        font-weight: 700;
    }

    .dark-mode .odoo-app:hover .odoo-label {
        color: #ffffff;
    }

    /* Premium Global Search Bar Style */
    .global-search-wrapper {
        position: relative;
        max-width: 520px;
        margin: 15px auto 25px auto;
        width: 100%;
        z-index: 1000;
    }

    .search-input-group {
        position: relative;
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.7);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.04), 0 1px 3px rgba(0, 0, 0, 0.02);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        padding: 4px 6px;
    }

    .dark-mode .search-input-group {
        background: rgba(15, 23, 42, 0.6);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2), 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .search-input-group:focus-within {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15), 0 10px 30px rgba(59, 130, 246, 0.1);
        transform: translateY(-2px);
    }

    .search-input-group i.search-icon {
        color: #94a3b8;
        font-size: 1.15rem;
        margin-left: 14px;
        margin-right: 10px;
        transition: color 0.3s;
        flex-shrink: 0;
    }

    .search-input-group:focus-within i.search-icon {
        color: #3b82f6;
    }

    .global-search-input {
        flex: 1;
        width: 0;
        border: none !important;
        background: transparent !important;
        outline: none !important;
        box-shadow: none !important;
        padding: 10px 0;
        font-size: 0.95rem;
        font-weight: 500;
        color: #1e293b;
        font-family: 'Inter', sans-serif;
    }

    .dark-mode .global-search-input {
        color: #f1f5f9;
    }

    .global-search-input::placeholder {
        color: #94a3b8;
        font-weight: 400;
    }

    .search-shortcut-badge {
        background: rgba(0, 0, 0, 0.05);
        color: #64748b;
        font-size: 0.72rem;
        font-weight: 700;
        padding: 4px 8px;
        border-radius: 8px;
        margin-right: 8px;
        border: 1px solid rgba(0, 0, 0, 0.04);
        pointer-events: none;
        user-select: none;
        letter-spacing: 0.5px;
        flex-shrink: 0;
        white-space: nowrap;
    }

    .dark-mode .search-shortcut-badge {
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    /* Floating Suggestions Panel */
    .search-suggestions-panel {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        background: rgba(255, 255, 255, 0.96);
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 18px;
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12), 0 1px 3px rgba(0, 0, 0, 0.02);
        max-height: 380px;
        overflow-y: auto;
        display: none;
        z-index: 1010;
        padding: 8px;
        transition: all 0.2s ease;
    }

    .dark-mode .search-suggestions-panel {
        background: rgba(15, 23, 42, 0.95);
        border: 1px solid rgba(255, 255, 255, 0.08);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4), 0 1px 3px rgba(0, 0, 0, 0.2);
    }

    /* Custom scrollbar for panel */
    .search-suggestions-panel::-webkit-scrollbar {
        width: 6px;
    }
    .search-suggestions-panel::-webkit-scrollbar-track {
        background: transparent;
    }
    .search-suggestions-panel::-webkit-scrollbar-thumb {
        background: rgba(0, 0, 0, 0.1);
        border-radius: 10px;
    }
    .dark-mode .search-suggestions-panel::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.1);
    }

    .suggestion-group-title {
        font-size: 0.72rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #94a3b8;
        padding: 8px 12px 4px 12px;
        letter-spacing: 0.8px;
    }

    .suggestion-item {
        display: flex;
        align-items: center;
        padding: 10px 14px;
        border-radius: 12px;
        color: #334155;
        text-decoration: none !important;
        transition: all 0.2s ease;
        cursor: pointer;
        margin-bottom: 2px;
    }

    .dark-mode .suggestion-item {
        color: #cbd5e1;
    }

    .suggestion-item:hover, .suggestion-item.active-item {
        background: rgba(59, 130, 246, 0.08);
        color: #1e3a8a;
        transform: translateX(4px);
    }

    .dark-mode .suggestion-item:hover, .dark-mode .suggestion-item.active-item {
        background: rgba(59, 130, 246, 0.15);
        color: #3b82f6;
    }

    .suggestion-item i {
        font-size: 1.05rem;
        width: 32px;
        height: 32px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(0, 0, 0, 0.04);
        color: #64748b;
        margin-right: 12px;
        transition: all 0.2s;
    }

    .dark-mode .suggestion-item i {
        background: rgba(255, 255, 255, 0.04);
        color: #94a3b8;
    }

    .suggestion-item:hover i, .suggestion-item.active-item i {
        background: #3b82f6;
        color: white;
        transform: scale(1.05);
    }

    .suggestion-info {
        display: flex;
        flex-direction: column;
    }

    .suggestion-title {
        font-size: 0.88rem;
        font-weight: 600;
        line-height: 1.3;
    }

    .suggestion-category-badge {
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        background: rgba(0, 0, 0, 0.05);
        color: #64748b;
        padding: 1px 6px;
        border-radius: 4px;
        align-self: flex-start;
        margin-top: 2px;
    }

    .dark-mode .suggestion-category-badge {
        background: rgba(255, 255, 255, 0.08);
        color: #cbd5e1;
    }

    .no-results-item {
        padding: 20px;
        text-align: center;
        color: #94a3b8;
        font-size: 0.9rem;
        font-weight: 500;
    }

    /* Attendance Reminder Timeline Styles */
    .attendance-timeline-container {
        margin-top: 40px;
        padding: 30px 20px;
        width: 100%;
        max-width: 1040px;
        margin-left: auto;
        margin-right: auto;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 0 25px rgba(0, 0, 0, 0.04);
        border: 1px solid #f1f5f9;
        transition: all 0.3s ease;
    }
    .attendance-timeline-container:hover {
        box-shadow: 0 0 35px rgba(0, 0, 0, 0.07);
    }
    .dark-mode .attendance-timeline-container {
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.02);
    }
    .timeline-wrapper {
        display: flex;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
        gap: 30px;
        position: relative;
    }
    /* Connecting line removed as requested */
    .timeline-item {
        position: relative;
        z-index: 2;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 80px;
        text-decoration: none !important;
        cursor: default;
    }
    a.timeline-item {
        cursor: pointer;
    }
    a.timeline-item:hover .timeline-circle {
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
    }
    .timeline-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        font-weight: 700;
        color: white;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        margin-bottom: 8px;
        background: #cbd5e1; /* Default/Future */
        transition: transform 0.3s ease;
    }
    .timeline-item:hover .timeline-circle {
        transform: scale(1.1);
    }
    .status-responded .timeline-circle {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 6px 15px rgba(16, 185, 129, 0.3);
    }
    .status-pending .timeline-circle {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7);
        animation: pulse-warning 2s infinite;
    }
    @keyframes pulse-warning {
        0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.7); }
        70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
        100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
    }
    a.timeline-item:hover .timeline-circle {
        animation: none;
        box-shadow: 0 8px 20px rgba(245, 158, 11, 0.4);
    }
    .status-missed .timeline-circle {
        background: rgba(239, 68, 68, 0.1);
        border: 2px solid #ef4444;
        color: #ef4444;
        box-shadow: none;
    }
    .timeline-time {
        font-size: 0.75rem;
        font-weight: 600;
        color: #64748b;
    }
    .dark-mode .timeline-time {
        color: #94a3b8;
    }
    .timeline-title {
        text-align: center;
        margin-bottom: 25px;
        font-family: 'Outfit', sans-serif;
        color: #334155;
        font-weight: 700;
    }
    .dark-mode .timeline-title {
        color: #e2e8f0;
    }

    /* Mobile Responsiveness for Timeline */
    @media (max-width: 576px) {
        .attendance-timeline-container {
            padding: 20px 10px;
        }
        .timeline-wrapper {
            gap: 15px;
        }
        .timeline-item {
            width: 75px;
        }
        .timeline-circle {
            width: 45px;
            height: 45px;
            font-size: 1.1rem;
        }
    }
    /* Responsive Grid for Odoo Dashboard */
    @media (max-width: 992px) {
        .odoo-grid {
            gap: 40px 20px;
            max-width: 100%;
        }
        .odoo-icon-wrapper {
            width: 85px;
            height: 85px;
            font-size: 2.8rem;
        }
        .odoo-app {
            width: calc(33.333% - 20px);
        }
    }

    @media (max-width: 576px) {
        .odoo-grid {
            gap: 30px 10px;
            max-width: 100%;
        }
        .odoo-icon-wrapper {
            width: 80px;
            height: 80px;
            font-size: 2.4rem;
            border-radius: 22px;
        }
        .odoo-app {
            width: calc(50% - 10px);
        }
        .odoo-label {
            font-size: 0.95rem;
        }
    }
</style>

<div class="odoo-container">
    <div class="odoo-title-section">
        <h3>Apps Workspace</h3>
        <p class="m-0">Choose an application module to begin your work</p>
    </div>

    <!-- Global Command Search Bar -->
    <div class="global-search-wrapper">
        <div class="search-input-group">
            <i class="ph ph-magnifying-glass search-icon"></i>
            <input type="text" class="global-search-input" id="globalSearchInput" autocomplete="off" placeholder="Search pages, vouchers, employees, suppliers...">
            <span class="search-shortcut-badge">Ctrl + K</span>
        </div>
        <div class="search-suggestions-panel" id="searchSuggestionsPanel"></div>
    </div>

    <!-- Customized Odoo Launcher Grid containing 16 modular blocks -->
    <div class="odoo-grid">
        
        <!-- Hot Reload / Refresh -->
        <a href="javascript:void(0)" onclick="this.querySelector('i').classList.add('fa-spin'); location.reload(true);" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #64748b 0%, #334155 100%);">
                <i class="ph ph-arrows-clockwise"></i>
            </div>
            <div class="odoo-label">Refresh</div>
        </a>

        <!-- Performance Dashboard -->
        @if(auth()->user()->role == 'Admin')
        <a href="{{ route('performance.sr') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                <i class="ph ph-chart-pie-slice"></i>
            </div>
            <div class="odoo-label">Dashboard</div>
        </a>
        @endif

        <!-- 5. Settings -->
        @can('settings')
        <a href="{{ route('settings.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                <i class="ph ph-gear"></i>
            </div>
            <div class="odoo-label">Settings</div>
        </a>
        @endcan

        <!-- Office Workspace -->
        @if(auth()->user()->can('office_management') || auth()->user()->can('user_management') || auth()->user()->can('manager_management') || auth()->user()->can('trainee_management') || auth()->user()->can('manage_company_loan') || auth()->user()->can('company_loan_report_statement'))
        <a href="{{ route('office_workspace.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                <i class="ph ph-buildings"></i>
            </div>
            <div class="odoo-label">Office Ws</div>
        </a>
        @endif

        <!-- Employee Profile Module -->
        @if(auth()->user()->employee_id)
        <a href="{{ route('employee_profile.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #14b8a6 0%, #0f766e 100%);">
                <i class="ph ph-identification-badge"></i>
            </div>
            <div class="odoo-label">My Profile</div>
        </a>
        @endif

        
        <!-- Branches -->
        @if(auth()->user()->can('branch_management'))
        <a href="{{ route('branch_management.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper bg-branches">
                <i class="ph ph-storefront"></i>
            </div>
            <div class="odoo-label">Branches</div>
        </a>
        @endif

        <!-- 2. CRM -->
        @if(auth()->user()->can('crm'))
        <a href="{{ route('crm_workspace.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                <i class="ph ph-users-three"></i>
            </div>
            <div class="odoo-label">SR Ws</div>
        </a>
        @endif

        <!-- 3. HR & Payroll Workspace -->
        @if(auth()->user()->can('hrm') || auth()->user()->can('payroll') || auth()->user()->can('contractor_management'))
        <a href="{{ route('hr_workspace.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                <i class="ph ph-users"></i>
            </div>
            <div class="odoo-label">HR & Payroll</div>
        </a>
        @endif

        <!-- 6. Purchase Workspace -->
        @if(auth()->user()->can('purchase') || auth()->user()->can('purchase_settings') || auth()->user()->can('inventory'))
        <a href="{{ route('purchase_workspace.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                <i class="ph ph-shopping-cart"></i>
            </div>
            <div class="odoo-label">Purchase</div>
        </a>
        @endif
        <!-- 7. Sales Workspace -->
        @if(auth()->user()->can('sales') || auth()->user()->can('ecommerce'))
        <a href="{{ route('sales_workspace.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper bg-sales">
                <i class="ph ph-shopping-bag"></i>
            </div>
            <div class="odoo-label">Sales</div>
        </a>
        @endif

        <!-- 7.5 Returns Management -->
        @if(auth()->user()->can('returns_management'))
        <a href="{{ route('returns-management.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #f43f5e 0%, #9f1239 100%);">
                <i class="ph ph-arrow-u-up-left"></i>
            </div>
            <div class="odoo-label">Returns Mgmt</div>
        </a>
        @endif

        <!-- Shomriddhi Modules Gateway -->
        @if(auth()->user()->can('shomriddhi_advance') || auth()->user()->can('shomriddhi_fdr') || auth()->user()->can('shomriddhi_dps') || auth()->user()->can('shomriddhi_loan'))
        <a href="{{ route('shomriddhi.gateway') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #84cc16 0%, #4d7c0f 100%);">
                <i class="ph ph-piggy-bank"></i>
            </div>
            <div class="odoo-label">Shomriddhi</div>
        </a>
        @endif

        <!-- Accounts Workspace -->
        @if(auth()->user()->can('accounts') || auth()->user()->can('fixed_asset'))
        <a href="{{ route('accounts_workspace.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                <i class="ph ph-calculator"></i>
            </div>
            <div class="odoo-label">Accounts Ws</div>
        </a>
        @endif

        {{-- 
        <!-- 12. Microfinance --> 
        @if(auth()->user()->can('microfinance'))
        <a href="{{ route('microfinance.dashboard') }}" class="odoo-app">
            <div class="odoo-icon-wrapper bg-microfinance">
                <i class="ph ph-hand-coins"></i>
            </div>
            <div class="odoo-label">Microfinance</div>
        </a>
        @endif 
        --}}
    </div>
    
    <!-- Daily Attendance Reminders Timeline -->
    @if(isset($dailyReminders) && $dailyReminders->count() > 0)
    <div class="attendance-timeline-container">
        <h4 class="timeline-title"><i class="ph ph-clock mr-2 text-primary"></i>Today's Attendance Track</h4>
        <div class="timeline-wrapper">
            @foreach($dailyReminders as $index => $reminder)
                @if($reminder->status == 'pending')
                    <a href="{{ route('employees.attendance') }}" class="timeline-item status-{{ strtolower($reminder->status) }}" title="Status: {{ ucfirst($reminder->status) }} - Click to Punch In">
                @else
                    <div class="timeline-item status-{{ strtolower($reminder->status) }}" title="Status: {{ ucfirst($reminder->status) }}">
                @endif
                    <div class="timeline-circle">
                        @if($reminder->status == 'responded')
                            <i class="ph ph-check"></i>
                        @elseif($reminder->status == 'missed')
                            <i class="ph ph-warning"></i>
                        @else
                            {{ $index + 1 }}
                        @endif
                    </div>
                    <div class="timeline-time">
                        {{ \Carbon\Carbon::parse($reminder->notified_at)->format('h:i A') }}
                    </div>
                @if($reminder->status == 'pending')
                    </a>
                @else
                    </div>
                @endif
            @endforeach
        </div>
    </div>
    @endif
</div>
