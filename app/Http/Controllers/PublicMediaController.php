<?php

namespace App\Http\Controllers;

use App\Support\PublicMedia;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicMediaController extends Controller
{
    public function show(string $path): StreamedResponse
    {
        abort_unless(PublicMedia::isServable($path), 404);

        $path = PublicMedia::normalize($path);
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return $disk->response($path, null, [
            'Cache-Control' => 'public, max-age=2592000',
        ]);
    }
}
