<?php

namespace App\Filament\Resources\HeroSlides\Schemas;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class HeroSlideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Hero slide')
                    ->description('Image-only coverflow slide. Optional link opens when the slide is clicked.')
                    ->schema([
                        Select::make('placement')
                            ->label('Page')
                            ->options([
                                'home' => 'Home',
                                'digital' => 'Digital',
                            ])
                            ->default('home')
                            ->required()
                            ->native(false),

                        TextInput::make('title')
                            ->label('Alt / admin label')
                            ->helperText('Used for accessibility and the admin list. Not shown as overlay text on the homepage.')
                            ->maxLength(255),

                        FileUpload::make('image_path')
                            ->label('Slide image')
                            ->helperText('Recommended ~1800×770 (desktop) or similar wide banner. Shown coverflow on home or digital.')
                            ->disk('public')
                            ->directory('hero-slides')
                            ->visibility('public')
                            ->image()
                            ->imageEditor()
                            ->imagePreviewHeight('180')
                            ->maxSize(5120)
                            ->required(),

                        TextInput::make('link_url')
                            ->label('Click link')
                            ->helperText('Internal path (/freefire) or full URL (https://...). Leave empty for no navigation.')
                            ->placeholder('/mobilelegends')
                            ->maxLength(500),

                        TextInput::make('sort_order')
                            ->label('Sort order')
                            ->numeric()
                            ->integer()
                            ->default(0),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false),
                    ]),
            ]);
    }
}
