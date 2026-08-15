<?php

namespace App\Filament\Resources\WheelEvents\Schemas;

use App\Models\DiamondPack;
use App\Models\WheelEvent;
use App\Services\WheelQualificationService;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class WheelEventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event details')
                    ->description('The wheel only runs for Mobile Legends, inside the window you define here.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Event name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        Select::make('game_type')
                            ->label('Game')
                            ->options([WheelQualificationService::GAME_TYPE => 'Mobile Legends'])
                            ->default(WheelQualificationService::GAME_TYPE)
                            ->selectablePlaceholder(false)
                            ->required(),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('The wheel only appears on the game page while this is on and the current time is inside the window.')
                            ->default(true)
                            ->inline(false)
                            ->rules([
                                fn (Get $get, ?WheelEvent $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    if (! $value) {
                                        return;
                                    }

                                    $overlaps = WheelEvent::query()
                                        ->where('game_type', $get('game_type') ?: WheelQualificationService::GAME_TYPE)
                                        ->where('is_active', true)
                                        ->when($record, fn ($query) => $query->whereKeyNot($record->getKey()))
                                        ->where('starts_at', '<', $get('ends_at'))
                                        ->where('ends_at', '>', $get('starts_at'))
                                        ->exists();

                                    if ($overlaps) {
                                        $fail('Another active event already overlaps this date range.');
                                    }
                                },
                            ]),

                        DateTimePicker::make('starts_at')
                            ->label('Starts at')
                            ->seconds(false)
                            ->required(),

                        DateTimePicker::make('ends_at')
                            ->label('Ends at')
                            ->seconds(false)
                            ->required()
                            ->after('starts_at'),

                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),

                        FileUpload::make('background_path')
                            ->label('Event background')
                            ->helperText('Artwork shown behind the wheel page. Landscape images work best. Leave empty to use the default background.')
                            ->disk('public')
                            ->directory('event-backgrounds')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('160')
                            ->maxSize(6144)
                            ->columnSpanFull(),
                    ]),

                Section::make('Rewards')
                    ->description('Milestones are unlocked in order. Progress carries over between events and never resets.')
                    ->schema([
                        Repeater::make('rewards')
                            ->relationship()
                            ->hiddenLabel()
                            ->addActionLabel('Add reward milestone')
                            ->orderColumn('sort_order')
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->cloneable()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->itemLabel(fn (array $state): ?string => static::rewardItemLabel($state))
                            ->columns(2)
                            ->mutateRelationshipDataBeforeCreateUsing(fn (array $data): array => static::normalizeReward($data))
                            ->mutateRelationshipDataBeforeSaveUsing(fn (array $data): array => static::normalizeReward($data))
                            ->schema([
                                TextInput::make('label')
                                    ->label('Reward label')
                                    ->placeholder('55 Diamonds')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('draws_required')
                                    ->label('Draws required')
                                    ->helperText('Spins needed after the previous milestone. Players never see this number.')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100000)
                                    ->required(),

                                Select::make('reward_type')
                                    ->label('Reward type')
                                    ->options([
                                        'diamond_pack' => 'Diamond pack (claim code)',
                                        'discount' => 'Discount coupon (%)',
                                    ])
                                    ->default('diamond_pack')
                                    ->selectablePlaceholder(false)
                                    ->live()
                                    ->required(),

                                Toggle::make('is_active')
                                    ->label('Active')
                                    ->default(true)
                                    ->inline(false),

                                FileUpload::make('image_paths')
                                    ->label('Reward images (optional)')
                                    ->helperText('The first image is cropped onto the wheel slice. Any extra images only show in the popup gallery when a player taps that slice — useful for game account screenshots. Drag to reorder. If empty, the Mobile Legends pack icon is chosen automatically.')
                                    ->disk('public')
                                    ->directory('wheel-reward-icons')
                                    ->visibility('public')
                                    ->image()
                                    ->imageEditor()
                                    ->imagePreviewHeight('140')
                                    ->multiple()
                                    ->reorderable()
                                    ->appendFiles()
                                    ->openable()
                                    ->maxFiles(8)
                                    ->maxSize(4096)
                                    ->columnSpanFull(),

                                Select::make('diamond_pack_id')
                                    ->label('Diamond pack')
                                    ->options(fn () => static::packOptions())
                                    ->searchable()
                                    ->visible(fn (Get $get): bool => $get('reward_type') === 'diamond_pack')
                                    ->required(fn (Get $get): bool => $get('reward_type') === 'diamond_pack')
                                    ->columnSpanFull(),

                                TextInput::make('discount_percentage')
                                    ->label('Discount percentage')
                                    ->helperText('Shown on the reward track and applied to the coupon. It is never printed on the wheel itself.')
                                    ->suffix('%')
                                    ->numeric()
                                    ->minValue(1)
                                    ->maxValue(100)
                                    ->visible(fn (Get $get): bool => $get('reward_type') === 'discount')
                                    ->required(fn (Get $get): bool => $get('reward_type') === 'discount'),

                                Select::make('eligiblePacks')
                                    ->label('Offers the discount applies to')
                                    ->relationship('eligiblePacks', 'name')
                                    ->options(fn () => static::packOptions())
                                    ->multiple()
                                    ->preload()
                                    ->searchable()
                                    ->visible(fn (Get $get): bool => $get('reward_type') === 'discount')
                                    ->required(fn (Get $get): bool => $get('reward_type') === 'discount')
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    public static function packOptions(): array
    {
        return DiamondPack::query()
            ->where('game_type', WheelQualificationService::GAME_TYPE)
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('price')
            ->pluck('name', 'id')
            ->all();
    }

    /**
     * Keep pack and discount fields mutually exclusive so a reward can never carry both payloads.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected static function normalizeReward(array $data): array
    {
        $type = $data['reward_type'] ?? 'diamond_pack';

        if ($type === 'discount') {
            $data['diamond_pack_id'] = null;
        } else {
            $data['discount_percentage'] = null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $state
     */
    protected static function rewardItemLabel(array $state): ?string
    {
        $label = $state['label'] ?? null;

        if (blank($label)) {
            return null;
        }

        $draws = $state['draws_required'] ?? null;

        return filled($draws)
            ? "{$label} — {$draws} draws"
            : $label;
    }
}
