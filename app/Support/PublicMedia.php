<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicMedia
{
    /**
     * Folders on the "public" disk that may be served over HTTP.
     */
    public const ALLOWED_DIRECTORIES = [
        'wheel-reward-icons',
        'event-backgrounds',
        'images_homepage',
        'flash-sale-images',
    ];

    /**
     * Build a URL that streams a file from the "public" disk through the app,
     * so uploads keep working on hosts where the public/storage symlink is
     * missing or refused by the web server.
     */
    public static function url(string $path): string
    {
        return url('/media/'.ltrim(static::normalize($path), '/'));
    }

    public static function normalize(string $path): string
    {
        return ltrim(str_replace('\\', '/', $path), '/');
    }

    public static function isServable(string $path): bool
    {
        $path = static::normalize($path);

        if ($path === '' || str_contains($path, '..')) {
            return false;
        }

        return in_array(Str::before($path, '/'), static::ALLOWED_DIRECTORIES, true);
    }
}
