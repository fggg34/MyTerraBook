<?php

namespace Database\Seeders;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'driving-the-ring-road-in-7-days',
                'title' => 'Driving the Ring Road in 7 days',
                'kicker' => 'Itinerary',
                'excerpt' => 'A complete loop itinerary with the best stops, fuel points and where to sleep each night.',
                'body' => <<<'HTML'
<p>The Ring Road (Route 1) circles Iceland in roughly 1,332 km. Seven days is tight but doable if you focus on highlights and book campsites or guesthouses ahead of time.</p>
<h2>Day 1–2: Reykjavík → South Coast</h2>
<p>Waterfalls, black-sand beaches, and your first night near Vík. Allow extra time for Seljalandsfoss and Skógafoss.</p>
<h2>Day 3–4: Eastfjords → Mývatn</h2>
<p>Longer driving days. Stock up on fuel in Egilsstaðir and plan for variable weather in the northeast.</p>
<h2>Day 5–7: North → West</h2>
<p>Akureyri, optional highland detours if your vehicle allows, then return via Borgarnes to Reykjavík.</p>
HTML,
                'featured_image' => '/images/homepage/hero.jpg',
                'image_alt' => 'Campervan on the Ring Road',
                'read_time' => '12 min read',
                'is_featured' => true,
                'aurora' => false,
                'sort_order' => 0,
            ],
            [
                'slug' => 'golden-circle-in-a-day',
                'title' => 'Golden Circle in a day',
                'kicker' => 'Day trip',
                'excerpt' => 'Þingvellir, Geysir, and Gullfoss — the classic day loop from Reykjavík.',
                'body' => <<<'HTML'
<p>The Golden Circle is the easiest introduction to Iceland if you are short on time. Start early to beat coach crowds at Þingvellir National Park.</p>
<p>Pair with a soak at a local pool or Secret Lagoon near Flúðir if you want a relaxed finish before driving back to the capital.</p>
HTML,
                'featured_image' => '/images/homepage/stay-hofn.jpg',
                'image_alt' => 'Golden Circle',
                'read_time' => '6 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 1,
            ],
            [
                'slug' => 'do-you-need-a-4x4',
                'title' => 'Do you need a 4×4?',
                'kicker' => 'Gear',
                'excerpt' => 'When a camper or 2WD car is enough — and when F-roads mean you need more.',
                'body' => <<<'HTML'
<p>For the Ring Road in summer, many travellers are fine in a 2WD car or standard campervan. You need a 4×4 if you plan to drive F-roads (mountain tracks) or visit the highlands.</p>
<p>Check road.is daily — closures change quickly. Gravel protection is included on MyTerraBook rentals either way.</p>
HTML,
                'featured_image' => '/images/homepage/why-photo.jpg',
                'image_alt' => '4x4 camper on gravel',
                'read_time' => '5 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 2,
            ],
            [
                'slug' => 'chasing-the-northern-lights',
                'title' => 'Chasing the northern lights',
                'kicker' => 'Nature',
                'excerpt' => 'Season, forecasts, and how to plan aurora nights without losing sleep.',
                'body' => <<<'HTML'
<p>Aurora season runs roughly September–April. Clear, dark skies matter more than a perfect app prediction.</p>
<p>Stay outside Reykjavík when possible, dress in layers, and use vedur.is cloud cover maps alongside dedicated aurora alerts.</p>
HTML,
                'featured_image' => null,
                'image_alt' => 'Northern lights over Iceland',
                'read_time' => '7 min read',
                'is_featured' => false,
                'aurora' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'campervan-vs-guesthouse',
                'title' => 'Campervan vs guesthouse',
                'kicker' => 'Compare',
                'excerpt' => 'Flexibility vs comfort — how to mix vans and stays on one trip.',
                'body' => <<<'HTML'
<p>Campervans offer freedom and campsite culture; guesthouses give you a warm bed, kitchen access, and local hosts. Many travellers book both: van for the loop, stays in Vík or Akureyri for recovery nights.</p>
<p>MyTerraBook lets you manage cars, vans, and guesthouses in one account.</p>
HTML,
                'featured_image' => '/images/homepage/cardhouse.jpg',
                'image_alt' => 'Guesthouse interior',
                'read_time' => '4 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 4,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    ...$post,
                    'status' => BlogPostStatus::Published,
                    'published_at' => now(),
                ],
            );
        }
    }
}
