@php
    $statusColors = [
        'unlocked' => 'warning',
        'contacted' => 'info',
        'fulfilled' => 'success',
        'used' => 'gray',
        'failed' => 'danger',
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ([
            'Lifetime spins' => $progress->total_spins_earned,
            'Spins used' => $progress->total_spins_used,
            'Unused spins' => $progress->availableSpins(),
            'Rewards unlocked' => $progress->total_rewards_unlocked,
        ] as $label => $value)
            <div class="rounded-lg border border-gray-200 p-3 dark:border-gray-700">
                <div class="text-xs text-gray-500 dark:text-gray-400">{{ $label }}</div>
                <div class="text-lg font-semibold text-gray-950 dark:text-white">{{ $value }}</div>
            </div>
        @endforeach
    </div>

    <div>
        <h3 class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">Claims in this event</h3>

        @forelse ($claims as $claim)
            <div class="mb-2 rounded-lg border border-gray-200 p-3 text-sm dark:border-gray-700">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-medium text-gray-950 dark:text-white">
                        {{ $claim->reward?->label ?? 'Reward #'.$claim->wheel_reward_id }}
                        <span class="text-gray-500 dark:text-gray-400">· occurrence #{{ $claim->occurrence }}</span>
                    </span>

                    <x-filament::badge :color="$statusColors[$claim->status] ?? 'gray'">
                        {{ ucfirst($claim->status) }}
                    </x-filament::badge>
                </div>

                <div class="mt-1 text-gray-600 dark:text-gray-400">
                    @if ($claim->claim_code)
                        Claim code: <span class="font-mono">{{ $claim->claim_code }}</span>
                    @endif

                    @if ($claim->coupon)
                        Coupon: <span class="font-mono">{{ $claim->coupon->code }}</span>
                    @endif

                    <div>
                        Unlocked {{ optional($claim->unlocked_at)->format('d M Y H:i') ?? '—' }}
                        @if ($claim->fulfilled_at)
                            · fulfilled {{ $claim->fulfilled_at->format('d M Y H:i') }}
                        @endif
                        @if ($claim->used_at)
                            · used {{ $claim->used_at->format('d M Y H:i') }}
                        @endif
                    </div>

                    @if ($claim->admin_notes)
                        <div class="mt-1 italic">{{ $claim->admin_notes }}</div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">No claims yet.</p>
        @endforelse
    </div>

    <div>
        <h3 class="mb-2 text-sm font-semibold text-gray-950 dark:text-white">Spin ledger</h3>

        @forelse ($ledgers as $ledger)
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 py-2 text-sm last:border-0 dark:border-gray-800">
                <span class="text-gray-600 dark:text-gray-400">
                    {{ $ledger->created_at?->format('d M Y H:i') }} · {{ $ledger->source_type }}
                    @if ($ledger->order)
                        · order {{ $ledger->order->order_number }}
                    @endif
                </span>
                <span class="font-medium {{ $ledger->entry_type === 'credit' ? 'text-success-600' : 'text-danger-600' }}">
                    {{ $ledger->entry_type === 'credit' ? '+' : '-' }}{{ abs($ledger->amount) }}
                </span>
            </div>
        @empty
            <p class="text-sm text-gray-500 dark:text-gray-400">No spin activity recorded for this event.</p>
        @endforelse
    </div>
</div>
