<?php

namespace App\Services;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class GiveawayDrawService
{
    /**
     * @return Collection<int, array{entry_id: int, name: string, initial: string}>
     */
    public function approvedParticipants(Giveaway $giveaway): Collection
    {
        return GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('status', GiveawayEntry::STATUS_APPROVED)
            ->with('user:id,name,email,created_at')
            ->orderBy('id')
            ->get()
            ->map(fn (GiveawayEntry $entry) => $this->serializeSegment($entry))
            ->values();
    }

    /**
     * @return array{
     *     ok: bool,
     *     preview: bool,
     *     winner: array{
     *         entry_id: int,
     *         name: string,
     *         initial: string,
     *         full_name: string,
     *         email: string,
     *         topups: int,
     *         joined_at: string|null,
     *         joined_label: string
     *     },
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
            ->with('user:id,name,email,created_at')
            ->inRandomOrder()
            ->first();

        if (! $winnerEntry) {
            throw new RuntimeException(__('giveaway.wheel_no_participants'));
        }

        $winner = $this->serializeWinner($winnerEntry);
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
     * Put every approved player on the wheel (winner always included).
     *
     * @param  Collection<int, array{entry_id: int, name: string, initial: string}>  $participants
     * @param  array{entry_id: int, name: string, initial: string}  $winner
     * @return list<array{entry_id: int, name: string, initial: string}>
     */
    public function buildSegments(Collection $participants, array $winner): array
    {
        $segments = $participants
            ->map(fn ($row) => [
                'entry_id' => (int) $row['entry_id'],
                'name' => (string) $row['name'],
                'initial' => (string) $row['initial'],
            ])
            ->shuffle()
            ->values()
            ->all();

        if ($segments === []) {
            return [[
                'entry_id' => (int) $winner['entry_id'],
                'name' => (string) $winner['name'],
                'initial' => (string) $winner['initial'],
            ]];
        }

        return $segments;
    }

    /**
     * @return array{entry_id: int, name: string, initial: string}
     */
    public function serializeSegment(GiveawayEntry $entry): array
    {
        $name = $entry->displayFirstName();

        return [
            'entry_id' => (int) $entry->id,
            'name' => $name,
            'initial' => mb_strtoupper(mb_substr($name, 0, 1)),
        ];
    }

    /**
     * @return array{
     *     entry_id: int,
     *     name: string,
     *     initial: string,
     *     full_name: string,
     *     email: string,
     *     topups: int,
     *     joined_at: string|null,
     *     joined_label: string
     * }
     */
    public function serializeWinner(GiveawayEntry $entry): array
    {
        $user = $entry->user;
        $fullName = trim((string) ($user?->name ?? ''));
        if ($fullName === '') {
            $fullName = $entry->displayFirstName();
        }

        $joinedAt = $user?->created_at;
        $joinedLabel = $joinedAt
            ? $joinedAt->timezone(config('app.timezone'))->format('d M Y')
            : '—';

        $segment = $this->serializeSegment($entry);

        return [
            'entry_id' => $segment['entry_id'],
            'name' => $segment['name'],
            'initial' => $segment['initial'],
            'full_name' => $fullName,
            'email' => (string) ($user?->email ?? ''),
            'topups' => (int) ($user?->deliveredTopupsCount() ?? 0),
            'joined_at' => $joinedAt?->toIso8601String(),
            'joined_label' => $joinedLabel,
        ];
    }
}
