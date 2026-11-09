@extends('layouts.app')
@section('title', 'HR & Payroll Workspace')

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
                <h3>HR & Payroll Workspace</h3>
                <p class="m-0">Human Resources & Workforce Management</p>
            </div>

            <div class="odoo-grid">
                
                <!-- HRM Module -->
                @if(auth()->user()->can('hrm'))
                <a href="{{ route('hrm.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);">
                        <i class="ph ph-users-three"></i>
                    </div>
                    <div class="odoo-label">HRM</div>
                </a>
                @endif

                <!-- Payroll Module -->
                @if(auth()->user()->can('payroll'))
                <a href="{{ route('payroll.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                        <i class="ph ph-wallet"></i>
                    </div>
                    <div class="odoo-label">Payroll</div>
                </a>
                @endif

                <!-- Contractors Module -->
                @if(auth()->user()->can('contractor_management'))
                <a href="{{ route('contractor_management.dashboard') }}" class="odoo-app">
                    <div class="odoo-icon-wrapper" style="background: linear-gradient(135deg, #fb923c 0%, #ea580c 100%);">
                        <i class="ph ph-hard-hat"></i>
                    </div>
                    <div class="odoo-label">Contractors</div>
                </a>
                @endif

            </div>
        </div>
    </div>
@endsection
