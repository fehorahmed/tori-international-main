<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Collection Sheet - {{ $account->account_number }}</title>
    @include('layouts.partial.__styles')
    <link href="https://fonts.googleapis.com/css2?family=Noto+Serif+Bengali:wght@400;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Arial', sans-serif;
            background: #e9ecef;
            color: #111;
        }
        .bengali-text {
            font-family: 'Noto Serif Bengali', serif;
        }

        .no-print {
            text-align: center;
            padding: 15px;
            background: #fff;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        /* A4 Landscape Container */
        .a4-sheet {
            width: 297mm;
            height: 210mm;
            margin: 0 auto 20px auto;
            background: white;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
            display: flex;
            page-break-after: always;
            overflow: hidden;
        }

        /* Each half is an A5 page */
        .a5-page {
            width: 50%;
            height: 100%;
            padding: 4mm;
            position: relative;
            border-right: 1px dashed #ccc; /* Fold line indicator */
        }
        .a5-page:last-child {
            border-right: none;
        }

        /* Info Section on Front Page */
        .cover-info {
            margin-bottom: 15px;
        }

        .cover-header {
            display: grid;
            grid-template-columns: 80px 1fr 90px;
            gap: 10px;
            align-items: flex-start;
            margin-bottom: 10px;
        }

        .qr-section { text-align: left; }
        .qr-section #qrcode {
            display: inline-block;
            padding: 3px;
            border: 1px solid #ddd;
        }
        .qr-ac-no {
            font-size: 0.75rem;
            font-weight: bold;
            margin-top: 4px;
            color: #1e3c72;
            text-align: center;
        }

        .title-section { text-align: center; }
        .title-bismillah { font-size: 0.9rem; color: #555; margin-bottom: 3px; }
        .title-main {
            font-size: 1.4rem;
            color: #1e3c72;
            font-weight: 800;
            text-transform: uppercase;
            margin: 0;
            line-height: 1;
        }
        .title-address { font-size: 0.8rem; color: #444; line-height: 1.3; }
        
        .badge-passbook {
            background: #1e3c72;
            color: white;
            padding: 4px 20px;
            font-size: 1.1rem;
            font-weight: bold;
            border-radius: 20px;
            display: inline-block;
            margin-top: 0px;
        }

        .photo-box {
            height: 80px;
            width: 80px;
            border: 2px solid #1e3c72;
            padding: 2px;
            border-radius: 4px;
            margin-left: auto;
        }
        .photo-box img {
            height: 100%;
            width: 100%;
            object-fit: cover;
        }
        .photo-placeholder {
            height: 100%;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            color: #aaa;
            font-weight: bold;
        }

        /* Detail Fields */
        .details-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 15px;
            font-size: 0.95rem;
        }
        .details-grid-full {
            grid-column: 1 / -1;
            display: grid;
            grid-template-columns: 1fr;
            gap: 5px;
            margin-top: 5px;
        }

        .c-field {
            display: flex;
            align-items: flex-end;
        }
        .c-label {
            color: #333;
            font-weight: bold;
            white-space: nowrap;
            margin-right: 5px;
        }
        .c-value {
            border-bottom: 1px dotted #666;
            flex-grow: 1;
            color: #000;
            font-weight: bold;
        }

        /* Transaction Table */
        .transactions-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.75rem;
        }
        .transactions-table thead th {
            background: #f0f0f0;
            color: #111;
            padding: 6px 4px;
            text-align: center;
            border: 1px solid #666;
            font-weight: bold;
        }
        .transactions-table tbody td {
            padding: 6px 4px;
            border: 1px solid #999;
            text-align: center;
            height: 25px;
        }

        /* For the specific table on the front page (which has less height available) */
        .table-title {
            text-align: center;
            color: #1e3c72;
            font-weight: bold;
            font-size: 1rem;
            margin-top: 15px;
            margin-bottom: 5px;
            border-top: 2px solid #1e3c72;
            padding-top: 10px;
        }

        @media print {
            @page {
                size: A4 landscape;
                margin: 0; /* Remove default page margins to stop scaling */
            }
            body { background: white; margin: 0; padding: 0; width: 100%; height: 100%; }
            .no-print { display: none !important; }
            .a4-sheet {
                width: 100% !important;
                height: 100vh !important;
                margin: 0 !important;
                box-shadow: none;
                page-break-after: always;
                page-break-inside: avoid;
            }
            .a5-page {
                padding: 0mm !important;
                border-right: none;
            }
            .a5-page:first-child {
                padding-right: 5mm !important;
                border-right: none;
            }
            .a5-page:last-child {
                padding-left: 5mm !important;
            }
            .transactions-table thead th {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                background: #f0f0f0 !important;
            }
            .badge-passbook {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <a href="{{ url()->previous() }}" class="btn btn-secondary btn-sm mr-3"><i class="fa fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-info btn-sm text-white"><i class="fa fa-print"></i> Print Booklet (A4 Landscape)</button>
        <p class="mt-2 text-muted small"><strong>Tip:</strong> In the print dialog, select <strong>A4</strong> paper, set orientation to <strong>Landscape</strong>, and enable <strong>Double-sided (Flip on short edge)</strong>.</p>
    </div>

    <!-- SHEET 1: OUTSIDE (Back Cover & Front Cover) -->
    <div class="a4-sheet bengali-text">
        <!-- LEFT HALF: PAGE 4 (Back Cover - Full Table) -->
        <div class="a5-page">
            <h4 style="text-align: center; color: #1e3c72; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem;">লেনদেনের বিবরণী</h4>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="15%">তারিখ</th>
                        <th width="17%">অগ্রিম</th>
                        <th width="17%">উত্তোলন</th>
                        <th width="17%">ব্যালেন্স</th>
                        <th width="14%">কর্মী স্বাক্ষর</th>
                        <th width="20%">গ্রাহক স্বাক্ষর</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i=1; $i<=22; $i++)
                    <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>

            <div style="display: flex; justify-content: space-between; margin-top: 50px; padding: 0 20px;">
                <div style="border-top: 1px dashed #333; padding-top: 5px; text-align: center; font-weight: bold; width: 120px; font-size: 0.85rem;">কর্মী স্বাক্ষর</div>
                <div style="border-top: 1px dashed #333; padding-top: 5px; text-align: center; font-weight: bold; width: 120px; font-size: 0.85rem;">ম্যানেজার স্বাক্ষর</div>
            </div>

            <div style="position: absolute; bottom: 0px; left: 0px; color: #d32f2f; font-weight: bold; font-size: 1.2rem;">৪</div>
        </div>

        <!-- RIGHT HALF: PAGE 1 (Front Cover Info + Table at bottom) -->
        <div class="a5-page">
            <div class="cover-info">
                <div class="cover-header">
                    <!-- QR Code -->
                    <div class="qr-section">
                        <div id="qrcode"></div>
                        <div class="qr-ac-no">{{ $account->account_number ?? '---' }}</div>
                    </div>

                    <!-- Title Area -->
                    <div class="title-section">
                        <h1 class="title-main">Tori International</h1>
                        <div class="title-address">শাখা কোড: {{ $sisterConcern ? $sisterConcern->code : '101' }}</div>
                        <div class="badge-passbook">কালেকশন শিট</div>
                    </div>

                    <!-- Photo -->
                    <div class="photo-box">
                        @if($account->customer && $account->customer->image)
                            <img src="{{ asset($account->customer->image) }}" alt="Photo">
                        @else
                            <div class="photo-placeholder">ছবি</div>
                        @endif
                    </div>
                </div>

                <div class="details-grid">
                    <div class="c-field">
                        <div class="c-label">সদস্য হিসাব নং:</div>
                        <div class="c-value">{{ $account->account_number }}</div>
                    </div>
                    <div class="c-field">
                        <div class="c-label">পাশ বই নং:</div>
                        <div class="c-value" style="color:#d32f2f;">
                            {{ $account->activePassbook ? $account->activePassbook->passbook_number : 'Not Issued' }}
                        </div>
                    </div>
                </div>
                <div class="details-grid-full">
                    <div class="c-field">
                        <div class="c-label">সদস্য নং:</div>
                        <div class="c-value">{{ $account->customer ? $account->customer->member_no : '' }}</div>
                        <div class="c-label" style="margin-left: 15px;">মোবাইল নং:</div>
                        <div class="c-value">{{ $account->customer ? $account->customer->mobile_no : '' }}</div>
                    </div>
                </div>
                <div class="details-grid-full">
                    <div class="c-field">
                        <div class="c-label">সদস্যের নাম:</div>
                        <div class="c-value">{{ $account->customer ? $account->customer->name : '' }}</div>
                    </div>
                    <div class="c-field">
                        <div class="c-label">পিতা/স্বামীর নাম:</div>
                        <div class="c-value">{{ $account->customer ? $account->customer->father_name : '' }}</div>
                    </div>
                </div>
                <div class="details-grid">
                    <div class="c-field">
                        <div class="c-label">ইউনিয়ন:</div>
                        <div class="c-value">{{ $account->customer && $account->customer->union ? ($account->customer->union->bn_name ?? $account->customer->union->name) : '' }}</div>
                    </div>
                    <div class="c-field">
                        <div class="c-label">গ্রাম:</div>
                        <div class="c-value">{{ $account->customer && $account->customer->village ? ($account->customer->village->bn_name ?? $account->customer->village->name) : '' }}</div>
                    </div>
                </div>
            </div>

            <!-- Table immediately after info -->
            <div class="table-title">লেনদেনের বিবরণী</div>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="15%">তারিখ</th>
                        <th width="17%">অগ্রিম</th>
                        <th width="17%">উত্তোলন</th>
                        <th width="17%">ব্যালেন্স</th>
                        <th width="14%">কর্মী স্বাক্ষর</th>
                        <th width="20%">গ্রাহক স্বাক্ষর</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $startLoop = 1;
                        $businessDate = $sisterConcern ? \Carbon\Carbon::parse($sisterConcern->business_date)->format('d/m/Y') : date('d/m/Y');
                    @endphp
                    @if($account->principal_balance > 0)
                    <tr>
                        <td>{{ $businessDate }}</td>
                        <td>{{ number_format($account->principal_balance, 2) }}</td>
                        <td></td>
                        <td>{{ number_format($account->principal_balance, 2) }}</td>
                        <td></td>
                        <td></td>
                    </tr>
                    @php $startLoop = 2; @endphp
                    @endif
                    <!-- Less rows here since upper half is taken by info -->
                    @for($i=$startLoop; $i<=14; $i++)
                    <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>
            <div style="position: absolute; bottom: 0px; right: 0px; color: #d32f2f; font-weight: bold; font-size: 1.2rem;">১</div>
        </div>
    </div>

    <!-- SHEET 2: INSIDE (Inside Left & Inside Right) -->
    <div class="a4-sheet bengali-text">
        <!-- LEFT HALF: PAGE 2 (Inside Left - Full Table) -->
        <div class="a5-page">
            <h4 style="text-align: center; color: #1e3c72; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem;">লেনদেনের বিবরণী</h4>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="15%">তারিখ</th>
                        <th width="17%">অগ্রিম</th>
                        <th width="17%">উত্তোলন</th>
                        <th width="17%">ব্যালেন্স</th>
                        <th width="14%">কর্মী স্বাক্ষর</th>
                        <th width="20%">গ্রাহক স্বাক্ষর</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i=1; $i<=25; $i++)
                    <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>
            <div style="position: absolute; bottom: 0px; left: 0px; color: #d32f2f; font-weight: bold; font-size: 1.2rem;">২</div>
        </div>

        <!-- RIGHT HALF: PAGE 3 (Inside Right - Full Table) -->
        <div class="a5-page">
            <h4 style="text-align: center; color: #1e3c72; margin-bottom: 10px; font-weight: bold; font-size: 1.1rem;">লেনদেনের বিবরণী</h4>
            <table class="transactions-table">
                <thead>
                    <tr>
                        <th width="15%">তারিখ</th>
                        <th width="17%">অগ্রিম</th>
                        <th width="17%">উত্তোলন</th>
                        <th width="17%">ব্যালেন্স</th>
                        <th width="14%">কর্মী স্বাক্ষর</th>
                        <th width="20%">গ্রাহক স্বাক্ষর</th>
                    </tr>
                </thead>
                <tbody>
                    @for($i=1; $i<=25; $i++)
                    <tr><td></td><td></td><td></td><td></td><td></td><td></td></tr>
                    @endfor
                </tbody>
            </table>
            <div style="position: absolute; bottom: 0px; right: 0px; color: #d32f2f; font-weight: bold; font-size: 1.2rem;">৩</div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script>
        $(document).ready(function() {
            var qrUrl = "{{ route('shomriddhi.mini_statement', $account->account_number) }}";
            new QRCode(document.getElementById("qrcode"), {
                text: qrUrl,
                width: 60,
                height: 60,
                colorDark : "#1e3c72",
                colorLight : "#ffffff",
                correctLevel : QRCode.CorrectLevel.M
            });
        });
    </script>
</body>
</html>
