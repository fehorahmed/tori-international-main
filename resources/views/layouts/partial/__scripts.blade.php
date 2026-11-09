<!-- jQuery -->
<script src="{{ asset('themes/backend/plugins/jquery/jquery-3.5.1.min.js') }}"></script>
<!-- Bootstrap 4 -->
<script src="{{ asset('themes/backend/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<!-- Select2 -->
<script src="{{ asset('themes/backend/plugins/select2/js/select2.full.min.js') }}"></script>
<!-- Bootstrap4 Duallistbox -->
<script src="{{ asset('themes/backend/plugins/bootstrap4-duallistbox/jquery.bootstrap-duallistbox.min.js') }}"></script>
<!-- InputMask -->
<script src="{{ asset('themes/backend/plugins/moment/moment.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/inputmask/jquery.inputmask.min.js') }}"></script>
<!-- date-range-picker -->
<script src="{{ asset('themes/backend/plugins/daterangepicker/daterangepicker.js') }}"></script>
<!-- bootstrap color picker -->
<script src="{{ asset('themes/backend/plugins/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js') }}"></script>
<!-- Tempus dominus Bootstrap 4 -->
<script
    src="{{ asset('themes/backend/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.min.js') }}"></script>
<!-- SweetAlert2 -->
<script src="{{ asset('themes/backend/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/bootstrap-switch/js/bootstrap-switch.min.js') }}"></script>
<script src="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.js"></script>
<!-- Toastr -->
<script src="{{ asset('themes/backend/plugins/toastr/toastr.min.js') }}"></script>
<!-- DataTables  & Plugins -->
<script src="{{ asset('themes/backend/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('themes/backend/plugins/datatables-colreorder/js/dataTables.colReorder.min.js') }}"></script>

<!-- jQuery Knob -->
<script src="{{ asset('themes/backend/plugins/jquery-knob/jquery.knob.min.js') }}"></script>

<!-- bootstrap datepicker -->
<script src="{{ asset('themes/backend/plugins/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script src="{{ asset('themes/backend/dist/js/sweetalert2@9.js') }}"></script>
<script src="{{ asset('themes/backend/jquery-ui-1.13.2.custom/jquery-ui.js') }}"></script>
<script src="{{ asset('themes/backend/month-picker/MonthPicker.min.js') }}"></script>
<script src="{{ asset('themes/backend/ui-date-picker/cdn.jsdelivr.net_gh_digitalBush_jquery.maskedinput@1.4.1_dist_jquery.maskedinput.min.js') }}"></script>
<script src="{{ asset('themes/backend/dist/js/jquery.magnific-popup.min.js') }}" ></script>

