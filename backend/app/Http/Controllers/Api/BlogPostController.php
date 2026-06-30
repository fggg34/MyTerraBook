<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\BlogPostResource;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class BlogPostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection|JsonResponse
    {
        $query = BlogPost::query()
            ->published()
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('published_at');

        if ($request->boolean('featured')) {
            $query->where('is_featured', true);
        }

        $perPage = min(24, max(1, (int) $request->input('per_page', 12)));

        return BlogPostResource::collection($query->paginate($perPage));
    }

    public function show(string $slug): BlogPostResource
    {
        $post = BlogPost::query()
            ->published()
            ->where('slug', $slug)
            ->firstOrFail();

        return new BlogPostResource($post);
    }
}
