@extends('layouts.app')
@section('title', 'Create Contractor Payment / Advance')
@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Payment Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('contractor_management.payments.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('contractor_id') ? 'has-error' :'' }}">
                                    <label for="contractor_id">Contractor <span class="text-danger">*</span></label>
                                    <select class="form-control select2" style="width: 100%;" name="contractor_id" id="contractor_id" required>
                                        <option value="">Select Contractor</option>
                                        @foreach($contractors as $contractor)
                                            <option value="{{ $contractor->id }}" {{ old('contractor_id', request('contractor_id')) == $contractor->id ? 'selected' : '' }}>{{ $contractor->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('contractor_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('payment_date') ? 'has-error' :'' }}">
                                    <label for="payment_date">Payment Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control business-date-picker" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required autocomplete="off" readonly>
                                    @error('payment_date')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>



                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('payment_type') ? 'has-error' :'' }}">
                                    <label>Payment Type <span class="text-danger">*</span></label>
                                    <select name="payment_type" class="form-control select2" required>
                                        <option value="1" {{ old('payment_type') == '1' ? 'selected' : '' }}>Payment</option>
                                        <option value="2" {{ old('payment_type') == '2' ? 'selected' : '' }}>Advance</option>
                                    </select>
                                    @error('payment_type')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('payment_method_id') ? 'has-error' :'' }}">
                                    <label>Payment Method (Cash/Bank) <span class="text-danger">*</span></label>
                                    <select class="form-control select2" name="payment_method_id" required>
                                        <option value="">Select Method</option>
                                        @foreach($paymentMethods as $method)
                                            <option value="{{ $method->id }}" {{ old('payment_method_id') == $method->id ? 'selected' : '' }}>{{ $method->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_method_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('amount') ? 'has-error' :'' }}">
                                    <label>Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="any" min="1" value="{{ old('amount') }}" name="amount" class="form-control" placeholder="Enter Amount" required>
                                    @error('amount')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('note') ? 'has-error' :'' }}">
                                    <label>Note</label>
                                    <textarea name="note" class="form-control" rows="3" placeholder="Enter any note...">{{ old('note') }}</textarea>
                                    @error('note')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="btnPreview" class="btn btn-info bg-gradient-info">Save Payment</button>
                        <button type="submit" id="btnRealSubmit" class="d-none"></button>
                        <a href="{{ route('contractor_management.payments.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewFormModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-search-plus mr-2"></i> Preview Payment Data</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0">
                    <div class="p-4 text-center bg-light border-bottom">
                        <h4 class="font-weight-bold mb-1 text-primary" id="p_contractor_name"></h4>
                        <p class="text-muted mb-2">Contractor Name</p>
                    </div>
                    
                    <div class="p-4">
                        <div class="row">
                            <div class="col-md-12">
                                <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i> Payment Info</h6>
                                <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                    <tr><td width="30%" class="text-muted">Payment Date</td><td width="70%" class="font-weight-bold text-dark" id="p_payment_date"></td></tr>

                                    <tr><td class="text-muted">Payment Type</td><td class="font-weight-bold text-dark" id="p_payment_type"></td></tr>
                                    <tr><td class="text-muted">Payment Method</td><td class="font-weight-bold text-dark" id="p_payment_method"></td></tr>
                                    <tr><td class="text-muted">Amount</td><td class="font-weight-bold text-dark" id="p_amount"></td></tr>
                                    <tr><td class="text-muted">Note</td><td class="font-weight-bold text-dark" id="p_note"></td></tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light justify-content-between">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-dismiss="modal"><i class="fa fa-edit"></i> Edit Data</button>
                    <button type="button" class="btn btn-success bg-gradient-success btn-sm font-weight-bold px-4" id="btnConfirmSave"><i class="fa fa-check-circle"></i> Confirm & Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2();

            $('.date-picker').datepicker({
                autoclose: true,
                format: 'yyyy-mm-dd'
            });

            $('#btnPreview').click(function() {
                let contractor = $('#contractor_id').val();
                let paymentDate = $('#payment_date').val();

                let paymentType = $('select[name="payment_type"]').val();
                let paymentMethod = $('select[name="payment_method_id"]').val();
                let amount = $('input[name="amount"]').val();
                let note = $('textarea[name="note"]').val();

                if (!contractor || !paymentDate || !paymentType || !paymentMethod || !amount) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Data',
                        text: 'Please fill all the required fields (*).',
                    });
                    return;
                }

                $('#p_contractor_name').text($('#contractor_id option:selected').text());
                $('#p_payment_date').text(paymentDate);

                $('#p_payment_type').text($('select[name="payment_type"] option:selected').text());
                $('#p_payment_method').text($('select[name="payment_method_id"] option:selected').text());
                $('#p_amount').text(jsNumberFormat(parseFloat(amount).toFixed(2)));
                $('#p_note').text(note || 'N/A');

                $('#previewFormModal').modal('show');
            });

            $('#btnConfirmSave').click(function() {
                $(this).html('<i class="fa fa-spinner fa-spin"></i> Saving...').prop('disabled', true);
                $('#previewFormModal').modal('hide');
                $('#btnRealSubmit').click();
            });
        });
    </script>
@endsection
