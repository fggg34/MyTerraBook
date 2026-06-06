<?php

namespace Database\Seeders;

use App\Data\SiteContentDefaults;
use App\Models\SiteContentPage;
use Illuminate\Database\Seeder;

class SiteContentSeeder extends Seeder
{
    public function run(): void
    {
        $pages = config('site_content.pages', []);
        $defaults = SiteContentDefaults::all();

        foreach ($defaults as $pageKey => $content) {
            $meta = $pages[$pageKey] ?? [];

            SiteContentPage::query()->updateOrCreate(
                ['page_key' => $pageKey],
                [
                    'label' => $meta['label'] ?? SiteContentDefaults::labelFor($pageKey),
                    'content' => $content,
                    'is_published' => true,
                    'sort_order' => $meta['sort_order'] ?? 99,
                ],
            );
        }
    }
}
