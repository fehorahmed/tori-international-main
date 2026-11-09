@extends('layouts.app')
@section('title')
    {{ $voucherTitle }} Edit : {{ $voucher->voucher_no }}
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
    <form enctype="multipart/form-data" action="{{ route('voucher.update',['voucher'=>$voucher->id,'voucher_type'=>$voucherType]) }}" class="form-horizontal" method="post">
        @csrf
        @method('PUT')
        <div class="row">
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $voucherTitle }} Information </h3>
                </div>
                <div class="card-header">
                   <div class="row">
                       <div class="col-md-3">
                           <div class="form-group">
                               <label for="select_debit_account_head">
                                  Debit Account Head
                                   <span class="text-danger">*</span></label>
                               <select class="form-control select2" id="select_debit_account_head" name="select_debit_account_head">
                                   <option value="">Search Debit Account Head</option>
                                   @foreach($accountHeads as $accountHead)
                                   <option value="{{ $accountHead->id }}">{{ $accountHead->name }} - {{ $accountHead->code }}</option>
                                   @endforeach
                               </select>
                           </div>
                       </div>
                       <div class="col-md-2">
                           <div class="form-group">
                               <label for="add_debit_amount">Dr. Amount <span class="text-danger">*</span></label>
                               <input type="number" step="any" class="form-control" id="add_debit_amount" placeholder="Dr. Amount">
                           </div>
                       </div>
                       <div class="col-md-3">
                           <div class="form-group">
                               <label for="select_credit_account_head">
                                  Credit Account Head
                                   <span class="text-danger">*</span></label>
                               <select class="form-control select2" id="select_credit_account_head" name="select_credit_account_head">
                                   <option value="">Search Credit Account Head</option>
                                   @foreach($accountHeads as $accountHead)
                                   <option value="{{ $accountHead->id }}">{{ $accountHead->name }} - {{ $accountHead->code }}</option>
                                   @endforeach
                               </select>
                           </div>
                       </div>
                       <div class="col-md-2">
                           <div class="form-group">
                               <label for="add_credit_amount">Cr. Amount <span class="text-danger">*</span></label>
                               <input type="number" readonly step="any" class="form-control" id="add_credit_amount" placeholder="Cr. Amount">
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
                                            <th colspan="6" class="text-center">{{ $voucherTitle }} Details</th>
                                        </tr>
                                       <tr>
                                           <th class="text-center" width="5%">S/L</th>
                                           <th class="text-center">Debit Account Head</th>
                                           <th class="text-center" width="20%">Dr. Amount</th>
                                           <th class="text-center">Credit Account Head</th>
                                           <th class="text-center" width="20%">Cr. Amount</th>
                                           <th class="text-center" width="5%"></th>
                                       </tr>
                                   </thead>
                                    <tbody id="account-head-container">
                                        @if (old('debit_account_head_id') != null && sizeof(old('debit_account_head_id')) > 0)
                                            @foreach(old('debit_account_head_id') as $key => $item)
                                                <tr class="account-head-item">
                                                    <td class="text-center">
                                                        <span class="account_head_sl">{{ ++$key }}</span>
                                                    </td>
                                                    <td class="text-left {{ $errors->has('debit_account_head_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="debit_account_head">{{ old('debit_account_head_val.'.$loop->index) }}</span>
                                                        <input type="hidden" name="debit_account_head_val[]" value="{{ old('debit_account_head_val.'.$loop->index) }}" class="debit_account_head_val">
                                                        <input type="hidden" name="debit_account_head_id[]" value="{{ old('debit_account_head_id.'.$loop->index) }}" class="debit_account_head_id">
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0  {{ $errors->has('debit_amount.'.$loop->index) ? 'has-error' :'' }}">
                                                            <input type="number" step="any" value="{{ old('debit_amount.'.$loop->index) }}" class="form-control text-right debit_amount" name="debit_amount[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-left {{ $errors->has('credit_account_head_id.'.$loop->index) ? 'bg-gradient-danger' :'' }}">
                                                        <span class="credit_account_head">{{ old('credit_account_head_val.'.$loop->index) }}</span>
                                                        <input type="hidden" name="credit_account_head_val[]" value="{{ old('credit_account_head_val.'.$loop->index) }}" class="credit_account_head_val">
                                                        <input type="hidden" name="credit_account_head_id[]" value="{{ old('credit_account_head_id.'.$loop->index) }}" class="credit_account_head_id">
                                                    </td>
                                                    <td class="text-right">
                                                        <div class="form-group mb-0  {{ $errors->has('credit_amount.'.$loop->index) ? 'has-error' :'' }}">
                                                            <input type="number" readonly step="any" value="{{ old('credit_amount.'.$loop->index) }}" class="form-control text-right credit_amount" name="credit_amount[]">
                                                        </div>
                                                    </td>
                                                    <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @else
                                            @foreach($voucher->transactions->chunk(2) as $key => $chunks)
                                                <tr class="account-head-item">
                                                    <td class="text-center">
                                                        <span class="account_head_sl">{{ ++$key }}</span>
                                                    </td>
                                                    @foreach($chunks as $item)
                                                        @if($item->transaction_type == \App\Enumeration\TransactionType::$DEBIT)
                                                        <td class="text-left">
                                                            <span class="debit_account_head">{{ $item->accountHead->name ?? '' }}</span>
                                                            <input type="hidden" name="debit_account_head_val[]" value="{{ $item->accountHead->name ?? '' }}" class="debit_account_head_val">
                                                            <input type="hidden" name="debit_account_head_id[]" value="{{ $item->account_head_id }}" class="debit_account_head_id">
                                                        </td>
                                                        <td class="text-right">
                                                            <div class="form-group mb-0">
                                                                <input type="number" step="any" value="{{ $item->amount }}" class="form-control text-right debit_amount" name="debit_amount[]">
                                                            </div>
                                                        </td>
                                                        @else
                                                            <td class="text-left">
                                                                <span class="credit_account_head">{{ $item->accountHead->name ?? '' }}</span>
                                                                <input type="hidden" name="credit_account_head_val[]" value="{{ $item->accountHead->name ?? '' }}" class="credit_account_head_val">
                                                                <input type="hidden" name="credit_account_head_id[]" value="{{ $item->account_head_id }}" class="credit_account_head_id">
                                                            </td>
                                                            <td class="text-right">
                                                                <div class="form-group mb-0">
                                                                    <input type="number" readonly step="any" value="{{ $item->amount }}" class="form-control text-right credit_amount" name="credit_amount[]">
                                                                </div>
                                                            </td>
                                                        @endif
                                                    @endforeach
                                                    <td class="text-center"><button type="button" class="btn btn-danger bg-gradient-danger btn-sm btn-remove"><i class="fa fa-trash-alt"></i></button></td>
                                                </tr>
                                            @endforeach
                                        @endif
                                        </tbody>
                                    <tfoot style="{{ (old('account_head_id') != null && sizeof(old('account_head_id'))) > 0 ? 'display: revert' : 'display: none' }}" id="footer_area">
                                        <tr>
                                            <th></th>
                                            <th  class="text-right">Total Debit</th>
                                            <th  class="text-right total_debit_amount"></th>
                                            <th  class="text-right">Total Credit</th>
                                            <th  class="text-right total_credit_amount"></th>
                                            <th></th>
                                        </tr>

                                    </tfoot>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span
                                            class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('date',\Carbon\Carbon::parse($voucher->date)->format('d-m-Y')) }}" name="date" id="date" class="form-control business-date-picker" placeholder="Enter date" readonly>
                                    @error('date')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes</label>
                                    <input type="text" value="{{ old('notes',$voucher->notes) }}" name="notes" id="notes" class="form-control" placeholder="Enter Notes">
                                    @error('notes')
                                    <span class="help-block">{{ $message }}</span>
                                    @enderror
                                </div>
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

    </div>
    </form>
    <template id="template-account-head">
        <tr class="account-head-item">
            <td class="text-center">
                <span class="account_head_sl"></span>
            </td>
            <td class="text-left">
              <span class="debit_account_head"></span>
                <input type="hidden" name="debit_account_head_val[]" class="debit_account_head_val">
                <input type="hidden" name="debit_account_head_id[]" class="debit_account_head_id">
            </td>

            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" step="any" class="form-control text-right debit_amount" name="debit_amount[]">
                </div>
            </td>
            <td class="text-left">
                <span class="credit_account_head"></span>
                <input type="hidden" name="credit_account_head_val[]" class="credit_account_head_val">
                <input type="hidden" name="credit_account_head_id[]" class="credit_account_head_id">
            </td>
            <td class="text-right">
                <div class="form-group mb-0">
                    <input type="number" readonly step="any" class="form-control text-right credit_amount" name="credit_amount[]">
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
            $('body').on('keyup', '#add_debit_amount', function (e) {
                $("#add_credit_amount").val($(this).val());
            });
            $('body').on('keypress', '#add_debit_amount,#add_credit_amount', function (e) {
                if (e.keyCode == 13) {
                    return false; // prevent the button click from happening
                }
            });

            $('body').on('click', '#add_new_btn', function (e) {
                let selectDebitAccountHead = $('#select_debit_account_head').val();
                let selectCreditAccountHead = $('#select_credit_account_head').val();
                let addDebitAmount = $('#add_debit_amount').val();
                let addCreditAmount = $('#add_credit_amount').val();
                if (selectDebitAccountHead == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select debit account head !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (selectCreditAccountHead == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, select credit account head !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }

                if(selectDebitAccountHead != '' && selectDebitAccountHead == selectCreditAccountHead){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, debit account head & credit account head never same at the same trnasction!',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }
                if (addDebitAmount == ''){
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Please, enter debit amount !',
                    });
                    // Play the notification sound
                    let notificationSound = document.getElementById('notification-error-audio');
                    if (notificationSound) {
                        notificationSound.play();
                    }
                    return false;
                }


                if (selectDebitAccountHead != '' && selectCreditAccountHead != ''  && addDebitAmount != '') {
                    var addMoreSound = document.getElementById("add_more_sound");
                    addMoreSound.play();
                    var html = $('#template-account-head').html();
                    var itemHtml = $(html);
                    $('#account-head-container').prepend(itemHtml);
                    var item = $('.account-head-item').first();
                    item.hide();
                    item.closest('tr').find('.debit_account_head').text($("#select_debit_account_head option:selected").text());
                    item.closest('tr').find('.debit_account_head_val').val($("#select_debit_account_head option:selected").text());
                    item.closest('tr').find('.debit_account_head_id').val($("#select_debit_account_head option:selected").val());

                    item.closest('tr').find('.credit_account_head').text($("#select_credit_account_head option:selected").text());
                    item.closest('tr').find('.credit_account_head_val').val($("#select_credit_account_head option:selected").text());
                    item.closest('tr').find('.credit_account_head_id').val($("#select_credit_account_head option:selected").val());
                    item.closest('tr').find('.amount').addClass('debit_amount');

                    item.closest('tr').find('.debit_amount').val(addDebitAmount);
                    item.closest('tr').find('.credit_amount').val(addCreditAmount);

                    item.show();
                    calculate();
                    $('#add_debit_amount').val('');
                    $('#add_credit_amount').val('');
                }
                return false; // prevent the button click from happening
            });

            $('body').on('click', '.btn-remove', function () {
                var account_head_id = $(this).closest('tr').find('.account_head_id').val();

                var removeItem = document.getElementById("remove_sound");
                removeItem.play();
                $(this).closest('.account-head-item').remove();
                calculate();

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
            $('.account-head-item').each(function(i, obj) {

                let debit_amount = parseFloat($('.debit_amount:eq('+i+')').val());
                debit_amount = (isNaN(debit_amount) || debit_amount < 0) ? 0 : debit_amount;

               $('.credit_amount:eq('+i+')').val(debit_amount);


                total_debit += debit_amount;
                total_credit += debit_amount;

            });

            $('.total_debit_amount').text(total_debit.toFixed(2));
            $('.total_credit_amount').text(total_credit.toFixed(2));
            if (rows.length > 0){
                $("#footer_area").show();
            }else{
                $("#footer_area").hide();
            }
        }
    </script>
@endsection
