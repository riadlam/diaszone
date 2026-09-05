<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * @deprecated Use VipResellerDigitalCatalogSeeder (full digital catalog + API pack sync).
 */
class VipResellerNetflixSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(VipResellerDigitalCatalogSeeder::class);
    }
}
