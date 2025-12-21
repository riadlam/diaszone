<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}" class="{{ app()->getLocale() == 'ar' ? 'overflow-x-hidden' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'DiasZone - Mobile Legends Top Up')</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
    
    <!-- Google Fonts - Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Vite Assets -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @stack('styles')
</head>
<body class="font-sans antialiased {{ request()->routeIs('dashboard*') ? 'dashboard-page' : '' }} {{ app()->getLocale() == 'ar' ? 'overflow-x-hidden' : '' }}" style="background-color: #ffffff;">
    <div id="app" class="{{ app()->getLocale() == 'ar' ? 'overflow-x-hidden' : '' }}" style="position: relative !important;">
        <!-- Header -->
        @include('components.header')
        
        <!-- Main Content -->
        <main style="overflow: visible !important; position: relative !important;">
            @yield('content')
        </main>
        
        <!-- Footer -->
        @unless(request()->routeIs('dashboard*'))
            @include('components.footer')
        @endunless
    </div>
    
    @include('components.whatsapp-fab')
        @stack('scripts')
        <!-- No real-time or polling scripts — order status is authoritative on the server and is changed only by webhooks. Clients should refresh to see updates. -->
</body>
</html>


