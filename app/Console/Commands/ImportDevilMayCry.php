<?php

namespace App\Console\Commands;

use App\Models\DiamondPack;
use App\Models\Game;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ImportDevilMayCry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devilmaycry:import {file?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import Devil May Cry: Peak of Combat from JSON file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Devil May Cry: Peak of Combat import...');

        $filePath = $this->argument('file') 
            ? $this->argument('file')
            : storage_path('app/private/games_data_organized/manual/devil_may_cry_peak_of_combat');

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
        $productName = $product['name'] ?? 'Devil May Cry: Peak of Combat';
        $gameType = 'devil_may_cry_peak_of_combat';
        $variations = $product['variations'] ?? [];

        if (empty($variations)) {
            $this->error("No variations found in the file");
            return 1;
        }

        $this->info("Found " . count($variations) . " variations");

        // Extract required fields from first variation
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

        $totalImported = 0;
        $totalUpdated = 0;

        DB::beginTransaction();
        try {
            foreach ($variations as $variation) {
                $variationId = (string)$variation['id'];
                $variationName = $variation['name'] ?? '';
                $priceUsd = (float)($variation['price'] ?? 0);
                $inStock = (bool)($variation['in_stock'] ?? false);
                $discount = (float)($variation['discount'] ?? 0);

                // Extract diamonds/bonus from name if possible
                $diamonds = 0;
                $bonusDiamonds = 0;
                if (preg_match('/(\d+)\s*\+\s*(\d+)/', $variationName, $matches)) {
                    $diamonds = (int)$matches[1];
                    $bonusDiamonds = (int)$matches[2];
                } elseif (preg_match('/(\d+)/', $variationName, $matches)) {
                    $diamonds = (int)$matches[1];
                }

                // Use variation ID as code
                $code = $variationId;

                // Check if pack already exists by code
                $pack = DiamondPack::where('code', $code)->first();

                if ($pack) {
                    // Update existing pack
                    $pack->game_type = $gameType;
                    $pack->name = $variationName;
                    $pack->diamonds = $diamonds;
                    $pack->bonus_diamonds = $bonusDiamonds;
                    $pack->price = $priceUsd;
                    $pack->price_usd = $priceUsd;
                    $pack->discount_percentage = $discount;
                    $pack->is_active = $inStock;
                    $pack->save();
                    $totalUpdated++;
                    $this->line("  ✓ Updated: {$variationName}");
                } else {
                    // Create new pack
                    DiamondPack::create([
                        'game_type' => $gameType,
                        'name' => $variationName,
                        'code' => $code,
                        'diamonds' => $diamonds,
                        'bonus_diamonds' => $bonusDiamonds,
                        'price' => $priceUsd,
                        'price_usd' => $priceUsd,
                        'discount_percentage' => $discount,
                        'is_active' => $inStock,
                        'sort_order' => 0,
                    ]);
                    $totalImported++;
                    $this->line("  ✓ Imported: {$variationName}");
                }
            }

            // Create or update Game entry
            $game = Game::firstOrNew(['game_type' => $gameType]);
            $gameChanged = false;

            if ($game->name !== $productName) {
                $game->name = $productName;
                $gameChanged = true;
            }

            // Update required fields
            if (!empty($requiredFields)) {
                $currentFields = $game->required_fields ?? [];
                if (json_encode($currentFields) !== json_encode($requiredFields)) {
                    $game->required_fields = $requiredFields;
                    $gameChanged = true;
                }
            }

            if (!$game->exists) {
                $game->is_active = true;
                $game->is_topseller = false;
                $game->is_newproduct = false;
                $game->is_giftcard = false;
                $gameChanged = true;
            }

            if ($gameChanged || !$game->exists) {
                $game->save();
                $this->info("  ✓ Game entry created/updated: {$productName} (game_type: {$gameType})");
                if (!empty($requiredFields)) {
                    $this->line("    Fields: " . implode(', ', array_column($requiredFields, 'data_name')));
                }
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

