@extends('layouts.app')
@section('title','EMI Sale Details')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000 !important;
            padding: 2px;
        }
        @media print {
            @page { size: auto; margin: .5in !important; }
            .signature-area { position: fixed; bottom: 0; left: 0; width: 100%; }
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <a href="#" role="button" onclick="getprint('printArea')" class="btn btn-info bg-gradient-info btn-sm">Print <i class="fa fa-print"></i></a>
                </div>
                <div class="card-body">
                    <div id="printArea">
                        @include('layouts.partial.__report_header')
                        <div class="row pt-3 pb-3">
                            <div class="col-4"><h5>Customer: {{ $emi_sale->customer->name ?? '' }}</h5></div>
                            <div class="col-4 text-center"><h5>EMI ORDER NO. {{ $emi_sale->order_no }}</h5></div>
                            <div class="col-4 text-right"><h5>DATE: {{ \Carbon\Carbon::parse($emi_sale->date)->format('d-m-Y') }}</h5></div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center">S/L</th>
                                <th class="text-center">Brand</th>
                                <th class="text-center">Category</th>
                                <th class="text-center">Product</th>
                                <th class="text-center">Barcode</th>
                                <th class="text-center">Unit</th>
                                <th class="text-center">Quantity</th>
                                <th class="text-center">Unit Price</th>
                                <th class="text-center">Warranty</th>
                                <th class="text-center">Total Price</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($emi_sale->saleOrderItems as $item)
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td class="text-left">{{ $item->product->brand->name ?? '' }}</td>
                                    <td class="text-left">{{ $item->product->productCategory->name ?? '' }}</td>
                                    <td>{{ $item->product->name ?? '' }}</td>
                                    <td class="text-center">{{ $item->product_code }}</td>
                                    <td class="text-center">{{ $item->product->unit->name ?? '' }}</td>
                                    <td class="text-center">{{ number_format($item->quantity) }}</td>
                                    <td class="text-right">{{ number_format($item->selling_price,2) }}</td>
                                    <td class="text-center">
                                        @if($item->product->has_warranty)
                                            <span class="badge {{ $item->hasActiveWarranty() ? 'badge-success' : 'badge-danger' }}">
                                                {{ $item->product->warranty_duration }} {{ $item->product->warranty_type }}(s) (Exp: {{ $item->warranty_expiry_date ? $item->warranty_expiry_date->format('d-m-Y') : 'N/A' }})
                                            </span>
                                        @else
                                            No Warranty
                                        @endif
                                    </td>
                                    <td class="text-right">{{ number_format($item->quantity * $item->selling_price,2) }}</td>
                                </tr>
                            @endforeach
                            </tbody>
                             <tfoot>
                                 <tr>
                                     <th class="text-right" colspan="6">Sub Total</th>
                                     <th class="text-center">{{ number_format($emi_sale->saleOrderItems->sum('quantity')) }}</th>
                                     <th class="text-right" colspan="3">{{ number_format($emi_sale->sub_total,2) }}</th>
                                 </tr>
                                 @if($emi_sale->discount > 0)
                                 <tr>
                                     <th class="text-right" colspan="9">
                                         Discount 
                                         @if($emi_sale->discount_type == 2)
                                             ({{ number_format($emi_sale->discount_percent, 1) }}%)
                                         @else
                                             (Flat)
                                         @endif
                                     </th>
                                     <th class="text-right">- {{ number_format($emi_sale->discount,2) }}</th>
                                 </tr>
                                 @endif
                                 <tr>
                                     <th class="text-right" colspan="9">Total Receivable</th>
                                     <th class="text-right">{{ number_format($emi_sale->total,2) }}</th>
                                 </tr>
                                 <tr>
                                     <th class="text-right" colspan="9">Down Payment</th>
                                     <th class="text-right">{{ number_format($emi_sale->paid,2) }}</th>
                                 </tr>
                                 <tr>
                                     <th class="text-right" colspan="9">EMI Total Amount (Due)</th>
                                     <th class="text-right">{{ number_format($emi_sale->due,2) }}</th>
                                 </tr>
                             </tfoot>
                        </table>

                        @if($emi_sale->notes)
                            <div class="row pt-2 pb-2 header-common-info">
                                <div class="col-12">
                                    <p class="mb-0"><strong>Note:</strong> {{ $emi_sale->notes }}</p>
                                </div>
                            </div>
                        @endif

                        @if($emi_sale->emiSaleDetail)
                        <div class="card card-outline card-success mt-4">
                            <div class="card-header bg-light">
                                <h5 class="card-title text-success font-weight-bold mb-0">
                                    <i class="fa fa-folder-open mr-2"></i>Customer & Guarantor Documents (গ্রাহক, জামিনদার ও আইনি ডকুমেন্টস)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <!-- Customer Documents -->
                                    <div class="col-md-5">
                                        <h6 class="text-primary font-weight-bold mb-2"><i class="fa fa-user mr-1"></i> Customer Documents (গ্রাহকের ডকুমেন্টস)</h6>
                                        <hr class="mt-1 mb-3">
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <strong>Customer NID Scan:</strong><br>
                                                @if($emi_sale->emiSaleDetail->customer_nid_attachment)
                                                    <a href="{{ asset($emi_sale->emiSaleDetail->customer_nid_attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2 btn-block">
                                                        <i class="fa fa-eye mr-1"></i> View NID
                                                    </a>
                                                @else
                                                    <span class="text-muted mt-2 d-inline-block">Not Uploaded</span>
                                                @endif
                                            </div>
                                            <div class="col-6">
                                                <strong>Customer Photo:</strong><br>
                                                @if($emi_sale->emiSaleDetail->customer_photo_attachment)
                                                    <a href="{{ asset($emi_sale->emiSaleDetail->customer_photo_attachment) }}" target="_blank" class="btn btn-outline-primary btn-sm mt-2 btn-block">
                                                        <i class="fa fa-eye mr-1"></i> View Photo
                                                    </a>
                                                @else
                                                    <span class="text-muted mt-2 d-inline-block">Not Uploaded</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Divider -->
                                    <div class="col-md-1 text-center d-none d-md-block" style="border-right: 1px solid #ddd;"></div>

                                    <!-- Guarantor Documents -->
                                    <div class="col-md-6">
                                        <h6 class="text-success font-weight-bold mb-2"><i class="fa fa-user-shield mr-1"></i> Guarantor & Security (জামিনদার ও নিরাপত্তা)</h6>
                                        <hr class="mt-1 mb-3">
                                        <div class="row mb-3">
                                            <div class="col-md-4">
                                                <span class="text-muted">Name:</span> <strong class="d-block">{{ $emi_sale->emiSaleDetail->guarantor_name ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">Phone:</span> <strong class="d-block">{{ $emi_sale->emiSaleDetail->guarantor_phone ?? '-' }}</strong>
                                            </div>
                                            <div class="col-md-4">
                                                <span class="text-muted">NID:</span> <strong class="d-block">{{ $emi_sale->emiSaleDetail->guarantor_nid ?? '-' }}</strong>
                                            </div>
                                        </div>
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <strong>Guarantor NID:</strong><br>
                                                @if($emi_sale->emiSaleDetail->guarantor_nid_attachment)
                                                    <a href="{{ asset($emi_sale->emiSaleDetail->guarantor_nid_attachment) }}" target="_blank" class="btn btn-outline-success btn-xs mt-2 btn-block">
                                                        <i class="fa fa-eye"></i> View NID
                                                    </a>
                                                @else
                                                    <span class="text-muted mt-2 d-inline-block">Not Uploaded</span>
                                                @endif
                                            </div>
                                            <div class="col-4">
                                                <strong>Cheque Scan:</strong><br>
                                                @if($emi_sale->emiSaleDetail->security_cheque_attachment)
                                                    <a href="{{ asset($emi_sale->emiSaleDetail->security_cheque_attachment) }}" target="_blank" class="btn btn-outline-success btn-xs mt-2 btn-block">
                                                        <i class="fa fa-eye"></i> View Cheque
                                                    </a>
                                                @else
                                                    <span class="text-muted mt-2 d-inline-block">Not Uploaded</span>
                                                @endif
                                            </div>
                                            <div class="col-4">
                                                <strong>Agreement:</strong><br>
                                                @if($emi_sale->emiSaleDetail->agreement_attachment)
                                                    <a href="{{ asset($emi_sale->emiSaleDetail->agreement_attachment) }}" target="_blank" class="btn btn-outline-success btn-xs mt-2 btn-block">
                                                        <i class="fa fa-eye"></i> View Agreement
                                                    </a>
                                                @else
                                                    <span class="text-muted mt-2 d-inline-block">Not Uploaded</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <h5 class="mt-4 text-info font-weight-bold">Installment Schedule</h5>
                        <table class="table table-bordered table-striped">
                            <thead class="bg-gradient-navy text-white">
                            <tr>
                                <th class="text-center">Inst. No</th>
                                <th class="text-center">Due Date</th>
                                <th class="text-right">Amount</th>
                                <th class="text-right">Paid Amount</th>
                                <th class="text-center">Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if($loanAccount)
                                @foreach($loanAccount->installments as $schedule)
                                    <tr>
                                        <td class="text-center font-weight-bold">{{ $schedule->installment_no }}</td>
                                        <td class="text-center text-primary font-weight-bold">{{ \Carbon\Carbon::parse($schedule->due_date)->format('d-m-Y') }}</td>
                                        <td class="text-right font-weight-bold">{{ number_format($schedule->total_amount, 2) }}</td>
                                        <td class="text-right text-success font-weight-bold">{{ $schedule->paid_amount > 0 ? number_format($schedule->paid_amount, 2) : '-' }}</td>
                                        <td class="text-center">
                                            @if($schedule->status == 'paid')
                                                <span class="badge badge-success px-2 py-1">Paid</span>
                                            @elseif($schedule->status == 'partial')
                                                <span class="badge badge-info px-2 py-1">Partial</span>
                                            @elseif($schedule->status == 'overdue')
                                                <span class="badge badge-danger px-2 py-1">Overdue</span>
                                            @else
                                                <span class="badge badge-warning px-2 py-1">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($schedule->status != 'paid')
                                                <a href="{{ route('shomriddhi-loan.transactions.create') }}" class="btn btn-sm btn-primary bg-gradient-primary">
                                                    Collect Payment <i class="fa fa-cash-register ml-1"></i>
                                                </a>
                                            @else
                                                <span class="text-success"><i class="fa fa-check-circle fa-lg"></i> Collected</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal removed, payment handled via Loan Collections -->
@endsection
@section('script')
    <script>
        function getprint(print) {
            var APP_URL = '{!! url()->full()  !!}';
            document.title = "{{ $emi_sale->order_no }}_emi_details";
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL);
        }

        $(function() {
            // Note: Payment modal logic has been moved to Shomriddhi Loan Collection page
        });
    </script>
@endsection
