<?php

namespace Database\Seeders;

use App\Models\DiamondPack;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AddBasePriceToDiamondPacksSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // For each diamond pack set base_price_dzd = price_dzd - 100
        DiamondPack::query()->get()->each(function (DiamondPack $pack) {
            $price = (float) $pack->price_dzd;
            // subtract 100 DZD, but ensure not negative
            $base = max(0, round($price - 100, 2));
            $pack->base_price_dzd = $base;
            $pack->save();
        });
    }
}
