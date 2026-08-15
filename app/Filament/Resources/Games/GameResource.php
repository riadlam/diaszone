<?php

namespace App\Filament\Resources\Games;

use App\Filament\Resources\Games\Pages\GamePacks;
use App\Filament\Resources\Games\Pages\ListGames;
use App\Models\DiamondPack;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;

class GameResource extends Resource
{
    protected static ?string $model = DiamondPack::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPuzzlePiece;

    protected static ?string $navigationLabel = 'Games';

    protected static ?string $modelLabel = 'game';

    protected static ?string $pluralModelLabel = 'games';

    protected static ?string $slug = 'games';

    public static function getPages(): array
    {
        return [
            'index' => ListGames::route('/'),
            'packs' => GamePacks::route('/{gameType}/packs'),
        ];
    }
}
