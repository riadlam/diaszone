<?php

namespace App\Support;

use Illuminate\Support\Str;

class PublicMedia
{
    /**
     * Folders on the "public" disk that may be served over HTTP.
     * Keep receipts / private uploads out of this list.
     */
    public const ALLOWED_DIRECTORIES = [
        'wheel-reward-icons',
        'event-backgrounds',
        'images_homepage',
        'flash-sale-images',
        'hero-slides',
        'game-content-images',
        'top4gamers_images',
        'seller-logos',
        'seller-banners',
    ];

    /**
     * Build a URL that streams a file from the "public" disk through the app,
     * so uploads keep working on hosts where the public/storage symlink is
     * missing or refused by the web server (403 on /storage/...).
     */
    public static function url(string $path): string
    {
        $relative = static::normalize($path);

        if ($relative === '') {
            return url('/media');
        }

        return url('/media/'.$relative);
    }

    public static function normalize(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));

        if ($path === '') {
            return '';
        }

        // Absolute URL → path only
        if (str_contains($path, '://')) {
            $parsed = parse_url($path, PHP_URL_PATH);
            $path = is_string($parsed) ? $parsed : $path;
        }

        $path = ltrim($path, '/');

        foreach (['storage/', 'storage_public/', 'media/', 'public/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        return ltrim($path, '/');
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
