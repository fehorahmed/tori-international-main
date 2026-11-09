<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $sisterConcern->name }} - {{ $typeName }}</title>
    <!-- Google Fonts for Elegant Typography -->
    <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;700;800&family=Great+Vibes&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        /* General Page Setup */
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: #faf8f5;
            font-family: 'Montserrat', sans-serif;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Certificate Container (A4 Landscape aspect ratio optimized) */
        .certificate-wrapper {
            position: relative;
            width: 297mm;
            height: 210mm;
            margin: 0 auto;
            box-sizing: border-box;
            padding: 20mm;
            background: #ffffff;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Outer Elegant Border */
        .certificate-border-outer {
            width: 100%;
            height: 100%;
            border: 6px double #c5a059;
            padding: 5px;
            box-sizing: border-box;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Inner Thin Border */
        .certificate-border-inner {
            width: 100%;
            height: 100%;
            border: 1px solid #c5a059;
            box-sizing: border-box;
            padding: 15mm;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
            text-align: center;
            background-image: radial-gradient(circle, rgba(197, 160, 89, 0.03) 0%, rgba(255, 255, 255, 0) 70%);
        }

        /* Corner Decorative Ornaments */
        .corner-ornament {
            position: absolute;
            width: 50px;
            height: 50px;
            border-color: #c5a059;
            border-style: solid;
            pointer-events: none;
        }
        .top-left {
            top: 10px;
            left: 10px;
            border-width: 4px 0 0 4px;
        }
        .top-right {
            top: 10px;
            right: 10px;
            border-width: 4px 4px 0 0;
        }
        .bottom-left {
            bottom: 10px;
            left: 10px;
            border-width: 0 0 4px 4px;
        }
        .bottom-right {
            bottom: 10px;
            right: 10px;
            border-width: 0 4px 4px 0;
        }

        /* Certificate Header */
        .certificate-header {
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .company-logo {
            max-height: 70px;
            margin-bottom: 15px;
        }

        .certificate-title-sub {
            font-size: 11px;
            letter-spacing: 5px;
            text-transform: uppercase;
            color: #7d725d;
            font-weight: 600;
            margin: 0;
        }

        .certificate-title {
            font-family: 'Cinzel', serif;
            font-size: 32px;
            font-weight: 700;
            color: #1a252f;
            margin: 5px 0 10px 0;
            letter-spacing: 2px;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.05);
        }

        .divider {
            width: 120px;
            height: 2px;
            background: linear-gradient(to right, transparent, #c5a059, transparent);
            margin: 5px 0 20px 0;
        }

        /* Certificate Body */
        .certificate-body {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            width: 100%;
        }

        .certify-text {
            font-size: 15px;
            color: #555555;
            font-style: italic;
            margin: 0 0 15px 0;
        }

        .branch-name {
            font-family: 'Great Vibes', cursive;
            font-size: 48px;
            color: #a37c3f;
            margin: 5px 0;
            line-height: 1;
        }

        .branch-code {
            font-family: 'Montserrat', sans-serif;
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 20px;
        }

        .description {
            font-size: 13px;
            line-height: 1.6;
            color: #666666;
            max-width: 80%;
            margin: 0 auto 25px auto;
        }

        /* Certificate Meta Grid (TIN, BIN, License etc.) */
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
            width: 85%;
            margin-bottom: 20px;
            border-top: 1px dashed #e2e8f0;
            border-bottom: 1px dashed #e2e8f0;
            padding: 12px 0;
        }

        .meta-item {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        .meta-label {
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #555555;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .meta-value {
            font-size: 14px;
            font-weight: 700;
            color: #111111;
        }

        /* Footer Section */
        .certificate-footer {
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-top: 15px;
        }

        .footer-col {
            width: 30%;
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
        }

        .sign-line {
            width: 80%;
            border-top: 1px solid #c5a059;
            margin-top: 5px;
            padding-top: 5px;
        }

        .sign-title {
            font-size: 11px;
            font-weight: 600;
            color: #4a5568;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .signature-img {
            max-height: 45px;
            position: absolute;
            bottom: 25px;
        }

        .seal-container {
            width: 80px;
            height: 80px;
            position: relative;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .seal-img {
            max-height: 75px;
            max-width: 75px;
            opacity: 0.9;
        }

        /* Print Controls */
        .print-btn-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
        }

        .print-btn {
            background-color: #c5a059;
            color: #ffffff;
            border: none;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px;
            cursor: pointer;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: background 0.2s;
        }

        .print-btn:hover {
            background-color: #a37c3f;
        }

        /* Print Media Styles */
        @media print {
            .print-btn-container {
                display: none;
            }
            body {
                background: #ffffff;
            }
            .certificate-wrapper {
                padding: 0;
                margin: 0;
                border: none;
                width: 100vw;
                height: 100vh;
            }
            @page {
                size: A4 landscape;
                margin: 0;
            }
        }
    </style>
</head>
<body>

    <!-- Print Button -->
    <div class="print-btn-container">
        <button class="print-btn" onclick="window.print()">Print Certificate</button>
    </div>

    <!-- Certificate Wrapper -->
    <div class="certificate-wrapper">
        <div class="certificate-border-outer">
            <!-- Decorative Corners -->
            <div class="corner-ornament top-left"></div>
            <div class="corner-ornament top-right"></div>
            <div class="corner-ornament bottom-left"></div>
            <div class="corner-ornament bottom-right"></div>

            <div class="certificate-border-inner">
                
                <!-- Header -->
                <div class="certificate-header">
                    @if($sisterConcern->logo && file_exists(public_path($sisterConcern->logo)))
                        <img src="{{ asset($sisterConcern->logo) }}" alt="Logo" class="company-logo">
                    @else
                        <img src="{{ asset('img/placeholder_logo.png') }}" alt="Logo" class="company-logo">
                    @endif
                    <p class="certificate-title-sub">Official Authorization</p>
                    <h1 class="certificate-title">BRANCH CERTIFICATE</h1>
                    <div class="divider"></div>
                </div>

                <!-- Body -->
                <div class="certificate-body">
                    <p class="certify-text">This is to certify that the business unit</p>
                    <h2 class="branch-name">{{ $sisterConcern->name }}</h2>
                    <div class="branch-code">Branch Code: {{ $sisterConcern->code }}</div>
                    
                    <p class="description">
                        Is an officially registered and authorized operational branch. 
                        This branch is permitted to run general trading, accounts, logistics, 
                        and system operations under the head office administration, located at 
                        <strong>{{ $sisterConcern->address }}</strong>
                        @if($sisterConcern->upazila || $sisterConcern->district)
                            (<strong>{{ $sisterConcern->upazila ?? '' }}{{ $sisterConcern->upazila && $sisterConcern->district ? ', ' : '' }}{{ $sisterConcern->district ?? '' }}</strong>)
                        @endif.
                    </p>

                    <!-- Legal Identifiers Grid -->
                    <div class="meta-grid">
                        <div class="meta-item">
                            <span class="meta-label">Trade License</span>
                            <span class="meta-value">{{ $sisterConcern->trade_license_no ?? 'N/A' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">TIN Number</span>
                            <span class="meta-value">{{ $sisterConcern->tin_no ?? 'N/A' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">BIN Number</span>
                            <span class="meta-value">{{ $sisterConcern->bin_no ?? 'N/A' }}</span>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="meta-grid" style="border-top: none; margin-top: -15px;">
                        <div class="meta-item">
                            <span class="meta-label">Establishment Date</span>
                            <span class="meta-value">
                                {{ $sisterConcern->establishment_date ? \Carbon\Carbon::parse($sisterConcern->establishment_date)->format('d M, Y') : 'N/A' }}
                            </span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Email</span>
                            <span class="meta-value">{{ $sisterConcern->email ?? 'N/A' }}</span>
                        </div>
                        <div class="meta-item">
                            <span class="meta-label">Certificate Expiry</span>
                            <span class="meta-value">
                                {{ $sisterConcern->certificate_expiry_date ? \Carbon\Carbon::parse($sisterConcern->certificate_expiry_date)->format('d M, Y') : 'Lifetime' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="certificate-footer">
                    <div class="footer-col">
                        <span class="sign-title">Issued Date</span>
                        <div class="sign-line"></div>
                        <span class="sign-title" style="font-size: 10px; color: #888;">
                            {{ $sisterConcern->business_date ? \Carbon\Carbon::parse($sisterConcern->business_date)->format('d M, Y') : date('d M, Y') }}
                        </span>
                    </div>

                    <div class="footer-col" style="align-items: center; justify-content: center;">
                        <div class="seal-container">
                            @if($sisterConcern->official_seal && file_exists(public_path($sisterConcern->official_seal)))
                                <img src="{{ asset($sisterConcern->official_seal) }}" alt="Seal" class="seal-img">
                            @endif
                        </div>
                        <span class="sign-title" style="font-size: 10px; color: #888;">OFFICIAL SEAL</span>
                    </div>

                    <div class="footer-col">
                        @if($sisterConcern->auth_signature && file_exists(public_path($sisterConcern->auth_signature)))
                            <img src="{{ asset($sisterConcern->auth_signature) }}" alt="Signature" class="signature-img">
                        @endif
                        <div class="sign-line"></div>
                        <span class="sign-title">Authorized Signature</span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
        window.onafterprint = function() {
            window.close();
        };
    </script>
</body>
</html>