<script>
    $(function () {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var message = '{{ session('message') }}'.trim();
        var success = '{{ session('success') }}'.trim();
        var error = '{{ session('error') }}'.trim();
        var validateError = '{{ $errors->any() }}'.trim();

        if (!window.performance || window.performance.navigation.type != window.performance.navigation.TYPE_BACK_FORWARD) {
            // Ignore if the message is just "1" (boolean true from session)
            if (message === '1') message = '';
            if (success === '1') success = '';
            
            if (message != '' || success != ''){
                if (message != ''){
                    $(document).Toasts('create', {
                        icon: 'fas fa-envelope fa-lg',
                        class: 'bg-success',
                        title: 'Success',
                        autohide: true,
                        delay: 5000,
                        body: message
                    })
                }
                if (success != ''){
                    $(document).Toasts('create', {
                        icon: 'fas fa-envelope fa-lg',
                        class: 'bg-success',
                        title: 'Success',
                        autohide: true,
                        delay: 5000,
                        body: success
                    })
                }
                // Play the notification sound
                let notificationSound = document.getElementById('notification-success-audio');
                if (notificationSound) {
                    console.log("Playing sound from session success");
                    let promise = notificationSound.play();
                    if (promise !== undefined) {
                        promise.catch(error => { /* browser blocked autoplay */ });
                    }
                }
            }

            if (error != ''){
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: error,
                })
                $(document).Toasts('create', {
                    icon: 'fas fa-envelope fa-lg',
                    class: 'bg-danger',
                    title: 'Error',
                    autohide: true,
                    delay: 5000,
                    body: error
                })
                // Play the notification sound
                let notificationSound = document.getElementById('notification-error-audio');
                if (notificationSound) {
                    console.log("Playing sound from session error");
                    let promise = notificationSound.play();
                    if (promise !== undefined) {
                        promise.catch(error => { /* browser blocked autoplay */ });
                    }
                }
            }
            if (validateError != ''){

                $(document).Toasts('create', {
                    icon: 'fas fa-envelope fa-lg',
                    class: 'bg-warning',
                    title: 'Error',
                    autohide: true,
                    delay: 5000,
                    body: 'Please fill up validate required fields.'
                })
                // Play the notification sound
                let notificationSound = document.getElementById('notification-error-audio');
                if (notificationSound) {
                    notificationSound.play();
                }
            }

        }

        //Date picker
        $(".date-picker").datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
        });
        //Date picker
        $(".date-picker-before-disable").datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            minDate: 0
        });



        $(".date-picker-after-disable").datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            maxDate: 0  // 0 means today
        });

        $(".business-date-picker").datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            minDate: businessDate,
            maxDate: businessDate
        }).val(businessDate);
        //Date picker

        $('.month-picker').MonthPicker({
            Button: false,
            MonthFormat: 'mm-yy',
        });
        $('.date-picker-fiscal-year').attr('readonly', true);
        $("#financial_year").change(function () {
            let FYear = $(this).val();
            if (FYear !== '') {
                fiscalYearDateRange(FYear)
            } else {
                $('.date-picker-fiscal-year').val(' ')
            }
        })
        $("#financial_year").trigger('change');
        //Initialize Select2 Elements
        $('.select2').select2({
            width: '100%'
        })
        //Initialize Select2 Elements
        $('.select2bs4').select2({
            theme: 'bootstrap4'
        })

        //Bootstrap Duallistbox
        $('.duallistbox').bootstrapDualListbox()
        //Colorpicker
        $('.my-colorpicker1').colorpicker()
        //color picker with addon
        $('.my-colorpicker2').colorpicker()
        $('.my-colorpicker2').on('colorpickerChange', function (event) {
            $('.my-colorpicker2 .fa-square').css('color', event.color.toString());
        })
        $("input[data-bootstrap-switch]").each(function () {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        })
        /* jQueryKnob */

        $('.knob').knob({
            /*change : function (value) {
            //console.log("change : " + value);
            },
            release : function (value) {
            console.log("release : " + value);
            },
            cancel : function () {
            console.log("cancel : " + this.value);
            },*/
            draw: function () {

                // "tron" case
                if (this.$.data('skin') == 'tron') {

                var a   = this.angle(this.cv)  // Angle
                    ,
                    sa  = this.startAngle          // Previous start angle
                    ,
                    sat = this.startAngle         // Start angle
                    ,
                    ea                            // Previous end angle
                    ,
                    eat = sat + a                 // End angle
                    ,
                    r   = true

                this.g.lineWidth = this.lineWidth

                this.o.cursor
                && (sat = eat - 0.3)
                && (eat = eat + 0.3)

                if (this.o.displayPrevious) {
                    ea = this.startAngle + this.angle(this.value)
                    this.o.cursor
                    && (sa = ea - 0.3)
                    && (ea = ea + 0.3)
                    this.g.beginPath()
                    this.g.strokeStyle = this.previousColor
                    this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sa, ea, false)
                    this.g.stroke()
                }

                this.g.beginPath()
                this.g.strokeStyle = r ? this.o.fgColor : this.fgColor
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth, sat, eat, false)
                this.g.stroke()

                this.g.lineWidth = 2
                this.g.beginPath()
                this.g.strokeStyle = this.o.fgColor
                this.g.arc(this.xy, this.xy, this.radius - this.lineWidth + 1 + this.lineWidth * 2 / 3, 0, 2 * Math.PI, false)
                this.g.stroke()

                return false
                }
            }
        })
        /* END JQUERY KNOB */

    })
    function fiscalYearDateRange(year) {

        $(".date-picker-fiscal-year").datepicker({
            dateFormat: 'dd-mm-yy',
            changeMonth: true,
            changeYear: true,
            minDate: '01-07-' + year,
            maxDate: '30-06-' + (parseFloat(year) + 1)
        });
    }
    function dateInit() {
        $(".date-picker").datepicker({
            dateFormat: 'dd-mm-yy',
        });
    }
    function businessDateInit() {
        $(".business-date-picker").datepicker({
            dateFormat: 'dd-mm-yy',
            minDate: businessDate,
            maxDate: businessDate
        }).val(businessDate);
    }
    function dateInitSelector(selector) {
        $('.'+selector).datepicker({
            dateFormat: 'dd-mm-yy',
        });
    }
     function select2Init() {
        $(".select2").select2();
    }
    function jsNumberFormat(yourNumber) {
        //Seperates the components of the number
        var n = yourNumber.toString().split(".");
        //Comma-fies the first part
        n[0] = n[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        //Combines the two sections
        return n.join(".");
    }
    function formSubmitConfirm(btnIdName) {
        $('body').on('click', '#' + btnIdName, function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure to save?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Save It!'

            }).then((result) => {
                if (result.isConfirmed) {
                    $('form').submit();
                }
            })

        });
    }
    // Global AJAX success handler
    $(document).ajaxComplete(function(event, xhr, settings) {
        // Check if the request was successful (status code 200)
        if (xhr.status === 200) {
            try {
                // Ignore background token saving and other analytical pings from playing sounds
                if (settings.url && settings.url.includes('save-device-token')) {
                    return;
                }

                let responseData = JSON.parse(xhr.responseText);
                let success = responseData.success;
                
                // Only play sound for non-GET requests (e.g., POST, PUT, DELETE) to avoid beeping on DataTables load
                let method = (settings.type || 'GET').toUpperCase();
                if (method !== 'GET') {
                    if(success == false){
                        let notificationSound = document.getElementById('notification-error-audio');
                        if (notificationSound) {
                            console.log("Playing error sound from ajaxComplete");
                            let promise = notificationSound.play();
                            if (promise !== undefined) { promise.catch(e => {}); }
                        }
                    } else if(success == true){
                        let notificationSound = document.getElementById('notification-success-audio');
                        if (notificationSound) {
                            console.log("Playing success sound from ajaxComplete");
                            let promise = notificationSound.play();
                            if (promise !== undefined) { promise.catch(e => {}); }
                        }
                    }
                }
            } catch (e) {
                // Ignore if response is not JSON
            }
        }
    });
    // Error handler for AJAX requests
    $(document).ajaxError(function (event, xhr, settings, error) {
        preloaderToggle(false);
    });
    function preloaderToggle(condition){
        if(condition){
            $("#preloader").fadeIn();
            $('body').css('overflow','hidden');
        }else{
            $("#preloader").fadeOut();
            $('body').css('overflow','initial');
        }
    }
    function insidePreloaderToggle(condition){
        if(condition){
            $("#inside-preloader").fadeIn();
            $('body').css('overflow','hidden');
        }else{
            $("#inside-preloader").fadeOut();
            $('body').css('overflow','initial');
        }
    }
    function ajaxSuccessMessage(message){
        $(document).Toasts('create', {
            icon: 'fas fa-envelope fa-lg',
            class: 'bg-success',
            title: 'Success',
            autohide: true,
            delay: 5000,
            body: message
        })
        // Play the notification sound
        let notificationSound = document.getElementById('notification-success-audio');
        if (notificationSound) {
            notificationSound.play();
        }
    }
    function ajaxErrorMessage(message){
        $(document).Toasts('create', {
            icon: 'fas fa-envelope fa-lg',
            class: 'bg-warning',
            title: 'Error',
            autohide: true,
            delay: 5000,
            body: message
        })
        // Play the notification sound
        let notificationSound = document.getElementById('notification-error-audio');
        if (notificationSound) {
            notificationSound.play();
        }
    }
    function ajaxWarningMessage(message){
        $(document).Toasts('create', {
            icon: 'fas fa-envelope fa-lg',
            class: 'bg-warning',
            title: 'Error',
            autohide: true,
            delay: 5000,
            body: message
        })
        // Play the notification sound
        let notificationSound = document.getElementById('notification-error-audio');
        if (notificationSound) {
            notificationSound.play();
        }
    }
    function datatableButtons(){
        return [
            {
                "extend": "copy",
                "text": "<i class='fas fa-copy'></i> Copy",
                "className": "btn btn-info bg-gradient-info btn-sm"
            },{
                "extend": "csv",
                "text": "<i class='fas fa-file-csv'></i> Export to CSV",
                "className": "btn btn-info bg-gradient-info btn-sm"
            },
            {
                "extend": "excel",
                "text": "<i class='fas fa-file-excel'></i> Export to Excel",
                "className": "btn btn-info bg-gradient-info btn-sm"
            },

            {
                "extend": "print",
                "text": "<i class='fas fa-print'></i> Print",
                "className": "btn btn-info bg-gradient-info btn-sm"
            },
            {
                "extend": "colvis",
                "text": "<i class='fas fa-eye'></i> Column visibility",
                "className": "btn btn-info bg-gradient-info btn-sm"
            }
        ];
    }
