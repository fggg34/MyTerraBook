<?php

use App\Data\SiteContentDefaults;
use App\Models\SiteContentPage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $global = SiteContentPage::query()->where('page_key', 'global')->first();
        if ($global) {
            $content = $global->content ?? [];
            $legal = $content['footer']['legal'] ?? [];

            $hasLink = collect($legal)->contains(
                fn (array $link): bool => ($link['href'] ?? '') === '/cardholder-terms'
                    || ($link['label'] ?? '') === 'Cardholder Terms',
            );

            if (! $hasLink) {
                $legal[] = ['label' => 'Cardholder Terms', 'href' => '/cardholder-terms'];
                $content['footer']['legal'] = array_values($legal);
                $global->update(['content' => $content]);
            }
        }

        SiteContentPage::query()->updateOrCreate(
            ['page_key' => 'cardholder-terms'],
            [
                'label' => config('site_content.pages.cardholder-terms.label', 'Cardholder Terms'),
                'content' => SiteContentDefaults::forPage('cardholder-terms'),
                'is_published' => true,
                'sort_order' => config('site_content.pages.cardholder-terms.sort_order', 9),
            ],
        );
    }

    public function down(): void
    {
        $global = SiteContentPage::query()->where('page_key', 'global')->first();
        if ($global) {
            $content = $global->content ?? [];
            $content['footer']['legal'] = array_values(array_filter(
                $content['footer']['legal'] ?? [],
                fn (array $link): bool => ($link['href'] ?? '') !== '/cardholder-terms'
                    && ($link['label'] ?? '') !== 'Cardholder Terms',
            ));
            $global->update(['content' => $content]);
        }

        SiteContentPage::query()->where('page_key', 'cardholder-terms')->delete();
    }
};
