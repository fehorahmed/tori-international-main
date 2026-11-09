<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Card Verification - Tori International</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4f46e5;
            --primary-dark: #312e81;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --danger: #ef4444;
            --danger-glow: rgba(239, 68, 68, 0.2);
            --card-bg: rgba(255, 255, 255, 0.85);
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(242, 245, 251) 0%, rgb(224, 231, 255) 90.1%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        /* Abstract decorative blobs */
        .blob {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(79, 70, 229, 0.15), rgba(124, 58, 237, 0.15));
            filter: blur(50px);
            z-index: -1;
            animation: float 10s ease-in-out infinite alternate;
        }
        .blob-1 { top: -50px; right: -50px; }
        .blob-2 { bottom: -50px; left: -50px; animation-delay: -5s; }

        @keyframes float {
            0% { transform: translateY(0) scale(1); }
            100% { transform: translateY(20px) scale(1.1); }
        }

        .verification-container {
            width: 100%;
            max-width: 460px;
            background: var(--card-bg);
            border: 1px solid rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 28px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Header Logo */
        .brand-header {
            margin-bottom: 25px;
        }
        .brand-logo {
            font-size: 22px;
            font-weight: 800;
            letter-spacing: 1.5px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-transform: uppercase;
        }
        .brand-tagline {
            font-size: 11px;
            color: var(--text-muted);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 2px;
            margin-top: 4px;
        }

        /* Success & Error State Visuals */
        .status-seal {
            position: relative;
            width: 90px;
            height: 90px;
            border-radius: 50%;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) 0.2s forwards;
            transform: scale(0);
        }

        .status-seal.success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            box-shadow: 0 10px 25px var(--success-glow);
        }

        .status-seal.danger {
            background: linear-gradient(135deg, var(--danger), #dc2626);
            color: white;
            box-shadow: 0 10px 25px var(--danger-glow);
        }

        @keyframes popIn {
            to { transform: scale(1); }
        }

        .badge-status {
            display: inline-block;
            padding: 6px 16px;
            border-radius: 50px;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }
        .badge-status.success {
            background-color: rgba(16, 185, 129, 0.12);
            color: #047857;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }
        .badge-status.danger {
            background-color: rgba(239, 68, 68, 0.12);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        /* Profile details container */
        .avatar-wrapper {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 8px 16px rgba(0,0,0,0.06);
            margin: 0 auto 15px auto;
            overflow: hidden;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .avatar-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .avatar-wrapper i {
            font-size: 45px;
            color: var(--text-muted);
        }

        .holder-name {
            font-size: 20px;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 4px;
        }

        .holder-role {
            font-size: 13px;
            color: var(--primary);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        /* Detail List Grid */
        .details-list {
            text-align: left;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 20px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.4);
            margin-bottom: 25px;
        }

        .detail-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.06);
        }
        .detail-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }
        .detail-row:first-child {
            padding-top: 0;
        }

        .detail-label {
            font-size: 13px;
            color: var(--text-muted);
            font-weight: 500;
        }

        .detail-value {
            font-size: 14px;
            color: var(--text-dark);
            font-weight: 600;
        }

        /* Footer styling */
        .footer-note {
            font-size: 12px;
            color: var(--text-muted);
            margin-top: 25px;
            border-top: 1px solid rgba(0,0,0,0.06);
            padding-top: 15px;
        }

        .footer-note a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
    </style>
</head>
<body>

    <div class="blob blob-1"></div>
    <div class="blob blob-2"></div>

    <div class="verification-container">
        <!-- Logo -->
        <div class="brand-header">
            <div class="brand-logo">Tori International</div>
            <div class="brand-tagline">Card Verification Portal</div>
        </div>

        @if($success)
            @php
                $isExpired = $card->created_at->addYears(2)->isPast();
                $displayStatus = ($card->status === 'Active' && $isExpired) ? 'Expired' : $card->status;
                $isValid = ($displayStatus === 'Active');
                $statusColor = $isValid ? 'var(--success)' : 'var(--danger)';
            @endphp

            @if($isValid)
                <!-- Seal Icon -->
                <div class="status-seal success">
                    <i class="fas fa-circle-check"></i>
                </div>

                <div class="badge-status success">
                    Verified Card
                </div>
            @else
                <!-- Seal Icon for Inactive/Expired -->
                <div class="status-seal danger">
                    <i class="fas fa-circle-xmark"></i>
                </div>

                <div class="badge-status danger">
                    {{ $displayStatus }} Card
                </div>
            @endif

            <!-- Profile Info -->
            <div class="avatar-wrapper">
                @if($photoUrl)
                    <img src="{{ $photoUrl }}" alt="{{ $card->cardable ? $card->cardable->name : 'Photo' }}">
                @else
                    <i class="fas fa-user-tie"></i>
                @endif
            </div>

            <div class="holder-name">
                {{ $card->cardable ? $card->cardable->name : 'N/A' }}
            </div>
            <div class="holder-role">
                {{ $isCustomer ? 'Somiti Member' : $role }}
            </div>

            <!-- Details List -->
            <div class="details-list">
                <div class="detail-row">
                    <span class="detail-label">Card Number</span>
                    <span class="detail-value text-primary">{{ $card->card_number }}</span>
                </div>
                
                @if($isCustomer)
                    <div class="detail-row">
                        <span class="detail-label">Somiti Name</span>
                        <span class="detail-value">{{ $somitiName }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Member No</span>
                        <span class="detail-value">{{ $memberNo }}</span>
                    </div>
                @else
                    <div class="detail-row">
                        <span class="detail-label">Department</span>
                        <span class="detail-value">{{ $department }}</span>
                    </div>
                @endif

                <div class="detail-row">
                    <span class="detail-label">Mobile Number</span>
                    <span class="detail-value">{{ $card->cardable ? $card->cardable->mobile_no : 'N/A' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Sister Concern</span>
                    <span class="detail-value">{{ $card->sisterConcern ? $card->sisterConcern->name : 'Head Office' }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Issue Date</span>
                    <span class="detail-value">{{ $card->created_at->format('d-M-Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Card Status</span>
                    <span class="detail-value" style="color: {{ $statusColor }}; font-weight: 800;">
                        {{ $displayStatus }}
                    </span>
                </div>
            </div>
        @else
            <!-- Seal Icon for Error -->
            <div class="status-seal danger">
                <i class="fas fa-circle-xmark"></i>
            </div>

            <div class="badge-status danger">
                Invalid Card
            </div>

            <div class="holder-name" style="margin-bottom: 15px;">
                Verification Failed
            </div>
            
            <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 25px; line-height: 1.5;">
                {{ $message }}<br>
                Please contact Tori International support if you believe this is an error.
            </p>
        @endif

        <div class="footer-note">
            © {{ date('Y') }} <a href="https://toriinternational.com" target="_blank">Tori International</a>. All rights reserved.
        </div>
    </div>

</body>
</html>
