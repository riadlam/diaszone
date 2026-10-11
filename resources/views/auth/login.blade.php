@extends('layouts.app')

@section('title', __('auth.sign_in_title') . ' - DiasZone')

@push('styles')
<style>
.dz-auth {
    --ink: #0b1220;
    --muted: #64748b;
    --line: rgba(15, 23, 42, 0.08);
    --panel: rgba(255, 255, 255, 0.96);
}
.dz-auth-shell {
    min-height: calc(100vh - 4rem);
    display: flex;
    align-items: center;
    justify-content: center;
    background:
        radial-gradient(900px 520px at 12% 18%, rgba(91, 33, 182, 0.14), transparent 58%),
        radial-gradient(720px 420px at 88% 8%, rgba(14, 165, 233, 0.1), transparent 55%),
        linear-gradient(165deg, #f8fafc 0%, #eef2ff 45%, #f1f5f9 100%);
    padding: 1.5rem 1rem 2.5rem;
}
.dz-auth-card {
    width: 100%;
    max-width: 420px;
    background: var(--panel);
    backdrop-filter: blur(12px);
    border: 1px solid var(--line);
    border-radius: 1.5rem;
    padding: 2rem 1.5rem 1.75rem;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
    text-align: center;
}
@media (min-width: 640px) {
    .dz-auth-card { padding: 2.5rem 2.25rem 2rem; }
}
.dz-auth-logo-wrap {
    display: flex;
    justify-content: center;
    margin-bottom: 1.25rem;
}
.dz-auth-logo {
    width: 4.5rem;
    height: 4.5rem;
    border-radius: 1.1rem;
    object-fit: cover;
    box-shadow: 0 10px 28px rgba(15, 23, 42, 0.16);
    border: 1px solid rgba(15, 23, 42, 0.08);
}
.dz-auth-card h1 {
    font-size: 1.55rem;
    font-weight: 900;
    color: var(--ink);
    letter-spacing: -0.02em;
    line-height: 1.2;
}
.dz-auth-card .sub {
    margin-top: 0.45rem;
    color: var(--muted);
    font-size: 0.95rem;
    line-height: 1.5;
}
.dz-google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.75rem;
    width: 100%;
    min-height: 3.35rem;
    padding: 0.9rem 1.1rem;
    border-radius: 0.95rem;
    border: 1px solid #dadce0;
    background: #fff;
    color: #3c4043;
    font-weight: 700;
    font-size: 1rem;
    box-shadow: 0 1px 2px rgba(60, 64, 67, 0.12), 0 1px 3px 1px rgba(60, 64, 67, 0.08);
    transition: background .15s ease, box-shadow .15s ease, border-color .15s ease, transform .15s ease;
}
.dz-google-btn:hover {
    background: #f8faff;
    border-color: #c6dafc;
    box-shadow: 0 4px 14px rgba(66, 133, 244, 0.18);
    transform: translateY(-1px);
}
.dz-google-btn:active { transform: translateY(0); }
.dz-google-btn__icon {
    display: grid;
    place-items: center;
    width: 1.5rem;
    height: 1.5rem;
}
.dz-auth-alert {
    border-radius: 0.9rem;
    padding: 0.85rem 1rem;
    font-size: 0.875rem;
    line-height: 1.45;
    text-align: left;
}
.dz-auth-alert--ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.dz-auth-alert--err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
</style>
@endpush

@section('content')
@php
    $googleHref = route('auth.google', array_filter(['redirect' => $redirect ?? request('redirect')]));
@endphp
<div class="dz-auth dz-auth-shell">
    <section class="dz-auth-card">
        <div class="dz-auth-logo-wrap">
            <img src="{{ storage_public_url('images_homepage/diaszonelogo.jpeg') }}" alt="DiasZone" class="dz-auth-logo">
        </div>

        <h1>{{ !empty($signupHint) ? __('auth.create_account_title') : __('auth.sign_in_title') }}</h1>
        <p class="sub">{{ !empty($signupHint) ? __('auth.create_account_hint') : __('auth.sign_in_subtitle') }}</p>

        <div class="mt-7 space-y-4">
            @if(session('success'))
                <div class="dz-auth-alert dz-auth-alert--ok">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="dz-auth-alert dz-auth-alert--err">{{ session('error') }}</div>
            @endif

            @if($errors->any())
                <div class="dz-auth-alert dz-auth-alert--err">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @include('components.google-sign-in-button', ['href' => $googleHref])

            <p class="text-xs text-slate-500 leading-relaxed">
                {{ __('checkout.terms_agreement') }}
                <a href="{{ route('terms-of-use') }}" target="_blank" class="text-violet-700 hover:underline font-semibold">{{ __('checkout.terms_of_use') }}</a>
                ·
                <a href="{{ route('privacy-policy') }}" target="_blank" class="text-violet-700 hover:underline font-semibold">{{ __('footer.privacy_policy') }}</a>
            </p>
        </div>

        <p class="text-sm text-slate-500 mt-7">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 font-bold text-violet-700 hover:text-violet-900">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                {{ __('common.go_to_home') }}
            </a>
        </p>
    </section>
</div>
@endsection
