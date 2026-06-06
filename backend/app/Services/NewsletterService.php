<?php

namespace App\Services;

use App\Enums\NewsletterCampaignStatus;
use App\Mail\NewsletterCampaignMail;
use App\Models\NewsletterCampaign;
use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NewsletterService
{
    public function subscribe(string $email, ?string $source = null): NewsletterSubscriber
    {
        $existing = NewsletterSubscriber::query()->where('email', $email)->first();

        if ($existing) {
            if (! $existing->is_active) {
                $existing->update([
                    'is_active' => true,
                    'subscribed_at' => now(),
                    'unsubscribed_at' => null,
                    'source' => $source ?? $existing->source,
                ]);
            }

            return $existing->fresh();
        }

        return NewsletterSubscriber::query()->create([
            'email' => $email,
            'is_active' => true,
            'source' => $source ?? 'homepage',
            'unsubscribe_token' => NewsletterSubscriber::generateUnsubscribeToken(),
            'subscribed_at' => now(),
        ]);
    }

    public function unsubscribe(string $token): bool
    {
        $subscriber = NewsletterSubscriber::query()
            ->where('unsubscribe_token', $token)
            ->where('is_active', true)
            ->first();

        if (! $subscriber) {
            return false;
        }

        $subscriber->update([
            'is_active' => false,
            'unsubscribed_at' => now(),
        ]);

        return true;
    }

    public function activeSubscriberCount(): int
    {
        return NewsletterSubscriber::query()->where('is_active', true)->count();
    }

    public function sendCampaign(NewsletterCampaign $campaign, ?int $sentByUserId = null): int
    {
        if (! $campaign->isDraft()) {
            return $campaign->recipient_count;
        }

        $subscribers = NewsletterSubscriber::query()
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        $sentCount = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(new NewsletterCampaignMail(
                    campaign: $campaign,
                    unsubscribeToken: $subscriber->unsubscribe_token,
                ));
                $sentCount++;
            } catch (\Throwable $e) {
                Log::error('Newsletter campaign mail failed', [
                    'campaign_id' => $campaign->id,
                    'subscriber_id' => $subscriber->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $campaign->update([
            'status' => NewsletterCampaignStatus::Sent,
            'recipient_count' => $sentCount,
            'sent_at' => now(),
            'sent_by' => $sentByUserId,
        ]);

        return $sentCount;
    }
}
