<?php

namespace Database\Seeders;

use App\Models\VipResellerCategory;
use App\Models\VipResellerPack;
use Illuminate\Database\Seeder;

class VipResellerNetflixSeeder extends Seeder
{
    public function run(): void
    {
        $category = VipResellerCategory::query()->updateOrCreate(
            ['slug' => 'netflix'],
            [
                'name' => 'Netflix Premium',
                'filter_game' => 'Netflix',
                'product_url' => '/digital/netflix',
                'required_fields' => ['email'],
                'description' => 'Netflix Premium profile sharing via VIP Reseller.',
                'is_active' => true,
                'is_topseller' => false,
                'is_newproduct' => true,
                'sort_order' => 1,
            ]
        );

        // Prices from docs.txt: Member / Reseller / H2H → basic / premium / special
        $packs = [
            [
                'code' => 'NTFLXSHARE30DGAR14D-S1',
                'name' => 'Profile Sharing 30 Hari — Garansi 14 Hari',
                'description' => 'Profile Sharing 30 Hari [ 1 Profile ] [ VIA LINK LOGIN - KHUSUS PC/LAPTOP/TV ] [ GARANSI 14 HARI ]',
                'price_special' => 12750,
                'sort_order' => 1,
            ],
            [
                'code' => 'NTFLXSHARE30DGAR28D-S1',
                'name' => 'Profile Sharing 30 Hari — Garansi 28 Hari',
                'description' => 'Profile Sharing 30 Hari [ 1 Profile ] [ VIA LINK LOGIN - KHUSUS PC/LAPTOP/TV ] [ GARANSI 28 HARI ]',
                'price_special' => 18750,
                'sort_order' => 2,
            ],
        ];

        foreach ($packs as $pack) {
            VipResellerPack::query()->updateOrCreate(
                ['code' => $pack['code']],
                [
                    'category_id' => $category->id,
                    'name' => $pack['name'],
                    'description' => $pack['description'],
                    'product_url' => '/digital/netflix',
                    'price_special' => $pack['price_special'],
                    'price_dzd' => 0,
                    'base_price_dzd' => null,
                    'price_usd' => null,
                    'discount_percentage' => 0,
                    'provider_status' => 'available',
                    'is_active' => true,
                    'sort_order' => $pack['sort_order'],
                ]
            );
        }
    }
}
