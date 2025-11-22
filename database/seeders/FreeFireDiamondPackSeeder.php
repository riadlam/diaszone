<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiamondPack;
use Illuminate\Support\Facades\DB;

class FreeFireDiamondPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packs = [
            ['diamonds' => 100, 'bonus_diamonds' => 0, 'price' => 0.98, 'original_price' => 1.05, 'sort_order' => 1],
            ['diamonds' => 210, 'bonus_diamonds' => 0, 'price' => 1.95, 'original_price' => 2.10, 'sort_order' => 2],
            ['diamonds' => 310, 'bonus_diamonds' => 0, 'price' => 2.62, 'original_price' => 3.28, 'sort_order' => 3],
            ['diamonds' => 520, 'bonus_diamonds' => 0, 'price' => 4.03, 'original_price' => 5.04, 'sort_order' => 4],
            ['diamonds' => 530, 'bonus_diamonds' => 0, 'price' => 4.88, 'original_price' => 5.25, 'sort_order' => 5],
            ['diamonds' => 1060, 'bonus_diamonds' => 0, 'price' => 8.03, 'original_price' => 10.16, 'sort_order' => 6],
            ['diamonds' => 1080, 'bonus_diamonds' => 0, 'price' => 9.77, 'original_price' => 10.50, 'sort_order' => 7],
            ['diamonds' => 2180, 'bonus_diamonds' => 0, 'price' => 16.44, 'original_price' => 21.08, 'sort_order' => 8],
            ['diamonds' => 2200, 'bonus_diamonds' => 0, 'price' => 19.53, 'original_price' => 21.00, 'sort_order' => 9],
            ['diamonds' => 5600, 'bonus_diamonds' => 0, 'price' => 39.34, 'original_price' => 50.43, 'sort_order' => 10],
            ['diamonds' => 11500, 'bonus_diamonds' => 0, 'price' => 79.69, 'original_price' => 79.69, 'sort_order' => 11],
        ];

        foreach ($packs as $pack) {
            $discount = 0;
            if ($pack['original_price'] > $pack['price']) {
                $discount = (($pack['original_price'] - $pack['price']) / $pack['original_price']) * 100;
            }

            DiamondPack::updateOrCreate(
                [
                    'game_type' => 'freefire',
                    'diamonds' => $pack['diamonds'],
                ],
                [
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
