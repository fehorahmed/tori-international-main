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
            justify-content: center;
            min-height: 100vh;
            gap: 30px;
            padding: 30px;
        }

        .print-btn-wrapper {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            justify-content: center;
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

        .card-wrapper {
            display: flex;
            flex-direction: row;
            align-items: flex-start;
            gap: 30px;
        }

        .card-side {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }

        .card-label {
            font-size: 13px;
            font-weight: 600;
            color: #555;
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

        /* ===== FRONT CARD NEW DESIGN ===== */
        .card-front {
            background: #f3eed6;
            color: #333;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        .shape-left {
            position: absolute; top: 0; left: 0; width: 15mm; height: 35mm;
            background: #0d2240;
            clip-path: polygon(0 0, 100% 0, 100% 100%, 0 80%);
            z-index: 1;
        }
        .shape-right {
            position: absolute; top: 0; right: 0; width: 15mm; height: 35mm;
            background: #0d2240;
            clip-path: polygon(0 0, 100% 0, 100% 80%, 0 100%);
            z-index: 1;
        }
        .shape-center {
            position: absolute; top: 0; left: 10mm; right: 10mm; height: 42mm;
            background: #d4af37;
            clip-path: polygon(0 0, 100% 0, 100% 82%, 50% 100%, 0 82%);
            z-index: 2;
        }
        .shape-bottom-left {
            position: absolute; bottom: 0; left: 0; width: 12mm; height: 10mm;
            background: #0d2240;
            clip-path: polygon(0 100%, 0 50%, 100% 100%);
            z-index: 1;
        }
        .shape-bottom-right {
            position: absolute; bottom: 0; right: 0; width: 12mm; height: 10mm;
            background: #0d2240;
            clip-path: polygon(100% 100%, 100% 50%, 0 100%);
            z-index: 1;
        }

        .front-content {
            position: relative; z-index: 10; display: flex; flex-direction: column; align-items: center; height: 100%; padding: 3mm;
        }

        .front-logo {
            display: flex; align-items: center; justify-content: center; gap: 1.5mm; margin-top: 1mm; width: 100%;
        }
        .front-logo img {
            width: 8mm; height: 8mm; object-fit: contain; background: #fff; border-radius: 50%; padding: 0.5mm;
        }
        .front-logo-text {
            display: flex; flex-direction: column; text-align: left;
        }
        .front-logo-title {
            font-size: 2.5mm; font-weight: 800; color: #0d2240; line-height: 1.1; letter-spacing: 0.5px;
        }
        .front-logo-sub {
            font-size: 1.5mm; color: #fff; font-weight: 600;
        }

        .hex-photo-wrapper {
            margin-top: 4mm; width: 28mm; height: 32mm; background: #fff;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            display: flex; align-items: center; justify-content: center; padding: 1mm;
        }
        .hex-photo {
            width: 100%; height: 100%; background: #e2e8f0;
            clip-path: polygon(50% 0%, 100% 25%, 100% 75%, 50% 100%, 0% 75%, 0% 25%);
            display: flex; align-items: center; justify-content: center;
        }
        .hex-photo img { width: 100%; height: 100%; object-fit: cover; }

        .emp-name-new {
            font-size: 4mm; font-weight: 800; color: #0d2240; margin-top: 2.5mm; text-transform: uppercase; letter-spacing: 0.5px; text-align: center; width: 100%;
        }
        .emp-designation-new {
            background: #d4af37; color: #0d2240; font-size: 2mm; font-weight: 800; padding: 0.6mm 3mm; margin-top: 1mm; text-transform: uppercase; border-radius: 0.5mm; letter-spacing: 0.5px;
        }

        .info-list {
            width: 100%; margin-top: 4.5mm; padding: 0 4mm; display: flex; flex-direction: column; gap: 1.5mm;
        }
        .info-row { display: flex; font-size: 2.2mm; color: #333; line-height: 1.2; }
        .info-label { width: 14mm; font-weight: 800; color: #0d2240; }
        .info-separator { width: 2mm; font-weight: 800; color: #0d2240; text-align: center; }
        .info-value { flex: 1; font-weight: 600; color: #444; }



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
        .qr-box img {
            width: 11mm;
            height: 11mm;
            display: block;
        }

        /* ===== BACK CARD ===== */
        .card-back {
            background: #f3eed6;
            color: #0d2240;
            display: flex;
            flex-direction: column;
        }

        /* Back header */
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

        /* Benefits 2-column grid */
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

        /* Back divider */
        .back-divider {
            height: 0.3mm;
            background: rgba(13,34,64,0.25);
            margin: 0 3mm;
        }

        /* Contact info */
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

        /* Signature */
        .signature-section {
            display: flex;
            justify-content: space-around;
            padding: 1mm 4mm 2mm;
            margin-top: 10px;
        }
        .sig-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
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

        /* Bottom slogan */
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
            .card-label {
                display: none !important;
            }

            .card-wrapper {
                display: block;
            }

            .card-side {
                display: block;
            }

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

    @php
        $employee = $card->cardable;

        // Resolve Photo
        $photoUrl = ($employee && $employee->photo) ? asset($employee->photo) : null;

        // Resolve QR Code Content
        $qrContent = route('card-management.employee.public_verify', ['card_number' => $card->card_number]);
        $qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=110x110&data=' . urlencode($qrContent);

        // Format card number
        $cardNoStr = str_replace('-', '', $card->card_number);
        $formatted_card_no = trim(chunk_split($cardNoStr, 4, ' '));

        // Sister Concern
        $sisterConcern = $employee->sisterConcern ?? $card->sisterConcern;
    @endphp

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

    <div class="card-wrapper">

        {{-- FRONT SIDE --}}
        <div class="card-side">
            <div class="card-label">Front Side</div>
            <div class="card card-front card-employee">
                <div class="shape-left"></div>
                <div class="shape-right"></div>
                <div class="shape-center"></div>
                <div class="shape-bottom-left"></div>
                <div class="shape-bottom-right"></div>

                <div class="front-content">
                    <div class="front-logo">
                        <img src="{{ asset('img/logo.png') }}" alt="Logo">
                        <div class="front-logo-text">
                            <span class="front-logo-title">TORI INTERNATIONAL</span>
                            <span class="front-logo-sub">Your Trusted Business Partner</span>
                        </div>
                    </div>

                    <div class="hex-photo-wrapper">
                        <div class="hex-photo">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="Photo">
                            @else
                                <div style="font-size: 2.2mm; color: #64748b; text-align: center;">No Image</div>
                            @endif
                        </div>
                    </div>

                    <div class="emp-name-new">{{ $employee ? $employee->name : 'N/A' }}</div>
                    <div class="emp-designation-new">{{ $employee->designation->name ?? '' }}</div>

                    <div class="info-list">
                        <div class="info-row">
                            <div class="info-label">ID No</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">{{ $employee->id_no ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Department</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">{{ $employee->department->name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Blood Group</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">{{ $employee->blood_group ?? 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Birth Date</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">{{ $employee->date_of_birth ? \Carbon\Carbon::parse($employee->date_of_birth)->format('d M, Y') : 'N/A' }}</div>
                        </div>
                        <div class="info-row">
                            <div class="info-label">Issue Date</div>
                            <div class="info-separator">:</div>
                            <div class="info-value">{{ $card->created_at ? $card->created_at->format('d M, Y') : 'N/A' }}</div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- BACK SIDE --}}
        <div class="card-side">
            <div class="card-label">Back Side</div>
            <div class="card card-back card-employee">
                <!-- Header -->
                <div class="back-header">
                    <div class="back-header-title">Company Services</div>
                    <div class="back-header-sub">TORI INTERNATIONAL</div>
                </div>

                <!-- Benefits 2-col grid -->
                <div class="benefits-grid text-center">
                    @php
                        $benefits = [
                            // ['icon' => 'fas fa-piggy-bank', 'label' => "Savings"],
                            ['icon' => 'fas fa-hand-holding-usd', 'label' => "Product Loan"],
                            ['icon' => 'fas fa-shopping-cart', 'label' => "Shopping"],
                            ['icon' => 'fas fa-couch', 'label' => "Furniture"],
                            ['icon' => 'fas fa-car', 'label' => "Auto"],
                            ['icon' => 'fas fa-car-battery', 'label' => "Battery & IPS"],
                            ['icon' => 'fas fa-ring', 'label' => "Gold"],
                            ['icon' => 'fas fa-pump-soap', 'label' => "Cosmetics"],
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

                <!-- Contact & QR -->
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 2mm 4mm;">
                    <div class="contact-section" style="padding: 0; gap: 1mm;">
                        <div class="contact-row">
                            <i class="fas fa-headset"></i>
                            <span>HR: {{ '01313-030390' }}</span>
                        </div>
                        <div class="contact-row">
                            <i class="fas fa-globe"></i>
                            <span>www.toriinternational.com</span>
                        </div>
                        <div class="contact-row">
                            <i class="fab fa-facebook-f"></i>
                            <span>/toriinternational</span>
                        </div>
                        <div class="contact-row">
                            <i class="fab fa-youtube"></i>
                            <span>/toriinternational</span>
                        </div>
                    </div>
                    <div style="display: flex; flex-direction: column; align-items: center;">
                        <div class="qr-box" style="padding: 0.8mm; border: 0.3mm solid #cbd5e1; box-shadow: none;">
                            <img src="{{ $qrUrl }}" alt="QR" style="width: 11mm; height: 11mm;">
                        </div>
                    </div>
                </div>

                <div class="back-divider"></div>

                <!-- Signatures -->
                <div class="signature-section">
                    <div class="sig-item">
                        <div class="sig-line">
                            @if ($employee->signature_photo)
                                <img src="{{ asset($employee->signature_photo) }}" style="max-width: 100%; max-height: 100%; object-fit: contain; margin-bottom: 0.5mm;">
                            @endif
                        </div>
                        <div class="sig-label">Holder Signature</div>
                    </div>
                    <div class="sig-item">
                        <div class="sig-line">
                            <img src="{{ asset('img/authorized_signature.png') }}" style="max-width: 100%; max-height: 100%; object-fit: contain; margin-bottom: 0.5mm;">
                        </div>
                        <div class="sig-label">Authorized Signature</div>
                    </div>
                </div>

                <div class="bottom-slogan">Together We Work, Together We Grow</div>
            </div>
        </div>

    </div>

</body>
</html>
