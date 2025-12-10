<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Seller Registration - DiasZone</title>
    <link rel="icon" type="image/png" href="{{ asset('storage/images_homepage/favicon.png') }}">
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
            <h1 class="text-white text-2xl font-bold">Become a Seller</h1>
            <p class="text-gray-400">Create your seller account</p>
        </div>
        
        @if($errors->any())
            <div class="mb-4 p-4 bg-red-600/20 border border-red-500/30 text-red-400 rounded-lg">
                @foreach($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        @endif
        
        <div class="bg-slate-800 rounded-2xl p-8 shadow-xl">
            <form action="{{ route('seller.register.submit') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Full Name</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Username (for your store URL)</label>
                    <div class="flex items-center">
                        <span class="px-3 py-3 bg-slate-600 text-gray-400 border border-slate-600 border-r-0 rounded-l-lg text-sm">/store/</span>
                        <input type="text" name="username" value="{{ old('username') }}" required
                            pattern="[a-zA-Z0-9_-]+"
                            class="flex-1 px-4 py-3 bg-slate-700 border border-slate-600 rounded-r-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                    </div>
                    <p class="text-gray-500 text-xs mt-1">Only letters, numbers, dashes and underscores</p>
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Email Address</label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Phone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Store Name</label>
                    <input type="text" name="store_name" value="{{ old('store_name') }}" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>

                <div>
                    <label class="block text-gray-300 text-sm mb-2">Where do most of your customers come from?</label>
                    <select id="main-platform-select" name="main_platform" required class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                        <option value="">Select platform</option>
                        <option value="facebook" {{ old('main_platform')=='facebook' ? 'selected' : '' }}>Facebook</option>
                        <option value="instagram" {{ old('main_platform')=='instagram' ? 'selected' : '' }}>Instagram</option>
                        <option value="tiktok" {{ old('main_platform')=='tiktok' ? 'selected' : '' }}>TikTok</option>
                    </select>
                    <p class="text-gray-500 text-xs mt-1">Choose the platform where most of your customers come from.</p>
                </div>

                <div id="platform-url-row" class="hidden">
                    <label class="block text-gray-300 text-sm mb-2">Platform Page URL</label>
                    <input id="platform-url-input" type="url" name="platform_url" value="{{ old('platform_url') }}" placeholder="https://facebook.com/yourpage"
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                    <p class="text-gray-500 text-xs mt-1">Enter a public URL to your page/profile on the chosen platform.</p>
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Password</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">Confirm Password</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 focus:ring-1 focus:ring-blue-500 outline-none transition">
                </div>
                
                <div class="bg-blue-600/20 border border-blue-500/30 rounded-lg p-4">
                    <p class="text-blue-300 text-sm">
                        <strong>Note:</strong> Your account will need admin approval before you can start selling.
                    </p>
                </div>
                
                <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition">
                    Create Account
                </button>
            </form>
            
            <div class="mt-6 text-center">
                <p class="text-gray-400">Already have an account?</p>
                <a href="{{ route('seller.login') }}" class="text-blue-400 hover:text-blue-300 font-medium">Login</a>
            </div>
        </div>
        
            {{-- Back to main website link removed as per request --}}
    </div>
    <script>
        const platformSelect = document.getElementById('main-platform-select');
        const platformRow = document.getElementById('platform-url-row');
        const platformInput = document.getElementById('platform-url-input');

        function updatePlatformVisibility() {
            if (!platformSelect) return;
            const v = platformSelect.value;
            if (v) {
                platformRow.classList.remove('hidden');
                platformInput.setAttribute('required', 'required');
            } else {
                platformRow.classList.add('hidden');
                platformInput.removeAttribute('required');
            }
        }
        platformSelect?.addEventListener('change', updatePlatformVisibility);
        updatePlatformVisibility();
    </script>
</body>
</html>
