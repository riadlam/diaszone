<?php

namespace App\Filament\Resources\Giveaways\Schemas;

use App\Models\Giveaway;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
class GiveawayForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Campaign')
                    ->description('Facebook giveaway powered by the website. Only one active campaign should cover the current time.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('title')
                            ->label('Title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, ?Giveaway $record) {
                                if ($record || blank($state)) {
                                    return;
                                }
                                $set('slug', Giveaway::uniqueSlugFromTitle($state));
                            })
                            ->columnSpanFull(),

                        TextInput::make('slug')
                            ->label('Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL: /giveaway/{slug}'),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->helperText('Public page only shows this while active and inside the date window.')
                            ->default(true)
                            ->inline(false)
                            ->rules([
                                fn (Get $get, ?Giveaway $record) => function (string $attribute, $value, \Closure $fail) use ($get, $record) {
                                    if (! $value) {
                                        return;
                                    }

                                    $starts = $get('starts_at');
                                    $ends = $get('ends_at');
                                    if (! $starts || ! $ends) {
                                        return;
                                    }

                                    $overlaps = Giveaway::query()
                                        ->where('is_active', true)
                                        ->when($record, fn ($q) => $q->whereKeyNot($record->getKey()))
                                        ->where('starts_at', '<', $ends)
                                        ->where('ends_at', '>', $starts)
                                        ->exists();

                                    if ($overlaps) {
                                        $fail('Another active giveaway already overlaps this date range.');
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

                        TextInput::make('facebook_post_url')
                            ->label('Facebook post URL')
                            ->url()
                            ->required()
                            ->columnSpanFull(),

                        TextInput::make('facebook_page_url')
                            ->label('Facebook page URL')
                            ->url()
                            ->required()
                            ->columnSpanFull(),

                        Textarea::make('description')
                            ->label('Short description')
                            ->rows(3)
                            ->columnSpanFull(),

                        Textarea::make('instructions')
                            ->label('Extra instructions')
                            ->helperText('Optional notes shown under the steps.')
                            ->rows(4)
                            ->columnSpanFull(),

                        FileUpload::make('hero_image')
                            ->label('Hero image')
                            ->disk('public')
                            ->directory('giveaway-heroes')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('160')
                            ->maxSize(6144)
                            ->helperText('Optional. Leave empty to use the default gift illustration.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
