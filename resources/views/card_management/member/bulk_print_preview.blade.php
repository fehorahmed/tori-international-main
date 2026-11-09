<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;600;700&family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        body {
            background: #e8e8e8;
            font-family: 'Noto Sans Bengali', 'Rajdhani', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            gap: 30px;
            padding: 40px 20px;
        }

        .print-btn-wrapper {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
            position: sticky;
            top: 20px;
            z-index: 1000;
            background: rgba(232,232,232,0.95);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }
        .print-btn {
            padding: 10px 28px;
            background: #122135;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 15px;
            font-family: inherit;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }
        .print-btn:hover { background: #0b1522; }
        .back-btn { background: #6c757d; }
        .back-btn:hover { background: #545b62; }

        .bulk-wrapper {
            display: flex;
            flex-direction: column;
            gap: 40px;
            width: 100%;
            align-items: center;
        }

        .card-wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 20px;
            padding: 40px 30px 30px 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px dashed #cbd5e1;
            position: relative;
        }

        .card-index-badge {
            position: absolute;
            top: -12px;
            left: 20px;
            background: #122135;
            color: #d4af37;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(18,33,53,0.4);
            z-index: 10;
        }

        .card-label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .card {
            width: 85.6mm;
            height: 53.98mm;
            border-radius: 4mm;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.25);
        }

        /* FRONT CARD */
        .card-front {
            background: linear-gradient(110deg, #122135 0%, #1e3752 40%, #eab85a 85%, #f6d365 100%);
            color: #fff;
        }

        .mountain-bg {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 65%;
            z-index: 1;
        }

        .sun-glow {
            position: absolute;
            top: 25%;
            left: 55%;
            width: 30mm;
            height: 30mm;
            background: radial-gradient(circle, rgba(255,215,0,0.4) 0%, rgba(255,140,0,0) 70%);
            z-index: 0;
        }

        .front-content {
            position: relative;
            z-index: 2;
            width: 100%;
            height: 100%;
        }

        .front-top {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 4mm 5mm 0 5mm;
        }

        .logo-area {
            display: flex;
            align-items: center;
            gap: 2mm;
        }
        .logo-circle {
            color: #fca311;
            font-size: 7mm;
            margin-top: -2mm;
        }
        .logo-title {
            font-size: 3.5mm;
            font-weight: 800;
            color: #fff;
            line-height: 1;
            letter-spacing: 0.5px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.6);
        }
        .logo-tagline {
            font-size: 2.2mm;
            color: #e2e8f0;
            font-weight: 500;
            margin-top: 1px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }

        .card-title-area {
            text-align: right;
            margin-top: 1mm;
        }
        .title-en {
            font-size: 2.5mm;
            font-weight: 700;
            color: #ffd700;
            line-height: 1.1;
        }

        .chip-container {
            margin-top: 3mm;
            margin-left: 5mm;
        }
        .chip {
            width: 9mm;
            height: 6.5mm;
            background: linear-gradient(135deg, #d4af37 0%, #aa7c11 100%);
            border-radius: 1mm;
            position: relative;
            box-shadow: inset 0 0 2px rgba(255,255,255,0.5), 0 2px 4px rgba(0,0,0,0.3);
        }
        .chip::after {
            content: '';
            position: absolute;
            inset: 1mm;
            border: 0.3mm solid rgba(0,0,0,0.2);
            border-radius: 0.5mm;
            background: linear-gradient(135deg, #e8c96b 0%, #b8860b 100%);
        }

        .card-number-area {
            margin-left: 5mm;
            margin-top: 2mm;
            font-family: 'Rajdhani', monospace;
            font-size: 4.8mm;
            font-weight: 700;
            letter-spacing: 1.5px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.8);
        }

        .front-bottom-area {
            position: absolute;
            bottom: 4mm;
            left: 5mm;
            right: 4mm;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            z-index: 2;
        }

        .bottom-left {
            flex: 1;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        .field-label {
            font-size: 2mm;
            color: #cbd5e1;
        }
        .field-value-name {
            font-size: 3.8mm;
            font-weight: 700;
            margin-bottom: 1.5mm;
        }
        .field-value-no {
            font-size: 3mm;
            font-weight: 600;
        }

        .bottom-middle-qr {
            background: #fff;
            padding: 1mm;
            border-radius: 1mm;
            margin-right: 3mm;
            margin-bottom: 2mm;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .bottom-middle-qr img {
            width: 10mm;
            height: 10mm;
            display: block;
        }

        .bottom-right-photo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 1.5mm;
        }
        .photo-box {
            width: 21mm;
            height: 25mm;
            border: 0.6mm solid #fca311;
            border-radius: 1.5mm;
            overflow: hidden;
            background: #e2e8f0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.4);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .photo-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .photo-box .no-photo {
            font-size: 2.5mm;
            color: #64748b;
        }
        .mini-logo-bottom {
            font-size: 2.2mm;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 1mm;
            font-weight: 600;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.8);
        }
        .mini-logo-bottom i { color: #10b981; }

        /* BACK CARD */
        .card-back {
            background: #f3eed6;
            color: #122135;
            display: flex;
            flex-direction: column;
        }

        .back-title {
            text-align: center;
            font-size: 3.5mm;
            font-weight: 700;
            color: #122135;
            margin-top: 3mm;
        }

        .benefits-grid {
            display: flex;
            justify-content: center;
            gap: 1.5mm;
            padding: 0 3mm;
            margin-top: 2.5mm;
        }
        .benefit-item {
            width: 9.5mm;
            height: 12mm;
            background: #122135;
            border-radius: 1mm;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 1mm;
            padding: 0.5mm;
            box-shadow: 0 2px 4px rgba(0,0,0,0.2);
        }
        .benefit-icon-box i {
            color: #d4af37;
            font-size: 3.5mm;
        }
        .benefit-text {
            color: #fff;
            font-size: 1.7mm;
            text-align: center;
            line-height: 1.1;
            font-weight: 600;
        }

        .and-more {
            text-align: right;
            font-size: 2.2mm;
            color: #122135;
            margin-right: 4mm;
            margin-top: 1mm;
            font-weight: 600;
        }

        .back-divider {
            height: 0.3mm;
            background: rgba(18,33,53,0.3);
            margin: 2mm 3mm;
        }

        .back-footer-content {
            display: flex;
            justify-content: space-between;
            padding: 0 5mm;
            flex: 1;
            align-items: flex-start;
        }

        .footer-left {
            display: flex;
            flex-direction: column;
            gap: 1.5mm;
        }
        .contact-row {
            display: flex;
            align-items: center;
            gap: 2mm;
            font-size: 2.4mm;
            color: #122135;
            font-weight: 600;
        }
        .contact-row i {
            font-size: 3.2mm;
            width: 4mm;
            text-align: center;
        }
        .contact-col {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }
        .contact-label { font-size: 2.2mm; font-weight: 700; }

        .footer-right {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 2mm;
        }
        .signature-label {
            font-size: 2.5mm;
            font-weight: 600;
            color: #122135;
        }
        .signature-line {
            width: 25mm;
            border-bottom: 0.4mm solid #122135;
            height: 8mm;
            display: flex;
            justify-content: center;
            align-items: flex-end;
        }

        .bottom-slogan {
            background: #122135;
            color: #d4af37;
            text-align: center;
            padding: 1.5mm 0;
            font-size: 2.6mm;
            font-weight: 600;
            margin-top: auto;
            letter-spacing: 0.5px;
        }

        @media print {
            @page {
                size: 85.6mm 53.98mm;
                margin: 0;
            }

            body {
                background: #fff;
                padding: 0;
                margin: 0;
                display: block;
            }

            .print-btn-wrapper,
            .card-label,
            .card-index-badge {
                display: none !important;
            }

            .bulk-wrapper {
                display: block;
                gap: 0;
            }

            .card-wrapper {
                display: block;
                box-shadow: none;
                border: none;
                background: transparent;
                margin: 0;
                padding: 0;
            }

            .card {
                width: 85.6mm;
                height: 53.98mm;
                border-radius: 0;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
                overflow: hidden;
            }

            .card-front { height: 53.98mm; }
            .card-back  { height: 53.98mm; }
        }

        body.print-front-only .card-back { display: none !important; }
        body.print-front-only .card-front {
            page-break-after: avoid !important;
            break-after: avoid !important;
        }

        body.print-back-only .card-front { display: none !important; }
        body.print-back-only .card-back {
            page-break-after: avoid !important;
            break-after: avoid !important;
        }
    </style>

    <script>
        function printFrontOnly() {
            document.body.classList.add('print-front-only');
            window.print();
            document.body.classList.remove('print-front-only');
        }

        function printBackOnly() {
            document.body.classList.add('print-back-only');
            window.print();
            document.body.classList.remove('print-back-only');
        }
    </script>
</head>
<body>

    <div class="print-btn-wrapper">
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Duplex Printer</span>
            <button class="print-btn" onclick="window.print()">
                🖨️ Print Both Sides (Duplex)
            </button>
        </div>

        <div style="width:1px;background:#ccc;margin:0 8px;"></div>

        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Single-Side Printer</span>
            <div style="display:flex;gap:8px;">
                <button class="print-btn" style="background:#1a6e3c;" onclick="printFrontOnly()">
                    🃏 Front Side
                </button>
                <button class="print-btn" style="background:#6e1a1a;" onclick="printBackOnly()">
                    🔄 Back Side
                </button>
            </div>
        </div>

        <div style="width:1px;background:#ccc;margin:0 8px;"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Back</span>
            <button class="print-btn back-btn" onclick="window.close()">
                ← Go Back
            </button>
        </div>
    </div>

    <div class="bulk-wrapper">
        @foreach($cards as $index => $card)
            @php
                $supplier = $card->cardable;
                $member   = $supplier ? $supplier->mfMember : null;

                // Resolve Photo
                $photoUrl = ($supplier && $supplier->image) ? asset($supplier->image) : null;

                // Resolve QR Code Content
                $qrContent = route('card-management.member.public_verify', ['card_number' => $card->card_number]);
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=' . urlencode($qrContent);

                // Format card number
                $cardNoStr = str_replace('-', '', $card->card_number);
                $formatted_card_no = trim(chunk_split($cardNoStr, 4, ' '));
            @endphp

            <div class="card-wrapper">
                <div class="card-index-badge">Card #{{ $index + 1 }}</div>

                <div class="card-label">Front Side</div>
                <div class="card card-front card-member">
                    <svg class="mountain-bg" viewBox="0 0 340 120" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                        <polygon points="0,120 60,60 120,80 180,20 240,60 300,30 340,50 340,120" fill="#122234"/>
                        <polygon points="0,120 80,70 140,85 200,45 260,75 320,40 340,65 340,120" fill="#0b1522"/>
                    </svg>
                    <div class="sun-glow"></div>

                    <div class="front-content">
                        <div class="front-top">
                            <div class="logo-area">
                                <div class="logo-circle"><i class="fas fa-sun"></i></div>
                                <div class="logo-text-block">
                                    <div class="logo-title">TORI INTERNATIONAL</div>
                                    <div class="logo-tagline">Your Partner in Development</div>
                                </div>
                            </div>
                            <div class="card-title-area">
                                <div class="title-en">MEMBER CARD</div>
                            </div>
                        </div>

                        <div class="chip-container">
                            <div class="chip"></div>
                        </div>
                        <div class="card-number-area">{{ $formatted_card_no }}</div>

                        <div class="front-bottom-area">
                            <div class="bottom-left">
                                <div class="field-label">Member Name</div>
                                <div class="field-value-name">{{ $supplier ? $supplier->name : 'N/A' }}</div>
                                <div class="field-label">Member No.</div>
                                <div class="field-value-no">{{ $supplier ? $supplier->code : 'N/A' }}</div>
                            </div>

                            <div class="bottom-middle-qr">
                                <img src="{{ $qrUrl }}" alt="QR">
                            </div>

                            <div class="bottom-right-photo">
                                <div class="photo-box">
                                    @if($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="Photo">
                                    @else
                                        <div class="no-photo">No Image</div>
                                    @endif
                                </div>
                                <div class="mini-logo-bottom">
                                    <i class="fas fa-users"></i> TORI INTERNATIONAL
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-label" style="margin-top:20px;">Back Side</div>
                <div class="card card-back card-member" style="{{ $loop->last ? 'page-break-after: avoid; break-after: avoid;' : '' }}">
                    <div class="back-title">Best Benefits for Members</div>

                    <div class="benefits-grid">
                        @php
                            $benefits = [
                                ['icon' => 'fas fa-piggy-bank',       'label' => "Savings"],
                                ['icon' => 'fas fa-hand-holding-usd', 'label' => "Loan"],
                                ['icon' => 'fas fa-shopping-cart',    'label' => "Shopping"],
                                ['icon' => 'fas fa-couch',            'label' => "Furniture"],
                                ['icon' => 'fas fa-car',              'label' => "Auto"],
                                ['icon' => 'fas fa-car-battery',      'label' => "Battery & IPS"],
                                ['icon' => 'fas fa-ring',             'label' => "Gold"],
                                ['icon' => 'fas fa-pump-soap',        'label' => "Cosmetics"],
                            ];
                        @endphp
                        @foreach($benefits as $b)
                            <div class="benefit-item">
                                <div class="benefit-icon-box"><i class="{{ $b['icon'] }}"></i></div>
                                <div class="benefit-text">{{ $b['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    <div class="and-more">And much more...</div>

                    <div class="back-divider"></div>

                    <div class="back-footer-content">
                        <div class="footer-left">
                            <div class="contact-row">
                                <i class="fas fa-headset"></i>
                                <div class="contact-col">
                                    <span class="contact-label">Helpline</span>
                                    <span class="contact-val">{{ $card->sisterConcern ? $card->sisterConcern->contact : '09612 123456' }}</span>
                                </div>
                            </div>
                            <div class="contact-row">
                                <i class="fas fa-globe"></i>
                                <span class="contact-val">www.toriinternational.com</span>
                            </div>
                            <div class="contact-row">
                                <i class="fab fa-facebook-f"></i>
                                <span class="contact-val">/toriinternational</span>
                            </div>
                        </div>
                        <div class="footer-right">
                            <div class="signature-label">Signature</div>
                            <div class="signature-line"></div>
                        </div>
                    </div>

                    <div class="bottom-slogan">
                        Together We Save, Together We Grow
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
