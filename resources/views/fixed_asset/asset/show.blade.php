@extends('layouts.app')
@section('title', 'Asset Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 4px;
        }
        .table thead th {
            vertical-align: bottom;
            border-bottom: 2px solid #000000 !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 16px !important;
        }
        @media print {
            .table-responsive {
                overflow-x: visible !important;
            }
            .table-bordered td, .table-bordered th {
                font-size: 16px !important;
            }
            .signature-area {
                position: fixed;
                bottom: 0;
                left: 0;
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Fixed Asset Details</h3>
                    <div>
                        @if($fixedAsset->status === 'active')
                            @if(auth()->user()->can('fixed_asset_sell'))
                                <a href="{{ route('fixed-assets.sell-form', $fixedAsset->id) }}" class="btn btn-success btn-sm"><i class="fas fa-hand-holding-usd"></i> Sell Asset</a>
                            @endif
                            @if(auth()->user()->can('fixed_asset_scrap'))
                                <a href="{{ route('fixed-assets.scrap-form', $fixedAsset->id) }}" class="btn btn-danger btn-sm"><i class="fas fa-dumpster"></i> Scrap</a>
                            @endif
                        @endif
                        <button class="btn btn-info bg-gradient-info btn-sm text-white" onclick="getprint('printArea')" title="Print">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                </div>
            </div>
            
            <div class="card-body">
                <div id="printArea">
                    @include('layouts.partial.__report_header', ['report_title' => 'Fixed Asset Details'])
                    
                    <div class="row pb-3 header-common-info">
                        <div class="col-4">
                            <h5 class="text-left m-0">Asset Code: <b>{{ $fixedAsset->asset_code }}</b></h5>
                        </div>
                        <div class="col-4">
                            <h5 class="text-center m-0">Asset Name: <b>{{ $fixedAsset->name }}</b></h5>
                        </div>
                        <div class="col-4">
                            <h5 class="text-right m-0">Purchase Date: <b>{{ $fixedAsset->date->format('d-M-Y') }}</b></h5>
                        </div>
                    </div>

                    <!-- General Information Table -->
                    <table class="table table-bordered mb-4">
                        <thead class="bg-light">
                            <tr>
                                <th colspan="4" class="text-center">General Information</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="25%">Category</th>
                                <td width="25%">{{ $fixedAsset->category->name ?? '-' }}</td>
                                <th width="25%">Status</th>
                                <td width="25%">{!! $fixedAsset->status_badge !!}</td>
                            </tr>
                            <tr>
                                <th>Location</th>
                                <td>{{ $fixedAsset->location ?? '-' }}</td>
                                <th>Vendor / Supplier</th>
                                <td>{{ $fixedAsset->vendor ?? '-' }}</td>
                            </tr>
                            <tr>
                                <th>Serial No</th>
                                <td>{{ $fixedAsset->serial_no ?? '-' }}</td>
                                <th>Warranty Expiry</th>
                                <td>{{ $fixedAsset->warranty_expiry ? \Carbon\Carbon::parse($fixedAsset->warranty_expiry)->format('d-M-Y') : '-' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Financial Information Table -->
                    <table class="table table-bordered mb-4">
                        <thead class="bg-light">
                            <tr>
                                <th colspan="4" class="text-center">Financial & Depreciation Information</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <th width="25%">Purchase Cost</th>
                                <td width="25%">৳ {{ number_format($fixedAsset->total, 2) }}</td>
                                <th width="25%">Current Book Value</th>
                                <td width="25%" class="text-success font-weight-bold">৳ {{ number_format($fixedAsset->book_value, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Depreciation Method</th>
                                <td>
                                    @if($fixedAsset->depreciation_method === 'none') No Depreciation
                                    @elseif($fixedAsset->depreciation_method === 'SLM') Straight Line ({{ $fixedAsset->useful_life_years }} Years)
                                    @elseif($fixedAsset->depreciation_method === 'WDV') Written Down Value ({{ $fixedAsset->depreciation_rate }}%)
                                    @endif
                                </td>
                                <th>Accumulated Depreciation</th>
                                <td class="text-danger">৳ {{ number_format($fixedAsset->accumulated_depreciation, 2) }}</td>
                            </tr>
                            <tr>
                                <th>Asset Account Head</th>
                                <td>{{ $fixedAsset->accountHead->name ?? '-' }}</td>
                                <th>Purchase Voucher</th>
                                <td>
                                    @if($fixedAsset->purchaseVoucher)
                                        <a href="{{ route('voucher.details', ['voucher' => $fixedAsset->purchaseVoucher->id, 'voucher_type' => $fixedAsset->purchaseVoucher->voucher_type]) }}" target="_blank">{{ $fixedAsset->purchaseVoucher->voucher_no }}</a>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Disposal Information (if applicable) -->
                    @if($fixedAsset->status === 'sold' || $fixedAsset->status === 'scrapped')
                        @if($fixedAsset->disposal)
                        <table class="table table-bordered mb-4">
                            <thead class="bg-light">
                                <tr>
                                    <th colspan="4" class="text-center">Removal / Disposal Information</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th width="25%">Removal Type</th>
                                    <td width="25%">{{ ucfirst($fixedAsset->disposal->disposal_type) }}</td>
                                    <th width="25%">Date</th>
                                    <td width="25%">{{ $fixedAsset->disposal->disposal_date->format('d-M-Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Book Value at Disposal</th>
                                    <td>৳ {{ number_format($fixedAsset->disposal->book_value_at_disposal, 2) }}</td>
                                    @if($fixedAsset->disposal->disposal_type === 'sold')
                                        <th>Sale Amount</th>
                                        <td>৳ {{ number_format($fixedAsset->disposal->sale_amount, 2) }}</td>
                                    @else
                                        <th>Scrap Loss</th>
                                        <td class="text-danger">৳ {{ number_format(abs($fixedAsset->disposal->gain_loss_amount), 2) }}</td>
                                    @endif
                                </tr>
                                @if($fixedAsset->disposal->disposal_type === 'sold')
                                <tr>
                                    <th>{{ $fixedAsset->disposal->gain_loss_amount >= 0 ? 'Gain' : 'Loss' }}</th>
                                    <td class="{{ $fixedAsset->disposal->gain_loss_amount >= 0 ? 'text-success' : 'text-danger' }}">
                                        ৳ {{ number_format(abs($fixedAsset->disposal->gain_loss_amount), 2) }}
                                    </td>
                                    <th>Disposal Voucher</th>
                                    <td>
                                        @if($fixedAsset->disposal->voucher)
                                            <a href="{{ route('voucher.details', ['voucher' => $fixedAsset->disposal->voucher->id, 'voucher_type' => $fixedAsset->disposal->voucher->voucher_type]) }}" target="_blank">{{ $fixedAsset->disposal->voucher->voucher_no }}</a>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                        @endif
                    @endif

                    @if($fixedAsset->notes)
                        <div class="row header-common-info mb-4">
                            <div class="col-12">
                                <p class="mb-0"><strong>Note:</strong> {{ $fixedAsset->notes }}</p>
                            </div>
                        </div>
                    @endif

                    <div class="row signature-area header-common-info mt-5 pt-5">
                        <div class="col text-center">
                            <span style="border-top: 1px solid #000 !important; display: block; padding-top: 5px; font-size: 16px; font-weight: bold; width: 60%; margin: 0 auto;">
                                Created By
                            </span>
                        </div>
                        <div class="col text-center">
                            <span style="border-top: 1px solid #000 !important; display: block; padding-top: 5px; font-size: 16px; font-weight: bold; width: 60%; margin: 0 auto;">
                                Checked By
                            </span>
                        </div>
                        <div class="col text-center">
                            <span style="border-top: 1px solid #000 !important; display: block; padding-top: 5px; font-size: 16px; font-weight: bold; width: 60%; margin: 0 auto;">
                                Authorized Signatory
                            </span>
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
