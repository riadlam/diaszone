<?php

namespace App\Support;

class FreeFirePackIcon
{
    public const FILENAME = 'freefire-diamonds.png';

    public static function path(): string
    {
        return 'images_homepage/'.self::FILENAME;
    }

    public static function url(): string
    {
        if (is_file(storage_path('app/public/'.self::path()))) {
            return PublicMedia::url(self::path());
        }

        return 'data:image/svg+xml;base64,'.base64_encode(self::fallbackSvg());
    }

    private static function fallbackSvg(): string
    {
        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">'
            .'<defs>'
            .'<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#1e3a5f"/><stop offset="1" stop-color="#0f172a"/></linearGradient>'
            .'<linearGradient id="gem" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#67e8f9"/><stop offset=".5" stop-color="#38bdf8"/><stop offset="1" stop-color="#6366f1"/></linearGradient>'
            .'<linearGradient id="crate" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#64748b"/><stop offset="1" stop-color="#334155"/></linearGradient>'
            .'</defs>'
            .'<rect width="128" height="128" rx="26" fill="url(#bg)"/>'
            .'<path d="M24 72h80v24a8 8 0 0 1-8 8H32a8 8 0 0 1-8-8z" fill="url(#crate)"/>'
            .'<path d="M20 64a12 12 0 0 1 12-12h64a12 12 0 0 1 12 12v12H20z" fill="#fbbf24"/>'
            .'<path d="m64 42 18 20-18 24-18-24z" fill="url(#gem)" stroke="#fff" stroke-width="3"/>'
            .'</svg>';
    }
}
