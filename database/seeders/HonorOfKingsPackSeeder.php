<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiamondPack;

class HonorOfKingsPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packs = [
            ['diamonds' => 1, 'bonus_diamonds' => 0, 'price' => 0.96, 'original_price' => 0.99, 'sort_order' => 10, 'name' => 'Weekly Card'],
            ['diamonds' => 2, 'bonus_diamonds' => 0, 'price' => 2.99, 'original_price' => 2.99, 'sort_order' => 20, 'name' => 'Weekly Card Plus'],
            ['diamonds' => 16, 'bonus_diamonds' => 0, 'price' => 0.20, 'original_price' => 0.20, 'sort_order' => 30, 'name' => '16 Tokens'],
            ['diamonds' => 80, 'bonus_diamonds' => 0, 'price' => 0.85, 'original_price' => 0.99, 'sort_order' => 40, 'name' => '80 Tokens'],
            ['diamonds' => 240, 'bonus_diamonds' => 0, 'price' => 2.57, 'original_price' => 2.99, 'sort_order' => 50, 'name' => '240 Tokens'],
            ['diamonds' => 400, 'bonus_diamonds' => 0, 'price' => 4.29, 'original_price' => 4.99, 'sort_order' => 60, 'name' => '400 Tokens'],
            ['diamonds' => 560, 'bonus_diamonds' => 0, 'price' => 6.01, 'original_price' => 6.99, 'sort_order' => 70, 'name' => '560 Tokens'],
            ['diamonds' => 800, 'bonus_diamonds' => 30, 'price' => 8.59, 'original_price' => 9.99, 'sort_order' => 80, 'name' => '800 + 30 Tokens'],
            ['diamonds' => 1200, 'bonus_diamonds' => 45, 'price' => 12.89, 'original_price' => 14.99, 'sort_order' => 90, 'name' => '1200 + 45 Tokens'],
            ['diamonds' => 2400, 'bonus_diamonds' => 108, 'price' => 25.79, 'original_price' => 29.99, 'sort_order' => 100, 'name' => '2400 + 108 Tokens'],
            ['diamonds' => 4000, 'bonus_diamonds' => 180, 'price' => 42.99, 'original_price' => 49.99, 'sort_order' => 110, 'name' => '4000 + 180 Tokens'],
            ['diamonds' => 8000, 'bonus_diamonds' => 360, 'price' => 85.99, 'original_price' => 99.99, 'sort_order' => 120, 'name' => '8000 + 360 Tokens'],
            ['diamonds' => 0, 'bonus_diamonds' => 0, 'price' => 0.32, 'original_price' => 0.36, 'sort_order' => 130, 'name' => 'Double Token Lucky Bag'],
            ['diamonds' => 0, 'bonus_diamonds' => 0, 'price' => 0.32, 'original_price' => 0.36, 'sort_order' => 140, 'name' => 'Standard Purchase Rebate Pack'],
            ['diamonds' => 0, 'bonus_diamonds' => 0, 'price' => 1.18, 'original_price' => 1.18, 'sort_order' => 150, 'name' => 'Premium Purchase Rebate Pack'],
            ['diamonds' => 0, 'bonus_diamonds' => 0, 'price' => 0.32, 'original_price' => 0.32, 'sort_order' => 160, 'name' => 'Honor Point Value Pack'],
        ];

        foreach ($packs as $pack) {
            $discount = 0;
            if ($pack['original_price'] > $pack['price']) {
                $discount = (($pack['original_price'] - $pack['price']) / $pack['original_price']) * 100;
            }

            DiamondPack::updateOrCreate(
                [
                    'game_type' => 'honorofkings',
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
