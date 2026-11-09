@extends('layouts.app')
@section('title', 'Edit Trainee Visit Type')
@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-outline card-primary shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">Edit Visit Type</h3>
                </div>
                <form action="{{ route('trainee.visit_type.update', $traineeVisitType->id) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group">
                            <label for="name">Type Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" value="{{ $traineeVisitType->name }}" class="form-control" id="name" required>
                        </div>
                        <div class="form-group">
                            <label>Status <span class="text-danger">*</span></label>
                            <div class="mt-2">
                                <div class="icheck-success d-inline mr-3">
                                    <input type="radio" id="active" name="status" value="1" {{ $traineeVisitType->status == 1 ? 'checked' : '' }}>
                                    <label for="active">Active</label>
                                </div>
                                <div class="icheck-danger d-inline">
                                    <input type="radio" id="inactive" name="status" value="0" {{ $traineeVisitType->status == 0 ? 'checked' : '' }}>
                                    <label for="inactive">Inactive</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white border-top-0">
                        <button type="submit" class="btn btn-primary px-4">Update Type</button>
                        <a href="{{ route('trainee.visit_type.index') }}" class="btn btn-default float-right">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
