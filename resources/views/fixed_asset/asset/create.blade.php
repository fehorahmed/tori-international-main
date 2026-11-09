@extends('layouts.app')
@section('title', 'Create Asset')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">New Asset Purchase Entry</h3>
            </div>
            <!-- form start -->
            <form action="{{ route('fixed-assets.store') }}" class="form-horizontal" id="asset-form" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="card-body">
                        <div class="row">
                            <!-- Category Selection -->
                            <div class="col-md-4 form-group">
                                <label>Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-control select2" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}" 
                                            {{ old('category_id') == $category->id ? 'selected' : '' }}
                                            data-method="{{ $category->depreciation_method }}"
                                            data-rate="{{ $category->depreciation_rate }}"
                                            data-life="{{ $category->useful_life_years }}">
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Asset Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="e.g. Wood Chair" required>
                                @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-2 form-group">
                                <label>Quantity <span class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="quantity" class="form-control @error('quantity') is-invalid @enderror" value="{{ old('quantity', 1) }}" min="1" max="500" required>
                                <small class="text-muted">Batch generation</small>
                            </div>

                            <div class="col-md-2 form-group">
                                <label>Unit Cost <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" name="total" id="total" class="form-control @error('total') is-invalid @enderror" value="{{ old('total') }}" required>
                                @error('total') <span class="invalid-feedback">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Total Cost</label>
                                <input type="text" id="total_cost" class="form-control" readonly style="font-weight: bold; background: #e9ecef;">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Date <span class="text-danger">*</span></label>
                                <input type="text" name="date" class="form-control @error('date') is-invalid @enderror business-date-picker" value="{{ old('date', date('Y-m-d')) }}" required>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Paid From (Cash/Bank) <span class="text-danger">*</span></label>
                                <select name="payment_account_head_id" class="form-control select2" required>
                                    <option value="">Select Account</option>
                                    @foreach($paymentHeads as $head)
                                        <option value="{{ $head->id }}" {{ old('payment_account_head_id') == $head->id ? 'selected' : '' }}>{{ $head->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Location</label>
                                <input type="text" name="location" class="form-control @error('location') is-invalid @enderror" value="{{ old('location') }}" placeholder="e.g. Reception">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Vendor / Supplier</label>
                                <input type="text" name="vendor" class="form-control @error('vendor') is-invalid @enderror" value="{{ old('vendor') }}">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Warranty Expiry</label>
                                <input type="text" name="warranty_expiry" class="form-control @error('warranty_expiry') is-invalid @enderror date-picker" value="{{ old('warranty_expiry') }}">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Serial No <small>(Only for qty=1)</small></label>
                                <input type="text" name="serial_no" id="serial_no" class="form-control @error('serial_no') is-invalid @enderror" value="{{ old('serial_no') }}">
                            </div>
                        </div>

                        <!-- Depreciation Override Section -->
                        <h5 class="mt-4 mb-3 border-bottom pb-2">Depreciation Configuration <small class="text-muted">(Auto-filled from Category)</small></h5>
                        <div class="row bg-light p-3 rounded mb-3">
                            <div class="col-md-3 form-group">
                                <label>Depreciation Method</label>
                                <select name="depreciation_method" id="depreciation_method" class="form-control select2" required>
                                    <option value="SLM" {{ old('depreciation_method') == 'SLM' ? 'selected' : '' }}>Straight Line Method (SLM)</option>
                                    <option value="WDV" {{ old('depreciation_method') == 'WDV' ? 'selected' : '' }}>Written Down Value (WDV)</option>
                                    <option value="none" {{ old('depreciation_method') == 'none' ? 'selected' : '' }}>No Depreciation</option>
                                </select>
                            </div>

                            <div class="col-md-3 form-group wdv-group">
                                <label>Rate (%)</label>
                                <input type="number" step="0.01" name="depreciation_rate" id="depreciation_rate" class="form-control" value="{{ old('depreciation_rate') }}">
                            </div>

                            <div class="col-md-3 form-group slm-group">
                                <label>Useful Life (Years)</label>
                                <input type="number" name="useful_life_years" id="useful_life_years" class="form-control" value="{{ old('useful_life_years') }}">
                            </div>

                            <div class="col-md-3 form-group slm-group">
                                <label>Salvage Value (Total)</label>
                                <input type="number" step="0.01" name="salvage_value" class="form-control" value="{{ old('salvage_value', 0) }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label>Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                    <button type="submit" class="btn btn-info bg-gradient-info">Save</button>
                    <a href="{{ route('fixed-assets.index') }}" class="btn btn-danger bg-gradient-danger float-right">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function () {
        // Calculate Total Cost
        function calculateTotal() {
            let qty = parseFloat($('#quantity').val()) || 0;
            let cost = parseFloat($('#total').val()) || 0;
            let total = qty * cost;
            $('#total_cost').val(total.toFixed(2));
            
            // Disable serial no if qty > 1
            if (qty > 1) {
                $('#serial_no').val('').prop('readonly', true);
            } else {
                $('#serial_no').prop('readonly', false);
            }
        }
        $('#quantity, #total').on('input', calculateTotal);
        calculateTotal();

        // Load Category Defaults
        $('#category_id').change(function() {
            let option = $(this).find(':selected');
            if (option.val()) {
                $('#depreciation_method').val(option.data('method')).trigger('change');
                $('#depreciation_rate').val(option.data('rate'));
                $('#useful_life_years').val(option.data('life'));
            }
        });

        // Toggle Depreciation Fields
        function toggleDep() {
            let method = $('#depreciation_method').val();
            if (method === 'SLM') {
                $('.slm-group').show();
                $('.wdv-group').hide();
            } else if (method === 'WDV') {
                $('.slm-group').hide();
                $('.wdv-group').show();
            } else {
                $('.slm-group').hide();
                $('.wdv-group').hide();
            }
        }
        $('#depreciation_method').change(toggleDep);
        toggleDep();

        // Form Submit Confirmation
        $('body').on('submit', '#asset-form', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to save this asset and record the financial transaction?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Save it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            })
        });
    });
</script>
@endsection
