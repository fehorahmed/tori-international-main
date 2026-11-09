<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Product Ledger</title>
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
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Product_Ledger_' . date('d_m_Y')])
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header', ['report_title' => 'Product Ledger'])
                        
                        <div class="row header-common-info">
                            <div class="col-12 text-center">
                                <h5 class="m-0">Product: <b>{{ $product->name }} ({{ $product->code }})</b></h5>
                                <h6 class="m-0">Date: <b>{{ $startDate->format('d-m-Y') }}</b> to <b>{{ $endDate->format('d-m-Y') }}</b></h6>
                            </div>
                        </div>

                        <table class="table table-bordered mt-3 text-center">
                            <thead>
                            <tr>
                                <th width="15%" class="text-center">Date</th>
                                <th class="text-left">Description </th>
                                <th width="10%" class="text-center">In</th>
                                <th width="10%" class="text-center">Out</th>
                                <th width="15%" class="text-center">Balance</th>
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                <td>{{ $startDate->format('d-m-Y') }}</td>
                                <td class="text-left font-weight-bold">Opening Balance</td>
                                <td></td>
                                <td></td>
                                <td class="font-weight-bold">{{ number_format($openingBalance, 2) }}</td>
                            </tr>
                            @foreach($logs as $log)
                                @php
                                    $description = '';
                                    if($log->type == \App\Enumeration\InventoryLogType::$PURCHASE_IN) {
                                        $description = 'Purchase Memo No- ' . ($log->purchaseOrder->order_no ?? '');
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$SALE_OUT) {
                                        $memo = !empty($log->saleOrder->memo_no) ? $log->saleOrder->memo_no : ($log->saleOrder->order_no ?? '');
                                        $description = 'Sale Memo No- ' . $memo;
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$SALES_RETURN_IN) {
                                        $memo = !empty($log->saleOrder->memo_no) ? $log->saleOrder->memo_no : ($log->saleOrder->order_no ?? '');
                                        $description = 'Sales Return Memo No- ' . $memo;
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$OPENING_STOCK) {
                                        $description = 'Opening Stock Entry';
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_OUT || $log->type == \App\Enumeration\InventoryLogType::$STOCK_TRANSFER_IN) {
                                        $description = 'Stock Transfer- ' . ($log->stockTransfer->transfer_no ?? '');
                                    } else if($log->type == \App\Enumeration\InventoryLogType::$FINISHED_GOODS_MANUFACTURED) {
                                        $description = 'Finished Goods Manufactured';
                                    } else {
                                        $description = 'Other (Type: '.$log->type.')';
                                    }
                                    
                                    $inQty = in_array($log->type, $inTypes) ? $log->quantity : 0;
                                    $outQty = !in_array($log->type, $inTypes) ? $log->quantity : 0;
                                    
                                    $openingBalance += $inQty;
                                    $openingBalance -= $outQty;
                                @endphp
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($log->date)->format('d-m-Y') }}</td>
                                    <td class="text-left">{{ $description }}</td>
                                    <td class="text-success">{{ $inQty > 0 ? number_format($inQty, 2) : '-' }}</td>
                                    <td class="text-danger">{{ $outQty > 0 ? number_format($outQty, 2) : '-' }}</td>
                                    <td class="font-weight-bold {{ $openingBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ $openingBalance > 0 ? '+' : '' }}{{ number_format($openingBalance, 2) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partial.__scripts')
    @include('layouts.partial.__export_scripts')
</body>
</html>
