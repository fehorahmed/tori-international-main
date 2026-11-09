<!-- Odoo-Style Apps Switcher Dropdown (Desktop & Mobile Friendly) -->
<li class="nav-item dropdown">
    <style>
        .quick-app-item {
            transition: all 0.2s ease;
            border-radius: 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px 4px;
        }
        .quick-app-item:hover {
            background: rgba(0, 123, 255, 0.08) !important;
            text-decoration: none !important;
        }
        .quick-app-item:hover .icon-box {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15) !important;
        }
        .quick-app-item .icon-box {
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .quick-apps-dropdown {
            right: 0 !important;
            left: auto !important;
        }
        @media (max-width: 767.98px) {
            .quick-apps-dropdown {
                width: calc(100vw - 30px) !important;
                max-width: 330px !important;
                position: absolute !important;
                right: -60px !important;
                left: auto !important;
                top: 45px !important;
                border-radius: 16px !important;
            }
        }
        .quick-apps-grid::-webkit-scrollbar {
            width: 5px;
        }
        .quick-apps-grid::-webkit-scrollbar-track {
            background: transparent;
        }
        .quick-apps-grid::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
    </style>
    <a class="nav-link" data-toggle="dropdown" href="#" aria-expanded="false" style="padding-top: 8px;">
        <i class="ph ph-squares-four text-muted" style="font-size: 1.15rem;"></i>
    </a>
    <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right p-3 border-0 shadow-lg quick-apps-dropdown" style="width: 320px; border-radius: 20px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(15px);">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <span class="font-weight-bold text-dark" style="font-size: 0.9rem; font-family: 'Outfit', sans-serif;">Quick Apps Launcher</span>
            <span class="badge bg-soft-blue text-primary px-2 py-1" style="font-size: 0.7rem; border-radius: 6px;">1-Click</span>
        </div>
        <div class="quick-apps-grid" style="max-height: 360px; overflow-y: auto; overflow-x: hidden; display: grid; grid-template-columns: repeat(3, 1fr); gap: 8px; padding: 4px;">
            
            

            @if(auth()->user()->role == 'Admin')
            <a href="{{ route('performance.sr') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <i class="ph ph-chart-pie-slice"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Dashboard</div>
            </a>
            @endif

            @if(auth()->user()->employee_id)
            <a href="{{ route('employee_profile.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #14b8a6, #0f766e);">
                    <i class="ph ph-identification-badge"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">My Profile</div>
            </a>
            @endif
            <!-- Office Workspace -->
            @if(auth()->user()->can('office_management') || auth()->user()->can('user_management') || auth()->user()->can('manager_management') || auth()->user()->can('trainee_management') || auth()->user()->can('manage_company_loan') || auth()->user()->can('company_loan_report_statement'))
            <a href="{{ route('office_workspace.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #06b6d4, #0891b2);">
                    <i class="ph ph-buildings"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Office Ws</div>
            </a>
            @endif



            <!-- 3. Branches -->
            @if(auth()->user()->can('branch_management') || auth()->user()->can('branch_view'))
            <a href="{{ route('branch_management.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #f472b6, #db2777);">
                    <i class="ph ph-storefront"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Branches</div>
            </a>
            @endif

            <!-- 4. CRM -->
            @if(auth()->user()->can('crm'))
            <a href="{{ route('crm_workspace.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #10b981, #059669);">
                    <i class="ph ph-users-three"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">SR Ws</div>
            </a>
            @endif

            <!-- 5. HR & Payroll Workspace -->
            @if(auth()->user()->can('hrm') || auth()->user()->can('payroll') || auth()->user()->can('contractor_management'))
            <a href="{{ route('hr_workspace.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #3b82f6, #1d4ed8);">
                    <i class="ph ph-users"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">HR Ws</div>
            </a>
            @endif





            <!-- 8. Settings -->
            @can('settings')
            <a href="{{ route('settings.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #64748b, #475569);">
                    <i class="ph ph-gear"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Settings</div>
            </a>
            @endcan

            <!-- 9. Purchase Workspace -->
            @if(auth()->user()->can('purchase') || auth()->user()->can('inventory'))
            <a href="{{ route('purchase_workspace.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <i class="ph ph-shopping-cart"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Purchase</div>
            </a>
            @endif

            <!-- 11. Sales Workspace -->
            @if(auth()->user()->can('sales') || auth()->user()->can('ecommerce'))
            <a href="{{ route('sales_workspace.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #6366f1, #4f46e5);">
                    <i class="ph ph-shopping-bag"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Sales</div>
            </a>
            @endif

            <!-- 11.5 Returns Management -->
            @if(auth()->user()->can('returns_management'))
            <a href="{{ route('returns-management.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #f43f5e, #e11d48);">
                    <i class="ph ph-arrow-u-up-left"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Returns</div>
            </a>
            @endif





            {{-- 
            <!-- 17. Microfinance -->
            @if(auth()->user()->can('microfinance'))
            <a href="{{ route('microfinance.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #ea9a08, #ca8a04);">
                    <i class="ph ph-hand-coins"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">M-Finance</div>
            </a>
            @endif
            --}}













            <!-- Shomriddhi Gateway -->
            @if(auth()->user()->can('shomriddhi_advance') || auth()->user()->can('shomriddhi_fdr') || auth()->user()->can('shomriddhi_dps') || auth()->user()->can('shomriddhi_loan'))
            <a href="{{ route('shomriddhi.gateway') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #10b981, #047857);">
                    <i class="ph ph-piggy-bank"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Shomriddhi</div>
            </a>
            @endif




            @if(auth()->user()->can('accounts') || auth()->user()->can('fixed_asset'))
            <a href="{{ route('accounts_workspace.dashboard') }}" class="quick-app-item text-decoration-none">
                <div class="icon-box d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; border-radius: 14px; font-size: 1.1rem; color: white; background: linear-gradient(135deg, #0284c7, #0369a1);">
                    <i class="ph ph-calculator"></i>
                </div>
                <div class="quick-app-label mt-1 text-secondary" style="font-size: 0.72rem; font-weight: 600;">Accounts</div>
            </a>
            @endif



        </div>
    </div>
</li>
