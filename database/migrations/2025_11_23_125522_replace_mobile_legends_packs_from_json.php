<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\DiamondPack;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Read JSON file
        $jsonPath = app_path('Http/Controllers/mlbb_list.json');
        if (!file_exists($jsonPath)) {
            throw new \Exception("JSON file not found: {$jsonPath}. Please make sure mlbb_list.json is saved in app/Http/Controllers/");
        }

        $jsonContent = file_get_contents($jsonPath);
        
        if (empty($jsonContent)) {
            throw new \Exception("JSON file is empty. Please save the mlbb_list.json file first.");
        }
        
        // Remove BOM if present
        if (substr($jsonContent, 0, 3) === "\xEF\xBB\xBF") {
            $jsonContent = substr($jsonContent, 3);
        }
        
        $data = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decode error: " . json_last_error_msg());
        }

        if (!isset($data['data']) || !is_array($data['data'])) {
            throw new \Exception("Invalid JSON structure. Expected 'data' key with array value. Got: " . print_r(array_keys($data ?? []), true));
        }

        // Delete all existing Mobile Legends packs
        DiamondPack::where('game_type', 'mobilelegends')->delete();

        $sortOrder = 1;
        foreach ($data['data'] as $item) {
            // Skip if required fields are missing
            if (!isset($item['name']) || !isset($item['code']) || !isset($item['price'])) {
                continue;
            }

            // Parse diamonds and bonus_diamonds from name
            $parsed = $this->parsePackName($item['name']);
            
            // Use special price EXACTLY as it is in JSON (no conversion, as integer)
            if (!isset($item['price']['special'])) {
                continue; // Skip if special price is not available
            }
            
            $priceIdr = (int) $item['price']['special'];
            
            if ($priceIdr <= 0) {
                continue; // Skip if no valid price
            }

            // Exchange rates
            // 1 USDT = 16,600 IDR
            // 1 USDT = 250 DZD
            $usdtRateIdr = 16600;
            $usdtRateDzd = 250;

            // Calculate price_usd: Convert IDR to USDT, then add 0.8 USD
            // USDT = Price in IDR / Rate of 1 USDT in IDR
            $priceUsdt = $priceIdr / $usdtRateIdr;
            $priceUsd = round($priceUsdt + 0.8, 2);

            // Calculate price_dzd: Convert IDR to DZD via USDT, then add 100 DZD
            // DZD = (Price in IDR / Rate of 1 USDT in IDR) × Rate of 1 USDT in DZD
            // Round to whole number (no decimals)
            $priceDzd = (int) round(($priceIdr / $usdtRateIdr) * $usdtRateDzd + 100);

            // Calculate discount percentage (if we had original price, but we don't, so set to 0)
            $discountPercentage = 0;

            // Only insert if status is available
            if (($item['status'] ?? 'available') === 'available') {
                DiamondPack::create([
                    'game_type' => 'mobilelegends',
                    'name' => $item['name'],
                    'code' => $item['code'],
                    'diamonds' => $parsed['diamonds'],
                    'bonus_diamonds' => $parsed['bonus_diamonds'],
                    'price' => $priceIdr,
                    'price_usd' => $priceUsd,
                    'price_dzd' => $priceDzd,
                    'discount_percentage' => $discountPercentage,
                    'is_active' => true,
                    'sort_order' => $sortOrder++,
                ]);
            }
        }
    }

    /**
     * Parse pack name to extract diamonds and bonus_diamonds
     */
    private function parsePackName(string $name): array
    {
        $diamonds = 0;
        $bonusDiamonds = 0;

        // Pattern 1: "55 Diamonds (50 + 5 Bonus)" or "86 Diamonds (78 + 8 Bonus)"
        // Extract base diamonds and bonus from parentheses
        if (preg_match('/\((\d+)\s*\+\s*(\d+)\s*Bonus\)/', $name, $matches)) {
            $diamonds = (int) $matches[1];
            $bonusDiamonds = (int) $matches[2];
        }
        // Pattern 2: "1x Weekly Diamond Pass (Event Topup 100)" - special case
        elseif (preg_match('/Event Topup (\d+)/', $name, $matches)) {
            $diamonds = (int) $matches[1];
            $bonusDiamonds = 0;
        }
        // Pattern 3: "Twilight Pass" - this is a special pass, set to 0 for now
        // You may need to manually adjust this based on actual value
        elseif (stripos($name, 'Twilight Pass') !== false || stripos($name, 'Pass') !== false) {
            // For passes, we'll set a default or you can manually update later
            $diamonds = 0;
            $bonusDiamonds = 0;
        }
        // Pattern 4: Fallback - try to extract any number before "Diamonds"
        elseif (preg_match('/(\d+)\s*Diamonds?/', $name, $matches)) {
            $totalDiamonds = (int) $matches[1];
            // If we couldn't parse the breakdown, assume all are base diamonds
            $diamonds = $totalDiamonds;
            $bonusDiamonds = 0;
        }

        return [
            'diamonds' => $diamonds,
            'bonus_diamonds' => $bonusDiamonds,
        ];
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration cannot be easily reversed
        // You would need to restore from a backup or re-seed with original data
    }
};
