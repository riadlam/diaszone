<?php

namespace App\Console\Commands;

use App\Models\DiamondPack;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SyncDigiflazzPrices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'digiflazz:sync-prices';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync diamond pack prices from Digiflazz API (mobilelegends, freefire, pubg_mobile, genshin_impact, bloodstrike, honorofkings, punishinggrayraven, and wutheringwaves)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $step = function (string $msg) {
            $this->info($msg);
            // File only — never call Laravel Log (can hang on locked laravel.log)
            \App\Support\SafeLog::file('digiflazz-sync.log', $msg);
        };

        $step('Starting Digiflazz price sync...');

        try {
            // Get Digiflazz credentials
            // Note: Digiflazz uses 'sign' as the API key name in config
            $username = config('services.digiflazz.username') ?: env('DIGIFLAZZ_USERNAME');
            $apiKey = config('services.digiflazz.sign')
                ?: config('services.digiflazz.api_key')
                ?: env('DIGIFLAZZ_SIGN')
                ?: env('DIGIFLAZZ_API_KEY');
            $baseUrl = config('services.digiflazz.base_url', 'https://api.digiflazz.com/v1');

            $step('Credentials check: username_set='.(!empty($username) ? 'yes' : 'no').' api_key_set='.(!empty($apiKey) ? 'yes' : 'no').' base_url='.$baseUrl);

            if (empty($username) || empty($apiKey)) {
                $this->error('Digiflazz credentials not configured');
                \App\Support\SafeLog::error('Digiflazz sync: Missing credentials', [
                    'username_set' => !empty($username),
                    'api_key_set' => !empty($apiKey),
                ]);
                return 1;
            }

            // Generate signature for price-list API (md5 of username + apiKey + "pricelist")
            // Note: Digiflazz uses "pricelist" as the constant string for price-list endpoint
            $sign = md5($username . $apiKey . 'pricelist');

            $step('Calling Digiflazz price-list API...');

            // Single API call to fetch all Games category products
            // We'll filter to only process products that match our packs for mobilelegends, freefire, pubg_mobile, genshin_impact, bloodstrike, honorofkings, punishinggrayraven, and wutheringwaves
            try {
                $response = Http::connectTimeout(8)
                    ->timeout(25)
                    ->post($baseUrl . '/price-list', [
                        'cmd' => 'prepaid',
                        'username' => $username,
                        'sign' => $sign,
                        'category' => 'Games',
                    ]);
            } catch (\Throwable $httpEx) {
                $this->error('Digiflazz HTTP error: '.$httpEx->getMessage());
                \App\Support\SafeLog::error('Digiflazz sync: HTTP exception', [
                    'error' => $httpEx->getMessage(),
                    'exception' => get_class($httpEx),
                ]);
                return 1;
            }

            $step('Digiflazz HTTP status='.$response->status());

            if (!$response->successful()) {
                $this->error('Failed to fetch products from Digiflazz');
                \App\Support\SafeLog::error('Digiflazz sync: API call failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return 1;
            }

            $data = $response->json();
            $products = $data['data'] ?? [];

            if (empty($products)) {
                $this->warn('No products returned from Digiflazz API');
                \App\Support\SafeLog::warning('Digiflazz sync: Empty product list');
                return 0;
            }

            $this->info('Fetched ' . count($products) . ' products from Digiflazz (will filter to match our packs)');

            // Filter active products
            $activeProducts = array_filter($products, function ($product) {
                return ($product['buyer_product_status'] ?? false) === true
                    && ($product['seller_product_status'] ?? false) === true;
            });

            $this->info('Found ' . count($activeProducts) . ' active products');

            // Group products by normalized product name and track all SKU variants
            $groupedProducts = [];
            foreach ($activeProducts as $product) {
                $normalizedName = $this->normalizeProductName($product['product_name']);
                $buyerSkuCode = $product['buyer_sku_code'];
                $price = (int)($product['price'] ?? 0);

                // Initialize group if not exists
                if (!isset($groupedProducts[$normalizedName])) {
                    $groupedProducts[$normalizedName] = [
                        'product_name' => $product['product_name'],
                        'buyer_sku_codes' => [$buyerSkuCode], // Track all SKU variants
                        'cheapest_sku_code' => $buyerSkuCode,
                        'cheapest_price' => $price,
                        'cheapest_seller' => $product['seller_name'] ?? '',
                    ];
                } else {
                    // Add this SKU to the list of variants
                    $groupedProducts[$normalizedName]['buyer_sku_codes'][] = $buyerSkuCode;
                    
                    // Update cheapest if this one is cheaper
                    if ($price < $groupedProducts[$normalizedName]['cheapest_price']) {
                        $groupedProducts[$normalizedName]['cheapest_sku_code'] = $buyerSkuCode;
                        $groupedProducts[$normalizedName]['cheapest_price'] = $price;
                        $groupedProducts[$normalizedName]['cheapest_seller'] = $product['seller_name'] ?? '';
                    }
                }
            }

            $this->info('Grouped to ' . count($groupedProducts) . ' unique packs');

            // Get all active buyer_sku_codes (all variants, not just cheapest)
            $activeSkuCodes = [];
            foreach ($groupedProducts as $group) {
                $activeSkuCodes = array_merge($activeSkuCodes, $group['buyer_sku_codes']);
            }
            $activeSkuCodes = array_unique($activeSkuCodes);

            // Update diamond packs
            $updatedCount = 0;
            $activatedCount = 0;
            $deactivatedCount = 0;
            $deactivatedPacks = []; // Format: ['name' => 'Pack Name', 'reason' => 'Reason text']
            $activatedPacks = []; // Format: ['name' => 'Pack Name', 'reason' => 'Reason text']
            $updatedPackIds = []; // Track pack IDs that were updated to exclude from deactivation
            $gameTypesInSync = []; // Track which game_types have products in this sync batch

            DB::beginTransaction();
            try {
                // Update packs that match active SKU codes
                foreach ($groupedProducts as $normalizedName => $productData) {
                    $cheapestSkuCode = $productData['cheapest_sku_code'];
                    $price = $productData['cheapest_price'];
                    $allSkuCodes = $productData['buyer_sku_codes'];

                    // Always match by normalized product name first - product name is the stable index
                    // SKU codes change in API responses, but product names are more consistent
                    $normalizedPackName = $this->normalizeProductName($productData['product_name']);
                    $pack = null;
                    
                    // Get all packs and match by normalized name (mobilelegends, freefire, pubg_mobile, genshin_impact, bloodstrike, honorofkings, punishinggrayraven, and wutheringwaves)
                    $allPacks = DiamondPack::whereNotNull('name')
                        ->where('name', '!=', '')
                        ->whereIn('game_type', ['mobilelegends', 'freefire', 'pubg_mobile', 'genshin_impact', 'bloodstrike', 'honorofkings', 'punishinggrayraven', 'wutheringwaves'])
                        ->get();
                    
                    foreach ($allPacks as $candidatePack) {
                        $candidateNormalizedName = $this->normalizeProductName($candidatePack->name);
                        if ($candidateNormalizedName === $normalizedPackName) {
                            $pack = $candidatePack;
                            $this->info("Matched pack by name: {$pack->name} (DB code: {$pack->code}, Digiflazz codes: " . implode(", ", $allSkuCodes) . ")");
                            break;
                        }
                    }

                    if ($pack) {
                        // Check price_limit: if set, price must be <= price_limit
                        $priceLimit = $pack->price_limit;
                        $exceedsPriceLimit = $priceLimit !== null && $price > $priceLimit;

                        if ($exceedsPriceLimit) {
                            // Price exceeds limit - don't update/activate, will be deactivated if no other sellers
                            $this->warn("Price exceeds limit for {$pack->name}: {$price} > {$priceLimit}");
                            \App\Support\SafeLog::info('Digiflazz sync: Price exceeds limit', [
                                'pack_id' => $pack->id,
                                'pack_name' => $pack->name,
                                'digiflazz_price' => $price,
                                'price_limit' => $priceLimit,
                            ]);
                            continue; // Skip this pack, move to next
                        }

                        // Price is within limit (or no limit set) - proceed with update
                        $wasInactive = !$pack->is_active;
                        $priceChanged = $pack->price != $price;
                        $oldPrice = $pack->price;
                        $oldCode = $pack->code;

                        // Update code to cheapest SKU, price, and activate
                        $codeChanged = $oldCode !== $cheapestSkuCode;
                        $pack->code = $cheapestSkuCode;
                        $pack->price = $price;
                        $pack->is_active = true;
                        $pack->save();

                        // Track this pack ID to exclude from deactivation check
                        $updatedPackIds[] = $pack->id;
                        
                        // Track game_type that has products in this sync batch
                        if ($pack->game_type && !in_array($pack->game_type, $gameTypesInSync)) {
                            $gameTypesInSync[] = $pack->game_type;
                        }

                        $updatedCount++;

                        if ($wasInactive) {
                            $activatedCount++;
                            $reason = "Activated - Price: {$price}";
                            if ($codeChanged) {
                                $reason .= ", SKU updated to: {$cheapestSkuCode}";
                            }
                            $activatedPacks[] = [
                                'name' => $pack->name,
                                'reason' => $reason,
                            ];
                        } elseif ($priceChanged || $codeChanged) {
                            $changes = [];
                            if ($priceChanged) {
                                $changes[] = "Price: {$oldPrice} → {$price}";
                            }
                            if ($codeChanged) {
                                $changes[] = "SKU: {$oldCode} → {$cheapestSkuCode}";
                            }
                            $activatedPacks[] = [
                                'name' => $pack->name,
                                'reason' => implode(", ", $changes),
                            ];
                        }

                        if ($priceChanged || $codeChanged) {
                            $changeMsg = [];
                            if ($codeChanged) {
                                $changeMsg[] = "SKU: {$oldCode} → {$cheapestSkuCode}";
                            }
                            if ($priceChanged) {
                                $changeMsg[] = "Price: {$oldPrice} → {$price}";
                            }
                            $this->line("Updated: {$pack->name} - " . implode(", ", $changeMsg));
                        }
                    } else {
                        // Pack not found - log for manual review
                        $this->warn("Pack not found for any SKU variants: " . implode(", ", $allSkuCodes) . " ({$productData['product_name']})");
                        \App\Support\SafeLog::warning('Digiflazz sync: Pack not found for any SKU variants', [
                            'sku_codes' => $allSkuCodes,
                            'cheapest_sku_code' => $cheapestSkuCode,
                            'product_name' => $productData['product_name'],
                        ]);
                    }
                }

                // Deactivate packs that:
                // 1. Don't have active sellers in Digiflazz (not found in groupedProducts)
                // 2. Have prices exceeding their price_limit
                // Only process game_types that have products matched in this sync batch
                // Exclude packs that were just updated in the update loop above
                // This prevents deactivating packs from game_types that didn't have matching products in the API response
                
                $packsToDeactivate = DiamondPack::where('is_active', true)
                    ->whereNotNull('code')
                    ->where('code', '!=', '')
                    ->whereIn('game_type', ['mobilelegends', 'freefire', 'pubg_mobile', 'genshin_impact', 'bloodstrike', 'honorofkings', 'punishinggrayraven', 'wutheringwaves'])
                    ->when(!empty($gameTypesInSync), function ($query) use ($gameTypesInSync) {
                        // Only check packs from game_types that had products matched in this sync
                        return $query->whereIn('game_type', $gameTypesInSync);
                    }, function ($query) {
                        // If no game_types had matches, don't deactivate anything (safety fallback)
                        return $query->whereRaw('1 = 0');
                    })
                    ->when(!empty($updatedPackIds), function ($query) use ($updatedPackIds) {
                        return $query->whereNotIn('id', $updatedPackIds);
                    })
                    ->get();

                foreach ($packsToDeactivate as $pack) {
                    $shouldDeactivate = false;
                    $reason = '';

                    // Check if pack has active sellers by matching normalized name
                    $packNormalizedName = $this->normalizeProductName($pack->name);
                    $productData = $groupedProducts[$packNormalizedName] ?? null;
                    
                    if (!$productData) {
                        // Pack not found in grouped products (no active sellers) - deactivate
                        $shouldDeactivate = true;
                        $reason = 'No active sellers available on Digiflazz';
                    } else {
                        // Pack has sellers - check if price exceeds limit
                        if ($pack->price_limit !== null) {
                            if ($productData['cheapest_price'] > $pack->price_limit) {
                                // Price exceeds limit - deactivate
                                $shouldDeactivate = true;
                                $reason = "Price exceeds limit: {$productData['cheapest_price']} > {$pack->price_limit}";
                            }
                        }
                    }

                    if ($shouldDeactivate) {
                        $pack->is_active = false;
                        $pack->save();
                        $deactivatedCount++;
                        $deactivatedPacks[] = [
                            'name' => $pack->name,
                            'reason' => $reason,
                        ];
                        $this->warn("Deactivated: {$pack->name} ({$reason})");
                    }
                }

                DB::commit();

                $this->info("Sync completed!");
                $this->info("Updated: {$updatedCount} packs");
                $this->info("Activated: {$activatedCount} packs");
                $this->info("Deactivated: {$deactivatedCount} packs");

                if ($updatedCount === 0) {
                    $this->warn('No packs matched Digiflazz products (Updated: 0). Check pack names vs Digiflazz product names.');
                    \App\Support\SafeLog::warning('Digiflazz sync: zero packs matched/updated', [
                        'products_fetched' => count($products),
                        'active_products' => count($activeProducts),
                        'grouped_products' => count($groupedProducts),
                        'game_types_in_sync' => $gameTypesInSync,
                    ]);
                }

                // Send Telegram notification
                $this->sendTelegramNotification($updatedCount, $activatedCount, $deactivatedCount, $activatedPacks, $deactivatedPacks);

                \App\Support\SafeLog::info('Digiflazz price sync completed', [
                    'updated' => $updatedCount,
                    'activated' => $activatedCount,
                    'deactivated' => $deactivatedCount,
                    'products_fetched' => count($products),
                    'grouped_products' => count($groupedProducts),
                ]);

                return 0;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error updating packs: ' . $e->getMessage());
                \App\Support\SafeLog::error('Digiflazz sync: Database error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            \App\Support\SafeLog::error('Digiflazz sync: Fatal error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }

    /**
     * Normalize product name for grouping
     * Removes seller-specific variations and standardizes the name
     *
     * @param string $productName
     * @return string
     */
    private function normalizeProductName(string $productName): string
    {
        // Remove trailing "(Global)" or similar location tags
        $normalized = preg_replace('/\s*\([^)]+\)\s*$/', '', $productName);

        // Convert to lowercase for case-insensitive comparison
        $normalized = strtolower(trim($normalized));

        // Remove extra whitespace
        $normalized = preg_replace('/\s+/', ' ', $normalized);

        return $normalized;
    }

    /**
     * Send Telegram notification about sync results
     *
     * @param int $updatedCount
     * @param int $activatedCount
     * @param int $deactivatedCount
     * @param array $activatedPacks Array of ['name' => string, 'reason' => string]
     * @param array $deactivatedPacks Array of ['name' => string, 'reason' => string]
     * @return void
     */
    private function sendTelegramNotification(int $updatedCount, int $activatedCount, int $deactivatedCount, array $activatedPacks, array $deactivatedPacks): void
    {
        try {
            $message = "🔄 <b>Digiflazz Price Sync Complete</b>\n\n";
            $message .= "✅ Updated: <b>{$updatedCount}</b> packs\n";
            $message .= "🟢 Activated: <b>{$activatedCount}</b> packs\n";

            // Show activated packs with reasons if any
            if (count($activatedPacks) > 0) {
                $message .= "\n<b>🟢 Activated/Updated Packs:</b>\n";
                foreach ($activatedPacks as $pack) {
                    $message .= "• <b>{$pack['name']}</b>\n";
                    $message .= "  └ {$pack['reason']}\n";
                }
            }

            // Show deactivated packs with reasons
            if ($deactivatedCount > 0) {
                $message .= "\n🔴 Deactivated: <b>{$deactivatedCount}</b> packs\n";
                $message .= "<b>🔴 Deactivated Packs:</b>\n";
                foreach ($deactivatedPacks as $pack) {
                    $message .= "• <b>{$pack['name']}</b>\n";
                    $message .= "  └ {$pack['reason']}\n";
                }
            } else {
                $message .= "\n🔴 Deactivated: <b>0</b> packs\n";
            }

            TelegramService::sendToUpdatesChannel($message);
        } catch (\Exception $e) {
            \App\Support\SafeLog::warning('Failed to send Telegram notification for price sync', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
