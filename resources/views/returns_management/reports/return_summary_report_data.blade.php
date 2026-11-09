<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Return Summary Report</title>
    @include('layouts.partial.__styles')
    <style>
        .table-bordered thead td, .table-bordered thead th { vertical-align: middle; border: 1px solid #000!important; padding: 4px; }
        .table-bordered td, .table-bordered th { border: 1px solid #000000!important; vertical-align: middle; padding: 4px; }
        @page { margin: .3in .5in .5in .5in; }
        body { background-color: #f4f6f9; }
        .report-container { background: #fff; padding: 1.5rem; box-shadow: 0 0 10px rgba(0,0,0,0.1); min-height: 100vh; }
        .table th, .table td { font-size: 13px; padding: 0.4rem; }
        @media print {
            .report-container { margin: 0; padding: 0; box-shadow: none; }
            body { background-color: #fff; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="report-container">
                    @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Return_Summary_Report_' . date('d_m_Y')])
                    
                    <div class="table-responsive-md" id="printArea">
                        @include('layouts.partial.__report_header')
                        
                        <div class="row">
                            <div class="col-12 text-center mb-4 mt-2">
                                <h4 style="text-decoration: underline;">Return Summary</h4>
                                <p class="text-muted">From: {{ \Carbon\Carbon::parse($startDate)->format('d M, Y') }} To: {{ \Carbon\Carbon::parse($endDate)->format('d M, Y') }}</p>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-success h-100">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fa fa-undo-alt mr-1"></i> Sales Returns (From Customers)</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="70%">Total Returned Product Value:</th>
                                                <td class="text-right text-success font-weight-bold">{{ number_format($totalSalesReturnVal, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Total Replaced Product Value:</th>
                                                <td class="text-right text-warning font-weight-bold">{{ number_format($totalSalesReplacementVal, 2) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <th>Total Cash Refunded / Adjusted (Net Payable/Receivable):</th>
                                                <td class="text-right text-primary font-weight-bold" style="font-size: 1.1rem;">{{ number_format($totalSalesRefundVal, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="2" class="text-center text-muted" style="font-size: 0.9rem;">
                                                    <hr>
                                                    Total Processed Returns: {{ $salesReturns->count() }}
                                                </th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="card card-outline card-danger h-100">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fa fa-exchange-alt mr-1"></i> Purchase Returns (To Suppliers)</h3>
                                    </div>
                                    <div class="card-body">
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="70%">Total Returned Product Value:</th>
                                                <td class="text-right text-danger font-weight-bold">{{ number_format($totalPurchaseReturnVal, 2) }}</td>
                                            </tr>
                                            <tr class="bg-light">
                                                <th>Total Cash Refunded / Adjusted (Supplier Account):</th>
                                                <td class="text-right text-primary font-weight-bold" style="font-size: 1.1rem;">{{ number_format($totalPurchaseReturnVal, 2) }}</td>
                                            </tr>
                                            <tr>
                                                <th colspan="2" class="text-center text-muted" style="font-size: 0.9rem;">
                                                    <hr>
                                                    Total Processed Returns: {{ $purchaseReturns->count() }}
                                                </th>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-4">
                            <div class="col-md-12">
                                <div class="card bg-info">
                                    <div class="card-body text-center">
                                        <h5>Overall Net Return Difference (Purchase Return - Sales Return)</h5>
                                        <h3 class="font-weight-bold">
                                            {{ number_format($totalPurchaseReturnVal - $totalSalesReturnVal, 2) }} Tk
                                        </h3>
                                        <small>Positive value means you returned more to suppliers than you received from customers.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('layouts.partial.__export_scripts')
</body>
</html>
