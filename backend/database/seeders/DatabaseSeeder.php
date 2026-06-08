<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ShopSettingsSeeder::class,
            TaxRateSeeder::class,
            UserSeeder::class,
            HomepageSectionSeeder::class,
            SitePageSeeder::class,
            SiteContentSeeder::class,
            BlogPostSeeder::class,
            EmailTemplateSeeder::class,
        ]);

        if (! app()->environment('testing')) {
            $this->call([
                CatalogSeeder::class,
                CarSeeder::class,
                OrderSeeder::class,
                DemoExtrasSeeder::class,
                GuestHouseAmenitySeeder::class,
                GuestHouseSeeder::class,
                DemoShowcaseSeeder::class,
            ]);
        }
    }
}
