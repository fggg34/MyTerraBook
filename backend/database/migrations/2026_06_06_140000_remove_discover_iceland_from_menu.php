<?php

use App\Models\HomepageSection;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $header = HomepageSection::query()->where('section_key', 'header')->first();
        if ($header) {
            $content = $header->content;
            $content['navLinks'] = array_values(array_filter(
                $content['navLinks'] ?? [],
                fn (array $link) => ($link['label'] ?? '') !== 'Discover Iceland',
            ));
            $header->update(['content' => $content]);
        }

        $footer = HomepageSection::query()->where('section_key', 'footer')->first();
        if ($footer) {
            $content = $footer->content;
            foreach ($content['columns'] ?? [] as $i => $column) {
                $content['columns'][$i]['links'] = array_values(array_filter(
                    $column['links'] ?? [],
                    fn (array $link) => ($link['label'] ?? '') !== 'Discover Iceland',
                ));
            }
            $footer->update(['content' => $content]);
        }
    }

    public function down(): void
    {
        $header = HomepageSection::query()->where('section_key', 'header')->first();
        if ($header) {
            $content = $header->content;
            $navLinks = $content['navLinks'] ?? [];
            $hasDiscover = collect($navLinks)->contains(fn (array $link) => ($link['label'] ?? '') === 'Discover Iceland');
            if (! $hasDiscover) {
                $guesthouseIndex = collect($navLinks)->search(fn (array $link) => ($link['label'] ?? '') === 'Guesthouse');
                $insertAt = $guesthouseIndex !== false ? $guesthouseIndex + 1 : count($navLinks);
                array_splice($navLinks, $insertAt, 0, [['label' => 'Discover Iceland', 'href' => '#discover']]);
                $content['navLinks'] = $navLinks;
                $header->update(['content' => $content]);
            }
        }

        $footer = HomepageSection::query()->where('section_key', 'footer')->first();
        if ($footer) {
            $content = $footer->content;
            foreach ($content['columns'] ?? [] as $i => $column) {
                if (($column['title'] ?? '') !== 'Menu') {
                    continue;
                }
                $links = $column['links'] ?? [];
                $hasDiscover = collect($links)->contains(fn (array $link) => ($link['label'] ?? '') === 'Discover Iceland');
                if (! $hasDiscover) {
                    $guesthouseIndex = collect($links)->search(fn (array $link) => ($link['label'] ?? '') === 'Guesthouse');
                    $insertAt = $guesthouseIndex !== false ? $guesthouseIndex + 1 : count($links);
                    array_splice($links, $insertAt, 0, [['label' => 'Discover Iceland', 'href' => '/#discover']]);
                    $content['columns'][$i]['links'] = $links;
                }
            }
            $footer->update(['content' => $content]);
        }
    }
};
