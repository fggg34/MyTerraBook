<?php

namespace Database\Seeders;

use App\Models\HomepageSection;
use Illuminate\Database\Seeder;

class HomepageSectionSeeder extends Seeder
{
    public function run(): void
    {
        $sections = [
            [
                'section_key' => 'topbar',
                'sort_order' => 1,
                'content' => [
                    'text' => 'Become a Host and start earning money!',
                    'linkLabel' => 'List your van or guesthouse',
                    'linkHref' => '/become-a-host',
                    'isVisible' => true,
                ],
            ],
            [
                'section_key' => 'header',
                'sort_order' => 2,
                'content' => [
                    'navLinks' => [
                        ['label' => 'Campervan', 'href' => '/campervans'],
                        ['label' => 'Car', 'href' => '/cars'],
                        ['label' => 'Guesthouse', 'href' => '/guesthouses'],
                        ['label' => 'Good to Know', 'href' => '/good-to-know'],
                    ],
                    'ctaLabel' => 'Become a host',
                    'ctaHref' => '/become-a-host',
                    'langLabel' => 'EN',
                    'currencyLabel' => '€ EUR',
                    'signInLabel' => 'Sign in',
                    'signInHref' => '/login',
                ],
            ],
            [
                'section_key' => 'hero',
                'sort_order' => 3,
                'content' => [
                    'heading' => "Book with the world's leading roadtrip provider!",
                    'subtitle' => 'Campervans, 4×4s and warm guesthouses — everything you need for the Ring Road, in one booking.',
                    'backgroundImage' => '/images/homepage/hero.jpg',
                    'tabs' => [
                        ['id' => 'campervan', 'label' => 'Campervan'],
                        ['id' => 'cars', 'label' => 'Cars'],
                        ['id' => 'guesthouses', 'label' => 'Guesthouses'],
                    ],
                    'experienceLabel' => 'Choose your perfect Icelandic experience',
                    'experiencePlaceholder' => 'Choose an experience',
                    'datesLabel' => 'Select dates',
                    'startDateLabel' => 'Starting date',
                    'endDateLabel' => 'Final date',
                    'travelersLabel' => 'Add travelers',
                    'travelersValue' => '1 traveler',
                    'searchLabel' => 'Search Now',
                    'footerHint' => 'Not sure where to start? Check out',
                    'footerLinkLabel' => 'Things to do in Iceland',
                    'footerLinkHref' => '#discover',
                ],
            ],
            [
                'section_key' => 'trust',
                'sort_order' => 4,
                'content' => [
                    'items' => [
                        [
                            'icon' => 'star',
                            'title' => '4.9 / 5',
                            'subtitle' => '12,400+ verified reviews',
                            'rating' => '4.9',
                            'stars' => 5,
                        ],
                        [
                            'icon' => 'check',
                            'title' => 'Free cancellation',
                            'subtitle' => 'Up to 48 hours before pickup',
                        ],
                        [
                            'icon' => 'shield',
                            'title' => 'Fully insured',
                            'subtitle' => 'Gravel & ash cover included',
                        ],
                        [
                            'icon' => 'phone',
                            'title' => 'Local support, 24/7',
                            'subtitle' => 'Real people in Reykjavík',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'rent',
                'sort_order' => 5,
                'content' => [
                    'heading' => 'Three ways to move through Iceland.',
                    'subtitle' => 'From a two-berth van for the Ring Road to a warm bed at the end of it.',
                    'cards' => [
                        [
                            'image' => '/images/homepage/cardcamper.jpg',
                            'alt' => 'Campervan on Icelandic road',
                            'listingCount' => '184 listings · from €89/night',
                            'name' => 'Campervans',
                            'tagline' => 'Sleep where you drive.',
                            'href' => '/campervans',
                        ],
                        [
                            'image' => '/images/homepage/cardcar.jpg',
                            'alt' => '4x4 car in Iceland',
                            'listingCount' => '96 listings · from €42/night',
                            'name' => 'Cars & 4×4',
                            'tagline' => 'Built for the ring road.',
                            'href' => '/cars',
                        ],
                        [
                            'image' => '/images/homepage/cardhouse.jpg',
                            'alt' => 'Guesthouse in Iceland',
                            'listingCount' => '73 listings · from €110/night',
                            'name' => 'Guesthouses',
                            'tagline' => 'Warm beds between drives.',
                            'href' => '/guesthouses',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'why',
                'sort_order' => 6,
                'content' => [
                    'heading' => 'One local platform for every way to explore Iceland.',
                    'subheading' => "Campervans, cars, guesthouses — even a way to earn from your own. All checked by a team that actually drives these roads.",
                    'photo' => '/images/homepage/why-photo.jpg',
                    'badge' => [
                        'rating' => '4.9',
                        'reviewBold' => '12,400+ travellers',
                        'reviewRest' => 'who booked with us',
                    ],
                    'featuresLeft' => [
                        [
                            'icon' => 'campervan',
                            'title' => 'Campervans',
                            'description' => 'Sleep where you stop — kitted out for the Ring Road and beyond.',
                            'expandedText' => 'Heaters, gas hobs and proper bedding come as standard, and every van is winter-checked before pickup so you can chase the aurora without packing half a house.',
                        ],
                        [
                            'icon' => 'car',
                            'title' => 'Cars & 4×4s',
                            'description' => 'From city compacts to proper F-road 4×4s with gravel cover.',
                            'expandedText' => "Need to reach the highlands? Our 4×4s clear the F-roads and river crossings the rental desks at the airport quietly tell you to avoid.",
                        ],
                        [
                            'icon' => 'house',
                            'title' => 'Guesthouses',
                            'description' => 'Warm, vetted beds spaced along your route across the island.',
                            'expandedText' => "Hand-picked stays in Vík, Höfn, Akureyri and more — each within an easy day's drive of the last, so your itinerary plans itself.",
                        ],
                    ],
                    'featuresRight' => [
                        [
                            'icon' => 'host',
                            'title' => 'Become a host',
                            'description' => 'List your van or guesthouse and earn between trips, hassle-free.',
                            'expandedText' => 'We handle bookings, payments and insurance, and you keep the calendar. Most hosts cover their winter storage within the first season.',
                        ],
                        [
                            'icon' => 'shield',
                            'title' => 'Fully insured',
                            'description' => 'Gravel, ash and tyre protection bundled into one clear price.',
                            'expandedText' => "Iceland's gravel and volcanic ash wreck more rentals than anything else — so that cover is included up front, not upsold at the counter.",
                        ],
                        [
                            'icon' => 'phone',
                            'title' => 'Local support, 24/7',
                            'description' => 'Real people in Reykjavík, any season, whenever the road turns.',
                            'expandedText' => 'Flat tyre at midnight, a road closed by weather, a route rethink — one call reaches a local who knows exactly where you are and what to do.',
                        ],
                    ],
                ],
            ],
            [
                'section_key' => 'footer',
                'sort_order' => 7,
                'content' => [
                    'tagline' => "Iceland's locally-run platform for campervans, 4×4s, cars and guesthouses — one booking, one team in Reykjavík behind it.",
                    'address' => "MyTerraBook ehf.\nLaugavegur 178 · 105 Reykjavík · Iceland\nKennitala 591284-0119 · VSK 142819",
                    'columns' => [
                        [
                            'title' => 'Menu',
                            'links' => [
                                ['label' => 'Campervan', 'href' => '/campervans'],
                                ['label' => 'Car', 'href' => '/cars'],
                                ['label' => 'Guesthouse', 'href' => '/guesthouses'],
                                ['label' => 'Good to Know', 'href' => '/good-to-know'],
                                ['label' => 'Become a host', 'href' => '/become-a-host'],
                            ],
                        ],
                        [
                            'title' => 'Pages',
                            'links' => [
                                ['label' => 'About us', 'href' => '/about'],
                                ['label' => 'FAQs', 'href' => '/faq'],
                                ['label' => 'Contact', 'href' => '/contact'],
                                ['label' => 'Sign in', 'href' => '/login'],
                                ['label' => 'Create account', 'href' => '/register'],
                            ],
                        ],
                    ],
                    'copyright' => '© 2026 MyTerraBook ehf. Made in Reykjavík.',
                    'social' => [],
                    'legal' => [
                        ['label' => 'Terms', 'href' => '/terms'],
                        ['label' => 'Privacy', 'href' => '/privacy'],
                        ['label' => 'Cookies', 'href' => '/cookies'],
                    ],
                    'locale' => 'English (UK)',
                    'currency' => 'EUR €',
                ],
            ],
            [
                'section_key' => 'guest_houses_highlight',
                'sort_order' => 8,
                'content' => [
                    'title' => 'Guest houses & stays',
                    'subtitle' => 'Hand-picked homes across Iceland — from cosy studios to private villas.',
                    'featured_slugs' => [
                        'northern-lights-villa',
                        'harbour-view-apartment',
                        'moss-cottage',
                    ],
                    'ctaLabel' => 'View all stays',
                    'ctaHref' => '/guesthouses',
                ],
            ],
        ];

        foreach ($sections as $section) {
            HomepageSection::query()->updateOrCreate(
                ['section_key' => $section['section_key']],
                [
                    'content' => $section['content'],
                    'is_active' => true,
                    'sort_order' => $section['sort_order'],
                ]
            );
        }
    }
}
