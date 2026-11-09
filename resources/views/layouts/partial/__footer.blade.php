<footer class="main-footer text-sm">
    <!-- To the right -->
    <div class="float-right d-none d-sm-inline">
        Developed by <a target="_blank" style="color: #0056b3" href="{{ config('app.developer_web') }}">{{ config('app.developer') }}</a>
    </div>
    <!-- Default to the left -->
    <strong>Copyright &copy; {{ date('Y') }} <a href="{{ route('dashboard') }}" style="color: #0056b3">{{ config('app.name') }}</a></strong> All rights reserved.
</footer>
