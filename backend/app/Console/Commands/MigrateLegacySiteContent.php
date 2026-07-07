<?php

namespace App\Console\Commands;

use App\Data\SiteContentDefaults;
use App\Models\HomepageSection;
use App\Models\SiteContentPage;
use App\Models\SitePage;
use Illuminate\Console\Command;

class MigrateLegacySiteContent extends Command
{
    protected $signature = 'site-content:migrate-legacy';

    protected $description = 'Merge legacy homepage_sections and site_pages into site_content_pages';

    public function handle(): int
    {
        $this->info('Migrating legacy content into site_content_pages…');

        $global = SiteContentPage::query()->firstOrNew(['page_key' => 'global']);
        $globalContent = array_replace_recursive(
            SiteContentDefaults::forPage('global'),
            $global->content ?? [],
        );

        foreach (['topbar', 'header', 'footer'] as $key) {
            $legacy = HomepageSection::query()->where('section_key', $key)->value('content');
            if (is_array($legacy)) {
                $globalContent[$key] = array_replace_recursive($globalContent[$key] ?? [], $legacy);
            }
        }

        $global->fill([
            'label' => 'Global chrome',
            'content' => $globalContent,
            'is_published' => true,
            'sort_order' => 0,
        ])->save();

        $home = SiteContentPage::query()->firstOrNew(['page_key' => 'home']);
        $homeContent = array_replace_recursive(
            SiteContentDefaults::forPage('home'),
            $home->content ?? [],
        );

        $homeMap = [
            'hero' => 'hero',
            'trust' => 'trustItems',
            'rent' => 'rentSection',
            'why' => 'whySection',
            'guest_houses_highlight' => 'guestHousesHighlight',
        ];

        foreach ($homeMap as $legacyKey => $contentKey) {
            $legacy = HomepageSection::query()->where('section_key', $legacyKey)->value('content');
            if (! is_array($legacy)) {
                continue;
            }

            if ($legacyKey === 'trust') {
                $homeContent[$contentKey] = $legacy['items'] ?? $legacy;
            } else {
                $homeContent[$contentKey] = array_replace_recursive($homeContent[$contentKey] ?? [], $legacy);
            }
        }

        $home->fill([
            'label' => 'Homepage',
            'content' => $homeContent,
            'is_published' => true,
            'sort_order' => 1,
        ])->save();

        $sitePageMap = [
            'about' => 'about',
            'faq' => 'faq',
            'contact' => 'contact',
            'terms' => 'terms',
            'privacy' => 'privacy',
            'refund-policy' => 'refund-policy',
            'cookies' => 'cookies',
        ];

        foreach ($sitePageMap as $slug => $pageKey) {
            $sitePage = SitePage::query()->where('slug', $slug)->first();
            if (! $sitePage) {
                continue;
            }

            $page = SiteContentPage::query()->firstOrNew(['page_key' => $pageKey]);
            $content = array_replace_recursive(
                SiteContentDefaults::forPage($pageKey),
                $page->content ?? [],
            );

            $content['hero'] = array_replace_recursive($content['hero'] ?? [], [
                'eyebrow' => $sitePage->eyebrow,
                'title' => $sitePage->title,
                'lead' => $sitePage->lead,
            ]);

            if ($sitePage->body) {
                $content['body'] = $sitePage->body;
            }

            if (is_array($sitePage->content)) {
                if ($slug === 'faq') {
                    $content = array_replace_recursive($content, $sitePage->content);
                } elseif ($slug === 'contact') {
                    $content = array_replace_recursive($content, $sitePage->content);
                }
            }

            $page->fill([
                'label' => config("site_content.pages.{$pageKey}.label", $pageKey),
                'content' => $content,
                'is_published' => $sitePage->is_published,
                'sort_order' => config("site_content.pages.{$pageKey}.sort_order", 99),
            ])->save();
        }

        $this->info('Legacy content migrated.');

        return self::SUCCESS;
    }
}
