@php
    use Illuminate\Support\Facades\Storage;
    $path = $entry->screenshot_path;
    $src = null;
    if ($path && Storage::disk('public')->exists($path)) {
        $mime = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
        $src = 'data:'.$mime.';base64,'.base64_encode(Storage::disk('public')->get($path));
    }
@endphp

<div class="space-y-3">
    @if($src)
        <img src="{{ $src }}" alt="Screenshot" class="w-full max-h-96 rounded-xl object-contain border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900" />
    @else
        <p class="text-sm text-danger-600">Screenshot file missing.</p>
    @endif
    <div class="text-sm text-gray-600 dark:text-gray-300 space-y-1">
        <p><strong>Player:</strong> {{ $entry->user?->name }} ({{ $entry->user?->email }})</p>
        <p><strong>Referral:</strong> {{ $entry->referralSatisfied() ? '1/1' : '0/1' }}</p>
    </div>
</div>
