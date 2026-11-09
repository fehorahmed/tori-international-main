<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shomriddhi Collection Form - {{ $account->customer->name ?? '' }}</title>
    <style>
        body { font-family: 'Arial', sans-serif; margin: 0; padding: 20px; color: #000; font-size: 13px; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        .header-section { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; }
        
        .qr-box { width: 100px; height: 100px; border: 2px dashed #000; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px; text-align: center; color: #555; }
        .photo-box { width: 110px; height: 130px; border: 2px dashed #000; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; text-align: center; color: #555; }
        
        .title-section { flex: 1; text-align: center; padding: 0 15px; margin-top: 10px; }
        .title-section h1 { margin: 0; font-size: 32px; text-transform: capitalize; color: #c0392b; letter-spacing: 1px; font-style: italic; font-weight: 900;}
        .name-row { font-size: 18px; display: flex; align-items: flex-end; justify-content: center; gap: 5px; margin-top: 25px; }
        .name-line { border-bottom: 1px dotted #000; width: 300px; text-align: center; font-size: 20px; color: #1565c0; padding-bottom: 2px; font-weight: bold;}

        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #c0392b; padding: 10px; text-align: center; }
        th { font-weight: bold; font-size: 15px; color: #c0392b; }
        /* Create empty rows */
        .empty-row td { height: 35px; }
        
        .print-btn { background: #1565c0; color: #fff; border: none; padding: 10px 20px; font-size: 16px; cursor: pointer; border-radius: 4px; margin-bottom: 20px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center;">
        <button class="print-btn" onclick="window.print()">Print Collection Form</button>
    </div>

    <div class="header-section">
        <!-- Left: QR Code -->
        <div class="qr-box">QR Code</div>
        
        <!-- Center: Title and Name -->
        <div class="title-section">
            <h1>Tori international</h1>
            <div class="name-row">
                <span>Name - </span>
                <span class="name-line">{{ $account->customer->name ?? '' }}</span>
            </div>
        </div>

        <!-- Right: Photo -->
        <div class="photo-box">ছবি</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Date</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 12%;">In</th>
                <th style="width: 12%;">Out</th>
                <th style="width: 15%;">Balance</th>
                <th style="width: 16%;">Signature</th>
            </tr>
        </thead>
        <tbody>
            @for ($i = 0; $i < 20; $i++)
            <tr class="empty-row">
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>
