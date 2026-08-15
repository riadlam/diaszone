<?php

namespace App\Filament\Resources\WheelEvents\Pages;

use App\Filament\Resources\WheelEvents\WheelEventResource;
use App\Models\WheelEvent;
use App\Services\WheelQualificationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditWheelEvent extends EditRecord
{
    protected static string $resource = WheelEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('backfill')
                ->label('Backfill spins')
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Scans qualifying Digiflazz top-ups inside the event window and credits any missing spins. Safe to run more than once.')
                ->action(function (WheelEvent $record): void {
                    $credited = app(WheelQualificationService::class)->backfillEvent($record);

                    Notification::make()
                        ->title($credited > 0 ? "Credited {$credited} spin(s)." : 'No missing spins found.')
                        ->success()
                        ->send();
                }),

            DeleteAction::make(),
        ];
    }
}
