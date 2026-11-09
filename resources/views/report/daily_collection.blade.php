@extends('layouts.app')
@section('title','Daily Collection Report')

@section('content')

    <div class="row justify-content-md-center">
        <div class="col-12 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-gradient-yellow elevation-1"><i class="text-white fa fa-bangladeshi-taka-sign"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">TOTAL COLLECTION</span>
                    <span class="info-box-number" id="total_collection">{{ number_format($totalCollection,2) }}</span>
                </div>
            </div>
        </div>
        @foreach($paymentModes as $paymentMode)
        @php
            $collectionAmount = \App\Models\Account\Voucher::
                where('voucher_type',\App\Enumeration\VoucherType::$COLLECTION_VOUCHER)
                //->whereNotNull('booking_id')
                ->where('date',date('Y-m-d'))
                ->where('payment_account_head_id',$paymentMode->id)
                ->sum('amount');
                $paymentModeLogoPath = null;
                $imagePath = public_path('img/payment_mode_logo/'.$paymentMode->name.'.png');
                if (file_exists($imagePath)) {
                    $paymentModeLogoPath = asset('img/payment_mode_logo/'.$paymentMode->name.'.png');
                }

        @endphp
        <div class="col-12 col-md-3">
            <div class="info-box">

                    @if($paymentModeLogoPath)
                        <div class="card  m-0 p-0">
                            <div class="card-body m-0 p-0">
                                <img height="60px" width="70px" src="{{ asset($paymentModeLogoPath) }}" alt="">
                            </div>
                        </div>
                    @else
                    <span class="info-box-icon bg-gradient-primary elevation-1">
                        <i class="text-white fa fa-bangladeshi-taka-sign"></i>
                    </span>
                    @endif

                <div class="info-box-content">
                    <span class="info-box-text">{{ $paymentMode->name }}</span>
                    <span class="info-box-number" id="payment_mode_collection_{{ $paymentMode->id }}">{{ number_format($collectionAmount,2) }}</span>
                </div>
            </div>
        </div>
        @endforeach

        <div class="col-12 col-md-3">
            <div class="info-box">
                <span class="info-box-icon  bg-gradient-success elevation-1"><i class="fa fa-handshake"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">SETTLED</span>
                    <span class="info-box-number" id="total_settled">{{ number_format($totalSettled,2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-3">
            <div class="info-box">
                <span class="info-box-icon bg-gradient-indigo elevation-1"><i class="fa fa-bank"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">BANK DEPOSIT</span>
                    <span class="info-box-number" id="total_bank_deposit">{{ number_format($totalBankDeposit,2) }}</span>
                </div>
            </div>
        </div>
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-header">
                    <div class="row">
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="start_date" class="col-form-label">Start Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('start_date',date('d-m-Y')) }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Enter Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label for="end_date" class="col-form-label">End Date <span
                                        class="text-danger">*</span></label>
                                <input required autocomplete="off" type="text" value="{{ request('end_date',date('d-m-Y')) }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="Enter End Date">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -4px;" type="button" id="search_btn" name="search"
                                       class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive-sm">
                        <form method="post" action="{{ route('daily_collection.approved_selected') }}">
                            @csrf
                            @can('daily_collection_approved')
                            <div class="row mb-2">
                                <div class="col-md-12">
                                    <div class="btn-group" id="confirm_settled_and_deposit" style="display: none">
                                        <button name="confirm_status" value="1" class="btn btn-success bg-gradient-success">Confirm Settled</button>
                                        <button name="confirm_status" value="2" class="btn btn-info  bg-gradient-info">Confirm Deposit</button>
                                    </div>
                                    <div class="icheck-warning float-right "><input type="checkbox"  id="checkAll"><label for="checkAll">Check All</label></div>

                                </div>
                            </div>
                            @endif
                            <table id="table" class="table table-bordered">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Card No.</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Particulars</th>
                                    <th>Collection Amount</th>
                                    <th>Payment Mode</th>
                                    <th>Remarks</th>
                                    <th>Action</th>
                                </tr>
                                </thead>
                            </table>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('#checkAll').on('click', function() {
                let isChecked = $(this).prop('checked');
                let singleOrderSelect = $('.daily_collection_approved_check').prop('checked', isChecked);
                checkSelectVoucher();
            });

            let table = $('#table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('daily_collection.datatable') }}",
                    data: function (d) {
                        d.start_date = $("#start_date").val()
                        d.end_date = $("#end_date").val()
                    }
                },
                "pagingType": "full_numbers",
                "pageLength": 50,
                "lengthMenu": [[10, 25, 50, -1],[10, 25, 50, "All"],

                ],
                columns: [
                            {data: 'date', name: 'date',visible:false},
                            {
                                data: 'edit_date',
                                name: 'edit_date',

                                render: function(data, type, row) {
                                    // Initialize a dictionary to keep track of formatted dates within the group
                                    if (!window.dateColors) {
                                        window.dateColors = {};
                                    }

                                    var date = new Date(data);
                                    var formattedDate = date.toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });

                                    // Check if the date has already been formatted and colored within the group
                                    if (!window.dateColors[formattedDate]) {
                                        // Assign blue, red, and black colors alternatively
                                        var colors = ['blue', 'red', 'black'];
                                        var index = Object.keys(window.dateColors).length % colors.length;
                                        window.dateColors[formattedDate] = colors[index];
                                    }

                                    // Apply the color to the date
                                    var color = window.dateColors[formattedDate];
                                    return '<span style="color: ' + color + ';">' + formattedDate + '</span>';
                                }

                            },
                            {data: 'card_no', name: 'booking.order_no'},
                            {data: 'room_no', name: 'guestExpenseLog.room.room_no'},
                            {data: 'status', name: 'status'},
                            {data: 'particulars', name: 'particulars','className':'text-center'},
                            {data: 'amount', name: 'amount',
                                render: function(data, type, row) {

                                    return jsNumberFormat(parseFloat(data).toFixed(2));
                                }
                            },
                            {data: 'payment_mode', name: 'paymentAccountHead.name'},
                            {data: 'collection_receive_status', name: 'collection_receive_status', orderable: false},
                            {data: 'action', name: 'action', orderable: false},
                        ],

                order: [[0, 'asc']],
                "columnDefs": [
                    {targets: 0,className: 'text-center'},
                    {targets: 1,className: 'text-center'},
                    {targets: 2,className: 'text-center'},
                    {targets: 3,className: 'text-center'},
                    {targets: 4,className: 'text-left'},
                    {targets: 5,className: 'text-left'},

                    {targets: 7,className: 'text-center'},
                    {targets: 8,className: 'text-center'},
                ],

                "dom": 'lBfrtip',
                "buttons": [
                    {
                        "extend": "copy",
                        "text": "<i class='fas fa-copy'></i> Copy",
                        "className": "btn btn-info bg-gradient-info btn-sm"
                    },{
                        "extend": "csv",
                        "text": "<i class='fas fa-file-csv'></i> Export to CSV",
                        "className": "btn btn-info bg-gradient-info btn-sm"
                    },
                    {
                        "extend": "excel",
                        "text": "<i class='fas fa-file-excel'></i> Export to Excel",
                        "className": "btn btn-info bg-gradient-info btn-sm"
                    },

                    {
                        "extend": "print",
                        "text": "<i class='fas fa-print'></i> Print",
                        "className": "btn btn-info bg-gradient-info btn-sm"
                    },
                    {
                        "extend": "colvis",
                        "text": "<i class='fas fa-eye'></i> Column visibility",
                        "className": "btn btn-info bg-gradient-info btn-sm"
                    }
                ],
                "responsive": true, "autoWidth": false,"colReorder": true,
            });
            $('body').on('click', '.daily_collection_approved_check', function () {
                checkSelectVoucher()
            })

            $('#start_date,#end_date,#search_btn').change(function () {
                table.ajax.reload();
                let start_date = $("#start_date").val();
                let end_date = $("#end_date").val();
                $.ajax({
                    method: "GET",
                    url: "{{ route('get_collection_amount') }}",
                    data: {start_date: start_date,end_date:end_date}
                }).done(function (response) {
                    if (response.status){
                        $("#total_collection").text(response.total_collection);
                        $("#total_settled").text(response.total_settled);
                        $("#total_bank_deposit").text(response.total_bank_deposit);

                        $.each(response.payment_mode_collections, function(index, item) {
                            let elementId = "payment_mode_collection_" + item.id;
                            $("#" + elementId).text(item.amount);
                        });
                    }
                });
            });

            $('body').on('click', '.btn-collected', function () {
                let id = $(this).data('id');
                let status = $(this).data('status');
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You won't be able to revert this!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, Approved!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        preloaderToggle(true);
                        $.ajax({
                            method: "POST",
                            url: "{{ route('daily_collection.approved') }}",
                            data: { id: id,status:status }
                        }).done(function( response ) {
                            preloaderToggle(false);
                            if (response.success) {
                                Swal.fire(
                                    'Approved!',
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
        function checkSelectVoucher(){
            let checkSelectVouchers = $('.daily_collection_approved_check:checked');
            if (checkSelectVouchers.length > 0){
                $('#confirm_settled_and_deposit').show();
            }else{
                $('#confirm_settled_and_deposit').hide();
            }
        }
    </script>
@endsection
