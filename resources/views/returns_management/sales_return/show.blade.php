@extends('layouts.app')
@section('title', 'Sales Return Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th { border: 1px solid #000000 !important; padding: 5px; }
        @media print {
            @page { size: auto; margin: .5in !important; }
            .btn-print { display: none; }
            .main-footer, .content-header, .card-header { display: none !important; }
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header text-right d-none">
                <a href="{{ route('returns-management.sales-returns.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left"></i> Back</a>
                <button type="button" onclick="window.print()" class="btn btn-info btn-sm btn-print"><i class="fa fa-print"></i> Print</button>
            </div>
            <div class="card-header border-bottom-0">
                @include('layouts.partial.__export_buttons', ['printAreaId' => 'printArea', 'fileName' => 'Sales_Report_' . date('d_m_Y')])
            </div>
            <div class="card-body">
                <div id="printArea">
                    @include('layouts.partial.__report_header')
                    <div class="text-center mt-3 mb-4">
                        <h4 style="text-decoration: underline; font-weight: bold;">
                            SALES RETURN INVOICE
                        </h4>
                        @if($salesReturn->trashed())
                            <h4 class="text-danger font-weight-bold mt-2">-- CANCELLED / VOIDED --</h4>
                        @endif
                    </div>

                    <div class="row mb-4">
                        <div class="col-4">
                            <strong>Customer:</strong> {{ $salesReturn->customer->member_no ?? '' }} - {{ $salesReturn->customer->name ?? 'N/A' }}<br>
                            <strong>Phone:</strong> {{ $salesReturn->customer->mobile_no ?? 'N/A' }}
                        </div>
                        <div class="col-4">
                            <strong>Return No:</strong> {{ $salesReturn->return_no }}<br>
                            <strong>Sales Invoice No:</strong> {{ $salesReturn->saleOrder->order_no ?? 'N/A' }}
                        </div>
                        <div class="col-4">
                            <strong>Date:</strong> {{ \Carbon\Carbon::parse($salesReturn->date)->format('d-m-Y') }}<br>
                            <strong>Warehouse:</strong> {{ $salesReturn->returnWarehouse->name ?? 'N/A' }}<br>
                            <!-- <strong>Status:</strong> <span class="badge badge-success">Approved</span> -->
                        </div>
                    </div>

                    <!-- Return Items -->
                    @php $returnItems = $salesReturn->items->where('is_replacement', false); @endphp
                    @if($returnItems->count() > 0)
                        <h5 class="font-weight-bold">1. Returned Items</h5>
                        <table class="table table-bordered mb-4">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">Sl</th>
                                    <th>Product</th>
                                    <th class="text-center">Returned Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($returnItems as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Replacement Items -->
                    @php $replacementItems = $salesReturn->items->where('is_replacement', true); @endphp
                    @if($replacementItems->count() > 0)
                        <h5 class="font-weight-bold">2. Replacement Items</h5>
                        <table class="table table-bordered mb-4">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center">Sl</th>
                                    <th>Product</th>
                                    <th class="text-center">Batch</th>
                                    <th class="text-center">Replacement Qty</th>
                                    <th class="text-right">Unit Price</th>
                                    <th class="text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($replacementItems as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $item->product->name ?? '' }}</td>
                                        <td class="text-center">{{ $item->inventory->batch_no ?? '' }}</td>
                                        <td class="text-center">{{ number_format($item->quantity, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-right">{{ number_format($item->quantity * $item->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif

                    <!-- Summary -->
                    <div class="row">
                        <div class="col-6 offset-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th class="text-right">Total Return Value</th>
                                    <td class="text-right">{{ number_format($salesReturn->total_return_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-right">Deduction Amount</th>
                                    <td class="text-right text-danger">- {{ number_format($salesReturn->return_deduction, 2) }}</td>
                                </tr>
                                <tr>
                                    <th class="text-right">Total Replacement Value</th>
                                    <td class="text-right">{{ number_format($salesReturn->total_replacement_amount, 2) }}</td>
                                </tr>
                                <tr class="bg-light font-weight-bold">
                                    <th class="text-right">
                                        Net Balance<br>
                                        <small class="{{ $salesReturn->net_payable_or_receivable > 0 ? 'text-success' : ($salesReturn->net_payable_or_receivable < 0 ? 'text-danger' : 'text-info') }}">
                                            {{ $salesReturn->net_payable_or_receivable > 0 ? '(Receivable from Customer)' : ($salesReturn->net_payable_or_receivable < 0 ? '(Payable to Customer)' : '(Adjusted)') }}
                                        </small>
                                    </th>
                                    <td class="text-right align-middle">{{ number_format(abs($salesReturn->net_payable_or_receivable), 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="row mt-5 pt-5 signature-area">
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid #000; width: 60%;">
                            <p>Customer Signature</p>
                        </div>
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid #000; width: 60%;">
                            <p>Prepared By ({{ $salesReturn->createUser->name ?? '' }})</p>
                        </div>
                        <div class="col-4 text-center">
                            <hr style="border-top: 1px solid #000; width: 60%;">
                            <p>Authorized Signature</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
    @include('layouts.partial.__export_scripts')
@endsection