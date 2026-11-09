<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&display=swap" rel="stylesheet">
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
            font-family: 'Rajdhani', sans-serif;
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
            background: #0d2240;
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
        .print-btn:hover { background: #07152b; }
        .back-btn { background: #6c757d; }
        .back-btn:hover { background: #545b62; }

        .bulk-wrapper {
            display: flex;
            flex-direction: column;
            gap: 50px;
            width: 100%;
            align-items: center;
        }

        /* Each employee set (front+back side by side) */
        .card-set {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 16px;
            padding: 50px 30px 30px 30px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            border: 1px dashed #cbd5e1;
            position: relative;
        }

        .card-set-inner {
            display: flex;
            flex-direction: row;
            gap: 30px;
            align-items: flex-start;
        }

        .card-index-badge {
            position: absolute;
            top: -12px;
            left: 20px;
            background: #0d2240;
            color: #d4af37;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 12px;
            border-radius: 20px;
            box-shadow: 0 2px 5px rgba(13,34,64,0.4);
            z-index: 10;
        }

        .card-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
        }

        .card-label {
            font-size: 12px;
            font-weight: 600;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* CR80 Portrait: 54mm × 86mm */
        .card {
            width: 54mm;
            height: 86mm;
            border-radius: 3mm;
            overflow: hidden;
            position: relative;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
            flex-shrink: 0;
        }

        /* ===== FRONT CARD ===== */
        .card-front {
            background: linear-gradient(170deg, #0d2240 0%, #1a3c6e 55%, #0d2240 100%);
            color: #fff;
            display: flex;
            flex-direction: column;
        }

        .front-header {
            background: linear-gradient(90deg, #0d2240 0%, #1e4080 100%);
            padding: 3mm 3mm 2mm 3mm;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 0.5mm solid #d4af37;
        }
        .front-logo-icon {
            font-size: 6mm;
            color: #d4af37;
        }
        .front-company-text { text-align: right; }
        .front-company-name {
            font-size: 3mm;
            font-weight: 800;
            color: #fff;
            letter-spacing: 0.5px;
            line-height: 1.1;
        }
        .front-company-sub {
            font-size: 1.8mm;
            color: #aac4e8;
            font-weight: 500;
        }

        .card-type-strip {
            background: #d4af37;
            text-align: center;
            padding: 1mm 0;
            font-size: 2.5mm;
            font-weight: 700;
            color: #0d2240;
            letter-spacing: 1px;
        }

        .photo-section {
            display: flex;
            justify-content: center;
            padding: 2mm 3mm 1.5mm;
        }
        .photo-box {
            width: 26mm;
            height: 30mm;
            border: 0.8mm solid #d4af37;
            border-radius: 2mm;
            overflow: hidden;
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.4);
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }
        .photo-box .no-photo {
            font-size: 2.5mm;
            color: rgba(255,255,255,0.4);
            text-align: center;
        }

        .info-section {
            padding: 0 3mm;
            text-align: center;
        }
        .emp-name {
            font-size: 4mm;
            font-weight: 700;
            color: #fff;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .emp-designation {
            font-size: 2.5mm;
            font-weight: 600;
            color: #d4af37;
            margin-top: 0.5mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .emp-department {
            font-size: 2mm;
            color: rgba(255,255,255,0.6);
            margin-top: 0.3mm;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .front-divider {
            height: 0.3mm;
            background: rgba(212,175,55,0.4);
            margin: 1.5mm 4mm;
        }

        .front-bottom {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 2mm 4mm 2.5mm;
            background: rgba(0,0,0,0.25);
        }
        .bottom-left-info { flex: 1; }
        .id-label { font-size: 1.8mm; color: rgba(255,255,255,0.5); }
        .id-value { font-size: 3mm; font-weight: 700; color: #fff; }

        .chip {
            width: 8mm;
            height: 6mm;
            background: linear-gradient(135deg, #d4af37 0%, #aa7c11 100%);
            border-radius: 1mm;
            position: relative;
            box-shadow: inset 0 0 2px rgba(255,255,255,0.5), 0 2px 4px rgba(0,0,0,0.3);
            margin-bottom: 1mm;
        }
        .chip::after {
            content: '';
            position: absolute;
            inset: 1mm;
            border: 0.3mm solid rgba(0,0,0,0.2);
            border-radius: 0.5mm;
            background: linear-gradient(135deg, #e8c96b 0%, #b8860b 100%);
        }

        .card-no-area {
            font-family: 'Rajdhani', monospace;
            font-size: 2.8mm;
            font-weight: 700;
            color: #d4af37;
            letter-spacing: 1px;
            margin-top: 0.5mm;
        }

        .qr-box {
            background: #fff;
            padding: 1mm;
            border-radius: 1mm;
            box-shadow: 0 2px 5px rgba(0,0,0,0.3);
        }
        .qr-box img { width: 11mm; height: 11mm; display: block; }

        /* ===== BACK CARD ===== */
        .card-back {
            background: #f3eed6;
            color: #0d2240;
            display: flex;
            flex-direction: column;
        }

        .back-header {
            background: linear-gradient(90deg, #0d2240, #1a3c6e);
            padding: 2.5mm 3mm;
            text-align: center;
        }
        .back-header-title {
            font-size: 3mm;
            font-weight: 700;
            color: #d4af37;
        }
        .back-header-sub {
            font-size: 1.8mm;
            color: rgba(255,255,255,0.6);
        }

        .benefits-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5mm;
            padding: 2mm 3mm;
        }
        .benefit-item {
            background: #0d2240;
            border-radius: 1mm;
            display: flex;
            align-items: center;
            gap: 1.5mm;
            padding: 1.5mm 2mm;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .benefit-item i {
            font-size: 3.2mm;
            color: #d4af37;
            width: 4mm;
            text-align: center;
            flex-shrink: 0;
        }
        .benefit-text {
            font-size: 2mm;
            color: #fff;
            font-weight: 600;
            line-height: 1.1;
        }

        .back-divider {
            height: 0.3mm;
            background: rgba(13,34,64,0.25);
            margin: 0 3mm;
        }

        .contact-section {
            padding: 2mm 4mm;
            display: flex;
            flex-direction: column;
            gap: 1.5mm;
        }
        .contact-row {
            display: flex;
            align-items: center;
            gap: 2mm;
            font-size: 2.3mm;
            color: #0d2240;
            font-weight: 600;
        }
        .contact-row i {
            font-size: 3mm;
            width: 4mm;
            text-align: center;
            color: #1a3c6e;
        }

        .signature-section {
            display: flex;
            justify-content: space-around;
            padding: 1mm 4mm 2mm;
        }
        .sig-item { display: flex; flex-direction: column; align-items: center; }
        .sig-line {
            width: 18mm;
            border-bottom: 0.4mm solid #0d2240;
            height: 6mm;
        }
        .sig-label {
            font-size: 1.8mm;
            color: #666;
            margin-top: 0.5mm;
        }

        .bottom-slogan {
            background: #0d2240;
            color: #d4af37;
            text-align: center;
            padding: 1.5mm 2mm;
            font-size: 2.2mm;
            font-weight: 600;
            margin-top: auto;
            letter-spacing: 0.3px;
        }

        /* ===== PRINT STYLES ===== */
        @media print {
            @page {
                size: 54mm 86mm;
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

            .bulk-wrapper { display: block; gap: 0; }

            .card-set {
                display: block;
                box-shadow: none;
                border: none;
                background: transparent;
                margin: 0;
                padding: 0;
            }

            .card-set-inner { display: block; }
            .card-side { display: block; }

            .card {
                width: 54mm;
                height: 86mm;
                border-radius: 0;
                box-shadow: none;
                page-break-after: always;
                break-after: page;
                overflow: hidden;
            }

            .card:last-child {
                page-break-after: avoid;
                break-after: avoid;
            }
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
            <button class="print-btn" onclick="window.print()">🖨️ Print Both Sides (Duplex)</button>
        </div>
        <div style="width:1px;background:#ccc;margin:0 8px;"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Single-Side Printer</span>
            <div style="display:flex;gap:8px;">
                <button class="print-btn" style="background:#1a6e3c;" onclick="printFrontOnly()">🃏 Front Side</button>
                <button class="print-btn" style="background:#6e1a1a;" onclick="printBackOnly()">🔄 Back Side</button>
            </div>
        </div>
        <div style="width:1px;background:#ccc;margin:0 8px;"></div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <span style="font-size:11px;color:#666;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Back</span>
            <button class="print-btn back-btn" onclick="window.close()">← Go Back</button>
        </div>
    </div>

    <div class="bulk-wrapper">
        @foreach($cards as $index => $card)
            @php
                $employee = $card->cardable;
                $photoUrl = ($employee && $employee->photo) ? asset($employee->photo) : null;
                $qrContent = route('card-management.employee.public_verify', ['card_number' => $card->card_number]);
                $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=' . urlencode($qrContent);
                $cardNoStr = str_replace('-', '', $card->card_number);
                $formatted_card_no = trim(chunk_split($cardNoStr, 4, ' '));
                $sisterConcern = $employee->sisterConcern ?? $card->sisterConcern;
            @endphp

            <div class="card-set">
                <div class="card-index-badge">Employee #{{ $index + 1 }}</div>

                <div class="card-set-inner">
                    {{-- FRONT --}}
                    <div class="card-side">
                        <div class="card-label">Front Side</div>
                        <div class="card card-front card-employee">
                            <div class="front-header">
                                <div class="front-logo-icon"><i class="fas fa-building"></i></div>
                                <div class="front-company-text">
                                    <div class="front-company-name">TORI INTERNATIONAL</div>
                                    <div class="front-company-sub">Your Trusted Employer</div>
                                </div>
                            </div>

                            <div class="card-type-strip">EMPLOYEE ID CARD</div>

                            <div class="photo-section">
                                <div class="photo-box">
                                    @if($photoUrl)
                                        <img src="{{ $photoUrl }}" alt="Photo">
                                    @else
                                        <div class="no-photo"><i class="fas fa-user" style="font-size:10mm;color:rgba(255,255,255,0.3)"></i></div>
                                    @endif
                                </div>
                            </div>

                            <div class="info-section">
                                <div class="emp-name">{{ $employee ? $employee->name : 'N/A' }}</div>
                                <div class="emp-designation">{{ $employee->designation->name ?? '' }}</div>
                                <div class="emp-department">{{ $employee->department->name ?? '' }}</div>
                            </div>

                            <div class="front-divider"></div>

                            <div class="front-bottom">
                                <div class="bottom-left-info">
                                    <div class="chip"></div>
                                    <div class="id-label">Employee ID</div>
                                    <div class="id-value">{{ $employee->id_no ?? 'N/A' }}</div>
                                    <div class="card-no-area">{{ $formatted_card_no }}</div>
                                </div>
                                <div class="qr-box">
                                    <img src="{{ $qrUrl }}" alt="QR">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- BACK --}}
                    <div class="card-side">
                        <div class="card-label">Back Side</div>
                        <div class="card card-back card-employee" style="{{ $loop->last ? 'page-break-after: avoid; break-after: avoid;' : '' }}">
                            <div class="back-header">
                                <div class="back-header-title">Employee Benefits & Services</div>
                                <div class="back-header-sub">TORI INTERNATIONAL</div>
                            </div>

                            <div class="benefits-grid">
                                @php
                                    $benefits = [
                                        ['icon' => 'fas fa-money-bill-wave', 'label' => 'Monthly Salary'],
                                        ['icon' => 'fas fa-calendar-alt',   'label' => 'Paid Leave'],
                                        ['icon' => 'fas fa-heartbeat',      'label' => 'Health Cover'],
                                        ['icon' => 'fas fa-shield-alt',     'label' => 'Insurance'],
                                        ['icon' => 'fas fa-graduation-cap', 'label' => 'Training'],
                                        ['icon' => 'fas fa-trophy',         'label' => 'Bonus'],
                                        ['icon' => 'fas fa-utensils',       'label' => 'Lunch Allowance'],
                                        ['icon' => 'fas fa-bus',            'label' => 'Transport'],
                                    ];
                                @endphp
                                @foreach($benefits as $b)
                                    <div class="benefit-item">
                                        <i class="{{ $b['icon'] }}"></i>
                                        <span class="benefit-text">{{ $b['label'] }}</span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="back-divider"></div>

                            <div class="contact-section">
                                <div class="contact-row">
                                    <i class="fas fa-headset"></i>
                                    <span>HR: {{ $sisterConcern ? $sisterConcern->contact : '09612 123456' }}</span>
                                </div>
                                <div class="contact-row">
                                    <i class="fas fa-globe"></i>
                                    <span>www.toriinternational.com</span>
                                </div>
                                <div class="contact-row">
                                    <i class="fab fa-facebook-f"></i>
                                    <span>/toriinternational</span>
                                </div>
                            </div>

                            <div class="back-divider"></div>

                            <div class="signature-section">
                                <div class="sig-item">
                                    <div class="sig-line"></div>
                                    <div class="sig-label">Signature</div>
                                </div>
                                <div class="sig-item">
                                    <div class="sig-line"></div>
                                    <div class="sig-label">Authority</div>
                                </div>
                            </div>

                            <div class="bottom-slogan">Together We Work, Together We Grow</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

</body>
</html>
