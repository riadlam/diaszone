<?php

namespace App\Support;

class GameProvider
{
    public static function digiflazzGames(): array
    {
        return config('services.digiflazz_games', [
            'mobilelegends',
            'freefire',
            'pubg_mobile',
            'pubgmobile',
            'genshin_impact',
            'bloodstrike',
            'honorofkings',
            'punishinggrayraven',
            'wutheringwaves',
        ]);
    }

    public static function usesDigiflazz(?string $gameType): bool
    {
        $gameType = strtolower(trim((string) $gameType));
        if ($gameType === '') {
            return false;
        }

        foreach (self::digiflazzGames() as $digiGame) {
            if ($gameType === $digiGame || str_starts_with($gameType, $digiGame)) {
                return true;
            }
        }

        return false;
    }

    public static function usesItem4Gamer(?string $gameType): bool
    {
        return ! self::usesDigiflazz($gameType);
    }

    public static function item4gamerPurchasesEnabled(): bool
    {
        return (bool) config('services.item4gamer.purchases_enabled', false);
    }

    /**
     * Item4Gamer products that should appear as Not Available and block checkout.
     */
    public static function isItem4GamerUnavailable(?string $gameType): bool
    {
        return self::usesItem4Gamer($gameType) && ! self::item4gamerPurchasesEnabled();
    }
}
