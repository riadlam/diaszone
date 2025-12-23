<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiamondPack;

class BloodStrikePackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packs = [
            ['diamonds' => 10000, 'bonus_diamonds' => 1600, 'price' => 67.81, 'original_price' => 83.72, 'sort_order' => 10, 'name' => '5000 + 800 Golds x2'],
            ['diamonds' => 15000, 'bonus_diamonds' => 2400, 'price' => 101.72, 'original_price' => 125.58, 'sort_order' => 20, 'name' => '5000 + 800 Golds x3'],
            ['diamonds' => 20000, 'bonus_diamonds' => 3200, 'price' => 135.63, 'original_price' => 167.44, 'sort_order' => 30, 'name' => '5000 + 800 Golds x4'],
            ['diamonds' => 25000, 'bonus_diamonds' => 4000, 'price' => 169.53, 'original_price' => 209.30, 'sort_order' => 40, 'name' => '5000 + 800 Golds x5'],
            ['diamonds' => 50000, 'bonus_diamonds' => 8000, 'price' => 339.07, 'original_price' => 418.60, 'sort_order' => 50, 'name' => '5000 + 800 Golds x10'],
            ['diamonds' => 100, 'bonus_diamonds' => 5, 'price' => 0.68, 'original_price' => 0.84, 'sort_order' => 60, 'name' => '100 + 5 Golds'],
            ['diamonds' => 300, 'bonus_diamonds' => 20, 'price' => 2.03, 'original_price' => 2.51, 'sort_order' => 70, 'name' => '300 + 20 Golds'],
            ['diamonds' => 500, 'bonus_diamonds' => 40, 'price' => 3.39, 'original_price' => 4.19, 'sort_order' => 80, 'name' => '500 + 40 Golds'],
            ['diamonds' => 1000, 'bonus_diamonds' => 100, 'price' => 6.78, 'original_price' => 8.37, 'sort_order' => 90, 'name' => '1000 + 100 Golds'],
            ['diamonds' => 2000, 'bonus_diamonds' => 260, 'price' => 13.56, 'original_price' => 16.74, 'sort_order' => 100, 'name' => '2000 + 260 Golds'],
            ['diamonds' => 5000, 'bonus_diamonds' => 800, 'price' => 33.91, 'original_price' => 41.86, 'sort_order' => 110, 'name' => '5000 + 800 Golds'],
            ['diamonds' => 96, 'bonus_diamonds' => 5, 'price' => 0.75, 'original_price' => 0.77, 'sort_order' => 120, 'name' => '96 + 5 Golds'],
            ['diamonds' => 490, 'bonus_diamonds' => 25, 'price' => 3.71, 'original_price' => 3.79, 'sort_order' => 130, 'name' => '490 + 25 Golds'],
            ['diamonds' => 981, 'bonus_diamonds' => 50, 'price' => 7.44, 'original_price' => 7.59, 'sort_order' => 140, 'name' => '981 + 50 Golds'],
            ['diamonds' => 2455, 'bonus_diamonds' => 123, 'price' => 18.59, 'original_price' => 18.97, 'sort_order' => 150, 'name' => '2455 + 123 Golds'],
            ['diamonds' => 4910, 'bonus_diamonds' => 246, 'price' => 37.17, 'original_price' => 37.17, 'sort_order' => 160, 'name' => '4910 + 246 Golds'],
        ];

        foreach ($packs as $pack) {
            $discount = 0;
            if ($pack['original_price'] > $pack['price']) {
                $discount = (($pack['original_price'] - $pack['price']) / $pack['original_price']) * 100;
            }

            DiamondPack::updateOrCreate(
                [
                    'game_type' => 'bloodstrike',
                    'diamonds' => $pack['diamonds'],
                ],
                [
                    'name' => $pack['name'] ?? null,
                    'bonus_diamonds' => $pack['bonus_diamonds'],
                    'price' => $pack['price'],
                    'discount_percentage' => round($discount, 2),
                    'is_active' => true,
                    'sort_order' => $pack['sort_order'],
                ]
            );
        }
    }
}
