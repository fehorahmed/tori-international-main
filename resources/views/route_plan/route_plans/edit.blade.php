@extends('layouts.app')
@section('title','Edit Route Plan')
@section('content')
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Edit Route Plan</h3>
                    <a href="{{ route('route-plans.index') }}" class="btn btn-dark btn-sm float-right"><i class="fa fa-arrow-left"></i> Back</a>
                </div>
                <!-- /.card-header -->
                <div class="card-body">
                    <form action="{{ route('route-plans.update', $route_plan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="form-group {{ $errors->has('sr_id') ? 'has-error' :'' }}">
                            <label for="sr_id">Select SR <span class="text-danger">*</span></label>
                            <select name="sr_id" id="sr_id" class="form-control select2">
                                <option value="">Select SR</option>
                                @foreach($srs as $sr)
                                    <option {{ old('sr_id', $route_plan->sr_id) == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }} ({{ $sr->mobile_no }})</option>
                                @endforeach
                            </select>
                            @error('sr_id')
                            <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('day_of_week') ? 'has-error' :'' }}">
                            <label for="day_of_week">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" id="day_of_week" class="form-control select2">
                                <option value="">Select Day</option>
                                @foreach($days as $day)
                                    <option {{ old('day_of_week', $route_plan->day_of_week) == $day ? 'selected' : '' }} value="{{ $day }}">{{ $day }}</option>
                                @endforeach
                            </select>
                            @error('day_of_week')
                            <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group {{ $errors->has('market_id') ? 'has-error' :'' }}">
                            <label for="market_id">Select Market <span class="text-danger">*</span></label>
                            <select name="market_id" id="market_id" class="form-control select2">
                                <option value="">Select Market</option>
                                @foreach($markets as $market)
                                    <option {{ old('market_id', $route_plan->market_id) == $market->id ? 'selected' : '' }} value="{{ $market->id }}">{{ $market->name }} ({{ $market->upazila->name ?? '' }})</option>
                                @endforeach
                            </select>
                            @error('market_id')
                            <span class="help-block text-danger">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="form-group text-right">
                            <button type="submit" class="btn btn-primary bg-gradient-primary">Update Route <i class="fa fa-save"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
