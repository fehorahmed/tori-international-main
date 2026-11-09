@extends('layouts.app')
@section('title','Cash/Bank Transaction Details')
@section('style')
    <style>

        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle !important;
            text-align: center;
            padding: 0.2rem !important;
            font-size: 12px !important;
        }

        .table-bordered {
            border: 1px solid #ffffff;
        }

        table thead {
            border-collapse: inherit !important;
        }

        @media print {
            .table-bordered td, .table-bordered th {
                font-size: 18px !important;
            }

        }

    </style>
@endsection
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Data Filter</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form target="_blank" action="{{ route('report.cash_or_bank_transaction_details') }}">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('start_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date"
                                           value="{{ request()->get('end_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="payment_mode">Payment Account <span class="text-danger">*</span></label>
                                    <select required class="form-control select2" id="payment_mode" name="payment_mode">
                                        <option value="">Search Payment Account</option>
                                        @foreach($accounts as $account)
                                            <option
                                                {{ request('payment_mode',($accounts->count() == 1 ? $accounts[0]->id : '')) == $account->id ? 'selected' : '' }} value="{{ $account->id }}">{{ $account->name }}
                                                -{{ $account->code }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control"
                                           value="Search">
                                </div>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>

        $(function (){
            var projectSelected = '{{ old('project',request('project')) }}';

            $("#sister_concern").change(function () {
                let sisterConcernId = $(this).val();
                $('#project').html('<option value="">All Project</option>');
                if (sisterConcernId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_sister_concern_wise_project') }}",
                        data: {sisterConcernId: sisterConcernId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            if (projectSelected == item.id)
                                $('#project').append('<option value="' + item.id + '" selected>' + item.name + '</option>');
                            else
                                $('#project').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            })

        })

        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column,.print-remove').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }
    </script>
@endsection
