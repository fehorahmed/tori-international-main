@extends('layouts.app')
@section('title','Customer Ledger Report')
@section('style')
    <style>
        .table-bordered td, .table-bordered th {
            border: 1px solid #000 !important;
            vertical-align: middle;
            text-align: center;
            padding: 0.2rem !important;
        }
        .table-bordered td, .table-bordered th {
            font-size: 18px !important;
        }
        td a:hover{
            color: #000 !important;
            text-decoration: underline;
        }
        @media print{
            .table-bordered td, .table-bordered th {
                font-size: 20px !important;
            }
           td a{
                color: #000 !important;
                text-decoration: none !important;
            }
        }

    </style>
@endsection
@section('content')
    @if(request('customer_id') == '')
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
                <form target="_blank" action="{{ route('report.customer_ledger') }}">
                    <div class="card-body">
                        <div class="row">
                            
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="division_id">Division</label>
                                    <select name="division_id" id="division_id" class="form-control select2">
                                        <option value="">All Divisions</option>
                                        @foreach($divisions as $division)
                                            <option value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="district_id">District</label>
                                    <select name="district_id" id="district_id" class="form-control select2">
                                        <option value="">All Districts</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="upazila_id">Upazila</label>
                                    <select name="upazila_id" id="upazila_id" class="form-control select2">
                                        <option value="">All Upazilas</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="union_id">Pourashava/Union</label>
                                    <select name="union_id" id="union_id" class="form-control select2">
                                        <option value="">All Unions</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="village_id">Village/Road</label>
                                    <select name="village_id" id="village_id" class="form-control select2">
                                        <option value="">All Villages</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="start_date">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="start_date" autocomplete="off"
                                           name="start_date" class="form-control date-picker"
                                           placeholder="Enter Start Date" value="{{ request()->get('start_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="end_date">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required type="text" id="end_date" autocomplete="off"
                                           name="end_date" class="form-control date-picker"
                                           placeholder="Enter Start Date" value="{{ request()->get('end_date') ?? date('d-m-Y', strtotime(auth()->user()->business_date)) }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customer_id">Customer <span
                                            class="text-danger">*</span></label>
                                    <select name="customer_id" id="customer_id" class="form-control select2">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                        <option {{ request('customer_id') == $customer->id ? 'selected' : '' }} value="{{ $customer->id }}">{{ $customer->member_no }} - {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input type="submit" name="search" class="btn btn-info bg-gradient-info form-control" value="Search">
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
    @endif
@endsection

@section('script')
    @include('layouts.partial.__location_filter_js')
    <script>
        $(function () {
            function updateCustomerList() {
                let division_id = $('#division_id').val();
                let district_id = $('#district_id').val();
                let upazila_id = $('#upazila_id').val();
                let union_id = $('#union_id').val();
                let village_id = $('#village_id').val();

                $.ajax({
                    method: "GET",
                    url: "{{ route('get_filtered_customers') }}",
                    data: {
                        division_id: division_id,
                        district_id: district_id,
                        upazila_id: upazila_id,
                        union_id: union_id,
                        village_id: village_id
                    }
                }).done(function(response) {
                    let customerSelect = $('#customer_id');
                    customerSelect.html('<option value="">Select Customer</option>');
                    $.each(response, function(index, item) {
                        let memberNo = item.member_no ? item.member_no + ' - ' : '';
                        customerSelect.append('<option value="'+item.id+'">'+memberNo+item.name+'</option>');
                    });
                });
            }

            $(document).on('location_filter_changed', function() {
                updateCustomerList();
            });
        });

        var APP_URL = '{!! url()->full()  !!}';

        function getprint(print) {
            $('.print-heading').css('display', 'block');
            $('.extra_column').remove();
            $('body').html($('#' + print).html());
            window.print();
            window.location.replace(APP_URL)
        }

    </script>

@endsection

