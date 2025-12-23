@extends('layouts.seller')

@section('title', __('seller.profile_title'))
@section('header', __('seller.profile_settings'))

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Profile Info -->
    <div class="bg-slate-800 rounded-xl p-6">
        <h3 class="text-lg font-bold mb-6">{{ __('seller.profile_information') }}</h3>
        
        <form action="{{ route('seller.profile.update') }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.full_name') }}</label>
                <input type="text" name="name" value="{{ old('name', $seller->name) }}" required
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.username_label') }}</label>
                <input type="text" value="{{ $seller->username }}" disabled
                    class="w-full px-4 py-3 bg-slate-600 border border-slate-500 rounded-lg text-gray-400 cursor-not-allowed">
                <p class="text-gray-500 text-xs mt-1">{{ __('seller.username_cannot_change') }}</p>
            </div>
            
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.email_label') }}</label>
                <input type="email" value="{{ $seller->email }}" disabled
                    class="w-full px-4 py-3 bg-slate-600 border border-slate-500 rounded-lg text-gray-400 cursor-not-allowed">
                <p class="text-gray-500 text-xs mt-1">{{ __('seller.contact_admin_email_change') }}</p>
            </div>
            
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.phone_label') }}</label>
                <input type="text" name="phone" value="{{ old('phone', $seller->phone) }}"
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.store_name_label') }}</label>
                <input type="text" name="store_name" value="{{ old('store_name', $seller->store_name) }}"
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
            </div>
            
            <div>
                <label class="block text-gray-300 text-sm mb-2">{{ __('seller.store_description_label') }}</label>
                <textarea name="store_description" rows="3"
                    class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none resize-none">{{ old('store_description', $seller->store_description) }}</textarea>
            </div>
            
            <button type="submit" class="w-full py-3 bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold rounded-lg hover:from-blue-700 hover:to-cyan-700 transition">
                {{ __('seller.update_profile') }}
            </button>
        </form>
    </div>
    
    <!-- Change Password -->
    <div class="space-y-6">
        <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-lg font-bold mb-6">{{ __('seller.change_password') }}</h3>
            
            <form action="{{ route('seller.profile.password') }}" method="POST" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">{{ __('seller.current_password') }}</label>
                    <input type="password" name="current_password" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">{{ __('seller.new_password') }}</label>
                    <input type="password" name="password" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                </div>
                
                <div>
                    <label class="block text-gray-300 text-sm mb-2">{{ __('seller.confirm_new_password') }}</label>
                    <input type="password" name="password_confirmation" required
                        class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-lg text-white focus:border-blue-500 outline-none">
                </div>
                
                <button type="submit" class="w-full py-3 bg-slate-600 text-white font-bold rounded-lg hover:bg-slate-500 transition">
                    {{ __('seller.change_password') }}
                </button>
            </form>
        </div>
        
        <!-- Store URL -->
        <div class="bg-gradient-to-r from-blue-600 to-cyan-600 rounded-xl p-6">
            <h3 class="text-lg font-bold text-white mb-3">{{ __('seller.your_store_url') }}</h3>
            <div class="flex items-center space-x-2">
                <input type="text" value="{{ url('/store/' . $seller->username) }}" readonly
                    class="flex-1 px-4 py-2 bg-white/20 border border-white/30 rounded-lg text-white">
                <button onclick="copyUrl()" class="px-4 py-2 bg-white/20 rounded-lg text-white hover:bg-white/30 transition">
                    {{ __('seller.copy') }}
                </button>
            </div>
            <p class="text-blue-100 text-sm mt-2">{{ __('seller.share_store_url') ?? 'Share this URL with your customers' }}</p>
        </div>
        
        <!-- Account Status -->
            <div class="bg-slate-800 rounded-xl p-6">
            <h3 class="text-lg font-bold mb-4">{{ __('seller.account_status') }}</h3>
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">{{ __('seller.status_label') }}</span>
                    <span class="px-3 py-1 rounded-full text-sm {{ $seller->status === 'active' ? 'bg-green-500/20 text-green-400' : ($seller->status === 'pending' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400') }}">
                        {{ ucfirst($seller->status) }}
                    </span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">{{ __('seller.member_since') }}</span>
                    <span class="text-white">{{ $seller->created_at->format('M d, Y') }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-gray-400">{{ __('seller.total_earnings') }}</span>
                    <span class="text-green-400 font-medium">{{ number_format($seller->total_earnings, 2) }} DZD</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function copyUrl() {
    const url = '{{ url("/store/" . $seller->username) }}';
    navigator.clipboard.writeText(url).then(() => {
        alert({!! json_encode(__('seller.url_copied')) !!});
    });
}
</script>
@endpush
