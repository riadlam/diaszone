<?php

namespace App\Console\Commands;

use App\Models\DiamondPack;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
    protected $description = 'Sync diamond pack prices from Digiflazz API';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Digiflazz price sync...');

        try {
            // Get Digiflazz credentials
            // Note: Digiflazz uses 'sign' as the API key name in config
            $username = config('services.digiflazz.username') ?? env('DIGIFLAZZ_USERNAME');
            $apiKey = config('services.digiflazz.sign') ?? config('services.digiflazz.api_key') ?? env('DIGIFLAZZ_SIGN') ?? env('DIGIFLAZZ_API_KEY');
            $baseUrl = config('services.digiflazz.base_url', 'https://api.digiflazz.com/v1');

            if (empty($username) || empty($apiKey)) {
                $this->error('Digiflazz credentials not configured');
                Log::error('Digiflazz sync: Missing credentials', [
                    'username_set' => !empty($username),
                    'api_key_set' => !empty($apiKey),
                ]);
                return 1;
            }

            // Generate signature for price-list API (md5 of username + apiKey + "pricelist")
            // Note: Digiflazz uses "pricelist" as the constant string for price-list endpoint
            $sign = md5($username . $apiKey . 'pricelist');

            // Call Digiflazz price-list API
            $response = Http::timeout(30)
                ->post($baseUrl . '/price-list', [
                    'cmd' => 'prepaid',
                    'username' => $username,
                    'sign' => $sign,
                    'type' => 'Global',
                    'category' => 'Games',
                ]);

            if (!$response->successful()) {
                $this->error('Failed to fetch price list from Digiflazz');
                Log::error('Digiflazz sync: API call failed', [
                    'status' => $response->status(),
                    'response' => $response->body(),
                ]);
                return 1;
            }

            $data = $response->json();
            $products = $data['data'] ?? [];

            if (empty($products)) {
                $this->warn('No products returned from Digiflazz API');
                Log::warning('Digiflazz sync: Empty product list');
                return 0;
            }

            $this->info('Fetched ' . count($products) . ' products from Digiflazz');

            // Filter active products
            $activeProducts = array_filter($products, function ($product) {
                return ($product['buyer_product_status'] ?? false) === true
                    && ($product['seller_product_status'] ?? false) === true;
            });

            $this->info('Found ' . count($activeProducts) . ' active products');

            // Group products by normalized product name and select cheapest
            $groupedProducts = [];
            foreach ($activeProducts as $product) {
                $normalizedName = $this->normalizeProductName($product['product_name']);
                $buyerSkuCode = $product['buyer_sku_code'];
                $price = (int)($product['price'] ?? 0);

                // Initialize group if not exists
                if (!isset($groupedProducts[$normalizedName])) {
                    $groupedProducts[$normalizedName] = [
                        'product_name' => $product['product_name'],
                        'buyer_sku_code' => $buyerSkuCode,
                        'price' => $price,
                        'seller_name' => $product['seller_name'] ?? '',
                    ];
                } else {
                    // Keep the cheapest one
                    if ($price < $groupedProducts[$normalizedName]['price']) {
                        $groupedProducts[$normalizedName] = [
                            'product_name' => $product['product_name'],
                            'buyer_sku_code' => $buyerSkuCode,
                            'price' => $price,
                            'seller_name' => $product['seller_name'] ?? '',
                        ];
                    }
                }
            }

            $this->info('Grouped to ' . count($groupedProducts) . ' unique packs');

            // Get all active buyer_sku_codes from grouped products
            $activeSkuCodes = array_column($groupedProducts, 'buyer_sku_code');

            // Update diamond packs
            $updatedCount = 0;
            $activatedCount = 0;
            $deactivatedCount = 0;
            $deactivatedPacks = []; // Format: ['name' => 'Pack Name', 'reason' => 'Reason text']
            $activatedPacks = []; // Format: ['name' => 'Pack Name', 'reason' => 'Reason text']

            DB::beginTransaction();
            try {
                // Update packs that match active SKU codes
                foreach ($groupedProducts as $normalizedName => $productData) {
                    $buyerSkuCode = $productData['buyer_sku_code'];
                    $price = $productData['price'];

                    // Find diamond pack by code (buyer_sku_code should match diamond_packs.code)
                    $pack = DiamondPack::where('code', $buyerSkuCode)->first();

                    if ($pack) {
                        // Check price_limit: if set, price must be <= price_limit
                        $priceLimit = $pack->price_limit;
                        $exceedsPriceLimit = $priceLimit !== null && $price > $priceLimit;

                        if ($exceedsPriceLimit) {
                            // Price exceeds limit - don't update/activate, will be deactivated if no other sellers
                            $this->warn("Price exceeds limit for {$pack->name}: {$price} > {$priceLimit}");
                            Log::info('Digiflazz sync: Price exceeds limit', [
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

                        // Update code, price, and activate
                        $pack->code = $buyerSkuCode;
                        $pack->price = $price;
                        $pack->is_active = true;
                        $pack->save();

                        $updatedCount++;

                        if ($wasInactive) {
                            $activatedCount++;
                            $activatedPacks[] = [
                                'name' => $pack->name,
                                'reason' => "Activated - Price: {$price} (within limit)",
                            ];
                        } elseif ($priceChanged) {
                            $activatedPacks[] = [
                                'name' => $pack->name,
                                'reason' => "Price updated: {$oldPrice} → {$price}",
                            ];
                        }

                        if ($priceChanged) {
                            $this->line("Updated: {$pack->name} - Price: {$price}");
                        }
                    } else {
                        // Pack not found - log for manual review
                        $this->warn("Pack not found for SKU: {$buyerSkuCode} ({$productData['product_name']})");
                        Log::warning('Digiflazz sync: Pack not found', [
                            'buyer_sku_code' => $buyerSkuCode,
                            'product_name' => $productData['product_name'],
                        ]);
                    }
                }

                // Deactivate packs that:
                // 1. Don't have active sellers in Digiflazz (not in activeSkuCodes)
                // 2. Have prices exceeding their price_limit
                $packsToDeactivate = DiamondPack::where('is_active', true)
                    ->whereNotNull('code')
                    ->where('code', '!=', '')
                    ->get();

                foreach ($packsToDeactivate as $pack) {
                    $shouldDeactivate = false;
                    $reason = '';

                    // Check if pack has active sellers
                    if (!in_array($pack->code, $activeSkuCodes)) {
                        // No active sellers - deactivate
                        $shouldDeactivate = true;
                        $reason = 'No active sellers available on Digiflazz';
                    } else {
                        // Pack has sellers - check if price exceeds limit
                        $productData = collect($groupedProducts)->firstWhere('buyer_sku_code', $pack->code);
                        if ($productData && $pack->price_limit !== null) {
                            if ($productData['price'] > $pack->price_limit) {
                                // Price exceeds limit - deactivate
                                $shouldDeactivate = true;
                                $reason = "Price exceeds limit: {$productData['price']} > {$pack->price_limit}";
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

                // Send Telegram notification
                $this->sendTelegramNotification($updatedCount, $activatedCount, $deactivatedCount, $activatedPacks, $deactivatedPacks);

                Log::info('Digiflazz price sync completed', [
                    'updated' => $updatedCount,
                    'activated' => $activatedCount,
                    'deactivated' => $deactivatedCount,
                ]);

                return 0;
            } catch (\Exception $e) {
                DB::rollBack();
                $this->error('Error updating packs: ' . $e->getMessage());
                Log::error('Digiflazz sync: Database error', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                return 1;
            }
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Digiflazz sync: Fatal error', [
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
            Log::warning('Failed to send Telegram notification for price sync', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}
