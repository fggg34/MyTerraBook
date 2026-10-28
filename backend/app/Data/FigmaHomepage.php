<?php

namespace App\Data;

/**
 * Figma homepage and chrome defaults (file HjqfE4nbHKhJLGEKIQgyQT, frame 132:551).
 * Seeded into site content so the public pages render from the API.
 */
class FigmaHomepage
{
    /**
     * @return array<string, mixed>
     */
    public static function chrome(): array
    {
        return [
            'header' => [
                'navLinks' => [
                    ['label' => 'Plan a trip', 'href' => '/#search'],
                    ['label' => 'Vehicles', 'href' => '/campervans'],
                    ['label' => 'Guesthouses', 'href' => '/guesthouses'],
                    ['label' => 'Destinations', 'href' => '/destinations'],
                    ['label' => 'Guides', 'href' => '/good-to-know'],
                ],
                'ctaLabel' => 'Become a host',
                'ctaHref' => '/become-a-host',
                'currencyLabel' => 'EUR',
                'signInLabel' => 'Sign in',
                'signInHref' => '/login',
                'accountLabel' => 'Account',
                'currencyMenuLabel' => 'Currency',
                'currencyNote' => 'Prices are shown in your currency. Bookings are charged in ISK.',
            ],
            'footer' => [
                'tagline' => 'Wheels and warm beds for road trips around the world — planned in one place.',
                'copyright' => '© MyTerraBook ehf.',
                'paymentsLabel' => 'Visa · Amex · Apple Pay · Google Pay · Klarna',
                'columns' => [
                    [
                        'key' => 'travel',
                        'title' => 'Travel',
                        'links' => [
                            ['label' => 'Plan a trip', 'href' => '/#search'],
                            ['label' => 'Campervans', 'href' => '/campervans'],
                            ['label' => 'Cars & 4×4', 'href' => '/cars'],
                            ['label' => 'Guesthouses', 'href' => '/guesthouses'],
                            ['label' => 'Destinations', 'href' => '/destinations'],
                        ],
                    ],
                    [
                        'key' => 'company',
                        'title' => 'Company',
                        'links' => [
                            ['label' => 'About', 'href' => '/about'],
                            ['label' => 'Good to Know', 'href' => '/good-to-know'],
                            ['label' => 'Campsite map', 'href' => '/campsite-map'],
                            ['label' => 'FAQ', 'href' => '/faq'],
                            ['label' => 'Contact', 'href' => '/contact'],
                        ],
                    ],
                    [
                        'key' => 'hosts',
                        'title' => 'Hosts',
                        'links' => [
                            ['label' => 'Become a host', 'href' => '/become-a-host'],
                            ['label' => 'Host sign in', 'href' => '/host/login'],
                            ['label' => 'Host register', 'href' => '/host/register'],
                        ],
                    ],
                    [
                        'key' => 'legal',
                        'title' => 'Legal',
                        'links' => [
                            ['label' => 'Terms', 'href' => '/terms'],
                            ['label' => 'Privacy', 'href' => '/privacy'],
                            ['label' => 'Refund policy', 'href' => '/refund-policy'],
                            ['label' => 'Cookies', 'href' => '/cookies'],
                            ['label' => 'Cardholder terms', 'href' => '/cardholder-terms'],
                        ],
                    ],
                ],
                'newsletterHeading' => 'Trip ideas, once a month.',
                'newsletterLead' => 'Routes, seasons and new places to stay. No spam.',
                'newsletterPlaceholder' => 'your@email.com',
                'newsletterButton' => 'Subscribe',
                'social' => [
                    ['icon' => 'instagram', 'href' => 'https://instagram.com', 'label' => 'Instagram'],
                    ['icon' => 'facebook', 'href' => 'https://facebook.com', 'label' => 'Facebook'],
                    ['icon' => 'mail', 'href' => 'mailto:hello@myterrabook.com', 'label' => 'Email'],
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function sections(): array
    {
        $photo = '/figma/cover.png';

        return [
            'cover' => [
                'visible' => true,
                'heading' => 'Your road trip starts ',
                'headingAccent' => 'here.',
                'backgroundImage' => $photo,
                'backgroundAlt' => 'Open road through mountains',
                'tabs' => [
                    ['id' => 'campervan', 'label' => 'Campervans'],
                    ['id' => 'car', 'label' => 'Cars'],
                    ['id' => 'guesthouse', 'label' => 'Guesthouses'],
                ],
                'whereLabel' => 'Where',
                'wherePlaceholder' => 'Country or city',
                'whenLabel' => 'When',
                'whenPlaceholder' => 'Add dates',
                'whoLabel' => 'Who',
                'whoValue' => '2 adults',
            ],
            'countries' => [
                'visible' => true,
                'eyebrow' => 'Destinations',
                'heading' => 'Where do you want to go?',
                'subtitle' => 'One platform, every country. Listings appear as soon as local hosts join.',
                'tabs' => ['All', 'Europe', 'North America', 'Oceania', 'Asia', 'Africa', 'South America'],
                'cards' => [
                    ['code' => 'IS', 'name' => 'Iceland', 'region' => 'Europe', 'image' => $photo, 'imageAlt' => 'Iceland', 'live' => true, 'campers' => '15', 'cars' => '18', 'stays' => '9', 'listings' => '42 listings', 'href' => '/iceland'],
                    ['code' => 'NO', 'name' => 'Norway', 'region' => 'Europe', 'image' => $photo, 'imageAlt' => 'Norway', 'live' => false, 'listings' => '0 listings yet', 'emptyCta' => 'Be the first host', 'href' => '/norway'],
                    ['code' => 'US', 'name' => 'United States', 'region' => 'North America', 'image' => $photo, 'imageAlt' => 'United States', 'live' => false, 'listings' => '0 listings yet', 'emptyCta' => 'Be the first host', 'href' => '/united-states'],
                    ['code' => 'NZ', 'name' => 'New Zealand', 'region' => 'Oceania', 'image' => $photo, 'imageAlt' => 'New Zealand', 'live' => false, 'listings' => '0 listings yet', 'emptyCta' => 'Be the first host', 'href' => '/new-zealand'],
                    ['code' => 'JP', 'name' => 'Japan', 'region' => 'Asia', 'image' => $photo, 'imageAlt' => 'Japan', 'live' => false, 'listings' => '0 listings yet', 'emptyCta' => 'Be the first host', 'href' => '/japan'],
                    ['code' => 'ZA', 'name' => 'South Africa', 'region' => 'Africa', 'image' => $photo, 'imageAlt' => 'South Africa', 'live' => false, 'listings' => '0 listings yet', 'emptyCta' => 'Be the first host', 'href' => '/south-africa'],
                ],
            ],
            'roadSigns' => [
                'visible' => true,
                'signs' => [
                    ['label' => 'Airport', 'distance' => '12 km'],
                    ['label' => 'City centre', 'distance' => '28 km'],
                    ['label' => 'Campsite', 'distance' => '64 km'],
                    ['label' => 'Next stay', 'distance' => '120 km'],
                ],
            ],
            'popularCampervans' => [
                'visible' => true,
                'eyebrow' => 'Campervans',
                'heading' => 'Sleep where the road ends.',
                'buttonLabel' => 'See campervans',
                'buttonHref' => '/campervans',
            ],
            'seasons' => [
                'visible' => true,
                'eyebrow' => 'When to go',
                'heading' => 'Every season, a different trip.',
                'items' => [
                    ['icon' => 'sun', 'title' => 'Summer', 'months' => 'Jun – Aug', 'text' => 'Campervans & long days'],
                    ['icon' => 'mountain', 'title' => 'Autumn', 'months' => 'Sep – Nov', 'text' => 'Cars + guesthouses'],
                    ['icon' => 'sparkles', 'title' => 'Spring', 'months' => 'Mar – May', 'text' => 'Cars + city stays'],
                    ['icon' => 'snowflake', 'title' => 'Winter', 'months' => 'Dec – Feb', 'text' => '4×4 + cosy stays'],
                ],
            ],
            'popularCars' => [
                'visible' => true,
                'eyebrow' => 'Cars & 4×4',
                'heading' => 'Wheels for every road.',
                'buttonLabel' => 'See cars',
                'buttonHref' => '/cars',
            ],
            'howTicket' => [
                'visible' => true,
                'eyebrow' => 'How it works',
                'heading' => 'Three steps. One booking.',
                'steps' => [
                    ['num' => 'Step 01', 'title' => 'Pick your wheels', 'text' => 'Campervan, car or 4×4 — filter by seats, transmission and winter readiness.'],
                    ['num' => 'Step 02', 'title' => 'Add a bed for each night', 'text' => 'We suggest guesthouses along your route. Skip nights you sleep in the camper.'],
                    ['num' => 'Step 03', 'title' => 'Pay once', 'text' => 'One basket, one secure payment. Your host confirms and you’re set.'],
                ],
            ],
            'placesToStay' => [
                'visible' => true,
                'eyebrow' => 'Places to stay',
                'heading' => 'A warm bed at every stop.',
                'subtitle' => 'Guesthouses, cabins and apartments from local hosts — add them to your trip night by night.',
                'buttonLabel' => 'See stays',
                'buttonHref' => '/guesthouses',
                'tabs' => ['All', 'Guesthouses', 'Cabins', 'Apartments', 'Farm stays'],
            ],
            'guides' => [
                'visible' => true,
                'eyebrow' => 'Guides',
                'heading' => 'Good to know.',
                'subtitle' => 'Practical, short reads to plan a road trip anywhere — wheels, nights and everything in between.',
                'buttonLabel' => 'All guides',
                'buttonHref' => '/good-to-know',
                'items' => [
                    ['tag' => 'Trip planning', 'minutes' => '8 min read', 'title' => 'How to plan a road trip in 7 days', 'text' => 'A simple week-long plan: pick your wheels, map the nights and book every stop in one place.', 'image' => $photo, 'imageAlt' => 'Road trip planning', 'href' => '/good-to-know'],
                    ['tag' => 'Choosing your trip', 'minutes' => '5 min read', 'title' => 'Campervan, or car + stays?', 'text' => 'Freedom on wheels or a warm bed every night — how to choose what fits your trip.', 'image' => $photo, 'imageAlt' => 'Campervan or car', 'href' => '/good-to-know'],
                    ['tag' => 'Before you go', 'minutes' => '6 min read', 'title' => 'Driving abroad: what you need', 'text' => 'Licences, insurance, tolls and local rules to check before you set off.', 'image' => $photo, 'imageAlt' => 'Driving abroad', 'href' => '/good-to-know'],
                ],
            ],
            'hostBanner' => [
                'visible' => true,
                'eyebrow' => 'For hosts',
                'heading' => 'Have a van, a car or a guesthouse?',
                'subtitle' => 'List it on MyTerraBook and reach travellers planning their whole trip.',
                'buttonLabel' => 'Become a host',
                'buttonHref' => '/become-a-host',
                'image' => $photo,
                'imageAlt' => 'A host with their vehicle',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function searchCampervan(): array
    {
        return [
            'title' => 'Campervans for your next adventure',
            'insuranceNote' => 'basic insurance included',
            'breadcrumbHome' => 'Home',
            'breadcrumbSection' => 'Campervans',
            'breadcrumbPlace' => 'All destinations',
            'pickupLabel' => 'Pick-up',
            'datesLabel' => 'Dates',
            'dropoffLabel' => 'Drop-off',
            'dropoffSame' => 'Same as pick-up',
            'datesEmpty' => 'Add dates',
            'updateLabel' => 'Update',
            'filtersLabel' => 'All filters',
            'filtersTitle' => 'Filters',
            'sortLabel' => 'Sort',
            'sortTitle' => 'Sort by',
            'sortPrefix' => 'Sort:',
            'partnerLabel' => 'Rental partner',
            'showAllLabel' => 'Show all',
            'bundleTitle' => 'Not sleeping in the van every night?',
            'bundleText' => 'Add a guesthouse along your route — one booking, one payment.',
            'bundleButton' => 'See guesthouses',
            'bundleHref' => '/guesthouses',
            'showingLabel' => 'Showing {shown} of {total} campervans',
            'loadMoreLabel' => 'Show more',
            'onBoardLabel' => 'On board',
            'fromLabel' => 'from',
            'perLabel' => '/ day',
            'bookLabel' => 'Book',
            'typeLabel' => 'Campervan',
            'clearLabel' => 'Clear all',
            'showLabel' => 'Show {count} vans',
            'matchLabel' => '{count} campervans match',
            'featuresLabel' => 'Features',
            'selectedLabel' => '{count} selected',
            'vehicleTypeLabel' => 'Vehicle type',
            'transmissionLabel' => 'Transmission',
            'seatsLabel' => 'Minimum seats',
            'sleepsLabel' => 'Minimum sleeps',
            'priceLabel' => 'Price per day',
            'anyLabel' => 'Any',
            'sortHints' => [
                ['id' => 'rec', 'hint' => 'Best match for your dates and route'],
                ['id' => 'price-asc', 'hint' => 'Cheapest per day first'],
                ['id' => 'price-desc', 'hint' => 'Most premium first'],
            ],
        ];
    }

    public static function searchCar(): array
    {
        $base = self::searchCampervan();
        return array_replace($base, [
            'title' => 'Cars & 4×4s for every road',
            'insuranceNote' => 'basic insurance (CDW) included',
            'breadcrumbSection' => 'Cars & 4×4s',
            'breadcrumbPlace' => 'Iceland',
            'showingLabel' => 'Showing {shown} of {total} cars',
            'typeLabel' => 'Car',
            'showLabel' => 'Show {count} cars',
            'matchLabel' => '{count} cars match',
            'bundleTitle' => 'Need a bed for the night?',
            'bundleText' => 'Add a guesthouse along your route — one booking, one payment.',
            'bundleButton' => 'Add a guesthouse',
            'bundleHref' => '/guesthouses',
        ]);
    }

    public static function searchGuesthouse(): array
    {
        $base = self::searchCampervan();
        return array_replace($base, [
            'title' => 'Guesthouses along the route',
            'insuranceNote' => 'guesthouses, cottages, cabins and apartments along the Ring Road',
            'breadcrumbSection' => 'Guesthouses',
            'breadcrumbPlace' => 'Iceland',
            'pickupLabel' => 'City or area',
            'datesLabel' => 'Check-in → Check-out',
            'whoLabel' => 'Guests',
            'whoValue' => '2 guests',
            'showingLabel' => 'Showing {shown} of {total} stays',
            'typeLabel' => 'Guesthouse',
            'bookLabel' => 'View',
            'perLabel' => '/ night',
            'showLabel' => 'Show {count} stays',
            'matchLabel' => '{count} stays match',
            'bundleTitle' => 'Need wheels too?',
            'bundleText' => 'Add a campervan or car to the same trip — one booking, one payment.',
            'bundleButton' => 'Browse vehicles',
            'bundleHref' => '/campervans',
        ]);
    }
}
