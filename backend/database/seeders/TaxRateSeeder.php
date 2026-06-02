<?php

namespace Database\Seeders;

use App\Models\TaxRate;
use Illuminate\Database\Seeder;

class TaxRateSeeder extends Seeder
{
    public function run(): void
    {
        TaxRate::query()->firstOrCreate(
            ['name' => 'Standard VAT (10%)'],
            ['basis_points' => 1000]
        );

        TaxRate::query()->firstOrCreate(
            ['name' => 'Zero VAT'],
            ['basis_points' => 0]
        );
    }
}
