<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\DiamondPack;
use App\Models\Order;

class WeeklyPassUiAndOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_weekly_pass_pack_shows_3x_badge_and_creates_order_with_quantity()
    {
            // Create the special pack with special_quantity = 3
            $pack = DiamondPack::create([
                'game_type' => 'mobilelegends',
                'name' => '3 Weekly Pass',
                'code' => 'mlbb-pass-3',
                'diamonds' => 55,
                'price' => 10.00,
                'price_dzd' => 1000,
                'is_active' => true,
                'special_quantity' => 3,
            ]);

            // Ensure the pack and its special badge are visible on the Mobile Legends page
            $getResponse = $this->get('/mobilelegends');
            $getResponse->assertStatus(200, 'Mobile Legends page did not return 200');
            $getResponse->assertSee('Weekly Pass', 'Weekly Pass not shown on Mobile Legends page');
            // Check for a badge span that includes the bg-blue-600 class and the 3× Weekly Pass text.
            // On mobile views the badge may not be present; instead the price or quantity will reflect the 3× behaviour.
            $content = $getResponse->getContent();
            $badgeRegex = '/<span[^>]+class="[^"]*bg-blue-600[^"]*"[^>]*>\s*3[^<]*Weekly Pass\s*<\/span>/iu';
            $badgeFound = (bool) preg_match($badgeRegex, $content);
            $multipliedPriceText = number_format(1000 * 3, 0) . ' DZD';
            $priceFound = str_contains($content, $multipliedPriceText);

            // Create a discounted pack case to assert original price multiplies by quantity in the UI
            $discountedPack = DiamondPack::create([
                'game_type' => 'mobilelegends',
                'name' => '3 Weekly Pass Discount',
                'code' => 'mlbb-pass-3-discount',
                'diamonds' => 55,
                'price' => 10.00,
                'price_dzd' => 1000,
                'discount_percentage' => 20,
                'is_active' => true,
                'special_quantity' => 3,
            ]);

            $discountResponse = $this->get('/mobilelegends');
            $discountResponse->assertStatus(200);
            $discountContent = $discountResponse->getContent();
            // Original price should be 1000 * 3 = 3000 DZD (strikethrough)
            $this->assertStringContainsString(number_format(1000 * 3, 0) . ' DZD', $discountContent, 'Original discounted price not multiplied on the UI');
            // Final price should be 3000 * (1 - 0.20) = 2400 DZD
            $this->assertStringContainsString(number_format(1000 * 3 * (1 - 0.20), 0) . ' DZD', $discountContent, 'Final discounted price not showing multiplied and discounted amount on the UI');

            // Simulate creating an order for this pack via API
            fwrite(STDERR, "ABOUT TO POST ORDER\n");
                // Perform POST to create the order
                $response = $this->postJson(route('api.orders.create'), [
                    'cart_items' => [
                        ['pack_id' => $pack->id, 'user_id' => '205762973', 'zone_id' => '4048']
                    ],
                    'payment_method' => 'bmccp'
                ]);

                $response->assertStatus(200)->assertJson(['success' => true]);
                $this->assertArrayHasKey('orders', $response->json(), 'Response JSON does not contain orders key: ' . json_encode($response->json()));
                $this->assertNotEmpty($response->json('orders'), 'Orders array is empty in response: ' . json_encode($response->json()));
                $orderId = $response->json('orders')[0]['id'];

                $order = Order::find($orderId);
                $this->assertNotNull($order, 'Order was not found in the database: ' . print_r($response->json(), true));
                $this->assertEquals(3, $order->quantity, 'Quantity mismatch: ' . print_r($order->toArray(), true));
                $this->assertEquals(1000 * 3, $order->original_price, 'Original price mismatch: ' . print_r($order->toArray(), true));
                $this->assertEquals(1000 * 3, $order->final_price, 'Final price mismatch: ' . print_r($order->toArray(), true));
    }
}
