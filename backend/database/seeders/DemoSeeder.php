<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            CatalogSeeder::class,
            CarSeeder::class,
            OrderSeeder::class,
            DemoExtrasSeeder::class,
        ]);
    }
}
