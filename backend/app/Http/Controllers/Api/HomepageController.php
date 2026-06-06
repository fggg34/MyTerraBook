<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BlogPostResource;
use App\Models\BlogPost;
use App\Models\HomepageSection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class HomepageController extends Controller
{
    public function show(): JsonResponse
    {
        $sections = HomepageSection::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get()
            ->keyBy('section_key');

        $hero = $this->resolveImages($sections->get('hero')?->content ?? [], ['backgroundImage']);
        $rent = $sections->get('rent')?->content ?? [];
        if (isset($rent['cards']) && is_array($rent['cards'])) {
            $rent['cards'] = array_map(function (array $card): array {
                if (
                    ! empty($card['image'])
                    && ! str_starts_with((string) $card['image'], 'http')
                    && ! str_starts_with((string) $card['image'], '/')
                ) {
                    $card['image'] = Storage::disk('public')->url($card['image']);
                }

                return $card;
            }, $rent['cards']);
        }

        $why = $this->resolveImages($sections->get('why')?->content ?? [], ['photo']);

        $guestHousesHighlight = $sections->get('guest_houses_highlight')?->content ?? [];

        $featuredBlogPosts = BlogPost::query()
            ->published()
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->limit(5)
            ->get();

        return response()->json([
            'topbar' => $sections->get('topbar')?->content ?? [],
            'header' => $sections->get('header')?->content ?? [],
            'hero' => $hero,
            'trustItems' => $sections->get('trust')?->content['items'] ?? [],
            'rentSection' => $rent,
            'whySection' => $why,
            'guestHousesHighlight' => $guestHousesHighlight,
            'footer' => $sections->get('footer')?->content ?? [],
            'featuredBlogPosts' => BlogPostResource::collection($featuredBlogPosts)->resolve(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $content
     * @param  list<string>  $imageKeys
     * @return array<string, mixed>
     */
    private function resolveImages(array $content, array $imageKeys): array
    {
        foreach ($imageKeys as $key) {
            if (
                empty($content[$key])
                || str_starts_with((string) $content[$key], 'http')
                || str_starts_with((string) $content[$key], '/')
            ) {
                continue;
            }
            $content[$key] = Storage::disk('public')->url($content[$key]);
        }

        return $content;
    }
}
