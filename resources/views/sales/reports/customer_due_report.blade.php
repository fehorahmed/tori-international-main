@extends('layouts.app')
@section('title','Customer Due Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 4px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 4px;
        }
        
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12">
            <form action="{{ route('report.customer_due') }}" method="GET" target="_blank">
                <div class="card card-default">
                    <div class="card-header">
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
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group mr-2">
                                    <label for="customer_type" class="mr-2">Customer Type:</label>
                                    <select name="customer_type" id="customer_type" class="form-control select2">
                                        <option value="">All</option>
                                        <option value="{{ \App\Enumeration\SupplierType::$CUSTOMER }}" {{ request('customer_type') == \App\Enumeration\SupplierType::$CUSTOMER ? 'selected' : '' }}>General Client</option>
                                        <option value="{{ \App\Enumeration\SupplierType::$CORPORATE_CUSTOMER }}" {{ request('customer_type') == \App\Enumeration\SupplierType::$CORPORATE_CUSTOMER ? 'selected' : '' }}>Corporate Client</option>
                                        <option value="{{ \App\Enumeration\SupplierType::$DEALER }}" {{ request('customer_type') == \App\Enumeration\SupplierType::$DEALER ? 'selected' : '' }}>Dealer</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search" class="btn btn-primary bg-gradient-primary form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@include('layouts.partial.__location_filter_js')

