<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DiamondPack;

class FreeFireDiamondPackSeeder extends Seeder
{
    /**
     * Pricing Formula:
     * 1. price_idr ÷ 16600 = USD cost
     * 2. USD cost × 250 = DZD base price
     * 3. DZD base price + 100 DZD margin = Final DZD price
     * 4. USD cost + $0.50 margin = USD selling price
     */
    
    const IDR_TO_USD = 16600;      // 1 USD = 16600 IDR
    const USD_TO_DZD = 250;        // 1 USD = 250 DZD
    const USD_MARGIN = 0.50;       // $0.50 USD profit margin
    const DZD_MARGIN = 100;        // 100 DZD profit margin

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // First, delete all existing freefire packs
        DiamondPack::where('game_type', 'freefire')->delete();
        
        // New Free Fire Global packs from provider API
        // Using the lowest price tier (3rd column) as price_idr
        $packs = [
            ['code' => 'FFGLOBAL110-S14', 'name' => '100 + 10 Diamonds', 'diamonds' => 100, 'bonus_diamonds' => 10, 'price_idr' => 15436],
            ['code' => 'FFGLOBAL100-S19', 'name' => '100 Diamonds', 'diamonds' => 100, 'bonus_diamonds' => 0, 'price_idr' => 16637],
            ['code' => 'FFGLOBAL231-S14', 'name' => '210 + 21 Diamonds', 'diamonds' => 210, 'bonus_diamonds' => 21, 'price_idr' => 30576],
            ['code' => 'FFGLOBALWMG-S19', 'name' => 'Weekly Membership Global', 'diamonds' => 0, 'bonus_diamonds' => 0, 'price_idr' => 32911],
            ['code' => 'FFGLOBAL310-S19', 'name' => '310 Diamonds', 'diamonds' => 310, 'bonus_diamonds' => 0, 'price_idr' => 50296],
            ['code' => 'FFGLOBAL583-S14', 'name' => '530 + 53 Diamonds', 'diamonds' => 530, 'bonus_diamonds' => 53, 'price_idr' => 76440],
            ['code' => 'FFGLOBAL520-S19', 'name' => '520 Diamonds', 'diamonds' => 520, 'bonus_diamonds' => 0, 'price_idr' => 77379],
            ['code' => 'FFGLOBAL1188-S14', 'name' => '1080 + 108 Diamonds', 'diamonds' => 1080, 'bonus_diamonds' => 108, 'price_idr' => 152880],
            ['code' => 'FFGLOBALMMG-S19', 'name' => 'Monthly Membership Global', 'diamonds' => 0, 'bonus_diamonds' => 0, 'price_idr' => 154370],
            ['code' => 'FFGLOBAL1060-S19', 'name' => '1060 Diamonds', 'diamonds' => 1060, 'bonus_diamonds' => 0, 'price_idr' => 154757],
            ['code' => 'FFGLOBAL2420-S14', 'name' => '2200 + 220 Diamonds', 'diamonds' => 2200, 'bonus_diamonds' => 220, 'price_idr' => 305760],
            ['code' => 'FFGLOBAL2180-S19', 'name' => '2180 Diamonds', 'diamonds' => 2180, 'bonus_diamonds' => 0, 'price_idr' => 315317],
            ['code' => 'FFGLOBAL6160-S19', 'name' => '6160 Diamonds', 'diamonds' => 6160, 'bonus_diamonds' => 0, 'price_idr' => 720680],
            ['code' => 'FFGLOBAL5600-S19', 'name' => '5600 Diamonds', 'diamonds' => 5600, 'bonus_diamonds' => 0, 'price_idr' => 754438],
        ];

        $sortOrder = 1;
        foreach ($packs as $pack) {
            // Calculate prices using the formula
            $usdCost = $pack['price_idr'] / self::IDR_TO_USD;
            $dzdBase = $usdCost * self::USD_TO_DZD;
            $dzdFinal = ceil($dzdBase + self::DZD_MARGIN); // Round up
            $usdFinal = round($usdCost + self::USD_MARGIN, 2);

            DiamondPack::create([
                'game_type' => 'freefire',
                'code' => $pack['code'],
                'name' => $pack['name'],
                'diamonds' => $pack['diamonds'],
                'bonus_diamonds' => $pack['bonus_diamonds'],
                'price' => $usdFinal,           // USD price with margin (legacy field)
                'price_usd' => $usdFinal,       // USD selling price
                'price_dzd' => $dzdFinal,       // DZD selling price
                'discount_percentage' => 0,
                'is_active' => true,
                'sort_order' => $sortOrder++,
            ]);
        }

        $this->command->info('Free Fire Global diamond packs seeded successfully! Total: ' . count($packs) . ' packs');
    }
}
