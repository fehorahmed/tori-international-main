@extends('layouts.app')
@section('title', $pageTitle)

@section('content')
    <div class="row">
        <div class="col-12">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible"><button type="button" class="close" data-dismiss="alert">&times;</button>{{ session('error') }}</div>
            @endif

            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Employee Incentive List</h3>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-3">
                            <label>Start Date</label>
                            <input type="text" id="filter_start_date" class="form-control date-picker" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-3">
                            <label>End Date</label>
                            <input type="text" id="filter_end_date" class="form-control date-picker" placeholder="dd-mm-yyyy">
                        </div>
                        <div class="col-md-3">
                            <label>Role</label>
                            <select id="filter_role" class="form-control select2">
                                <option value="">All Roles</option>
                                <option value="sr">SR (Sales Representatative)</option>
                                <option value="branch_manager">Branch Manager</option>
                                <option value="area_manager">Area Manager</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label>&nbsp;</label>
                            <button id="btn_filter" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Filter</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="employeeIncentiveDT" class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Employee</th>
                                    <th>Total Earned</th>
                                    <th>Total Paid</th>
                                    <th>Due Incentive</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

{{-- ─── Payment Modal ─────────────────────────────────────── --}}
<div class="modal fade" id="paymentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title"><i class="fas fa-hand-holding-usd mr-2"></i>Pay Incentive</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('incentive-payments.store') }}" method="POST" id="paymentForm">
                @csrf
                <div class="modal-body">
                    <input type="hidden" name="employee_id" id="pay_employee_id">
                    <input type="hidden" name="start_date" id="pay_start_date">
                    <input type="hidden" name="end_date" id="pay_end_date">
                    <input type="hidden" name="role_type" id="pay_role_type">
                    
                    <div class="form-group">
                        <label>Due Incentive Balance</label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                            <input type="text" id="pay_due_balance" class="form-control font-weight-bold text-success" readonly>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Payment Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                            <input type="number" step="0.01" min="1" name="amount" id="pay_amount" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Cash / Bank Account <span class="text-danger">*</span></label>
                        <select name="account_head_id" class="form-control" required>
                            <option value="">-- Select Account --</option>
                            @foreach($accountHeads as $head)
                                <option value="{{ $head->id }}">{{ $head->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="mobile_banking">Mobile Banking</option>
                        </select>
                    </div>

                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Reference No</label>
                            <input type="text" name="reference_no" class="form-control" placeholder="Check/TRX No">
                        </div>
                    </div>

                    <div class="form-group mb-0">
                        <label>Note</label>
                        <textarea name="note" class="form-control" rows="2" placeholder="Optional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check-circle"></i> Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>



@endsection

@section('script')
<script>
$(function () {
    // ─── DataTable ────────────────────────────────────────────────
    let table = $('#employeeIncentiveDT').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '{{ route('incentive-payments.datatable') }}',
            data: function (d) {
                d.start_date = $('#filter_start_date').val();
                d.end_date   = $('#filter_end_date').val();
                d.role       = $('#filter_role').val();
            }
        },
        "pagingType": "full_numbers",
        "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
        columns: [
            { data: 'DT_RowIndex', orderable: false, searchable: false },
            { data: 'employee_name', name: 'employee.name' },
            { data: 'total_earned', orderable: false, searchable: false },
            { data: 'total_paid', orderable: false, searchable: false },
            { data: 'due', orderable: false, searchable: false },
            { data: 'action', orderable: false, searchable: false }
        ],
        "dom": 'lBfrtip',
        "buttons": datatableButtons(),
        "responsive": true, "autoWidth": false, "colReorder": true,
    });

    $('#btn_filter').click(function() {
        table.ajax.reload();
    });

    // ─── Pay Button Click ─────────────────────────────────────────
    $(document).on('click', '.pay-btn', function() {
        const empId = $(this).data('emp');
        const due = parseFloat($(this).data('due')).toFixed(2);
        
        $('#pay_employee_id').val(empId);
        $('#pay_start_date').val($('#filter_start_date').val());
        $('#pay_end_date').val($('#filter_end_date').val());
        $('#pay_role_type').val($('#filter_role').val());
        $('#pay_due_balance').val(due);
        $('#pay_amount').val(due);
        $('#pay_amount').attr('max', due);
        
        $('#paymentModal').modal('show');
    });

    // ─── Payment Form Submit Validation ───────────────────────────
    $('#paymentForm').on('submit', function (e) {
        const due = parseFloat($('#pay_due_balance').val()) || 0;
        const amount = parseFloat($('#pay_amount').val()) || 0;

        if (amount <= 0) { 
            e.preventDefault(); 
            toastr.error('Amount must be greater than zero.'); 
        } else if (amount > due) { 
            e.preventDefault(); 
            toastr.error('Payment amount cannot exceed the due balance!'); 
        }
    });
});
</script>
@endsection
