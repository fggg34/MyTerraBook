<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactMessageMail;
use App\Models\SitePage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
        ]);

        $contactPage = SitePage::query()->where('slug', 'contact')->first();
        $recipient = $contactPage?->content['email'] ?? config('mail.from.address');

        if (! $recipient) {
            return response()->json([
                'message' => 'Contact email is not configured.',
            ], 503);
        }

        try {
            Mail::to($recipient)->send(new ContactMessageMail($validated));
        } catch (\Throwable $e) {
            Log::error('Contact form mail failed', ['error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Unable to send your message right now. Please email us directly.',
            ], 503);
        }

        return response()->json([
            'message' => 'Thanks — we will get back to you shortly.',
        ]);
    }
}
