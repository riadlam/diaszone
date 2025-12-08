<?php

namespace Database\Factories;

use App\Models\Seller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SellerFactory extends Factory
{
    protected $model = Seller::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => 'password',
            'phone' => $this->faker->phoneNumber(),
            'store_name' => $this->faker->company(),
            'store_description' => $this->faker->sentence(),
            'wallet_balance' => 0,
            'total_earnings' => 0,
            'total_sales' => 0,
            'website_enabled' => false,
            'flexy_enabled' => false,
            'status' => 'active',
            'remember_token' => Str::random(10),
        ];
    }
}
