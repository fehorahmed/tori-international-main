<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cash/Bank Book Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
        }
        @page {
            margin: .3in .5in .5in .5in;
        }
        body {
            background-color: #f4f6f9;
        }
        .report-container {
            background: #fff;
            padding: 1.5rem;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            min-height: 100vh;
        }
        .table th, .table td {
            font-size: 13px;
            padding: 0.4rem;
        }
        @media print {
            .report-container {
                margin: 0;
                padding: 0;
                box-shadow: none;
            }
            .extra-remove {
                display: none !important;
            }
            body {
                background-color: #fff;
            }
            .table-responsive-md {
                overflow-x: visible !important;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Cash_Bank_Book_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                    <div class="row header-common-info">
                        <div class="col-12">
                            <h3 class="text-center" style="font-size: 25px"><u><b>Cash/ Bank Book</b></u></h3>
                            <h3 class="text-center" style="font-size: 25px">Account Name: {{ $selectAccount->name }} - {{ $selectAccount->code }}</h3>
                            <h5 class="text-center" style="font-size: 18px">Date: {{ \Carbon\Carbon::parse(request('start_date'))->format('d-m-Y') }} to {{ \Carbon\Carbon::parse(request('end_date'))->format('d-m-Y') }}</h5>
                        </div>
                    </div>
                    <table id="table" class="table table-bordered">
                        <thead>
                                <th class="text-center">Date</th>
                                <th class="text-center">Particulars</th>
                                <th class="text-center">Payment</th>
                                <th class="text-center">Receipt</th>
                                <th class="text-center">Balance</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $initialOpeningBalance = $openingBalance;
                                $totalCredit = $transactions->where('transaction_type',\App\Enumeration\TransactionType::$CREDIT)->sum('amount');
                                $totalDebit = $transactions->where('transaction_type',\App\Enumeration\TransactionType::$DEBIT)->sum('amount');
                                
                                if ($initialOpeningBalance > 0) {
                                    $totalDebit += $initialOpeningBalance;
                                } elseif ($initialOpeningBalance < 0) {
                                    $totalCredit += abs($initialOpeningBalance);
                                }
                            @endphp
                            <tr>
                                <td class="text-center">{{ request('start_date') }}</td>
                                <td class="text-left">Opening Balance</td>
                                <td class="text-right">
                                    @if($initialOpeningBalance < 0)
                                        {{ number_format(abs($initialOpeningBalance), 2) }}
                                    @endif
                                </td>
                                <td class="text-right">
                                    @if($initialOpeningBalance >= 0)
                                        {{ number_format($initialOpeningBalance, 2) }}
                                    @endif
                                </td>
                                <td class="text-right">{{ wrapNegative($initialOpeningBalance) }}</td>
                            </tr>
                            @php
                                $groupedTransactions = [];
                                foreach($transactions as $tx) {
                                    $date = \Carbon\Carbon::parse($tx->date)->format('d-m-Y');
                                    $purpose = $tx->payment_purpose;
                                    if (!$purpose) {
                                        $purpose = $tx->transaction_type == \App\Enumeration\TransactionType::$DEBIT ? 'other_receipts' : 'other_payments';
                                    }
                                    $groupedTransactions[$date][$purpose][] = $tx;
                                }
                                $groupIndex = 0;
                            @endphp
                            @foreach($groupedTransactions as $date => $purposes)
                                @foreach($purposes as $purpose => $transGroup)
                                    @php
                                        $groupIndex++;
                                        $purposeName = $summary[$purpose]['name'] ?? 'Other / Unknown';
                                        
                                        $groupReceipt = 0;
                                        $groupPayment = 0;
                                        foreach($transGroup as $tx) {
                                             if ($tx->transaction_type == \App\Enumeration\TransactionType::$DEBIT) {
                                                 $groupReceipt += $tx->amount;
                                             } else {
                                                 $groupPayment += $tx->amount;
                                             }
                                        }
                                        
                                        $groupRunningBalance = $openingBalance + $groupReceipt - $groupPayment;
                                    @endphp

                                    <tr class="group-header" data-target=".details-{{ $groupIndex }}" style="cursor: pointer; background-color: #f8f9fa;">
                                        <td class="text-center">{{ $date }}</td>
                                        <td class="text-left text-primary">
                                            <i class="fa fa-plus-square mr-1 icon-toggle"></i> {{ $purposeName }}
                                        </td>
                                        <td class="text-right text-danger">{{ $groupPayment > 0 ? number_format($groupPayment, 2) : '' }}</td>
                                        <td class="text-right text-success">{{ $groupReceipt > 0 ? number_format($groupReceipt, 2) : '' }}</td>
                                        <td class="text-right">{{ wrapNegative($groupRunningBalance) }}</td>
                                    </tr>

                                    @foreach($transGroup as $key => $transaction)
                                    @php
                                        $oppositeType = $transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT 
                                            ? \App\Enumeration\TransactionType::$CREDIT 
                                            : \App\Enumeration\TransactionType::$DEBIT;

                                        $mainTransactionsQuery = $transaction->voucher->transactions ? $transaction->voucher->transactions
                                            ->whereNull('source_account_head_id')
                                            ->where('except_edit',0)
                                            ->whereNull('parent_account_head_id')
                                            ->where('account_head_id','!=',$transaction->account_head_id)
                                            ->where('transaction_type', $oppositeType) : collect([]);

                                        // For Sales Vouchers, COGS and Inventory are paired internally, not opposite to Cash.
                                        if ($transaction->voucher->sale_order_id) {
                                            $cogsId = reservedAccountHeadId(\App\Enumeration\ReservedAccountHead::COST_OF_GOODS_SOLD);
                                            $invId = reservedAccountHeadId(\App\Enumeration\ReservedAccountHead::FINISHED_GOODS_INVENTORY);
                                            $mainTransactionsQuery = $mainTransactionsQuery->whereNotIn('account_head_id', [$cogsId, $invId]);
                                        }

                                        $mainTransactions = $mainTransactionsQuery;
                                        $totalMainAmount = $mainTransactions->sum('amount');
                                        $cashAmount = $transaction->amount;
                                        $isReceipt = $transaction->transaction_type == \App\Enumeration\TransactionType::$DEBIT;
                                    @endphp
                                    
                                    @if($totalMainAmount > 0)
                                        @foreach($mainTransactions as $mainTrans)
                                            @php
                                                $headId = $mainTrans->account_head_id;
                                                $headName = $mainTrans->accountHead->name ?? 'Unknown Head';
                                                if (isset($mainTrans->accountHead->supplier) && $mainTrans->accountHead->supplier->member_no) {
                                                    $headName .= ' - ' . $mainTrans->accountHead->supplier->member_no;
                                                }
                                                $groupName = $mainTrans->accountHead->accountGroup->name ?? '';
                                                $allocatedAmount = ($mainTrans->amount / $totalMainAmount) * $cashAmount;
                                                
                                                if ($isReceipt) {
                                                    $openingBalance += $allocatedAmount;
                                                } else {
                                                    $openingBalance -= $allocatedAmount;
                                                }
                                            @endphp
                                            <tr class="details-{{ $groupIndex }}" style="display: none; background-color: #ffffff;">
                                                <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                                <td class="text-left" style="padding-left: 30px;">
                                                    <a target="_blank" href="{{ route('report.ledger', ['account_head' => $headId, 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'search' => 1]) }}">
                                                        {{ $headName }}
                                                    </a>
                                                    @if($groupName)
                                                        <br><small class="text-muted">Group: {{ $groupName }}</small>
                                                    @endif
                                                    @if($transaction->notes)
                                                        <br><small class="text-muted">{{ $transaction->notes }}</small>
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if(!$isReceipt)
                                                        {{ number_format($allocatedAmount, 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-right">
                                                    @if($isReceipt)
                                                        {{ number_format($allocatedAmount, 2) }}
                                                    @endif
                                                </td>
                                                <td class="text-right">{{ wrapNegative($openingBalance) }}</td>
                                            </tr>
                                        @endforeach
                                    @else
                                        @php
                                            // fallback for transfers etc.
                                            $headName = 'Other / Transfer';
                                            $headId = 0;
                                            if ($transaction->sourceAccountHead) {
                                                $headName = $transaction->sourceAccountHead->name . ' (Transfer In)';
                                            } elseif ($transaction->targetAccountHead) {
                                                $headName = $transaction->targetAccountHead->name . ' (Transfer Out)';
                                            }
                                            
                                            if ($isReceipt) {
                                                $openingBalance += $cashAmount;
                                            } else {
                                                $openingBalance -= $cashAmount;
                                            }
                                        @endphp
                                        <tr class="details-{{ $groupIndex }}" style="display: none; background-color: #ffffff;">
                                            <td class="text-center">{{ \Carbon\Carbon::parse($transaction->date)->format('d-m-Y') }}</td>
                                            <td class="text-left" style="padding-left: 30px;">
                                                {{ $headName }}
                                                @if($transaction->notes)
                                                    <br><small class="text-muted">{{ $transaction->notes }}</small>
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if(!$isReceipt)
                                                    {{ number_format($cashAmount, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-right">
                                                @if($isReceipt)
                                                    {{ number_format($cashAmount, 2) }}
                                                @endif
                                            </td>
                                            <td class="text-right">{{ wrapNegative($openingBalance) }}</td>
                                        </tr>
                                    @endif
                                    @endforeach
                                @endforeach
                            @endforeach


                            <tr>
                                <th colspan="2" class="text-right">Total</th>
                                <th class="text-right">{{ number_format($totalCredit,2) }}</th>
                                <th class="text-right">{{ number_format($totalDebit,2) }}</th>
                                <th class="text-right">{{ wrapNegative($openingBalance) }}</th>
                            </tr>
                        </tbody>
                    </table>
                    

                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var headers = document.querySelectorAll('.group-header');
            headers.forEach(function(header) {
                header.addEventListener('click', function() {
                    var targetClass = this.getAttribute('data-target');
                    var targetElements = document.querySelectorAll(targetClass);
                    
                    targetElements.forEach(function(el) {
                        if (el.style.display === 'none' || el.style.display === '') {
                            el.style.display = 'table-row';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                    
                    var icon = this.querySelector('.icon-toggle');
                    if (icon) {
                        if (icon.classList.contains('fa-plus-square')) {
                            icon.classList.remove('fa-plus-square');
                            icon.classList.add('fa-minus-square');
                        } else {
                            icon.classList.remove('fa-minus-square');
                            icon.classList.add('fa-plus-square');
                        }
                    }
                });
            });
        });
    </script>
</body>
</html>
