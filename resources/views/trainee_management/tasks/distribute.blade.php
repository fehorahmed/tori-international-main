@extends('layouts.app')
@section('title','Distribute Trainee Targets')
@section('style')
    <link rel="stylesheet" href="{{ asset('themes/backend/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
    <style>
        .target-input { width: 120px; text-align: center; font-weight: bold; }
        .trainee-row:hover { background-color: #f8f9fa; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-share-alt mr-2"></i> Distribute Monthly Visit Targets</h3>
                </div>
                
                <div class="card-body">
                    <form action="{{ route('trainee.tasks.distribute') }}" method="get" id="monthForm" class="mb-4">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <div class="form-group mb-0">
                                    <label>Select Target Month</label>
                                    <div class="input-group">
                                        <input type="text" name="month" class="form-control month-picker" value="{{ \Carbon\Carbon::parse($formattedMonth.'-01')->format('m-Y') }}" readonly>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-info">Change Month</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8 text-right">
                                <h5 class="text-muted mb-0">Target Month: <span class="text-primary font-weight-bold">{{ \Carbon\Carbon::parse($formattedMonth.'-01')->format('F, Y') }}</span></h5>
                            </div>
                        </div>
                    </form>

                    <form action="{{ route('trainee.tasks.store_distribute') }}" method="post">
                        @csrf
                        <input type="hidden" name="month" value="{{ $formattedMonth }}">
                        
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped">
                                <thead class="bg-light">
                                <tr>
                                    <th width="50">SL</th>
                                    <th>Trainee Details</th>
                                    <th class="text-center" width="200">Visit Count Target</th>
                                    <th>Note / Status</th>
                                </tr>
                                </thead>
                                <tbody>
                                @forelse($trainees as $trainee)
                                    <tr class="trainee-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $trainee->image ? asset($trainee->image) : asset('img/avatar.png') }}" class="img-circle mr-2" width="35" height="35" style="object-fit: cover;">
                                                <div>
                                                    <span class="font-weight-bold d-block">{{ $trainee->name }}</span>
                                                    <small class="text-muted">{{ $trainee->mobile_no }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <input type="number" name="targets[{{ $trainee->id }}]" 
                                                   class="form-control target-input mx-auto" 
                                                   value="{{ $trainee->target_value }}" 
                                                   min="0" placeholder="0">
                                        </td>
                                        <td>
                                            @if($trainee->target_value > 0)
                                                <span class="badge badge-success">Target Set</span>
                                            @else
                                                <span class="badge badge-warning">No Target</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center p-4">
                                            <div class="text-muted">No active trainees found in your branch.</div>
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="card-footer bg-white border-top-0 text-center mt-3">
                            <button type="submit" class="btn btn-success btn-lg px-5 shadow-sm" {{ count($trainees) == 0 ? 'disabled' : '' }}>
                                <i class="fas fa-save mr-1"></i> Save All Targets
                            </button>
                            <a href="{{ route('trainee.tasks.index') }}" class="btn btn-default btn-lg px-5 border">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script src="{{ asset('themes/backend/bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
    <script>
        $(function () {
            // MonthPicker is already initialized in layout for .month-picker
        });
    </script>
@endsection
