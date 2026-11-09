@extends('layouts.app')
@section('title', 'Issue Employee Card')
@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card card-default">
                <div class="card-header bg-gradient-info text-white">
                    <h5 class="card-title mb-0">
                        <i class="fa fa-plus-circle mr-2"></i> Issue Employee Card (কর্মকর্তা কার্ড তৈরি করুন)
                    </h5>
                </div>
                <form action="{{ route('card-management.employee.store') }}" method="POST">
                    @csrf
                    <div class="card-body p-4">
                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                {{ session('error') }}
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                            </div>
                        @endif

                        <div class="form-group mb-4" id="employee_wrapper">
                            <label class="small font-weight-bold text-dark">Select Employee (কর্মচারী নির্বাচন করুন) <span class="text-danger">*</span></label>
                            <select name="holder_id" id="holder_id_employee" class="select2 form-control" style="width: 100%;" required>
                                <option value="">Select Employee</option>
                                @foreach($employees as $employee)
                                    <option value="{{ $employee->id }}">{{ $employee->name }} ({{ $employee->mobile_no ?? 'No Mobile' }})</option>
                                @endforeach
                            </select>
                            <span class="text-muted small">Only employees who do not currently have an active card are shown.</span>
                        </div>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between">
                        <a href="{{ route('card-management.employee.index') }}" class="btn btn-secondary btn-sm"><i class="fa fa-arrow-left mr-1"></i> Back to Dashboard</a>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm"><i class="fa fa-check-circle mr-1"></i> Issue Card</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('.select2').select2({
                placeholder: "Select an Employee",
                allowClear: true
            });
        });
    </script>
@endsection
