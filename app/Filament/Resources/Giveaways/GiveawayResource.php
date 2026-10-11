<?php

namespace App\Filament\Resources\Giveaways;

use App\Filament\Resources\Giveaways\Pages\CreateGiveaway;
use App\Filament\Resources\Giveaways\Pages\EditGiveaway;
use App\Filament\Resources\Giveaways\Pages\GiveawayEntries;
use App\Filament\Resources\Giveaways\Pages\ListGiveaways;
use App\Filament\Resources\Giveaways\Schemas\GiveawayForm;
use App\Filament\Resources\Giveaways\Tables\GiveawaysTable;
use App\Models\Giveaway;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GiveawayResource extends Resource
{
    protected static ?string $model = Giveaway::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Giveaways';

    protected static ?string $modelLabel = 'giveaway';

    protected static ?string $recordTitleAttribute = 'title';

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return GiveawayForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return GiveawaysTable::configure($table);
    }

    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditGiveaway::class,
            GiveawayEntries::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListGiveaways::route('/'),
            'create' => CreateGiveaway::route('/create'),
            'edit' => EditGiveaway::route('/{record}/edit'),
            'entries' => GiveawayEntries::route('/{record}/entries'),
        ];
    }
}
