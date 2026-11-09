
<!-- Google Font: Source Sans Pro -->
{{-- <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> --}}
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Parkinsans:wght@300..800&display=swap" rel="stylesheet">
<!-- Font Awesome Icons -->
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/fontawesome-free/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/fontawesome-6/css/all.min.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/tippyjs/tippy.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/tippyjs/themes/material.css') }}">
<!-- Phosphor Icons -->
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/phosphor-icons/src/regular/style.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/phosphor-icons/src/bold/style.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/phosphor-icons/src/fill/style.css') }}">
<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<!-- SweetAlert2 -->
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/bootstrap-switch/css/bootstrap3/bootstrap-switch.min.css') }}">
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/spectrum-colorpicker2/dist/spectrum.min.css">
<!-- Toastr -->
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/toastr/toastr.min.css') }}">
<!-- DataTables -->
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<!-- ColReorder CSS -->
<link rel="stylesheet"  href="{{ asset('themes/backend/plugins/datatables-colreorder/css/colReorder.bootstrap4.min.css') }}">


<!-- iCheck for checkboxes and radio inputs -->
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/icheck-bootstrap/icheck-bootstrap.min.css') }}">
<!-- Tempusdominus Bootstrap 4 -->
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">

<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('themes/backend/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">

<!-- bootstrap datepicker -->
<link rel="stylesheet"
      href="{{ asset('themes/backend/plugins/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css') }}">
<link href="{{ asset('themes/backend/jquery-ui-1.13.2.custom/jquery-ui.css') }}" rel="stylesheet" type="text/css"/>

<link href="{{ asset('themes/backend/month-picker/MonthPicker.min.css') }}" rel="stylesheet" type="text/css"/>

<link rel="stylesheet" href="{{ asset('themes/backend/plugins/pace-progress/themes/black/pace-theme-flat-top.css') }}">

<link rel="stylesheet" href="{{ asset('themes/backend/dist/css/magnific-popup.min.css') }}" />
<!-- Theme style -->
<link rel="stylesheet" href="{{ asset('themes/backend/dist/css/adminlte.min.css') }}">
<link rel="stylesheet" href="{{ asset('themes/backend/dist/css/custom.css') }}">
<style>
    html,body {
        font-family: "Parkinsans", sans-serif;
    }
    .pace .pace-progress {
        height: 3px !important;
    }
    /* [class*=sidebar-dark-] {
        background-color: #000000;
    } */
    [class*=sidebar-dark-] .sidebar a {
        color: #fcfdff;
    }
    .nav-flat .nav-item>.nav-link>.nav-icon {
        color: #d10a55;
    }
    .create-task-btn {
        background-color: #28a745;
        color: white;
        border: none;
        padding: 6px 16px;
        font-size: 18px;
        font-weight: bold;
        border-radius: 50px;
        cursor: pointer;
        outline: none;
        position: relative;
        overflow: hidden;
        animation: pulse 2s infinite ease-in-out;
        margin-left: 18px;
    }
    .create-task-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 50px;
        background: rgba(255, 255, 255, 0.2);
        z-index: 1;
        animation: glow 3s infinite ease-in-out; /* Continuous glow effect */
    }
    
    /* Pulse animation for the button */
    @keyframes pulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    /* Glow animation for the background effect */
    @keyframes glow {
        0%, 100% {
            opacity: 0.4;
            transform: scale(1);
        }
        50% {
            opacity: 0.8;
            transform: scale(1.2);
        }
    }
    @media print {
        @page {
            size: auto;
            margin: .3in .5in .5in .5in !important;
        }
        #header-common-info{
            display: flex !important;
        }
    }
    .tippy-box[data-theme~='material'] {
        font-size: 12px !important;
        font-weight: 400 !important;
        padding: 0px 0px !important;
    }

    /* Global Image Upload Box Styles */
    .image-upload-box {
        width: 150px;
        height: 150px;
        border: 2px dashed #ddd;
        border-radius: 10px;
        display: flex !important;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        overflow: hidden;
        position: relative;
        background-color: #f8f9fa;
        transition: all 0.3s ease;
    }
    .image-upload-box:hover {
        border-color: #007bff;
        background-color: #e9ecef;
    }
    .image-upload-box img {
        max-width: 100%;
        max-height: 100%;
        object-fit: cover;
        display: none;
    }
    .image-upload-box .placeholder-content {
        text-align: center;
        color: #888;
    }
    .image-upload-box .placeholder-content i {
        display: block;
        font-size: 2rem;
        margin-bottom: 5px;
    }
    .file-input {
        display: none;
    }
</style>
