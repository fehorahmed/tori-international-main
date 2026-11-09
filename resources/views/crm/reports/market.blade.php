@extends('layouts.app')
@section('title','Market Report')
@section('style')
    <style>
        .table-bordered thead td, .table-bordered thead th {
            vertical-align: middle;
            border: 1px solid #000!important;
            padding: 5px;
        }
        .table-bordered td, .table-bordered th {
            border: 1px solid #000000!important;
            vertical-align: middle;
            padding: 5px;
        }
        
        @page{
            margin: .3in .5in .5in .5in;
        }
    </style>
@endsection
@section('content')
    <div class="row">
        <div class="col-12 extra-remove">
            <div class="card card-default">
                <div class="card-header">
                    <div class="card-title">Data Filter</div>
                </div>
                <div class="card-body">
                    <form action="{{ route('report.markets') }}" target="_blank">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="division_id">Division</label>
                                    <select name="division_id" id="division_id" class="form-control select2">
                                        <option value="">All Divisions</option>
                                        @foreach($divisions as $division)
                                            <option {{ request('division_id') == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="district_id">District</label>
                                    <select name="district_id" id="district_id" class="form-control select2">
                                        <option value="">All Districts</option>
                                        @foreach($districts as $district)
                                            <option {{ request('district_id') == $district->id ? 'selected' : '' }} value="{{ $district->id }}">{{ $district->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label for="upazila_id">Upazila</label>
                                    <select name="upazila_id" id="upazila_id" class="form-control select2">
                                        <option value="">All Upazilas</option>
                                        @foreach($upazilas as $upazila)
                                            <option {{ request('upazila_id') == $upazila->id ? 'selected' : '' }} value="{{ $upazila->id }}">{{ $upazila->name }}</option>
                                        @endforeach
                                    </select>
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

@section('script')
    <script>
        $(function() {
            $('#division_id').change(function () {
                let divisionId = $(this).val();
                $('#district_id').html('<option value="">All Districts</option>');
                $('#upazila_id').html('<option value="">All Upazilas</option>');

                if (divisionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_districts') }}",
                        data: { divisionId: divisionId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#district_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
            });

            $('#district_id').change(function () {
                let districtId = $(this).val();
                $('#upazila_id').html('<option value="">All Upazilas</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#upazila_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
            });
        });
    </script>
@endsection
