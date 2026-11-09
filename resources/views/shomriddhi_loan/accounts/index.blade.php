@extends('layouts.app')
@section('title', 'Loan Accounts')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-file-signature mr-2"></i> Loan Accounts List
                </h5>
                <div class="card-tools">
                    <a href="{{ route('shomriddhi-loan.accounts.create') }}" class="btn btn-sm btn-success">
                        <i class="fas fa-plus"></i> Open New Loan Account
                    </a>
                </div>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif
                <div class="table-responsive-sm">
                    <table class="table table-bordered table-striped" id="table" style="width:100%">
                        <thead>
                            <tr>
                                <th>S/L</th>
                                <th>Loan No</th>
                                <th>Customer</th>
                                <th>Type</th>
                                <th>Principal</th>
                                <th>Total Payable</th>
                                <th>Paid</th>
                                <th>Disbursed</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Loan Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog" aria-labelledby="approveModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form id="approveForm" action="" method="POST">
                @csrf
                <div class="modal-header bg-gradient-success">
                    <h5 class="modal-title" id="approveModalLabel">Approve Cash Loan (<span id="modal_loan_no"></span>)</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Disbursement Account (Cash/Bank) <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-control select2" required style="width: 100%;">
                            <option value="">Select Account</option>
                            @foreach($paymentModes as $mode)
                                <option value="{{ $mode->id }}">{{ $mode->name }}</option>
                            @endforeach
                        </select>
                        <small class="text-muted">This account will be credited for the loan disbursement.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve & Disburse</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(function () {
        const table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: '{{ route('shomriddhi-loan.accounts.datatable') }}',
            pagingType: 'full_numbers',
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'loan_no', name: 'loan_no' },
                { data: 'customer', name: 'customer' },
                { data: 'type', name: 'type' },
                { data: 'principal_amount', name: 'principal_amount' },
                { data: 'total_payable', name: 'total_payable' },
                { data: 'paid_amount', name: 'paid_amount' },
                { data: 'disbursement_date', name: 'disbursement_date' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', orderable: false, searchable: false },
            ],
            dom: 'lBfrtip',
            buttons: datatableButtons(),
            responsive: true,
            autoWidth: false,
        });
        
        $('body').on('click', '.btn-approve', function() {
            let id = $(this).data('id');
            let loanNo = $(this).data('loan');
            $('#modal_loan_no').text(loanNo);
            
            let actionUrl = "{{ route('shomriddhi-loan.accounts.approve', ':id') }}".replace(':id', id);
            $('#approveForm').attr('action', actionUrl);
            
            $('#approveModal').modal('show');
        });
        
        if ($.fn.select2) {
            $('.select2').select2({ dropdownParent: $('#approveModal') });
        }
    });
</script>
@endsection
