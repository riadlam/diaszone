<?php

namespace App\Console\Commands;

use App\Models\WheelEvent;
use App\Services\WheelQualificationService;
use Illuminate\Console\Command;

class BackfillWheelSpins extends Command
{
    protected $signature = 'wheel:backfill {eventId? : Wheel event ID (defaults to all active/current events)}';

    protected $description = 'Backfill wheel spin credits from qualifying Digiflazz Mobile Legends top-ups';

    public function handle(WheelQualificationService $service): int
    {
        $eventId = $this->argument('eventId');

        $events = $eventId
            ? WheelEvent::where('id', $eventId)->get()
            : WheelEvent::forGame(WheelQualificationService::GAME_TYPE)->where('is_active', true)->get();

        if ($events->isEmpty()) {
            $this->warn('No wheel events found.');

            return self::FAILURE;
        }

        foreach ($events as $event) {
            $credited = $service->backfillEvent($event);
            $this->info("Event #{$event->id} ({$event->name}): credited {$credited} spins");
        }

        return self::SUCCESS;
    }
}
