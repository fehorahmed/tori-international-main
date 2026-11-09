@extends('layouts.app')
@section('title','Stock Return Report')
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
                    <div class="card-title"><i class="fa fa-filter text-info mr-1"></i> Report Filter</div>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('stock-transfers.return_report') }}">
                        <div class="row">
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Direction</label>
                                    <select class="form-control select2" name="direction">
                                        <option value="sent" {{ request('direction') == 'sent' ? 'selected' : '' }}>Sent (Outgoing)</option>
                                        <option value="received" {{ request('direction') == 'received' ? 'selected' : '' }}>Received (Incoming)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-3">
                                <div class="form-group">
                                    <label>Target Branch</label>
                                    <select class="form-control select2" name="sister_concern">
                                        <option value="">All Branches</option>
                                        @php
                                            $loggedInScId = auth()->user()->active_sister_concern_id ?? auth()->user()->sister_concern_id;
                                        @endphp
                                        @foreach ($sister_concerns as $sc)
                                            @if ($sc->id != $loggedInScId)
                                                <option value="{{ $sc->id }}" {{ request('sister_concern') == $sc->id ? 'selected' : '' }}>{{ $sc->name }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select class="form-control select2" name="status">
                                        <option value="">All</option>
                                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Pending Dispatch</option>
                                        <option value="2" {{ request('status') === '2' ? 'selected' : '' }}>In Transit</option>
                                        <option value="3" {{ request('status') === '3' ? 'selected' : '' }}>Received</option>
                                        <option value="5" {{ request('status') === '5' ? 'selected' : '' }}>Rejected</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label>Return Reason</label>
                                    <select class="form-control select2" name="return_reason">
                                        <option value="">All Reasons</option>
                                        <option value="Damaged Product" {{ request('return_reason') == 'Damaged Product' ? 'selected' : '' }}>Damaged Product</option>
                                        <option value="Defective/Faulty" {{ request('return_reason') == 'Defective/Faulty' ? 'selected' : '' }}>Defective / Faulty</option>
                                        <option value="Unsold/Dead Stock" {{ request('return_reason') == 'Unsold/Dead Stock' ? 'selected' : '' }}>Unsold / Dead Stock</option>
                                        <option value="Replacement Request" {{ request('return_reason') == 'Replacement Request' ? 'selected' : '' }}>Replacement Request</option>
                                        <option value="Expired" {{ request('return_reason') == 'Expired' ? 'selected' : '' }}>Expired</option>
                                        <option value="Other" {{ request('return_reason') == 'Other' ? 'selected' : '' }}>Other</option>
                                    </select>
                                </div>
                            </div>


                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="start_date" class="col-form-label">Start Date <span class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('start_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
                                           name="start_date" class="form-control date-picker" id="start_date"
                                           placeholder="Enter Start Date">
                                </div>
                            </div>
                            <div class="col-12 col-md-2">
                                <div class="form-group">
                                    <label for="end_date" class="col-form-label">End Date <span class="text-danger">*</span></label>
                                    <input required autocomplete="off" type="text" value="{{ request('end_date', \Carbon\Carbon::parse(auth()->user()->activeSisterConcern->business_date ?? auth()->user()->sisterConcern->business_date ?? now())->format('d-m-Y')) }}"
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