</script>
<script>
    $(document).ready(function() {
        // Listen for changes in the dropdown
        $('#dashboard_year').on('change', function() {
            $('#dashboard_year_form').submit();
        });
    });

    // Bridge function for Android WebView
    window.receiveAppFcmToken = function(token) {
        console.log("FCM Token received from Android App:", token);
        $.ajax({
            url: "{{ route('save_device_token') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                token: token,
                device_type: 'android'
            },
            success: function(response) {
                console.log('Android device token saved successfully.');
            },
            error: function(error) {
                console.error('Error saving Android device token:', error);
            }
        });
    };
</script>

@php
    $fcmSetting = \App\Models\Hrm\HrmSetting::first();
@endphp
@if(auth()->check() && $fcmSetting && $fcmSetting->is_browser_notification_enabled && $fcmSetting->fcm_api_key)
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.10.1/firebase-messaging.js"></script>
<script>
    const firebaseConfig = {
        apiKey: "{{ $fcmSetting->fcm_api_key }}",
        authDomain: "{{ $fcmSetting->fcm_auth_domain }}",
        projectId: "{{ $fcmSetting->fcm_project_id }}",
        storageBucket: "{{ $fcmSetting->fcm_storage_bucket }}",
        messagingSenderId: "{{ $fcmSetting->fcm_messaging_sender_id }}",
        appId: "{{ $fcmSetting->fcm_app_id }}"
    };

    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }
    const messaging = firebase.messaging();

    function requestNotificationPermission() {
        Notification.requestPermission().then((permission) => {
            if (permission === 'granted') {
                messaging.getToken().then((currentToken) => {
                    if (currentToken) {
                        saveTokenToDatabase(currentToken);
                    } else {
                        console.log('No FCM registration token available.');
                    }
                }).catch((err) => {
                    console.log('An error occurred while retrieving token.', err);
                });
            } else {
                console.log('FCM Notification permission denied.');
            }
        });
    }

    function saveTokenToDatabase(token) {
        $.ajax({
            url: "{{ route('save_device_token') }}",
            method: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                token: token,
                device_type: 'web'
            },
            success: function(response) {
                // Token saved successfully
            },
            error: function(error) {
                // Handle error
            }
        });
    }

    messaging.onMessage((payload) => {
        let notificationSound = document.getElementById('notification-success-audio');
        if (notificationSound) {
            notificationSound.play();
        }

        $(document).Toasts('create', {
            icon: 'fas fa-bell fa-lg',
            class: 'bg-primary',
            title: payload.notification.title,
            autohide: true,
            delay: 10000,
            body: payload.notification.body
        });
    });

    $(document).ready(function() {
        // Check if service worker is supported
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/firebase-messaging-sw.js').then(function(registration) {
                messaging.useServiceWorker(registration);
                requestNotificationPermission();
            }).catch(function(err) {
                console.log('Service worker registration failed, error:', err);
            });
        }
    });
</script>
@endif
