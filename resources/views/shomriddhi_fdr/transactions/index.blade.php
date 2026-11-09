@extends('layouts.app')
@section('title', 'FDR Transactions Ledger')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-exchange-alt mr-2"></i> FDR Transactions Ledger
                </h5>
            </div>

            <div class="card-body">
                <!-- Filter form -->
                <div class="row align-items-end mb-4">
                    <div class="col-md-3">
                        <label for="type">Transaction Type</label>
                        <select name="type" id="typeFilter" class="form-control">
                            <option value="">All Transactions</option>
                            <option value="Deposit">Deposit</option>
                            <option value="Interest Payout">Interest Payout</option>
                            <option value="Encashment">Encashment</option>
                            <option value="Penalty">Penalty</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" id="btnFilter" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
                        <button type="button" id="btnReset" class="btn btn-secondary">Reset</button>
                    </div>
                </div>

                <div class="table-responsive-sm">
                    <table class="table table-bordered table-striped" id="table" style="width: 100%">
                        <thead>
                            <tr>
                                <th width="5%">S/L</th>
                                <th>Date</th>
                                <th>Account No</th>
                                <th>Customer Name</th>
                                <th>Transaction Type</th>
                                <th>Amount</th>
                                <th>Note / Remarks</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        let table = $('#table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: '{{ route('shomriddhi-fdr.transactions.datatable') }}',
                data: function (d) {
                    d.type = $('#typeFilter').val();
                }
            },
            "pagingType": "full_numbers",
            "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'date', name: 'date' },
                { data: 'account_no', name: 'fdr_accounts.fdr_no' },
                { data: 'customer', name: 'customer' },
                { data: 'type', name: 'type' },
                { data: 'amount', name: 'amount' },
                { data: 'note', name: 'note' },
            ],
            "dom": 'lBfrtip',
            "buttons": datatableButtons(),
            "responsive": true, "autoWidth": false,
        });

        $('#btnFilter').click(function() {
            table.draw();
        });

        $('#btnReset').click(function() {
            $('#typeFilter').val('');
            table.draw();
        });
    });
</script>
@endsection
