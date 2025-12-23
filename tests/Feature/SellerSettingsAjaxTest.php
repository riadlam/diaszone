<?php

namespace Tests\Feature;

use App\Models\Seller;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SellerSettingsAjaxTest extends TestCase
{
    use RefreshDatabase;

    public function test_slug_is_available_when_unused()
    {
        $seller = Seller::factory()->create(['username' => 'alice']);
        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.settings.check-slug'), ['slug' => 'unique-slug']);
        $resp->assertStatus(200)->assertJson(['available' => true]);
    }

    public function test_slug_unavailable_when_taken_by_other_username()
    {
        // another seller with username 'taken' exists
        Seller::factory()->create(['username' => 'taken']);

        $seller = Seller::factory()->create(['username' => 'owner']);
        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.settings.check-slug'), ['slug' => 'taken']);
        $resp->assertStatus(200)->assertJson(['available' => false]);
    }

    public function test_slug_unavailable_when_taken_by_other_website_url()
    {
        Seller::factory()->create(['username' => 'foo', 'website_url' => 'myslug']);

        $seller = Seller::factory()->create(['username' => 'owner2']);
        $this->actingAs($seller, 'seller');

        $resp = $this->postJson(route('seller.settings.check-slug'), ['slug' => 'myslug']);
        $resp->assertStatus(200)->assertJson(['available' => false]);
    }

    public function test_packs_ajax_returns_missing_flexy_game_types()
    {
        $seller = Seller::factory()->create(['username' => 'ajax-seller']);
        $seller->allowed_games = ['mobilelegends'];
        $seller->save();
        $this->actingAs($seller, 'seller');

        // create a pack without a seller flexy price
        \App\Models\DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Ajax',
            'code' => 'MLA1',
            'diamonds' => 99,
            'price' => 1.0,
            'price_dzd' => 100,
            'base_price_dzd' => 90,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        $resp = $this->get(route('seller.packs') . '?ajax=1');
        $resp->assertStatus(200);
        // debug: assert query resulting JSON
        $json = $resp->json();
        // Now run the same query as controller to inspect the list of missing game types
        $missingQuery = \App\Models\DiamondPack::where('is_active', true)
            ->when(!empty($seller->allowed_games), function ($q) use ($seller) {
                $q->whereIn('game_type', $seller->allowed_games);
            })
            ->whereNotExists(function ($q) use ($seller) {
                $q->select(\DB::raw(1))
                    ->from('seller_game_prices')
                    ->whereColumn('seller_game_prices.diamond_pack_id', 'diamond_packs.id')
                    ->where('seller_game_prices.seller_id', $seller->id)
                    ->whereNotNull('seller_game_prices.flexy_price');
            })
            ->pluck('game_type')
            ->unique()
            ->values()
            ->toArray();
        // If we detect missing query types, show diagnostic info (packs & prices)
        if (!empty($missingQuery)) {
            $allPacks = \App\Models\DiamondPack::all()->toArray();
            $prices = \DB::table('seller_game_prices')->where('seller_id', $seller->id)->get()->toArray();
            // keep debug in log for now
            // \Log::debug('ajax-debug', compact('json', 'missingQuery', 'allPacks', 'prices'));
        }
        // replicate controller logic and check for per-pack missing
        $types = \App\Models\DiamondPack::where('is_active', true)->select('game_type')->distinct()->pluck('game_type');
        $types = $types->intersect($seller->allowed_games);
        foreach ($types as $type) {
            foreach (\App\Models\DiamondPack::where('game_type', $type)->where('is_active', true)->pluck('id') as $packId) {
                $price = \App\Models\SellerGamePrice::where('seller_id', $seller->id)->where('diamond_pack_id', $packId)->whereNotNull('flexy_price')->first();
                if (! $price) {
                    // log and continue
                    // \Log::debug('ajax-missing-pack', ['pack' => \App\Models\DiamondPack::find($packId)->toArray()]);
                }
            }
        }
        $total = \App\Models\DiamondPack::where('game_type', 'mobilelegends')->where('is_active', true)->count();
        $provided = \DB::table('seller_game_prices')->join('diamond_packs', 'diamond_packs.id', '=', 'seller_game_prices.diamond_pack_id')->where('diamond_packs.game_type', 'mobilelegends')->where('seller_game_prices.seller_id', $seller->id)->whereNotNull('seller_game_prices.flexy_price')->count();
        if ($total !== $provided) {
            // \Log::debug('ajax-counts', compact('total', 'provided', 'json'));
        }
        // debug print to inspect payload
        // dump($json);
        $this->assertArrayHasKey('missing_flexy', $json);
        // run aggregate to inspect counts
        $rows = \DB::table('diamond_packs')
            ->leftJoin('seller_game_prices', function ($join) use ($seller) {
                $join->on('seller_game_prices.diamond_pack_id', '=', 'diamond_packs.id')
                    ->where('seller_game_prices.seller_id', $seller->id);
            })
            ->select('diamond_packs.game_type', \DB::raw('count(diamond_packs.id) as total'), \DB::raw('sum(case when seller_game_prices.flexy_price is not null then 1 else 0 end) as provided'))
            ->where('diamond_packs.is_active', true)
            ->when(!empty($seller->allowed_games), function ($q) use ($seller) {
                $q->whereIn('diamond_packs.game_type', $seller->allowed_games);
            })
            ->groupBy('diamond_packs.game_type')
            ->get();
        // \Log::debug('ajax-rows', (array) $rows->toArray());
        $this->assertContains('mobilelegends', $json['missing_flexy']);
    }

    public function test_packs_ajax_returns_no_missing_when_all_have_flexy()
    {
        $seller = Seller::factory()->create(['username' => 'ajax-seller-2']);
        $seller->allowed_games = ['mobilelegends'];
        $seller->save();
        $this->actingAs($seller, 'seller');

        $pack = \App\Models\DiamondPack::create([
            'game_type' => 'mobilelegends',
            'name' => 'ML Ajax 2',
            'code' => 'MLA2',
            'diamonds' => 99,
            'price' => 1.0,
            'price_dzd' => 100,
            'base_price_dzd' => 90,
            'is_active' => true,
            'sort_order' => 1,
        ]);
        \App\Models\SellerGamePrice::create([
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'custom_price_dzd' => 120,
            'custom_price_usd' => 1.2,
            'flexy_price' => 130,
            'is_active' => true,
        ]);

        // Ensure all mobilelegends packs have a flexy price for this seller
        foreach (\App\Models\DiamondPack::where('game_type', 'mobilelegends')->where('is_active', true)->get() as $p) {
            if (!\App\Models\SellerGamePrice::where('seller_id', $seller->id)->where('diamond_pack_id', $p->id)->exists()) {
                \App\Models\SellerGamePrice::create([
                    'seller_id' => $seller->id,
                    'diamond_pack_id' => $p->id,
                    'custom_price_dzd' => $p->price_dzd ?? ($p->base_price_dzd ?? 100),
                    'custom_price_usd' => $p->price ?? 0.0,
                    'flexy_price' => ($p->price_dzd ?? ($p->base_price_dzd ?? 100)) + 10,
                    'is_active' => true,
                ]);
            }
        }

        $this->assertDatabaseHas('seller_game_prices', [
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'flexy_price' => 130,
        ]);

        $resp = $this->get(route('seller.packs') . '?ajax=1');
        $resp->assertStatus(200);
        $json = $resp->json();
        $this->assertArrayHasKey('missing_flexy', $json);
        // Ensure mobilelegends is not included in missing_flexy — other games may appear but are outside allowed_games
        $this->assertNotContains('mobilelegends', $json['missing_flexy']);
    }
}
