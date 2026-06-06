<?php

namespace Database\Seeders;

use App\Models\SearchPromotion;
use Illuminate\Database\Seeder;

class SearchPromotionSeeder extends Seeder
{
    public function run(): void
    {
        $promotions = [
            [
                'kicker' => 'Also on MyTerraBook',
                'title' => 'Warm guesthouses along the route',
                'text' => 'Book a room for the nights you want a proper bed — same account, same support team.',
                'cta_label' => 'Browse guesthouses',
                'cta_href' => '/guesthouses',
                'layout' => SearchPromotion::LAYOUT_CARD,
                'context' => 'campervan',
                'insert_after' => 2,
                'sort_order' => 1,
            ],
            [
                'kicker' => 'Plan the full route',
                'title' => 'Pair your van with warm guesthouses',
                'text' => 'Book a room for the nights you want a proper bed — same account, same support team.',
                'cta_label' => 'Browse guesthouses',
                'cta_href' => '/guesthouses',
                'layout' => SearchPromotion::LAYOUT_LANDSCAPE,
                'context' => 'campervan',
                'insert_after' => 0,
                'image_path' => null,
                'sort_order' => 10,
            ],
            [
                'kicker' => 'Upgrade your trip',
                'title' => 'Need more space? Try a campervan',
                'text' => 'Sleep onboard and wake up closer to the next waterfall — hundreds of vans ready near Keflavík.',
                'cta_label' => 'Browse campervans',
                'cta_href' => '/campervans',
                'layout' => SearchPromotion::LAYOUT_CARD,
                'context' => 'car',
                'insert_after' => 2,
                'sort_order' => 1,
            ],
            [
                'kicker' => 'Also on MyTerraBook',
                'title' => 'Explore campervans & cars',
                'text' => 'Wake up closer to the next waterfall — winter-checked vans and 4×4s ready near Keflavík.',
                'cta_label' => 'Browse campervans',
                'cta_href' => '/campervans',
                'layout' => SearchPromotion::LAYOUT_CARD,
                'context' => 'guesthouse',
                'insert_after' => 2,
                'sort_order' => 1,
            ],
        ];

        foreach ($promotions as $data) {
            SearchPromotion::query()->updateOrCreate(
                [
                    'title' => $data['title'],
                    'context' => $data['context'],
                    'layout' => $data['layout'],
                ],
                $data,
            );
        }
    }
}
