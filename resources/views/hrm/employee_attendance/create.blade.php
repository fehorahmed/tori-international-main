@extends('layouts.app')

@section('title', 'Employee Attendance')

@section('style')
    <style>
        video { width: 100%; background: #000; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        canvas { display: none; }
        #img_src { width: 100%; border-radius: 8px; display: none; margin-bottom: 10px; border: 2px solid #17a2b8; }
        .location-status { margin-top: 10px; font-size: 14px; }
        .card-attendance { border-top: 4px solid #17a2b8; }
    </style>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card card-outline card-attendance shadow-lg">
                <div class="card-header bg-white">
                    <h3 class="card-title text-info font-weight-bold">
                        <i class="fas fa-user-check mr-2"></i> Employee Attendance
                    </h3>
                </div>
                <form method="post" action="{{ route('employees.attendance') }}" id="attendanceForm">
                    @csrf
                    <input type="hidden" id="employee_photo_src" name="employee_photo_src">
                    <input type="hidden" id="latitude" name="latitude">
                    <input type="hidden" id="longitude" name="longitude">

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                <button type="button" class="close" data-dismiss="alert">&times;</button>
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="comments" class="font-weight-bold">Notes/Comments</label>
                            <textarea class="form-control" id="comments" name="note" placeholder="Write any specific note here..." rows="2"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-12 text-center">
                                <label class="d-block text-left font-weight-bold">Capture Photo <span class="text-danger">*</span></label>
                                
                                <img id="img_src" src="" alt="Preview">
                                <video id="video" autoplay playsinline muted></video>
                                <canvas id="canvas"></canvas>

                                <div class="mt-3">
                                    <!-- Browser Camera Button -->
                                    <div class="capture-button" style="display: none">
                                        <button type="button" class="btn btn-info btn-lg px-4 shadow-sm" id="click-photo">
                                            <i class="fas fa-camera mr-1"></i> Capture Photo
                                        </button>
                                    </div>

                                    <!-- Native App Camera Button -->
                                    <div class="native-app-button" style="display: none;">
                                        <button type="button" class="btn btn-success btn-lg btn-block shadow-sm" id="native-camera">
                                            <i class="fas fa-mobile-alt mr-1"></i> Take Photo (Mobile App)
                                        </button>
                                        <p class="text-muted mt-2 small">Use this for better quality and location accuracy.</p>
                                    </div>

                                    <button type="button" class="btn btn-secondary mt-2" id="retake_btn" style="display: none;">
                                        <i class="fas fa-undo mr-1"></i> Retake Photo
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="location-status text-center mt-4">
                            <p class="badge badge-warning p-2" id="location-msg">
                                <i class="fas fa-spinner fa-spin mr-1"></i> Fetching location, please wait...
                            </p>
                        </div>
                    </div>

                    <div class="card-footer bg-light text-right">
                        <button type="submit" class="btn btn-info bg-gradient-info btn-lg px-5 shadow submit-button" style="display: none">
                            <i class="fas fa-check-circle mr-1"></i> Submit Attendance
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('script')
    <script type="text/javascript">
        $(document).ready(function() {
            let video = document.querySelector("#video");
            let click_button = document.querySelector("#click-photo");
            let retake_btn = document.querySelector("#retake_btn");
            let img_preview = document.querySelector("#img_src");
            let canvas = document.querySelector("#canvas");
            
            let hasLocation = false;
            let hasPhoto = false;

            function checkSubmitBtn() {
                if (hasLocation && hasPhoto) {
                    $(".submit-button").show();
                } else {
                    $(".submit-button").hide();
                }
            }
            
            async function openCameraAndLocation() {
                const isApp = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                if (isApp) {
                    $(".native-app-button").show();
                    $(".location-status").hide(); 
                }

                try {
                    if (navigator.mediaDevices && navigator.mediaDevices.getUserMedia) {
                        const stream = await navigator.mediaDevices.getUserMedia({ 
                            video: { facingMode: "user" }, 
                            audio: false 
                        });

                        video.srcObject = stream;
                        video.onloadedmetadata = () => {
                            video.play();
                            if (!isApp) {
                                $(".capture-button").show();
                            }
                        };
                    }

                    const geoOptions = {
                        enableHighAccuracy: true,
                        timeout: 15000,
                        maximumAge: 30000 // Cache for 30 seconds
                    };

                    const getPosition = (options) => {
                        navigator.geolocation.getCurrentPosition(
                            (position) => {
                                $("#latitude").val(position.coords.latitude);
                                $("#longitude").val(position.coords.longitude);
                                $("#location-msg").text("✅ Location Captured").removeClass("badge-warning badge-danger").addClass("badge-success");
                                hasLocation = true;
                                checkSubmitBtn();
                            },
                            (error) => {
                                console.warn(`Geolocation error (${error.code}): ${error.message}`);
                                if (options.enableHighAccuracy) {
                                    console.log("Retrying with enableHighAccuracy: false");
                                    getPosition({ ...options, enableHighAccuracy: false });
                                    return;
                                }

                                let msg = "❌ Location required for attendance!";
                                if (error.code === 1) msg = "❌ Location permission denied!";
                                else if (error.code === 2) msg = "❌ Location unavailable!";
                                else if (error.code === 3) msg = "❌ Location request timed out!";

                                $("#location-msg").text(msg).removeClass("badge-warning").addClass("badge-danger");

                                if (!navigator.userAgent.includes('TORI_INTERNATIONAL_APP')) {
                                    alert(msg);
                                }
                            },
                            options
                        );
                    };

                    getPosition(geoOptions);
                } catch (error) {
                    console.error("Camera error:", error);
                }
            }

            window.onNativeDataCaptured = function(image, lat, lng) {
                $("#employee_photo_src").val(image);
                $("#latitude").val(lat);
                $("#longitude").val(lng);
                
                $("#img_src").attr("src", image).show();
                $(video).hide();
                $(click_button).parent().hide();
                $(".native-app-button").hide();
                $(retake_btn).show();
                hasPhoto = true;
                hasLocation = true; // App provides location together with photo
                checkSubmitBtn();
                $("#location-msg").text("✅ Location Captured (via App)").removeClass("badge-warning").addClass("badge-success");
            };

            openCameraAndLocation();

            document.querySelector("#native-camera")?.addEventListener('click', function() {
                if (window.CameraBridge) {
                    CameraBridge.postMessage('open_native_camera');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'App Bridge not found! This feature works only inside the mobile app.'
                    });
                }
            });

            click_button.addEventListener('click', function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                let dataUrl = canvas.toDataURL('image/jpeg');

                $("#img_src").attr("src", dataUrl).show();
                $("#employee_photo_src").val(dataUrl);
                $(video).hide();
                $(click_button).parent().hide();
                $(retake_btn).show();
                hasPhoto = true;
                checkSubmitBtn();
            });

            retake_btn.onclick = function() {
                const isApp = navigator.userAgent.includes('TORI_INTERNATIONAL_APP');
                $(img_preview).hide();
                $(retake_btn).hide();
                $("#employee_photo_src").val("");
                hasPhoto = false;
                checkSubmitBtn();

                if (isApp) {
                    $(".native-app-button").show();
                    $(video).hide();
                } else {
                    $(video).show();
                    $(click_button).parent().show();
                }
            };
        });
    </script>
@endsection
