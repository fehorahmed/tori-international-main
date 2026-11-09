@extends('layouts.app')
@section('title', $pageTitle)

@section('style')
<style>
    .admission-card { border: none; border-radius: 25px; background: #fff; }
    .admission-header { background: linear-gradient(135deg, #17a2b8 0%, #007bff 100%); border-radius: 25px 25px 0 0 !important; padding: 20px 30px; }
    .section-title { color: #007bff; font-weight: 700; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 25px; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 1px; margin-top: 30px; }
    .form-control { border-radius: 10px; border: 1px solid #e1e5eb; padding: 8px 15px; height: 40px; }
    /* Select2 Height Match */
    .select2-container .select2-selection--single {
        height: 40px !important;
        border-radius: 10px !important;
        border: 1px solid #e1e5eb !important;
        background-color: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 45px !important;
        padding-left: 10px !important;
        color: #495057 !important;
    }
    .image-upload-box {
        width: 100%; height: 150px; border: 2px dashed #d1d9e6; border-radius: 15px;
        display: flex; flex-direction: column; align-items: center; justify-content: center;
        cursor: pointer; transition: all 0.3s; overflow: hidden; background: #f8fafc; position: relative;
    }
    .image-upload-box:hover { border-color: #007bff; background: #f0f7ff; }
    .image-upload-box img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .image-upload-box .placeholder-content { text-align: center; color: #94a3b8; }
    .image-upload-box .placeholder-content i { font-size: 2.5rem; margin-bottom: 5px; display: block; }
    .image-upload-box.has-image img { display: block; }
    .image-upload-box.has-image .placeholder-content { display: none; }
    .file-input { display: none; }
    .nominee-item { background: #fff; border: 1px solid #e1e5eb !important; border-radius: 20px; padding: 25px; margin-bottom: 25px; }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card admission-card shadow-lg mb-5">
                <div class="card-header admission-header text-white">
                    <h3 class="card-title mb-0"><i class="fa fa-edit mr-2"></i> Edit Member: {{ $member->member_no }}</h3>
                </div>
                <form enctype="multipart/form-data" action="{{ route('microfinance.member.update', $member->id) }}" class="form-horizontal" method="post">
                    @csrf
                    @method('PUT')
                    <div class="card-body p-4 p-md-5">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius: 15px;">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                        @endif
                        
                        <!-- Customer Association -->
                        <h5 class="section-title" style="margin-top: 0;"><i class="fa fa-handshake mr-2"></i> Customer Association (গ্রাহক সংযোগ)</h5>
                        <div class="row mb-4">
                            @if($member->supplier_id)
                                <div class="col-md-12 mb-3">
                                    <label class="small font-weight-bold">Linked Customer (সংযুক্ত গ্রাহক)</label>
                                    <div class="form-control bg-light" style="height: auto; min-height: calc(1.5em + .75rem + 2px);">
                                        <strong>{{ $member->customer->name ?? 'N/A' }}</strong> ({{ $member->customer->mobile_no ?? 'N/A' }} - {{ $member->customer->type ?? 'Customer' }})
                                    </div>
                                    <input type="hidden" name="customer_option" value="keep_current">
                                </div>
                            @else
                                <div class="col-md-4 mb-3">
                                    <label class="small font-weight-bold">Customer Option (গ্রাহক সংযোগ অপশন) <span class="text-danger">*</span></label>
                                    <select name="customer_option" id="customer_option" class="form-control select2" required>
                                        <option value="auto_create" {{ old('customer_option') == 'auto_create' ? 'selected' : '' }}>
                                            Auto Create New Customer (স্বয়ংক্রিয় নতুন গ্রাহক তৈরি)
                                        </option>
                                        <option value="existing" {{ old('customer_option') == 'existing' ? 'selected' : '' }}>
                                            Link to Existing Customer (বিদ্যমান গ্রাহকের সাথে লিঙ্ক করুন)
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-8 mb-3" id="existing_customer_wrapper" style="{{ old('customer_option') == 'existing' ? '' : 'display: none;' }}">
                                    <label class="small font-weight-bold">Select Existing Customer (বিদ্যমান গ্রাহক) <span class="text-danger">*</span></label>
                                    <select name="supplier_id" id="supplier_id" class="form-control select2" style="width: 100%;">
                                        <option value="">Select Customer</option>
                                        @foreach($existingCustomers as $cust)
                                            <option value="{{ $cust->id }}" {{ old('supplier_id') == $cust->id ? 'selected' : '' }}>
                                                {{ $cust->name }} ({{ $cust->mobile_no }} - {{ $cust->type }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>

                        <!-- Row 1: Basic Information -->
                        <h5 class="section-title"><i class="fa fa-info-circle mr-2"></i> Basic Information</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Select Somiti (সমিতি) <span class="text-danger">*</span></label>
                                <select name="somiti_id" class="form-control select2" required>
                                    <option value="">Select Somiti</option>
                                    @foreach($somitis as $somiti)
                                        <option value="{{ $somiti->id }}" {{ (old('somiti_id') ?? $member->somiti_id) == $somiti->id ? 'selected' : '' }}>{{ $somiti->name }} ({{ $somiti->somiti_code }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Member No (Admission No)</label>
                                <input type="text" value="{{ $member->member_no }}" class="form-control bg-light font-weight-bold text-primary" readonly tabindex="-1">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Member Category <span class="text-danger">*</span></label>
                                <select name="member_category" class="form-control select2" required>
                                    <option value="1" {{ old('member_category', $member->member_category) == 1 ? 'selected' : '' }}>General Member</option>
                                    <option value="2" {{ old('member_category', $member->member_category) == 2 ? 'selected' : '' }}>Lead Member (Group Leader)</option>
                                    <option value="3" {{ old('member_category', $member->member_category) == 3 ? 'selected' : '' }}>Somiti Head</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold text-success">Admission Date <span class="text-danger">*</span></label>
                                <input type="text" name="admission_date" value="{{ old('admission_date', $member->admission_date ? $member->admission_date->format('d-m-Y') : date('d-m-Y')) }}" class="form-control date-picker" readonly required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Admission Fee</label>
                                <input type="number" step="any" name="admission_fee" value="{{ old('admission_fee', $member->admission_fee) }}" class="form-control text-right">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Form Fee</label>
                                <input type="number" step="any" name="form_fee" value="{{ old('form_fee', $member->form_fee) }}" class="form-control text-right">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Passbook Fee</label>
                                <input type="number" step="any" name="passbook_fee" value="{{ old('passbook_fee', $member->passbook_fee) }}" class="form-control text-right">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Full Name (English) <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">নাম (বাংলায়)</label>
                                <input type="text" name="name_bn" value="{{ old('name_bn', $member->name_bn) }}" class="form-control">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Mobile No <span class="text-danger">*</span></label>
                                <input type="text" name="mobile_no" value="{{ old('mobile_no', $member->mobile_no) }}" class="form-control" required>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Father's Name</label>
                                <input type="text" name="father_name" value="{{ old('father_name', $member->father_name) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Mother's Name</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name', $member->mother_name) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Spouse Name</label>
                                <input type="text" name="spouse_name" value="{{ old('spouse_name', $member->spouse_name) }}" class="form-control">
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Date of Birth</label>
                                <input type="text" name="dob" value="{{ old('dob', $member->dob ? $member->dob->format('d-m-Y') : '') }}" class="form-control date-picker" autocomplete="off">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" class="form-control select2" required>
                                    <option value="Male" {{ old('gender', $member->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                                    <option value="Female" {{ old('gender', $member->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                                    <option value="Other" {{ old('gender', $member->gender) == 'Other' ? 'selected' : '' }}>Other</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Marital Status <span class="text-danger">*</span></label>
                                <select name="marital_status" class="form-control select2" required>
                                    <option value="1" {{ old('marital_status', $member->marital_status) == 1 ? 'selected' : '' }}>Married</option>
                                    <option value="2" {{ old('marital_status', $member->marital_status) == 2 ? 'selected' : '' }}>Single</option>
                                    <option value="3" {{ old('marital_status', $member->marital_status) == 3 ? 'selected' : '' }}>Widowed</option>
                                    <option value="4" {{ old('marital_status', $member->marital_status) == 4 ? 'selected' : '' }}>Divorced</option>
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Religion</label>
                                <select name="religion" class="form-control select2">
                                    @foreach(['Islam', 'Hinduism', 'Christianity', 'Buddhism', 'Other'] as $rel)
                                        <option value="{{ $rel }}" {{ old('religion', $member->religion) == $rel ? 'selected' : '' }}>{{ $rel }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Educational Qualification</label>
                                <select name="education" class="form-control select2">
                                    <option value="">Select Education</option>
                                    @php
                                        $eduOptions = [
                                            'Illiterate' => 'Illiterate (অশিক্ষিত)',
                                            'Signature Only' => 'Signature Only (স্বাক্ষর জ্ঞানসম্পন্ন)',
                                            'Primary' => 'Primary (প্রাথমিক)',
                                            'Secondary' => 'Secondary (মাধ্যমিক/এসএসসি)',
                                            'Higher Secondary' => 'Higher Secondary (উচ্চ মাধ্যমিক/এইচএসসি)',
                                            'Graduate' => 'Graduate (স্নাতক/অনার্স)',
                                            'Post-Graduate' => 'Post-Graduate (স্নাতকোত্তর/মাস্টার্স)',
                                            'Others' => 'Others (অন্যান্য)'
                                        ];
                                    @endphp
                                    @foreach($eduOptions as $val => $label)
                                        <option value="{{ $val }}" {{ old('education', $member->education) == $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="small font-weight-bold">Blood Group</label>
                                <select name="blood_group" class="form-control select2">
                                    <option value="">Select</option>
                                    @foreach(['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $group)
                                        <option value="{{ $group }}" {{ old('blood_group', $member->blood_group) == $group ? 'selected' : '' }}>{{ $group }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Row 2: Family & Professional -->
                        <h5 class="section-title"><i class="fa fa-briefcase mr-2"></i> Family & Professional Information</h5>
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Father's Name</label>
                                <input type="text" name="father_name" value="{{ old('father_name', $member->father_name) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Mother's Name</label>
                                <input type="text" name="mother_name" value="{{ old('mother_name', $member->mother_name) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Spouse Name</label>
                                <input type="text" name="spouse_name" value="{{ old('spouse_name', $member->spouse_name) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Profession</label>
                                <input type="text" name="profession" value="{{ old('profession', $member->profession) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Monthly Income</label>
                                <input type="number" step="any" name="monthly_income" value="{{ old('monthly_income', $member->monthly_income) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>NID No</label>
                                <input type="text" name="nid_no" value="{{ old('nid_no', $member->nid_no) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Birth Certificate No</label>
                                <input type="text" name="birth_certificate_no" value="{{ old('birth_certificate_no', $member->birth_certificate_no) }}" class="form-control">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label>Total Family Members</label>
                                <input type="number" name="total_family_members" value="{{ old('total_family_members', $member->total_family_members) }}" class="form-control">
                            </div>
                        </div>

                        <!-- Row 3: Documents -->
                        <h5 class="section-title"><i class="fa fa-camera mr-2"></i> Member Documents</h5>
                        <div class="row text-center">
                            <div class="col-md-3 mb-4">
                                <label class="d-block mb-2">Member Photo</label>
                                <div class="image-upload-box mx-auto {{ $member->photo ? 'has-image' : '' }}" style="width: 150px; height: 150px;" onclick="document.getElementById('member_photo').click()">
                                    <div class="placeholder-content"><i class="fa fa-user-circle"></i></div>
                                    <img id="preview_member_photo" src="{{ $member->photo ? asset($member->photo) : '#' }}" style="{{ $member->photo ? 'display:block' : '' }}">
                                </div>
                                <input type="file" id="member_photo" name="photo" class="file-input" onchange="previewImage(this, 'preview_member_photo')">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="d-block mb-2">Member Signature</label>
                                <div class="image-upload-box mx-auto {{ $member->signature_photo ? 'has-image' : '' }}" style="width: 150px; height: 100px;" onclick="document.getElementById('member_signature').click()">
                                    <div class="placeholder-content"><i class="fa fa-pen-fancy"></i></div>
                                    <img id="preview_member_signature" src="{{ $member->signature_photo ? asset($member->signature_photo) : '#' }}" style="{{ $member->signature_photo ? 'display:block' : '' }}">
                                </div>
                                <input type="file" id="member_signature" name="signature_photo" class="file-input" onchange="previewImage(this, 'preview_member_signature')">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="d-block mb-2">NID Front</label>
                                <div class="image-upload-box mx-auto {{ $member->nid_photo_front ? 'has-image' : '' }}" style="width: 100%; height: 120px;" onclick="document.getElementById('nid_front').click()">
                                    <div class="placeholder-content"><i class="fa fa-id-card" style="font-size: 1.5rem;"></i></div>
                                    <img id="preview_nid_front" src="{{ $member->nid_photo_front ? asset($member->nid_photo_front) : '#' }}" style="{{ $member->nid_photo_front ? 'display:block' : '' }}">
                                </div>
                                <input type="file" id="nid_front" name="nid_photo_front" class="file-input" onchange="previewImage(this, 'preview_nid_front')">
                            </div>
                            <div class="col-md-3 mb-4">
                                <label class="d-block mb-2">NID Back</label>
                                <div class="image-upload-box mx-auto {{ $member->nid_photo_back ? 'has-image' : '' }}" style="width: 100%; height: 120px;" onclick="document.getElementById('nid_back').click()">
                                    <div class="placeholder-content"><i class="fa fa-id-card" style="font-size: 1.5rem;"></i></div>
                                    <img id="preview_nid_back" src="{{ $member->nid_photo_back ? asset($member->nid_photo_back) : '#' }}" style="{{ $member->nid_photo_back ? 'display:block' : '' }}">
                                </div>
                                <input type="file" id="nid_back" name="nid_photo_back" class="file-input" onchange="previewImage(this, 'preview_nid_back')">
                            </div>
                            <div class="col-md-12 mb-4">
                                <div class="card bg-light border-0" style="border-radius: 10px;">
                                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                                        <div>
                                            <label class="mb-0 font-weight-bold">Other Documents</label>
                                            <small class="text-muted d-block">(Allowed: jpg, jpeg, png, pdf, doc, docx, xls, xlsx)</small>
                                            @if($member->document)
                                                <a href="{{ asset($member->document) }}" target="_blank" class="badge badge-info mt-1"><i class="fa fa-download mr-1"></i> Current Document</a>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1 mx-4">
                                            <input type="file" name="document" class="form-control-file">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Row 4: Address -->
                        <h5 class="section-title"><i class="fa fa-map-marker-alt mr-2"></i> Address Details</h5>
                        <div class="row">
                            <!-- Present Address -->
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 15px; background: #f8fafc;">
                                    <div class="card-body">
                                        <h6 class="font-weight-bold mb-3 text-primary"><i class="fa fa-home mr-1"></i> Present Address</h6>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Division</label>
                                                <select name="present_division_id" class="form-control select2 division-select" data-target-district="present_district_id">
                                                    <option value="">Select Division</option>
                                                    @foreach($divisions as $div)
                                                        <option value="{{ $div->id }}" {{ old('present_division_id', $member->present_division_id) == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">District</label>
                                                <select name="present_district_id" id="present_district_id" class="form-control select2 district-select" data-target-upazila="present_upazila_id" data-selected="{{ old('present_district_id', $member->present_district_id) }}">
                                                    <option value="">Select District</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Upazilla</label>
                                                <select name="present_upazila_id" id="present_upazila_id" class="form-control select2 upazila-select" data-target-union="present_union_id" data-selected="{{ old('present_upazila_id', $member->present_upazila_id) }}">
                                                    <option value="">Select Upazilla</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Union/Pourashava</label>
                                                 <select name="present_union_id" id="present_union_id" class="form-control select2 union-select" data-target-village="present_village_id" data-selected="{{ old('present_union_id', $member->present_pourashava_id ? 'pourashava_'.$member->present_pourashava_id : ($member->present_union_id ? 'union_'.$member->present_union_id : '')) }}">
                                                     <option value="">Select Union/Pourashava</option>
                                                 </select>
                                             </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Village</label>
                                                <select name="present_village_id" id="present_village_id" class="form-control select2 village-select" data-selected="{{ old('present_village_id', $member->present_village_id) }}">
                                                    <option value="">Select Village</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">House/Road</label>
                                                <input type="text" name="present_house_road" value="{{ old('present_house_road', $member->present_house_road) }}" class="form-control" placeholder="House No, Road No, etc.">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Permanent Address -->
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm" style="border-radius: 15px; background: #f8fafc;">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="font-weight-bold mb-0 text-primary"><i class="fa fa-map mr-1"></i> Permanent Address</h6>
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="same_as_present">
                                                <label class="custom-control-label" for="same_as_present">Same as Present</label>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Division</label>
                                                <select name="permanent_division_id" class="form-control select2 division-select" data-target-district="permanent_district_id">
                                                    <option value="">Select Division</option>
                                                    @foreach($divisions as $div)
                                                        <option value="{{ $div->id }}" {{ old('permanent_division_id', $member->permanent_division_id) == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">District</label>
                                                <select name="permanent_district_id" id="permanent_district_id" class="form-control select2 district-select" data-target-upazila="permanent_upazila_id" data-selected="{{ old('permanent_district_id', $member->permanent_district_id) }}">
                                                    <option value="">Select District</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Upazilla</label>
                                                <select name="permanent_upazila_id" id="permanent_upazila_id" class="form-control select2 upazila-select" data-target-union="permanent_union_id" data-selected="{{ old('permanent_upazila_id', $member->permanent_upazila_id) }}">
                                                    <option value="">Select Upazilla</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Union/Pourashava</label>
                                                 <select name="permanent_union_id" id="permanent_union_id" class="form-control select2 union-select" data-target-village="permanent_village_id" data-selected="{{ old('permanent_union_id', $member->permanent_pourashava_id ? 'pourashava_'.$member->permanent_pourashava_id : ($member->permanent_union_id ? 'union_'.$member->permanent_union_id : '')) }}">
                                                     <option value="">Select Union/Pourashava</option>
                                                 </select>
                                             </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">Village</label>
                                                <select name="permanent_village_id" id="permanent_village_id" class="form-control select2 village-select" data-selected="{{ old('permanent_village_id', $member->permanent_village_id) }}">
                                                    <option value="">Select Village</option>
                                                </select>
                                            </div>
                                            <div class="col-md-6 mb-2">
                                                <label class="small">House/Road</label>
                                                <input type="text" name="permanent_house_road" value="{{ old('permanent_house_road', $member->permanent_house_road) }}" class="form-control" placeholder="House No, Road No, etc.">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-5">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="section-title mb-0" style="border: none;"><i class="fa fa-users mr-2"></i> Nominee Information</h5>
                            <button type="button" class="btn btn-primary btn-sm px-4" id="btn-add-nominee" style="border-radius: 10px;"><i class="fa fa-plus-circle mr-1"></i> Add Nominee</button>
                        </div>
                        <div id="nominee-container">
                            @php
                                $nominees = old('nominees') ? old('nominees') : $member->nominees;
                            @endphp
                            @foreach($nominees as $index => $nominee)
                                @php
                                    $n = (object) $nominee;
                                @endphp
                                <div class="nominee-item position-relative">
                                    <button type="button" class="btn btn-danger btn-sm position-absolute btn-remove-nominee shadow-sm" style="top: -10px; right: -10px; border-radius: 50%; width: 30px; height: 30px; z-index:10;"><i class="fa fa-times"></i></button>
                                    <input type="hidden" name="nominees[{{ $index }}][old_photo]" value="{{ $n->photo ?? ($nominee['old_photo'] ?? '') }}">
                                    <input type="hidden" name="nominees[{{ $index }}][old_nid_photo_front]" value="{{ $n->nid_photo_front ?? ($nominee['old_nid_photo_front'] ?? '') }}">
                                    <input type="hidden" name="nominees[{{ $index }}][old_nid_photo_back]" value="{{ $n->nid_photo_back ?? ($nominee['old_nid_photo_back'] ?? '') }}">
                                    
                                    <div class="row">
                                        <!-- Nominee Personal Details -->
                                        <div class="col-12 mb-4">
                                            <div class="row">
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Nominee Name</label>
                                                    <input type="text" name="nominees[{{ $index }}][name]" value="{{ $n->name ?? '' }}" class="form-control" required>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Relation</label>
                                                    <input type="text" name="nominees[{{ $index }}][relation]" value="{{ $n->relation ?? '' }}" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Phone</label>
                                                    <input type="text" name="nominees[{{ $index }}][phone]" value="{{ $n->phone ?? '' }}" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">NID No</label>
                                                    <input type="text" name="nominees[{{ $index }}][nid_no]" value="{{ $n->nid_no ?? '' }}" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Gender</label>
                                                    <select name="nominees[{{ $index }}][gender]" class="form-control select2">
                                                        <option value="Male" {{ ($n->gender ?? '') == 'Male' ? 'selected' : '' }}>Male</option>
                                                        <option value="Female" {{ ($n->gender ?? '') == 'Female' ? 'selected' : '' }}>Female</option>
                                                        <option value="Other" {{ ($n->gender ?? '') == 'Other' ? 'selected' : '' }}>Other</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Father Name</label>
                                                    <input type="text" name="nominees[{{ $index }}][father_name]" value="{{ $n->father_name ?? '' }}" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Mother Name</label>
                                                    <input type="text" name="nominees[{{ $index }}][mother_name]" value="{{ $n->mother_name ?? '' }}" class="form-control">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold">Birth Date</label>
                                                    <input type="text" name="nominees[{{ $index }}][dob]" value="{{ $n->dob ? date('d-m-Y', strtotime($n->dob)) : '' }}" class="form-control date-picker" autocomplete="off">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small font-weight-bold text-primary">Share (%)</label>
                                                    <input type="number" name="nominees[{{ $index }}][share_percentage]" value="{{ $n->share_percentage ?? '100' }}" class="form-control font-weight-bold" min="1" max="100">
                                                </div>

                                                <div class="col-12 mt-3">
                                                    <label class="small font-weight-bold text-info"><i class="fa fa-map-marker-alt mr-1"></i> Nominee Address</label>
                                                    <div class="row">
                                                        <div class="col-md-3 mb-2">
                                                            <label class="small">Division</label>
                                                            <select name="nominees[{{ $index }}][division_id]" class="form-control select2 division-select" data-target-district="nom_dist_{{ $index }}">
                                                                <option value="">Select</option>
                                                                @foreach($divisions as $div)
                                                                    <option value="{{ $div->id }}" {{ ($n->division_id ?? '') == $div->id ? 'selected' : '' }}>{{ $div->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <label class="small">District</label>
                                                            <select name="nominees[{{ $index }}][district_id]" id="nom_dist_{{ $index }}" class="form-control select2 district-select" data-target-upazila="nom_upz_{{ $index }}" data-selected="{{ $n->district_id ?? '' }}">
                                                                <option value="">Select</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <label class="small">Upazilla</label>
                                                            <select name="nominees[{{ $index }}][upazila_id]" id="nom_upz_{{ $index }}" class="form-control select2 upazila-select" data-target-union="nom_uni_{{ $index }}" data-selected="{{ $n->upazila_id ?? '' }}">
                                                                <option value="">Select</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <label class="small">Union/Pourashava</label>
                                                             <select name="nominees[{{ $index }}][union_id]" id="nom_uni_{{ $index }}" class="form-control select2 union-select" data-target-village="nom_vil_{{ $index }}" data-selected="{{ old('nominees.'.$index.'.union_id', ($n->pourashava_id ?? '') ? 'pourashava_'.($n->pourashava_id) : (($n->union_id ?? '') ? 'union_'.($n->union_id) : '')) }}">
                                                                 <option value="">Select Union/Pourashava</option>
                                                             </select>
                                                         </div>
                                                        <div class="col-md-3 mb-2">
                                                            <label class="small">Village</label>
                                                            <select name="nominees[{{ $index }}][village_id]" id="nom_vil_{{ $index }}" class="form-control select2 village-select" data-selected="{{ $n->village_id ?? '' }}">
                                                                <option value="">Select Village</option>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-6 mb-2">
                                                            <label class="small">House/Road</label>
                                                            <input type="text" name="nominees[{{ $index }}][house_road]" class="form-control" value="{{ $n->house_road ?? '' }}">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Nominee Documents -->
                                        <div class="col-12">
                                            <div class="row text-center mt-4">
                                                <div class="col-md-3 mb-3">
                                                    <label class="small d-block mb-1">Nominee Photo</label>
                                                    @php $p = $n->photo ?? ($nominee['old_photo'] ?? ''); @endphp
                                                    <div class="image-upload-box mx-auto {{ $p ? 'has-image' : '' }}" style="width: 100px; height: 100px;" onclick="document.getElementById('nom_photo_{{ $index }}').click()">
                                                        <div class="placeholder-content"><i class="fa fa-camera" style="font-size: 1.5rem;"></i></div>
                                                        <img id="preview_nom_photo_{{ $index }}" src="{{ $p ? asset($p) : '#' }}" style="{{ $p ? 'display:block' : '' }}">
                                                    </div>
                                                    <input type="file" id="nom_photo_{{ $index }}" name="nominees[{{ $index }}][photo]" class="file-input" onchange="previewImage(this, 'preview_nom_photo_{{ $index }}')">
                                                    <input type="hidden" name="nominees[{{ $index }}][old_photo]" value="{{ $p }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small d-block mb-1">Signature</label>
                                                    @php $s = $n->signature_photo ?? ($nominee['old_signature_photo'] ?? ''); @endphp
                                                    <div class="image-upload-box mx-auto {{ $s ? 'has-image' : '' }}" style="width: 100px; height: 60px;" onclick="document.getElementById('nom_sig_{{ $index }}').click()">
                                                        <div class="placeholder-content"><i class="fa fa-pen-fancy"></i></div>
                                                        <img id="preview_nom_sig_{{ $index }}" src="{{ $s ? asset($s) : '#' }}" style="{{ $s ? 'display:block' : '' }}">
                                                    </div>
                                                    <input type="file" id="nom_sig_{{ $index }}" name="nominees[{{ $index }}][signature_photo]" class="file-input" onchange="previewImage(this, 'preview_nom_sig_{{ $index }}')">
                                                    <input type="hidden" name="nominees[{{ $index }}][old_signature_photo]" value="{{ $s }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small d-block mb-1">NID Front</label>
                                                    @php $nf = $n->nid_photo_front ?? ($nominee['old_nid_photo_front'] ?? ''); @endphp
                                                    <div class="image-upload-box mx-auto {{ $nf ? 'has-image' : '' }}" style="width: 150px; height: 80px;" onclick="document.getElementById('nom_nid_f_{{ $index }}').click()">
                                                        <div class="placeholder-content"><i class="fa fa-id-card" style="font-size: 1.5rem;"></i></div>
                                                        <img id="preview_nom_nid_f_{{ $index }}" src="{{ $nf ? asset($nf) : '#' }}" style="{{ $nf ? 'display:block' : '' }}">
                                                    </div>
                                                    <input type="file" id="nom_nid_f_{{ $index }}" name="nominees[{{ $index }}][nid_photo_front]" class="file-input" onchange="previewImage(this, 'preview_nom_nid_f_{{ $index }}')">
                                                    <input type="hidden" name="nominees[{{ $index }}][old_nid_photo_front]" value="{{ $nf }}">
                                                </div>
                                                <div class="col-md-3 mb-3">
                                                    <label class="small d-block mb-1">NID Back</label>
                                                    @php $nb = $n->nid_photo_back ?? ($nominee['old_nid_photo_back'] ?? ''); @endphp
                                                    <div class="image-upload-box mx-auto {{ $nb ? 'has-image' : '' }}" style="width: 150px; height: 80px;" onclick="document.getElementById('nom_nid_b_{{ $index }}').click()">
                                                        <div class="placeholder-content"><i class="fa fa-id-card" style="font-size: 1.5rem;"></i></div>
                                                        <img id="preview_nom_nid_b_{{ $index }}" src="{{ $nb ? asset($nb) : '#' }}" style="{{ $nb ? 'display:block' : '' }}">
                                                    </div>
                                                    <input type="file" id="nom_nid_b_{{ $index }}" name="nominees[{{ $index }}][nid_photo_back]" class="file-input" onchange="previewImage(this, 'preview_nom_nid_b_{{ $index }}')">
                                                    <input type="hidden" name="nominees[{{ $index }}][old_nid_photo_back]" value="{{ $nb }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                    </div>
                    <div class="card-footer bg-light p-4 text-center border-0">
                        <button type="submit" class="btn btn-info btn-lg bg-gradient-info px-5 shadow-lg" style="border-radius: 15px;">UPDATE MEMBER RECORDS</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="nominee-template">
        <div class="nominee-item position-relative">
            <button type="button" class="btn btn-danger btn-sm position-absolute btn-remove-nominee shadow-sm" style="top: -10px; right: -10px; border-radius: 50%; width: 30px; height: 30px; z-index: 10;"><i class="fa fa-times"></i></button>
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Nominee Name</label>
                            <input type="text" name="nominees[INDEX][name]" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Relation</label>
                            <input type="text" name="nominees[INDEX][relation]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Phone</label>
                            <input type="text" name="nominees[INDEX][phone]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">NID No</label>
                            <input type="text" name="nominees[INDEX][nid_no]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Gender</label>
                            <select name="nominees[INDEX][gender]" class="form-control select2-new">
                                <option value="Male">Male</option>
                                <option value="Female">Female</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Father Name</label>
                            <input type="text" name="nominees[INDEX][father_name]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Mother Name</label>
                            <input type="text" name="nominees[INDEX][mother_name]" class="form-control">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold">Birth Date</label>
                            <input type="text" name="nominees[INDEX][dob]" class="form-control date-picker" autocomplete="off">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small font-weight-bold text-primary">Share (%)</label>
                            <input type="number" name="nominees[INDEX][share_percentage]" class="form-control font-weight-bold" value="100" min="1" max="100">
                        </div>

                        <div class="col-12 mt-3">
                            <label class="small font-weight-bold text-info"><i class="fa fa-map-marker-alt mr-1"></i> Nominee Address</label>
                            <div class="row">
                                <div class="col-md-3 mb-2">
                                    <label class="small">Division</label>
                                    <select name="nominees[INDEX][division_id]" class="form-control select2-new division-select" data-target-district="nom_dist_INDEX">
                                        <option value="">Select</option>
                                        @foreach($divisions as $div)
                                            <option value="{{ $div->id }}">{{ $div->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small">District</label>
                                    <select name="nominees[INDEX][district_id]" id="nom_dist_INDEX" class="form-control select2-new district-select" data-target-upazila="nom_upz_INDEX">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small">Upazilla</label>
                                    <select name="nominees[INDEX][upazila_id]" id="nom_upz_INDEX" class="form-control select2-new upazila-select" data-target-union="nom_uni_INDEX">
                                        <option value="">Select</option>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small">Union/Pourashava</label>
                                     <select name="nominees[INDEX][union_id]" id="nom_uni_INDEX" class="form-control select2-new union-select" data-target-village="nom_vil_INDEX">
                                         <option value="">Select Union/Pourashava</option>
                                     </select>
                                 </div>
                                <div class="col-md-3 mb-2">
                                    <label class="small">Village</label>
                                    <select name="nominees[INDEX][village_id]" id="nom_vil_INDEX" class="form-control select2-new village-select">
                                        <option value="">Select Village</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="small">House/Road</label>
                                    <input type="text" name="nominees[INDEX][house_road]" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="row text-center mt-4">
                        <div class="col-md-3 mb-3">
                            <label class="small d-block mb-1">Photo</label>
                            <div class="image-upload-box mx-auto" style="width: 100px; height: 100px;" onclick="document.getElementById('nom_photo_INDEX').click()">
                                <div class="placeholder-content"><i class="fa fa-camera" style="font-size: 1.5rem;"></i></div>
                                <img id="preview_nom_photo_INDEX" src="#">
                            </div>
                            <input type="file" id="nom_photo_INDEX" name="nominees[INDEX][photo]" class="file-input" onchange="previewImage(this, 'preview_nom_photo_INDEX')">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small d-block mb-1">Signature</label>
                            <div class="image-upload-box mx-auto" style="width: 100px; height: 60px;" onclick="document.getElementById('nom_sig_INDEX').click()">
                                <div class="placeholder-content"><i class="fa fa-pen-fancy"></i></div>
                                <img id="preview_nom_sig_INDEX" src="#">
                            </div>
                            <input type="file" id="nom_sig_INDEX" name="nominees[INDEX][signature_photo]" class="file-input" onchange="previewImage(this, 'preview_nom_sig_INDEX')">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small d-block mb-1">NID Front</label>
                            <div class="image-upload-box mx-auto" style="width: 150px; height: 80px;" onclick="document.getElementById('nom_nid_f_INDEX').click()">
                                <div class="placeholder-content"><i class="fa fa-id-card" style="font-size: 1.5rem;"></i></div>
                                <img id="preview_nom_nid_f_INDEX" src="#">
                            </div>
                            <input type="file" id="nom_nid_f_INDEX" name="nominees[INDEX][nid_photo_front]" class="file-input" onchange="previewImage(this, 'preview_nom_nid_f_INDEX')">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="small d-block mb-1">NID Back</label>
                            <div class="image-upload-box mx-auto" style="width: 150px; height: 80px;" onclick="document.getElementById('nom_nid_b_INDEX').click()">
                                <div class="placeholder-content"><i class="fa fa-id-card" style="font-size: 1.5rem;"></i></div>
                                <img id="preview_nom_nid_b_INDEX" src="#">
                            </div>
                            <input type="file" id="nom_nid_b_INDEX" name="nominees[INDEX][nid_photo_back]" class="file-input" onchange="previewImage(this, 'preview_nom_nid_b_INDEX')">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </template>
@endsection

@section('script')
    <script>
        function previewImage(input, previewId) {
            if (input.files && input.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#' + previewId).attr('src', e.target.result).show();
                    $('#' + previewId).parent().addClass('has-image');
                }
                reader.readAsDataURL(input.files[0]);
            }
        }
        $(function () {
            $('.select2').select2();
            
            $('body').on('change', '.division-select', function() {
                let divisionId = $(this).val();
                let targetDistrictId = $(this).data('target-district');
                let targetDistrict = $('#' + targetDistrictId);
                let selectedVal = targetDistrict.data('selected');
                
                targetDistrict.html('<option value="">Loading...</option>');
                
                if (divisionId) {
                    $.get("{{ route('get_districts') }}", { division_id: divisionId }, function(data) {
                        let options = '<option value="">Select District</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}" ${selectedVal == item.id ? 'selected' : ''}>${item.name}</option>`;
                        });
                        targetDistrict.html(options).trigger('change');
                    });
                } else {
                    targetDistrict.html('<option value="">Select District</option>').trigger('change');
                }
            });

            $('body').on('change', '.district-select', function() {
                let districtId = $(this).val();
                let targetUpazilaId = $(this).data('target-upazila');
                let targetUpazila = $('#' + targetUpazilaId);
                let selectedUpazila = targetUpazila.data('selected');
                
                let targetUnionId = targetUpazila.data('target-union');
                let targetUnion = $('#' + targetUnionId);
                let selectedUnion = targetUnion.data('selected');

                targetUpazila.html('<option value="">Loading...</option>');
                targetUnion.html('<option value="">Loading...</option>');
                
                if (districtId) {
                    $.get("{{ route('get_upazilas') }}", { district_id: districtId }, function(data) {
                        let options = '<option value="">Select Upazilla</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}" ${selectedUpazila == item.id ? 'selected' : ''}>${item.name}</option>`;
                        });
                        targetUpazila.html(options).trigger('change');
                    });

                    $.get("{{ route('get_pourashavas') }}", { district_id: districtId }, function(data) {
                        if (targetUnion.find('option[value=""]').text() === 'Loading...') {
                            targetUnion.html('<option value="">Select Union/Pourashava</option>');
                        }
                        data.forEach(function(item) {
                            let val = 'pourashava_' + item.id;
                            let selected = (selectedUnion == val) ? 'selected' : '';
                            targetUnion.append(`<option value="${val}" ${selected}>${item.name} (Pourashava)</option>`);
                        });
                    });
                } else {
                    targetUpazila.html('<option value="">Select Upazilla</option>').trigger('change');
                    targetUnion.html('<option value="">Select Union/Pourashava</option>').trigger('change');
                }
            });

            $('body').on('change', '.upazila-select', function() {
                let upazilaId = $(this).val();
                let targetUnionId = $(this).data('target-union');
                let targetUnion = $('#' + targetUnionId);
                let selectedVal = targetUnion.data('selected');
                
                targetUnion.find('option').filter(function() {
                    let v = $(this).val();
                    return v.startsWith('union_') || ($(this).text().includes('(Union)') && v !== "");
                }).remove();
                
                if (upazilaId) {
                    $.get("{{ route('get_unions') }}", { upazila_id: upazilaId }, function(data) {
                        if (targetUnion.find('option[value=""]').text() === 'Loading...') {
                            targetUnion.html('<option value="">Select Union/Pourashava</option>');
                        }
                        data.forEach(function(item) {
                            let val = 'union_' + item.id;
                            let selected = (selectedVal == val) ? 'selected' : '';
                            targetUnion.append(`<option value="${val}" ${selected}>${item.name} (Union)</option>`);
                        });
                        targetUnion.trigger('change');
                    });
                }
            });

            $('body').on('change', '.union-select', function() {
                let val = $(this).val();
                let targetVillageId = $(this).data('target-village');
                if(!targetVillageId) return;

                let targetVillage = $('#' + targetVillageId);
                let selectedVal = targetVillage.data('selected');
                
                targetVillage.html('<option value="">Loading...</option>');

                if (val) {
                    let reqData = {};
                    if (val.startsWith('union_')) {
                        reqData.union_id = val.split('_')[1];
                    } else if (val.startsWith('pourashava_')) {
                        reqData.pourashava_id = val.split('_')[1];
                    }
                    $.get("{{ route('get_villages') }}", reqData, function(data) {
                        let options = '<option value="">Select Village</option>';
                        data.forEach(function(item) {
                            options += `<option value="${item.id}" ${selectedVal == item.id ? 'selected' : ''}>${item.name}</option>`;
                        });
                        targetVillage.html(options).trigger('change');
                    });
                } else {
                    targetVillage.html('<option value="">Select Village</option>').trigger('change');
                }
            });

            $('.division-select').each(function() {
                if ($(this).val()) {
                    $(this).trigger('change');
                }
            });

            $('#same_as_present').change(function() {
                if ($(this).is(':checked')) {
                    $('[name="permanent_division_id"]').val($('[name="present_division_id"]').val()).trigger('change');
                    
                    setTimeout(() => {
                        $('[name="permanent_district_id"]').val($('[name="present_district_id"]').val()).trigger('change');
                        
                        setTimeout(() => {
                            $('[name="permanent_upazila_id"]').val($('[name="present_upazila_id"]').val()).trigger('change');
                            
                            setTimeout(() => {
                                $('[name="permanent_union_id"]').val($('[name="present_union_id"]').val()).trigger('change');

                                setTimeout(() => {
                                    $('[name="permanent_village_id"]').val($('[name="present_village_id"]').val()).trigger('change');
                                }, 500);
                            }, 500);
                        }, 500);
                    }, 500);

                    $('[name="permanent_house_road"]').val($('[name="present_house_road"]').val());
                }
            });

            let nomineeIndex = {{ $member->nominees->count() }};
            $('#btn-add-nominee').click(function() {
                let html = $('#nominee-template').html();
                html = html.replace(/INDEX/g, nomineeIndex);
                $('#nominee-container').append(html);
                
                $('.select2-new').select2({ width: '100%' });
                
                // Initialize date-picker for the new nominee
                $('.date-picker').datepicker({
                    dateFormat: 'dd-mm-yy',
                    changeMonth: true,
                    changeYear: true,
                });
                
                nomineeIndex++;
            });

            $('body').on('click', '.btn-remove-nominee', function() {
                $(this).closest('.nominee-item').remove();
            });

            // Handle customer option change
            $('#customer_option').change(function() {
                let opt = $(this).val();
                if (opt === 'existing') {
                    $('#existing_customer_wrapper').slideDown();
                    $('#supplier_id').prop('required', true);
                } else {
                    $('#existing_customer_wrapper').slideUp();
                    $('#supplier_id').prop('required', false).val('').trigger('change');
                }
            });
            
            // Set initial required state
            if ($('#customer_option').val() === 'existing') {
                $('#supplier_id').prop('required', true);
            }
        });
    </script>
@endsection
