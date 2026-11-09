@extends('layouts.app')

@section('title', 'Attendance Success')

@section('content')
<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card text-center shadow-sm" style="border-radius: 15px;">
            <div class="card-body py-5">
                <i class="fas fa-check-circle text-success" style="font-size: 80px; margin-bottom: 20px;"></i>
                <h3 class="font-weight-bold text-success mb-3">Attendance Submitted!</h3>
                
                @if(session('message'))
                    <p class="text-muted" style="font-size: 1.1rem;">{{ session('message') }}</p>
                @else
                    <p class="text-muted" style="font-size: 1.1rem;">Your attendance has been successfully recorded.</p>
                @endif
                
                <div class="mt-4">
                    <a href="{{ route('dashboard') }}" class="btn btn-primary px-4 py-2" style="border-radius: 30px;">
                        <i class="fas fa-home mr-1"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
