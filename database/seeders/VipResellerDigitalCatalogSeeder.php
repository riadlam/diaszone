<?php

namespace Database\Seeders;

use App\Models\VipResellerCategory;
use App\Models\VipResellerPack;
use App\Services\VipResellerService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class VipResellerDigitalCatalogSeeder extends Seeder
{
    /**
     * Approved digital storefront brands (VIP filter_game must match provider).
     *
     * @var list<array{name: string, slug: string, filter_game: string, required_fields: list<string>, sort_order: int}>
     */
    private array $catalog = [
        [
            'name' => 'Netflix',
            'slug' => 'netflix',
            'filter_game' => 'Netflix Premium',
            'required_fields' => ['email'],
            'sort_order' => 1,
        ],
        [
            'name' => 'EA Sports FC Mobile',
            'slug' => 'ea-sports-fc-mobile',
            'filter_game' => 'EA Sports FC Mobile',
            'required_fields' => ['user_id'],
            'sort_order' => 2,
        ],
        [
            'name' => 'Mobile Legends Gift',
            'slug' => 'mobile-legends-gift',
            'filter_game' => 'Mobile Legends Gift',
            'required_fields' => ['user_id'],
            'sort_order' => 3,
        ],
        [
            'name' => 'Steam Wallet Code',
            'slug' => 'steam-wallet-code',
            'filter_game' => 'Steam Wallet Code',
            'required_fields' => ['user_id'],
            'sort_order' => 4,
        ],
        [
            'name' => 'Voucher Roblox',
            'slug' => 'voucher-roblox',
            'filter_game' => 'Voucher Roblox',
            'required_fields' => ['user_id'],
            'sort_order' => 5,
        ],
        [
            'name' => 'Voucher Razer Gold',
            'slug' => 'voucher-razer-gold',
            'filter_game' => 'Voucher Razer Gold',
            'required_fields' => ['user_id'],
            'sort_order' => 6,
        ],
        [
            'name' => 'Valorant',
            'slug' => 'valorant',
            'filter_game' => 'Valorant',
            'required_fields' => ['user_id'],
            'sort_order' => 7,
        ],
        [
            'name' => 'Voucher Valorant',
            'slug' => 'voucher-valorant',
            'filter_game' => 'Voucher Valorant',
            'required_fields' => ['user_id'],
            'sort_order' => 8,
        ],
        [
            'name' => 'Alight Motion',
            'slug' => 'alight-motion',
            'filter_game' => 'Alight Motion',
            'required_fields' => ['email'],
            'sort_order' => 9,
        ],
        [
            'name' => 'Amazon Prime Video',
            'slug' => 'amazon-prime-video',
            'filter_game' => 'Amazon Prime Video',
            'required_fields' => ['email'],
            'sort_order' => 10,
        ],
        [
            'name' => 'Canva Pro',
            'slug' => 'canva-pro',
            'filter_game' => 'Canva Pro',
            'required_fields' => ['email'],
            'sort_order' => 11,
        ],
        [
            'name' => 'CapCut Pro',
            'slug' => 'capcut-pro',
            'filter_game' => 'CapCut Pro',
            'required_fields' => ['email'],
            'sort_order' => 12,
        ],
        [
            'name' => 'Gemini',
            'slug' => 'gemini',
            'filter_game' => 'Gemini',
            'required_fields' => ['email'],
            'sort_order' => 13,
        ],
        [
            'name' => 'YouTube Premium',
            'slug' => 'youtube-premium',
            'filter_game' => 'Youtube Premium',
            'required_fields' => ['email'],
            'sort_order' => 14,
        ],
        [
            'name' => 'Voucher PSN',
            'slug' => 'voucher-psn',
            'filter_game' => 'Voucher PSN',
            'required_fields' => ['user_id'],
            'sort_order' => 15,
        ],
        [
            'name' => 'Lords Mobile',
            'slug' => 'lords-mobile',
            'filter_game' => 'Lords Mobile',
            'required_fields' => ['user_id'],
            'sort_order' => 16,
        ],
        [
            'name' => 'Marvel Rivals',
            'slug' => 'marvel-rivals',
            'filter_game' => 'Marvel Rivals',
            'required_fields' => ['user_id'],
            'sort_order' => 17,
        ],
    ];

    public function run(): void
    {
        $apiKey = config('services.vip_reseller.api_key') ?: env('VIP_RESELLER_API_KEY');
        $sign = config('services.vip_reseller.sign') ?: env('VIP_RESELLER_SIGN');

        if (empty($apiKey) || empty($sign)) {
            throw new \RuntimeException(
                'VIP Reseller credentials missing. Set VIP_RESELLER_API_KEY and VIP_RESELLER_SIGN before seeding the digital catalog.'
            );
        }

        /** @var VipResellerService $vip */
        $vip = app(VipResellerService::class);

        foreach ($this->catalog as $row) {
            $productUrl = '/digital/'.$row['slug'];

            $category = VipResellerCategory::query()->updateOrCreate(
                ['slug' => $row['slug']],
                [
                    'name' => $row['name'],
                    'filter_game' => $row['filter_game'],
                    'product_url' => $productUrl,
                    'required_fields' => $row['required_fields'],
                    'description' => $row['name'].' via VIP Reseller.',
                    'is_active' => true,
                    'is_topseller' => false,
                    'is_newproduct' => true,
                    'sort_order' => $row['sort_order'],
                ]
            );

            $services = $this->fetchServices($vip, $row['filter_game']);

            if ($services === []) {
                $this->command?->warn("No VIP services for filter_game=\"{$row['filter_game']}\" (slug={$row['slug']}). Category kept.");
                Log::warning('VipResellerDigitalCatalogSeeder: empty services', [
                    'slug' => $row['slug'],
                    'filter_game' => $row['filter_game'],
                ]);

                continue;
            }

            $sort = 0;
            foreach ($services as $service) {
                $code = trim((string) ($service['code'] ?? ''));
                if ($code === '') {
                    continue;
                }

                $sort++;
                $status = strtolower(trim((string) ($service['status'] ?? 'available')));
                $isAvailable = $status === 'available';

                $existing = VipResellerPack::query()->where('code', $code)->first();

                $payload = [
                    'category_id' => $category->id,
                    'description' => (string) ($service['description'] ?? ''),
                    'product_url' => $productUrl,
                    'price_special' => $service['price_special'] ?? null,
                    'server' => $service['server'] ?? null,
                    'provider_status' => $isAvailable ? 'available' : ($status ?: 'empty'),
                    'is_active' => $isAvailable,
                    'sort_order' => $sort,
                ];

                if (! $existing) {
                    $payload['name'] = (string) ($service['name'] ?? $code);
                    $payload['price_dzd'] = 0;
                    $payload['base_price_dzd'] = null;
                    $payload['price_usd'] = null;
                    $payload['discount_percentage'] = 0;
                }

                VipResellerPack::query()->updateOrCreate(
                    ['code' => $code],
                    $payload
                );
            }

            $this->command?->info("Synced {$row['name']}: ".count($services).' pack(s).');
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function fetchServices(VipResellerService $vip, string $filterGame): array
    {
        $response = $vip->getServices($filterGame, 'available');
        $data = is_array($response['data'] ?? null) ? $response['data'] : [];

        if ($data === []) {
            $response = $vip->getServices($filterGame, null);
            $data = is_array($response['data'] ?? null) ? $response['data'] : [];
        }

        return array_values(array_filter($data, fn ($row) => is_array($row)));
    }
}
