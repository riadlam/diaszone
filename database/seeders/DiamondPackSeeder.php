<?php

namespace Database\Seeders;

use App\Models\DiamondPack;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DiamondPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $packs = [
            ['diamonds' => 13, 'bonus_diamonds' => 1, 'price' => 0.28, 'discount_percentage' => 17.0, 'sort_order' => 1],
            ['diamonds' => 20, 'bonus_diamonds' => 2, 'price' => 0.38, 'discount_percentage' => 7.0, 'sort_order' => 2],
            ['diamonds' => 38, 'bonus_diamonds' => 4, 'price' => 0.85, 'discount_percentage' => 18.0, 'sort_order' => 3],
            ['diamonds' => 50, 'bonus_diamonds' => 5, 'price' => 1.11, 'discount_percentage' => 17.0, 'sort_order' => 4],
            ['diamonds' => 51, 'bonus_diamonds' => 5, 'price' => 0.95, 'discount_percentage' => 18.0, 'sort_order' => 5],
            ['diamonds' => 64, 'bonus_diamonds' => 6, 'price' => 1.41, 'discount_percentage' => 18.0, 'sort_order' => 6],
            ['diamonds' => 78, 'bonus_diamonds' => 8, 'price' => 1.33, 'discount_percentage' => 11.0, 'sort_order' => 7],
            ['diamonds' => 102, 'bonus_diamonds' => 10, 'price' => 1.90, 'discount_percentage' => 18.0, 'sort_order' => 8],
            ['diamonds' => 127, 'bonus_diamonds' => 13, 'price' => 2.82, 'discount_percentage' => 18.0, 'sort_order' => 9],
            ['diamonds' => 150, 'bonus_diamonds' => 15, 'price' => 3.30, 'discount_percentage' => 17.0, 'sort_order' => 10],
            ['diamonds' => 153, 'bonus_diamonds' => 15, 'price' => 2.85, 'discount_percentage' => 18.0, 'sort_order' => 11],
            ['diamonds' => 156, 'bonus_diamonds' => 16, 'price' => 2.66, 'discount_percentage' => 11.0, 'sort_order' => 12],
            ['diamonds' => 202, 'bonus_diamonds' => 22, 'price' => 3.80, 'discount_percentage' => 18.0, 'sort_order' => 13],
            ['diamonds' => 234, 'bonus_diamonds' => 23, 'price' => 3.98, 'discount_percentage' => 11.0, 'sort_order' => 14],
            ['diamonds' => 250, 'bonus_diamonds' => 25, 'price' => 5.49, 'discount_percentage' => 17.0, 'sort_order' => 15],
            ['diamonds' => 254, 'bonus_diamonds' => 30, 'price' => 5.66, 'discount_percentage' => 18.0, 'sort_order' => 16],
            ['diamonds' => 303, 'bonus_diamonds' => 33, 'price' => 5.71, 'discount_percentage' => 18.0, 'sort_order' => 17],
            ['diamonds' => 310, 'bonus_diamonds' => 34, 'price' => 5.32, 'discount_percentage' => 6.0, 'sort_order' => 18],
            ['diamonds' => 317, 'bonus_diamonds' => 38, 'price' => 7.08, 'discount_percentage' => 18.0, 'sort_order' => 19],
            ['diamonds' => 383, 'bonus_diamonds' => 46, 'price' => 8.49, 'discount_percentage' => 18.0, 'sort_order' => 20],
            ['diamonds' => 500, 'bonus_diamonds' => 65, 'price' => 11.19, 'discount_percentage' => 17.0, 'sort_order' => 21],
            ['diamonds' => 504, 'bonus_diamonds' => 66, 'price' => 9.51, 'discount_percentage' => 18.0, 'sort_order' => 22],
            ['diamonds' => 625, 'bonus_diamonds' => 81, 'price' => 10.64, 'discount_percentage' => 11.0, 'sort_order' => 23],
            ['diamonds' => 633, 'bonus_diamonds' => 83, 'price' => 14.16, 'discount_percentage' => 18.0, 'sort_order' => 24],
            ['diamonds' => 940, 'bonus_diamonds' => 144, 'price' => 21.17, 'discount_percentage' => 17.0, 'sort_order' => 25],
            ['diamonds' => 1007, 'bonus_diamonds' => 156, 'price' => 19.02, 'discount_percentage' => 18.0, 'sort_order' => 26],
            ['diamonds' => 1160, 'bonus_diamonds' => 186, 'price' => 19.95, 'discount_percentage' => 8.0, 'sort_order' => 27],
            ['diamonds' => 1252, 'bonus_diamonds' => 194, 'price' => 28.24, 'discount_percentage' => 18.0, 'sort_order' => 28],
            ['diamonds' => 1547, 'bonus_diamonds' => 278, 'price' => 26.60, 'discount_percentage' => 8.0, 'sort_order' => 29],
            ['diamonds' => 1860, 'bonus_diamonds' => 335, 'price' => 31.92, 'discount_percentage' => 11.0, 'sort_order' => 30],
            ['diamonds' => 2015, 'bonus_diamonds' => 383, 'price' => 38.04, 'discount_percentage' => 18.0, 'sort_order' => 31],
            ['diamonds' => 2501, 'bonus_diamonds' => 475, 'price' => 56.64, 'discount_percentage' => 18.0, 'sort_order' => 32],
            ['diamonds' => 3099, 'bonus_diamonds' => 589, 'price' => 53.19, 'discount_percentage' => 11.0, 'sort_order' => 33],
            ['diamonds' => 4649, 'bonus_diamonds' => 883, 'price' => 79.79, 'discount_percentage' => 11.0, 'sort_order' => 34],
            ['diamonds' => 5035, 'bonus_diamonds' => 1007, 'price' => 95.11, 'discount_percentage' => 18.0, 'sort_order' => 35],
            ['diamonds' => 6252, 'bonus_diamonds' => 1250, 'price' => 141.32, 'discount_percentage' => 18.0, 'sort_order' => 36],
            ['diamonds' => 7740, 'bonus_diamonds' => 1548, 'price' => 132.99, 'discount_percentage' => 11.0, 'sort_order' => 37],
        ];

        foreach ($packs as $pack) {
            DiamondPack::updateOrCreate(
                [
                    'diamonds' => $pack['diamonds'],
                    'bonus_diamonds' => $pack['bonus_diamonds'],
                ],
                $pack
            );
        }
    }
}
