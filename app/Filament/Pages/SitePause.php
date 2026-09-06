<?php

namespace App\Filament\Pages;

use App\Services\SitePauseService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class SitePause extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPauseCircle;

    protected static ?string $navigationLabel = 'Site pause';

    protected static ?string $title = 'Site pause';

    protected static ?string $slug = 'site-pause';

    protected static ?int $navigationSort = 99;

    protected string $view = 'filament.pages.site-pause';

    public function getPaused(): bool
    {
        return app(SitePauseService::class)->isPaused();
    }

    /**
     * @return array{paused_at: string|null, paused_by: string|null, message: string|null}
     */
    public function getStatus(): array
    {
        return app(SitePauseService::class)->status();
    }

    public function pauseSite(): void
    {
        app(SitePauseService::class)->pause();

        Notification::make()
            ->title('Website paused')
            ->body('Customers will see “We’ll be back soon” on all storefront pages. Admin stays available.')
            ->warning()
            ->send();
    }

    public function resumeSite(): void
    {
        app(SitePauseService::class)->resume();

        Notification::make()
            ->title('Website is live')
            ->body('Customers can browse and top up again.')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        $paused = $this->getPaused();

        return [
            Action::make('pause')
                ->label('Pause website')
                ->icon(Heroicon::OutlinedPause)
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Pause the website?')
                ->modalDescription('Every public page and checkout will show “We’ll be back soon”. The admin panel stays open.')
                ->modalSubmitActionLabel('Pause website')
                ->visible(! $paused)
                ->action(fn () => $this->pauseSite()),

            Action::make('resume')
                ->label('Go live')
                ->icon(Heroicon::OutlinedPlay)
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Take the website live?')
                ->modalDescription('Customers will be able to browse and place orders again.')
                ->modalSubmitActionLabel('Go live')
                ->visible($paused)
                ->action(fn () => $this->resumeSite()),
        ];
    }
}
