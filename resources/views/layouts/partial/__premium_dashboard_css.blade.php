<style>
    /* Premium Card Base */
    .premium-card {
        background: #ffffff;
        border-radius: 16px;
        border: none;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
        position: relative;
    }
    .premium-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.08);
    }

    /* Icon Box Base */
    .premium-icon-box {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        color: #fff;
    }

    /* Typography & Layout */
    .premium-number {
        font-size: 2rem;
        font-weight: 800;
        color: #1e293b;
        line-height: 1.1;
        font-family: 'Outfit', 'Inter', sans-serif;
    }
    .premium-title {
        color: #64748b;
        font-size: 0.85rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    .premium-footer {
        display: block;
        padding: 10px 16px;
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.8rem;
        text-decoration: none !important;
        transition: all 0.2s;
        border-top: 1px solid rgba(0,0,0,0.03);
    }
    .premium-card:hover .premium-footer {
        background: #f1f5f9;
        color: #0f172a;
    }

    /* Decorative Circle */
    .deco-circle {
        position: absolute;
        right: -20px;
        top: -20px;
        width: 100px;
        height: 100px;
        border-radius: 50%;
        background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0));
        z-index: 0;
    }

    /* Welcome Banner Base */
    .welcome-banner {
        border-radius: 16px;
        color: #0f172a;
        padding: 20px 30px;
        position: relative;
        overflow: hidden;
    }
    .welcome-title {
        font-size: 1.4rem;
        font-weight: 700;
        margin-bottom: 5px;
        font-family: 'Outfit', sans-serif;
    }
    .welcome-subtitle {
        color: #475569;
        font-size: 0.95rem;
    }

    /* Premium Table Styling */
    .premium-table th {
        border-top: none;
        color: #64748b;
        font-size: 0.8rem;
        text-transform: uppercase;
        font-weight: 600;
        padding: 8px 12px;
    }
    .premium-table td {
        padding: 8px 12px;
        vertical-align: middle;
        color: #334155;
        border-top: 1px solid #f1f5f9;
    }

    /* --- Theme Specific Colors & Variants --- */
    
    /* Office Management Variants */
    .border-top-office { border-top: 4px solid #0ea5e9; }
    .border-top-urgent { border-top: 4px solid #ef4444; }
    .border-top-meeting { border-top: 4px solid #6366f1; }
    .border-top-upcoming { border-top: 4px solid #f59e0b; }
    .bg-gradient-office { background: linear-gradient(135deg, #0ea5e9, #0284c7); }
    .bg-gradient-urgent { background: linear-gradient(135deg, #ef4444, #b91c1c); }
    .welcome-banner.theme-office {
        background: linear-gradient(135deg, #f0f9ff 0%, #f2f2f2 100%);
        border: 1px solid #bae6fd;
        box-shadow: 0 10px 30px rgba(14, 165, 233, 0.05);
    }
    .welcome-banner.theme-office::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -5%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(56,189,248,0.15) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }

    /* User Management Variants */
    .border-top-users { border-top: 4px solid #6366f1; }
    .border-top-active { border-top: 4px solid #10b981; }
    .border-top-roles { border-top: 4px solid #f59e0b; }
    .bg-gradient-users { background: linear-gradient(135deg, #6366f1, #4f46e5); }
    .bg-gradient-active { background: linear-gradient(135deg, #10b981, #059669); }
    .bg-gradient-roles { background: linear-gradient(135deg, #f59e0b, #d97706); }
    .welcome-banner.theme-users {
        background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);
        border: 1px solid #c7d2fe;
        box-shadow: 0 10px 30px rgba(99, 102, 241, 0.05);
    }
    .welcome-banner.theme-users::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -5%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(99,102,241,0.1) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }

    /* Branch Management Variants */
    .border-top-branches { border-top: 4px solid #ec4899; }
    .bg-gradient-branches { background: linear-gradient(135deg, #ec4899, #be185d); }
    .welcome-banner.theme-branches {
        background: linear-gradient(135deg, #fdf2f8 0%, #fce7f3 100%);
        border: 1px solid #fbcfe8;
        box-shadow: 0 10px 30px rgba(236, 72, 153, 0.05);
    }
    .welcome-banner.theme-branches::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -5%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(236,72,153,0.1) 0%, rgba(255,255,255,0) 70%);
        border-radius: 50%;
    }
</style>
