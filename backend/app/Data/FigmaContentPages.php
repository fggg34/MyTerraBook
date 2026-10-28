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
            'items' => [
                ['num' => '01', 'cat' => 'booking', 'label' => 'BOOKING & PAYMENT', 'q' => 'How does booking work on MyTerraBook?', 'a' => 'Choose your campervan, car or guesthouse, pick your dates and send a booking request. Your host reviews the request — nothing is charged today. Once it is approved you pay a 20% prepayment, and the balance is due on pick-up or check-in.', 'linkLabel' => 'View the booking process', 'linkHref' => '/good-to-know/how-to-plan-a-road-trip-in-7-days'],
                ['num' => '02', 'cat' => 'booking', 'label' => 'BOOKING & PAYMENT', 'q' => 'Which payment methods do you accept?', 'a' => 'We accept Visa, Mastercard and American Express, plus Apple Pay and Google Pay. Card payments are processed securely by Rapyd. A 20% prepayment is taken once the host approves, and the balance is due at pick-up or check-in.'],
                ['num' => '03', 'cat' => 'booking', 'label' => 'BOOKING & PAYMENT', 'q' => 'Can I cancel or change my booking?', 'a' => 'You can cancel free of charge until 48 hours before pick-up or check-in unless the listing states a stricter policy. To change dates, use Request a modification in your account.', 'linkLabel' => 'Read the refund policy', 'linkHref' => '/refund-policy'],
                ['num' => '04', 'cat' => 'insurance', 'label' => 'INSURANCE & COVER', 'q' => 'What insurance is included in the price?', 'a' => 'Each vehicle comes with Basic cover. Premium and Platinum protection can be added at checkout.'],
                ['num' => '05', 'cat' => 'insurance', 'label' => 'INSURANCE & COVER', 'q' => 'Am I covered for gravel, sand and ash damage?', 'a' => 'Basic cover is included with every vehicle. Premium and Platinum add protection for gravel, sand and ash.'],
                ['num' => '06', 'cat' => 'road', 'label' => 'ON THE ROAD', 'q' => 'Can I drive my rental on F-roads and highland routes?', 'a' => 'F-roads are only permitted in vehicles the host has marked as 4×4 and F-road approved. Off-road driving is illegal and never covered.'],
                ['num' => '07', 'cat' => 'road', 'label' => 'ON THE ROAD', 'q' => 'Where do I pick up and drop off my vehicle?', 'a' => 'Pick-up and drop-off locations are shown on each listing. Airport pick-up is available at Keflavík.'],
                ['num' => '08', 'cat' => 'road', 'label' => 'ON THE ROAD', 'q' => 'Is mileage unlimited, and what about one-way trips?', 'a' => 'Mileage is unlimited. One-way fees, when the host offers them, are included in the checkout total.'],
                ['num' => '09', 'cat' => 'booking', 'label' => 'BOOKING & PAYMENT', 'q' => 'What happens if my host cancels?', 'a' => 'If a host cancels an approved booking, you get a full refund and support helps you find a comparable vehicle or stay.', 'extra' => true],
                ['num' => '10', 'cat' => 'insurance', 'label' => 'INSURANCE & COVER', 'q' => 'Is a security deposit taken at pick-up?', 'a' => 'A temporary hold may be placed at pick-up and released after the vehicle is checked. The amount is shown in your booking.', 'extra' => true],
                ['num' => '11', 'cat' => 'road', 'label' => 'ON THE ROAD', 'q' => 'Can I wild camp in a campervan?', 'a' => 'No. Overnight parking outside registered campsites is not allowed for campervans in Iceland.', 'linkLabel' => 'Open the campsite map', 'linkHref' => '/campsite-map', 'extra' => true],
                ['num' => '12', 'cat' => 'booking', 'label' => 'BOOKING & PAYMENT', 'q' => 'How are refunds paid?', 'a' => 'Refunds go back to the original payment method within 5–10 business days, in EUR.', 'extra' => true],
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
                'namePlaceholder' => 'e.g. Anna Jónsdóttir',
                'emailLabel' => 'Email',
                'emailPlaceholder' => 'you@example.com',
                'topicLabel' => 'Topic',
                'topicPlaceholder' => 'Choose a topic',
                'messageLabel' => 'Message',
                'messagePlaceholder' => 'Tell us about your trip, your booking reference or your question…',
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

    /**
     * @return array<string, mixed>
     */
    public static function hostPanel(): array
    {
        return [
            'sidebar' => [
                'hostTag' => 'Host',
                'menuLabel' => 'MENU',
                'supportTitle' => 'Host support',
                'supportLine' => '+354 787 0707 · info@myterrabook.com',
            ],
            'signOutLabel' => 'Sign out',
            'navItems' => [
                ['to' => '/host', 'label' => 'Dashboard'],
                ['to' => '/host/cars', 'label' => 'Cars & vans'],
                ['to' => '/host/guesthouses', 'label' => 'Guesthouses'],
                ['to' => '/host/bookings', 'label' => 'Bookings'],
                ['to' => '/host/integrations', 'label' => 'API connections'],
                ['to' => '/host/settings', 'label' => 'Settings'],
            ],
            'topbar' => [
                'pageTitle' => 'Dashboard',
                'searchPlaceholder' => 'Search bookings, listings…',
                'accountRole' => 'Host account',
            ],
            'onboarding' => [
                'eyebrow' => 'GETTING STARTED',
                'title' => "Welcome, let's get your first listing live",
                'lead' => 'Three short steps and travellers can book your vehicles and guesthouses on MyTerraBook.',
                'progressLabel' => '{done} of {total} complete',
                'steps' => [
                    ['title' => 'Complete your host profile', 'text' => 'Company details, kennitala, partner logo and contract rental terms.', 'status' => 'Completed', 'button' => ''],
                    ['title' => 'Add your first listing', 'text' => 'Create a vehicle or guesthouse with photos, pricing and availability.', 'status' => '', 'button' => 'Add listing'],
                    ['title' => 'Submit for review', 'text' => 'The MyTerraBook team checks your listing, then it goes live for travellers.', 'status' => 'Locked', 'button' => ''],
                ],
            ],
            'overview' => [
                'title' => 'Overview',
                'lead' => 'Your listings and bookings at a glance',
                'periodLabel' => 'This month',
                'tiles' => [
                    ['label' => 'Live', 'note' => 'Listings bookable'],
                    ['label' => 'Revenue', 'note' => 'Paid this month'],
                    ['label' => 'Pending bookings', 'note' => 'Waiting for you'],
                    ['label' => 'Needs attention', 'note' => 'Action required'],
                    ['label' => 'Pending review', 'note' => 'With MyTerraBook'],
                    ['label' => 'Drafts', 'note' => 'Not submitted'],
                    ['label' => 'Needs changes', 'note' => 'Fix and resubmit'],
                ],
            ],
            'calendar' => [
                'title' => 'Reservations calendar',
                'vehiclesTab' => 'Vehicles',
                'staysTab' => 'Guesthouses',
                'today' => 'Today',
                'allVehicles' => 'All vehicles',
                'confirmed' => 'Confirmed',
                'awaiting' => 'Awaiting approval',
                'blocked' => 'Blocked dates',
            ],
            'upcoming' => [
                'title' => 'Upcoming',
                'viewAll' => 'View all',
                'pickupPrefix' => 'Pick-up',
            ],
            'vehicles' => [
                'title' => 'Cars & vans',
                'addLabel' => 'Add vehicle',
                'empty' => 'No vehicles yet',
            ],
            'stays' => [
                'title' => 'Guesthouses',
                'addLabel' => 'Add guesthouse',
                'empty' => 'No guesthouses yet',
            ],
            'bookings' => [
                'title' => 'Bookings',
                'vehiclesTab' => 'Vehicles',
                'staysTab' => 'Guesthouses',
            ],
            'api' => [
                'title' => 'API connections',
                'lead' => 'Share availability with your booking system.',
            ],
            'settings' => [
                'title' => 'Settings',
                'lead' => 'Company details, kennitala, partner logo and contract rental terms.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function clientPanel(): array
    {
        return [
            'sidebarTitle' => 'My trips',
            'signOutLabel' => 'Sign out',
            'navItems' => [
                ['to' => '/dashboard', 'label' => 'My trips'],
                ['to' => '/dashboard/stays', 'label' => 'Stays'],
                ['to' => '/dashboard/settings', 'label' => 'Settings'],
            ],
            'trips' => [
                'kicker' => 'MY TRIPS',
                'title' => 'My trips',
                'lead' => 'Campervans, cars and guesthouses you have booked.',
                'welcome' => 'Welcome back',
                'emptyTitle' => 'No trips yet',
                'emptyText' => 'When you book a campervan, car or guesthouse, it will show up here.',
                'browseLabel' => 'Find a ride',
                'browseHref' => '/campervans',
            ],
            'stays' => [
                'title' => 'Stays',
                'lead' => 'Guesthouse nights you have booked.',
                'emptyTitle' => 'No stays yet',
                'emptyText' => 'When you book a guesthouse, it will show up here.',
            ],
            'history' => [
                'title' => 'Trip history',
                'lead' => 'Cars, campervans and guesthouses you have booked with MyTerraBook.',
                'emptyTitle' => 'No trips yet',
                'emptyText' => 'When you book a car, campervan or guesthouse, it will appear here.',
            ],
            'settings' => [
                'title' => 'Settings',
                'lead' => 'Your name, contact details and password.',
            ],
            'actions' => [
                'addToCalendarLabel' => 'Add to calendar',
                'cancelBookingLabel' => 'Cancel booking',
                'viewListingLabel' => 'View listing',
                'downloadPdfLabel' => 'Download PDF',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function register(): array
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
            'formKicker' => 'CREATE ACCOUNT',
            'formHeadingBefore' => 'Start your ',
            'formHeadingAccent' => 'account',
            'formHeadingAfter' => '.',
            'formLead' => 'Book campervans, cars and guesthouses with one login.',
            'nameLabel' => 'Full name',
            'namePlaceholder' => 'Jane Smith',
            'emailLabel' => 'Email',
            'emailPlaceholder' => 'you@example.com',
            'phoneLabel' => 'Phone',
            'phonePlaceholder' => '555 1234',
            'passwordLabel' => 'Password',
            'confirmPasswordLabel' => 'Confirm password',
            'passwordHint' => 'At least 8 characters',
            'submitLabel' => 'Create account',
            'submittingLabel' => 'Creating account…',
            'loginPrompt' => 'Already have an account?',
            'loginLink' => 'Sign in',
            'forgotPasswordLabel' => 'Forgot password?',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function checkout(): array
    {
        return [
            'labels' => [
                'tripTitle' => 'When & where is your trip?',
                'tripSubtitle' => 'Confirm your dates and choose your pick-up and drop-off times.',
                'extrasTitle' => 'Add extras & choose your cover',
                'extrasSubtitle' => 'Everything below is optional. You can change these until pick-up.',
                'detailsTitle' => 'Your information',
                'detailsSubtitle' => 'We need the main driver\'s details to prepare the rental agreement.',
                'paymentTitle' => 'Payment',
                'paymentSubtitle' => 'Choose how you would like to pay. A prepayment confirms the booking.',
                'dueOnApprovalLabel' => 'Due on approval ({percent}%)',
                'confirmedTitle' => 'Booking confirmed',
                'confirmedLead' => 'Your trip is confirmed. We emailed the details and notified your host.',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function article(): array
    {
        return [
            'header' => [
                'backLabel' => 'All articles',
                'shareLabel' => 'Share',
            ],
            'related' => [
                'eyebrow' => 'KEEP READING',
                'title' => 'Related guides',
                'button' => 'All guides',
                'href' => '/good-to-know',
                'readLabel' => 'Read',
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function comingSoon(): array
    {
        return [
            'hero' => [
                'pill' => 'Launching 2026 · Iceland first',
                'eyebrow' => 'COMING SOON',
                'headingBefore' => 'MyTerraBook is ',
                'headingAccent' => 'almost ready',
                'headingAfter' => '',
                'lead' => 'Finishing touches on the road-trip platform.',
                'sub' => 'Campervans, cars & 4x4s and guesthouses — one booking for the whole trip.',
                'placeholder' => 'Your email address',
                'button' => 'Notify me',
                'fine' => 'One email when we launch. No spam — unsubscribe any time.',
                'success' => 'You’re on the list. We’ll write once, when we launch.',
                'error' => 'Could not save that email just now.',
                'adminLabel' => 'Admins can preview the site',
                'signIn' => 'Sign in',
                'image' => '/figma/login-hero.png',
            ],
            'footer' => [
                'copyright' => '© 2026 MyTerraBook ehf.',
                'contact' => 'info@myterrabook.com · +354 787 0707',
            ],
        ];
    }
}
