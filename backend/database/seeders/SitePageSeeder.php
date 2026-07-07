<?php

namespace Database\Seeders;

use App\Models\SitePage;
use Illuminate\Database\Seeder;

class SitePageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'slug' => 'about',
                'title' => 'About MyTerraBook',
                'eyebrow' => 'Our story',
                'lead' => "Iceland's locally-run platform for campervans, cars, 4×4s and guesthouses, one booking, one team in Reykjavík behind it.",
                'body' => <<<'HTML'
<p>MyTerraBook started with a simple idea: travellers should be able to plan an entire Iceland trip in one place, with real people on the other end of the phone when the weather turns.</p>
<p>We are a Reykjavík-based team working with trusted local hosts and fleet partners across the island. Every listing is hand-checked, every price is transparent, and every booking comes with gravel protection, local support, and clear cancellation terms.</p>
<p>Whether you are driving the Ring Road in a campervan, adding a 4×4 for the highlands, or booking a guesthouse between adventures, we are here to make it straightforward.</p>
HTML,
                'content' => null,
            ],
            [
                'slug' => 'faq',
                'title' => 'Questions & answers',
                'eyebrow' => 'Good to know',
                'lead' => 'Still curious before you book? Our team in Reykjavík is one message away.',
                'body' => null,
                'content' => [
                    'phone' => '+354 519 1010',
                    'email' => 'support@myterrabook.is',
                    'items' => [
                        [
                            'num' => '01',
                            'question' => 'What do I need to rent and drive in Iceland?',
                            'answer' => "A full driving licence held for at least one year and a card in the main driver's name. Most vehicles ask that you're 20+, or 23+ for the larger 4×4s. Bring your licence and passport to pickup, that's it.",
                            'open' => true,
                        ],
                        [
                            'num' => '02',
                            'question' => 'Is gravel and ash damage covered?',
                            'answer' => "Yes. Gravel protection and sand-and-ash cover are built into every booking, not sold separately at the counter, they're the two things that catch most travellers out on Iceland's roads.",
                        ],
                        [
                            'num' => '03',
                            'question' => 'Where can I sleep in a campervan?',
                            'answer' => "At any of Iceland's registered campsites, which line the Ring Road. Wild camping in a vehicle isn't permitted, so we send a campsite map with every van booking to make planning each night easy.",
                        ],
                        [
                            'num' => '04',
                            'question' => 'What if a road closes or the weather turns?',
                            'answer' => "Call our Reykjavík team any hour and we'll help you re-route, find a stay, or rethink the plan. Live road and weather alerts are also pinned inside your booking so nothing catches you off guard.",
                        ],
                        [
                            'num' => '05',
                            'question' => 'How does pickup near Keflavík work?',
                            'answer' => "Land, hop on the quick shuttle, and collect your keys minutes from the terminal. You'll get a hands-on walkthrough of the vehicle before you drive off, with studded tyres and gear already fitted for the season.",
                        ],
                    ],
                ],
            ],
            [
                'slug' => 'contact',
                'title' => 'Contact us',
                'eyebrow' => 'We are here to help',
                'lead' => 'Questions about a booking, a listing, or planning your trip? Reach our Reykjavík team.',
                'body' => null,
                'content' => [
                    'phone' => '+354 519 1010',
                    'email' => 'support@myterrabook.is',
                    'address' => "MyTerraBook ehf.\nLaugavegur 178 · 105 Reykjavík · Iceland",
                    'hours' => 'Mon–Sun · 08:00–22:00 GMT',
                    'show_form' => true,
                ],
            ],
            [
                'slug' => 'terms',
                'title' => 'Terms & conditions',
                'eyebrow' => 'Legal',
                'lead' => 'Rental and stay terms for bookings made through MyTerraBook.',
                'body' => <<<'HTML'
<h2>1. Bookings</h2>
<p>By completing a booking on MyTerraBook you enter into an agreement with the listed host or fleet partner, facilitated by MyTerraBook ehf. Prices shown include mandatory insurance bundles unless stated otherwise.</p>
<h2>2. Driver requirements</h2>
<p>All drivers must hold a valid licence for at least one year and meet the minimum age for the selected vehicle category. Additional drivers must be declared before pickup.</p>
<h2>3. Cancellations</h2>
<p>Free cancellation up to 48 hours before pickup or check-in applies on most listings unless a stricter policy is shown on the listing page. Late cancellations may incur fees set by the host.</p>
<h2>4. Deposits & payments</h2>
<p>Security deposits are pre-authorised on your card at pickup or check-in. Final charges are settled according to the booking confirmation and any agreed extras.</p>
<h2>5. Liability</h2>
<p>Travellers are responsible for fines, tolls, and damage outside covered insurance events. MyTerraBook acts as booking agent unless otherwise stated on your confirmation.</p>
HTML,
                'content' => null,
            ],
            [
                'slug' => 'privacy',
                'title' => 'Privacy policy',
                'eyebrow' => 'Legal',
                'lead' => 'How we collect, use, and protect your personal data.',
                'body' => <<<'HTML'
<h2>Data we collect</h2>
<p>We collect information you provide when creating an account, making a booking, or contacting support, including name, email, phone, payment details, and travel dates.</p>
<h2>How we use it</h2>
<p>Your data is used to process bookings, communicate about your trip, prevent fraud, and improve our services. We share necessary details with hosts and payment providers to fulfil your booking.</p>
<h2>Retention</h2>
<p>We retain booking records as required by Icelandic law and for legitimate business purposes. You may request access or deletion where applicable by emailing support@myterrabook.is.</p>
<h2>Cookies</h2>
<p>See our <a href="/cookies">cookie policy</a> for details on analytics and preference cookies.</p>
HTML,
                'content' => null,
            ],
            [
                'slug' => 'refund-policy',
                'title' => 'Refund policy',
                'eyebrow' => 'Legal',
                'lead' => 'How refunds and cancellations work for bookings made through MyTerraBook.',
                'body' => <<<'HTML'
<h2>Online payments</h2>
<p>When you pay online at checkout, the amount charged is shown on your booking confirmation. Refunds for eligible cancellations are returned to the original payment method.</p>
<h2>Cancellation windows</h2>
<p>Most listings offer free cancellation up to 48 hours before pickup or check-in unless a stricter policy is shown on the listing page. Late cancellations may be partially or fully non-refundable according to the host's policy.</p>
<h2>Partial payments</h2>
<p>Where a booking includes an online deposit and a balance due on arrival, only the online portion is refundable when a cancellation qualifies. The remaining balance is not charged if you cancel within the free cancellation window.</p>
<h2>Processing time</h2>
<p>Approved refunds are typically processed within 5–10 business days, depending on your card issuer or payment provider.</p>
<h2>How to request a refund</h2>
<p>Cancel through your booking dashboard or contact support@myterrabook.is with your booking reference. We will confirm eligibility and next steps.</p>
HTML,
                'content' => null,
            ],
            [
                'slug' => 'cookies',
                'title' => 'Cookie policy',
                'eyebrow' => 'Legal',
                'lead' => 'How MyTerraBook uses cookies and similar technologies.',
                'body' => <<<'HTML'
<h2>Essential cookies</h2>
<p>Required for sign-in, checkout, and security. These cannot be disabled while using the site.</p>
<h2>Analytics cookies</h2>
<p>Help us understand how visitors use the site so we can improve search, listings, and content. You can opt out via your browser settings.</p>
<h2>Preference cookies</h2>
<p>Remember language, currency, and session choices to make return visits smoother.</p>
<h2>Managing cookies</h2>
<p>Most browsers let you block or delete cookies. Blocking essential cookies may limit booking functionality.</p>
HTML,
                'content' => null,
            ],
        ];

        foreach ($pages as $page) {
            SitePage::query()->updateOrCreate(
                ['slug' => $page['slug']],
                [
                    ...$page,
                    'is_published' => true,
                    'published_at' => now(),
                ],
            );
        }
    }
}
