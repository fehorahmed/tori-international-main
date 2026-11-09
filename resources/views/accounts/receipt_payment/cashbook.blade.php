@extends('layouts.app')
@section('title','Cashbook')
@section('content')

    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <form action="{{ route('cashbook') }}">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <button type="button" style="margin-top: 23px;" class="btn btn-outline-primary btn-block">Remaining Balance: <b class="badge badge-primary">{{ number_format($balance,2) }}</b></button>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_head" class="col-form-label">Payment Head <span
                                            class="text-danger">*</span></label>
                                    <select required name="payment_head" id="payment_head" class="form-control select2">
                                        <option value="">Select Payment Head</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option {{ request('payment_head') == $paymentMode->id ? 'selected' : '' }} value="{{ $paymentMode->id }}">{{ $paymentMode->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="date" class="col-form-label">Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('date',date('d-m-Y')) }}"
                                           name="date" class="form-control date-picker" id="date"
                                           placeholder="Enter Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" id="search_btn" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    @if(request('payment_head') != '')
                        @include('layouts.partial.__report_header')
                        <div class="row">
                            <div class="col-md-6">
                                <div class="table-responsive-sm">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="text-center" colspan="4"><h5>Debit</h5></th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Particulars</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $debitTotal = $openingBalance;
                                        @endphp
                                        @if($openingBalance > 0)
                                            <tr>
                                                <th class="text-left">Opening Balance </th>
                                                <th class="text-center">{{ number_format($openingBalance,2) }}</th>
                                                <th class="text-center">{{ number_format($openingBalance,2) }}</th>
                                                <th class="text-center"></th>
                                            </tr>
                                        @endif
                                        @foreach($debitTransactions as $debitTransaction)
                                            @php
                                                $debitTotal += $debitTransaction->amount;
                                        $sourceOfFund = \App\Models\Account\Transaction::
                                                            where('transaction_type',\App\Enumeration\TransactionType::$CREDIT)
                                                            ->where('voucher_id',$debitTransaction->voucher_id)
                                                            //->where('voucher_type',\App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                                            ->first();
                                            @endphp
                                            <tr>
                                                <td class="text-left">{{ $sourceOfFund->accountHead->name ?? '' }}
                                                    @if($debitTransaction->notes != '')
                                                        ({{ $debitTransaction->notes }})
                                                    @endif
                                                </td>
                                                <td class="text-center">{{ number_format($debitTransaction->amount,2) }}</td>
                                                <td class="text-center">{{ number_format($debitTotal,2) }}</td>
                                                <td class="text-center">
                                                    @if(auth()->user()->can('cashbook_add_fund_delete'))
                                                        <button type="button" data-id="{{ $debitTransaction->voucher_id }}" class="btn btn-danger bg-gradient-danger btn-xs btn-voucher-delete"><i class="fa fa-trash"></i></button>
                                                    @endif

                                                    @if(auth()->user())
                                                        <button onclick="modifyDebitTransaction({{ $debitTransaction->voucher_id }}, '{{ $debitTransaction->notes }}', {{ $debitTransaction->amount }},{{$sourceOfFund->accountHead->id}},'{{$debitTransaction->date}}')" type="button" class="btn btn-info bg-gradient-info btn-xs btn-voucher-modify"><i class="fa fa-edit"></i></button>
                                                    @endif
                                                </td>



                                            </tr>
                                        @endforeach
                                        </tbody>
                                        @if(auth()->user()->can('cashbook_add_fund'))
                                            <tfoot>
                                            <tr>
                                                <th colspan="4">
                                                    <button type="button" id="add_fund" class="btn btn-primary bg-primary btn-sm">Add Fund  <i class="fa fa-plus"></i></button>
                                                </th>
                                            </tr>
                                            </tfoot>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="table-responsive-sm">
                                    <table class="table table-bordered">
                                        <thead>
                                        <tr>
                                            <th class="text-center" colspan="4"><h5>Credit</h5></th>
                                        </tr>
                                        <tr>
                                            <th class="text-center">Particulars</th>
                                            <th class="text-center">Amount</th>
                                            <th class="text-center">Total</th>
                                            <th class="text-center">Action</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $creditTotal = 0;
                                        @endphp
                                        @foreach($creditTransactions as $creditTransaction)
                                            @php
                                                $creditTotal += $creditTransaction->amount;
                                            @endphp
                                            <tr>
                                                <td class="text-left">{{ $creditTransaction->accountHead->name ?? '' }}
                                                    @if($creditTransaction->notes != '')
                                                        ({{ $creditTransaction->notes }})
                                                    @endif
                                                </td>
                                                <td class="text-right">{{ number_format($creditTransaction->amount,2) }}</td>
                                                <td class="text-right">{{ number_format($creditTotal,2) }}</td>
                                                <td class="text-center">
                                                    @if($creditTransaction->voucher->supporting_document ?? false)
                                                        <a href="{{ asset($creditTransaction->voucher->supporting_document ?? '') }}" download="" class="btn btn-primary bg-primary btn-xs"><i class="fa fa-file"></i></a>
                                                    @endif
                                                    @if(auth()->user()->can('cashbook_add_expense_delete'))
                                                        <button type="button" data-id="{{ $creditTransaction->voucher_id }}" class="btn btn-danger bg-gradient-danger btn-xs btn-voucher-delete"><i class="fa fa-trash"></i></button>
                                                    @endif

                                                    @if(auth()->user())
                                                        <button onclick="modifyCreditTransaction({{ $creditTransaction->voucher_id }}, '{{ $creditTransaction->notes }}', {{ $creditTransaction->amount }} ,{{ $creditTransaction->account_head_id }}, '{{ date('d-m-Y',strtotime($creditTransaction->date)) }}' )" type="button" class="btn btn-info bg-gradient-info btn-xs btn-voucher-modify"><i class="fa fa-edit"></i></button>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                        <tfoot>
                                        <tr>
                                            <th class="text-left">Closing Balance</th>
                                            <th class="text-right">{{ number_format(($debitTotal - $creditTotal),2) }}</th>
                                            @php
                                                @endphp
                                            <th class="text-right">{{ number_format(($debitTotal - $creditTotal) + $creditTotal,2) }}</th>
                                            <th class="text-right"></th>
                                        </tr>
                                        <tr>
                                            <th colspan="4">
                                                @if(auth()->user()->can('cashbook_add_expense'))
                                                    <button type="button" id="add_payment" class="btn btn-primary bg-primary btn-sm">Add Expense <i class="fa fa-plus"></i></button>
                                                @endif
                                            </th>
                                        </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
                <!-- /.card-body -->
            </div>
        </div>
    </div>

    {{-- credit modal --}}

    <div class="modal fade" id="modal-credit-modify-transaction" data-backdrop="static">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modify Transaction</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modify-transaction-form-credit" method="post">

                        @csrf

                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="hidden" name="particular_head" value="{{ request('payment_head') }}">
                                <label for="particular_head">Particulars <span class="text-danger">*</span></label>
                                <select name="particular_head" id="particular_head" class="form-control select2">
                                    <option    value="">Select Particular Head</option>
                                    @foreach($expenseHeads as $expenseHead)
                                        <option
                                            value="{{ $expenseHead->id }}">{{ $expenseHead->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-message" id="particular_head-error"></span>
                            </div>
                        </div>

                        {{--                        <div class="col-md-12">--}}
                        {{--                            <div class="form-group">--}}
                        {{--                                <input type="hidden" name="fund_target" value="{{ request('payment_account_head_id') }}">--}}
                        {{--                                <label for="payment_account_head_id">Source of Fund <span class="text-danger">*</span></label>--}}
                        {{--                                <select name="payment_account_head_id"  class="form-control select2">--}}
                        {{--                                    <option id="payment_account_head_id" value="">Select Source of Fund</option>--}}
                        {{--                                    @foreach($fundSources as $fundSource)--}}
                        {{--                                        <option--}}
                        {{--                                            value="{{ $fundSource->id }}">{{ $fundSource->name }}</option>--}}
                        {{--                                    @endforeach--}}
                        {{--                                </select>--}}
                        {{--                                <span class="text-danger error-message" id="payment_account_head_id-error"></span>--}}
                        {{--                            </div>--}}
                        {{--                        </div>--}}

                        <input type="hidden" id="modify-voucher-credit-id" name="voucher_id">
                        <div class="form-group">
                            <label for="modify-notes">Notes</label>
                            <input type="text" id="modify-notes-credit" class="form-control" name="notes">
                        </div>
                        <div class="form-group">
                            <label for="modify-amount">Amount</label>
                            <input type="number" id="modify-amount-credit" class="form-control" name="amount">
                        </div>

                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="supporting_document">Supporting Document</label>
                                <input type="file" id="supporting_document" class="form-control"
                                       name="supporting_document">
                                <span class="text-danger error-message" id="supporting_document-error"></span>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group" >
                                <label for="expense_date">Date <span
                                        class="text-danger">*</span></label>
                                <input id="modify-date"  type="text"
                                       class="form-control date-picker"
                                       name="date"
                                       placeholder="Enter Date">
                                <span class="text-danger error-message" id="date-error"></span>
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-save-modified-transaction-credit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- debit modal -->
    <div class="modal fade" id="modal-dabit-modify-transaction" data-backdrop="static">
        <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Modify Transaction</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="modify-transaction-form-debit"  method="post">
                        @csrf
                        <input type="hidden" id="modify-voucher-debit-id" name="voucher_id">

                        <div class="col-md-12">
                            <div class="form-group">
                                <input type="hidden" name="fund_target" value="{{ request('payment_head') }}">
                                <label for="payment_account_head_id">Source of Fund <span class="text-danger">*</span></label>
                                <select name="payment_account_head_id" id="payment_account_head_id"  class="form-control select2">
                                    <option value="">Select Source of Fund</option>
                                    @foreach($fundSources as $fundSource)
                                        <option
                                            value="{{ $fundSource->id }}">{{ $fundSource->name }}</option>
                                    @endforeach
                                </select>
                                <span class="text-danger error-message" id="payment_account_head_id-error"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="modify-amount">Amount</label>
                            <input type="number" id="modify-debit-amount-credit" class="form-control" name="amount">
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="modify-debit-notes-credit">Extra Information</label>
                                <textarea id="modify-debit-notes-credit" class="form-control"
                                          name="notes"
                                          placeholder="Enter Extra Information"></textarea>
                                <span class="text-danger error-message" id="modify-debit-notes-credit-error"></span>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="modify-dabit-date">Date <span
                                        class="text-danger">*</span></label>
                                <input type="text" value="{{ date('d-m-Y') }}"
                                       class="form-control date-picker" id="modify-dabit-date"
                                       name="date"
                                       placeholder="Enter Date">
                                <span class="text-danger error-message" id="date-error"></span>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                    <button type="button" id="btn-save-modified-transaction-debit" class="btn btn-primary">Save Changes</button>
                </div>
            </div>
        </div>
    </div>

    @if(auth()->user()->can('cashbook_add_fund'))
        <div class="modal fade" id="modal-fund" data-backdrop="static" >
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Fund</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="add-fund-form" method="POST"
                              action="{{ route('cashbook.add_fund') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="hidden" name="fund_target" value="{{ request('payment_head') }}">
                                        <label for="fund_source">Source of Fund <span class="text-danger">*</span></label>
                                        <select name="fund_source" id="fund_source" class="form-control select2">
                                            <option value="">Select Source of Fund</option>
                                            @foreach($fundSources as $fundSource)
                                                <option
                                                    value="{{ $fundSource->id }}">{{ $fundSource->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-message" id="fund_source-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="fund_amount">Amount <span
                                                class="text-danger">*</span></label>
                                        <input type="number" id="fund_amount" class="form-control"
                                               name="fund_amount"
                                               placeholder="Enter Amount">
                                        <span class="text-danger error-message" id="fund_amount-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="add_fund_extra_information">Extra Information</label>
                                        <textarea id="add_fund_extra_information" class="form-control"
                                                  name="add_fund_extra_information"
                                                  placeholder="Enter Extra Information"></textarea>
                                        <span class="text-danger error-message" id="add_fund_extra_information-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="fund_add_date">Date <span
                                                class="text-danger">*</span></label>
                                        <input type="text"  value="{{ date('d-m-Y') }}" readonly
                                               id="fund_add_date" class="form-control"
                                               name="fund_add_date"
                                               placeholder="Enter Date">
                                        <span class="text-danger error-message" id="fund_add_date-error"></span>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                        <button type="submit" id="btn-add-fund-save" class="btn btn-primary">Add</button>
                    </div>
                </div>

            </div>

        </div>
    @endif
    @if(auth()->user()->can('cashbook_add_expense'))
        <div class="modal fade" id="modal-payment" data-backdrop="static" >
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Expense</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form enctype="multipart/form-data" id="payment-form" method="POST"
                              action="{{ route('cashbook.payment') }}">
                            @csrf
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <input type="hidden" name="payment_account_head" value="{{ request('payment_head') }}">
                                        <label for="particular_head">Particulars <span class="text-danger">*</span></label>
                                        <select name="particular_head" id="particular_head" class="form-control select2">
                                            <option value="">Select Particular Head</option>
                                            @foreach($expenseHeads as $expenseHead)
                                                <option
                                                    value="{{ $expenseHead->id }}">{{ $expenseHead->name }}</option>
                                            @endforeach
                                        </select>
                                        <span class="text-danger error-message" id="particular_head-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="extra_information">Extra Information</label>
                                        <textarea id="extra_information" class="form-control"
                                                  name="extra_information"
                                                  placeholder="Enter Extra Information"></textarea>
                                        <span class="text-danger error-message" id="extra_information-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="amount">Amount <span
                                                class="text-danger">*</span></label>
                                        <input type="number" id="amount" class="form-control"
                                               name="amount"
                                               placeholder="Enter Amount">
                                        <span class="text-danger error-message" id="amount-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="supporting_document">Supporting Document</label>
                                        <input type="file" id="supporting_document" class="form-control"
                                               name="supporting_document">
                                        <span class="text-danger error-message" id="supporting_document-error"></span>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="expense_date">Date <span
                                                class="text-danger">*</span></label>
                                        <input type="text" value="{{ date('d-m-Y') }}"
                                               class="form-control date-picker" id="expense_date"
                                               name="date"
                                               placeholder="Enter Date">
                                        <span class="text-danger error-message" id="date-error"></span>
                                    </div>
                                </div>

                            </div>
                        </form>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-danger bg-gradient-danger" data-dismiss="modal">Close</button>
                        <button type="submit" id="btn-payment-save" class="btn btn-primary">Add</button>
                    </div>
                </div>

            </div>

        </div>
    @endif
@endsection

@section('script')
    <script>
        $(function () {
            $("#add_fund").click(function (){
                $('.error-message').text(' ');
                $("#modal-fund").modal('show');
            })
            $('#btn-add-fund-save').click(function () {
                preloaderToggle(true);
                $('.error-message').text(' ');
                $.ajax({
                    type: 'POST',
                    url: $('#add-fund-form').attr('action'),
                    data: $('#add-fund-form').serialize(),
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            ajaxSuccessMessage(response.message)
                            location.reload()
                        } else {
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function (xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            ajaxWarningMessage('Please fill up validate required fields.');
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text(' ');
                            // Update error messages for each field
                            $.each(errors, function (field, errorMessage) {
                                $('#' + field + '-error').text(errorMessage[0]);
                            });
                        }
                    }
                });
            });
            $("#add_payment").click(function (){
                $('.error-message').text(' ');
                $("#modal-payment").modal('show');
            })
            $('#btn-payment-save').click(function () {
                preloaderToggle(true);
                $('.error-message').text(' ');
                let formData = new FormData($('#payment-form')[0]);
                $.ajax({
                    type: 'POST',
                    url: $('#payment-form').attr('action'),
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function (response) {
                        preloaderToggle(false);
                        if (response.status) {
                            ajaxSuccessMessage(response.message)
                            location.reload()
                        } else {
                            ajaxErrorMessage(response.message);
                        }
                    },
                    error: function (xhr) {
                        preloaderToggle(false);
                        // If the form submission encounters an error
                        // Display validation errors
                        if (xhr.status === 422) {
                            ajaxWarningMessage('Please fill up validate required fields.');
                            let errors = xhr.responseJSON.errors;
                            // Clear previous error messages
                            $('.error-message').text(' ');
                            // Update error messages for each field
                            $.each(errors, function (field, errorMessage) {
                                $('#' + field + '-error').text(errorMessage[0]);
                            });
                        }
                    }
                });
            });


            $('body').on('click', '.btn-voucher-delete', function () {
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
                            url: "{{ route('voucher.destroy', ['voucher' => 'REPLACE_WITH_ID_HERE']) }}".replace('REPLACE_WITH_ID_HERE', id),
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
        })
    </script>



    <script>
        // function modifyTransaction(voucherId, notes, amount) {
        //     $('#modify-voucher-id').val(voucherId);
        //     $('#modify-notes').val(notes);
        //     $('#modify-amount').val(amount);
        //     $('#modal-modify-transaction').modal('show');
        // }

        function modifyDebitTransaction(voucherId, notes, amount, payment_account_head_id, date) {
            $('#modify-voucher-debit-id').val(voucherId);
            $('#modify-debit-notes-credit').val(notes);
            $('#modify-debit-amount-credit').val(amount);
            $('#payment_account_head_id').val(payment_account_head_id);
            $('#modify-dabit-date').val(date);
            $('#modal-dabit-modify-transaction').modal('show');

        }

        $('#btn-save-modified-transaction').click(function() {
            let formData = $('#modify-transaction-form').serialize();
            $.ajax({
                type: 'POST',
                url: '{{ route("modify-transaction") }}',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Updated!',
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
                }
            });
        });
    </script>

    {{-- credit modify script --}}
    <script>
        function modifyCreditTransaction(voucherId, notes, amount, account_head_id, date) {
            $('#modify-voucher-credit-id').val(voucherId);
            $('#modify-notes-credit').val(notes);
            $('#modify-amount-credit').val(amount);
            $('#particular_head').val(account_head_id);
            $('#modify-date').val(date);
            $('#modal-credit-modify-transaction').modal('show');
            $('#particular_head').select2({
                placeholder: "Search for a particular head",
                allowClear: true // This option allows clearing the selection
            });

        }


        $('#btn-save-modified-transaction-credit').click(function() {
            let formData = $('#modify-transaction-form-credit').serialize();
            console.log(formData);
            $.ajax({
                type: 'POST',
                url: '{{ route("modify-transaction") }}',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Updated!',
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
                }
            });
        });

        function modifyDebitTransaction(voucherId, notes, amount, payment_account_head_id, date) {
            $('#modify-voucher-debit-id').val(voucherId);
            $('#modify-debit-notes-credit').val(notes);
            $('#modify-debit-amount-credit').val(amount);
            $('#payment_account_head_id').val(payment_account_head_id);
            $('#modify-dabit-date').val(date);
            $('#modal-dabit-modify-transaction').modal('show');
            $('#payment_account_head_id').select2({
                // placeholder: "Search for a particular head",
                allowClear: true // This option allows clearing the selection
            });

        }


        $('#btn-save-modified-transaction-debit').click(function() {
            let formData = $('#modify-transaction-form-debit').serialize();
            console.log(formData);
            $.ajax({
                type: 'POST',
                url: '{{ route("modify-transaction-debit") }}',
                data: formData,
                success: function(response) {
                    if (response.success) {
                        Swal.fire(
                            'Updated!',
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
                }
            });
        });
    </script>
@endsection
