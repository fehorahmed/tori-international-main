@extends('layouts.app')
@section('title', 'Price Book History Report')
@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.price_book_history') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="product">Product</label>
                                    <select name="product" id="product" class="form-control select2">
                                        <option value="">All Product</option>
                                        @foreach($products as $product)
                                            <option {{ request('product') == $product->id ? 'selected' : '' }} value="{{ $product->id }}">{{ $product->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Start Date</label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control date-picker" name="start_date" value="{{ request('start_date') }}" autocomplete="off">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>End Date</label>
                                    <div class="input-group date">
                                        <input type="text" class="form-control date-picker" name="end_date" value="{{ request('end_date') }}" autocomplete="off">
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                        </div>
                                    </div>
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
