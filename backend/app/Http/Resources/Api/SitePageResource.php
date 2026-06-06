<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SitePage */
class SitePageResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'eyebrow' => $this->eyebrow,
            'lead' => $this->lead,
            'body' => $this->body,
            'content' => $this->content ?? [],
            'published_at' => $this->published_at?->toIso8601String(),
        ];
    }
}
