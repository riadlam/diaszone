@extends('layouts.app')

@section('title', __('auth.sign_in_title') . ' - DiasZone')

@section('content')
<div class="bg-gradient-to-br from-gray-50 via-purple-50/40 to-pink-50/30 min-h-screen py-12 px-4">
    <div class="max-w-md mx-auto">
        <div class="bg-white rounded-2xl shadow-xl border border-purple-100 overflow-hidden">
            <div class="bg-gradient-to-r from-purple-600 to-pink-600 px-8 py-10 text-center">
                <h1 class="text-2xl font-bold text-white tracking-tight">{{ __('auth.sign_in_title') }}</h1>
                <p class="text-purple-100 text-sm mt-2">{{ __('auth.sign_in_subtitle') }}</p>
            </div>

            <div class="p-8 md:p-10 space-y-6">
                @if(session('success'))
                    <div class="p-4 bg-green-50 border border-green-200 text-green-800 rounded-xl text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                        {{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-xl text-sm">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($signupHint))
                    <p class="text-center text-sm text-gray-600 bg-purple-50/80 border border-purple-100 rounded-xl py-3 px-4">
                        {{ __('auth.create_account_hint') }}
                    </p>
                @endif

                <a href="{{ route('auth.google') }}"
                   class="flex items-center justify-center gap-3 w-full py-3.5 px-4 rounded-xl border-2 border-gray-200 bg-white text-gray-800 font-semibold shadow-sm hover:shadow-md hover:border-gray-300 transition-all duration-200 group">
                    <svg class="w-5 h-5 shrink-0" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    <span class="group-hover:text-purple-700 transition-colors">{{ __('auth.sign_in_with_google') }}</span>
                </a>

                <p class="text-center text-xs text-gray-500 leading-relaxed">
                    {{ __('checkout.terms_agreement') }}
                    <a href="{{ route('terms-of-use') }}" target="_blank" class="text-purple-600 hover:underline font-medium">{{ __('checkout.terms_of_use') }}</a>
                    ·
                    <a href="{{ route('privacy-policy') }}" target="_blank" class="text-purple-600 hover:underline font-medium">{{ __('footer.privacy_policy') }}</a>
                </p>
            </div>
        </div>

        <p class="text-center text-sm text-gray-600 mt-6">
            <a href="{{ route('home') }}" class="text-purple-600 hover:text-purple-800 font-medium">{{ __('common.go_to_home') }}</a>
        </p>
    </div>
</div>
@endsection
