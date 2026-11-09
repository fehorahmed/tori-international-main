@extends('layouts.app')
@section('title','Salary Details List')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="employee">Employee <span class="text-danger">*</span></label>
                                <select name="employee" id="employee" class="form-control select2">
                                    <option value="">All Employee</option>
                                    @foreach($employees as $employee)
                                        <option value="{{ $employee->id }}">{{ $employee->name }} ID No. {{ $employee->id_no }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="year">Year</label>
                                <select name="year" id="year" class="form-control select2">
                                    <option value="">All</option>
                                    @for($i=2024;$i <= date('Y');$i++)
                                        <option  value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="month">Month</label>
                                <select  name="month" id="month" class="form-control select2">
                                    <option value="">All</option>
                                    @for ($monthIndex = 1; $monthIndex <= 12; $monthIndex++)
                                        <option value="{{ $monthIndex }}">{{ numberToMonthName($monthIndex) }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input type="button" id="search" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form id="disburse-form">
                        <div class="row mb-4 p-3 bg-light rounded border">
                            <div class="col-md-12">
                                <h5 class="text-info font-weight-bold mb-3">Salary Disbursement (Payment)</h5>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_mode">Payment Mode <span class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-control select2" required>
                                        <option value="">Select Payment Mode</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option value="{{ ($paymentMode->id.'|'.$paymentMode->payment_mode) }}">{{ $paymentMode->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 if_payment_bank_mode" style="display: none">
                                <div class="form-group">
                                    <label for="cheque_no">Cheque No.  <span class="text-danger">*</span></label>
                                    <input type="text" id="cheque_no" class="form-control" name="cheque_no" placeholder="Enter Cheque No.">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="disburse_date">Payment Date <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ date('d-m-Y') }}" id="disburse_date" name="disburse_date" class="form-control business-date-picker" required readonly>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button type="button" id="btn-disburse" class="btn btn-success form-control font-weight-bold">
                                        <i class="fa fa-money-bill"></i> Disburse Selected (<span id="selected-count">0</span>)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="table-responsive">
                        <table id="table" class="table table-bordered">
                            <thead>
                            <tr>
                                <th class="text-center"><input type="checkbox" id="select-all"></th>
                                <th>S/L</th>
                                <th>Employee</th>
                                <th>Year</th>
                                <th>Month</th>
                                <th>Date</th>
                                <th>Total Salary</th>
                                <th>Total Deduction</th>
                                <th>Net Pay</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                            </thead>
                        </table>
                    </div>
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>
    
    <!-- Modal Container for History -->
    <div id="history-modal-container"></div>
@endsection
@section('script')
    <script>
        $(function () {

           let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('salary-processes.details.datatable') }}',
                    data: function (d) {
                        d.salary_process = '{{ $salary_process->id ?? '' }}'
                        d.employee = $("#employee").val()
                        d.year = $("#year").val()
                        d.month = $("#month").val()
                    }
                },
                "pagingType": "full_numbers",
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"]],
                "order": [[2, "asc"]], // Sort by Employee Name by default to avoid sorting the checkbox column
                columns: [
                    {data: 'checkbox', name: 'checkbox', orderable: false, sorting: false, searchable: false, className: 'text-center'},
                    {data: 'id', name: 'id',visible:false},
                    {data: 'employee_name', name: 'employee.name'},
                    {data: 'year', name: 'year'},
                    {data: 'month', name: 'month'},
                    {data: 'process_date', name: 'process_date'},
                    {data: 'gross', name: 'gross',className: 'text-right'},
                    {data: 'total_deduction', name: 'total_deduction',className: 'text-right'},
                    {data: 'net_pay', name: 'net_pay',className: 'text-right'},
                    {data: 'status', name: 'status',className: 'text-center'},
                    {data: 'action', name: 'action', orderable: false,className:'text-center'},
                ],
                "dom": 'lBfrtip',
                "buttons": datatableButtons(),
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('#search').click(function () {
                table.ajax.reload();
            });
            $('#year,#month,#salary_process,#employee').change(function () {
                table.ajax.reload();
            });
            $('body').on('click', '.btn-delete', function () {
                let id = $(this).data('id');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Delete it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "DELETE",
                            url: "{{ route('salary-processes.details.destroy', ['salary_process_item' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
                            data: { id: id }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Deleted!',
                                    response.message,
                                    'success'
                                ).then((result) => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Oops...',
                                    text: response.message,
                                });
                            }
                        });

                    }
                })

            });

            // History Modal Logic
            $('body').on('click', '.btn-history', function (e) {
                e.preventDefault();
                let url = $(this).data('url');
                preloaderToggle(true);
                $.ajax({
                    method: "GET",
                    url: url,
                }).done(function( response ) {
                    preloaderToggle(false);
                    $('#history-modal-container').html(response);
                    $('#historyModal').modal('show');
                }).fail(function() {
                    preloaderToggle(false);
                    Swal.fire('Error', 'Failed to load history.', 'error');
                });
            });

            // Disbursement Logic
            $('#payment_mode').change(function (){
                let paymentMode = $(this).val();
                if (paymentMode != ''){
                    var valuesArray = paymentMode.split('|'); 
                    var mode = valuesArray[1];
                    if (paymentMode != '' && mode == 1){
                        $(".if_payment_bank_mode").show();
                    }else{
                        $(".if_payment_bank_mode").hide();
                    }
                } else {
                    $(".if_payment_bank_mode").hide();
                }
            });
            $('#payment_mode').trigger('change');

            // Select All Checkbox
            $('#select-all').on('click', function() {
                $('.disburse-checkbox').prop('checked', this.checked);
                updateSelectedCount();
            });

            $('body').on('change', '.disburse-checkbox', function() {
                if (!this.checked) {
                    $('#select-all').prop('checked', false);
                }
                updateSelectedCount();
            });

            function updateSelectedCount() {
                $('#selected-count').text($('.disburse-checkbox:checked').length);
            }

            table.on('draw', function() {
                $('#select-all').prop('checked', false);
                updateSelectedCount();
            });

            $('#btn-disburse').click(function() {
                let selectedItems = [];
                $('.disburse-checkbox:checked').each(function() {
                    selectedItems.push($(this).val());
                });

                if (selectedItems.length === 0) {
                    Swal.fire('Warning', 'Please select at least one employee to disburse salary.', 'warning');
                    return;
                }

                let paymentMode = $('#payment_mode').val();
                let disburseDate = $('#disburse_date').val();
                let chequeNo = $('#cheque_no').val();

                if (!paymentMode || !disburseDate) {
                    Swal.fire('Warning', 'Please select Payment Mode and Payment Date.', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Confirm Disbursement',
                    text: "You are about to disburse salary for " + selectedItems.length + " employees. Are you sure?",
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Disburse Now!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "POST",
                            url: "{{ route('salary-processes.disburse') }}",
                            data: {
                                _token: "{{ csrf_token() }}",
                                item_ids: selectedItems,
                                payment_mode: paymentMode,
                                disburse_date: disburseDate,
                                cheque_no: chequeNo
                            }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire('Success!', response.message, 'success').then(() => {
                                    table.ajax.reload();
                                    $('#select-all').prop('checked', false);
                                    updateSelectedCount();
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }).fail(function() {
                            preloaderToggle(false);
                            Swal.fire('Error', 'Something went wrong. Please try again.', 'error');
                        });
                    }
                });
            });
        });
    </script>
@endsection
