<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SitePage;
use App\Services\SiteContentService;
use Illuminate\Http\JsonResponse;

class SitePageController extends Controller
{
    public function show(string $slug, SiteContentService $siteContent): JsonResponse
    {
        $content = $siteContent->pageContent($slug);

        if ($content !== []) {
            return response()->json([
                'data' => [
                    'slug' => $slug,
                    'title' => $content['hero']['title'] ?? '',
                    'eyebrow' => $content['hero']['eyebrow'] ?? '',
                    'lead' => $content['hero']['lead'] ?? '',
                    'body' => $content['body'] ?? null,
                    'content' => array_filter([
                        'phone' => $content['phone'] ?? null,
                        'email' => $content['email'] ?? null,
                        'items' => $content['items'] ?? null,
                        'address' => $content['address'] ?? null,
                        'hours' => $content['hours'] ?? null,
                        'show_form' => $content['show_form'] ?? null,
                        'stats' => $content['stats'] ?? null,
                        'pillars' => $content['pillars'] ?? null,
                        'offerings' => $content['offerings'] ?? null,
                        'categories' => $content['categories'] ?? null,
                        'quickLinks' => $content['quickLinks'] ?? null,
                        'cta' => $content['cta'] ?? null,
                        'formLabels' => $content['formLabels'] ?? null,
                    ], fn ($value) => $value !== null),
                    'published_at' => now()->toIso8601String(),
                ],
            ]);
        }

        $page = SitePage::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->json([
            'data' => [
                'slug' => $page->slug,
                'title' => $page->title,
                'eyebrow' => $page->eyebrow,
                'lead' => $page->lead,
                'body' => $page->body,
                'content' => $page->content ?? [],
                'published_at' => $page->published_at?->toIso8601String(),
            ],
        ]);
    }
}
