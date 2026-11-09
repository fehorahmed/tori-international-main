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
            body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        
        .section-title { background: #e3f2fd; color: #1565c0; padding: 6px 12px; font-weight: bold; border: 1px solid #1565c0; margin-top: 5px; margin-bottom: 5px; text-transform: uppercase; display: flex; align-items: center; border-radius: 4px; }
        .section-title i { margin-right: 10px; font-size: 14px; color: #1565c0; }
        
        .row { display: flex; flex-wrap: wrap; margin-bottom: 5px; }
        .col { flex: 1; padding: 4px 8px; box-sizing: border-box; }
        .col-3 { flex: 0 0 25%; padding: 4px 8px; box-sizing: border-box; }
        .col-4 { flex: 0 0 33.33%; padding: 4px 8px; box-sizing: border-box; }
        .col-6 { flex: 0 0 50%; padding: 4px 8px; box-sizing: border-box; }
        .col-12 { flex: 0 0 100%; padding: 4px 8px; box-sizing: border-box; }
        
        /* Flex group for Label and Line on same row */
        .field-group { display: flex; align-items: flex-end; margin-bottom: 12px; }
        .label { font-weight: bold; margin-right: 8px; white-space: nowrap; color: #0d47a1; }
        .input-line { border-bottom: 1px dotted #1565c0; flex: 1; height: 18px; position: relative; top: -2px; }
        
        .photo-box { width: 130px; height: 150px; border: 2px dashed #1565c0; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 12px; margin-bottom: 10px; font-weight: bold; box-sizing: border-box; color: #1565c0; background: #f8fbff; border-radius: 6px; }
        .nid-box { width: 100%; height: 110px; border: 2px dashed #1565c0; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 11px; font-weight: bold; box-sizing: border-box; color: #1565c0; background: #f8fbff; border-radius: 6px; }
        
        .address-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .address-table td { border: 1px solid #1565c0; padding: 15px; vertical-align: top; width: 100%; background: #fcfdfe; }
        .address-title { font-weight: bold; margin-bottom: 15px; display: inline-block; font-size: 14px; background: #e3f2fd; color: #1565c0; padding: 4px 10px; border-radius: 4px; border: 1px solid #1565c0; }
        
        .address-inner-row { display: flex; align-items: flex-end; margin-bottom: 10px; }
        .address-inner-label { font-weight: bold; width: 130px; font-size: 12px; color: #0d47a1; }
        .address-inner-value { flex: 1; border-bottom: 1px dotted #1565c0; height: 16px; }

        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; padding: 0 20px; }
        .sig-box { text-align: center; border-top: 2px solid #1565c0; width: 200px; padding-top: 8px; font-weight: bold; color: #0d47a1; }

        .page-break { page-break-before: always; }
        
        /* Checkbox style */
        .checkbox-group { display: flex; gap: 15px; align-items: center; }
        .box { width: 12px; height: 12px; border: 1px solid #1565c0; display: inline-block; margin-right: 4px; }
    </style>
</head>
<body>
    <div class="no-print" style="background: #1565c0; padding: 15px; text-align: center; margin-bottom: 25px; border-radius: 8px; font-weight: bold; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 12px 30px; cursor: pointer; background: #fff; color: #1565c0; border: 2px solid #fff; border-radius: 6px; font-weight: 900; text-transform: uppercase;"><i class="fa fa-print"></i> CLICK TO PRINT DATA COLLECTION FORM</button>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0px;">
        <div style="width: 140px; display: flex; align-items: center; justify-content: flex-start;">
            <img src="{{ asset('img/logo.png') }}" onerror="this.style.display='none'" style="max-height: 80px; max-width: 100%; object-fit: contain;">
        </div> 
        <div style="flex: 1; text-align: center; border-bottom: 3px solid #1565c0; padding-bottom: 15px; margin-left: 20px; margin-right: 20px;">
            <h1 style="margin: 0; font-size: 28px; text-transform: uppercase; letter-spacing: 2px; color: #0d47a1;">TORI INTERNATIONAL</h1>
            <p style="margin: 8px 0; font-size: 17px; font-weight: bold; color: #1565c0; background: #e3f2fd; display: inline-block; padding: 4px 15px; border-radius: 20px;">SUPPLIER DATA COLLECTION FORM</p>
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
                <span class="label">Supplier No:</span>
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
        <div class="col-6">
            <div class="field-group">
                <span class="label">NID No:</span>
                <span class="input-line"></span>
            </div>
        </div>
        <div class="col-6">
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

    <div class="section-title"><i class="fa fa-pen-nib"></i> DECLARATION & SIGNATURE (ঘোষণা ও স্বাক্ষর)</div>
    <p style="text-align: justify; line-height: 1.8; margin-bottom: 40px; font-size: 13px; padding: 15px; background: #f8fbff; border-left: 4px solid #1565c0; border-radius: 4px; color: #333;">
        <i class="fa fa-info-circle" style="color: #1565c0; margin-right: 5px;"></i> I hereby declare that the information provided above is true and correct to the best of my knowledge and belief. (আমি ঘোষণা করছি যে উপরে প্রদত্ত তথ্যগুলো আমার জ্ঞান ও বিশ্বাস মতে সত্য এবং সঠিক।)
    </p>

    <div class="signature-area">
        <div class="sig-box">Supplier's Signature<br>(সরবরাহকারীর স্বাক্ষর)</div>
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
