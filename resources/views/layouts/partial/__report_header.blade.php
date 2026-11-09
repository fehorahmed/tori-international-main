<div class="row mx-0 header-common-info" id="header-common-info" style="display: none">
    <div class="col-6 text-left">
        <p><b>{{ date('d-m-Y, h:i A') }}</b></p>
    </div>
    <div class="col-6 text-right">
        <p><b>Printed by: {{ auth()->user()->name }}</b></p>
    </div>
</div>
<div class="row mx-0 header-common-info">
    <div class="col-4">
        <h1 class="text-left m-0" style="font-size: 45px !important;font-weight: bold">
            <img height="110px" src="{{ asset('img/logo.png') }}" alt="">
        </h1>
    </div>
    <div class="col-8 text-right">
        <h2 class="m-0"><b>{{ config('app.name') }}</b></h2>
        <h5 class="m-0">{{ config('app.address') }}</h5>
        <h5 class="m-0">{{ config('app.contact') }}</h5>
        <h5>{{ config('app.email') }}</h5>
    </div>
</div>
<div style="border-bottom: 1.5px solid #000;"></div>
@isset($report_title)
    <div class="row mx-0 header-common-info">
        <div class="col-md-12 text-center">
            <h2 class="mb-0"><u>{{ $report_title }}</u></h2>
        </div>
    </div>
@endisset