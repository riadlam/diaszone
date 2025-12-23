<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Seller;
use App\Models\DiamondPack;
use Illuminate\Database\Seeder;

class TestOrdersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $seller = Seller::where('username', 'testseller')->first();
        
        if (!$seller) {
            $this->command->error('Test seller not found! Run TestSellerSeeder first.');
            return;
        }

        $pack = DiamondPack::where('game_type', 'mobilelegends')->first();
        
        if (!$pack) {
            $this->command->error('No diamond packs found!');
            return;
        }

        // Create a pending Flexy verification order
        Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending_flexy_verification',
            'user_id_ml' => '123456789',
            'zone_id_ml' => '1234',
            'wallet_deducted' => false,
            'seller_cost' => $pack->price_dzd,
            'seller_profit' => 50.00,
            'is_direct_topup' => false,
            'original_price' => $pack->price_dzd + 50,
            'final_price' => $pack->price_dzd + 50,
            'payment_method' => 'flexy',
            'flexy_receipt' => null,
            'flexy_description' => 'Test flexy order - needs verification',
        ]);

        // Create a completed order
        Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'completed',
            'user_id_ml' => '987654321',
            'zone_id_ml' => '5678',
            'wallet_deducted' => true,
            'seller_cost' => $pack->price_dzd,
            'seller_profit' => 50.00,
            'is_direct_topup' => false,
            'original_price' => $pack->price_dzd + 50,
            'final_price' => $pack->price_dzd + 50,
            'payment_method' => 'baridimob',
        ]);

        // Create a pending order
        Order::create([
            'order_number' => Order::generateOrderNumber(),
            'seller_id' => $seller->id,
            'diamond_pack_id' => $pack->id,
            'status' => 'pending',
            'user_id_ml' => '555555555',
            'zone_id_ml' => '9999',
            'wallet_deducted' => false,
            'seller_cost' => $pack->price_dzd,
            'seller_profit' => 50.00,
            'is_direct_topup' => false,
            'original_price' => $pack->price_dzd + 50,
            'final_price' => $pack->price_dzd + 50,
            'payment_method' => 'flexy',
            'flexy_description' => 'Another pending order',
        ]);

        $this->command->info('Test orders created successfully!');
        $this->command->info('Created 3 orders for testseller');
        $this->command->info('- 1 pending_flexy_verification');
        $this->command->info('- 1 completed');
        $this->command->info('- 1 pending');
    }
}
