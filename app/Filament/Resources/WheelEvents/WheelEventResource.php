<?php

namespace App\Filament\Resources\WheelEvents;

use App\Filament\Resources\WheelEvents\Pages\CreateWheelEvent;
use App\Filament\Resources\WheelEvents\Pages\EditWheelEvent;
use App\Filament\Resources\WheelEvents\Pages\EventParticipants;
use App\Filament\Resources\WheelEvents\Pages\ListWheelEvents;
use App\Filament\Resources\WheelEvents\RelationManagers\ClaimsRelationManager;
use App\Filament\Resources\WheelEvents\Schemas\WheelEventForm;
use App\Filament\Resources\WheelEvents\Tables\WheelEventsTable;
use App\Models\WheelEvent;
use BackedEnum;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class WheelEventResource extends Resource
{
    protected static ?string $model = WheelEvent::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGift;

    protected static ?string $navigationLabel = 'Lucky Wheel';

    protected static ?string $modelLabel = 'wheel event';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return WheelEventForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WheelEventsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ClaimsRelationManager::class,
        ];
    }

    /**
     * @return array<\Filament\Navigation\NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        return $page->generateNavigationItems([
            EditWheelEvent::class,
            EventParticipants::class,
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWheelEvents::route('/'),
            'create' => CreateWheelEvent::route('/create'),
            'edit' => EditWheelEvent::route('/{record}/edit'),
            'participants' => EventParticipants::route('/{record}/participants'),
        ];
    }
}
