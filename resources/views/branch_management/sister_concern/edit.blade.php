@extends('layouts.app')
@section('title',$typeName.' Edit')
@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">{{ $typeName }} Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('sister-concerns.update',['sister_concern'=>$sisterConcern->id]) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body">
                        <div class="form-group row {{ $errors->has('name') ? 'has-error' :'' }}">
                            <label for="name" class="col-sm-2 col-form-label">Name <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('name',$sisterConcern->name) }}" name="name" class="form-control" id="name" placeholder="Enter Name">
                                @error('name')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('code') ? 'has-error' :'' }}">
                            <label for="code" class="col-sm-2 col-form-label">Code <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('code', $sisterConcern->code) }}" name="code" class="form-control" id="code" placeholder="Auto Generated Code" readonly>
                                @error('code')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('logo') ? 'has-error' :'' }}">
                            <label for="logo" class="col-sm-2 col-form-label">Logo </label>
                            <div class="col-sm-10">
                                <input type="file" value="{{ old('logo') }}" name="logo" class="form-control" id="logo" placeholder="Enter Logo">
                                @if($sisterConcern->logo && file_exists(public_path($sisterConcern->logo)))
                                    <div class="mt-2">
                                        <img src="{{ asset($sisterConcern->logo) }}" alt="Logo" style="max-height: 50px;">
                                    </div>
                                @endif
                                @error('logo')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('auth_signature') ? 'has-error' :'' }}">
                            <label for="auth_signature" class="col-sm-2 col-form-label">Authorized Signature</label>
                            <div class="col-sm-10">
                                <input type="file" name="auth_signature" class="form-control" id="auth_signature">
                                @if($sisterConcern->auth_signature && file_exists(public_path($sisterConcern->auth_signature)))
                                    <div class="mt-2">
                                        <img src="{{ asset($sisterConcern->auth_signature) }}" alt="Signature" style="max-height: 50px;">
                                    </div>
                                @endif
                                @error('auth_signature')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('official_seal') ? 'has-error' :'' }}">
                            <label for="official_seal" class="col-sm-2 col-form-label">Official Seal</label>
                            <div class="col-sm-10">
                                <input type="file" name="official_seal" class="form-control" id="official_seal">
                                @if($sisterConcern->official_seal && file_exists(public_path($sisterConcern->official_seal)))
                                    <div class="mt-2">
                                        <img src="{{ asset($sisterConcern->official_seal) }}" alt="Official Seal" style="max-height: 50px;">
                                    </div>
                                @endif
                                @error('official_seal')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('trade_license_no') ? 'has-error' :'' }}">
                            <label for="trade_license_no" class="col-sm-2 col-form-label">Trade License No</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('trade_license_no',$sisterConcern->trade_license_no) }}" name="trade_license_no" class="form-control" id="trade_license_no" placeholder="Enter Trade License Number">
                                @error('trade_license_no')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('tin_no') ? 'has-error' :'' }}">
                            <label for="tin_no" class="col-sm-2 col-form-label">TIN Number</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('tin_no',$sisterConcern->tin_no) }}" name="tin_no" class="form-control" id="tin_no" placeholder="Enter TIN Number">
                                @error('tin_no')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('bin_no') ? 'has-error' :'' }}">
                            <label for="bin_no" class="col-sm-2 col-form-label">BIN Number</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('bin_no',$sisterConcern->bin_no) }}" name="bin_no" class="form-control" id="bin_no" placeholder="Enter BIN Number">
                                @error('bin_no')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('contact') ? 'has-error' :'' }}">
                            <label for="contact" class="col-sm-2 col-form-label">Contact</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('contact',$sisterConcern->contact) }}" name="contact" class="form-control" id="contact" placeholder="e.g: Tel.: +880721 -000000, Mobile: +8801711-000000">
                                @error('contact')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('email') ? 'has-error' :'' }}">
                            <label for="email" class="col-sm-2 col-form-label">Email</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('email',$sisterConcern->email) }}" name="email" class="form-control" id="email" placeholder="e.g: E-mail: contact@example.bd">
                                @error('email')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('business_date') ? 'has-error' :'' }}">
                            <label for="business_date" class="col-sm-2 col-form-label">Business Date <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('business_date',date('d-m-Y',strtotime($sisterConcern->business_date))) }}" name="business_date" class="form-control date-picker" id="business_date" placeholder="Enter Business Date" autocomplete="off">
                                @error('business_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror 
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('establishment_date') ? 'has-error' :'' }}">
                            <label for="establishment_date" class="col-sm-2 col-form-label">Establishment Date</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('establishment_date',$sisterConcern->establishment_date ? date('d-m-Y',strtotime($sisterConcern->establishment_date)) : '') }}" name="establishment_date" class="form-control date-picker" id="establishment_date" placeholder="Enter Establishment Date" autocomplete="off">
                                @error('establishment_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('certificate_expiry_date') ? 'has-error' :'' }}">
                            <label for="certificate_expiry_date" class="col-sm-2 col-form-label">Certificate Expiry Date</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('certificate_expiry_date',$sisterConcern->certificate_expiry_date ? date('d-m-Y',strtotime($sisterConcern->certificate_expiry_date)) : '') }}" name="certificate_expiry_date" class="form-control date-picker" id="certificate_expiry_date" placeholder="Enter Certificate Expiry Date (Keep blank for Lifetime)" autocomplete="off">
                                @error('certificate_expiry_date')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row {{ $errors->has('address') ? 'has-error' :'' }}">
                            <label for="address" class="col-sm-2 col-form-label">Address <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <textarea name="address" class="form-control" id="address" placeholder="Enter Address">{{ old('address',$sisterConcern->address) }}</textarea>
                                @error('address')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('district') ? 'has-error' :'' }}">
                            <label for="district" class="col-sm-2 col-form-label">District</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('district',$sisterConcern->district) }}" name="district" class="form-control" id="district" placeholder="Enter District">
                                @error('district')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('upazila') ? 'has-error' :'' }}">
                            <label for="upazila" class="col-sm-2 col-form-label">Upazila</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('upazila',$sisterConcern->upazila) }}" name="upazila" class="form-control" id="upazila" placeholder="Enter Upazila">
                                @error('upazila')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('post_code') ? 'has-error' :'' }}">
                            <label for="post_code" class="col-sm-2 col-form-label">Post Code</label>
                            <div class="col-sm-10">
                                <input type="text" value="{{ old('post_code',$sisterConcern->post_code) }}" name="post_code" class="form-control" id="post_code" placeholder="Enter Post Code">
                                @error('post_code')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('office_rent') ? 'has-error' :'' }}">
                            <label for="office_rent" class="col-sm-2 col-form-label">Office Rent</label>
                            <div class="col-sm-10">
                                <input type="number" step="0.01" value="{{ old('office_rent', $sisterConcern->office_rent) }}" name="office_rent" class="form-control" id="office_rent" placeholder="Enter Office Rent Amount">
                                @error('office_rent')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('electricity_bill') ? 'has-error' :'' }}">
                            <label for="electricity_bill" class="col-sm-2 col-form-label">Electricity Bill</label>
                            <div class="col-sm-10">
                                <input type="number" step="0.01" value="{{ old('electricity_bill', $sisterConcern->electricity_bill) }}" name="electricity_bill" class="form-control" id="electricity_bill" placeholder="Enter Electricity Bill Amount">
                                @error('electricity_bill')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>

                        <div class="form-group row {{ $errors->has('status') ? 'has-error' :'' }}">
                            <label class="col-sm-2 col-form-label">Status <span class="text-danger">*</span></label>
                            <div class="col-sm-10">
                                <div class="icheck-success d-inline">
                                    <input checked type="radio" id="active" name="status" value="1" {{ old('status',$sisterConcern->status) == '1' ? 'checked' : '' }}>
                                    <label for="active">
                                        Active
                                    </label>
                                </div>

                                <div class="icheck-danger d-inline">
                                    <input type="radio" id="inactive" name="status" value="0" {{ old('status',$sisterConcern->status) == '0' ? 'checked' : '' }}>
                                    <label for="inactive">
                                        Inactive
                                    </label>
                                </div>

                                @error('status')
                                <span class="help-block">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-sm">Save</button>
                        <a href="{{ route('sister-concerns.index') }}" class="btn btn-danger bg-gradient-danger btn-sm float-right">Cancel</a>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection
