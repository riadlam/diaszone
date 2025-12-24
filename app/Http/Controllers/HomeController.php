<?php

namespace App\Http\Controllers;

use App\Models\DiamondPack;
use App\Models\Game;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class HomeController extends Controller
{
    public function index()
    {
        // Get all unique active games from diamond_packs
        $games = DiamondPack::where('is_active', true)
            ->select('game_type')
            ->distinct()
            ->get()
            ->map(function($pack) {
                // Get first pack to extract game name
                $firstPack = DiamondPack::where('game_type', $pack->game_type)
                    ->where('is_active', true)
                    ->first();
                
                $gameName = $firstPack ? $firstPack->name : '';
                // Extract game name from pack name (e.g., "Arena Breakout - 60 + 6 Bonds" -> "Arena Breakout")
                if ($gameName && strpos($gameName, ' - ') !== false) {
                    $gameName = explode(' - ', $gameName)[0];
                }
                
                // Try to find image in top4gamers_images folder
                $imagePath = $this->findGameImage($pack->game_type, $gameName);
                
                return [
                    'game_type' => $pack->game_type,
                    'name' => $gameName ?: ucfirst(str_replace('_', ' ', $pack->game_type)),
                    'route' => $this->getGameRoute($pack->game_type),
                    'image_path' => $imagePath,
                ];
            });

        // Get top selling games
        $topSellingGames = Game::where('is_topseller', true)
            ->where('is_active', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function($game) {
                $imagePath = $this->findGameImage($game->game_type, $game->name);
                $route = $this->getGameRoute($game->game_type);
                
                // Extract game name - if name contains " - " or digits, extract the game name part
                $displayName = $game->name;
                if (strpos($displayName, ' - ') !== false) {
                    $displayName = explode(' - ', $displayName)[0];
                } elseif (preg_match('/^\d+/', $displayName) || preg_match('/\d+\s*\+?\s*\d+/', $displayName)) {
                    // If name starts with numbers or contains diamond counts, use game_type to generate name
                    $displayName = $this->getGameDisplayName($game->game_type);
                }
                
                // Calculate average rating and total reviews
                $totalReviews = $game->reviews_count ?? 0;
                $averageRating = $totalReviews > 0 ? round($game->reviews_avg_rating ?? 0, 1) : 5.0;
                
                return [
                    'id' => $game->id,
                    'game_type' => $game->game_type,
                    'name' => $displayName,
                    'route' => $route,
                    'image_path' => $imagePath,
                    'averageRating' => $averageRating,
                    'totalReviews' => $totalReviews,
                ];
            });

        // Get new products games
        $newProducts = Game::where('is_newproduct', true)
            ->where('is_active', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function($game) {
                $imagePath = $this->findGameImage($game->game_type, $game->name);
                $route = $this->getGameRoute($game->game_type);
                
                // Extract game name - if name contains " - " or digits, extract the game name part
                $displayName = $game->name;
                if (strpos($displayName, ' - ') !== false) {
                    $displayName = explode(' - ', $displayName)[0];
                } elseif (preg_match('/^\d+/', $displayName) || preg_match('/\d+\s*\+?\s*\d+/', $displayName)) {
                    // If name starts with numbers or contains diamond counts, use game_type to generate name
                    $displayName = $this->getGameDisplayName($game->game_type);
                }
                
                // Calculate average rating and total reviews
                $totalReviews = $game->reviews_count ?? 0;
                $averageRating = $totalReviews > 0 ? round($game->reviews_avg_rating ?? 0, 1) : 5.0;
                
                return [
                    'id' => $game->id,
                    'game_type' => $game->game_type,
                    'name' => $displayName,
                    'route' => $route,
                    'image_path' => $imagePath,
                    'averageRating' => $averageRating,
                    'totalReviews' => $totalReviews,
                ];
            });

        // Get gift cards games
        $giftCards = Game::where('is_giftcard', true)
            ->where('is_active', true)
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(function($game) {
                $imagePath = $this->findGameImage($game->game_type, $game->name);
                $route = $this->getGameRoute($game->game_type);
                
                // Extract game name - if name contains " - " or digits, extract the game name part
                $displayName = $game->name;
                if (strpos($displayName, ' - ') !== false) {
                    $displayName = explode(' - ', $displayName)[0];
                } elseif (preg_match('/^\d+/', $displayName) || preg_match('/\d+\s*\+?\s*\d+/', $displayName)) {
                    // If name starts with numbers or contains diamond counts, use game_type to generate name
                    $displayName = $this->getGameDisplayName($game->game_type);
                }
                
                // Calculate average rating and total reviews
                $totalReviews = $game->reviews_count ?? 0;
                $averageRating = $totalReviews > 0 ? round($game->reviews_avg_rating ?? 0, 1) : 5.0;
                
                return [
                    'id' => $game->id,
                    'game_type' => $game->game_type,
                    'name' => $displayName,
                    'route' => $route,
                    'image_path' => $imagePath,
                    'averageRating' => $averageRating,
                    'totalReviews' => $totalReviews,
                ];
            });

        return view('pages.new-home', compact('games', 'topSellingGames', 'newProducts', 'giftCards'));
    }
    
    /**
     * Shop page with paginated products
     */
    public function shop(Request $request)
    {
        $query = Game::where('is_active', true);
        
        // Filter by category if provided
        $category = $request->get('category');
        if ($category === 'topseller') {
            $query->where('is_topseller', true);
        } elseif ($category === 'new') {
            $query->where('is_newproduct', true);
        } elseif ($category === 'giftcard') {
            $query->where('is_giftcard', true);
        }
        
        // Search functionality
        $search = $request->get('search');
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('game_type', 'like', '%' . $search . '%');
            });
        }
        
        // Get paginated games (16 per page) with review counts and avg ratings
        // Sort by category groups first (topseller, newproduct, giftcard), then by sort_order within each group, then by name
        $games = $query->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->orderByRaw('is_topseller DESC, is_newproduct DESC, is_giftcard DESC')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(16)->appends($request->query());
        
        // Transform games with image paths and routes
        $games->getCollection()->transform(function($game) {
            $imagePath = $this->findGameImage($game->game_type, $game->name);
            $route = $this->getGameRoute($game->game_type);
            
            // Extract game name - if name contains " - " or digits, extract the game name part
            $displayName = $game->name;
            if (strpos($displayName, ' - ') !== false) {
                $displayName = explode(' - ', $displayName)[0];
            } elseif (preg_match('/^\d+/', $displayName) || preg_match('/\d+\s*\+?\s*\d+/', $displayName)) {
                $displayName = $this->getGameDisplayName($game->game_type);
            }
            
            // Calculate average rating and total reviews
            $totalReviews = $game->reviews_count ?? 0;
            $averageRating = $totalReviews > 0 ? round($game->reviews_avg_rating ?? 0, 1) : 5.0;
            
            return [
                'id' => $game->id,
                'game_type' => $game->game_type,
                'name' => $displayName,
                'route' => $route,
                'image_path' => $imagePath,
                'is_topseller' => $game->is_topseller,
                'is_newproduct' => $game->is_newproduct,
                'is_giftcard' => $game->is_giftcard,
                'averageRating' => $averageRating,
                'totalReviews' => $totalReviews,
            ];
        });
        
        return view('pages.shop', compact('games', 'category', 'search'));
    }
    
    /**
     * AJAX search endpoint for real-time search
     */
    public function searchAjax(Request $request)
    {
        $query = Game::where('is_active', true);
        
        // Get search term and limit results
        $search = $request->get('q', '');
        $limit = min($request->get('limit', 10), 20); // Max 20 results
        
        if (strlen($search) < 2) {
            return response()->json([
                'success' => true,
                'results' => [],
                'count' => 0
            ]);
        }
        
        // Search in name and game_type
        $query->where(function($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('game_type', 'like', '%' . $search . '%');
        });
        
        // Get games (limit to 10-20 for performance)
        // Sort by category groups first, then by sort_order, then by name
        $games = $query->orderByRaw('is_topseller DESC, is_newproduct DESC, is_giftcard DESC')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit($limit)
            ->get();
        
        // Transform games
        $results = $games->map(function($game) {
            $imagePath = $this->findGameImage($game->game_type, $game->name);
            $route = $this->getGameRoute($game->game_type);
            
            // Extract game name
            $displayName = $game->name;
            if (strpos($displayName, ' - ') !== false) {
                $displayName = explode(' - ', $displayName)[0];
            } elseif (preg_match('/^\d+/', $displayName) || preg_match('/\d+\s*\+?\s*\d+/', $displayName)) {
                $displayName = $this->getGameDisplayName($game->game_type);
            }
            
            return [
                'id' => $game->id,
                'game_type' => $game->game_type,
                'name' => $displayName,
                'route' => $route,
                'image_path' => $imagePath,
                'is_topseller' => $game->is_topseller,
                'is_newproduct' => $game->is_newproduct,
                'is_giftcard' => $game->is_giftcard,
            ];
        });
        
        return response()->json([
            'success' => true,
            'results' => $results,
            'count' => $results->count()
        ]);
    }
    
    /**
     * Get display name for game type
     */
    private function getGameDisplayName($gameType)
    {
        $gameNames = [
            'mobilelegends' => 'Mobile Legends',
            'freefire' => 'Free Fire',
            'pubgmobile' => 'PUBG Mobile',
            'honorofkings' => 'Honor of Kings',
            'bloodstrike' => 'Blood Strike',
        ];
        
        return $gameNames[$gameType] ?? ucfirst(str_replace('_', ' ', $gameType));
    }
    
    /**
     * Get route for game type
     */
    private function getGameRoute($gameType)
    {
        // Standard games with named routes
        if (in_array($gameType, ['mobilelegends', 'freefire', 'pubgmobile', 'honorofkings', 'bloodstrike', 'steam_giftcard'])) {
            return route($gameType);
        }
        
        // Special case: Genshin Impact - route to /genshin_impact regardless of variant
        if (strpos($gameType, 'genshin_impact') === 0) {
            return url('/genshin_impact');
        }
        
        // Default: use game_type as URL path
        return url('/' . $gameType);
    }
    
    /**
     * Find game image in top4gamers_images folder
     * Tries multiple matching strategies based on game_type and game name
     */
    private function findGameImage($gameType, $gameName = null)
    {
        // Check public/storage first (symlinked path), then storage/app/public
        $top4gamersDir = public_path('storage/top4gamers_images');
        if (!is_dir($top4gamersDir)) {
            $top4gamersDir = storage_path('app/public/top4gamers_images');
        }
        
        if (!is_dir($top4gamersDir)) {
            return null;
        }
        
        $gameTypeLower = strtolower($gameType);
        $extensions = ['.webp', '.jpg', '.jpeg', '.png'];
        
        // Special case: Delta Force - check for delta-force-coin.webp specifically
        if (stripos($gameName ?? '', 'delta force') !== false || $gameTypeLower === 'deltaforce' || stripos($gameTypeLower, 'delta') !== false) {
            foreach ($extensions as $ext) {
                $deltaForcePath = $top4gamersDir . '/delta-force-coin' . $ext;
                if (file_exists($deltaForcePath)) {
                    return 'storage_public/top4gamers_images/delta-force-coin' . $ext;
                }
            }
            // Also check without numbered prefix
            try {
                $files = scandir($top4gamersDir);
                foreach ($files as $file) {
                    if ($file === '.' || $file === '..' || is_dir($top4gamersDir . '/' . $file)) {
                        continue;
                    }
                    $fileLower = strtolower($file);
                    if (strpos($fileLower, 'delta-force-coin') !== false) {
                        return 'storage_public/top4gamers_images/' . $file;
                    }
                }
            } catch (\Exception $e) {
                // Continue to other strategies
            }
        }
        
        // Strategy 0: Try numbered prefix with game_type (e.g., 02_mobile-legends.webp, 04_free-fire-icon.webp)
        // Build game type variations for matching
        $gameTypeVariations = [];
        
        // Add standard variations
        if ($gameTypeLower === 'freefire') {
            $gameTypeVariations[] = 'free-fire';
            $gameTypeVariations[] = 'free_fire';
        } elseif ($gameTypeLower === 'mobilelegends') {
            $gameTypeVariations[] = 'mobile-legends';
            $gameTypeVariations[] = 'mobile_legends';
        } elseif ($gameTypeLower === 'pubgmobile') {
            $gameTypeVariations[] = 'pubg-mobile';
            $gameTypeVariations[] = 'pubg_mobile';
        } elseif ($gameTypeLower === 'honorofkings') {
            $gameTypeVariations[] = 'honor-of-kings';
            $gameTypeVariations[] = 'honor_of_kings';
        } elseif ($gameTypeLower === 'bloodstrike') {
            $gameTypeVariations[] = 'blood-strike';
            $gameTypeVariations[] = 'blood_strike';
        }
        
        // Always add the original game_type
        $gameTypeVariations[] = $gameTypeLower;
        
        // Scan directory once for numbered prefix patterns
        try {
            $files = scandir($top4gamersDir);
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || is_dir($top4gamersDir . '/' . $file)) {
                    continue;
                }
                
                $fileLower = strtolower($file);
                
                // Check if file starts with 2 digits followed by underscore
                if (!preg_match('/^\d{2}_/', $fileLower)) {
                    continue;
                }
                
                // Remove the numbered prefix for matching
                $fileWithoutPrefix = preg_replace('/^\d{2}_/', '', $fileLower);
                
                // Check if file matches any game type variation (with or without icon suffix)
                foreach ($gameTypeVariations as $variation) {
                    $variationLower = strtolower($variation);
                    // Check for exact match or match with icon suffix
                    if ($fileWithoutPrefix === $variationLower . '.webp' ||
                        $fileWithoutPrefix === $variationLower . '.jpg' ||
                        $fileWithoutPrefix === $variationLower . '.png' ||
                        $fileWithoutPrefix === $variationLower . '-icon.webp' ||
                        $fileWithoutPrefix === $variationLower . '-icon.jpg' ||
                        $fileWithoutPrefix === $variationLower . '-icon.png' ||
                        $fileWithoutPrefix === $variationLower . '_icon.webp' ||
                        $fileWithoutPrefix === $variationLower . '_icon.jpg' ||
                        $fileWithoutPrefix === $variationLower . '_icon.png' ||
                        strpos($fileWithoutPrefix, $variationLower . '-') === 0 ||
                        strpos($fileWithoutPrefix, $variationLower . '_') === 0) {
                        return 'storage_public/top4gamers_images/' . $file;
                    }
                }
            }
        } catch (\Exception $e) {
            // If scanning fails, continue to other strategies
        }
        
        // Strategy 1: Try exact game_type match (e.g., arena_breakout.webp)
        foreach ($extensions as $ext) {
            $testPath = $top4gamersDir . '/' . $gameTypeLower . $ext;
            if (file_exists($testPath)) {
                return 'storage_public/top4gamers_images/' . $gameTypeLower . $ext;
            }
        }
        
        // Strategy 2: Try normalized game name (e.g., "Arena Breakout" -> "arena_breakout.webp")
        if ($gameName) {
            // First try with hyphens (more common in filenames)
            $hyphenName = strtolower(preg_replace('/[^a-z0-9]+/', '-', trim($gameName)));
            
            // Strategy 2a: Try hyphenated name with "icon" suffix first (e.g., "arena-of-valor-icon.webp")
            foreach ($extensions as $ext) {
                $testPath = $top4gamersDir . '/' . $hyphenName . '-icon' . $ext;
                if (file_exists($testPath)) {
                    return 'storage_public/top4gamers_images/' . $hyphenName . '-icon' . $ext;
                }
            }
            
            // Strategy 2b: Try hyphenated name without suffix
            foreach ($extensions as $ext) {
                $testPath = $top4gamersDir . '/' . $hyphenName . $ext;
                if (file_exists($testPath)) {
                    return 'storage_public/top4gamers_images/' . $hyphenName . $ext;
                }
            }
            
            // Strategy 2c: Try with underscores
            $normalizedName = strtolower(preg_replace('/[^a-z0-9]+/', '_', trim($gameName)));
            foreach ($extensions as $ext) {
                $testPath = $top4gamersDir . '/' . $normalizedName . $ext;
                if (file_exists($testPath)) {
                    return 'storage_public/top4gamers_images/' . $normalizedName . $ext;
                }
            }
            
            // Strategy 3: Try without underscores (e.g., "arenabreakout.webp")
            $noUnderscore = str_replace('_', '', $normalizedName);
            foreach ($extensions as $ext) {
                $testPath = $top4gamersDir . '/' . $noUnderscore . $ext;
                if (file_exists($testPath)) {
                    return 'storage_public/top4gamers_images/' . $noUnderscore . $ext;
                }
            }
            
            // Strategy 4: Try with spaces replaced (e.g., "arena breakout.webp" -> "arena_breakout.webp")
            $spaceReplaced = strtolower(str_replace(' ', '_', trim($gameName)));
            foreach ($extensions as $ext) {
                $testPath = $top4gamersDir . '/' . $spaceReplaced . $ext;
                if (file_exists($testPath)) {
                    return 'storage_public/top4gamers_images/' . $spaceReplaced . $ext;
                }
            }
        }
        
        // Strategy 5: Get all files and try fuzzy matching on filenames
        try {
            $files = scandir($top4gamersDir);
            $gameTypeWords = explode('_', $gameTypeLower);
            $gameNameWords = $gameName ? array_filter(explode(' ', strtolower($gameName))) : [];
            
            // Filter out common words like "of", "the", "a", "an" and keep only meaningful words
            $gameNameWords = array_filter($gameNameWords, function($word) {
                return strlen($word) > 2 && !in_array($word, ['of', 'the', 'and', 'a', 'an']);
            });
            
            // Special case: For "Arena of Valor", require both "arena" AND "valor" to be present
            $isArenaOfValor = false;
            if ($gameName && stripos($gameName, 'arena') !== false && stripos($gameName, 'valor') !== false) {
                $isArenaOfValor = true;
            }
            
            $bestMatch = null;
            $bestMatchScore = 0;
            
            foreach ($files as $file) {
                if ($file === '.' || $file === '..' || is_dir($top4gamersDir . '/' . $file)) {
                    continue;
                }
                
                $fileLower = strtolower(pathinfo($file, PATHINFO_FILENAME));
                
                // Remove common prefixes like "12_", "01_", etc. for matching
                $fileLowerClean = preg_replace('/^\d+[_-]/', '', $fileLower);
                
                $nameMatches = 0;
                $typeMatches = 0;
                $allNameWordsMatch = true;
                
                // Check game name words
                foreach ($gameNameWords as $word) {
                    $wordInFile = (strpos($fileLower, $word) !== false || strpos($fileLowerClean, $word) !== false);
                    if ($wordInFile) {
                        $nameMatches++;
                    } else {
                        $allNameWordsMatch = false;
                    }
                }
                
                // Special handling for Arena of Valor - must have both "arena" and "valor"
                if ($isArenaOfValor) {
                    $hasArena = stripos($fileLower, 'arena') !== false;
                    $hasValor = stripos($fileLower, 'valor') !== false;
                    if (!$hasArena || !$hasValor) {
                        continue; // Skip this file if it doesn't have both words
                    }
                }
                
                // Check game type words
                foreach ($gameTypeWords as $word) {
                    if (strlen($word) > 2 && (strpos($fileLower, $word) !== false || strpos($fileLowerClean, $word) !== false)) {
                        $typeMatches++;
                    }
                }
                
                // Calculate match score - prioritize files where ALL name words match, but also accept partial matches
                $matchScore = $nameMatches;
                if ($allNameWordsMatch && count($gameNameWords) > 0) {
                    $matchScore += 10; // Big bonus for matching all words
                }
                
                // For Arena of Valor with both words, return immediately (highest priority)
                if ($isArenaOfValor && stripos($fileLower, 'arena') !== false && stripos($fileLower, 'valor') !== false) {
                    if ($nameMatches >= 2) {
                        return 'storage_public/top4gamers_images/' . $file;
                    }
                }
                
                // For other games, accept matches if at least one word matches (but prefer more matches)
                $minMatches = $isArenaOfValor ? 2 : 1; // Arena of Valor needs 2, others need 1
                if ($nameMatches >= $minMatches && $matchScore > $bestMatchScore) {
                    $bestMatch = $file;
                    $bestMatchScore = $matchScore;
                }
            }
            
            // Return best match if found
            if ($bestMatch && $bestMatchScore > 0) {
                return 'storage_public/top4gamers_images/' . $bestMatch;
            }
        } catch (\Exception $e) {
            // If scanning fails, just return null
        }
        
        return null;
    }

    /**
     * Get flag emoji for region code
     */
    private function getRegionFlag($regionCode)
    {
        $flags = [
            'free' => '🌍',
            'us' => '🇺🇸',
            'br' => '🇧🇷',
            'cn' => '🇨🇳',
            'eu' => '🇪🇺',
            'gb' => '🇬🇧',
            'ae' => '🇦🇪',
            'hk' => '🇭🇰',
            'tw' => '🇹🇼',
            'vn' => '🇻🇳',
            'th' => '🇹🇭',
            'ph' => '🇵🇭',
            'sg' => '🇸🇬',
            'id' => '🇮🇩',
            'in' => '🇮🇳',
            'kw' => '🇰🇼',
            'qa' => '🇶🇦',
            'sa' => '🇸🇦',
            'za' => '🇿🇦',
            'ua' => '🇺🇦',
            'tr' => '🇹🇷',
            'cr' => '🇨🇷',
            'pe' => '🇵🇪',
            'uy' => '🇺🇾',
        ];
        return $flags[$regionCode] ?? '🌍';
    }

    /**
     * Get region name for region code
     */
    private function getRegionName($regionCode)
    {
        $names = [
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
        return $names[$regionCode] ?? ucfirst(str_replace('_', ' ', $regionCode));
    }

    public function gameTopUp($gameType)
    {
        // Reusable game top-up page
        // For genshin_impact, load all variants (e.g., genshin_impact, genshin_impact_genesis_crystals)
        $packQuery = DiamondPack::where('is_active', true);
        if ($gameType === 'genshin_impact') {
            $packQuery->where(function($query) {
                $query->where('game_type', 'genshin_impact')
                      ->orWhere('game_type', 'like', 'genshin_impact%');
            });
        } else {
            $packQuery->where('game_type', $gameType);
        }
        $packs = $packQuery->orderBy('sort_order')
            ->orderBy('price')
            ->get();

        // For Steam Gift Cards, get available regions for filtering (only EU, USA, and Global)
        $availableRegions = collect([]);
        if ($gameType === 'steam_giftcard' && $packs->isNotEmpty()) {
            $allowedRegions = ['eu', 'us', 'free']; // Only Europe, USA, and Global
            $availableRegions = $packs->whereNotNull('region')
                ->whereIn('region', $allowedRegions)
                ->groupBy('region')
                ->keys()
                ->map(function($regionCode) {
                    return [
                        'code' => $regionCode,
                        'name' => $this->getRegionName($regionCode),
                        'flag' => $this->getRegionFlag($regionCode)
                    ];
                })
                ->sortBy(function($region) {
                    // Sort: Global first, then USA, then Europe
                    $order = ['free' => 0, 'us' => 1, 'eu' => 2];
                    return $order[$region['code']] ?? 99;
                })
                ->values();
        }

        // Try to get game info from games table with content and images
        // For genshin_impact, try exact match first, then any variant
        $gameQuery = Game::where('is_active', true);
        if ($gameType === 'genshin_impact') {
            $gameQuery->where(function($query) {
                $query->where('game_type', 'genshin_impact')
                      ->orWhere('game_type', 'like', 'genshin_impact%');
            })->orderByRaw("CASE WHEN game_type = 'genshin_impact' THEN 0 ELSE 1 END");
        } else {
            $gameQuery->where('game_type', $gameType);
        }
        $game = $gameQuery
            ->with(['content', 'images' => function($query) {
                $query->orderBy('display_order');
            }])
            ->first();

        // Get game title and image dynamically
        if ($game) {
            // Extract game name from game record
            $gameName = $game->name;
            if (strpos($gameName, ' - ') !== false) {
                $gameTitle = explode(' - ', $gameName)[0];
            } elseif (preg_match('/^\d+/', $gameName) || preg_match('/\d+\s*\+?\s*\d+/', $gameName)) {
                $gameTitle = $this->getGameDisplayName($gameType);
            } else {
                $gameTitle = $gameName;
            }
            
            $gameImage = $this->findGameImage($gameType, $game->name);
        } else {
            // Fallback to pack-based extraction
        $gameTitles = [
            'mobilelegends' => 'Mobile Legends',
            'freefire' => 'Free Fire',
            'pubgmobile' => 'PUBG Mobile',
            'honorofkings' => 'Honor of Kings',
            'bloodstrike' => 'Blood Strike',
            'steam_giftcard' => 'Steam Gift Cards',
        ];

            if ($packs->isNotEmpty() && $packs->first()->game_type) {
                $firstPackName = $packs->first()->name ?? '';
                if ($firstPackName && strpos($firstPackName, ' - ') !== false) {
                    $gameTitle = explode(' - ', $firstPackName)[0];
                } else {
                    $gameTitle = $gameTitles[$gameType] ?? $this->getGameDisplayName($gameType);
                }
            } else {
                $gameTitle = $gameTitles[$gameType] ?? $this->getGameDisplayName($gameType);
            }
            
            $gameImage = $this->findGameImage($gameType, $gameTitle);
        }

        // Load reviews for this game if game exists
        $reviews = collect([]);
        $averageRating = 0;
        $totalReviews = 0;
        
        if ($game) {
            $reviews = $game->reviews()->latest()->get();
            $totalReviews = $reviews->count();
            if ($totalReviews > 0) {
                $averageRating = round($reviews->avg('rating'), 1);
            }
        }

        // Pass helper functions to view for region flags/names
        view()->share('getRegionFlag', [$this, 'getRegionFlag']);
        view()->share('getRegionName', [$this, 'getRegionName']);
        
        return view('pages.game-topup', compact('packs', 'gameType', 'gameTitle', 'gameImage', 'game', 'reviews', 'averageRating', 'totalReviews', 'availableRegions'));
    }

    public function mobileLegends()
    {
        // Mobile Legends page (old home page) - redirect to new structure
        return $this->gameTopUp('mobilelegends');
    }

    public function about()
    {
        return view('pages.about');
    }

    public function termsOfUse()
    {
        return view('pages.terms-of-use');
    }

    public function privacyPolicy()
    {
        return view('pages.privacy-policy');
    }

    public function contact()
    {
        return view('pages.contact');
    }

    public function contactSubmit(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        // Here you can add logic to send email, save to database, etc.
        // For now, we'll just return a success response
        
        // TODO: Implement email sending or database storage
        // Example: Mail::to('support@diaszone.com')->send(new ContactFormMail($request->all()));
        
        return response()->json([
            'success' => true,
            'message' => 'Thank you for contacting us! We have received your message and will get back to you soon.'
        ]);
    }

    /**
     * Submit a review for a game
     */
    public function submitReview(Request $request)
    {
        // Rate limiting: max 2 reviews per 10 minutes per user (session-based)
        $gameId = $request->input('game_id');
        $sessionId = $request->session()->getId();

        // Create unique key for this user (session) and game combination
        $key = 'review-submission:' . $sessionId . ':' . ($gameId ?? 'all');

        // Check rate limit (2 reviews per 10 minutes = 600 seconds)
        $maxAttempts = 2;
        $decaySeconds = 600; // 10 minutes

        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            $minutes = ceil($seconds / 60);

            return response()->json([
                'success' => false,
                'message' => "You've submitted too many reviews. Please wait {$minutes} minute(s) before submitting another review.",
            ], 429); // HTTP 429 Too Many Requests
        }

        // Strict validation to prevent XSS, SQL injection, and other attacks
        $validated = $request->validate([
            'game_id' => 'required|integer|exists:games,id',
            'name' => [
                'required',
                'string',
                'max:100',
                'regex:/^[a-zA-Z0-9\s\-_\.]+$/', // Only alphanumeric, spaces, hyphens, underscores, dots
            ],
            'comment' => [
                'required',
                'string',
                'min:5',
                'max:1000',
            ],
            'rating' => 'required|integer|min:1|max:5', // 1-5 star rating
        ], [
            'game_id.required' => 'Game ID is required.',
            'game_id.exists' => 'Invalid game selected.',
            'name.required' => 'Name is required.',
            'name.max' => 'Name must not exceed 100 characters.',
            'name.regex' => 'Name contains invalid characters.',
            'comment.required' => 'Comment is required.',
            'comment.min' => 'Comment must be at least 5 characters.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
            'rating.required' => 'Rating is required.',
            'rating.min' => 'Rating must be at least 1.',
            'rating.max' => 'Rating must not exceed 5.',
        ]);

        try {
            // Sanitize input: strip HTML tags to prevent XSS
            // Store clean data, Blade will escape it when displaying with {{ }}
            $name = strip_tags(trim($validated['name']));
            $comment = strip_tags(trim($validated['comment']));
            
            // Verify game exists and is active
            $game = Game::where('id', $validated['game_id'])
                ->where('is_active', true)
                ->firstOrFail();

            // Create review (data will be escaped by Blade when displayed)
            $review = Review::create([
                'game_id' => $game->id,
                'name' => $name,
                'comment' => $comment,
                'rating' => (int) $validated['rating'],
            ]);

            // Hit rate limiter after successful submission
            RateLimiter::hit($key, $decaySeconds);

            // Get updated reviews and rating for response
            $reviews = $game->reviews()->latest()->take(3)->get();
            $totalReviews = $game->reviews()->count();
            $averageRating = $totalReviews > 0 ? round($game->reviews()->avg('rating'), 1) : 5.0;

            return response()->json([
                'success' => true,
                'message' => 'Review submitted successfully!',
                'review' => [
                    'id' => $review->id,
                    'name' => $review->name,
                    'comment' => $review->comment,
                    'rating' => $review->rating,
                    'created_at' => $review->created_at->diffForHumans(),
                ],
                'averageRating' => $averageRating,
                'totalReviews' => $totalReviews,
                'reviews' => $reviews->map(function($r) {
                    return [
                        'id' => $r->id,
                        'name' => $r->name,
                        'comment' => $r->comment,
                        'rating' => $r->rating,
                        'created_at' => $r->created_at->diffForHumans(),
                    ];
                }),
            ], 201);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Game not found or inactive.',
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Review submission error', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while submitting your review. Please try again.',
            ], 500);
        }
    }

    /**
     * Get reviews and rating for a game via AJAX
     */
    public function getGameReviews(Request $request, $gameId)
    {
        try {
            $game = Game::where('id', $gameId)
                ->where('is_active', true)
                ->firstOrFail();

            $reviews = $game->reviews()->latest()->take(3)->get();
            $totalReviews = $game->reviews()->count();
            $averageRating = $totalReviews > 0 ? round($game->reviews()->avg('rating'), 1) : 5.0;

            return response()->json([
                'success' => true,
                'averageRating' => $averageRating,
                'totalReviews' => $totalReviews,
                'reviews' => $reviews->map(function($review) {
                    return [
                        'id' => $review->id,
                        'name' => $review->name,
                        'comment' => $review->comment,
                        'rating' => $review->rating,
                        'created_at' => $review->created_at->diffForHumans(),
                    ];
                }),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load reviews.',
            ], 404);
        }
    }
}