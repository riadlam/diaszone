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

    public static function isPubgMobile(?string $gameType): bool
    {
        return in_array(strtolower(trim((string) $gameType)), ['pubgmobile', 'pubg_mobile'], true);
    }

    public static function isFreeFire(?string $gameType): bool
    {
        return strtolower(trim((string) $gameType)) === 'freefire';
    }

    public static function isGenshinImpact(?string $gameType): bool
    {
        $gameType = strtolower(trim((string) $gameType));

        return $gameType === 'genshin_impact' || str_starts_with($gameType, 'genshin_impact');
    }

    /**
     * Digiflazz Genshin desc: Format no tujuan [UID]|[Server].
     * Map form / os_* aliases to the seller labels our catalog uses.
     */
    public static function normalizeGenshinServer(?string $server): ?string
    {
        $server = trim((string) $server);
        if ($server === '') {
            return null;
        }

        $key = strtolower(str_replace([' ', '-', ',', '_'], ['', '', '', ''], $server));

        return match ($key) {
            'asia', 'osasia' => 'Asia',
            'america', 'amerika', 'na', 'osusa', 'usa' => 'America',
            'europe', 'europa', 'eu', 'oseuro', 'euro' => 'Europe',
            'twhkmo', 'tw', 'hk', 'mo', 'oscht', 'cht', 'taiwanhongkongmacao', 'taiwanhongkongmacau' => 'TW, HK, MO',
            default => $server,
        };
    }

    /**
     * Canonical game_type values used across routes / packs for one product.
     *
     * @return list<string>
     */
    public static function aliases(?string $gameType): array
    {
        if (self::isPubgMobile($gameType)) {
            return ['pubgmobile', 'pubg_mobile'];
        }

        if (self::isGenshinImpact($gameType)) {
            return ['genshin_impact', 'genshin_impact_genesis_crystals'];
        }

        $gameType = strtolower(trim((string) $gameType));

        return $gameType === '' ? [] : [$gameType];
    }

    public static function usesItem4Gamer(?string $gameType): bool
    {
        return ! self::usesDigiflazz($gameType);
    }

    /**
     * Restrict a query to the game types Digiflazz fulfils, mirroring the
     * exact-or-prefix matching used by usesDigiflazz().
     */
    public static function scopeDigiflazz(\Illuminate\Database\Eloquent\Builder $query, string $column = 'game_type'): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where(function ($inner) use ($column): void {
            foreach (self::digiflazzGames() as $gameType) {
                $inner->orWhere($column, $gameType)
                    ->orWhere($column, 'like', $gameType.'%');
            }
        });
    }

    public static function label(?string $gameType): string
    {
        $gameType = strtolower(trim((string) $gameType));

        if (self::isGenshinImpact($gameType)) {
            return 'Genshin Impact';
        }
        if (self::isPubgMobile($gameType)) {
            return 'PUBG Mobile';
        }

        $names = [
            'mobilelegends' => 'Mobile Legends',
            'freefire' => 'Free Fire',
            'bloodstrike' => 'Blood Strike',
            'honorofkings' => 'Honor of Kings',
            'punishinggrayraven' => 'Punishing: Gray Raven',
            'wutheringwaves' => 'Wuthering Waves',
            'vipreseller' => 'Digital',
        ];

        return $names[$gameType] ?? ucwords(str_replace('_', ' ', $gameType));
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
