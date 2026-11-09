<x-guest-layout>
    @section('title', 'Login')
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
        <div>
            <label class="block font-medium text-sm text-gray-700 mb-1" for="email">{{ __('Email/Username/Mobile No.') }} <span style="color: darkred">*</span></label>
            <x-text-input id="email" class="block mt-1 w-full h-10 py-3 px-4 !rounded-lg !border !border-gray-300 focus:!border-primary_color focus:!ring-primary_color transition-all duration-300 shadow-sm" type="text" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <label class="block font-medium text-sm text-gray-700 mb-1" for="password">{{ __('Password') }} <span style="color: darkred">*</span></label>
            <x-text-input id="password" class="block mt-1 w-full h-10 py-3 px-4 !rounded-lg !border !border-gray-300 focus:!border-primary_color focus:!ring-primary_color transition-all duration-300 shadow-sm"
                          type="password"
                          name="password"
                          required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">{{ __('Remember me') }}</span>
            </label>
        </div>

        <div class="flex items-center justify-end mt-4">
            @if (Route::has('password.request'))
                <a class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" href="{{ route('password.request') }}">
                    {{ __('Forgot your password?') }}
                </a>
            @endif

            <x-primary-button class="ms-3">
                {{ __('Log in') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
