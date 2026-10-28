<?php

namespace App\Data;

/**
 * Default copy for the public pages rebuilt from Figma.
 * These are the sections shown in Site content.
 */
class FigmaContentPages
{
    /**
     * @return array<string, mixed>
     */
    public static function about(): array
    {
        return [
            'hero' => [
                'crumb' => 'About us',
                'eyebrow' => 'ABOUT MYTERRABOOK',
                'headingBefore' => 'One booking for the ',
                'headingAccent' => 'whole',
                'headingAfter' => ' road trip.',
                'lead' => 'MyTerraBook brings campervans, cars & 4×4s and guesthouses together in one place — so you can plan the drive, the wheels and the bed for the night in a single booking. Born in Reykjanesbær, built for every country.',
                'primaryLabel' => 'Find your ride',
                'primaryHref' => '/campervans',
                'secondaryLabel' => 'Become a host',
                'secondaryHref' => '/become-a-host',
                'image' => '/figma/login-hero.png',
            ],
            'stats' => [
                ['icon' => 'star', 'value' => '4.9', 'text' => 'Average rating from 12,400+ reviews'],
                ['icon' => 'sliders', 'value' => '350+', 'text' => 'Campervans, cars & guesthouses listed'],
                ['icon' => 'headset', 'value' => '24/7', 'text' => 'Local support, every day of your trip'],
                ['icon' => 'calendar', 'value' => '48h', 'text' => 'Free cancellation up to 48h before pick-up'],
            ],
            'story' => [
                'eyebrow' => 'OUR STORY',
                'headingBefore' => 'We started where the ',
                'headingAccent' => 'road',
                'headingAfter' => ' starts.',
                'image' => '/figma/about-camper.png',
                'address' => 'MyTerraBook ehf. · 260 Reykjanesbær, Iceland',
                'paragraphs' => [
                    ['text' => 'MyTerraBook began in Reykjanesbær, ten minutes from Keflavík airport — the place almost every Iceland road trip begins. We watched travellers juggle three websites, three deposits and three sets of terms just to get a van, a car and a bed for the night.'],
                    ['text' => 'So we built one platform for all of it. Local rental companies and guesthouse owners list directly with us, and travellers book the whole trip in one go — with clear prices, real insurance and people on the ground who answer the phone.'],
                    ['text' => 'Today we work with local partners like Konvin, Green Light and Foxie, and we are building MyTerraBook for road trips in every country.'],
                ],
            ],
            'values' => [
                'eyebrow' => 'WHAT WE STAND FOR',
                'headingBefore' => 'Three things we ',
                'headingAccent' => 'never',
                'headingAfter' => ' compromise on.',
                'lead' => 'The promises behind every van, car and guesthouse on MyTerraBook.',
                'items' => [
                    ['icon' => 'mountain', 'title' => 'Local first', 'text' => 'Every listing comes from a local company or host who knows the roads, the weather and the hidden detours.'],
                    ['icon' => 'tag', 'title' => 'Honest prices', 'text' => 'What you see is what you pay — rental rate, fees and taxes shown up front, with free cancellation until 48h before pick-up.'],
                    ['icon' => 'shield', 'title' => 'Covered on every road', 'text' => 'Basic insurance is always included, with Premium and Platinum cover for gravel, sand & ash when you want peace of mind.'],
                ],
            ],
            'products' => [
                'eyebrow' => 'ONE PLATFORM',
                'headingBefore' => 'Everything your trip ',
                'headingAccent' => 'needs',
                'headingAfter' => '.',
                'lead' => 'Pick one, or combine all three in a single booking.',
                'button' => 'See all listings',
                'href' => '/campervans',
                'items' => [
                    ['icon' => 'tent', 'title' => 'Campervans', 'text' => 'Drive and sleep in one — from compact 2-seaters to 4×4 campers that are winter-ready.', 'meta' => '15 listings · from €59.80/day', 'button' => 'Browse campervans', 'href' => '/campervans', 'image' => '/figma/about-camper.png'],
                    ['icon' => 'gauge', 'title' => 'Cars & 4×4s', 'text' => 'Small city cars for the Ring Road, rugged 4×4s for the highlands and F-roads.', 'meta' => '18 listings · from €47.60/day', 'button' => 'Browse cars', 'href' => '/cars', 'image' => '/figma/about-car.png'],
                    ['icon' => 'heart', 'title' => 'Guesthouses', 'text' => 'Cosy rooms, cottages and farm stays — a warm bed at the end of every driving day.', 'meta' => '9 listings · from €3.57/night', 'button' => 'Browse guesthouses', 'href' => '/guesthouses', 'image' => '/figma/about-stay.png'],
                ],
            ],
            'cta' => [
                'eyebrow' => 'READY WHEN YOU ARE',
                'headingBefore' => 'Your next road trip starts ',
                'headingAccent' => 'here',
                'headingAfter' => '.',
                'lead' => 'Search campervans, cars and guesthouses across Iceland — and soon, every country.',
                'primaryLabel' => 'Plan your trip',
                'primaryHref' => '/campervans',
                'secondaryLabel' => 'Become a host',
                'secondaryHref' => '/become-a-host',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function faq(): array
    {
        return [
            'hero' => [
                'crumb' => 'FAQs',
                'eyebrow' => 'HELP CENTRE',
                'headingBefore' => 'Questions? We have ',
                'headingAccent' => 'answers',
                'headingAfter' => '.',
                'lead' => 'Everything about booking, paying, insurance and life on the road — from the team that drives these roads every day.',
                'searchPlaceholder' => 'Search questions',
                'searchButton' => 'Search',
                'popularLabel' => 'Popular:',
                'chips' => ['Cancellation', 'Insurance', 'F-roads', 'Pick-up'],
            ],
            'categories' => [
                ['id' => 'all', 'label' => 'All', 'icon' => 'grid'],
                ['id' => 'booking', 'label' => 'Booking & payment', 'icon' => 'card'],
                ['id' => 'insurance', 'label' => 'Insurance & cover', 'icon' => 'shield'],
                ['id' => 'road', 'label' => 'On the road', 'icon' => 'route'],
            ],
            'help' => [
                'headingBefore' => 'Need a ',
                'headingAccent' => 'human',
                'headingAfter' => '?',
                'body' => 'Our local team in Reykjanesbær answers every day, Mon–Sun 08–22.',
                'phoneLabel' => 'Call us',
                'phone' => '+354 787 0707',
                'emailLabel' => 'Email us',
                'email' => 'info@myterrabook.com',
                'hoursLabel' => 'Opening hours',
                'hours' => 'Mon–Sun 08–22',
                'buttonLabel' => 'Contact us',
                'buttonHref' => '/contact',
            ],
            'guide' => [
                'title' => 'Still planning your route?',
                'body' => 'Read our guides on routes, driving and packing in Iceland.',
                'buttonLabel' => 'Go to Good to Know',
                'buttonHref' => '/good-to-know',
            ],
            'more' => [
                'prompt' => 'Didn’t find it? Try a category above or',
                'button' => 'Show more questions',
                'empty' => 'No questions match that search. Try a category above, or contact the team.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function contact(): array
    {
        return [
            'hero' => [
                'crumb' => 'Contact',
                'eyebrow' => 'CONTACT US',
                'headingBefore' => 'Talk to a local. We’re here to ',
                'headingAccent' => 'help',
                'headingAfter' => '.',
                'lead' => 'Questions about a booking, a vehicle or becoming a host? Our team in Reykjanesbær answers every day, Mon–Sun 08–22.',
            ],
            'methods' => [
                ['icon' => 'phone', 'label' => 'PHONE', 'value' => '+354 787 0707', 'note' => 'Mon–Sun 08–22 · English & Icelandic', 'action' => 'Call now', 'href' => 'tel:+3547870707'],
                ['icon' => 'mail', 'label' => 'EMAIL', 'value' => 'info@myterrabook.com', 'note' => 'We reply within opening hours', 'action' => 'Send email', 'href' => 'mailto:info@myterrabook.com'],
                ['icon' => 'pin', 'label' => 'ADDRESS', 'value' => 'MyTerraBook ehf. 260 Reykjanesbær, Iceland', 'note' => '10 minutes from Keflavík airport', 'action' => 'Get directions', 'href' => 'https://maps.google.com/?q=Reykjanesb%C3%A6r+Iceland'],
                ['icon' => 'clock', 'label' => 'HOURS', 'value' => 'Mon–Sun 08–22', 'note' => 'Local support 24/7 during your trip', 'action' => '', 'href' => ''],
            ],
            'social' => [
                'eyebrow' => 'FOLLOW THE ROAD',
                'text' => 'Trip ideas, road conditions & hidden detours',
                'links' => [
                    ['label' => 'Instagram', 'href' => 'https://www.instagram.com/myterrabook', 'icon' => 'instagram'],
                    ['label' => 'Facebook', 'href' => 'https://www.facebook.com/myterrabook', 'icon' => 'facebook'],
                ],
            ],
            'form' => [
                'eyebrow' => 'SEND US A MESSAGE',
                'heading' => 'How can we help?',
                'lead' => 'Fill in the form and we’ll get back to you by email.',
                'nameLabel' => 'Your name',
                'emailLabel' => 'Email',
                'topicLabel' => 'Topic',
                'messageLabel' => 'Message',
                'topics' => ['New booking', 'Existing booking', 'Insurance & damage', 'Becoming a host', 'Other'],
                'privacy' => 'By sending this message you agree to our',
                'submit' => 'Send message',
                'sending' => 'Sending…',
            ],
            'email' => 'info@myterrabook.com',
            'map' => [
                'title' => 'MyTerraBook ehf.',
                'address' => '260 Reykjanesbær, Iceland',
                'button' => 'Get directions',
                'href' => 'https://maps.google.com/?q=Reykjanesb%C3%A6r+Iceland',
                'embed' => 'https://maps.google.com/maps?q=Reykjanesb%C3%A6r%20Iceland&z=11&output=embed',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function guides(): array
    {
        return [
            'hero' => [
                'crumb' => 'Good to Know',
                'eyebrow' => 'GOOD TO KNOW',
                'headingBefore' => 'Guides, routes & ',
                'headingAccent' => 'tips',
                'headingAfter' => '',
                'lead' => 'Itineraries, day trips, driving advice and packing lists from people who drive Iceland’s roads every day.',
                'searchPlaceholder' => 'Search guides',
                'categories' => ['All', 'Itinerary', 'Nature', 'Day trip', 'Driving', 'Packing'],
            ],
            'list' => [
                'heading' => 'Latest guides',
                'sortNewest' => 'Newest',
                'sortOldest' => 'Oldest',
                'readLabel' => 'Read',
                'featuredLabel' => 'Featured',
                'featuredButton' => 'Read the guide',
            ],
            'newsletter' => [
                'heading' => 'New guides, straight to your inbox.',
                'lead' => 'Road conditions, seasonal deals & hidden detours.',
                'placeholder' => 'your@email.com',
                'button' => 'Subscribe',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function campsite(): array
    {
        return [
            'hero' => [
                'crumb' => 'Campsite map',
                'eyebrow' => 'CAMPSITE MAP',
                'headingBefore' => 'Find a place to park for the ',
                'headingAccent' => 'night',
                'headingAfter' => '.',
                'lead' => 'Campsites across Iceland on one map — with season dates and facilities, so you always know where you can stop with your campervan.',
                'button' => 'Find a campervan',
                'buttonHref' => '/campervans',
            ],
            'filters' => [
                'searchPlaceholder' => 'Search campsites',
                'regionLabel' => 'Region',
                'regions' => ['All regions', 'Capital', 'South', 'Southeast', 'East', 'North', 'West'],
                'winterLabel' => 'Open in winter',
                'clearLabel' => 'Clear',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function login(): array
    {
        return [
            'visual' => [
                'kicker' => 'ROAD TRIPS · ICELAND',
                'headingBefore' => 'One booking for the ',
                'headingAccent' => 'whole',
                'headingAfter' => ' trip.',
                'lead' => 'Campervans, cars & 4x4s and guesthouses from local hosts — booked together, managed in one place.',
                'pills' => [
                    ['icon' => 'star', 'text' => '4.9/5 · 12,400+ reviews'],
                    ['icon' => 'shield', 'text' => 'Free cancellation 48h'],
                    ['icon' => 'headset', 'text' => 'Local support 24/7'],
                ],
            ],
            'backLabel' => 'Back to MyTerraBook',
            'newPrompt' => 'New to MyTerraBook?',
            'newLink' => 'Create account',
            'copyright' => '© 2026 MyTerraBook ehf.',
            'formKicker' => 'WELCOME BACK',
            'formHeadingBefore' => 'Sign in to your ',
            'formHeadingAccent' => 'trip',
            'formHeadingAfter' => '.',
            'formLead' => 'Manage bookings, download vouchers and pick up right where you left off.',
            'emailLabel' => 'Email',
            'passwordLabel' => 'Password',
            'rememberLabel' => 'Remember me',
            'forgotPasswordLabel' => 'Forgot password?',
            'submitLabel' => 'Sign in',
            'signingInLabel' => 'Signing in…',
            'orLabel' => 'or',
            'registerLink' => 'Create an account',
            'hostSignInLabel' => 'Sign in as a host →',
        ];
    }
}
