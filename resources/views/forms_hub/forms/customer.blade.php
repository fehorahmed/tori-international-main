<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link rel="stylesheet" href="{{ asset('themes/backend/dist/css/adminlte.min.css') }}">
    <link rel="stylesheet" href="{{ asset('themes/backend/plugins/fontawesome-free/css/all.min.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; font-size: 16px; color: #222; margin: 0; padding: 20px; line-height: 1.6; background-color: #fff; }
        .row { display: flex; flex-wrap: wrap; margin-bottom: 20px; }
        .col-4 { width: 33.33%; padding: 0 10px; box-sizing: border-box; }
        .col-6 { width: 50%; padding: 0 10px; box-sizing: border-box; }
        .col-12 { width: 100%; padding: 0 10px; box-sizing: border-box; }
        
        .section-title { 
            font-size: 18px; 
            font-weight: 700; 
            margin-top: 30px; 
            margin-bottom: 20px; 
            padding: 10px 15px;
            background: linear-gradient(90deg, #e3f2fd 0%, rgba(227, 242, 253, 0.2) 100%);
            color: #0d47a1; 
            border-left: 5px solid #0d47a1; 
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .field-group { margin-bottom: 15px; display: flex; align-items: flex-end; }
        .label { font-weight: 600; width: 160px; flex-shrink: 0; color: #444; font-size: 16px; white-space: nowrap; }
        .input-line { flex-grow: 1; border-bottom: 1px dotted #888; height: 24px; margin-left: 5px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        table, th, td { border: 1px solid #b0bec5; }
        th { padding: 12px; text-align: left; background: #f1f5f9; font-size: 15px; font-weight: 600; color: #1e293b; text-transform: uppercase; }
        td { padding: 12px; font-size: 16px; }
        
        .address-table td { padding: 20px; vertical-align: top; width: 50%; background: #fcfdfe; border: 1px solid #1565c0; }
        .address-title { font-weight: 700; margin-bottom: 20px; display: inline-block; font-size: 17px; background: #e3f2fd; color: #1565c0; padding: 5px 12px; border-radius: 4px; border: 1px solid #1565c0; }
        .address-inner-row { display: flex; align-items: flex-end; margin-bottom: 15px; }
        .address-inner-label { font-weight: 700; width: 140px; font-size: 15px; color: #0d47a1; }
        .address-inner-value { flex-grow: 1; border-bottom: 1px dotted #1565c0; height: 22px; }
        
        .box { width: 16px; height: 16px; border: 1px solid #555; display: inline-block; vertical-align: middle; margin-right: 5px; border-radius: 2px; }
        .checkbox-group span { margin-right: 15px; font-size: 16px; }
        
        .nid-box { width: 100%; height: 110px; border: 1.5px dashed #1565c0; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 15px; font-weight: 600; box-sizing: border-box; color: #1565c0; background: rgba(21, 101, 192, 0.03); border-radius: 6px; }
        .photo-box { width: 130px; height: 160px; border: 2px dashed #1565c0; display: flex; align-items: center; justify-content: center; text-align: center; margin-bottom: 10px; color: #1565c0; background: rgba(21, 101, 192, 0.03); border-radius: 6px; }
        
        .signature-area { display: flex; justify-content: space-between; margin-top: 70px; margin-bottom: 20px; padding: 0 20px; }
        .sig-box { text-align: center; font-weight: 600; width: 200px; border-top: 2px solid #0d47a1; padding-top: 10px; font-size: 15px; color: #0d47a1; }
        
        /* Watermark element */
        .watermark { position: fixed; top: 50%; left: 50%; transform: translate(-50%, -50%); opacity: 0.04; z-index: -1; width: 90%; pointer-events: none; }
        .print-footer { display: none; }

        @media print {
            body { padding: 0; background-color: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .no-print { display: none !important; }
            .page-break { page-break-before: always; }
            
            /* Add professional footer on printed pages */
            @page {
                size: A4;
                margin: 7mm 7mm;
            }
            .print-footer { display: flex; position: fixed; bottom: 0; width: 100%; justify-content: space-between; font-size: 10px; color: #888; border-top: 1px solid #ddd; padding-top: 5px; background: white; z-index: 10; }
        }
    </style>
</head>
<body>
    <!-- Watermark -->
    <img src="{{ asset('img/logo.png') }}" class="watermark" alt="Watermark" onerror="this.style.display='none'">
    
    <!-- Print Footer -->
    <div class="print-footer">
        <div>Powered by <strong>Tori International</strong> System</div>
        <div>Printed on: {{ date('d M, Y h:i A') }}</div>
    </div>

    <div class="no-print" style="background: #1565c0; padding: 15px; text-align: center; margin-bottom: 25px; border-radius: 8px; font-weight: bold; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 12px 30px; cursor: pointer; background: #fff; color: #1565c0; border: 2px solid #fff; border-radius: 6px; font-weight: 900; text-transform: uppercase;"><i class="fa fa-print"></i> CLICK TO PRINT DATA COLLECTION FORM</button>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0px;">
        <div style="width: 140px; display: flex; align-items: center; justify-content: flex-start;">
            <img src="{{ asset('img/logo.png') }}" onerror="this.style.display='none'" style="max-height: 80px; max-width: 100%; object-fit: contain;">
        </div> 
        <div style="flex: 1; text-align: center; border-bottom: 3px solid #1565c0; padding-bottom: 15px; margin-left: 20px; margin-right: 20px;">
            <h1 style="margin: 0; font-size: 28px; text-transform: uppercase; letter-spacing: 2px; color: #0d47a1;">TORI INTERNATIONAL</h1>
            <p style="margin: 8px 0; font-size: 17px; font-weight: bold; color: #1565c0; background: #e3f2fd; display: inline-block; padding: 4px 15px; border-radius: 20px;">CUSTOMER DATA COLLECTION FORM</p>
        </div>
        <div style="width: 140px; display: flex; flex-direction: column; align-items: flex-end; padding-right: 5px;">
            <div class="photo-box" style="margin-bottom: 0;"><i class="fa fa-camera" style="font-size: 24px; margin-bottom: 8px; color:#1565c0;"></i></div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="field-group">
                <span class="label">Date:</span>
                <span class="input-line"></span>
            </div>
            <div class="field-group">
                <span class="label">Customer No:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-user"></i> BASIC INFORMATION (প্রাথমিক তথ্য)</div>
    <div class="row">
        <div class="col-6">
            <div class="field-group">
                <span class="label">Full Name (English):</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-6">
            <div class="field-group">
                <span class="label">নাম (বাংলায়):</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label">Mobile No:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Email:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Date of Birth:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label" style="width: 70px;">NID No:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label" style="width: 65px;">Gender:</span>
                <span class="checkbox-group" style="margin-left: 10px;">
                    <span><div class="box"></div> Male</span>
                    <span><div class="box"></div> Female</span>
                </span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Blood Group:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label">Religion:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Profession:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Opening Balance:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-users"></i> FAMILY INFO (পারিবারিক তথ্য)</div>
    <div class="row">
        <div class="col-6">
            <div class="field-group">
                <span class="label">Father's Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-6">
            <div class="field-group">
                <span class="label">Mother's Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="field-group">
                <span class="label">Spouse Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-map-marker-alt"></i> ADDRESS DETAILS (ঠিকানা)</div>
    <table class="address-table">
        <tr>
            <td>
                <span class="address-title"><i class="fa fa-home"></i> ADDRESS (ঠিকানা)</span>
                <div class="address-inner-row"><span class="address-inner-label">Division:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">District:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Upazilla:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Union/Paurashava:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Village/Area:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">House/Road:</span><span class="address-inner-value"></span></div>
            </td>
        </tr>
    </table>

    <div class="section-title"><i class="fa fa-file-alt"></i> CUSTOMER DOCUMENTS (ডকুমেন্টস)</div>
    <div class="row" style="margin-bottom: 0px;">
        <div class="col-6">
            <div class="nid-box" style="height: 110px;">NID FRONT SIDE<br>(এনআইডি সামনের দিক)</div>
        </div>
        <div class="col-6">
            <div class="nid-box" style="height: 110px;">NID BACK SIDE<br>(এনআইডি পেছনের দিক)</div>
        </div>
    </div>

    <div class="page-break"></div>

    <div class="section-title"><i class="fa fa-signature"></i> SPECIMEN SIGNATURE / L.T.I (নমুনা স্বাক্ষর / টিপসই)</div>
    <table style="width: 99.5%; border-collapse: collapse; border: 1px solid #1565c0; margin-bottom: 25px; margin-left: auto; margin-right: auto;">
        <tr>
            <td style="border: 1px solid #1565c0; padding: 10px; text-align: center; width: 33.33%; height: 70px; font-weight: bold; font-size: 16px;">স্বাক্ষর (Signature)</td>
            <td style="border: 1px solid #1565c0; height: 70px; width: 33.33%;"></td>
            <td style="border: 1px solid #1565c0; height: 70px; width: 33.33%;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid #1565c0; padding: 10px; text-align: center; width: 33.33%; height: 70px; font-weight: bold; font-size: 16px;">স্বাক্ষর (Signature)</td>
            <td style="border: 1px solid #1565c0; height: 70px; width: 33.33%;"></td>
            <td style="border: 1px solid #1565c0; height: 70px; width: 33.33%;"></td>
        </tr>
        <tr>
            <td style="border: 1px solid #1565c0; padding: 10px; text-align: center; width: 33.33%; height: 70px; font-weight: bold; font-size: 16px;">স্বাক্ষর (Signature)</td>
            <td style="border: 1px solid #1565c0; height: 70px; width: 33.33%;"></td>
            <td style="border: 1px solid #1565c0; height: 70px; width: 33.33%;"></td>
        </tr>
    </table>

    <div class="section-title"><i class="fa fa-user-friends"></i> NOMINEE INFORMATION (নমিনির তথ্য)</div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label">Nominee Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Relation:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Share (%):</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label">Mobile/Phone:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Nominee NID:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label" style="width: 65px;">Gender:</span>
                <span class="checkbox-group" style="margin-left: 10px;">
                    <span><div class="box"></div> Male</span>
                    <span><div class="box"></div> Female</span>
                </span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label">Father's Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Mother's Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Date of Birth:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    
    <div style="margin: 15px 8px;">
        <span class="address-title" style="font-size: 12px; margin-bottom: 10px;">NOMINEE ADDRESS (নমিনির ঠিকানা)</span>
        <div class="row">
            <div class="col-4">
                <div class="address-inner-row"><span class="address-inner-label" style="width: 70px;">Division:</span><span class="address-inner-value"></span></div>
            </div>
            <div class="col-4">
                <div class="address-inner-row"><span class="address-inner-label" style="width: 70px;">District:</span><span class="address-inner-value"></span></div>
            </div>
            <div class="col-4">
                <div class="address-inner-row"><span class="address-inner-label" style="width: 70px;">Upazilla:</span><span class="address-inner-value"></span></div>
            </div>
        </div>
        <div class="row">
            <div class="col-4">
                <div class="address-inner-row"><span class="address-inner-label" style="width: 70px;">Union:</span><span class="address-inner-value"></span></div>
            </div>
            <div class="col-4">
                <div class="address-inner-row"><span class="address-inner-label" style="width: 70px;">Village:</span><span class="address-inner-value"></span></div>
            </div>
            <div class="col-4">
                <div class="address-inner-row"><span class="address-inner-label" style="width: 70px;">House:</span><span class="address-inner-value"></span></div>
            </div>
        </div>
    </div>

    <div class="row" style="margin-top: 20px;">
        <div class="col-4">
            <div class="photo-box" style="margin-top: 0px;">NOMINEE PHOTO<br>(নমিনির ছবি)</div>
        </div>
        <div class="col-4">
            <div class="nid-box" style="margin-top: 0px;">NID FRONT SIDE<br>(এনআইডি সামনের দিক)</div>
        </div>
        <div class="col-4">
            <div class="nid-box" style="margin-top: 0px;">NID BACK SIDE<br>(এনআইডি পেছনের দিক)</div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-pen-nib"></i> DECLARATION & SIGNATURE (ঘোষণা ও স্বাক্ষর)</div>
    <p style="text-align: justify; line-height: 1.8; margin-bottom: 40px; font-size: 13px; padding: 15px; background: #f8fbff; border-left: 4px solid #1565c0; border-radius: 4px; color: #333;">
        <i class="fa fa-info-circle" style="color: #1565c0; margin-right: 5px;"></i> I hereby declare that the information provided above is true and correct to the best of my knowledge and belief. (আমি ঘোষণা করছি যে উপরে প্রদত্ত তথ্যগুলো আমার জ্ঞান ও বিশ্বাস মতে সত্য এবং সঠিক।)
    </p>

    <div class="signature-area">
        <div class="sig-box">Customer's Signature<br>(গ্রাহকের স্বাক্ষর)</div>
        <div class="sig-box">Nominee's Signature<br>(নমিনির স্বাক্ষর)</div>
        <div class="sig-box">Authorized Signature<br>(কর্তৃপক্ষের স্বাক্ষর)</div>
    </div>


    <script>
        window.onload = function() {
            window.print();
            // Close window after print dialog is closed
            window.onafterprint = function() {
                window.close();
            };
            // Fallback for some browsers
            setTimeout(function() {
                // If onafterprint didn't trigger, we can't reliably close 
                // but usually print() is blocking.
            }, 1000);
        };
    </script>
</body>
</html>
