<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\BlogPost */
class BlogPostResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'og_image' => $this->resolveImageUrl($this->og_image),
            'kicker' => $this->kicker,
            'excerpt' => $this->excerpt,
            'body' => $this->when(
                $request->route()?->getName() === 'api.blog-posts.show'
                    || (bool) $request->input('include_body'),
                $this->body,
            ),
            'featured_image' => $this->resolveImageUrl($this->featured_image),
            'image_alt' => $this->image_alt,
            'read_time' => $this->read_time,
            'is_featured' => $this->is_featured,
            'aurora' => $this->aurora,
            'published_at' => $this->published_at?->toIso8601String(),
            'sort_order' => $this->sort_order,
        ];
    }

    private function resolveImageUrl(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        if (str_starts_with($path, 'http') || str_starts_with($path, '/')) {
            return $path;
        }

        return Storage::disk('public')->url($path);
    }
}
