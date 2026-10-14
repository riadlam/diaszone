<?php

namespace App\Http\Controllers;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use App\Services\GiveawayDrawService;
use App\Services\GiveawayReferralService;
use App\Services\GiveawayTelegramNotifier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GiveawayController extends Controller
{
    public function __construct(
        private readonly GiveawayReferralService $referralService,
        private readonly GiveawayTelegramNotifier $telegramNotifier,
        private readonly GiveawayDrawService $drawService
    ) {}

    public function show(Request $request, ?string $slug = null)
    {
        if (! filled($slug)) {
            $featuredSlug = Giveaway::featuredSlug();
            if ($featuredSlug !== '' && Giveaway::query()->where('slug', $featuredSlug)->exists()) {
                return redirect()->route('giveaway.show', $featuredSlug);
            }
        }

        $giveaway = Giveaway::resolveForDisplay($slug);
        if (filled($slug) && ! $giveaway) {
            abort(404);
        }

        if (! $giveaway) {
            return view('pages.giveaway', [
                'giveaway' => null,
                'entry' => null,
                'enrollees' => collect(),
                'enrolleeCount' => 0,
                'canSpinGiveaway' => false,
                'giveawayParticipants' => [],
                'winnerReveal' => null,
                'winnerAnnouncement' => null,
                'drawLiveEnabled' => Giveaway::drawLiveEnabled(),
            ]);
        }

        $this->referralService->captureFromRequest($request, $giveaway);

        if (Auth::check() && $giveaway->isCurrentlyActive()) {
            if ($request->filled('ref')) {
                $this->referralService->attributeWithCode(Auth::user(), (string) $request->query('ref'), $request);
            } else {
                $this->referralService->attribute(Auth::user(), $request);
            }
        }

        $entry = null;
        if (Auth::check()) {
            if ($giveaway->isCurrentlyActive()) {
                $entry = $this->ensureDraftEntry($giveaway, (int) Auth::id());
            } else {
                $entry = GiveawayEntry::query()
                    ->where('giveaway_id', $giveaway->id)
                    ->where('user_id', Auth::id())
                    ->first();
            }
        }

        $enrollees = GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('status', GiveawayEntry::STATUS_APPROVED)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        $canSpinGiveaway = Giveaway::isWheelAdmin(Auth::user()) && ! $giveaway->hasWinner();
        $giveawayParticipants = $canSpinGiveaway
            ? $this->drawService->approvedParticipants($giveaway)->all()
            : [];

        $winnerReveal = null;
        $winnerAnnouncement = null;
        if (Giveaway::winnerRevealEnabled() && $giveaway->hasWinner()) {
            $giveaway->loadMissing('winnerEntry.user:id,name');
            $winnerEntry = $giveaway->winnerEntry;
            if ($winnerEntry) {
                $winnerName = trim((string) ($winnerEntry->user?->name ?? '')) ?: $winnerEntry->displayFirstName();
                $isPersonalWinner = Auth::check() && (int) $winnerEntry->user_id === (int) Auth::id();
                $isParticipant = $entry && in_array($entry->status, [
                    GiveawayEntry::STATUS_PENDING,
                    GiveawayEntry::STATUS_APPROVED,
                ], true);
                $isAdmin = Giveaway::isWheelAdmin(Auth::user());

                if ($isPersonalWinner) {
                    $winnerReveal = [
                        'name' => $winnerName,
                        'entry_id' => $winnerEntry->id,
                    ];
                } elseif ($isParticipant || $isAdmin) {
                    $winnerAnnouncement = [
                        'name' => $winnerName,
                        'entry_id' => $winnerEntry->id,
                    ];
                }
            }
        }

        return view('pages.giveaway', [
            'giveaway' => $giveaway,
            'entry' => $entry,
            'enrollees' => $enrollees,
            'enrolleeCount' => GiveawayEntry::query()
                ->where('giveaway_id', $giveaway->id)
                ->where('status', GiveawayEntry::STATUS_APPROVED)
                ->count(),
            'canSpinGiveaway' => $canSpinGiveaway,
            'giveawayParticipants' => $giveawayParticipants,
            'winnerReveal' => $winnerReveal,
            'winnerAnnouncement' => $winnerAnnouncement,
            'drawLiveEnabled' => Giveaway::drawLiveEnabled(),
        ]);
    }

    public function spin(Request $request, string $slug)
    {
        if (! Giveaway::isWheelAdmin(Auth::user())) {
            abort(403);
        }

        $giveaway = Giveaway::query()->where('slug', $slug)->firstOrFail();

        try {
            $result = $this->drawService->spin(
                $giveaway,
                forceRedraw: (bool) $request->boolean('force_redraw')
            );
        } catch (RuntimeException $e) {
            return response()->json([
                'ok' => false,
                'message' => $e->getMessage(),
            ], 422);
        }

        return response()->json($result);
    }

    public function uploadScreenshot(Request $request, string $slug)
    {
        $giveaway = Giveaway::query()->where('slug', $slug)->firstOrFail();
        $this->assertActiveAuth($giveaway);

        $entry = $this->ensureDraftEntry($giveaway, (int) Auth::id());
        if (! $entry->isEditable()) {
            return response()->json(['ok' => false, 'message' => __('giveaway.already_entered')], 422);
        }

        $request->validate([
            'screenshot' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ]);

        $entry->deleteScreenshotFile();
        $path = $request->file('screenshot')->store('giveaway-screenshots', 'public');

        $entry->update([
            'screenshot_path' => $path,
        ]);

        $entry = $entry->fresh();

        return response()->json([
            'ok' => true,
            'message' => __('giveaway.screenshot_saved'),
            'url' => route('giveaway.screenshot', $giveaway->slug).'?t='.time(),
            'can_submit' => $entry->canSubmitForApproval(),
            'referral_satisfied' => $entry->referralSatisfied(),
        ]);
    }

    public function deleteScreenshot(Request $request, string $slug)
    {
        $giveaway = Giveaway::query()->where('slug', $slug)->firstOrFail();
        $this->assertActiveAuth($giveaway);

        $entry = $this->ensureDraftEntry($giveaway, (int) Auth::id());
        if (! $entry->isEditable()) {
            return response()->json(['ok' => false, 'message' => __('giveaway.already_entered')], 422);
        }

        $entry->deleteScreenshotFile();
        $entry->update(['screenshot_path' => null]);
        $entry = $entry->fresh();

        return response()->json([
            'ok' => true,
            'message' => __('giveaway.screenshot_removed'),
            'can_submit' => false,
            'referral_satisfied' => $entry->referralSatisfied(),
        ]);
    }

    public function screenshot(string $slug): StreamedResponse
    {
        $giveaway = Giveaway::query()->where('slug', $slug)->firstOrFail();

        if (! Auth::check()) {
            abort(403);
        }

        $entry = GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        if (! $entry->hasScreenshot() || ! Storage::disk('public')->exists($entry->screenshot_path)) {
            abort(404);
        }

        return Storage::disk('public')->response($entry->screenshot_path);
    }

    public function store(Request $request, string $slug)
    {
        $giveaway = Giveaway::query()->where('slug', $slug)->firstOrFail();

        if (! $giveaway->isCurrentlyActive()) {
            return back()->with('error', __('giveaway.closed'));
        }

        if (! Auth::check()) {
            return redirect()->route('login', [
                'redirect' => '/giveaway/'.$giveaway->slug,
            ]);
        }

        $entry = $this->ensureDraftEntry($giveaway, (int) Auth::id());

        if (! $entry->isEditable()) {
            return redirect()
                ->route('giveaway.show', $giveaway->slug)
                ->with('success', __('giveaway.already_entered'));
        }

        $request->validate([
            'liked_followed_ack' => ['accepted'],
        ]);

        if (! $entry->hasScreenshot()) {
            return back()->with('error', __('giveaway.screenshot_required'));
        }

        if (! $entry->referralSatisfied()) {
            return back()->with('error', __('giveaway.referral_required'));
        }

        $entry->update([
            'liked_followed_ack' => true,
            'status' => GiveawayEntry::STATUS_PENDING,
            'decline_reason' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
        ]);

        $this->telegramNotifier->sync($entry->fresh(['user', 'giveaway', 'referrals.referredUser']), forceNew: ! $entry->tlg_message_id);

        return redirect()
            ->route('giveaway.show', $giveaway->slug)
            ->with('success', __('giveaway.submitted_success'))
            ->with('highlight_referral', true);
    }

    private function ensureDraftEntry(Giveaway $giveaway, int $userId): GiveawayEntry
    {
        $existing = GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            return $existing;
        }

        return GiveawayEntry::create([
            'giveaway_id' => $giveaway->id,
            'user_id' => $userId,
            'referral_code' => GiveawayEntry::generateUniqueReferralCode(),
            'screenshot_path' => null,
            'liked_followed_ack' => false,
            'status' => GiveawayEntry::STATUS_DRAFT,
        ]);
    }

    private function assertActiveAuth(Giveaway $giveaway): void
    {
        if (! Auth::check()) {
            abort(401);
        }

        if (! $giveaway->isCurrentlyActive()) {
            abort(422, __('giveaway.closed'));
        }
    }
}
