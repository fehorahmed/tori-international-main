@extends('layouts.app')
@section('title', 'Defect / Damage Stock Report')
@section('content')
<div class="row">
    <div class="col-12">
        <div class="card card-default">
            <div class="card-header">
                <h3 class="card-title">Defect / Damage Stock Report</h3>
            </div>
            <div class="card-body">
                <form id="report-form" action="{{ route('returns-management.report.defect_stock') }}" target="_blank">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="warehouse_id">Warehouse</label>
                                <select class="form-control select2" name="warehouse_id" id="warehouse_id">
                                    @foreach($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="category_id">Category</label>
                                <select class="form-control select2" name="category_id" id="category_id">
                                    <option value="">All Categories</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3" style="margin-top: 30px;">
                            <button type="submit" name="search" value="1" class="btn btn-info px-5">
                                <i class="fas fa-search"></i> Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
