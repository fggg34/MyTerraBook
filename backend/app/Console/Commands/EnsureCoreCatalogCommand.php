<?php

namespace App\Console\Commands;

use App\Models\MainCategory;
use Illuminate\Console\Command;

class EnsureCoreCatalogCommand extends Command
{
    protected $signature = 'catalog:ensure-core';

    protected $description = 'Restore or create Car and Campervan main categories (safe after accidental deletion)';

    public function handle(): int
    {
        MainCategory::ensureBySlug('car', [
            'name' => 'Car',
            'description' => 'Passenger cars and 4×4s for everyday driving.',
            'sort_order' => 1,
        ]);

        MainCategory::ensureBySlug('campervan', [
            'name' => 'Campervan',
            'description' => 'Campervans and motorhomes for self-contained road trips.',
            'sort_order' => 2,
        ]);

        $this->call('db:seed', ['--class' => 'Database\\Seeders\\CatalogSeeder', '--force' => true]);

        $this->info('Core main categories and sub-categories are active.');

        return self::SUCCESS;
    }
}
