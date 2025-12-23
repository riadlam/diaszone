<?php
// Global helpers - intentionally in the application's root app/ folder.

if (!function_exists('storage_public_url')) {
    /**
     * Return the canonical storage_public URL for an asset/path.
     */
    function storage_public_url(?string $path = null): string
    {
        if (empty($path)) return '/storage_public';
        $clean = ltrim($path, '/');
        return '/' . trim('storage_public/' . $clean, '/');
    }
}
