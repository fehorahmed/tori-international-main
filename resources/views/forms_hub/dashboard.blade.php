@extends('layouts.app')
@section('title', 'Forms Hub')
@section('content')
@include('layouts.partial.__premium_dashboard_css')

<!-- Welcome Banner -->
<div class="row mb-4">
    <div class="col-12">
        <div class="welcome-banner d-flex justify-content-between align-items-center" style="background: linear-gradient(135deg, #1e293b, #0f172a);">
            <div style="z-index: 1;">
                <div class="welcome-title text-white">Centralized Forms Repository</div>
                <div class="welcome-subtitle text-white-50">Access, view and print all necessary organizational forms from one place.</div>
            </div>
            <div class="d-none d-md-block" style="z-index: 1; opacity: 0.8;">
                <i class="fas fa-file-pdf fa-4x text-white"></i>
            </div>
        </div>
    </div>
</div>

<!-- Global Command Search Bar -->
<div class="global-search-wrapper mb-4" style="max-width: 520px; margin: 0 auto;">
    <div class="search-input-group" style="display: flex; align-items: center; background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); border: 1px solid rgba(0, 0, 0, 0.08); border-radius: 16px; padding: 8px 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);">
        <i class="fas fa-search search-icon" style="color: #94a3b8; margin-right: 10px;"></i>
        <input type="text" class="global-search-input" id="formSearchInput" autocomplete="off" placeholder="Search forms by name or type..." style="flex: 1; border: none; background: transparent; outline: none; font-size: 16px;">
    </div>
</div>

<div class="row">
    <!-- Forms Grid (Odoo Style Cards) -->
    <div class="col-12">
        <div class="odoo-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(140px, 1fr)); gap: 30px; justify-items: center;">
            
            <!-- Example Form 1 -->
            <a href="{{ route('forms_hub.customer') }}" target="_blank" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper bg-crm" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">Customer Form</div>
            </a>

            <!-- Example Form 2 -->
            <a href="{{ route('forms_hub.supplier') }}" target="_blank" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper bg-purchase" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-truck"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">Supplier Form</div>
            </a>

            <!-- Example Form 3 -->
            <a href="#" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper bg-microfinance" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #eab308 0%, #ca8a04 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-donate"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">Microfinance Admission</div>
            </a>

            <!-- Dealer Form -->
            <a href="{{ route('forms_hub.dealer') }}" target="_blank" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">Dealer Form</div>
            </a>

            <!-- DPS Form -->
            <a href="{{ route('forms_hub.dps') }}" target="_blank" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #10b981 0%, #047857 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-leaf"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">DPS Form</div>
            </a>

            <!-- FDR Form -->
            <a href="{{ route('forms_hub.fdr') }}" target="_blank" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #8b5cf6 0%, #5b21b6 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-lock"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">FDR Form</div>
            </a>

            <!-- Shomriddhi Forms -->
            <a href="{{ route('forms_hub.shomriddhi.index') }}" class="odoo-app" style="display: flex; flex-direction: column; align-items: center; text-decoration: none; text-align: center;">
                <div class="odoo-icon-wrapper" style="width: 80px; height: 80px; border-radius: 20px; display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: #fff; background: linear-gradient(135deg, #ec4899 0%, #be185d 100%); box-shadow: 0 10px 25px rgba(0,0,0,0.15); margin-bottom: 12px; transition: transform 0.3s;">
                    <i class="fas fa-piggy-bank"></i>
                </div>
                <div class="odoo-label" style="font-weight: 600; color: #475569;">Shomriddhi Forms</div>
            </a>

        </div>
    </div>
</div>

<style>
    .odoo-app:hover .odoo-icon-wrapper {
        transform: translateY(-5px) scale(1.05);
    }
    .odoo-app:hover .odoo-label {
        color: #0f172a !important;
        font-weight: 700 !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('formSearchInput');
        const apps = document.querySelectorAll('.odoo-app');

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                
                apps.forEach(app => {
                    const label = app.querySelector('.odoo-label').textContent.toLowerCase();
                    if (label.includes(term)) {
                        app.style.display = 'flex';
                    } else {
                        app.style.display = 'none';
                    }
                });
            });
        }
    });
</script>
@endsection
