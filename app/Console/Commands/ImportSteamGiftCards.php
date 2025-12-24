<?php

namespace App\Console\Commands;

use App\Models\DiamondPack;
use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportSteamGiftCards extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'steam:import-giftcards {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Steam Gift Cards from JSON file with region support';

    /**
     * Region mapping from code to display name
     */
    protected $regionNames = [
        'free' => 'Global',
        'us' => 'United States',
        'br' => 'Brazil',
        'cn' => 'China',
        'eu' => 'Europe',
        'gb' => 'United Kingdom',
        'ae' => 'United Arab Emirates',
        'hk' => 'Hong Kong',
        'tw' => 'Taiwan',
        'vn' => 'Vietnam',
        'th' => 'Thailand',
        'ph' => 'Philippines',
        'sg' => 'Singapore',
        'id' => 'Indonesia',
        'in' => 'India',
        'kw' => 'Kuwait',
        'qa' => 'Qatar',
        'sa' => 'Saudi Arabia',
        'za' => 'South Africa',
        'ua' => 'Ukraine',
        'tr' => 'Turkey',
        'cr' => 'Costa Rica',
        'pe' => 'Peru',
        'uy' => 'Uruguay',
    ];

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Steam Gift Cards import...');

        $filePath = $this->argument('file') 
            ? $this->argument('file')
            : storage_path('app/private/games_data_organized/code/steam_giftcard.json');

        if (!File::exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $content = File::get($filePath);
        $data = json_decode($content, true);

        if (!$data) {
            $this->error("Failed to decode JSON. Error: " . json_last_error_msg());
            return 1;
        }

        if (!isset($data['data']['product'])) {
            $this->error("Invalid JSON structure. Missing 'data.product'");
            return 1;
        }

        $product = $data['data']['product'];
        $variations = $product['variations'] ?? [];

        if (empty($variations)) {
            $this->error("No variations found");
            return 1;
        }

        $this->info("Found " . count($variations) . " variations");

        $gameType = 'steam_giftcard';
        $gameName = 'Steam Gift Cards';

        DB::beginTransaction();
        try {
            $totalImported = 0;
            $totalUpdated = 0;
            
            // Group variations by region for sorting
            $variationsByRegion = [];
            foreach ($variations as $variation) {
                $regionCode = null;
                
                // Extract region from attributes
                if (isset($variation['attributes']) && is_array($variation['attributes'])) {
                    foreach ($variation['attributes'] as $attr) {
                        if (($attr['key'] ?? '') === 'pa_region') {
                            $regionCode = $attr['value'] ?? null;
                            break;
                        }
                    }
                }
                
                if (!$regionCode) {
                    $regionCode = 'free'; // Default to Global
                }
                
                if (!isset($variationsByRegion[$regionCode])) {
                    $variationsByRegion[$regionCode] = [];
                }
                
                $variationsByRegion[$regionCode][] = $variation;
            }

            // Sort regions alphabetically
            ksort($variationsByRegion);

            $sortOrder = 0;
            
            // Process each region
            foreach ($variationsByRegion as $regionCode => $regionVariations) {
                // Sort variations by price within region
                usort($regionVariations, function($a, $b) {
                    $priceA = (float)($a['price'] ?? 0);
                    $priceB = (float)($b['price'] ?? 0);
                    return $priceA <=> $priceB;
                });

                foreach ($regionVariations as $variation) {
                    $variationId = (string)$variation['id'];
                    $variationName = $variation['name'] ?? '';
                    $priceUsd = (float)($variation['price'] ?? 0);
                    $inStock = (bool)($variation['in_stock'] ?? false);
                    $discount = (float)($variation['discount'] ?? 0);
                    
                    // Extract region code
                    $regionCodeForPack = null;
                    if (isset($variation['attributes']) && is_array($variation['attributes'])) {
                        foreach ($variation['attributes'] as $attr) {
                            if (($attr['key'] ?? '') === 'pa_region') {
                                $regionCodeForPack = $attr['value'] ?? null;
                                break;
                            }
                        }
                    }
                    
                    if (!$regionCodeForPack) {
                        $regionCodeForPack = 'free';
                    }

                    // Check if pack already exists by code
                    $pack = DiamondPack::where('code', $variationId)->first();

                    if ($pack) {
                        // Update existing pack
                        $pack->name = $variationName;
                        $pack->game_type = $gameType;
                        $pack->region = $regionCodeForPack;
                        $pack->price_usd = $priceUsd;
                        $pack->price = $priceUsd;
                        $pack->is_active = $inStock;
                        $pack->discount_percentage = $discount;
                        $pack->diamonds = 0; // Gift cards don't have diamonds
                        $pack->bonus_diamonds = 0;
                        $pack->sort_order = $sortOrder;
                        $pack->save();
                        $totalUpdated++;
                    } else {
                        // Create new pack
                        $packData = [
                            'code' => $variationId,
                            'name' => $variationName,
                            'game_type' => $gameType,
                            'region' => $regionCodeForPack,
                            'price_usd' => $priceUsd,
                            'price' => $priceUsd,
                            'is_active' => $inStock,
                            'discount_percentage' => $discount,
                            'diamonds' => 0,
                            'bonus_diamonds' => 0,
                            'sort_order' => $sortOrder,
                        ];
                        
                        DiamondPack::create($packData);
                        $totalImported++;
                    }
                    
                    $sortOrder++;
                }
            }

            // Create or update Game entry
            $game = Game::firstOrNew(['game_type' => $gameType]);
            $gameChanged = false;

            if ($game->name !== $gameName) {
                $game->name = $gameName;
                $gameChanged = true;
            }

            // Set as giftcard
            if (!$game->is_giftcard) {
                $game->is_giftcard = true;
                $gameChanged = true;
            }

            // Set required fields - Steam Gift Cards need save_id (email/username)
            $requiredFields = [
                [
                    'data_name' => 'save_id',
                    'type' => 'text',
                    'required' => true,
                    'name' => 'Steam Email/Username',
                ]
            ];
            
            $currentFields = $game->required_fields ?? [];
            if (json_encode($currentFields) !== json_encode($requiredFields)) {
                $game->required_fields = $requiredFields;
                $gameChanged = true;
            }

            if (!$game->exists) {
                $game->is_active = true;
                $game->is_topseller = false;
                $game->is_newproduct = false;
                $gameChanged = true;
            }

            if ($gameChanged || !$game->exists) {
                $game->save();
                $this->info("  ✓ Game entry created/updated: {$gameName}");
            }

            DB::commit();

            $this->info("\nImport completed!");
            $this->info("Imported: {$totalImported} packs");
            $this->info("Updated: {$totalUpdated} packs");

            return 0;

        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Import failed: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
