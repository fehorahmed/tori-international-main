@extends('layouts.app')
@section('title', 'Office Workspace')

@section('style')
<style>
    /* Odoo ERP Switcher Style */
    .odoo-container {
        background: #f8fafc;
        border-radius: 24px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        border: 1px solid rgba(0, 0, 0, 0.02);
        box-shadow: inset 0 2px 4px rgba(0,0,0,0.01);
        padding: 40px 20px;
    }

    .dark-mode .odoo-container {
        background: #0f172a;
        border: 1px solid rgba(255, 255, 255, 0.02);
    }

    .odoo-title-section {
        text-align: center;
        margin-bottom: 40px;
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
        border-radius: 26px;
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
@endsection

@section('content')
    <div class="container-fluid py-2">
        
        <div class="odoo-container">
            <a href="{{ route('dashboard') }}" class="btn btn-sm btn-light shadow-sm" style="position: absolute; top: 20px; left: 20px; border-radius: 12px; font-weight: 600; color: #475569;">
                <i class="ph-bold ph-arrow-left mr-1"></i> Back to Apps
            </a>
        
            <div class="odoo-title-section">
                <h3>Office Workspace</h3>
                <p class="m-0">Office & Administrative Modules</p>
            </div>

            <div class="odoo-grid">
                
                <!-- Office Utility Module -->
                @if(auth()->user()->can('office_management'))
                <a href="{{ route('office_management.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                        <i class="ph ph-buildings"></i>
                    </div>
                    <div class="odoo-label">Office Utility</div>
                </a>
                @endif

                <!-- Users Mgmt Module -->
                @if(auth()->user()->can('user_management'))
                <a href="{{ route('user_management.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                        <i class="ph ph-shield-check"></i>
                    </div>
                    <div class="odoo-label">Users Mgmt</div>
                </a>
                @endif

                <!-- Managers Module -->
                @can('manager_management')
                <a href="{{ route('manager_management.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                        <i class="ph ph-user-circle-gear"></i>
                    </div>
                    <div class="odoo-label">Managers</div>
                </a>
                @endcan

                <!-- Trainee Management Module -->
                @if(auth()->user()->can('trainee_management'))
                <a href="{{ route('trainee_management.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);">
                        <i class="ph ph-graduation-cap"></i>
                    </div>
                    <div class="odoo-label">Trainee Mgmt</div>
                </a>
                @endif

                <!-- Company Loan Module -->
                @if(auth()->user()->can('manage_company_loan') || auth()->user()->can('company_loan_report_statement'))
                <a href="{{ route('company_loan.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #06b6d4 0%, #0891b2 100%);">
                        <i class="ph ph-handshake"></i>
                    </div>
                    <div class="odoo-label">Company Loan</div>
                </a>
                @endif

                <!-- SMS Mgmt Module -->
                @if(auth()->user()->hasPermissionTo('sms_dashboard') || auth()->user()->hasPermissionTo('sms_gateways') || auth()->user()->hasPermissionTo('sms_templates'))
                <a href="{{ route('sms_management.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #f43f5e 0%, #9f1239 100%);">
                        <i class="ph ph-chat-text"></i>
                    </div>
                    <div class="odoo-label">SMS Mgmt</div>
                </a>
                @endif

                <!-- Forms Hub Module -->
                @if(auth()->user()->can('forms_hub'))
                <a href="{{ route('forms_hub.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        <i class="ph ph-file-pdf"></i>
                    </div>
                    <div class="odoo-label">Forms Hub</div>
                </a>
                @endif

            </div>
        </div>
    </div>
@endsection
