@extends('layouts.admin')

@section('title', 'Edit Game Content - Admin - DiasZone')

@section('content')
<div class="p-4 md:p-6">
    <div class="max-w-7xl mx-auto">
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Edit Game Content</h1>
                    <p class="text-gray-600 mt-2 text-sm md:text-base">{{ $game->name }} ({{ $game->game_type }})</p>
                </div>
                <a href="{{ route('admin.game-content.index') }}" 
                   class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors">
                    ← Back to List
                </a>
            </div>
        </div>

        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <!-- Content Form -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6 mb-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">{{ __('game_content.game_content') }}</h2>
            
            <form action="{{ route('admin.game-content.store', $game) }}" method="POST" class="space-y-6">
                @csrf

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Currency Name -->
                    <div>
                        <label for="currency_name" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('game_content.currency_name') }} <span class="text-gray-500">{{ __('game_content.currency_name_hint') }}</span>
                        </label>
                        <input type="text" 
                               id="currency_name" 
                               name="currency_name" 
                               value="{{ old('currency_name', $content->currency_name ?? '') }}"
                               class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                    </div>

                    <!-- ID Format -->
                    <div>
                        <label for="id_format" class="block text-sm font-semibold text-gray-700 mb-2">
                            {{ __('game_content.id_format') }}
                        </label>
                        <select id="id_format" 
                                name="id_format" 
                                class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">{{ __('game_content.select_format') }}</option>
                            <option value="user_id_zone_id" {{ old('id_format', $content->id_format ?? '') === 'user_id_zone_id' ? 'selected' : '' }}>{{ __('game_content.id_format_user_id_zone_id') }}</option>
                            <option value="player_id" {{ old('id_format', $content->id_format ?? '') === 'player_id' ? 'selected' : '' }}>{{ __('game_content.id_format_player_id') }}</option>
                            <option value="user_id" {{ old('id_format', $content->id_format ?? '') === 'user_id' ? 'selected' : '' }}>{{ __('game_content.id_format_user_id') }}</option>
                            <option value="user_id_server" {{ old('id_format', $content->id_format ?? '') === 'user_id_server' ? 'selected' : '' }}>{{ __('game_content.id_format_user_id_server') }}</option>
                        </select>
                    </div>
                </div>

                <!-- About Text -->
                <div>
                    <label for="about_text" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('game_content.about_text') }}
                    </label>
                    <textarea id="about_text" 
                              name="about_text" 
                              rows="4"
                              class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('about_text', $content->about_text ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('game_content.about_text_hint') }}</p>
                </div>

                <!-- Instructions Text -->
                <div>
                    <label for="instructions_text" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('game_content.instructions_text') }}
                    </label>
                    <textarea id="instructions_text" 
                              name="instructions_text" 
                              rows="6"
                              class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('instructions_text', $content->instructions_text ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('game_content.instructions_text_hint') }}</p>
                </div>

                <!-- How to Top Up -->
                <div>
                    <label for="how_to_topup" class="block text-sm font-semibold text-gray-700 mb-2">
                        {{ __('game_content.how_to_topup') }}
                    </label>
                    <textarea id="how_to_topup" 
                              name="how_to_topup" 
                              rows="4"
                              class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">{{ old('how_to_topup', $content->how_to_topup ?? '') }}</textarea>
                    <p class="text-xs text-gray-500 mt-1">{{ __('game_content.how_to_topup_hint') }}</p>
                </div>

                <div class="flex justify-end">
                    <button type="submit" 
                            class="px-6 py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold rounded-lg transition-colors">
                        {{ __('game_content.save_content') }}
                    </button>
                </div>
            </form>
        </div>

        <!-- Images Section -->
        <div class="bg-white rounded-xl shadow-lg border-2 border-purple-100 p-4 md:p-6 mb-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-900">{{ __('game_content.game_images') }}</h2>
                <button type="button" 
                        id="toggleUploadForm"
                        class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors shadow-md">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    <span>{{ __('game_content.add_image') }}</span>
                </button>
            </div>

            <!-- Upload Form (Collapsible) -->
            <div id="uploadFormContainer" class="hidden bg-gradient-to-br from-gray-50 to-purple-50 rounded-xl border-2 border-purple-200 p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-800">{{ __('game_content.upload_new_image') }}</h3>
                    <button type="button" 
                            id="closeUploadForm"
                            class="text-gray-500 hover:text-gray-700 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="{{ route('admin.game-content.images.store', $game) }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      id="imageUploadForm"
                      class="space-y-4">
                    @csrf

                    <!-- Image Preview & Upload -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="image" class="block text-sm font-semibold text-gray-700 mb-2">
                                Image File <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="file" 
                                       id="image" 
                                       name="image" 
                                       accept="image/jpeg,image/jpg,image/png,image/webp"
                                       required
                                       class="hidden"
                                       onchange="previewImage(this)">
                                <label for="image" class="cursor-pointer flex flex-col items-center justify-center w-full h-48 border-2 border-dashed border-gray-300 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors">
                                    <div id="imagePreviewPlaceholder" class="text-center">
                                        <svg class="w-12 h-12 text-gray-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <p class="text-sm text-gray-600 font-medium">Click to upload</p>
                                        <p class="text-xs text-gray-500 mt-1">Max 5MB • JPEG, PNG, WebP</p>
                                    </div>
                                    <img id="imagePreview" src="" alt="Preview" class="hidden w-full h-full object-contain rounded-lg">
                                </label>
                            </div>
                            <button type="button" 
                                    id="removePreview" 
                                    onclick="removeImagePreview()"
                                    class="hidden mt-2 text-sm text-red-600 hover:text-red-700 font-medium">
                                Remove image
                            </button>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label for="image_type" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Section <span class="text-red-500">*</span>
                                </label>
                                <select id="image_type" 
                                        name="image_type" 
                                        required
                                        class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 bg-white">
                                    <option value="about">About Section</option>
                                    <option value="instruction">Instruction Section</option>
                                    <option value="how_to_topup">How to Top Up Section</option>
                                </select>
                            </div>

                            <div>
                                <label for="display_order" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Display Order
                                </label>
                                <input type="number" 
                                       id="display_order" 
                                       name="display_order" 
                                       value="{{ old('display_order', $images->count()) }}"
                                       min="0"
                                       class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                                <p class="text-xs text-gray-500 mt-1">Lower numbers display first</p>
                            </div>

                            <div>
                                <label for="alt_text" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Alt Text
                                </label>
                                <input type="text" 
                                       id="alt_text" 
                                       name="alt_text" 
                                       value="{{ old('alt_text') }}"
                                       placeholder="Describe the image for accessibility"
                                       class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>

                            <div>
                                <label for="title" class="block text-sm font-semibold text-gray-700 mb-2">
                                    Title/Caption (Optional)
                                </label>
                                <input type="text" 
                                       id="title" 
                                       name="title" 
                                       value="{{ old('title') }}"
                                       placeholder="Optional caption below image"
                                       class="w-full px-4 py-2 border-2 border-gray-200 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                        <button type="button" 
                                id="cancelUpload"
                                class="px-6 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition-colors">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition-colors shadow-md flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                            </svg>
                            <span>Upload Image</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Existing Images Grouped by Section -->
            @php
                $aboutImages = $images->where('image_type', 'about')->sortBy('display_order');
                $instructionImages = $images->where('image_type', 'instruction')->sortBy('display_order');
                $howToTopupImages = $images->where('image_type', 'how_to_topup')->sortBy('display_order');
            @endphp

            @if($images->count() > 0)
            <div class="space-y-6">
                <!-- About Section Images -->
                @if($aboutImages->count() > 0)
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('game_content.section_about') }}</h3>
                        <span class="px-2 py-1 bg-blue-100 text-blue-700 rounded-full text-xs font-semibold">
                            {{ $aboutImages->count() }} {{ $aboutImages->count() === 1 ? __('game_content.image') : __('game_content.images') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($aboutImages as $image)
                        @include('admin.game-content.partials.image-card', ['image' => $image, 'game' => $game])
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Instruction Section Images -->
                @if($instructionImages->count() > 0)
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('game_content.section_instruction') }}</h3>
                        <span class="px-2 py-1 bg-green-100 text-green-700 rounded-full text-xs font-semibold">
                            {{ $instructionImages->count() }} {{ $instructionImages->count() === 1 ? __('game_content.image') : __('game_content.images') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($instructionImages as $image)
                        @include('admin.game-content.partials.image-card', ['image' => $image, 'game' => $game])
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- How to Top Up Section Images -->
                @if($howToTopupImages->count() > 0)
                <div>
                    <div class="flex items-center gap-3 mb-4">
                        <h3 class="text-lg font-semibold text-gray-800">{{ __('game_content.section_how_to_topup') }}</h3>
                        <span class="px-2 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-semibold">
                            {{ $howToTopupImages->count() }} {{ $howToTopupImages->count() === 1 ? __('game_content.image') : __('game_content.images') }}
                        </span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach($howToTopupImages as $image)
                        @include('admin.game-content.partials.image-card', ['image' => $image, 'game' => $game])
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @else
            <div class="text-center py-12 bg-gradient-to-br from-gray-50 to-purple-50 rounded-xl border-2 border-dashed border-gray-300">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <p class="text-gray-600 font-medium">{{ __('game_content.no_images_yet') }}</p>
                <p class="text-sm text-gray-500 mt-2">{{ __('game_content.click_add_image_to_start') }}</p>
            </div>
            @endif
        </div>

    </div>
</div>

@push('scripts')
<script>
    // Toggle upload form
    document.addEventListener('DOMContentLoaded', function() {
        const toggleBtn = document.getElementById('toggleUploadForm');
        const uploadForm = document.getElementById('uploadFormContainer');
        const closeBtn = document.getElementById('closeUploadForm');
        const cancelBtn = document.getElementById('cancelUpload');

        function showUploadForm() {
            if (uploadForm) uploadForm.classList.remove('hidden');
            if (toggleBtn) toggleBtn.classList.add('hidden');
        }

        function hideUploadForm() {
            if (uploadForm) uploadForm.classList.add('hidden');
            if (toggleBtn) toggleBtn.classList.remove('hidden');
            const form = document.getElementById('imageUploadForm');
            if (form) form.reset();
            removeImagePreview();
        }

        if (toggleBtn) toggleBtn.addEventListener('click', showUploadForm);
        if (closeBtn) closeBtn.addEventListener('click', hideUploadForm);
        if (cancelBtn) cancelBtn.addEventListener('click', hideUploadForm);
    });

    // Image preview - make it global so it can be called from inline handlers
    function previewImage(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('imagePreview');
                const placeholder = document.getElementById('imagePreviewPlaceholder');
                const removeBtn = document.getElementById('removePreview');
                
                if (preview) {
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                }
                if (placeholder) placeholder.classList.add('hidden');
                if (removeBtn) removeBtn.classList.remove('hidden');
            };
            reader.readAsDataURL(file);
        }
    }

    function removeImagePreview() {
        const preview = document.getElementById('imagePreview');
        const placeholder = document.getElementById('imagePreviewPlaceholder');
        const removeBtn = document.getElementById('removePreview');
        const fileInput = document.getElementById('image');
        
        if (preview) {
            preview.src = '';
            preview.classList.add('hidden');
        }
        if (placeholder) placeholder.classList.remove('hidden');
        if (removeBtn) removeBtn.classList.add('hidden');
        if (fileInput) fileInput.value = '';
    }
</script>
@endpush
@endsection
