@extends('layouts.app')
@section('title','Outlet Setup Report')
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
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.leads') }}" target="_blank">
                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="user_id">Created By</label>
                                    <select name="user_id" id="user_id" class="form-control select2">
                                        <option value="">All Users</option>
                                        @foreach($users as $user)
                                            <option {{ request('user_id') == $user->id ? 'selected' : '' }} value="{{ $user->id }}">{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="customer_type_id">Outlet Type</label>
                                    <select name="customer_type_id" id="customer_type_id" class="form-control select2">
                                        <option value="">All Types</option>
                                        @foreach($customerTypes as $type)
                                            <option {{ request('customer_type_id') == $type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="market_id">Market / Area</label>
                                    <select name="market_id" id="market_id" class="form-control select2">
                                        <option value="">All Markets</option>
                                        @foreach($markets as $market)
                                            <option {{ request('market_id') == $market->id ? 'selected' : '' }} value="{{ $market->id }}">{{ $market->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date', date('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span
                                            class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date', date('d-m-Y')) }}"
                                           name="end_date" class="form-control date-picker" id="end_date"
                                           placeholder="Enter End Date">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <input style="margin-top: -1px;" type="submit" name="search"
                                           class="btn btn-info bg-gradient-info form-control" value="Search">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

