<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\NewsletterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NewsletterController extends Controller
{
    public function __construct(
        private readonly NewsletterService $newsletterService,
    ) {}

    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'source' => ['nullable', 'string', 'max:64'],
        ]);

        $this->newsletterService->subscribe(
            email: strtolower(trim($validated['email'])),
            source: $validated['source'] ?? 'homepage',
        );

        return response()->json([
            'message' => 'Thanks, you are on the list.',
        ]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'token' => ['required', 'string', 'size:64'],
        ]);

        $unsubscribed = $this->newsletterService->unsubscribe($validated['token']);

        if (! $unsubscribed) {
            return response()->json([
                'message' => 'This unsubscribe link is invalid or already used.',
            ], 404);
        }

        return response()->json([
            'message' => 'You have been unsubscribed.',
        ]);
    }
}
