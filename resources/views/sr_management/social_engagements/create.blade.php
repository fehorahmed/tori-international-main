@extends('layouts.app')

@section('title')
    New Social Engagement
@endsection

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
                    <h3 class="card-title">Register Social Engagement</h3>
                </div>
                <div class="card-body">
                    <form id="socialForm">
                        @csrf
                        <input type="hidden" name="lat" id="latitude">
                        <input type="hidden" name="long" id="longitude">

                        <div class="form-group text-center">
                            <span id="location_msg" class="text-warning fw-bold"><i class="fas fa-spinner fa-spin"></i> Fetching Live GPS Location...</span>
                        </div>

                        @if(auth()->user()->role != \App\Enumeration\RoleType::$SR)
                            <div class="form-group">
                                <label>Select SR *</label>
                                <select name="sr_id" class="form-control select2" required>
                                    <option value="">Select SR</option>
                                    @foreach($srs as $sr)
                                        <option value="{{ $sr->id }}">{{ $sr->name }} ({{ $sr->id_no }})</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group">
                            <label>Platform *</label>
                            <select name="platform" class="form-control select2" required>
                                <option value="">Select Platform</option>
                                <option value="youtube">YouTube Subscriber</option>
                                <option value="facebook">Facebook Follower</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Customer Name (Optional)</label>
                            <input type="text" name="customer_name" class="form-control" placeholder="Enter name">
                        </div>

                        <div class="form-group">
                            <label>Customer Phone (Optional)</label>
                            <input type="text" name="customer_phone" class="form-control" placeholder="Enter phone">
                        </div>

                        <!-- Camera Section -->
                        <div class="form-group">
                            <label>Screenshot Proof *</label>
                            
                            <!-- Mobile App Native Camera Trigger -->
                            <div id="native_camera_section" style="display: none;" class="text-center mb-3">
                                <button type="button" class="btn btn-warning btn-lg btn-block" onclick="captureNativeImage()">
                                    <i class="fas fa-camera"></i> Open Camera
                                </button>
                            </div>

                            <!-- Browser Camera Preview -->
                            <div class="text-center" style="position: relative; background: #000; border-radius: 8px; overflow: hidden; min-height: 200px;">
                                <video id="video" width="100%" autoplay playsinline style="display: none;"></video>
                                <img id="img_preview" style="display: none; width: 100%; border-radius: 8px;">
                                
                                <div id="camera_controls" style="position: absolute; bottom: 15px; left: 0; right: 0; display: none;">
                                    <button type="button" id="capture_btn" class="btn btn-primary btn-lg rounded-circle" style="width: 60px; height: 60px; box-shadow: 0 4px 8px rgba(0,0,0,0.3);">
                                        <i class="fas fa-camera"></i>
                                    </button>
                                </div>
                                
                                <button type="button" id="retake_btn" class="btn btn-danger btn-sm" style="display: none; position: absolute; top: 10px; right: 10px;">
                                    <i class="fas fa-redo"></i> Retake
                                </button>
                            </div>
                            
                            <input type="hidden" name="image" id="image_base64" required>
                        </div>

                    </form>
                </div>
                <div class="card-footer text-center">
                    <button type="submit" form="socialForm" id="submit_btn" class="btn btn-success btn-lg btn-block" disabled>Submit Entry</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script>
        // Native App Callback
        function onNativeDataCaptured(base64Image, lat, lng, isMock) {
            if (base64Image) {
                let dataUrl = base64Image;
                if (!base64Image.startsWith('data:image')) {
                    dataUrl = "data:image/jpeg;base64," + base64Image;
                }
                $('#image_base64').val(dataUrl);
                $('#img_preview').attr('src', dataUrl).show();
                
                $('#native_camera_section').hide();
                $('#retake_btn').show();
            }

            if (lat && lng) {
                $('#latitude').val(lat);
                $('#longitude').val(lng);
                
                let msg = "✅ Location captured!";
                if (isMock) msg += " ⚠️ (Mock Location)";
                
                $('#location_msg').text(msg).removeClass("text-warning text-danger").addClass("text-success");
            }
            
            checkFormValidity();
        }

        // Trigger App Camera
        function captureNativeImage() {
            if (window.AndroidInterface && typeof window.AndroidInterface.captureImageWithLocation === 'function') {
                window.AndroidInterface.captureImageWithLocation();
            } else {
                alert("Native interface not found!");
            }
        }
        
        function checkFormValidity() {
            let lat = $('#latitude').val();
            let img = $('#image_base64').val();
            if (lat && img) {
                $('#submit_btn').prop('disabled', false);
            }
        }

        $(document).ready(function() {
            const video = document.getElementById('video');
            const img_preview = document.getElementById('img_preview');
            const capture_btn = document.getElementById('capture_btn');
            const retake_btn = document.getElementById('retake_btn');
            const image_base64 = document.getElementById('image_base64');
            let stream = null;

            // Check if running in the mobile app
            const isApp = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');

            if (isApp) {
                $('#native_camera_section').show();
            } else {
                // Browser mode
                $(video).show();
                $('#camera_controls').show();
                
                if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                    navigator.mediaDevices.getUserMedia({ 
                        video: { facingMode: 'environment' } 
                    }).then(function(s) {
                        stream = s;
                        video.srcObject = stream;
                        video.play();
                    }).catch(function(err) {
                        toastr.error("Camera error: " + err.message);
                    });
                }
            }

            capture_btn.onclick = function() {
                const canvas = document.createElement('canvas');
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
                
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                image_base64.value = dataUrl;
                img_preview.src = dataUrl;
                
                $(video).hide();
                $(img_preview).show();
                $(capture_btn).hide();
                $(retake_btn).show();
                checkFormValidity();
            };

            retake_btn.onclick = function() {
                const isAppLocal = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                $(img_preview).hide();
                $(retake_btn).hide();
                image_base64.value = "";
                
                if (isAppLocal || isApp) {
                    $('#native_camera_section').show();
                    $('#video').hide(); // Keep video hidden in app if it was hidden
                } else {
                    $(video).show();
                    $(capture_btn).show();
                }
                
                checkFormValidity();
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
                    const isAppLocal = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                    if (!isAppLocal) {
                        toastr.error(msg);
                    }
                }, { enableHighAccuracy: true });
            } else {
                $('#location_msg').text("❌ Browser doesn't support Geolocation.").addClass("text-danger");
            }

            // AJAX Submit
            $('#socialForm').submit(function(e) {
                e.preventDefault();
                
                if(!$('#image_base64').val()) {
                    toastr.warning('Please take a screenshot proof first!');
                    return;
                }

                if(!$('#latitude').val() || !$('#longitude').val()) {
                    toastr.warning('Waiting for GPS location...');
                    return;
                }

                let formData = $(this).serialize();
                let submitBtn = $('#submit_btn');
                submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Submitting...');

                $.ajax({
                    url: "{{ route('sr_social.store') }}",
                    type: "POST",
                    data: formData,
                    success: function(response) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = "{{ route('sr_social.index') }}";
                        }, 1000);
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let msg = xhr.responseJSON.message || 'Error occurred';
                        if (errors) {
                            msg = Object.values(errors).join('<br>');
                        }
                        toastr.error(msg);
                        submitBtn.prop('disabled', false).html('Submit Entry');
                    }
                });
            });
        });
    </script>
@endsection
