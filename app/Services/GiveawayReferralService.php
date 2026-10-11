<?php

namespace App\Services;

use App\Models\Giveaway;
use App\Models\GiveawayEntry;
use App\Models\GiveawayReferral;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Cookie as SymfonyCookie;

class GiveawayReferralService
{
    public const SESSION_KEY = 'giveaway_ref';

    public const COOKIE_NAME = 'giveaway_ref';

    public const COOKIE_MINUTES = 60 * 24 * 7;

    /**
     * Capture a valid referral code into session + cookie (first-touch).
     */
    public function captureFromRequest(Request $request, ?Giveaway $giveaway = null): void
    {
        $code = trim((string) $request->query('ref', ''));
        if ($code === '') {
            return;
        }

        // First-touch: do not overwrite an existing capture for the same giveaway.
        $existing = $this->readCapture($request);
        if ($existing && (! $giveaway || (int) ($existing['giveaway_id'] ?? 0) === (int) $giveaway->id)) {
            $this->persistCapture($request, $existing);

            return;
        }

        $entry = GiveawayEntry::query()
            ->where('referral_code', $code)
            ->with('giveaway')
            ->first();

        if (! $entry || ! $entry->giveaway) {
            return;
        }

        if ($giveaway && (int) $entry->giveaway_id !== (int) $giveaway->id) {
            return;
        }

        if (! $entry->giveaway->isOpenForAttribution()) {
            return;
        }

        $payload = [
            'code' => $entry->referral_code,
            'giveaway_id' => (int) $entry->giveaway_id,
            'captured_at' => now()->toIso8601String(),
        ];

        $this->persistCapture($request, $payload);
    }

    /**
     * Re-queue session/cookie before OAuth so the round-trip does not drop the ref.
     */
    public function rememberForOAuth(Request $request): void
    {
        $payload = $this->readCapture($request);
        if ($payload) {
            $this->persistCapture($request, $payload);
        }
    }

    /**
     * Attribute the current user from session/cookie capture.
     *
     * @return array{ok: bool, reason?: string, referral?: GiveawayReferral}
     */
    public function attribute(User $user, ?Request $request = null): array
    {
        $request = $request ?: request();
        $payload = $this->readCapture($request);

        if (! $payload || empty($payload['code'])) {
            return ['ok' => false, 'reason' => 'missing'];
        }

        return $this->attributeWithCode($user, (string) $payload['code'], $request);
    }

    /**
     * Attribute using an explicit code (e.g. live ?ref= on an authenticated visit).
     *
     * @return array{ok: bool, reason?: string, referral?: GiveawayReferral}
     */
    public function attributeWithCode(User $user, string $code, ?Request $request = null): array
    {
        $request = $request ?: request();
        $code = trim($code);

        if ($code === '') {
            return ['ok' => false, 'reason' => 'missing'];
        }

        try {
            $result = DB::transaction(function () use ($user, $code) {
                $entry = GiveawayEntry::query()
                    ->where('referral_code', $code)
                    ->lockForUpdate()
                    ->with('giveaway')
                    ->first();

                if (! $entry || ! $entry->giveaway) {
                    return ['ok' => false, 'reason' => 'invalid'];
                }

                if (! $entry->giveaway->isOpenForAttribution()) {
                    return ['ok' => false, 'reason' => 'closed'];
                }

                if ((int) $entry->user_id === (int) $user->id) {
                    return ['ok' => false, 'reason' => 'self'];
                }

                if (GiveawayReferral::query()
                    ->where('giveaway_id', $entry->giveaway_id)
                    ->where('referred_user_id', $user->id)
                    ->exists()) {
                    return ['ok' => false, 'reason' => 'already'];
                }

                $referral = GiveawayReferral::create([
                    'giveaway_id' => $entry->giveaway_id,
                    'referrer_entry_id' => $entry->id,
                    'referrer_user_id' => $entry->user_id,
                    'referred_user_id' => $user->id,
                    'referral_code_used' => $entry->referral_code,
                    'attributed_at' => now(),
                ]);

                if ($entry->referral_satisfied_at === null) {
                    $entry->referral_satisfied_at = now();
                    $entry->save();
                }

                return ['ok' => true, 'referral' => $referral];
            });
        } catch (QueryException $e) {
            // Unique constraint race — treat as already attributed.
            $already = GiveawayReferral::query()
                ->where('referred_user_id', $user->id)
                ->where('referral_code_used', $code)
                ->exists();

            if ($already || str_contains(strtolower($e->getMessage()), 'unique')) {
                $this->clearCapture($request);

                return ['ok' => false, 'reason' => 'already'];
            }

            throw $e;
        }

        if (($result['ok'] ?? false) === true) {
            $this->clearCapture($request);

            $referrerEntry = GiveawayEntry::query()
                ->where('referral_code', $code)
                ->with(['user', 'giveaway', 'referrals.referredUser'])
                ->first();

            // Only refresh Telegram after the entry was actually submitted for review.
            // Draft referral credits must not create a message without Accept/Decline.
            if ($referrerEntry && $referrerEntry->isSubmitted()) {
                app(GiveawayTelegramNotifier::class)->sync($referrerEntry);
            }
        }

        return $result;
    }

    /**
     * @return array{code: string, giveaway_id: int, captured_at?: string}|null
     */
    public function readCapture(Request $request): ?array
    {
        if ($request->hasSession()) {
            $session = $request->session()->get(self::SESSION_KEY);
            if (is_array($session) && ! empty($session['code'])) {
                return [
                    'code' => (string) $session['code'],
                    'giveaway_id' => (int) ($session['giveaway_id'] ?? 0),
                    'captured_at' => isset($session['captured_at']) ? (string) $session['captured_at'] : null,
                ];
            }
        }

        $raw = $request->cookie(self::COOKIE_NAME);
        if (! is_string($raw) || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (! is_array($decoded) || empty($decoded['code'])) {
            return null;
        }

        return [
            'code' => (string) $decoded['code'],
            'giveaway_id' => (int) ($decoded['giveaway_id'] ?? 0),
            'captured_at' => isset($decoded['captured_at']) ? (string) $decoded['captured_at'] : null,
        ];
    }

    /**
     * @param  array{code: string, giveaway_id: int, captured_at?: string}  $payload
     */
    public function persistCapture(Request $request, array $payload): void
    {
        if ($request->hasSession()) {
            $request->session()->put(self::SESSION_KEY, $payload);
        }

        $cookie = cookie(
            self::COOKIE_NAME,
            json_encode($payload),
            self::COOKIE_MINUTES,
            '/',
            null,
            (bool) config('session.secure', false),
            true,
            false,
            SymfonyCookie::SAMESITE_LAX
        );

        Cookie::queue($cookie);
    }

    public function clearCapture(Request $request): void
    {
        if ($request->hasSession()) {
            $request->session()->forget(self::SESSION_KEY);
        }
        Cookie::queue(Cookie::forget(self::COOKIE_NAME));
    }
}
