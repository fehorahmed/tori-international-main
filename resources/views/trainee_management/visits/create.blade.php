@extends('layouts.app')
@section('title', 'Log Trainee Visit')

@section('style')
    <style>
        video { width: 100%; background: #000; border-radius: 8px; }
        canvas { display: none; }
        #img_preview { width: 100%; border-radius: 8px; display: none; margin-bottom: 10px; border: 2px solid #17a2b8; }
        .location-status { margin-top: 10px; font-size: 14px; }
    </style>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card card-outline card-info shadow-sm">
                <div class="card-header bg-white">
                    <h3 class="card-title font-weight-bold">
                        <i class="fas fa-map-marker-alt mr-2 text-info"></i> Log New Visit
                    </h3>
                </div>
                <form id="visitForm" method="POST" action="{{ route('trainee.visits.store') }}">
                    @csrf
                    <input type="hidden" id="latitude" name="lat">
                    <input type="hidden" id="longitude" name="long">
                    <input type="hidden" id="location" name="location">
                    <input type="hidden" id="image_base64" name="image">

                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 border-right">
                                <h5 class="mb-3 text-muted">Visit Details</h5>
                                <div class="form-group">
                                    <label>Client Status <span class="text-danger">*</span></label>
                                    <div class="d-flex mt-1">
                                        <div class="custom-control custom-radio mr-4">
                                            <input class="custom-control-input" type="radio" id="type_existing" name="visit_type" value="existing" checked>
                                            <label for="type_existing" class="custom-control-label">Existing Customer</label>
                                        </div>
                                        <div class="custom-control custom-radio">
                                            <input class="custom-control-input" type="radio" id="type_new" name="visit_type" value="new">
                                            <label for="type_new" class="custom-control-label">New Prospect</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="trainee_visit_type_id">Visit Category <span class="text-danger">*</span></label>
                                    <select name="trainee_visit_type_id" id="trainee_visit_type_id" class="form-control select2" style="width: 100%;" required>
                                        <option value="">-- Choose Category --</option>
                                        @foreach($visitTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div id="existing_customer_section">
                                    <div class="form-group">
                                        <label for="customer_id">Select Client <span class="text-danger">*</span></label>
                                        <select name="customer_id" id="customer_id" class="form-control select2" style="width: 100%;">
                                            <option value="">-- Choose Client --</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}">{{ $customer->name }} ({{ $customer->contact_no }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div id="new_customer_section" style="display: none;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Name <span class="text-danger">*</span></label>
                                                <input type="text" name="name" class="form-control" placeholder="Shop/Person Name">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Mobile No <span class="text-danger">*</span></label>
                                                <input type="text" name="mobile_no" class="form-control" placeholder="Mobile Number">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Division <span class="text-danger">*</span></label>
                                                <select name="division_id" id="division_id" class="form-control select2">
                                                    <option value="">-- Select --</option>
                                                    @foreach($divisions as $division)
                                                        <option value="{{ $division->id }}">{{ $division->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>District <span class="text-danger">*</span></label>
                                                <select name="district_id" id="district_id" class="form-control select2">
                                                    <option value="">-- Select --</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Upazila</label>
                                                <select name="upazila_id" id="upazila_id" class="form-control select2">
                                                    <option value="">-- Select --</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Union/Pourashava</label>
                                                <select name="union_id" id="union_id" class="form-control select2">
                                                    <option value="">-- Select --</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label>Village</label>
                                        <select name="village_id" id="village_id" class="form-control select2" data-selected="{{ old('village_id') }}">
                                            <option value="">Select Village</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label>Visit Notes</label>
                                    <textarea name="notes" class="form-control" rows="3" placeholder="Describe the visit..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <h5 class="mb-3 text-muted">Capture Evidence</h5>
                                <div class="text-center">
                                    <img id="img_preview" src="" alt="Preview">
                                    <video id="video" autoplay playsinline muted></video>
                                    <canvas id="canvas"></canvas>
                                    
                                    <div class="mt-3">
                                        <button type="button" class="btn btn-warning btn-lg btn-block mb-2" id="capture_btn">
                                            <i class="fas fa-camera"></i> Capture Photo
                                        </button>
                                        
                                        <!-- App Bridge Support -->
                                        <div id="native_camera_section" style="display: none;">
                                            <button type="button" class="btn btn-success btn-lg btn-block" id="native_camera_btn">
                                                <i class="fas fa-mobile-alt"></i> Take Photo (App Camera)
                                            </button>
                                        </div>

                                        <button type="button" class="btn btn-secondary btn-block" id="retake_btn" style="display: none;">
                                            <i class="fas fa-undo"></i> Retake Photo
                                        </button>
                                    </div>

                                    <div class="location-status mt-4">
                                        <div id="location_box" class="p-2 border rounded bg-light">
                                            <p id="location_msg" class="text-warning mb-0">
                                                <i class="fas fa-spinner fa-spin mr-1"></i> Waiting for GPS...
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer bg-white text-right py-3">
                        <a href="{{ route('trainee.visits.index') }}" class="btn btn-default mr-2">Cancel</a>
                        <button type="submit" class="btn btn-info bg-gradient-info btn-lg px-5" id="submit_btn" disabled>
                            <i class="fas fa-save mr-1"></i> Submit Visit
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

            // Toggle Sections
            $('input[name="visit_type"]').change(function() {
                if (this.value === 'existing') {
                    $('#existing_customer_section').slideDown();
                    $('#new_customer_section').slideUp();
                } else {
                    $('#existing_customer_section').slideUp();
                    $('#new_customer_section').slideDown();
                }
            });

            // Location Dropdowns
            $('#division_id').change(function() {
                let id = $(this).val();
                $('#district_id').html('<option value="">-- Select --</option>');
                $('#upazila_id').html('<option value="">-- Select --</option>');
                $('#union_id').html('<option value="">-- Select --</option>');
                if (id) {
                    $.get("{{ url('admin/get-districts') }}/" + id, function(data) {
                        let html = '<option value="">-- Select District --</option>';
                        data.forEach(item => html += `<option value="${item.id}">${item.name}</option>`);
                        $('#district_id').html(html);
                    });
                }
            });

            $('#district_id').change(function() {
                let id = $(this).val();
                $('#upazila_id').html('<option value="">-- Select --</option>');
                $('#union_id').html('<option value="">-- Select Union/Pourashava --</option>');
                if (id) {
                    // Load Upazilas
                    $.get("{{ url('admin/get-upazilas') }}/" + id, function(data) {
                        let html = '<option value="">-- Select Upazila --</option>';
                        data.forEach(item => html += `<option value="${item.id}">${item.name}</option>`);
                        $('#upazila_id').html(html);
                    });

                    // Load Pourashavas directly into Union/Pourashava dropdown
                    $.get("{{ url('admin/get-pourashavas') }}", {districtId: id}, function(data) {
                        data.forEach(item => {
                            $('#union_id').append(`<option value="pourashava_${item.id}">${item.name} (Pourashava)</option>`);
                        });
                    });
                }
            });

            $('#upazila_id').change(function() {
                let id = $(this).val();
                // Remove only Union options, keep Pourashava options if loaded from District
                $('#union_id option').filter(function() {
                    return $(this).text().includes('(Union)');
                }).remove();
                
                if (id) {
                    $.get("{{ url('admin/get-unions') }}", {upazilaId: id}, function(data) {
                        data.forEach(item => {
                            $('#union_id').append(`<option value="union_${item.id}">${item.name} (Union)</option>`);
                        });
                    });
                }
            });

            $('#union_id').change(function() {
                let id = $(this).val();
                let targetEl = $('#village_id');
                targetEl.html('<option value="">Select Village</option>');
                
                if (id) {
                    let data = {};
                    if (id.includes('union_')) {
                        data.unionId = id.replace('union_', '');
                    } else if (id.includes('pourashava_')) {
                        data.pourashavaId = id.replace('pourashava_', '');
                    }

                    $.get("{{ route('get_villages') }}", data, function(response) {
                        response.forEach(item => {
                            targetEl.append(`<option value="${item.id}">${item.name}</option>`);
                        });
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
                if (isApp) { $('#native_camera_section').show(); $(capture_btn).hide(); }
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: "environment" }, audio: false });
                    video.srcObject = stream;
                } catch (e) {
                    console.error("Camera error:", e);
                    $('#location_msg').text("❌ Camera access denied!").addClass("text-danger");
                }
            }

            capture_btn.onclick = function() {
                canvas.width = video.videoWidth;
                canvas.height = video.videoHeight;
                canvas.getContext('2d').drawImage(video, 0, 0);
                let dataUrl = canvas.toDataURL('image/png');
                image_base64.value = dataUrl;
                img_preview.src = dataUrl;
                $(video).hide(); $(img_preview).show(); $(capture_btn).hide(); $(retake_btn).show();
                checkSubmitButton();
            };

            retake_btn.onclick = function() {
                $(img_preview).hide(); $(retake_btn).hide(); image_base64.value = "";
                if (navigator.userAgent.includes('TORI_INTERNATIONAL_APP')) { $('#native_camera_section').show(); }
                else { $(video).show(); $(capture_btn).show(); }
                checkSubmitButton();
            };

            // App Bridge
            $('#native_camera_btn').click(function() {
                if (window.CameraBridge) { window.CameraBridge.postMessage('open_native_camera'); }
            });

            window.onNativeDataCaptured = function(image, lat, lng) {
                image_base64.value = image;
                $('#latitude').val(lat); $('#longitude').val(lng);
                $(img_preview).attr('src', image).show(); $(video).hide(); $(capture_btn).hide();
                $('#native_camera_section').hide(); $(retake_btn).show();
                $('#location_msg').html('<i class="fas fa-check-circle mr-1"></i> Location & Photo captured (App)').removeClass("text-warning").addClass("text-success");
                checkSubmitButton();
            };

            // Location
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    $('#latitude').val(pos.coords.latitude);
                    $('#longitude').val(pos.coords.longitude);
                    $('#location_msg').html('<i class="fas fa-check-circle mr-1"></i> Location captured!').removeClass("text-warning").addClass("text-success");
                    checkSubmitButton();
                }, function(err) {
                    $('#location_msg').text("❌ Location required!").removeClass("text-warning").addClass("text-danger");
                }, { enableHighAccuracy: true });
            }

            function checkSubmitButton() {
                if ($('#latitude').val() && image_base64.value) {
                    $('#submit_btn').prop('disabled', false);
                } else {
                    $('#submit_btn').prop('disabled', true);
                }
            }

            initCamera();

            // Submit
            $('#visitForm').submit(function(e) {
                e.preventDefault();
                let btn = $('#submit_btn');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.message);
                            window.location.href = "{{ route('trainee.visits.index') }}";
                        } else {
                            alert(res.message);
                            btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Submit Visit');
                        }
                    },
                    error: function(err) {
                        alert(err.responseJSON.message || "An error occurred!");
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Submit Visit');
                    }
                });
            });
        });
    </script>
@endsection
