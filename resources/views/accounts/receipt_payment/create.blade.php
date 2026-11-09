@extends('layouts.app')
@section('title')
    {{ $voucherTitle }} Create
@endsection
@section('style')
    <style>
        .table td, .table th {
            padding: 6px;
            vertical-align: middle;
        }
        /* Remove arrow style (spinners) from number input */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type="number"] {
            -moz-appearance: textfield; /* Firefox */
        }
    </style>
@endsection
@section('content')
    <audio id="add_more_sound" style="display: none">
        <source src="{{ asset('img/add.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <audio id="remove_sound" style="display: none">
        <source src="{{ asset('img/remove.mp3') }}" type="audio/mpeg">
        Your browser does not support the audio element.
    </audio>
    <!-- form start -->
    <form enctype="multipart/form-data" action="{{ route('voucher.store',['voucher_type'=>$voucherType]) }}" class="form-horizontal" method="post">
        @csrf
        <div class="row">
        <div class="col-md-8">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $voucherTitle }} Information </h3>
                </div>
                <div class="card-header">
                   <div class="row">
                       <div class="col-md-5">
                           <div class="form-group">
                               @if($voucherType == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER)
                                  @php
                                      $headLabel = 'Head of Expense';
                                  @endphp
                               @else
                                   @php
                                       $headLabel = 'Account head';
                                   @endphp

                               @endif
                               <label for="select_account_head">
                                  {{ $headLabel }}
                                   <span class="text-danger">*</span></label>
                               <select class="form-control select2" id="select_account_head" name="select_account_head">
                                   <option value="">Search {{ $headLabel }}</option>
                                   @foreach($accountHeads as $accountHead)
                                   <option value="{{ $accountHead->id }}">{{ $accountHead->name }} - {{ $accountHead->code }}</option>
                                   @endforeach
                               </select>
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="form-group">
                               <label for="transaction_type">Trnx Type <span class="text-danger">*</span></label>
                               <select name="transaction_type" id="transaction_type" class="form-control select2">
                                   @if($voucherType == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                       <option value="{{ \App\Enumeration\TransactionType::$DEBIT }}">Debit</option>
                                       <option value="{{ \App\Enumeration\TransactionType::$CREDIT }}">Credit</option>
                                   @elseif($voucherType == \App\Enumeration\VoucherType::$PAYMENT_VOUCHER)
                                       <option value="{{ \App\Enumeration\TransactionType::$DEBIT }}">Debit</option>
                                       <option value="{{ \App\Enumeration\TransactionType::$CREDIT }}">Credit</option>
                                   @elseif($voucherType == \App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                                       <option value="{{ \App\Enumeration\TransactionType::$CREDIT }}">Credit</option>
                                       <option value="{{ \App\Enumeration\TransactionType::$DEBIT }}">Debit</option>
                                   @endif

                               </select>
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="form-group">
                               <label for="add_amount">Amount <span class="text-danger">*</span></label>
                               <input type="number" class="form-control" id="add_amount" placeholder="Amount">
                           </div>
                       </div>
                       <div class="col-md-1">
                           <div class="form-group">
                               <button type="button" style="margin-top: 31px;" id="add_new_btn" class="btn btn-info bg-gradient-info btn-sm btn-block"><i class="fa fa-plus"></i></button>
                           </div>
                       </div>
                   </div>
                </div>
                <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-bordered">
                                   <thead>
                                       <tr>
                                           <th class="text-center" width="5%">S/L</th>
                                           <th class="text-center">{{ $voucherTitle }} Details</th>
                                           <th class="text-center" width="5%">Type</th>
                                           <th class="text-center" width="20%">Amount</th>
                                           <th class="text-center" width="5%"></th>
                                       </tr>
                                   </thead>
                                    <tbody id="account-head-container">
                                        @if (old('account_head_id') != null && sizeof(old('account_head_id')) > 0)
                                            @foreach(old('account_head_id') as $key => $item)
                                                <tr class="account-head-item">
                                                    <td class="text-center">
                                                        <span class="account_head_sl">{{ ++$key }}</span>
                                                    </td>
                                                    <td class="text-left {{ $errors->has('account_head_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="account_head">{{ old('account_head_val.'.$loop->index) }}</span>
                                                        <input type="hidden" name="account_head_val[]" value="{{ old('account_head_val.'.$loop->index) }}" class="account_head_val">
                                                        <input type="hidden" name="account_head_id[]" value="{{ old('account_head_id.'.$loop->index) }}" class="account_head_id">
                                                    </td>
                                                    <td class="text-center">
                                                        <span class="trnx_type">
                                                             {{ old('trnx_type_val.'.$loop->index) == 1 ? 'Debit' : 'Credit' }}
                                                        </span>
                                                        <input type="hidden" value="{{ old('trnx_type_val.'.$loop->index) }}" class="trnx_type_val" name="trnx_type_val[]">
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0  {{ $errors->has('amount.'.$loop->index) ? 'has-error' :'' }}">
                                                            <input type="number" value="{{ old('amount.'.$loop->index) }}" class="form-control text-right amount {{ old('trnx_type_val.'.$loop->index) == 1 ? 'debit_amount' : 'credit_amount' }}" name="amount[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    <tfoot style="{{ (old('account_head_id') != null && sizeof(old('account_head_id'))) > 0 ? 'display: revert' : 'display: none' }}" id="footer_area">
                                    <tr>
                                        <th colspan="{{ $isNotJournalVoucher == true ? 3 : 3 }}" class="text-right">
                                            @if($voucherType == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                                @php
                                                    $trnxTotalLabel = 'debit_total';
                                                @endphp
                                                Total Debit
                                            @else
                                                @php
                                                    $trnxTotalLabel = $voucherType == 2 ? 'debit_total' : 'credit_total';
                                                @endphp
                                                Total {{ $voucherType == 2 ? 'Debit' : 'Credit' }}
                                            @endif
                                        </th>
                                        <th  class="text-right total_amount {{ $trnxTotalLabel }}"></th>
                                        <th></th>
                                    </tr>

                                    @if($voucherType != \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                        <tr>
                                            <th colspan="3" class="text-right">
                                                @php
                                                    $trnxTotalLabel = $voucherType == 3 ? 'other_debit_total' : 'other_credit_total';
                                                @endphp
                                                Total {{ $voucherType == 3 ? 'Other Deduction Debit' : 'Other Deduction Credit' }}
                                            </th>
                                            <th  class="text-right total_amount {{ $trnxTotalLabel }}"></th>
                                            <th></th>
                                        </tr>
                                    @endif
                                    <tr>
                                        <th colspan="{{ $isNotJournalVoucher == true ? 3 : 3 }}" class="text-right">
                                            @if($voucherType == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER)
                                                @php
                                                    $trnxTotalLabel = 'credit_total';
                                                @endphp
                                                Total Credit
                                            @else
                                                @php
                                                    $trnxTotalLabel = $voucherType == 2 ? 'credit_total' : 'debit_total';
                                                @endphp
                                                Total <span id="payment_mode_label"></span> {{ $voucherType == 2 ? 'Credit' : 'Debit' }}
                                            @endif

                                        </th>
                                        <th  class="text-right total_amount {{ $trnxTotalLabel }}"></th>
                                        <th></th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('voucher.index',['voucher_type'=>$voucherType]) }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
            </div>
            <!-- /.card -->
        </div>
        <div class="col-md-4">
                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Basic Information</h3>
                    </div>
                    <!-- /.card-header -->
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12" style="{{ $voucherType == \App\Enumeration\VoucherType::$JOURNAL_VOUCHER ? 'display:none' : '' }}">
                                <div class="form-group {{ $errors->has('payment_mode') ? 'has-error' :'' }}">
                                    <label for="payment_mode">{{ $voucherType == 2 ? 'Payment' : 'Receipt' }} Mode <span
                                            class="text-danger">*</span></label>
                                    <select name="payment_mode" id="payment_mode" class="form-control select2">
                                        <option value="">Select {{ $voucherType == 2 ? 'Payment' : 'Receipt' }} Mode</option>
                                        @foreach($paymentModes as $paymentMode)
                                            <option {{ old('payment_mode') == ($paymentMode->id.'|'.$paymentMode->payment_mode) ? 'selected' : '' }} value="{{ $paymentMode->id }}|{{ $paymentMode->payment_mode }}">{{ $paymentMode->name }}- {{ $paymentMode->code }}</option>
                                        @endforeach
                                    </select>
                                    @error('payment_mode')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12" style="display: none" id="if_payment_bank_mode">
                                <div class="form-group {{ $errors->has('cheque_no') ? 'has-error' :'' }}">
                                    <label for="cheque_no">Cheque No.</label>
                                    <input type="text" value="{{ old('cheque_no') }}" id="cheque_no" class="form-control" name="cheque_no" placeholder="Enter Cheque No.">
                                    @error('cheque_no')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('payee_or_depositor') ? 'has-error' :'' }}">
                                    <label for="payee_or_depositor">{{ $partyLabel }}</label>
                                    <select class="form-control select2" id="payee_or_depositor" name="payee_or_depositor">
                                        <option value="">Search {{ $partyLabel }}</option>
                                        @foreach($parties as $party)
                                            <option {{ old('payee_or_depositor') == $party->id ? 'selected' : '' }} value="{{ $party->id }}">{{ $party->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('payee_or_depositor')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('date',date('d-m-Y')) }}" name="date" id="date" class="form-control business-date-picker" placeholder="Enter date" readonly>
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes</label>
                                    <input type="text" value="{{ old('notes') }}" name="notes" id="notes" class="form-control" placeholder="Enter Notes">
                                    @error('notes')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </div>
    </form>
    <template id="template-account-head">
        <tr class="account-head-item">
            <td class="text-center">
                <span class="account_head_sl"></span>
            </td>
            <td class="text-left">
                <span class="account_head"></span>
                <input type="hidden" name="account_head_val[]" class="account_head_val">
                <input type="hidden" name="account_head_id[]" class="account_head_id">
            </td>
            <td class="text-center">
                <span class="trnx_type"></span>
                <input type="hidden" class="trnx_type_val" name="trnx_type_val[]">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right amount" name="amount[]">
                </div>
            </td>
            <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
        </tr>
    </template>

@endsection
@section('script')
    <script>
        $(function (){
            calculate();

            $('#payment_mode').change(function (){
                let paymentMode = $(this).val();
                if (paymentMode != ''){
                    var valuesArray = paymentMode.split('|'); // Split the selected value into an array
                    var id = valuesArray[0];
                    var mode = valuesArray[1];

                    $('#payment_mode_label').text($("#payment_mode option:selected").text());

                    if (paymentMode != '' && mode == 1){
                        $("#if_payment_bank_mode").show();
                    }else{
                        $("#if_payment_bank_mode").hide();
                    }
                }
            })
            $('#payment_mode').trigger('change');
            var accountHeadIds = [];

            $( ".account_head_id" ).each(function( index ) {
                if ($(this).val() != '') {
                    accountHeadIds.push($(this).val());
                }
            });
            $('body').on('keypress', '#amount', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectAccountHead = $('#select_account_head').val();
                let selectAccountHeadName = $("#select_account_head option:selected").text();
                let addAmount = $('#add_amount').val();
                let transactionType = $('#transaction_type').val();
                if (selectAccountHead == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select account head !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (transactionType == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select trnx type !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addAmount == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, type amount !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if($.inArray(selectAccountHead, accountHeadIds) != -1) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: selectAccountHeadName+ ' already exist in list.',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if (selectAccountHead != '' && transactionType != ''  && addAmount != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-account-head').html();
                    var itemHtml = $(html);
                    $('#account-head-container').append(itemHtml);
                    var item = $('.account-head-item').last();
                    item.hide();
                    item.closest('tr').find('.account_head').text($("#select_account_head option:selected").text());
                    item.closest('tr').find('.account_head_val').val($("#select_account_head option:selected").text());
                    item.closest('tr').find('.account_head_id').val($("#select_account_head option:selected").val());
                    if (transactionType == 1){
                        item.closest('tr').find('.trnx_type').text('Debit');
                        item.closest('tr').find('.trnx_type_val').val(1);
                        item.closest('tr').find('.amount').addClass('debit_amount');
                    }else{
                        item.closest('tr').find('.trnx_type').text('Credit');
                        item.closest('tr').find('.trnx_type_val').val(2);
                        item.closest('tr').find('.amount').addClass('credit_amount');
                    }

                    item.closest('tr').find('.amount').val(addAmount);
                    accountHeadIds.push(selectAccountHead);
                    item.show();
                    calculate();
                    $('#add_amount').val('');
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var account_head_id = $(this).closest('tr').find('.account_head_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();

                $(this).closest('.account-head-item').remove();
                calculate();
                accountHeadIds = $.grep(accountHeadIds, function(value) {
                    return value != account_head_id;
                });
            });
            $('body').on('keyup', 'input[type="number"]', function() {
                calculate();
            });
            $('body').on('change', 'input[type="number"]', function() {
                calculate();
            });
        })

        function calculate(){
            // Assuming you want to start the account_head_sl value from 1
            var AccountHeadSl = 1;

            // Select all the table rows with the class .account-head-item
            var rows = $("#account-head-container .account-head-item");
            // Iterate over each row and update the account_head_sl value
            rows.each(function() {
                // Find the .account_head_sl element within the current row
                var AccountHeadSlElement = $(this).find('.account_head_sl');
                // Update the text of the .account_head_sl element with the account_head_sl value
                AccountHeadSlElement.text(AccountHeadSl);
                // Increment the account_head_sl value for the next iteration
                AccountHeadSl++;
            });
            var total_debit = 0;
            var total_credit = 0;
            var total_amount = 0;
            var other_debit_total = 0;
            var other_credit_total = 0;
            $('.account-head-item').each(function(i, obj) {

                let amount = parseFloat($('.amount:eq('+i+')').val());
                amount = (isNaN(amount) || amount < 0) ? 0 : amount;

                let debit_amount = parseFloat($('.debit_amount:eq('+i+')').val());
                debit_amount = (isNaN(debit_amount) || debit_amount < 0) ? 0 : debit_amount;

                let credit_amount = parseFloat($('.credit_amount:eq('+i+')').val());
                credit_amount = (isNaN(credit_amount) || credit_amount < 0) ? 0 : credit_amount;


                if ('{{ $voucherType }}' == '{{ \App\Enumeration\VoucherType::$JOURNAL_VOUCHER }}'){
                    total_debit += debit_amount;
                    total_credit += credit_amount;
                }else if ('{{ $voucherType }}' == '{{ \App\Enumeration\VoucherType::$PAYMENT_VOUCHER }}'){
                    total_debit += debit_amount;
                    total_credit += debit_amount - credit_amount;
                    other_credit_total += credit_amount;
                }else if ('{{ $voucherType }}' == '{{ \App\Enumeration\VoucherType::$COLLECTION_VOUCHER }}'){
                    total_credit += credit_amount;
                    total_debit += credit_amount - debit_amount;
                    other_debit_total += debit_amount;
                }

            });

            $('.other_debit_total').text(other_debit_total.toFixed(2));
            $('.other_credit_total').text(other_credit_total.toFixed(2));

            $('.debit_total').text(total_debit.toFixed(2));
            $('.credit_total').text(total_credit.toFixed(2));

            if (rows.length > 0){
                $("#footer_area").show();
            }else{
                $("#footer_area").hide();
            }
        }
    </script>
@endsection
