<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store pending - {{ $seller->store_name ?? $seller->name }}</title>
    @vite(['resources/css/app.css'])
    <style>body{font-family:inherit;background:#0f172a;color:#fff}</style>
</head>
<body class="min-h-screen flex items-center justify-center p-6">
    <div class="max-w-2xl text-center bg-slate-900/70 border border-slate-700 rounded-2xl p-8">
        <div class="mb-4">
            <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-cyan-500 rounded-lg mx-auto flex items-center justify-center text-white font-bold text-xl">{{ substr($seller->name,0,1) }}</div>
        </div>
        <h1 class="text-3xl font-bold mb-3">This store is coming soon</h1>
        <p class="text-slate-300 mb-6">The seller "{{ $seller->store_name ?? $seller->name }}" has not enabled their storefront yet. Please check back later.</p>

        @if(!empty($seller->website_url))
            <p class="text-slate-400 text-sm">Store slug: <strong class="text-white">{{ $seller->website_url }}</strong></p>
        @endif

        <div class="mt-6">
            <a href="{{ route('home') }}" class="inline-block px-5 py-3 bg-blue-600 rounded-lg hover:bg-blue-700">Back to home</a>
        </div>
    </div>
</body>
</html>
