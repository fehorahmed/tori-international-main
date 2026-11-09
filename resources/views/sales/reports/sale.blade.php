@extends('layouts.app')
@section('title','Sale Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 2px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
        }
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('report.sale') }}">
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
                            @if (auth()->user()->role == 'Superadmin')
                                <div class="col-12 col-md-3">
                                    <div class="form-group">
                                        <label>Sister Concern</label>
                                        <select class="form-control select2" id="sister_concern" name="sister_concern">
                                            <option value="">All Sister Concern</option>
                                            @foreach ($sister_concerns as $sister_concern)
                                                <option value="{{ $sister_concern->id }}" {{ request('sister_concern') == $sister_concern->id ? 'selected' : ''  }}>{{ $sister_concern->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="customer">Customer</label>
                                    <select name="customer" id="customer" class="form-control select2">
                                        <option value="">All Customer</option>
                                        @foreach($customers as $customer)
                                            <option {{ request('customer') == $customer->id ? 'selected' : '' }} value="{{ $customer->id }}">{{ $customer->member_no }} - {{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                    <input type="hidden" name="product_type" value="{{ request('product_type') }}">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
@include('layouts.partial.__location_filter_js')
    <script>
        $(function () {
            var customerSelected = '{{ old('customer',request('customer')) }}';

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
                    let customerSelect = $('#customer');
                    customerSelect.html('<option value="">All Customer</option>');
                    $.each(response, function(index, item) {
                        let memberNo = item.member_no ? item.member_no + ' - ' : '';
                        if (customerSelected == item.id) {
                            customerSelect.append('<option value="'+item.id+'" selected>'+memberNo+item.name+'</option>');
                        } else {
                            customerSelect.append('<option value="'+item.id+'">'+memberNo+item.name+'</option>');
                        }
                    });
                });
            }

            $(document).on('location_filter_changed', function() {
                updateCustomerList();
            });

            $("#sister_concern").change(function () {
                let sisterConcernId = $(this).val();
                $('#customer').html('<option value="">All Customer</option>');
                if (sisterConcernId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_sister_concern_wise_customer') }}",
                        data: {sisterConcernId: sisterConcernId}
                    }).done(function (data) {
                        $.each(data, function (index, item) {
                            if (customerSelected == item.id)
                                $('#customer').append('<option value="' + item.id + '" selected>' + item.name + '</option>');
                            else
                                $('#customer').append('<option value="' + item.id + '">' + item.name + '</option>');
                        });
                    });
                }
            })
            $("#sister_concern").trigger('change');
        });

    </script>
@endsection
