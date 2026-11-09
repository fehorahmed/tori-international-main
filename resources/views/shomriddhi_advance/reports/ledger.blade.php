@extends('layouts.app')

@section('title', 'Customer Passbook')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header bg-gradient-info text-white">
            <h5 class="card-title mb-0"><i class="fas fa-file-invoice mr-2"></i> Customer Passbook (Ledger)</h5>
        </div>
        <div class="card-body">
            <form target="_blank" action="{{ route('shomriddhi-advance.reports.ledger') }}" method="GET" class="mb-4">
                <div class="row mb-3">
                    <div class="col-md-2">
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
                    <div class="col-md-3">
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

                <div class="row align-items-end">
                    <div class="col-md-4">
                        <label for="customer_id">Select Customer <span class="text-danger">*</span></label>
                        <select name="customer_id" id="customer_id" class="form-control select2" required style="width: 100%;">
                            <option value="">Select a Customer</option>
                            @foreach($accounts as $account)
                                @if($account->customer)
                                    <option value="{{ $account->customer->id }}" {{ request('customer_id') == $account->customer->id ? 'selected' : '' }}>
                                        {{ $account->customer->name }} ({{ $account->customer->member_no ?? 'N/A' }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date">Start Date</label>
                        <input type="text" name="start_date" id="start_date" class="form-control date-picker" value="{{ request('start_date') ?? date('d-m-Y',strtotime(auth()->user()->business_date)) }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date">End Date</label>
                        <input type="text" name="end_date" id="end_date" class="form-control date-picker" value="{{ request('end_date') ?? date('d-m-Y',strtotime(auth()->user()->business_date)) }}">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-search"></i> Generate</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('script')
@include('layouts.partial.__location_filter_js')
<script>
    $(document).ready(function() {
        $('.select2').select2();

        function updateCustomerList() {
            let division_id = $('#division_id').val();
            let district_id = $('#district_id').val();
            let upazila_id = $('#upazila_id').val();
            let union_id = $('#union_id').val();
            let village_id = $('#village_id').val();

            $.ajax({
                method: "GET",
                url: "{{ route('shomriddhi-advance.reports.get_filtered_accounts') }}",
                data: {
                    division_id: division_id,
                    district_id: district_id,
                    upazila_id: upazila_id,
                    union_id: union_id,
                    village_id: village_id
                }
            }).done(function(response) {
                let customerSelect = $('#customer_id');
                customerSelect.html('<option value="">Select a Customer</option>');
                $.each(response, function(index, item) {
                    let memberNo = item.member_no ? item.member_no : 'N/A';
                    customerSelect.append('<option value="'+item.id+'">'+item.name+' ('+memberNo+')</option>');
                });
            });
        }

        $(document).on('location_filter_changed', function() {
            updateCustomerList();
        });
    });
</script>
@endpush
