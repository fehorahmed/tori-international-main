@extends('layouts.app')

@section('title', 'New Visit Entry')

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
        <div class="col-md-8 offset-md-2">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">Register SR Visit</h3>
                </div>
                <form id="visitForm" method="POST" action="{{ route('sr-visits.store') }}">
                    @csrf
                    <input type="hidden" id="latitude" name="lat">
                    <input type="hidden" id="longitude" name="long">
                    <input type="hidden" id="location" name="location">
                    <input type="hidden" id="image_base64" name="image">
                    <input type="hidden" id="existing_client_id" name="existing_client_id">

                    <div class="card-body">
                        @if(auth()->user()->role != \App\Enumeration\RoleType::$SR)
                            <div class="alert alert-warning">
                                <strong><i class="fas fa-user-shield"></i> Supervisor Mode</strong>
                                <div class="form-group mt-2 mb-0 text-left">
                                    <label class="text-dark">Select SR for this Visit <span class="text-danger">*</span></label>
                                    <select name="sr_id" id="sr_id" class="form-control select2" required>
                                        <option value="">-- Choose SR --</option>
                                        @foreach($srs as $sr_employee)
                                            <option value="{{ $sr_employee->id }}">{{ $sr_employee->name }} ({{ $sr_employee->id_no ?? '' }})</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        @endif
                        @php
                            $decrypted_id = null;
                            $targetShop = null;
                            try {
                                if(request('s')) {
                                    $decrypted_id = decrypt(request('s'));
                                    $targetShop = \App\Models\Supplier::find($decrypted_id);
                                }
                            } catch(\Exception $e) {}
                        @endphp

                        <div class="form-group">
                            @if(auth()->user()->role == \App\Enumeration\RoleType::$SR)
                                <label>Assigned Customer/Shop <span class="text-danger">*</span></label>
                                <input type="hidden" name="s" value="{{ request('s') }}">
                                <div class="form-control" style="background-color: #e9ecef; height: auto;">
                                    @if($targetShop)
                                        <strong class="text-primary"><i class="fas fa-store mr-1"></i> {{ $targetShop->name }}</strong> 
                                        <br><small class="text-muted"><i class="fas fa-phone-alt"></i> {{ $targetShop->mobile_no ?? 'N/A' }}</small>
                                    @else
                                        <strong class="text-danger">Invalid Shop Selected!</strong>
                                    @endif
                                </div>
                                @if($targetShop && $targetShop->lat && $targetShop->long)
                                    <p class="text-info mt-2 mb-0" id="live_distance_info" data-shop-lat="{{ $targetShop->lat }}" data-shop-lng="{{ $targetShop->long }}" style="font-weight: 600; font-size: 14px;">
                                        <i class="fas fa-route"></i> Calculating exact distance...
                                    </p>
                                @endif
                            @else
                                <label>Select Customer/Shop <span class="text-danger">*</span></label>
                                <select name="supplier_id" id="supplier_id" class="form-control select2" required>
                                    <option value="">-- Choose Customer --</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->mobile_no }})</option>
                                    @endforeach
                                </select>
                            @endif
                        </div>


                        <div class="form-group">
                            <label>Visit Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Purpose of visit, results..."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-12">
                                <label>Live Visit Image <span class="text-danger">*</span></label>
                                <img id="img_preview" src="" alt="Preview">
                                <video id="video" autoplay playsinline muted></video>
                                <canvas id="canvas"></canvas>
                                <div class="mt-2 text-center">
                                    <button type="button" class="btn btn-warning" id="capture_btn">
                                        <i class="fas fa-camera"></i> Capture Photo
                                    </button>
                                    
                                    <!-- Native App Camera Button -->
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
                            </div>
                        </div>

                        <div class="location-status text-center">
                            <p id="location_msg" class="text-warning">⏳ Fetching location...</p>
                        </div>
                    </div>

                    <div class="card-footer text-right">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-lg" id="submit_btn" disabled>
                            <i class="fas fa-save"></i> Submit Visit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        $(document).ready(function() {
            $('.select2').select2();





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
                $('#latitude').val(lat);
                $('#longitude').val(lng);
                
                $(img_preview).attr('src', image).show();
                $(video).hide();
                $(capture_btn).hide();
                $('#native_camera_section').hide();
                $(retake_btn).show();
                $('#submit_btn').prop('disabled', false).show();
                $('#location_msg').text("✅ Location captured (via App)").removeClass("text-warning").addClass("text-success");
            };

            initCamera();

            $('#native_camera_btn').click(function() {
                if (window.CameraBridge) {
                    window.CameraBridge.postMessage('open_native_camera');
                } else {
                    alert("App Bridge not found!");
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
                checkFormValidity();
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
                
                checkFormValidity();
            };

            // Distance calculation
            function calculateDistance(lat1, lon1, lat2, lon2) {
                var R = 6371e3;
                var φ1 = lat1 * Math.PI/180;
                var φ2 = lat2 * Math.PI/180;
                var Δφ = (lat2-lat1) * Math.PI/180;
                var Δλ = (lon2-lon1) * Math.PI/180;

                var a = Math.sin(Δφ/2) * Math.sin(Δφ/2) + Math.cos(φ1) * Math.cos(φ2) * Math.sin(Δλ/2) * Math.sin(Δλ/2);
                var c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                return R * c;
            }

            // Location Logic
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    $('#latitude').val(pos.coords.latitude);
                    $('#longitude').val(pos.coords.longitude);
                    $('#location_msg').text("✅ Location captured!").removeClass("text-warning").addClass("text-success");
                    $('#submit_btn').prop('disabled', false);

                    // Check live distance
                    let shopLat = $('#live_distance_info').data('shop-lat');
                    let shopLng = $('#live_distance_info').data('shop-lng');
                    if (shopLat && shopLng) {
                        let distance = calculateDistance(pos.coords.latitude, pos.coords.longitude, shopLat, shopLng);
                        let distanceText = distance > 1000 ? (distance / 1000).toFixed(2) + " km" : Math.round(distance) + " meters";
                        
                        if (distance > 50) {
                            $('#live_distance_info').html('<i class="fas fa-exclamation-triangle text-danger"></i> Distance: ' + distanceText + ' (Too far to check-in!)').removeClass('text-info').addClass('text-danger');
                            $('#submit_btn').prop('disabled', true);
                            $('#location_msg').append(" - You must be within 50 meters!");
                        } else {
                            $('#live_distance_info').html('<i class="fas fa-check-circle text-success"></i> Distance: ' + distanceText + ' (Ready to Check-in)').removeClass('text-info text-danger').addClass('text-success');
                        }
                    }

                }, function(err) {
                    let msg = "❌ Location required! Please enable GPS.";
                    if (err.code === 1) msg = "❌ Please allow location access.";
                    $('#location_msg').text(msg).removeClass("text-warning").addClass("text-danger");
                    
                    // Show alert only for browser users, not in the app
                    const isAppLocal = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                    if (!isAppLocal) {
                        alert(msg);
                    }
                }, { enableHighAccuracy: true });
            } else {
                $('#location_msg').text("❌ Browser doesn't support Geolocation.").addClass("text-danger");
            }

            function checkFormValidity() {
                // Basic check, submit button is enabled when location is fetched
                // but we could add more checks here
            }

            // AJAX Submit
            $('#visitForm').submit(function(e) {
                e.preventDefault();
                if (!$('#image_base64').val()) {
                    alert("Please capture a photo first!");
                    return;
                }

                let btn = $('#submit_btn');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status === 'success') {
                            // alert(res.message);
                            toastr.success(res.message);
                            setTimeout(function() {
                                window.location.href = "{{ route('sr-visits.index') }}";
                            }, 1000);
                        } else {
                            toastr.error(res.message);
                            btn.prop('disabled', false).html('<i class="fas fa-save"></i> Submit Visit');
                        }
                    },
                    error: function(err) {
                        toastr.error(err.responseJSON.message || "An error occurred!");
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Submit Visit');
                    }
                });
            });

            // Supervisor Mode: Fetch SR's Active Roads dynamically
            $('#sr_id').on('change', function() {
                var srId = $(this).val();
                var roadSelect = $('#admin_road_id');
                var roadSection = $('#admin_route_section');
                
                roadSelect.empty().append('<option value="">-- Select Route --</option>');
                roadSection.hide();
                roadSelect.prop('required', false);

                if (srId) {
                    $.ajax({
                        url: "{{ url('admin/get-active-roads') }}/" + srId,
                        type: 'GET',
                        success: function(data) {
                            if (data.length > 0) {
                                $.each(data, function(key, value) {
                                    roadSelect.append('<option value="'+ value.id +'">'+ value.name +'</option>');
                                });
                                roadSection.show();
                                roadSelect.prop('required', true);
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection
