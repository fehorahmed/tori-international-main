@extends('layouts.app')
@section('title', 'Sell Asset')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title text-success"><i class="fas fa-dollar-sign"></i> Sell Asset: {{ $fixedAsset->asset_code }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                <!-- Asset Summary Widget -->
                <div class="col-md-4">
                    <div class="card card-outline card-success shadow-sm">
                        <div class="card-header">
                            <h3 class="card-title font-weight-bold">Asset Summary</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-sm">
                                <tbody>
                                    <tr>
                                        <td><strong>Asset Name</strong></td>
                                        <td>{{ $fixedAsset->name }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Original Cost</strong></td>
                                        <td>৳ {{ number_format($fixedAsset->total, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Accumulated Dep.</strong></td>
                                        <td class="text-danger">৳ {{ number_format($fixedAsset->accumulated_depreciation, 2) }}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>Current Book Value</strong></td>
                                        <td class="text-primary font-weight-bold">
                                            ৳ <span id="current_book_value">{{ number_format($fixedAsset->book_value, 2, '.', '') }}</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info shadow-sm mt-3">
                        <i class="fas fa-info-circle"></i> <strong>Accounting Logic:</strong> Selling this asset will automatically generate a JV to credit the Asset account, debit Accumulated Depreciation, debit Cash/Bank for the sale proceeds, and post the difference to Gain/Loss on Asset Sale.
                    </div>
                </div>

                <!-- Sell Form -->
                <div class="col-md-8">
                    <div class="card shadow-sm">
                        <form action="{{ route('fixed-assets.sell-store', $fixedAsset->id) }}" id="sell-form" class="form-horizontal" method="POST">
                            @csrf
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 form-group">
                                        <label>Sale Date <span class="text-danger">*</span></label>
                                        <input type="text" name="disposal_date" class="form-control business-date-picker @error('disposal_date') is-invalid @enderror" value="{{ old('disposal_date') }}" required>
                                        @error('disposal_date') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Receiving Account (Cash/Bank) <span class="text-danger">*</span></label>
                                        <select name="payment_account_head_id" class="form-control select2" required>
                                            <option value="">Select Account</option>
                                            @foreach($paymentHeads as $head)
                                                <option value="{{ $head->id }}">{{ $head->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Sale Amount (Proceeds) <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">৳</span>
                                            </div>
                                            <input type="number" step="0.01" min="0" name="sale_amount" id="sale_amount" class="form-control @error('sale_amount') is-invalid @enderror" value="{{ old('sale_amount') }}" required>
                                        </div>
                                        @error('sale_amount') <span class="invalid-feedback d-block">{{ $message }}</span> @enderror
                                    </div>

                                    <div class="col-md-6 form-group">
                                        <label>Calculated Gain / Loss</label>
                                        <input type="text" id="gain_loss_display" class="form-control" readonly style="font-weight: bold; font-size: 1.1rem;">
                                    </div>

                                    <div class="col-md-12 form-group">
                                        <label>Notes / Reason</label>
                                        <textarea name="notes" class="form-control" rows="3" placeholder="e.g. Sold to ABC Corp">{{ old('notes') }}</textarea>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-body -->
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success bg-gradient-success">Process Sale</button>
                                <a href="{{ route('fixed-assets.show', $fixedAsset->id) }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        const bookValue = parseFloat($('#current_book_value').text());

        $('#sale_amount').on('input', function() {
            let saleAmount = parseFloat($(this).val());
            let display = $('#gain_loss_display');
            
            if (isNaN(saleAmount)) {
                display.val('').removeClass('text-success text-danger bg-light-success bg-light-danger');
                return;
            }

            let diff = saleAmount - bookValue;
            
            if (diff > 0) {
                display.val('Gain: ৳ ' + diff.toFixed(2))
                       .removeClass('text-danger bg-light-danger')
                       .addClass('text-success bg-light-success');
            } else if (diff < 0) {
                display.val('Loss: ৳ ' + Math.abs(diff).toFixed(2))
                       .removeClass('text-success bg-light-success')
                       .addClass('text-danger bg-light-danger');
            } else {
                display.val('No Gain / No Loss')
                       .removeClass('text-success text-danger bg-light-success bg-light-danger');
            }
        });

        // SweetAlert Confirmation
        $('body').on('submit', '#sell-form', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to process the sale of this asset? This will record the financial transaction.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Process Sale!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    });
</script>
<style>
    .bg-light-success { background-color: #d4edda !important; }
    .bg-light-danger { background-color: #f8d7da !important; }
</style>
@endsection
