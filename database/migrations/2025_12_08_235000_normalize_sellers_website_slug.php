<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Normalize the sellers.website_url column to be a slug (last path segment only)
        $sellers = DB::table('sellers')->whereNotNull('website_url')->get();
        foreach ($sellers as $s) {
            $raw = trim($s->website_url);
            if ($raw === '') continue;

            // If it contains a protocol or slashes, extract last path segment
            $slug = $raw;
            if (str_contains($raw, '://') || str_contains($raw, '/')) {
                $parts = parse_url($raw);
                $path = $parts['path'] ?? '';
                $segments = array_values(array_filter(explode('/', $path)));
                $slug = end($segments) ?: null;
            }

            if (!$slug) {
                // if we couldn't derive a slug, fallback to username or null
                continue;
            }

            $slug = strtolower(preg_replace('/[^a-z0-9_-]+/', '', $slug));

            if ($slug) {
                DB::table('sellers')->where('id', $s->id)->update(['website_url' => $slug]);
            }
        }
    }

    public function down()
    {
        // no-op, we won't reverse normalization
    }
};
