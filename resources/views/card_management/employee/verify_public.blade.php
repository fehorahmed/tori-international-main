<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Card Verification - Tori International</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Noto+Sans+Bengali:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #1a3c6e;
            --primary-dark: #0f2a52;
            --accent: #ffd700;
            --success: #10b981;
            --success-glow: rgba(16, 185, 129, 0.2);
            --card-bg: rgba(255, 255, 255, 0.9);
            --text-dark: #0f172a;
            --text-muted: #475569;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Outfit', 'Noto Sans Bengali', sans-serif;
            background: radial-gradient(circle at 10% 20%, rgb(240, 244, 250) 0%, rgb(215, 226, 240) 90.1%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
            position: relative;
        }

        .blob {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(26, 60, 110, 0.15), rgba(15, 42, 82, 0.15));
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
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: var(--accent);
            box-shadow: 0 10px 25px rgba(26, 60, 110, 0.3);
            border: 2px solid var(--accent);
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
            background-color: rgba(26, 60, 110, 0.12);
            color: #1a3c6e;
            border: 1px solid rgba(26, 60, 110, 0.2);
        }

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
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 25px;
        }

        .details-list {
            text-align: left;
            background: rgba(255, 255, 255, 0.7);
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
        <div class="brand-header">
            <div class="brand-logo">Tori International</div>
            <div class="brand-tagline">Card Verification Portal</div>
        </div>

        @php
            $isExpired = $card->created_at->addYears(2)->isPast();
            $displayStatus = ($card->status === 'Active' && $isExpired) ? 'Expired' : $card->status;
            $isValid = ($displayStatus === 'Active');
            $statusColor = $isValid ? '#1a3c6e' : '#dc2626';
        @endphp

        <div class="status-seal" style="background: transparent; border: none; box-shadow: none;">
            <img src="{{ asset('img/logo.png') }}" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
        </div>

        <div class="badge-status success">
            Verified Employee Card
        </div>

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
            {{ $role }} (Officer / Staff)
        </div>

        <div class="details-list">
            <div class="detail-row">
                <span class="detail-label">Card No</span>
                <span class="detail-value text-primary" style="font-family: monospace;">{{ $card->card_number }}</span>
            </div>
            
            <div class="detail-row">
                <span class="detail-label">Department</span>
                <span class="detail-value">{{ $department }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Mobile</span>
                <span class="detail-value">{{ $card->cardable ? $card->cardable->mobile_no : 'N/A' }}</span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Blood Group</span>
                <span class="detail-value" style="color: var(--primary); font-weight: 700;">
                    {{ ($card->cardable && $card->cardable->blood_group) ? $card->cardable->blood_group : 'N/A' }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Date of Birth</span>
                <span class="detail-value">
                    {{ ($card->cardable && $card->cardable->date_of_birth) ? \Carbon\Carbon::parse($card->cardable->date_of_birth)->format('d-M-Y') : 'N/A' }}
                </span>
            </div>

            <div class="detail-row">
                <span class="detail-label">Issue Date</span>
                <span class="detail-value">{{ $card->created_at->format('d-M-Y') }}</span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Incentive Balance</span>
                <span class="detail-value" style="color: var(--success); font-weight: 800;">
                    ৳ {{ number_format($incentiveBalance, 2) }}
                </span>
            </div>
            <div class="detail-row">
                <span class="detail-label">Card Status</span>
                <span class="detail-value" style="color: {{ $statusColor }}; font-weight: 800;">
                    {{ $displayStatus }}
                </span>
            </div>
        </div>

        <div style="margin-top: 10px; margin-bottom: 15px;">
            <a href="{{ route('employee_profile.profile.index') }}" style="display: inline-block; padding: 10px 25px; background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; text-decoration: none; border-radius: 50px; font-weight: 600; font-size: 14px; letter-spacing: 0.5px; box-shadow: 0 4px 15px rgba(26, 60, 110, 0.2); transition: all 0.3s ease;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(26, 60, 110, 0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 15px rgba(26, 60, 110, 0.2)'">
                <i class="fas fa-user-circle" style="margin-right: 5px;"></i> Employee Profile
            </a>
        </div>

        <div class="social-links" style="margin-top: 20px; margin-bottom: 15px; display: flex; justify-content: center; gap: 20px;">
            <a href="https://toriinternational.com" target="_blank" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.08); color: #0d2240; border-radius: 50%; text-decoration: none; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 15px rgba(13,34,64,0.2)'; this.style.background='#0d2240'; this.style.color='#fff'; this.style.borderColor='#0d2240';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)'; this.style.background='#f1f5f9'; this.style.color='#0d2240'; this.style.borderColor='rgba(0,0,0,0.08)';"><i class="fas fa-globe"></i></a>
            <a href="https://www.facebook.com/share/1E4C6C6yJg" target="_blank" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.08); color: #1877F2; border-radius: 50%; text-decoration: none; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 15px rgba(24,119,242,0.3)'; this.style.background='#1877F2'; this.style.color='#fff'; this.style.borderColor='#1877F2';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)'; this.style.background='#f1f5f9'; this.style.color='#1877F2'; this.style.borderColor='rgba(0,0,0,0.08)';"><i class="fab fa-facebook-f"></i></a>
            <a href="https://www.youtube.com/channel/UCBTXcsyC15eynn5l398yWrA" target="_blank" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.08); color: #FF0000; border-radius: 50%; text-decoration: none; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 15px rgba(255,0,0,0.3)'; this.style.background='#FF0000'; this.style.color='#fff'; this.style.borderColor='#FF0000';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)'; this.style.background='#f1f5f9'; this.style.color='#FF0000'; this.style.borderColor='rgba(0,0,0,0.08)';"><i class="fab fa-youtube"></i></a>
        </div>

        <div class="footer-note">
            © {{ date('Y') }} <a href="https://toriinternational.com" target="_blank">Tori International</a>. All rights reserved.
        </div>
    </div>

</body>
</html>
