<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use App\Models\WheelClaim;
use App\Models\WheelEvent;
use App\Models\WheelReward;
use App\Models\WheelSpinLedger;
use App\Models\WheelUserProgress;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class WheelProgressService
{
    public function __construct(
        private readonly WheelQualificationService $qualification
    ) {}

    public function ensureProgress(User $user, ?WheelEvent $event = null): WheelUserProgress
    {
        $event = $event ?: $this->qualification->currentActiveEvent();

        $progress = WheelUserProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'game_type' => WheelQualificationService::GAME_TYPE,
            ],
            [
                'current_reward_id' => $event?->activeRewards()->orderBy('sort_order')->orderBy('id')->value('id'),
                'draws_toward_current' => 0,
                'total_spins_earned' => 0,
                'total_spins_used' => 0,
                'total_rewards_unlocked' => 0,
                'version' => 0,
            ]
        );

        // Only seed the first reward for brand-new progress — never restart a finished track.
        if (! $progress->current_reward_id && $event && $progress->total_rewards_unlocked === 0) {
            $progress->current_reward_id = $event->activeRewards()->orderBy('sort_order')->orderBy('id')->value('id');
            $progress->save();
        }

        return $progress->fresh(['currentReward.diamondPack', 'currentReward.eligiblePacks']);
    }

    public function snapshot(User $user, ?WheelEvent $event = null): array
    {
        $event = $event ?: $this->qualification->currentActiveEvent();
        $progress = $this->ensureProgress($user, $event);
        $currentReward = $progress->currentReward;

        if (! $currentReward && $event && ($progress->total_rewards_unlocked === 0 || $user->hasUnlimitedWheelSpins())) {
            $currentReward = $event->activeRewards()->orderBy('sort_order')->orderBy('id')->first();
            if ($currentReward) {
                $progress->current_reward_id = $currentReward->id;
                $progress->save();
            }
        }

        $nextReward = null;
        if ($currentReward && $event) {
            $nextReward = $event->activeRewards()
                ->where(function ($q) use ($currentReward) {
                    $q->where('sort_order', '>', $currentReward->sort_order)
                        ->orWhere(function ($inner) use ($currentReward) {
                            $inner->where('sort_order', $currentReward->sort_order)
                                ->where('id', '>', $currentReward->id);
                        });
                })
                ->orderBy('sort_order')
                ->orderBy('id')
                ->first();
        }

        $claims = WheelClaim::with(['reward.diamondPack', 'coupon'])
            ->where('user_id', $user->id)
            ->orderByDesc('unlocked_at')
            ->get();

        return [
            'event' => $event,
            'progress' => $progress,
            'available_spins' => $progress->availableSpins(),
            'current_reward' => $currentReward,
            'next_reward' => $nextReward,
            'draws_toward_current' => $progress->draws_toward_current,
            'draws_required' => $currentReward?->draws_required,
            'claims' => $claims,
            'unlimited_spins' => $user->hasUnlimitedWheelSpins(),
            'wheel_can_spin' => $event
                && $event->isCurrentlyActive()
                && ($user->hasUnlimitedWheelSpins() || $progress->availableSpins() > 0)
                && $currentReward,
        ];
    }

    /**
     * Consume one spin and advance milestone progress. Returns spin result payload.
     */
    public function draw(User $user): array
    {
        $event = $this->qualification->currentActiveEvent();

        if (! $event) {
            return [
                'success' => false,
                'message' => __('event.no_active_event'),
                'code' => 'no_event',
            ];
        }

        $result = DB::transaction(function () use ($user, $event) {
            $progress = WheelUserProgress::where('user_id', $user->id)
                ->where('game_type', WheelQualificationService::GAME_TYPE)
                ->lockForUpdate()
                ->first();

            if (! $progress) {
                $progress = $this->ensureProgress($user, $event);
                $progress = WheelUserProgress::where('id', $progress->id)->lockForUpdate()->first();
            }

            $unlimitedSpins = $user->hasUnlimitedWheelSpins();

            if (! $unlimitedSpins && $progress->availableSpins() <= 0) {
                return [
                    'success' => false,
                    'message' => __('event.no_spins_text'),
                    'code' => 'no_spins',
                    'available_spins' => 0,
                ];
            }

            $reward = $progress->current_reward_id
                ? WheelReward::where('id', $progress->current_reward_id)->lockForUpdate()->first()
                : null;

            if (! $reward || ! $reward->is_active) {
                if ($progress->total_rewards_unlocked > 0 && ! $unlimitedSpins) {
                    return [
                        'success' => false,
                        'message' => __('event.no_rewards_configured'),
                        'code' => 'track_complete',
                        'available_spins' => $progress->availableSpins(),
                    ];
                }

                $reward = $event->activeRewards()->orderBy('sort_order')->orderBy('id')->lockForUpdate()->first();
                if (! $reward) {
                    return [
                        'success' => false,
                        'message' => __('event.no_rewards_configured'),
                        'code' => 'no_rewards',
                    ];
                }
                $progress->current_reward_id = $reward->id;
            }

            $debitKey = 'draw:'.$user->id.':'.Str::uuid()->toString();

            WheelSpinLedger::create([
                'user_id' => $user->id,
                'game_type' => WheelQualificationService::GAME_TYPE,
                'wheel_event_id' => $event->id,
                'entry_type' => 'debit',
                'amount' => $unlimitedSpins ? 0 : -1,
                'source_type' => $unlimitedSpins ? 'admin_draw' : 'draw',
                'source_key' => $debitKey,
                'meta' => [
                    'reward_id' => $reward->id,
                    'before_draws' => $progress->draws_toward_current,
                    'unlimited_admin_spin' => $unlimitedSpins,
                ],
            ]);

            if (! $unlimitedSpins) {
                $progress->total_spins_used = $progress->total_spins_used + 1;
            }
            $progress->draws_toward_current = $progress->draws_toward_current + 1;
            $progress->version = $progress->version + 1;

            $claim = null;
            $rewardUnlocked = false;

            if ($progress->draws_toward_current >= $reward->draws_required) {
                $occurrence = WheelClaim::where('user_id', $user->id)
                    ->where('wheel_reward_id', $reward->id)
                    ->lockForUpdate()
                    ->count() + 1;

                $claim = $this->createClaim($user, $event, $reward, $occurrence);
                $rewardUnlocked = true;
                $progress->total_rewards_unlocked = $progress->total_rewards_unlocked + 1;
                $progress->draws_toward_current = 0;

                $next = $this->nextRewardAfter($event, $reward);
                if (! $next && $unlimitedSpins) {
                    $next = $event->activeRewards()->orderBy('sort_order')->orderBy('id')->first();
                }
                $progress->current_reward_id = $next?->id;
            }

            $progress->save();

            $fresh = $this->snapshot($user, $event);

            return [
                'success' => true,
                'reward_unlocked' => $rewardUnlocked,
                'claim' => $claim ? $this->serializeClaim($claim->fresh(['reward.diamondPack', 'coupon'])) : null,
                'spun_toward' => [
                    'reward_id' => $reward->id,
                    'label' => $reward->label,
                    'draws_required' => $reward->draws_required,
                ],
                'available_spins' => $fresh['available_spins'],
                'unlimited_spins' => $fresh['unlimited_spins'],
                'draws_toward_current' => $fresh['draws_toward_current'],
                'draws_required' => $fresh['draws_required'],
                'current_reward' => $fresh['current_reward'] ? [
                    'id' => $fresh['current_reward']->id,
                    'label' => $fresh['current_reward']->label,
                    'draws_required' => $fresh['current_reward']->draws_required,
                    'reward_type' => $fresh['current_reward']->reward_type,
                ] : null,
            ];
        });

        if ($result['success'] ?? false) {
            TelegramService::sendMessage(
                TelegramService::formatWheelSpinMessage($user, $event, $result)
            );

            if (($result['reward_unlocked'] ?? false) && ! empty($result['claim'])) {
                TelegramService::sendMessage(
                    TelegramService::formatWheelRewardMessage($user, $event, $result['claim'])
                );
            }
        }

        return $result;
    }

    public function createClaim(User $user, WheelEvent $event, WheelReward $reward, int $occurrence): WheelClaim
    {
        $idempotency = 'u'.$user->id.':r'.$reward->id.':o'.$occurrence;

        $existing = WheelClaim::where('idempotency_key', $idempotency)->first();
        if ($existing) {
            return $existing;
        }

        $payload = [
            'user_id' => $user->id,
            'wheel_event_id' => $event->id,
            'wheel_reward_id' => $reward->id,
            'occurrence' => $occurrence,
            'reward_type' => $reward->reward_type,
            'status' => 'unlocked',
            'unlocked_at' => now(),
            'idempotency_key' => $idempotency,
        ];

        if ($reward->isPackReward()) {
            $payload['claim_code'] = WheelClaim::generateClaimCode();
        } else {
            $coupon = $this->mintDiscountCoupon($user, $reward);
            $payload['coupon_id'] = $coupon->id;
            $payload['claim_code'] = $coupon->code;
        }

        return WheelClaim::create($payload);
    }

    public function mintDiscountCoupon(User $user, WheelReward $reward): Coupon
    {
        $packIds = $reward->eligiblePacks()->pluck('diamond_packs.id')->values()->all();
        if (empty($packIds) && $reward->diamond_pack_id) {
            $packIds = [$reward->diamond_pack_id];
        }

        $code = 'WHEEL-'.$user->id.'-'.strtoupper(Str::random(8));

        return Coupon::create([
            'code' => $code,
            'discount_type' => 'percentage',
            'discount_value' => $reward->discount_percentage,
            'applies_to' => 'specific',
            'allowed_packages' => $packIds,
            'allowed_games' => ['mlbb', 'mobilelegends'],
            // One-time use only — same limits as the normal coupon system.
            'max_uses' => 1,
            'max_uses_per_user' => 1,
            'used_count' => 0,
            'starts_at' => now(),
            'expires_at' => null,
            'is_active' => true,
            'created_by' => 'wheel_event',
            'description' => 'Wheel reward for user #'.$user->id.' — '.$reward->label,
        ]);
    }

    public function markClaimUsedFromCoupon(int $couponId, int $userId): void
    {
        $claim = WheelClaim::where('coupon_id', $couponId)
            ->where('user_id', $userId)
            ->where('status', 'unlocked')
            ->first();

        if (! $claim) {
            return;
        }

        $claim->update([
            'status' => 'used',
            'used_at' => now(),
        ]);
    }

    public function serializeClaim(WheelClaim $claim): array
    {
        $eligiblePackNames = [];

        if ($claim->reward?->isDiscountReward()) {
            $eligiblePackNames = $claim->reward->eligiblePacks()
                ->orderBy('diamond_packs.name')
                ->pluck('diamond_packs.name')
                ->values()
                ->all();

            if (empty($eligiblePackNames) && $claim->reward->diamondPack?->name) {
                $eligiblePackNames = [$claim->reward->diamondPack->name];
            }
        }

        return [
            'id' => $claim->id,
            'reward_type' => $claim->reward_type,
            'status' => $claim->status,
            'claim_code' => $claim->claim_code,
            'coupon_code' => $claim->coupon?->code,
            'discount_percentage' => $claim->reward?->discount_percentage,
            'label' => $claim->reward?->label,
            'pack_name' => $claim->reward?->diamondPack?->name,
            'eligible_pack_names' => $eligiblePackNames,
            'unlocked_at' => optional($claim->unlocked_at)->toDateTimeString(),
            'fulfilled_at' => optional($claim->fulfilled_at)->toDateTimeString(),
            'used_at' => optional($claim->used_at)->toDateTimeString(),
            'is_contact_reward' => $claim->reward_type === 'diamond_pack',
            'is_discount_reward' => $claim->reward_type === 'discount',
        ];
    }

    private function nextRewardAfter(WheelEvent $event, WheelReward $current): ?WheelReward
    {
        return $event->activeRewards()
            ->where(function ($q) use ($current) {
                $q->where('sort_order', '>', $current->sort_order)
                    ->orWhere(function ($inner) use ($current) {
                        $inner->where('sort_order', $current->sort_order)
                            ->where('id', '>', $current->id);
                    });
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }
}
