@extends('layouts.app')
@section('title', 'Create Contractor Bill')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Contractor Bill Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('contractor_management.bills.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('contractor_id') ? 'has-error' : '' }}">
                                    <label for="contractor_id">Contractor <span class="text-danger">*</span></label>
                                    <select class="form-control select2" style="width: 100%;" name="contractor_id"
                                        id="contractor_id" required>
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

                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('bill_date') ? 'has-error' : '' }}">
                                    <label for="bill_date">Bill Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control business-date-picker" id="bill_date" name="bill_date"
                                        value="{{ old('bill_date', date('d-m-Y')) }}" required autocomplete="off" readonly>
                                    @error('bill_date')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>



                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('note') ? 'has-error' : '' }}">
                                    <label for="note">Note</label>
                                    <input type="text" value="{{ old('note') }}" name="note" class="form-control" id="note"
                                        placeholder="Enter Note">
                                    @error('note')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped" id="item-table">
                                        <thead>
                                            <tr>
                                                <th width="35%">Expense Head <span class="text-danger">*</span></th>
                                                <th width="25%">Amount <span class="text-danger">*</span></th>
                                                <th width="30%">Remarks</th>
                                                <th width="10%" class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bill-container">
                                            @if(old('expense_head_id'))
                                                @foreach(old('expense_head_id') as $key => $head)
                                                    <tr class="bill-item">
                                                        <td>
                                                            <select name="expense_head_id[]"
                                                                class="form-control select2 expense_head" required>
                                                                <option value="">Select Expense Head</option>
                                                                @foreach($expenseHeads as $expenseHead)
                                                                    <option value="{{ $expenseHead->id }}" {{ $head == $expenseHead->id ? 'selected' : '' }}>{{ $expenseHead->name }}
                                                                        ({{ $expenseHead->code }})</option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="any" class="form-control amount"
                                                                name="amount[]" value="{{ old('amount')[$key] }}" required min="1">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" name="remarks[]"
                                                                value="{{ old('remarks')[$key] }}">
                                                        </td>
                                                        <td class="text-center">
                                                            <a role="button" class="btn btn-danger btn-sm btn-remove"><i
                                                                    class="fa fa-times"></i></a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr class="bill-item">
                                                    <td>
                                                        <select name="expense_head_id[]"
                                                            class="form-control select2 expense_head" required>
                                                            <option value="">Select Expense Head</option>
                                                            @foreach($expenseHeads as $expenseHead)
                                                                <option value="{{ $expenseHead->id }}">{{ $expenseHead->name }}
                                                                    ({{ $expenseHead->code }})</option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <input type="number" step="any" class="form-control amount"
                                                            name="amount[]" value="" required min="1">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" name="remarks[]" value="">
                                                    </td>
                                                    <td class="text-center">
                                                        <a role="button" class="btn btn-danger btn-sm btn-remove"><i
                                                                class="fa fa-times"></i></a>
                                                    </td>
                                                </tr>
                                            @endif
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <th class="text-right">Total Amount</th>
                                                <th id="total-amount">0.00</th>
                                                <th></th>
                                                <th class="text-center">
                                                    <button type="button" class="btn btn-info btn-sm" id="btn-add-more"><i class="fas fa-plus"></i></button>
                                                </th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="button" id="btnPreview" class="btn btn-info bg-gradient-info">Save Bill</button>
                        <button type="submit" id="btnRealSubmit" class="d-none"></button>
                        <a href="{{ route('contractor_management.bills.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
    </div>

    <!-- Template for new row -->
    <template id="bill-template">
        <tr class="bill-item">
            <td>
                <select name="expense_head_id[]" class="form-control expense_head" required>
                    <option value="">Select Expense Head</option>
                    @foreach($expenseHeads as $expenseHead)
                        <option value="{{ $expenseHead->id }}">{{ $expenseHead->name }} ({{ $expenseHead->code }})</option>
                    @endforeach
                </select>
            </td>
            <td>
                <input type="number" step="any" class="form-control amount" name="amount[]" value="" required min="1">
            </td>
            <td>
                <input type="text" class="form-control" name="remarks[]" value="">
            </td>
            <td class="text-center">
                <a role="button" class="btn btn-danger btn-sm btn-remove"><i class="fa fa-times"></i></a>
            </td>
        </tr>
    </template>

    <!-- Preview Modal -->
    <div class="modal fade" id="previewFormModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-info text-white border-0">
                    <h5 class="modal-title font-weight-bold"><i class="fa fa-search-plus mr-2"></i> Preview Bill Data</h5>
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
                                <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-info-circle mr-1"></i> Bill Info</h6>
                                <table class="table table-sm table-borderless mb-4" style="font-size: 14px;">
                                    <tr><td width="30%" class="text-muted">Bill Date</td><td width="70%" class="font-weight-bold text-dark" id="p_bill_date"></td></tr>

                                    <tr><td class="text-muted">Note</td><td class="font-weight-bold text-dark" id="p_note"></td></tr>
                                </table>
                            </div>
                        </div>

                        <h6 class="text-uppercase text-primary font-weight-bold mb-3" style="font-size: 13px; letter-spacing: 1px;"><i class="fa fa-list mr-1"></i> Expense Heads</h6>
                        <table class="table table-bordered table-sm" style="font-size: 14px;">
                            <thead class="bg-light">
                                <tr>
                                    <th>Expense Head</th>
                                    <th>Remarks</th>
                                    <th class="text-right">Amount</th>
                                </tr>
                            </thead>
                            <tbody id="p_bill_items">
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="2" class="text-right">Total Amount:</th>
                                    <th class="text-right" id="p_total_amount">0.00</th>
                                </tr>
                            </tfoot>
                        </table>
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

            $('#btn-add-more').click(function () {
                var html = $('#bill-template').html();
                var row = $(html);
                $('#bill-container').append(row);
                row.find('.expense_head').select2();
                calculateTotal();
            });

            $('body').on('click', '.btn-remove', function () {
                if ($('.bill-item').length > 1) {
                    $(this).closest('tr').remove();
                    calculateTotal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'You must have at least one item!',
                    });
                }
            });

            $('body').on('keyup', '.amount', function () {
                calculateTotal();
            });

            function calculateTotal() {
                var total = 0;
                $('.amount').each(function () {
                    var val = parseFloat($(this).val());
                    if (!isNaN(val)) {
                        total += val;
                    }
                });
                $('#total-amount').text(jsNumberFormat(total.toFixed(2)));
            }

            $('body').on('change', '.expense_head', function () {
                let currentVal = $(this).val();
                if (!currentVal) return;
                
                let currentSelect = $(this);
                let duplicateCount = 0;
                
                $('.expense_head').each(function () {
                    if ($(this).val() == currentVal) {
                        duplicateCount++;
                    }
                });

                if (duplicateCount > 1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Duplicate Head',
                        text: 'This Expense Head is already selected in another row!',
                    });
                    currentSelect.val('').trigger('change');
                }
            });

            calculateTotal();

            $('#btnPreview').click(function() {
                let contractor = $('#contractor_id').val();
                let billDate = $('#bill_date').val();
                let note = $('#note').val();
                let totalAmount = $('#total-amount').text();

                if (!contractor || !billDate) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Data',
                        text: 'Please fill all the required fields (*).',
                    });
                    return;
                }

                let itemsValid = true;
                let itemsHtml = '';
                
                $('.bill-item').each(function() {
                    let headId = $(this).find('.expense_head').val();
                    let headName = $(this).find('.expense_head option:selected').text();
                    let amount = $(this).find('.amount').val();
                    let remarks = $(this).find('input[name="remarks[]"]').val() || '';

                    if (!headId || !amount) {
                        itemsValid = false;
                        return false; // break loop
                    }

                    itemsHtml += `
                        <tr>
                            <td>${headName}</td>
                            <td>${remarks}</td>
                            <td class="text-right">${jsNumberFormat(parseFloat(amount).toFixed(2))}</td>
                        </tr>
                    `;
                });

                if (!itemsValid) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Incomplete Data',
                        text: 'Please fill Expense Head and Amount for all items.',
                    });
                    return;
                }

                $('#p_contractor_name').text($('#contractor_id option:selected').text());
                $('#p_bill_date').text(billDate);

                $('#p_note').text(note || 'N/A');
                
                $('#p_bill_items').html(itemsHtml);
                $('#p_total_amount').text(totalAmount);

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