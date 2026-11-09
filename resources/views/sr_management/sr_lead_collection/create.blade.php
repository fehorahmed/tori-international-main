@extends('layouts.app')
@section('title','Lead Entry')
@section('style')
    <style>
        video { width: 100%; background: #000; border-radius: 8px; }
        canvas { display: none; }
        #img_preview { width: 100%; border-radius: 8px; display: none; margin-bottom: 10px; border: 2px solid #17a2b8; }
        .location-status { margin-top: 10px; font-size: 14px; }
    </style>
@endsection

@section('content')
    <div class="row">
        <!-- left column -->
        <div class="col-md-8 offset-md-2">
            <!-- jquery validation -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Lead Entry Information</h3>
                </div>
                <!-- /.card-header -->
                <!-- form start -->
                <form enctype="multipart/form-data" action="{{ route('sr_lead_collection.store') }}" method="post" id="leadForm">
                    @csrf
                    <input type="hidden" id="latitude" name="lat">
                    <input type="hidden" id="longitude" name="long">
                    <input type="hidden" id="location" name="location">
                    <input type="hidden" id="existing_client_id" name="existing_client_id">
                    
                    <div class="card-body">
                        @if(isset($activeMarkets) && $activeMarkets->count() > 0)
                            <div class="alert alert-info">
                                <strong><i class="fas fa-route"></i> Assigned Market(s) for Today:</strong>
                                <div class="form-group mt-2 mb-0">
                                    <select name="market_id" class="form-control" required>
                                        @if($activeMarkets->count() > 1)
                                            <option value="">-- Select Your Current Market --</option>
                                        @endif
                                        @foreach($activeMarkets as $market)
                                            <option value="{{ $market->id }}">{{ $market->name }} ({{ $market->upazila->name ?? '' }})</option>
                                        @endforeach
                                    </select>
                                    @if($activeMarkets->count() > 1)
                                        <small class="text-white">You have multiple markets assigned today. Please select the one you are currently visiting.</small>
                                    @endif
                                </div>
                            </div>
                        @endif
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('client_name') ? 'has-error' :'' }}">
                                    <label for="client_name">Client/Shop Name <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('client_name') }}" name="client_name" class="form-control" id="client_name" placeholder="Enter Client Name">
                                    @error('client_name')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('client_contact_no') ? 'has-error' :'' }}">
                                    <label for="client_contact_no">Client Contact No. <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('client_contact_no') }}" name="client_contact_no" class="form-control" id="client_contact_no" placeholder="Enter Client Contact No.">
                                    @error('client_contact_no')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">

                            <div class="col-md-{{ auth()->user()->role == \App\Enumeration\RoleType::$SR ? '4' : '3' }}">
                                <div class="form-group {{ $errors->has('date') ? 'has-error' :'' }}">
                                    <label for="date">Date <span class="text-danger">*</span></label>
                                    <input type="text" autocomplete="off" value="{{ old('date', date('Y-m-d')) }}" name="date" class="form-control" id="date" readonly placeholder="Enter Date">
                                    @error('date')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-{{ auth()->user()->role == \App\Enumeration\RoleType::$SR ? '4' : '3' }}">
                                <div class="form-group {{ $errors->has('client_email') ? 'has-error' :'' }}">
                                    <label for="client_email">Client Email</label>
                                    <input type="email" value="{{ old('client_email') }}" name="client_email" class="form-control" id="client_email" placeholder="Enter Client Email">
                                    @error('client_email')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            
                            @if(auth()->user()->role != \App\Enumeration\RoleType::$SR)
                                <div class="col-md-3">
                                    <div class="form-group {{ $errors->has('sr_id') ? 'has-error' :'' }}">
                                        <label for="sr_id">Select SR <span class="text-danger">*</span></label>
                                        <select name="sr_id" id="sr_id" class="form-control select2" required>
                                            <option value="">Select SR</option>
                                            @foreach($srs as $sr)
                                                <option {{ old('sr_id') == $sr->id ? 'selected' : '' }} value="{{ $sr->id }}">{{ $sr->name }} ({{ $sr->id_no }})</option>
                                            @endforeach
                                        </select>
                                        @error('sr_id')
                                        <span class="help-block text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('division') ? 'has-error' :'' }}">
                                    <label for="division">Division <span class="text-danger">*</span></label>
                                    <select name="division" id="division" class="form-control select2">
                                        <option value="">Select Division</option>
                                        @foreach($divisions as $division)
                                            <option {{ old('division') == $division->id ? 'selected' : '' }} value="{{ $division->id }}">{{ $division->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('division')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('district') ? 'has-error' :'' }}">
                                    <label for="district">District <span class="text-danger">*</span></label>
                                    <select name="district" id="district" class="form-control select2">
                                        <option value="">Select District</option>
                                    </select>
                                    @error('district')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('upazila') ? 'has-error' :'' }}">
                                    <label for="upazila">Upazila <span class="text-danger">*</span></label>
                                    <select name="upazila" id="upazila" class="form-control select2">
                                        <option value="">Select Upazila</option>
                                    </select>
                                    @error('upazila')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('market_id') ? 'has-error' :'' }}">
                                    <label for="market_id">Market / Area <span class="text-danger">*</span></label>
                                    <select name="market_id" id="market_id" class="form-control select2" required>
                                        <option value="">Select Market</option>
                                    </select>
                                    @error('market_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('customer_type_id') ? 'has-error' :'' }}">
                                    <label for="customer_type_id">Customer Type <span class="text-danger">*</span></label>
                                    <select name="customer_type_id" id="customer_type_id" class="form-control select2" required>
                                        <option value="">Select Type</option>
                                        @foreach($customerTypes as $type)
                                            <option {{ old('customer_type_id') == $type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('customer_type_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('union') ? 'has-error' :'' }}">
                                    <label for="union">Union/Pourashava</label>
                                    <select name="union" id="union" class="form-control select2">
                                        <option value="">Select Union/Pourashava</option>
                                    </select>
                                    @error('union')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('village_id') ? 'has-error' :'' }}">
                                    <label for="village_id">Village</label>
                                    <select name="village_id" id="village_id" class="form-control select2" data-selected="{{ old('village_id') }}">
                                        <option value="">Select Village</option>
                                    </select>
                                    @error('village_id')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group {{ $errors->has('notes') ? 'has-error' :'' }}">
                                    <label for="notes">Notes <span class="text-danger">*</span></label>
                                    <input type="text" value="{{ old('notes') }}" name="notes" class="form-control" id="notes" placeholder="Enter Notes..">
                                    @error('notes')
                                    <span class="help-block text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <label>Client Image / Store Photo</label>
                                <!-- Camera Section -->
                                <input type="hidden" id="image_base64" name="image_base64">
                                
                                <img id="img_preview" alt="Preview">
                                <video id="video" autoplay playsinline muted></video>
                                <canvas id="canvas"></canvas>
                                
                                <div class="mt-2 text-center">
                                    <button type="button" class="btn btn-warning" id="capture_btn">
                                        <i class="fas fa-camera"></i> Capture Photo
                                    </button>
                                    
                                    <div id="native_camera_section" style="display: none;" class="mt-2">
                                        <button type="button" class="btn btn-success btn-lg btn-block" id="native_camera_btn">
                                            <i class="fas fa-mobile-alt"></i> Take Photo (Mobile App)
                                        </button>
                                        <p class="text-info mt-1"><small>Use this if browser camera doesn't work.</small></p>
                                    </div>

                                    <button type="button" class="btn btn-secondary" id="retake_btn" style="display: none;">
                                        <i class="fas fa-undo"></i> Retake
                                    </button>
                                </div>
                                <div class="location-status text-center mt-2">
                                    <p id="location_msg" class="text-warning">⏳ Fetching location...</p>
                                </div>
                                @error('image_base64')
                                <span class="help-block text-danger">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                    <!-- /.card-body -->
                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-lg" id="submit_btn" disabled>
                            <i class="fas fa-save"></i> Save Lead
                        </button>
                    </div>
                    <!-- /.card-footer -->
                </form>
            </div>
            <!-- /.card -->
        </div>
        <!--/.col (left) -->
    </div>
@endsection

@section('script')
    <script>
        $(function () {
            $('#division').change(function () {
                let divisionId = $(this).val();
                $('#district').html('<option value="">Select District</option>');
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Union/Pourashava</option>');

                if (divisionId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_districts') }}",
                        data: { divisionId: divisionId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#district').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });
                }
            });

            $('#district').change(function () {
                let districtId = $(this).val();
                $('#upazila').html('<option value="">Select Upazila</option>');
                $('#union').html('<option value="">Select Union/Pourashava</option>');

                if (districtId != '') {
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_upazilas') }}",
                        data: { districtId: districtId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#upazila').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });

                    // Load Pourashavas directly into Union/Pourashava dropdown
                    $.get("{{ url('admin/get-pourashavas') }}", {districtId: districtId}, function(data) {
                        data.forEach(item => {
                            $('#union').append(`<option value="pourashava_${item.id}">${item.name} (Pourashava)</option>`);
                        });
                    });
                }
            });

            $('#upazila').change(function () {
                let upazilaId = $(this).val();
                $('#market_id').html('<option value="">Select Market</option>');
                
                // Remove only Union options, keep Pourashava options if loaded from District
                $('#union option').filter(function() {
                    return $(this).text().includes('(Union)');
                }).remove();

                if (upazilaId != '') {
                    // Fetch Markets
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_markets') }}",
                        data: { upazilaId: upazilaId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#market_id').append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                    });

                    // Fetch Unions
                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_unions') }}",
                        data: { upazilaId: upazilaId }
                    }).done(function( data ) {
                        $.each(data, function( index, item ) {
                            $('#union').append('<option value="union_'+item.id+'">'+item.name+' (Union)</option>');
                        });
                    });
                }
            });

            $('#union').change(function () {
                let id = $(this).val();
                let targetEl = $('#village_id');
                targetEl.html('<option value="">Select Village</option>');

                if (id != '') {
                    let data = {};
                    if (id.includes('union_')) {
                        data.unionId = id.replace('union_', '');
                    } else if (id.includes('pourashava_')) {
                        data.pourashavaId = id.replace('pourashava_', '');
                    }

                    $.ajax({
                        method: "GET",
                        url: "{{ route('get_villages') }}",
                        data: data
                    }).done(function( response ) {
                        $.each(response, function( index, item ) {
                            targetEl.append('<option value="'+item.id+'">'+item.name+'</option>');
                        });
                        let oldVillage = targetEl.data('selected');
                        if (oldVillage) {
                            targetEl.val(oldVillage).trigger('change');
                        }
                    });
                }
            });

            // Camera Logic
            let video = document.getElementById('video');
            let canvas = document.getElementById('canvas');
            let capture_btn = document.getElementById('capture_btn');
            let retake_btn = document.getElementById('retake_btn');
            let img_preview = document.getElementById('img_preview');
            let image_base64 = document.getElementById('image_base64');
            let stream = null;

            async function initCamera() {
                const isApp = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                if (isApp) {
                    $('#native_camera_section').show();
                    $(capture_btn).hide(); // Hide browser capture button in app
                }

                try {
                    stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: "environment" }, // Rear camera
                        audio: false 
                    });
                    video.srcObject = stream;
                } catch (e) {
                    console.error("Camera error:", e);
                    if (!isApp) {
                        $('#location_msg').text("❌ Camera access denied!").addClass("text-danger");
                    } else {
                        // In app, we can just rely on Native Camera
                        $('#video').hide();
                        $('#location_msg').text("Please use the 'Take Photo' button below.").addClass("text-info");
                    }
                }
            }

            window.onNativeDataCaptured = function(image, lat, lng) {
                $('#image_base64').val(image);
                
                if (lat && lng) {
                    $('#latitude').val(lat);
                    $('#longitude').val(lng);
                    $('#location_msg').text("✅ Location & Photo captured!").removeClass("text-warning text-danger").addClass("text-success");
                    

                } else {
                    $('#location_msg').text("✅ Photo captured (via App)").removeClass("text-warning text-danger").addClass("text-success");
                }
                
                $(img_preview).attr('src', image).show();
                $(video).hide();
                $(capture_btn).hide();
                $('#native_camera_section').hide();
                $(retake_btn).show();
                $('#submit_btn').prop('disabled', false).show();
            };

            initCamera();

            $('#native_camera_btn').click(function() {
                if (window.CameraBridge) {
                    window.CameraBridge.postMessage('open_native_camera');
                } else {
                    toastr.error("App Bridge not found!");
                }
            });

            capture_btn.onclick = function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                let dataUrl = canvas.toDataURL('image/png');
                
                image_base64.value = dataUrl;
                img_preview.src = dataUrl;
                
                $(video).hide();
                $(img_preview).show();
                $(capture_btn).hide();
                $(retake_btn).show();
            };

            retake_btn.onclick = function() {
                const isApp = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                $(img_preview).hide();
                $(retake_btn).hide();
                image_base64.value = "";
                
                if (isApp) {
                    $('#native_camera_section').show();
                    $('#video').hide(); // Keep video hidden in app if it was hidden
                } else {
                    $(video).show();
                    $(capture_btn).show();
                }
            };

            // Location Logic
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    $('#latitude').val(pos.coords.latitude);
                    $('#longitude').val(pos.coords.longitude);
                    $('#location_msg').text("✅ Location captured!").removeClass("text-warning").addClass("text-success");
                    $('#submit_btn').prop('disabled', false);

                }, function(err) {
                    let msg = "❌ Location required! Please enable GPS.";
                    if (err.code === 1) msg = "❌ Please allow location access.";
                    $('#location_msg').text(msg).removeClass("text-warning").addClass("text-danger");
                    
                    // Show alert only for browser users, not in the app
                    if (!navigator.userAgent.includes('TORI_INTERNATIONAL_APP')) {
                        toastr.error(msg);
                    }
                }, { enableHighAccuracy: true });
            } else {
                $('#location_msg').text("❌ Browser doesn't support Geolocation.").addClass("text-danger");
            }

            // AJAX Submit
            $('#leadForm').submit(function(e) {
                // Remove required check for image if not strict, but disable btn
                let btn = $('#submit_btn');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');
                // We'll let normal form submission happen
            });
        });
    </script>
@endsection

