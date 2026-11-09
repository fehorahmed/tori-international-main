<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Mini Statement - {{ $account->account_number }}</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #f0f4ff;
            min-height: 100vh;
            padding-bottom: 30px;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #0f2027, #203a43, #2c5364);
            color: white;
            padding: 30px 20px 25px;
            text-align: center;
            border-bottom-left-radius: 25px;
            border-bottom-right-radius: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .header .company { font-size: 20px; font-weight: 800; letter-spacing: 0.5px; }
        .header .subtitle { font-size: 11px; opacity: 0.8; margin-top: 4px; letter-spacing: 1.5px; text-transform: uppercase; }
        .header .acc-badge {
            display: inline-block;
            background: rgba(255,255,255,0.15);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 30px;
            padding: 6px 18px;
            font-size: 14px;
            font-weight: 600;
            margin-top: 15px;
            letter-spacing: 1px;
            backdrop-filter: blur(5px);
        }

        /* Account Card */
        .account-card {
            margin: -20px 16px 20px;
            background: white;
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
            position: relative;
            z-index: 10;
        }
        .account-card .name {
            font-size: 18px;
            font-weight: 800;
            color: #1a1a6e;
        }
        .account-card .meta {
            font-size: 12px;
            color: #666;
            margin-top: 4px;
            font-weight: 500;
        }
        .balance-row {
            display: flex;
            gap: 12px;
            margin-top: 16px;
        }
        .balance-box {
            flex: 1;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-radius: 12px;
            padding: 12px;
            text-align: center;
            border: 1px solid #e2e8f0;
        }
        .balance-box .label { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; }
        .balance-box .amount { font-size: 17px; font-weight: 800; color: #0f172a; margin-top: 4px; }
        .balance-box.interest { background: linear-gradient(135deg, #f0fdf4, #dcfce7); border-color: #bbf7d0; }
        .balance-box.interest .amount { color: #166534; }
        .balance-box.interest .label { color: #166534; }

        /* Section Title */
        .section-title {
            margin: 0 20px 12px;
            font-size: 14px;
            font-weight: 800;
            color: #334155;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Transaction List */
        .txn-list { margin: 0 16px; display: flex; flex-direction: column; gap: 10px; }
        .txn-item {
            background: white;
            border-radius: 14px;
            padding: 14px 16px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
            transition: transform 0.2s;
        }
        .txn-item:active { transform: scale(0.98); }
        .txn-left { display: flex; align-items: center; gap: 12px; }
        .txn-icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
            font-weight: bold;
        }
        .txn-icon.deposit { background: #dcfce7; color: #166534; }
        .txn-icon.withdraw { background: #fee2e2; color: #991b1b; }
        .txn-desc { font-size: 14px; font-weight: 700; color: #1e293b; }
        .txn-date { font-size: 11px; color: #64748b; margin-top: 3px; font-weight: 500; }
        .txn-right { text-align: right; }
        .txn-amount { font-size: 16px; font-weight: 800; }
        .txn-amount.deposit { color: #059669; }
        .txn-amount.withdraw { color: #dc2626; }
        .txn-balance { font-size: 11px; color: #64748b; margin-top: 3px; font-weight: 600; }

        /* Empty State */
        .empty { text-align: center; padding: 40px 20px; color: #94a3b8; font-weight: 600; }
        .empty .icon { font-size: 44px; margin-bottom: 12px; opacity: 0.5; }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 11px;
            color: #94a3b8;
            font-weight: 500;
        }
        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #f0fdf4;
            color: #15803d;
            margin-top: 6px;
            padding: 4px 12px;
            border-radius: 20px;
            font-weight: 700;
        }

        /* Action Button */
        .action-area { text-align: center; margin: 30px 20px; }
        .btn-view {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            border: none;
            padding: 16px 30px;
            font-size: 16px;
            font-weight: 700;
            border-radius: 30px;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.3);
            cursor: pointer;
            width: 100%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: transform 0.2s;
        }
        .btn-view:active { transform: scale(0.98); }

        /* Social Links */
        .social-links {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            margin-bottom: 20px;
        }

        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.6);
            z-index: 1000;
            display: none;
            align-items: flex-end;
            backdrop-filter: blur(4px);
        }
        .modal-overlay.active { display: flex; animation: fadeIn 0.3s ease; }
        .modal-content {
            background: #f0f4ff;
            width: 100%;
            max-height: 85vh;
            border-top-left-radius: 25px;
            border-top-right-radius: 25px;
            padding: 20px 0;
            position: relative;
            animation: slideUp 0.3s ease;
            display: flex;
            flex-direction: column;
        }
        .modal-header {
            padding: 0 20px 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 15px;
        }
        .modal-title { font-size: 18px; font-weight: 800; color: #1e293b; }
        .close-btn {
            background: #e2e8f0;
            border: none;
            width: 32px; height: 32px;
            border-radius: 50%;
            font-size: 18px;
            color: #64748b;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
        }
        .modal-body {
            overflow-y: auto;
            padding-bottom: 20px;
        }

        @keyframes slideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        /* Statement Table */
        .table-responsive { overflow-x: auto; }
        .statement-table { width: 100%; border-collapse: collapse; font-size: 13px; background: white; border: 1px solid #e2e8f0; }
        .statement-table th { background: #1e3c72; color: white; padding: 10px 12px; text-align: left; font-weight: 600; white-space: nowrap; }
        .statement-table td { padding: 10px 12px; border-bottom: 1px solid #e2e8f0; color: #334155; }
        .statement-table tr:nth-child(even) { background-color: #f8fafc; }
        .text-right { text-align: right !important; }
        .text-center { text-align: center !important; }
        
        .btn-download { background: #059669; color: white; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 600; display: inline-flex; align-items: center; gap: 5px; }
        
        @media print {
            body * { visibility: hidden; }
            .modal-overlay, .modal-overlay * { visibility: visible; }
            .modal-overlay { position: absolute; left: 0; top: 0; width: 100%; background: transparent; display: block !important; padding: 0; }
            .modal-content { background: white !important; box-shadow: none; border-radius: 0; width: 100%; height: auto; position: static; max-height: none; padding-top: 0; }
            .close-btn, .btn-download { display: none !important; }
        }
    </style>
</head>
<body>

<!-- Header -->
<div class="header">
    <div class="company">{{ $sisterConcern ? $sisterConcern->name : 'Tori International' }}</div>
    <div class="subtitle">SHOMRIDDHI COLLECTION CARD</div>
    <div class="acc-badge">{{ $account->account_number }}</div>
</div>

<!-- Account Info -->
<div class="account-card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap: 15px;">
        <div style="flex: 1;">
            <div class="name">{{ $account->customer ? $account->customer->name : 'N/A' }}</div>
            <div class="meta">Member No: <strong style="color:#334155;">{{ $account->customer ? $account->customer->member_no : '-' }}</strong></div>
            <div class="meta">Mobile: {{ $account->customer ? $account->customer->mobile_no : '-' }}</div>
            <div style="margin-top: 8px;">
                <span style="background:{{ $account->status === 'Active' ? '#27ae60' : '#e74c3c' }};color:white;padding:3px 12px;border-radius:20px;font-size:11px;font-weight:700;">
                    {{ $account->status }}
                </span>
            </div>
        </div>
        <div>
            @if($account->customer && $account->customer->image)
                <img src="{{ asset($account->customer->image) }}" alt="Photo" style="width: 70px; height: 80px; object-fit: cover; border-radius: 8px; border: 2px solid #e2e8f0; padding: 2px;">
            @else
                <div style="width: 70px; height: 80px; border-radius: 8px; border: 2px dashed #cbd5e1; display:flex; align-items:center; justify-content:center; color:#94a3b8; font-size:10px; font-weight:700; background:#f8fafc;">NO PHOTO</div>
            @endif
        </div>
    </div>
    <div class="balance-row">
        <div class="balance-box">
            <div class="label">Principal Balance</div>
            <div class="amount">৳ {{ number_format($account->principal_balance, 2) }}</div>
        </div>
        <div class="balance-box interest">
            <div class="label">Accrued Interest</div>
            <div class="amount">৳ {{ number_format($account->accrued_interest, 2) }}</div>
        </div>
    </div>
</div>

<!-- Action Button to Open Modal -->
<div class="action-area">
    <button class="btn-view" onclick="document.getElementById('statementModal').classList.add('active')">
        <i class="fas fa-file-invoice-dollar"></i> View Full Statement
    </button>
</div>

<!-- Social Links -->
<div class="social-links">
    <a href="https://toriinternational.com" target="_blank" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.08); color: #0d2240; border-radius: 50%; text-decoration: none; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 15px rgba(13,34,64,0.2)'; this.style.background='#0d2240'; this.style.color='#fff'; this.style.borderColor='#0d2240';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)'; this.style.background='#f1f5f9'; this.style.color='#0d2240'; this.style.borderColor='rgba(0,0,0,0.08)';"><i class="fas fa-globe"></i></a>
    <a href="https://www.facebook.com/share/1E4C6C6yJg" target="_blank" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.08); color: #1877F2; border-radius: 50%; text-decoration: none; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 15px rgba(24,119,242,0.3)'; this.style.background='#1877F2'; this.style.color='#fff'; this.style.borderColor='#1877F2';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)'; this.style.background='#f1f5f9'; this.style.color='#1877F2'; this.style.borderColor='rgba(0,0,0,0.08)';"><i class="fab fa-facebook-f"></i></a>
    <a href="https://www.youtube.com/channel/UCBTXcsyC15eynn5l398yWrA" target="_blank" style="width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; background: #f1f5f9; border: 1px solid rgba(0,0,0,0.08); color: #FF0000; border-radius: 50%; text-decoration: none; font-size: 20px; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 6px 15px rgba(255,0,0,0.3)'; this.style.background='#FF0000'; this.style.color='#fff'; this.style.borderColor='#FF0000';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 10px rgba(0,0,0,0.05)'; this.style.background='#f1f5f9'; this.style.color='#FF0000'; this.style.borderColor='rgba(0,0,0,0.08)';"><i class="fab fa-youtube"></i></a>
</div>

<!-- Modal Overlay -->
<div class="modal-overlay" id="statementModal" onclick="if(event.target === this) this.classList.remove('active')">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-title">Recent Transactions</div>
            <div style="display: flex; gap: 10px; align-items: center;">
                <button class="btn-download" onclick="window.print()"><i class="fas fa-download"></i> Download</button>
                <button class="close-btn" onclick="document.getElementById('statementModal').classList.remove('active')">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
        <div class="modal-body" style="padding: 0 20px;">
            <div class="table-responsive">
                <table class="statement-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Particulars</th>
                            <th class="text-right">Withdraw</th>
                            <th class="text-right">Deposit</th>
                            <th class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if(isset($openingBalance) && $transactions->isNotEmpty())
                        <tr>
                            <td>-</td>
                            <td>Opening Balance</td>
                            <td class="text-right">-</td>
                            <td class="text-right">-</td>
                            <td class="text-right">৳ {{ number_format($openingBalance, 2) }}</td>
                        </tr>
                        @endif
                        
                        @forelse($transactions as $txn)
                        <tr>
                            <td style="white-space: nowrap;">{{ \Carbon\Carbon::parse($txn->date)->format('d M Y') }}</td>
                            <td>{{ $txn->note ?? ($txn->type === 'deposit' ? 'Deposit' : 'Withdrawal') }}</td>
                            <td class="text-right" style="color: #dc2626; white-space: nowrap;">{{ $txn->type === 'withdraw' ? '৳ ' . number_format($txn->amount, 2) : '-' }}</td>
                            <td class="text-right" style="color: #059669; white-space: nowrap;">{{ $txn->type === 'deposit' ? '৳ ' . number_format($txn->amount, 2) : '-' }}</td>
                            <td class="text-right" style="white-space: nowrap; font-weight: 600;">
                                @if(isset($balanceMap[$txn->id]))
                                ৳ {{ number_format($balanceMap[$txn->id], 2) }}
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 30px;">No transactions found</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        </div>
    </div>

<div class="footer">
    <div class="verified-badge"><i class="fas fa-check-circle"></i> Verified Account</div>
    <div>Generated: {{ \Carbon\Carbon::now()->format('d M Y, h:i A') }}</div>
    <div style="margin-top:4px;">Tori International — Shomriddhi Savings System</div>
</div>

</body>
</html>
