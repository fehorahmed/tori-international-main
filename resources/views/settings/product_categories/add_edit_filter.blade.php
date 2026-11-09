@extends('layouts.app')
@section('title','Add/Edit Filters for '.$product_category->name)
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Assign Filters to <b>{{ $product_category->name }}</b></h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form action="{{ route('product-categories.add_edit_filter', ['product_category' => $product_category->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Available Filters</label>
                            <div class="col-sm-10">
                                <div class="row">
                                    @foreach($filters as $filter)
                                        <div class="col-md-3">
                                            <div class="icheck-primary">
                                                <input type="checkbox" id="filter_{{ $filter->id }}" name="filters[]" value="{{ $filter->id }}" {{ in_array($filter->id, $selectedFilters) ? 'checked' : '' }}>
                                                <label for="filter_{{ $filter->id }}">
                                                    {{ $filter->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('filters')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save Assignments</button>
                        <a href="{{ route('product-categories.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection
