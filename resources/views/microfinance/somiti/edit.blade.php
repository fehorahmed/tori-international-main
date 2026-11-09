@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .somiti-card { border: none; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
    .somiti-header { background: linear-gradient(135deg, #28a745 0%, #218838 100%); border-radius: 20px 20px 0 0 !important; padding: 25px; }
    .form-label { font-weight: 600; color: #495057; margin-bottom: 8px; }
    .form-control { border-radius: 12px; padding: 12px 15px; border: 1px solid #dee2e6; transition: all 0.3s; }
    .form-control:focus { box-shadow: 0 0 0 4px rgba(40,167,69,0.1); border-color: #28a745; }
    .section-title { font-size: 1.1rem; font-weight: 700; color: #28a745; margin-bottom: 25px; display: flex; align-items: center; }
    .section-title::after { content: ''; flex: 1; height: 1px; background: #eee; margin-left: 15px; }
    .btn-submit { border-radius: 12px; padding: 12px 30px; font-weight: 600; transition: all 0.3s; }
    .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40,167,69,0.3); }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card somiti-card">
                <div class="card-header somiti-header text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-edit mr-2"></i> {{ $pageTitle }}</h3>
                </div>
                <form action="{{ route('microfinance.somiti.update', $somiti->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-5">
                        <div class="section-title"><i class="fa fa-info-circle mr-2"></i> Update Information</div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Somiti Name (English) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ $somiti->name }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Somiti Name (Bengali)</label>
                                <input type="text" name="name_bn" class="form-control" value="{{ $somiti->name_bn }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Somiti Code</label>
                                <input type="text" name="somiti_code" class="form-control bg-light" value="{{ $somiti->somiti_code }}" readonly>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Somiti Type <span class="text-danger">*</span></label>
                                <select name="somiti_type" class="form-control select2" required>
                                    <option value="1" {{ $somiti->somiti_type == 1 ? 'selected' : '' }}>Male</option>
                                    <option value="2" {{ $somiti->somiti_type == 2 ? 'selected' : '' }}>Female</option>
                                    <option value="3" {{ $somiti->somiti_type == 3 ? 'selected' : '' }}>Mixed</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Max Member Limit <span class="text-danger">*</span></label>
                                <input type="number" name="max_member_limit" class="form-control" value="{{ $somiti->max_member_limit }}" min="1" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Field Officer <span class="text-danger">*</span></label>
                                <select name="field_officer_id" class="form-control select2" required>
                                    <option value="">Select Field Officer</option>
                                    @foreach($fieldOfficers as $officer)
                                        <option value="{{ $officer->id }}" {{ $somiti->field_officer_id == $officer->id ? 'selected' : '' }}>{{ $officer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meeting Day <span class="text-danger">*</span></label>
                                <select name="meeting_day" class="form-control select2" required>
                                    @foreach($days as $day)
                                        <option value="{{ $day }}" {{ $somiti->meeting_day == $day ? 'selected' : '' }}>{{ $day }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Meeting Time</label>
                                <input type="time" name="meeting_time" class="form-control" value="{{ $somiti->meeting_time }}">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Opening Date <span class="text-danger">*</span></label>
                                <input type="text" name="opening_date" class="form-control date-picker" value="{{ $somiti->opening_date ? $somiti->opening_date->format('d-m-Y') : '' }}" readonly required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Collection Start Date <span class="text-danger">*</span></label>
                                <input type="text" name="collection_start_date" class="form-control date-picker" value="{{ $somiti->collection_start_date ? $somiti->collection_start_date->format('d-m-Y') : '' }}" readonly required>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Latitude</label>
                                <input type="text" name="lat" class="form-control" value="{{ $somiti->lat }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Longitude</label>
                                <input type="text" name="long" class="form-control" value="{{ $somiti->long }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-control select2">
                                    <option value="1" {{ $somiti->status == 1 ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ $somiti->status == 0 ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="text-right mt-4">
                            <a href="{{ route('microfinance.somiti.index') }}" class="btn btn-secondary btn-submit mr-2">Cancel</a>
                            <button type="submit" class="btn btn-success btn-submit">Update Somiti</button>
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
