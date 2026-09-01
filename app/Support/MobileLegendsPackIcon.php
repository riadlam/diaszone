<?php

namespace App\Support;

use App\Models\DiamondPack;

class MobileLegendsPackIcon
{
    public static function filename(DiamondPack $pack): string
    {
        $code = strtolower((string) ($pack->code ?? ''));
        if (in_array($code, ['startlight-1', 'starlight-1'], true)) {
            return 'startlight-1.png';
        }

        if ($code === 'starlightplus') {
            return 'starlightplus.png';
        }

        if ($code === 'mpic') {
            return 'mpic.png';
        }

        if ($code === 'superpass') {
            return 'superpass.png';
        }

        if (stripos($pack->name, 'Weekly Diamond Pass') !== false
            || stripos($pack->name, 'Event Topup') !== false) {
            return 'weeklymlbb.webp';
        }

        if (stripos($pack->name, 'Twilight Pass') !== false) {
            return 'twlilightpass.jpg';
        }

        return match (true) {
            (int) $pack->diamonds >= 2000 => 'diasbigbig.webp',
            (int) $pack->diamonds >= 500 => 'diaslarge.webp',
            (int) $pack->diamonds >= 100 => 'diasmid.webp',
            default => 'diaslow.webp',
        };
    }

    public static function path(DiamondPack $pack): string
    {
        return 'images_homepage/'.self::filename($pack);
    }

    public static function url(DiamondPack $pack): string
    {
        $path = self::path($pack);

        if (is_file(storage_path('app/public/'.$path))) {
            return PublicMedia::url($path);
        }

        return 'data:image/svg+xml;base64,'.base64_encode(self::fallbackSvg(self::filename($pack)));
    }

    private static function fallbackSvg(string $filename): string
    {
        $art = match ($filename) {
            'weeklymlbb.webp' => '
                <rect x="19" y="23" width="90" height="82" rx="18" fill="url(#card)"/>
                <path d="M19 47h90" stroke="#fff" stroke-width="8" opacity=".85"/>
                <path d="M42 17v19M86 17v19" stroke="#fff" stroke-width="9" stroke-linecap="round"/>
                <path d="m64 55 17 18-17 23-17-23z" fill="url(#gem)" stroke="#fff" stroke-width="3"/>
            ',
            'startlight-1.png' => '
                <rect x="18" y="18" width="92" height="92" rx="22" fill="url(#card)"/>
                <path d="M64 28l8 18 19 2-14 13 4 19-17-10-17 10 4-19-14-13 19-2z" fill="#fde68a" stroke="#fff" stroke-width="2"/>
            ',
            'starlightplus.png' => '
                <rect x="18" y="18" width="92" height="92" rx="22" fill="url(#card)"/>
                <path d="M64 28l8 18 19 2-14 13 4 19-17-10-17 10 4-19-14-13 19-2z" fill="#fde68a" stroke="#fff" stroke-width="2"/>
                <text x="64" y="108" text-anchor="middle" font-size="10" font-weight="700" fill="#fde68a">PLUS</text>
            ',
            'mpic.png' => '
                <rect x="18" y="18" width="92" height="92" rx="22" fill="url(#card)"/>
                <path d="M64 28l8 18 19 2-14 13 4 19-17-10-17 10 4-19-14-13 19-2z" fill="url(#gem)" stroke="#fff" stroke-width="2"/>
                <text x="64" y="108" text-anchor="middle" font-size="9" font-weight="700" fill="#fff">EPIC</text>
            ',
            'superpass.png' => '
                <rect x="20" y="34" width="88" height="60" rx="10" fill="#fbbf24" stroke="#fff" stroke-width="3"/>
                <path d="M20 52h88" stroke="#fff" stroke-width="4" opacity=".8"/>
                <path d="m64 48 14 15-14 19-14-19z" fill="url(#gem)" stroke="#fff" stroke-width="2"/>
            ',
            'twlilightpass.jpg' => '
                <rect x="18" y="18" width="92" height="92" rx="22" fill="url(#card)"/>
                <path d="M84 40c-25 3-34 35-13 49-27 2-43-29-27-49 10-13 28-16 40 0z" fill="#fde68a"/>
                <path d="m87 68 6 7 9-3-5 8 5 8-9-3-6 7 1-9-8-3 8-3z" fill="#fff"/>
            ',
            'diasbigbig.webp' => '
                <path d="M17 50h94v50a15 15 0 0 1-15 15H32a15 15 0 0 1-15-15z" fill="url(#chest)"/>
                <path d="M12 45a17 17 0 0 1 17-17h70a17 17 0 0 1 17 17v15H12z" fill="#fbbf24"/>
                <path d="M59 45h14v30H59z" fill="#fef3c7"/>
                <path d="m66 65 18 16-18 23-18-23z" fill="url(#gem)" stroke="#fff" stroke-width="3"/>
            ',
            'diaslarge.webp' => '
                <path d="M21 52h86v46a14 14 0 0 1-14 14H35a14 14 0 0 1-14-14z" fill="url(#chest)"/>
                <path d="M17 47a15 15 0 0 1 15-15h64a15 15 0 0 1 15 15v13H17z" fill="#fbbf24"/>
                <path d="m64 59 16 17-16 22-16-22z" fill="url(#gem)" stroke="#fff" stroke-width="3"/>
            ',
            'diasmid.webp' => '
                <path d="m64 16 24 26-24 33-24-33z" fill="url(#gem)" stroke="#fff" stroke-width="4"/>
                <path d="m35 56 20 21-20 27-20-27zM93 56l20 21-20 27-20-27z" fill="url(#gem)" stroke="#fff" stroke-width="4"/>
            ',
            default => '
                <path d="m64 17 34 36-34 49-34-49z" fill="url(#gem)" stroke="#fff" stroke-width="5"/>
                <path d="M31 53h66M64 18 49 53l15 49 15-49z" fill="none" stroke="#fff" stroke-width="3" opacity=".7"/>
            ',
        };

        return '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 128 128">'
            .'<defs>'
            .'<linearGradient id="bg" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#ede9fe"/><stop offset="1" stop-color="#ddd6fe"/></linearGradient>'
            .'<linearGradient id="gem" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#67e8f9"/><stop offset=".5" stop-color="#38bdf8"/><stop offset="1" stop-color="#6366f1"/></linearGradient>'
            .'<linearGradient id="card" x1="0" y1="0" x2="1" y2="1"><stop stop-color="#8b5cf6"/><stop offset="1" stop-color="#4f46e5"/></linearGradient>'
            .'<linearGradient id="chest" x1="0" y1="0" x2="0" y2="1"><stop stop-color="#f59e0b"/><stop offset="1" stop-color="#b45309"/></linearGradient>'
            .'</defs>'
            .'<rect width="128" height="128" rx="26" fill="url(#bg)"/>'
            .$art
            .'</svg>';
    }
}
