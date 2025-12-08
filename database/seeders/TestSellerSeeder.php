<?php

namespace Database\Seeders;

use App\Models\Seller;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TestSellerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Seller::updateOrCreate(
            ['username' => 'testseller'],
            [
                'name' => 'Test Seller',
                'email' => 'testseller@test.com',
                'password' => Hash::make('test1234'),
                'phone' => '0555123456',
                'store_name' => 'Test Store',
                'store_description' => 'A test store for demo purposes',
                'wallet_balance' => 5000.00,
                'total_earnings' => 0.00,
                'total_sales' => 0.00,
                'status' => 'active',
                'allowed_games' => null, // Can sell all games
            ]
        );

        $this->command->info('Test seller created successfully!');
        $this->command->info('Username: testseller');
        $this->command->info('Email: testseller@test.com');
        $this->command->info('Password: test1234');
        $this->command->info('Status: active');
        $this->command->info('Wallet Balance: 5000 DZD');
    }
}
