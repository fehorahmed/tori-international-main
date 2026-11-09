<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $pageTitle }}</title>
    <link rel="stylesheet" href="{{ asset('themes/backend/plugins/fontawesome-free/css/all.min.css') }}">
    <style>
        body { font-family: 'Arial', sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; line-height: 1.4; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
        
        .section-title { background: #eee; padding: 6px 12px; font-weight: bold; border: 1px solid #333; margin-top: 5px; margin-bottom: 5px; text-transform: uppercase; display: flex; align-items: center; border-radius: 4px; }
        .section-title i { margin-right: 10px; font-size: 14px; }
        
        .row { display: flex; flex-wrap: wrap; margin-bottom: 5px; }
        .col { flex: 1; padding: 4px 8px; box-sizing: border-box; }
        .col-3 { flex: 0 0 25%; padding: 4px 8px; box-sizing: border-box; }
        .col-4 { flex: 0 0 33.33%; padding: 4px 8px; box-sizing: border-box; }
        .col-6 { flex: 0 0 50%; padding: 4px 8px; box-sizing: border-box; }
        .col-12 { flex: 0 0 100%; padding: 4px 8px; box-sizing: border-box; }
        
        /* Flex group for Label and Line on same row */
        .field-group { display: flex; align-items: flex-end; margin-bottom: 10px; }
        .label { font-weight: bold; margin-right: 8px; white-space: nowrap; color: #000; }
        .input-line { border-bottom: 1px dotted #444; flex: 1; height: 18px; position: relative; top: -2px; }
        
        .photo-box { width: 130px; height: 150px; border: 1px dashed #000; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 11px; margin-bottom: 10px; font-weight: bold; box-sizing: border-box; }
        .nid-box { width: 100%; height: 110px; border: 1px dashed #000; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 11px; font-weight: bold; box-sizing: border-box; }
        
        .address-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .address-table td { border: 1px solid #333; padding: 12px; vertical-align: top; width: 50%; }
        .address-title { font-weight: bold; text-decoration: underline; margin-bottom: 12px; display: block; font-size: 14px; }
        
        .address-inner-row { display: flex; align-items: flex-end; margin-bottom: 8px; }
        .address-inner-label { font-weight: bold; width: 120px; font-size: 12px; }
        .address-inner-value { flex: 1; border-bottom: 1px dotted #666; height: 16px; }

        .signature-area { margin-top: 60px; display: flex; justify-content: space-between; padding: 0 10px; }
        .sig-box { text-align: center; border-top: 1px solid #000; width: 180px; padding-top: 8px; font-weight: bold; }

        .page-break { page-break-before: always; }
        
        /* Checkbox style */
        .checkbox-group { display: flex; gap: 15px; align-items: center; }
        .box { width: 12px; height: 12px; border: 1px solid #000; display: inline-block; margin-right: 4px; }
    </style>
</head>
<body>
    <div class="no-print" style="background: #28a745; padding: 15px; text-align: center; margin-bottom: 25px; border-radius: 8px; font-weight: bold; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 12px 30px; cursor: pointer; background: #fff; color: #28a745; border: 2px solid #fff; border-radius: 6px; font-weight: 900; text-transform: uppercase;">CLICK TO PRINT ADMISSION FORM</button>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div style="width: 140px;"></div>
        <div style="flex: 1; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-left: 20px; margin-right: 20px;">
            <h1 style="margin: 0; font-size: 26px; text-transform: uppercase; letter-spacing: 1px;">TORI INTERNATIONAL</h1>
            <p style="margin: 5px 0; font-size: 16px; font-weight: bold; color: #000;">MEMBER ADMISSION FORM (সদস্য ভর্তি ফরম)</p>
        </div>
        <div style="width: 140px; display: flex; flex-direction: column; align-items: flex-end; padding-right: 5px;">
            <div class="photo-box" style="margin-bottom: 0;">MEMBER PHOTO<br>(সদস্যের ছবি)</div>
        </div>
    </div>

    <div class="row">
        <div class="col-6">
            <div class="field-group">
                <span class="label">Admission Date:</span>
                <span class="input-line"></span>
            </div>
            <div class="field-group">
                <span class="label">Member No:</span>
                <span class="input-line"></span>
            </div>
            <div class="field-group">
                <span class="label">Somiti (সমিতি):</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-6">
            <div class="field-group">
                <span class="label">Admission Fee:</span>
                <span class="input-line"></span>
            </div>
            <div class="field-group">
                <span class="label">Form Fee:</span>
                <span class="input-line"></span>
            </div>
            <div class="field-group">
                <span class="label">Passbook Fee:</span>
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
                <span class="label">Date of Birth:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Gender:</span>
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
                <span class="label">Marital Status:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Religion:</span>
                <span class="input-line"></span>
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
        <div class="col-6">
            <div class="field-group">
                <span class="label">Education:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-6">
            <div class="field-group">
                <span class="label">Member Category:</span>
                <span class="checkbox-group" style="margin-left: 10px;">
                    <span><div class="box"></div> General</span>
                    <span><div class="box"></div> Lead</span>
                    <span><div class="box"></div> Somiti Head</span>
                </span>
            </div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-users"></i> FAMILY & PROFESSIONAL INFO (পারিবারিক ও পেশাগত)</div>
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
                <span class="label">Spouse Name:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-4">
            <div class="field-group">
                <span class="label">Profession:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Monthly Income:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-4">
            <div class="field-group">
                <span class="label">Total Family Members:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-6">
            <div class="field-group">
                <span class="label">NID No:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-6">
            <div class="field-group">
                <span class="label">Birth Certificate:</span>
                <span class="input-line"></span>
            </div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-map-marker-alt"></i> ADDRESS DETAILS (ঠিকানা)</div>
    <table class="address-table">
        <tr>
            <td>
                <span class="address-title">PRESENT ADDRESS (বর্তমান ঠিকানা)</span>
                <div class="address-inner-row"><span class="address-inner-label">Division:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">District:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Upazilla:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Union/Paurashava:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Village/Area:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">House/Road:</span><span class="address-inner-value"></span></div>
            </td>
            <td>
                <span class="address-title">PERMANENT ADDRESS (স্থায়ী ঠিকানা)</span>
                <div class="address-inner-row"><span class="address-inner-label">Division:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">District:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Upazilla:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Union/Paurashava:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">Village/Area:</span><span class="address-inner-value"></span></div>
                <div class="address-inner-row"><span class="address-inner-label">House/Road:</span><span class="address-inner-value"></span></div>
            </td>
        </tr>
    </table>

    <div class="page-break"></div>

    <div class="section-title"><i class="fa fa-file-alt"></i> MEMBER DOCUMENTS (ডকুমেন্টস)</div>
    <div class="row">
        <div class="col-6">
            <div class="nid-box">NID FRONT SIDE<br>(এনআইডি সামনের দিক)</div>
        </div>
        <div class="col-6">
            <div class="nid-box">NID BACK SIDE<br>(এনআইডি পেছনের দিক)</div>
        </div>
    </div>
    <div class="section-title"><i class="fa fa-signature"></i> SPECIMEN SIGNATURE / L.T.I (নমুনা স্বাক্ষর / টিপসই)</div>
    <div class="row" style="margin-bottom: 10px;">
        <div class="col-4">
            <div style="width: 100%; height: 60px; border: 1px dashed #000; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 5px; font-weight: bold; font-size: 11px; box-sizing: border-box;">
                Signature / L.T.I (1)
            </div>
        </div>
        <div class="col-4">
            <div style="width: 100%; height: 60px; border: 1px dashed #000; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 5px; font-weight: bold; font-size: 11px; box-sizing: border-box;">
                Signature / L.T.I (2)
            </div>
        </div>
        <div class="col-4">
            <div style="width: 100%; height: 60px; border: 1px dashed #000; display: flex; align-items: flex-end; justify-content: center; padding-bottom: 5px; font-weight: bold; font-size: 11px; box-sizing: border-box;">
                Signature / L.T.I (3)
            </div>
        </div>
    </div>

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
                <span class="label">Gender:</span>
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
            <div class="photo-box">NOMINEE PHOTO<br>(নমিনির ছবি)</div>
        </div>
        <div class="col-4">
            <div class="nid-box">NID FRONT SIDE<br>(এনআইডি সামনের দিক)</div>
        </div>
        <div class="col-4">
            <div class="nid-box">NID BACK SIDE<br>(এনআইডি পেছনের দিক)</div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-pen"></i> DECLARATION & SIGNATURE (ঘোষণা ও স্বাক্ষর)</div>
    <p style="text-align: justify; line-height: 1.8; margin-bottom: 30px; font-size: 13px;">
        I hereby declare that the information provided above is true and correct to the best of my knowledge and belief. I agree to abide by all the rules and regulations of the organization. (আমি ঘোষণা করছি যে উপরে প্রদত্ত তথ্যগুলো আমার জ্ঞান ও বিশ্বাস মতে সত্য এবং সঠিক। আমি প্রতিষ্ঠানের সকল নিয়মকানুন মেনে চলতে অঙ্গীকারাবদ্ধ।)
    </p>

    <div class="signature-area">
        <div class="sig-box">Member's Signature<br>(সদস্যের স্বাক্ষর)</div>
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
