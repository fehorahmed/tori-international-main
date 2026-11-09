@extends('layouts.app')
@section('title', 'Contractor Ledger')

@section('style')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('themes/backend/bower_components/select2/dist/css/select2.min.css') }}">
@endsection

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-outline card-default">
                <div class="card-header">
                    <h3 class="card-title">Filter Ledger</h3>
                </div>
                <div class="card-body">
                    <form target="_blank" action="{{ route('contractor_management.reports.ledger') }}" method="GET">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group {{ $errors->has('contractor_id') ? 'has-error' :'' }}">
                                    <label for="contractor_id">Contractor <span class="text-danger">*</span></label>
                                    <select class="form-control select2" style="width: 100%;" name="contractor_id" id="contractor_id" required>
                                        <option value="">Select Contractor</option>
                                        @foreach($contractors as $c)
                                            <option value="{{ $c->id }}" {{ (isset($contractor) && $contractor->id == $c->id) ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('contractor_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('start_date') ? 'has-error' :'' }}">
                                    <label for="start_date">Start Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" id="start_date" name="start_date" value="{{ $start_date ?? date('d-m-Y', strtotime(auth()->user()->business_date ?? date('Y-m-d'))) }}" required autocomplete="off" placeholder="Enter Start Date">
                                    @error('start_date')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group {{ $errors->has('end_date') ? 'has-error' :'' }}">
                                    <label for="end_date">End Date <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control date-picker" id="end_date" name="end_date" value="{{ $end_date ?? date('d-m-Y', strtotime(auth()->user()->business_date ?? date('Y-m-d'))) }}" required autocomplete="off" placeholder="Enter End Date">
                                    @error('end_date')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-2 mt-4">
                                <button type="submit" name="search" value="1" class="btn btn-info btn-block bg-gradient-info">Generate Report</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <!-- Select2 -->
    <script src="{{ asset('themes/backend/bower_components/select2/dist/js/select2.full.min.js') }}"></script>
    <script>
        $(function () {
            $('.select2').select2();
            $('.date-picker').datepicker({
                autoclose: true,
                format: 'dd-mm-yyyy'
            });
        });
    </script>
@endsection
