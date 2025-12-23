<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Login - DiasZone</title>
    <link rel="icon" type="image/png" href="{{ asset('storage_public/images_homepage/favicon.png') }}">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        body { font-family: 'Cairo', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="w-16 h-16 mx-auto mb-4">
                <img src="{{ storage_public_url('images_homepage/diaszonelogo.jpeg') }}" alt="DiasZone logo" class="w-16 h-16 object-contain rounded-xl mx-auto" />
            </div>
            <h1 class="text-white text-2xl font-bold">{{ __('seller.seller_login') }}</h1>
            <p class="text-gray-400">{{ __('seller.welcome_back', ['app' => config('app.name', 'DiasZone')]) }}</p>
        </div>
        
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-600/20 border border-green-500/30 text-green-400 rounded-lg text-center">
                {{ session('success') }}
            </div>
        @endif
        
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-600/20 border border-red-500/30 text-red-400 rounded-lg">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <div class="bg-slate-800 rounded-2xl p-8 shadow-xl">
            <form action="{{ route('seller.login.submit') }}" method="POST" class="space-y-6">
                @csrf
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">{{ __('seller.email_address') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">{{ __('seller.password') }}</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-blue-600 bg-slate-700 border-slate-600 rounded focus:ring-blue-500">
                        <span class="ml-2 text-gray-400 text-sm">{{ __('seller.remember_me') }}</span>
                    </label>
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition">
                    Login
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-400">Don't have an account?</p>
                <a href="{{ route('seller.register') }}" class="text-blue-400 hover:text-blue-300 font-medium">{{ __('seller.register_as_seller') }}</a>
            </div>
        </div>
        
        {{-- Back to main website link removed as requested --}}
    </div>
</body>
</html>
