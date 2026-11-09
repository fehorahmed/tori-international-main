@extends('layouts.app')
@section('title', $reportTitle)

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
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <div class="card-title"><i class="fa fa-filter text-info mr-1"></i> {{ $reportTitle }} Filter</div>
            </div>
            <!-- /.card-header -->
            <!-- form start -->
            <form action="{{ $reportRoute }}" method="GET" target="_blank">
                <div class="card-body">
                    <div class="row">
                        @if(request()->routeIs('fixed-assets.reports.register') || request()->routeIs('fixed-assets.reports.depreciation') || request()->routeIs('fixed-assets.reports.addition_disposal'))
                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label>Category (Optional)</label>
                                <select name="category_id" class="form-control select2">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option {{ request('category_id') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }} - {{ $category->code }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        @endif
                        


                        <div class="col-12 col-md-3">
                            <div class="form-group">
                                <label>Status (Optional)</label>
                                <select name="status" class="form-control select2">
                                    <option value="">All Statuses</option>
                                    <option {{ request('status') == 'active' ? 'selected' : '' }} value="active">Active</option>
                                    <option {{ request('status') == 'sold' ? 'selected' : '' }} value="sold">Sold</option>
                                    <option {{ request('status') == 'scrapped' ? 'selected' : '' }} value="scrapped">Scrapped</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input autocomplete="off" type="text" value="{{ request('start_date') }}"
                                       name="start_date" class="form-control date-picker" id="start_date"
                                       placeholder="Start Date">
                            </div>
                        </div>
                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input autocomplete="off" type="text" value="{{ request('end_date') }}"
                                       name="end_date" class="form-control date-picker" id="end_date"
                                       placeholder="End Date">
                            </div>
                        </div>

                        <div class="col-12 col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <input style="margin-top: -1px;" type="submit" name="search"
                                       class="btn btn-info bg-gradient-info form-control" value="Search">
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
