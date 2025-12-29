<?php

namespace App\Console\Commands;

use App\Models\DiamondPack;
use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ImportGamesFromJson extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'games:import-from-json';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import games from JSON files in storage/app/private/games_data_organized/topup, topup_two, and manual directories to diamond_packs table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting game import from JSON files...');

        // Check topup, topup_two, and manual directories
        $jsonDirs = [
            storage_path('app/private/games_data_organized/topup'),
            storage_path('app/private/games_data_organized/topup_two'),
            storage_path('app/private/games_data_organized/manual'),
        ];

        $allFiles = [];
        
        foreach ($jsonDirs as $jsonDir) {
            $this->line("Checking directory: {$jsonDir}");
            
            if (!File::exists($jsonDir)) {
                $this->warn("Directory not found: {$jsonDir} - skipping");
                continue;
            }

            // Get all files (they might not have .json extension)
            $allItems = File::allFiles($jsonDir);
            foreach ($allItems as $item) {
                if ($item->isFile()) {
                    $allFiles[] = $item;
                }
            }
        }

        if (empty($allFiles)) {
            $this->error("No files found in any directory");
            return 1;
        }
        
        $this->info("Found " . count($allFiles) . " files to process");

        $totalImported = 0;
        $totalUpdated = 0;

        DB::beginTransaction();
        try {
            foreach ($allFiles as $file) {
                $filePath = $file->getPathname();
                $filename = $file->getFilename();
                $gameSlug = pathinfo($filename, PATHINFO_FILENAME); // e.g., "arena_breakout"
                $gameType = $gameSlug; // Use filename as game_type (e.g., "arena_breakout")

                $this->info("Processing: {$filename}");

                if (!File::exists($filePath)) {
                    $this->error("File does not exist: {$filePath}");
                    continue;
                }

                if (!File::isReadable($filePath)) {
                    $this->error("File is not readable: {$filePath}");
                    continue;
                }

                $content = File::get($file->getPathname());
                $data = json_decode($content, true);

                if (!$data) {
                    $this->error("Failed to decode JSON in {$filename}. JSON error: " . json_last_error_msg());
                    continue;
                }

                if (!isset($data['data']['product'])) {
                    $this->error("Invalid JSON structure in {$filename}. Missing 'data.product'");
                    $this->line("JSON keys: " . implode(', ', array_keys($data)));
                    continue;
                }

                $product = $data['data']['product'];
                $productName = $product['name'] ?? '';
                
                // Extract game name from product name (e.g., "Genshin Impact Genesis Crystals" -> "Genshin Impact")
                // Or use the game slug if name extraction fails
                $gameName = $productName;
                if ($gameName && strpos($gameName, ' - ') !== false) {
                    $gameName = explode(' - ', $gameName)[0];
                } elseif (empty($gameName)) {
                    $gameName = ucfirst(str_replace('_', ' ', $gameSlug));
                }
                
                $variations = $product['variations'] ?? [];

                if (empty($variations)) {
                    $this->warn("No variations found in {$filename}");
                    continue;
                }

                $this->info("Found " . count($variations) . " variations for {$gameName}");

                // Extract required fields from first variation (all variations should have same fields)
                $requiredFields = [];
                if (!empty($variations) && isset($variations[0]['fields']) && is_array($variations[0]['fields'])) {
                    foreach ($variations[0]['fields'] as $field) {
                        $fieldData = [
                            'data_name' => $field['data_name'] ?? '',
                            'type' => $field['type'] ?? 'text',
                            'required' => $field['required'] ?? true,
                            'name' => $field['name'] ?? '',
                        ];
                        
                        // For select fields, include options
                        if ($field['type'] === 'select' && isset($field['options']) && is_array($field['options'])) {
                            $fieldData['options'] = $field['options'];
                        }
                        
                        $requiredFields[] = $fieldData;
                    }
                }

                foreach ($variations as $index => $variation) {
                    $variationId = (string)$variation['id'];
                    $variationName = $variation['name'] ?? '';
                    $priceUsd = (float)($variation['price'] ?? 0);
                    $inStock = (bool)($variation['in_stock'] ?? false);
                    $discount = (float)($variation['discount'] ?? 0);
                    $fields = $variation['fields'] ?? [];

                    // Extract diamonds/bonus from name if possible (e.g., "60 + 6 Bonds")
                    $diamonds = 0;
                    $bonusDiamonds = 0;
                    if (preg_match('/(\d+)\s*\+\s*(\d+)/', $variationName, $matches)) {
                        $diamonds = (int)$matches[1];
                        $bonusDiamonds = (int)$matches[2];
                    } elseif (preg_match('/(\d+)/', $variationName, $matches)) {
                        $diamonds = (int)$matches[1];
                    }

                    // Check if pack already exists by code
                    $pack = DiamondPack::where('code', $variationId)->first();

                    if ($pack) {
                        // Update existing pack
                        $pack->name = $variationName;
                        $pack->game_type = $gameType;
                        $pack->price_usd = $priceUsd;
                        $pack->price = $priceUsd; // Keep legacy price field
                        $pack->is_active = (bool)$inStock;
                        $pack->discount_percentage = $discount;
                        $pack->diamonds = $diamonds;
                        $pack->bonus_diamonds = $bonusDiamonds;
                        $pack->sort_order = $index;
                        $pack->save();
                        $totalUpdated++;
                        $this->line("  ✓ Updated: {$variationName} (Code: {$variationId})");
                    } else {
                        // Create new pack
                        try {
                            $packData = [
                                'code' => $variationId,
                                'name' => $variationName,
                                'game_type' => $gameType,
                                'price_usd' => $priceUsd,
                                'price' => $priceUsd, // Legacy price field
                                'is_active' => $inStock,
                                'discount_percentage' => $discount,
                                'diamonds' => $diamonds,
                                'bonus_diamonds' => $bonusDiamonds,
                                'sort_order' => $index,
                            ];
                            
                            $this->line("  Creating pack with data: " . json_encode($packData));
                            
                            $newPack = DiamondPack::create($packData);
                            $totalImported++;
                            $this->line("  ✓ Imported: {$variationName} (Code: {$variationId}, ID: {$newPack->id})");
                        } catch (\Illuminate\Database\QueryException $e) {
                            $this->error("  ✗ Database error creating pack: {$variationName}");
                            $this->error("    SQL Error: " . $e->getMessage());
                            $this->error("    SQL: " . $e->getSql());
                            throw $e; // Re-throw to trigger rollback
                        } catch (\Exception $e) {
                            $this->error("  ✗ Failed to create pack: {$variationName}");
                            $this->error("    Error: " . $e->getMessage());
                            $this->error("    Trace: " . $e->getTraceAsString());
                            throw $e; // Re-throw to trigger rollback
                        }
                    }
                }
                
                // Create or update Game entry for this game_type
                $game = Game::firstOrNew(['game_type' => $gameType]);
                $gameChanged = false;
                
                if ($game->name !== $gameName) {
                    $game->name = $gameName;
                    $gameChanged = true;
                }
                
                // Update required_fields if they exist in JSON (only update if different to avoid overwriting manual changes)
                if (!empty($requiredFields)) {
                    $currentFields = $game->required_fields ?? [];
                    if (json_encode($currentFields) !== json_encode($requiredFields)) {
                        $game->required_fields = $requiredFields;
                        $gameChanged = true;
                    }
                }
                
                if (!$game->exists) {
                    $game->is_active = true; // Set to active by default
                    $game->is_topseller = false;
                    $game->is_newproduct = false;
                    $game->is_giftcard = false;
                    $gameChanged = true;
                }
                
                if ($gameChanged || !$game->exists) {
                    $game->save();
                    $this->line("  ✓ Game entry created/updated: {$gameName} (game_type: {$gameType})");
                    if (!empty($requiredFields)) {
                        $this->line("    Fields: " . implode(', ', array_column($requiredFields, 'data_name')));
                    }
                }
            }

            DB::commit();

            $this->info("Import completed!");
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

