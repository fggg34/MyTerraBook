<?php

namespace Tests\Feature;

use App\Models\NewsletterSubscriber;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NewsletterApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscribe_creates_active_subscriber(): void
    {
        $response = $this->postJson('/api/newsletter/subscribe', [
            'email' => 'traveler@example.com',
            'source' => 'homepage',
        ]);

        $response->assertOk()->assertJsonFragment([
            'message' => 'Thanks, you are on the list.',
        ]);

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'traveler@example.com',
            'is_active' => true,
            'source' => 'homepage',
        ]);
    }

    public function test_resubscribe_reactivates_unsubscribed_email(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'traveler@example.com',
            'is_active' => false,
            'source' => 'homepage',
            'unsubscribe_token' => NewsletterSubscriber::generateUnsubscribeToken(),
            'subscribed_at' => now()->subMonth(),
            'unsubscribed_at' => now()->subWeek(),
        ]);

        $response = $this->postJson('/api/newsletter/subscribe', [
            'email' => 'traveler@example.com',
        ]);

        $response->assertOk();

        $subscriber->refresh();
        $this->assertTrue($subscriber->is_active);
        $this->assertNull($subscriber->unsubscribed_at);
    }

    public function test_unsubscribe_deactivates_subscriber(): void
    {
        $subscriber = NewsletterSubscriber::query()->create([
            'email' => 'traveler@example.com',
            'is_active' => true,
            'source' => 'homepage',
            'unsubscribe_token' => NewsletterSubscriber::generateUnsubscribeToken(),
            'subscribed_at' => now(),
        ]);

        $response = $this->postJson('/api/newsletter/unsubscribe', [
            'token' => $subscriber->unsubscribe_token,
        ]);

        $response->assertOk()->assertJsonFragment([
            'message' => 'You have been unsubscribed.',
        ]);

        $subscriber->refresh();
        $this->assertFalse($subscriber->is_active);
        $this->assertNotNull($subscriber->unsubscribed_at);
    }
}
