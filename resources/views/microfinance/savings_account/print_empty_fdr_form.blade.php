<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FDR Application Form</title>
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
        .col-4 { flex: 0 0 33.33%; padding: 4px 8px; box-sizing: border-box; }
        .col-6 { flex: 0 0 50%; padding: 4px 8px; box-sizing: border-box; }
        .col-12 { flex: 0 0 100%; padding: 4px 8px; box-sizing: border-box; }
        .field-group { display: flex; align-items: flex-end; margin-bottom: 10px; }
        .label { font-weight: bold; margin-right: 8px; white-space: nowrap; color: #000; }
        .input-line { border-bottom: 1px dotted #444; flex: 1; height: 18px; position: relative; top: -2px; }
        .photo-box { width: 130px; height: 130px; border: 1px dashed #000; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 11px; margin-bottom: 8px; font-weight: bold; margin-left: auto; margin-right: auto; box-sizing: border-box; }
        .nid-box { width: 100%; height: 90px; border: 1px dashed #000; display: flex; align-items: center; justify-content: center; text-align: center; font-size: 11px; font-weight: bold; box-sizing: border-box; }
        .signature-area { margin-top: 50px; display: flex; justify-content: space-between; padding: 0 10px; }
        .sig-box { text-align: center; border-top: 1px solid #000; width: 180px; padding-top: 8px; font-weight: bold; }
        .checkbox-group { display: flex; gap: 15px; align-items: center; }
        .box { width: 12px; height: 12px; border: 1px solid #000; display: inline-block; margin-right: 4px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <div class="no-print" style="background: #28a745; padding: 15px; text-align: center; margin-bottom: 25px; border-radius: 8px; font-weight: bold; color: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <button onclick="window.print()" style="padding: 12px 30px; cursor: pointer; background: #fff; color: #28a745; border: 2px solid #fff; border-radius: 6px; font-weight: 900; text-transform: uppercase;">CLICK TO PRINT FDR FORM</button>
    </div>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
        <div style="width: 140px;"></div>
        <div style="flex: 1; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-left: 20px; margin-right: 20px;">
            <h1 style="margin: 0; font-size: 26px; text-transform: uppercase; letter-spacing: 1px;">TORI INTERNATIONAL</h1>
            <p style="margin: 5px 0; font-size: 16px; font-weight: bold; color: #000;">FDR APPLICATION FORM (স্থায়ী আমানত আবেদন ফরম)</p>
        </div>
        <div style="width: 140px; display: flex; flex-direction: column; align-items: flex-end; padding-right: 5px;">
            <div class="photo-box" style="margin-bottom: 0;">APPLICANT PHOTO<br>(আবেদনকারীর ছবি)</div>
        </div>
    </div>

    <div class="row">
        <div class="col-4">
            <div class="field-group"><span class="label">Date:</span><span class="input-line"></span></div>
        </div>
        <div class="col-4">
            <div class="field-group"><span class="label">FDR A/C No:</span><span class="input-line"></span></div>
        </div>
        <div class="col-4">
            <div class="field-group"><span class="label">Somiti:</span><span class="input-line"></span></div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-user"></i> APPLICANT INFO (আবেদনকারীর তথ্য)</div>
    <div class="row">
        <div class="col-6">
            <div class="field-group"><span class="label">Member Name:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Member No:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Father's/Spouse Name:</span><span class="input-line"></span></div>
        </div>
        <div class="col-6">
            <div class="field-group"><span class="label">Mobile No:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">NID No:</span><span class="input-line"></span></div>
        </div>
    </div>

    <div class="section-title"><i class="fa fa-wallet"></i> FDR DETAILS (স্থায়ী আমানতের বিবরণ)</div>
    <div class="row">
        <div class="col-6">
            <div class="field-group"><span class="label">Scheme Name:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Fixed Deposit Amount:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Tenure (Months/Years):</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Source of Fund:</span><span class="input-line"></span></div>
        </div>
        <div class="col-6">
            <div class="field-group"><span class="label">Interest/Profit Rate (%):</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Maturity Amount:</span><span class="input-line"></span></div>
            <div class="field-group" style="margin-top: 15px;">
                <span class="label">Profit Withdrawal Method:</span>
                <span class="checkbox-group" style="margin-left: 10px;">
                    <span><div class="box"></div> Monthly</span>
                    <span><div class="box"></div> End of Term</span>
                </span>
            </div>
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

    <div class="section-title"><i class="fa fa-user-friends"></i> NOMINEE INFO (নমিনির তথ্য)</div>
    <div class="row">
        <div class="col-6">
            <div class="field-group"><span class="label">Name:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Father's Name:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">NID No:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Mobile No:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Relation:</span><span class="input-line"></span></div>
            <div class="field-group"><span class="label">Share (%):</span><span class="input-line"></span></div>
        </div>
        <div class="col-6" style="text-align: right;">
            <div class="photo-box">NOMINEE PHOTO<br>(নমিনির ছবি)</div>
        </div>
    </div>
    <div class="row">
        <div class="col-6"><div class="nid-box">NOMINEE NID FRONT</div></div>
        <div class="col-6"><div class="nid-box">NOMINEE NID BACK</div></div>
    </div>

    <div class="page-break"></div>

    <div class="section-title"><i class="fa fa-pen"></i> DECLARATION & SIGNATURE (ঘোষণা ও স্বাক্ষর)</div>
    <p style="text-align: justify; line-height: 1.6; margin-bottom: 20px; font-size: 13px;">
        আমি ঘোষণা করছি যে, উপরে প্রদত্ত তথ্য সম্পূর্ণ সত্য। আমি এই ফিক্সড ডিপোজিট (FDR) স্কিমের সকল নিয়মকানুন মেনে চলতে অঙ্গীকারাবদ্ধ।
    </p>

    <div class="signature-area" style="margin-top: 60px;">
        <div class="sig-box">Applicant Signature<br>(আবেদনকারীর স্বাক্ষর)</div>
        <div class="sig-box">Nominee Signature<br>(নমিনির স্বাক্ষর)</div>
    </div>
    <div class="signature-area" style="margin-top: 60px; justify-content: space-around;">
        <div class="sig-box">Field Officer<br>(মাঠকর্মীর স্বাক্ষর)</div>
        <div class="sig-box">Branch Manager<br>(শাখা ব্যবস্থাপক)</div>
    </div>

    <script>
        window.onload = function() { window.print(); window.onafterprint = function() { window.close(); }; };
    </script>
</body>
</html>
