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
            $columns = $content['footer']['columns'] ?? [];

            foreach ($columns as $index => $column) {
                $title = strtolower((string) ($column['title'] ?? ''));
                if (! str_contains($title, 'company')) {
                    continue;
                }

                $links = $column['links'] ?? [];
                $hasLink = collect($links)->contains(
                    fn (array $link): bool => ($link['href'] ?? '') === '/campsite-map'
                        || ($link['label'] ?? '') === 'Campsite Map',
                );

                if ($hasLink) {
                    break;
                }

                $goodToKnowIndex = collect($links)->search(
                    fn (array $link): bool => ($link['href'] ?? '') === '/good-to-know'
                        || ($link['label'] ?? '') === 'Good to Know',
                );
                $insertAt = $goodToKnowIndex !== false ? $goodToKnowIndex : 0;

                array_splice($links, $insertAt, 0, [[
                    'label' => 'Campsite Map',
                    'href' => '/campsite-map',
                ]]);

                $columns[$index]['links'] = array_values($links);
                $content['footer']['columns'] = $columns;
                $global->update(['content' => $content]);

                break;
            }
        }

        SiteContentPage::query()->updateOrCreate(
            ['page_key' => 'campsite-map'],
            [
                'label' => config('site_content.pages.campsite-map.label', 'Campsite map'),
                'content' => SiteContentDefaults::forPage('campsite-map'),
                'is_published' => true,
                'sort_order' => config('site_content.pages.campsite-map.sort_order', 9),
            ],
        );
    }

    public function down(): void
    {
        $global = SiteContentPage::query()->where('page_key', 'global')->first();
        if ($global) {
            $content = $global->content ?? [];
            $columns = $content['footer']['columns'] ?? [];

            foreach ($columns as $index => $column) {
                $title = strtolower((string) ($column['title'] ?? ''));
                if (! str_contains($title, 'company')) {
                    continue;
                }

                $columns[$index]['links'] = array_values(array_filter(
                    $column['links'] ?? [],
                    fn (array $link): bool => ($link['href'] ?? '') !== '/campsite-map'
                        && ($link['label'] ?? '') !== 'Campsite Map',
                ));

                $content['footer']['columns'] = $columns;
                $global->update(['content' => $content]);

                break;
            }
        }

        SiteContentPage::query()->where('page_key', 'campsite-map')->delete();
    }
};
