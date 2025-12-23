<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiamondPack;

class PubgMobileUcPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packs = [
            ['diamonds' => 60, 'bonus_diamonds' => 0, 'price' => 0.88, 'original_price' => 0.99, 'sort_order' => 10],
            ['diamonds' => 300, 'bonus_diamonds' => 25, 'price' => 4.44, 'original_price' => 4.99, 'sort_order' => 20],
            ['diamonds' => 600, 'bonus_diamonds' => 60, 'price' => 8.89, 'original_price' => 9.99, 'sort_order' => 30],
            ['diamonds' => 1500, 'bonus_diamonds' => 300, 'price' => 22.24, 'original_price' => 24.99, 'sort_order' => 40],
            ['diamonds' => 3000, 'bonus_diamonds' => 850, 'price' => 44.49, 'original_price' => 49.99, 'sort_order' => 50],
            ['diamonds' => 6000, 'bonus_diamonds' => 2100, 'price' => 88.99, 'original_price' => 99.99, 'sort_order' => 60],
            ['diamonds' => 12000, 'bonus_diamonds' => 4200, 'price' => 177.99, 'original_price' => 199.99, 'sort_order' => 70],
            ['diamonds' => 18000, 'bonus_diamonds' => 6300, 'price' => 266.99, 'original_price' => 299.99, 'sort_order' => 80],
            ['diamonds' => 24000, 'bonus_diamonds' => 8400, 'price' => 355.99, 'original_price' => 399.99, 'sort_order' => 90],
            ['diamonds' => 30000, 'bonus_diamonds' => 10500, 'price' => 444.99, 'original_price' => 499.99, 'sort_order' => 100],
        ];

        foreach ($packs as $pack) {
            $discount = 0;
            if ($pack['original_price'] > $pack['price']) {
                $discount = (($pack['original_price'] - $pack['price']) / $pack['original_price']) * 100;
            }

            DiamondPack::updateOrCreate(
                [
                    'game_type' => 'pubgmobile',
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
