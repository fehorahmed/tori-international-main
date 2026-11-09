@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .somiti-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(0,123,255,0.1); border-color: #007bff; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #007bff; margin-bottom: 25px; display: flex; align-items: center; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: #eee; margin-left: 15px; }
    .btn-submit { border-radius: 12px; padding: 12px 30px; font-weight: 600; transition: all 0.3s; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,123,255,0.3); }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card somiti-card">
                <div class="card-header somiti-header bg-primary text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-plus-circle mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.somiti.store') }}" method="POST">
                    @csrf
                    <div class="card-body p-5">
                        <div class="section-title"><i class="fa fa-info-circle mr-2"></i> Basic Information</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Somiti Name (English) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" placeholder="Enter English name" value="{{ old('name') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Somiti Name (Bengali)</label>
                                <input type="text" name="name_bn" class="form-control" placeholder="বাংলা নাম লিখুন" value="{{ old('name_bn') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Somiti Code (Auto)</label>
                                <input type="text" name="somiti_code" class="form-control bg-light" value="{{ $nextCode }}" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Somiti Type <span class="text-danger">*</span></label>
                                <select name="somiti_type" class="form-control select2" required>
                                    <option value="1" {{ old('somiti_type') == 1 ? 'selected' : '' }}>Male</option>
                                    <option value="2" {{ old('somiti_type') == 2 ? 'selected' : '' }}>Female</option>
                                    <option value="3" {{ old('somiti_type') == 3 ? 'selected' : '' }}>Mixed</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Member Limit <span class="text-danger">*</span></label>
                                <input type="number" name="max_member_limit" class="form-control" value="{{ old('max_member_limit', 30) }}" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Field Officer <span class="text-danger">*</span></label>
                                <select name="field_officer_id" class="form-control select2" required>
                                    <option value="">Select Field Officer</option>
                                    @foreach($fieldOfficers as $officer)
                                        <option value="{{ $officer->id }}" {{ old('field_officer_id') == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meeting Day <span class="text-danger">*</span></label>
                                <select name="meeting_day" class="form-control select2" required>
                                    @foreach($days as $day)
                                        <option value="{{ $day }}" {{ old('meeting_day') == $day ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meeting Time</label>
                                <input type="time" name="meeting_time" class="form-control" value="{{ old('meeting_time', '10:00') }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Opening Date <span class="text-danger">*</span></label>
                                <input type="text" name="opening_date" class="form-control date-picker" value="{{ old('opening_date', date('d-m-Y')) }}" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Collection Start Date <span class="text-danger">*</span></label>
                                <input type="text" name="collection_start_date" class="form-control date-picker" value="{{ old('collection_start_date', date('d-m-Y')) }}" readonly required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Latitude (Optional)</label>
                                <input type="text" name="lat" class="form-control" placeholder="e.g. 23.8103" value="{{ old('lat') }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Longitude (Optional)</label>
                                <input type="text" name="long" class="form-control" placeholder="e.g. 90.4125" value="{{ old('long') }}">
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('microfinance.somiti.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-submit">Create Somiti</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%'
        });

        if ($.isFunction($.fn.datepicker)) {
            $('.date-picker').datepicker({
                dateFormat: 'dd-mm-yy',
                changeMonth: true,
                changeYear: true,
                autoclose: true,
                todayHighlight: true
            });
        }
    });
</script>
@endsection
