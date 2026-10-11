@extends('layouts.app')

@section('title', __('auth.sign_in_title') . ' - DiasZone')

@push('styles')
<style>
.dz-auth {
    --ink: #0b1220;
    --muted: #64748b;
    --line: rgba(15, 23, 42, 0.08);
    --accent: #5b21b6;
    --panel: rgba(255, 255, 255, 0.92);
}
.dz-auth-shell {
    min-height: calc(100vh - 4rem);
    display: flex;
    align-items: stretch;
    background:
        radial-gradient(900px 520px at 12% 18%, rgba(91, 33, 182, 0.16), transparent 58%),
        radial-gradient(720px 420px at 88% 8%, rgba(14, 165, 233, 0.12), transparent 55%),
        linear-gradient(165deg, #f8fafc 0%, #eef2ff 45%, #f1f5f9 100%);
}
.dz-auth-grid {
    width: 100%;
    max-width: 1080px;
    margin: 0 auto;
    padding: 1.5rem 1rem 2.5rem;
    display: grid;
    gap: 1.25rem;
    align-items: center;
}
@media (min-width: 900px) {
    .dz-auth-grid {
        grid-template-columns: 1.05fr 0.95fr;
        gap: 2rem;
        padding: 2.5rem 1.25rem 3.5rem;
        min-height: calc(100vh - 4rem);
    }
}
.dz-auth-brand {
    position: relative;
    overflow: hidden;
    border-radius: 1.5rem;
    padding: 1.75rem 1.5rem;
    color: #fff;
    background:
        linear-gradient(145deg, rgba(15, 23, 42, 0.96) 0%, rgba(49, 20, 92, 0.94) 55%, rgba(30, 41, 59, 0.96) 100%);
    box-shadow: 0 24px 60px rgba(15, 23, 42, 0.18);
    min-height: 280px;
}
@media (min-width: 900px) {
    .dz-auth-brand { min-height: 520px; padding: 2.5rem; }
}
.dz-auth-brand::before {
    content: "";
    position: absolute;
    inset: auto -20% -40% 20%;
    height: 280px;
    background: radial-gradient(circle, rgba(167, 139, 250, 0.35), transparent 65%);
    pointer-events: none;
}
.dz-auth-brand > * { position: relative; z-index: 1; }
.dz-auth-logo {
    width: 3.25rem;
    height: 3.25rem;
    border-radius: 0.9rem;
    object-fit: cover;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.28);
    border: 1px solid rgba(255, 255, 255, 0.18);
}
.dz-auth-brand h1 {
    margin: 1.25rem 0 0.65rem;
    font-size: clamp(1.75rem, 3.5vw, 2.55rem);
    font-weight: 900;
    letter-spacing: -0.03em;
    line-height: 1.1;
}
.dz-auth-brand p.lead {
    color: rgba(226, 232, 240, 0.88);
    font-size: 0.98rem;
    line-height: 1.55;
    max-width: 28rem;
}
.dz-auth-benefits {
    margin-top: 1.5rem;
    display: grid;
    gap: 0.75rem;
}
.dz-auth-benefit {
    display: flex;
    gap: 0.75rem;
    align-items: flex-start;
    padding: 0.85rem 0.95rem;
    border-radius: 0.9rem;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.1);
}
.dz-auth-benefit strong {
    display: block;
    font-size: 0.9rem;
    font-weight: 800;
}
.dz-auth-benefit span {
    display: block;
    margin-top: 0.15rem;
    font-size: 0.8rem;
    color: rgba(226, 232, 240, 0.72);
    line-height: 1.4;
}
.dz-auth-benefit-icon {
    flex-shrink: 0;
    width: 2rem;
    height: 2rem;
    border-radius: 0.6rem;
    display: grid;
    place-items: center;
    background: rgba(167, 139, 250, 0.22);
    color: #ddd6fe;
}
.dz-auth-card {
    background: var(--panel);
    backdrop-filter: blur(12px);
    border: 1px solid var(--line);
    border-radius: 1.5rem;
    padding: 1.5rem;
    box-shadow: 0 18px 50px rgba(15, 23, 42, 0.08);
}
@media (min-width: 640px) {
    .dz-auth-card { padding: 2rem 2.25rem; }
}
.dz-auth-card h2 {
    font-size: 1.45rem;
    font-weight: 900;
    color: var(--ink);
    letter-spacing: -0.02em;
}
.dz-auth-card .sub {
    margin-top: 0.4rem;
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
    min-height: 3.25rem;
    padding: 0.85rem 1.1rem;
    border-radius: 0.95rem;
    border: 1px solid #dadce0;
    background: #fff;
    color: #3c4043;
    font-weight: 700;
    font-size: 0.98rem;
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
}
.dz-auth-alert--ok { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }
.dz-auth-alert--err { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
.dz-auth-alert--hint { background: #f5f3ff; border: 1px solid #ddd6fe; color: #4c1d95; }
.dz-auth-trust {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem 0.85rem;
    margin-top: 1.1rem;
    color: var(--muted);
    font-size: 0.75rem;
    font-weight: 700;
}
.dz-auth-trust span {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
}
</style>
@endpush

@section('content')
@php
    $googleHref = route('auth.google', array_filter(['redirect' => $redirect ?? request('redirect')]));
@endphp
<div class="dz-auth dz-auth-shell">
    <div class="dz-auth-grid">
        <section class="dz-auth-brand" aria-label="DiasZone">
            <img src="{{ storage_public_url('images_homepage/diaszonelogo.jpeg') }}" alt="DiasZone" class="dz-auth-logo">
            <h1>{{ __('auth.brand_headline') }}</h1>
            <p class="lead">{{ __('auth.brand_subheadline') }}</p>

            <div class="dz-auth-benefits">
                <div class="dz-auth-benefit">
                    <div class="dz-auth-benefit-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    </div>
                    <div>
                        <strong>{{ __('auth.benefit_fast_title') }}</strong>
                        <span>{{ __('auth.benefit_fast_desc') }}</span>
                    </div>
                </div>
                <div class="dz-auth-benefit">
                    <div class="dz-auth-benefit-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <strong>{{ __('auth.benefit_secure_title') }}</strong>
                        <span>{{ __('auth.benefit_secure_desc') }}</span>
                    </div>
                </div>
                <div class="dz-auth-benefit">
                    <div class="dz-auth-benefit-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    </div>
                    <div>
                        <strong>{{ __('auth.benefit_orders_title') }}</strong>
                        <span>{{ __('auth.benefit_orders_desc') }}</span>
                    </div>
                </div>
            </div>
        </section>

        <section class="dz-auth-card">
            <p class="text-xs font-bold uppercase tracking-[0.14em] text-violet-700 mb-2">DiasZone</p>
            <h2>{{ !empty($signupHint) ? __('auth.create_account_title') : __('auth.sign_in_title') }}</h2>
            <p class="sub">{{ !empty($signupHint) ? __('auth.create_account_hint') : __('auth.sign_in_subtitle') }}</p>

            <div class="mt-6 space-y-4">
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

                <div class="dz-auth-trust">
                    <span>
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('auth.trust_oauth') }}
                    </span>
                    <span>
                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ __('auth.trust_no_password') }}
                    </span>
                </div>

                <p class="text-center text-xs text-slate-500 leading-relaxed pt-2">
                    {{ __('checkout.terms_agreement') }}
                    <a href="{{ route('terms-of-use') }}" target="_blank" class="text-violet-700 hover:underline font-semibold">{{ __('checkout.terms_of_use') }}</a>
                    ·
                    <a href="{{ route('privacy-policy') }}" target="_blank" class="text-violet-700 hover:underline font-semibold">{{ __('footer.privacy_policy') }}</a>
                </p>
            </div>

            <p class="text-center text-sm text-slate-500 mt-7">
                <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 font-bold text-violet-700 hover:text-violet-900">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    {{ __('common.go_to_home') }}
                </a>
            </p>
        </section>
    </div>
</div>
@endsection
