<?php

namespace App\Http\Controllers;

use App\Support\PublicMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function show(string $path): BinaryFileResponse|StreamedResponse
    {
        abort_unless(PublicMedia::isServable($path), 404);

        $path = PublicMedia::normalize($path);
        $disk = Storage::disk('public');

        if ($disk->exists($path)) {
            return $disk->response($path, null, [
                'Cache-Control' => 'public, max-age=2592000',
            ]);
        }

        // Git-tracked copies live under public/storage_public; some hosts only
        // symlink root storage_public → storage/app/public, so fall back here.
        $fallback = public_path('storage_public/'.$path);
        abort_unless(is_file($fallback), 404);

        return response()->file($fallback, [
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }
}
