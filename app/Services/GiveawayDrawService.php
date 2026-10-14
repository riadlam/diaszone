<?php

namespace App\Services;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GiveawayDrawService
{
    public const MAX_SEGMENTS = 12;

    /**
     * @return Collection<int, array{entry_id: int, name: string, initial: string}>
     */
    public function approvedParticipants(Giveaway $giveaway): Collection
    {
        return GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('status', GiveawayEntry::STATUS_APPROVED)
            ->with('user:id,name')
            ->orderBy('id')
            ->get()
            ->map(fn (GiveawayEntry $entry) => $this->serializeParticipant($entry))
            ->values();
    }

    /**
     * @return array{
     *     ok: bool,
     *     preview: bool,
     *     winner: array{entry_id: int, name: string, initial: string},
     *     segments: list<array{entry_id: int, name: string, initial: string}>,
     *     winner_index: int,
     *     message: string
     * }
     */
    public function spin(Giveaway $giveaway, bool $forceRedraw = false): array
    {
        $participants = $this->approvedParticipants($giveaway);
        if ($participants->isEmpty()) {
            throw new RuntimeException(__('giveaway.wheel_no_participants'));
        }

        $live = Giveaway::drawLiveEnabled();

        if ($live && $giveaway->hasWinner() && ! $forceRedraw) {
            throw new RuntimeException(__('giveaway.wheel_already_drawn'));
        }

        $winnerEntry = GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('status', GiveawayEntry::STATUS_APPROVED)
            ->with('user:id,name')
            ->inRandomOrder()
            ->first();

        if (! $winnerEntry) {
            throw new RuntimeException(__('giveaway.wheel_no_participants'));
        }

        $winner = $this->serializeParticipant($winnerEntry);
        $segments = $this->buildSegments($participants, $winner);
        $winnerIndex = collect($segments)->search(fn ($row) => (int) $row['entry_id'] === (int) $winner['entry_id']);
        if ($winnerIndex === false) {
            $winnerIndex = 0;
        }

        if ($live) {
            DB::transaction(function () use ($giveaway, $winnerEntry) {
                $locked = Giveaway::query()->whereKey($giveaway->id)->lockForUpdate()->first();
                if (! $locked) {
                    throw new RuntimeException(__('giveaway.wheel_error'));
                }
                $locked->forceFill([
                    'winner_entry_id' => $winnerEntry->id,
                    'winner_drawn_at' => now(),
                ])->save();
            });
            $giveaway->refresh();
        }

        return [
            'ok' => true,
            'preview' => ! $live,
            'winner' => $winner,
            'segments' => $segments,
            'winner_index' => (int) $winnerIndex,
            'message' => $live
                ? __('giveaway.wheel_winner_locked')
                : __('giveaway.wheel_preview_result'),
        ];
    }

    /**
     * @param  Collection<int, array{entry_id: int, name: string, initial: string}>  $participants
     * @param  array{entry_id: int, name: string, initial: string}  $winner
     * @return list<array{entry_id: int, name: string, initial: string}>
     */
    public function buildSegments(Collection $participants, array $winner): array
    {
        $pool = $participants
            ->reject(fn ($row) => (int) $row['entry_id'] === (int) $winner['entry_id'])
            ->shuffle()
            ->values();

        $slots = min(self::MAX_SEGMENTS, max(6, $participants->count()));
        $othersNeeded = max(0, $slots - 1);
        $segments = $pool->take($othersNeeded)->all();
        $segments[] = $winner;
        shuffle($segments);

        // Ensure uniqueness and pad if tiny pool by cycling names carefully.
        while (count($segments) < 6 && $participants->isNotEmpty()) {
            $segments[] = $participants[$participants->count() % max(1, count($segments))] ?? $winner;
            if (count($segments) > 20) {
                break;
            }
        }

        return array_values($segments);
    }

    /**
     * @return array{entry_id: int, name: string, initial: string}
     */
    public function serializeParticipant(GiveawayEntry $entry): array
    {
        $name = $entry->displayFirstName();

        return [
            'entry_id' => (int) $entry->id,
            'name' => $name,
            'initial' => mb_strtoupper(mb_substr($name, 0, 1)),
        ];
    }
}
