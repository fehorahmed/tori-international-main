@extends('layouts.app')
@section('title', $pageTitle)
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Due Installments (বকেয়া কিস্তি)</h3>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="table" class="table table-bordered table-striped text-center">
                            <thead>
                            <tr>
                                <th>#</th>
                                <th>Loan No</th>
                                <th>Member</th>
                                <th>Kisti No</th>
                                <th>Due Date</th>
                                <th>Amount</th>
                                <th>Penalty</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Collection Modal -->
    <div class="modal fade" id="collectModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Record Payment</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="collectForm">
                    @csrf
                    <input type="hidden" name="installment_id" id="modal_installment_id">
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Installment Amount</label>
                            <input type="text" id="modal_amount_display" class="form-control" readonly>
                        </div>
                        <div class="form-group">
                            <label>Penalty (Auto Calculated)</label>
                            <input type="number" step="any" name="penalty_amount" id="modal_penalty" class="form-control">
                        </div>
                        <div class="form-group">
                            <label>Total Paid Amount <span class="text-danger">*</span></label>
                            <input type="number" step="any" name="paid_amount" id="modal_total_paid" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Paid Date <span class="text-danger">*</span></label>
                            <input type="text" name="paid_date" value="{{ date('Y-m-d') }}" class="form-control date-picker" autocomplete="off" data-date-format="yyyy-mm-dd" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-success">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            if ($.isFunction($.fn.datepicker)) {
                $('.date-picker').datepicker({
                    autoclose: true,
                    format: 'yyyy-mm-dd',
                    todayHighlight: true
                });
            }

            var table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('microfinance.collection.datatable') }}',
                columns: [
                    {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
                    {data: 'loan.loan_no', name: 'loan.loan_no'},
                    {data: 'loan.member.name', name: 'loan.member.name'},
                    {data: 'installment_no', name: 'installment_no'},
                    {data: 'due_date', name: 'due_date'},
                    {data: 'total_due', name: 'total_due'},
                    {data: 'calculated_penalty', name: 'calculated_penalty'},
                    {data: 'action', name: 'action', orderable: false, searchable: false}
                ]
            });

            $('body').on('click', '.btn-collect', function () {
                var id = $(this).data('id');
                var amount = parseFloat($(this).data('amount'));
                var penalty = parseFloat($(this).data('penalty'));

                $('#modal_installment_id').val(id);
                $('#modal_amount_display').val(amount.toFixed(2));
                $('#modal_penalty').val(penalty.toFixed(2));
                $('#modal_total_paid').val((amount + penalty).toFixed(2));
                
                $('#collectModal').modal('show');
            });

            $('#collectForm').submit(function (e) {
                e.preventDefault();
                $.ajax({
                    method: "POST",
                    url: "{{ route('microfinance.collection.collect') }}",
                    data: $(this).serialize()
                }).done(function( response ) {
                    if (response.success) {
                        $('#collectModal').modal('hide');
                        Swal.fire('Success!', response.message, 'success');
                        table.ajax.reload();
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                });
            });
        });
    </script>
@endsection
