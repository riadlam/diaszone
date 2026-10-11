<?php

namespace App\Http\Controllers;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use App\Services\GiveawayReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GiveawayController extends Controller
{
    public function __construct(
        private readonly GiveawayReferralService $referralService
    ) {}

    public function show(Request $request, ?string $slug = null)
    {
        $giveaway = $slug
            ? Giveaway::query()->where('slug', $slug)->firstOrFail()
            : Giveaway::currentActive();

        if (! $giveaway) {
            return view('pages.giveaway', [
                'giveaway' => null,
                'entry' => null,
                'enrollees' => collect(),
                'enrolleeCount' => 0,
            ]);
        }

        $this->referralService->captureFromRequest($request, $giveaway);

        if (Auth::check()) {
            if ($request->filled('ref')) {
                $this->referralService->attributeWithCode(Auth::user(), (string) $request->query('ref'), $request);
            } else {
                $this->referralService->attribute(Auth::user(), $request);
            }
        }

        $entry = Auth::check()
            ? GiveawayEntry::query()
                ->where('giveaway_id', $giveaway->id)
                ->where('user_id', Auth::id())
                ->first()
            : null;

        $enrollees = GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->limit(40)
            ->get();

        return view('pages.giveaway', [
            'giveaway' => $giveaway,
            'entry' => $entry,
            'enrollees' => $enrollees,
            'enrolleeCount' => GiveawayEntry::query()->where('giveaway_id', $giveaway->id)->count(),
        ]);
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

        if (GiveawayEntry::query()
            ->where('giveaway_id', $giveaway->id)
            ->where('user_id', Auth::id())
            ->exists()) {
            return redirect()
                ->route('giveaway.show', $giveaway->slug)
                ->with('success', __('giveaway.already_entered'));
        }

        $validated = $request->validate([
            'screenshot' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'liked_followed_ack' => ['accepted'],
        ]);

        $path = $request->file('screenshot')->store('giveaway-screenshots', 'public');

        $entry = GiveawayEntry::create([
            'giveaway_id' => $giveaway->id,
            'user_id' => Auth::id(),
            'referral_code' => GiveawayEntry::generateUniqueReferralCode(),
            'screenshot_path' => $path,
            'liked_followed_ack' => true,
            'status' => GiveawayEntry::STATUS_PENDING,
        ]);

        return redirect()
            ->route('giveaway.show', $giveaway->slug)
            ->with('success', __('giveaway.submitted_success'))
            ->with('highlight_referral', true);
    }
}
