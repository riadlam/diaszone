<?php

namespace App\Filament\Widgets;

use App\Services\SitePauseService;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;

class SitePauseWidget extends Widget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = 'full';

    protected string $view = 'filament.widgets.site-pause-widget';

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
            ->body('Customers will see “We’ll be back soon”. Admin stays available.')
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
}
