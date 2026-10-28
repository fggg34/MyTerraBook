export const LEGAL_TABS = [
  { slug: 'terms', label: 'Terms' },
  { slug: 'privacy', label: 'Privacy' },
  { slug: 'refund-policy', label: 'Refund policy' },
  { slug: 'cookies', label: 'Cookies' },
  { slug: 'cardholder-terms', label: 'Cardholder terms' },
]

const ask = {
  title: 'Questions? info@myterrabook.com',
  body: 'Our team in Reykjanesbær replies within one business day. Phone +354 787 0707 · Mon–Sun 08–22.',
}

export const LEGAL_PAGES = {
  terms: {
    crumb: 'Terms',
    italic: null,
    title: ['Terms of ', 'Service', ''],
    intro: 'These terms apply whenever you search, book or host on MyTerraBook. Please read them before you confirm a booking — they explain what we do, what your host does, and what we expect from you on the road.',
    lead: 'MyTerraBook is a booking marketplace. When you book a campervan, car, 4x4 or guesthouse, your contract for the rental or stay is with the host; MyTerraBook ehf. runs the platform, takes payment on the host’s behalf and supports you before and during your trip.',
    sections: [
      { id: 'who', num: '01', title: 'Who we are', paragraphs: [
        'myterrabook.com is operated by MyTerraBook ehf., a private limited company registered in Iceland, 260 Reykjanesbær. In these terms “MyTerraBook”, “we” and “us” mean MyTerraBook ehf.; “host” means the rental company or accommodation provider listing on the platform; “you” means the person making the booking.',
        'Listings, prices, vehicle specifications and house rules are provided by hosts. We review listings before they go live, but the host remains responsible for the accuracy of its listing and for delivering the rental or stay.',
      ] },
      { id: 'account', num: '02', title: 'Your account', paragraphs: [
        'You need an account to book. You must be at least 18 years old, give accurate details and keep your password private. You are responsible for activity on your account.',
      ], list: [
        'One account per person — accounts may not be shared or transferred.',
        'Bookings for vehicles must be made in the name of the main driver.',
        'We may suspend accounts used for fraud, abuse of hosts or repeated no-shows.',
      ] },
      { id: 'pay', num: '03', title: 'Bookings and payment', paragraphs: [
        'A booking is a request until the host approves it. Once approved you receive a confirmation e-mail and voucher, and the agreement between you and the host becomes binding.',
        'Prices are shown in EUR; other currencies (USD, GBP, ISK) are estimates only. Card payments are processed securely by our payment partner Rapyd. The total you see at checkout includes the rental rate, any one-way, location or cleaning fees, and applicable taxes.',
      ], callout: { icon: 'info', title: 'Nothing is charged until your host approves', body: 'When you request to book, your host reviews the request — nothing is charged today. A 20% prepayment is taken once the host approves, and the balance is due at pick-up or check-in.' } },
      { id: 'cancel', num: '04', title: 'Cancellations and changes', paragraphs: [
        'You can cancel free of charge until 48 hours before pick-up or check-in unless the listing states a stricter policy. Late cancellations and no-shows are handled under our Refund policy.',
        'To change dates, locations or extras, use “Request a modification” in your account. The host may accept or decline, and you will see the new total before anything changes.',
      ] },
      { id: 'road', num: '05', title: 'On the road', paragraphs: [
        'Drivers must hold a full licence valid in Iceland for at least one year and carry it at pick-up. Each vehicle comes with Basic cover; Premium and Platinum protection can be added at checkout.',
      ], list: [
        'Off-road driving is illegal in Iceland and is never covered by insurance.',
        'F-roads are only permitted in vehicles the host has marked as 4x4 and F-road approved.',
        'Check road.is and vedur.is before every drive — closures are not a reason for compensation.',
      ] },
      { id: 'law', num: '06', title: 'Liability and governing law', paragraphs: [
        'MyTerraBook is liable for running the platform with reasonable care. Hosts are liable for the vehicle or property they provide. Nothing in these terms limits rights you have under mandatory consumer law.',
        'These terms are governed by Icelandic law. Disputes go to the District Court of Reykjavík, unless consumer rules in your country of residence give you the right to use your local courts.',
      ] },
    ],
    ask,
  },
  privacy: {
    crumb: 'Privacy',
    title: ['', 'Privacy', ' Policy'],
    intro: 'How MyTerraBook ehf. collects, uses and protects personal data when you browse, book or host on myterrabook.com — and the choices you have.',
    lead: 'We only collect what we need to run your booking and keep the platform safe. We share it with your host so they can deliver your rental or stay, and with a small number of service providers who work for us. We never sell your data.',
    sections: [
      { id: 'who', num: '01', title: 'Who is responsible', paragraphs: [
        'MyTerraBook ehf., 260 Reykjanesbær, Iceland, is the data controller for personal data processed on myterrabook.com. We follow the EU General Data Protection Regulation (GDPR) as implemented in Iceland by Act No. 90/2018.',
        'For anything privacy-related, contact info@myterrabook.com. Hosts are separate controllers for the data they receive to fulfil your booking.',
      ] },
      { id: 'collect', num: '02', title: 'What we collect', paragraphs: ['Depending on what you do on the platform, we process:'], list: [
        'Account data — name, e-mail, phone number and password (stored hashed).',
        'Booking data — dates, pick-up and drop-off locations, flight number, travellers, date of birth, driving licence number and issuing country.',
        'Payment data — billing address and the last four digits of your card; full card details are handled by Rapyd.',
        'Usage data — device, pages viewed and search filters, collected with your consent where required (see Cookies).',
      ] },
      { id: 'use', num: '03', title: 'How we use it', paragraphs: [
        'We use personal data to create and manage your account, pass bookings to hosts, take payments, send confirmations and pre-trip information, provide support, prevent fraud and meet accounting obligations.',
        'Our legal bases are performance of a contract, legal obligation, our legitimate interest in a safe and working service, and your consent for marketing e-mails and non-essential cookies.',
      ], callout: { icon: 'shield', title: 'We never sell your data', body: 'Your details are shared only with the host you book with and with processors bound by data-processing agreements. Card numbers are handled by Rapyd and never stored on our servers.' } },
      { id: 'share', num: '04', title: 'Who we share it with', paragraphs: [
        'Your host receives the booking and driver details needed to hand over the vehicle or welcome you. We also use processors for hosting, e-mail delivery, payments (Rapyd) and analytics. Some of them may process data outside the EEA under the EU Standard Contractual Clauses.',
      ] },
      { id: 'rights', num: '05', title: 'Retention and your rights', paragraphs: [
        'We keep booking and accounting records for seven years as required by Icelandic bookkeeping law; other data is deleted or anonymised when no longer needed, or two years after your last activity.',
      ], list: [
        'Access, correct or delete your data from Settings or by e-mail.',
        'Object to processing or withdraw consent at any time.',
        'Ask for a copy of your data in a portable format.',
        'Complain to Persónuvernd, the Icelandic Data Protection Authority.',
      ] },
    ],
    ask,
  },
  'refund-policy': {
    crumb: 'Refund policy',
    title: ['', 'Refund', ' Policy'],
    intro: 'What happens to your money if you cancel, change or cut a trip short — for campervans, cars & 4x4s and guesthouses booked on MyTerraBook.',
    lead: 'Most bookings can be cancelled free of charge until 48 hours before pick-up or check-in. Some hosts use a stricter policy; when they do, it is shown on the listing and again at checkout before you pay anything.',
    sections: [
      { id: 'free', num: '01', title: 'Free cancellation', paragraphs: [
        'If you cancel more than 48 hours before the start of your booking, the full prepayment is refunded and no balance is due. Cancel from Trip history in your account or reply to your confirmation e-mail.',
      ] },
      { id: 'late', num: '02', title: 'Late cancellations and no-shows', paragraphs: ['How much is refunded depends on the host’s policy:'], list: [
        'Flexible — full refund until 48 hours before; after that the prepayment is kept.',
        'Moderate — 50% of the prepayment refunded until 24 hours before.',
        'Strict — the prepayment is non-refundable once the host has approved the booking.',
        'No-show — if you do not arrive and have not told us, the booking is treated as a late cancellation.',
      ], callout: { icon: 'snowflake', title: 'Weather and road closures', body: 'Icelandic weather changes fast. If a storm warning or road closure stops you reaching pick-up, contact us right away — we will work with your host on new dates first. Refunds still follow the host’s cancellation policy.' } },
      { id: 'changes', num: '03', title: 'Changes and early returns', paragraphs: [
        'Date or location changes are requests to the host and may change the price — you will always see the new total before you confirm. Returning a vehicle early or leaving a guesthouse before check-out does not entitle you to a refund for unused days or nights, unless the host agrees.',
      ] },
      { id: 'host', num: '04', title: 'If your host cancels', paragraphs: [
        'If a host cancels an approved booking, you get a full refund of everything you paid. Our support team will also help you find a comparable vehicle or stay, and we may cover a reasonable price difference.',
      ] },
      { id: 'paid', num: '05', title: 'How refunds are paid', paragraphs: [
        'Refunds go back to the original payment method within 5–10 business days of being approved. Amounts are refunded in EUR; your bank’s exchange rate may make the final amount slightly different from what you paid in another currency. Security deposits are released by the host after the vehicle has been checked.',
      ] },
    ],
    ask,
  },
  cookies: {
    crumb: 'Cookies',
    title: ['', 'Cookie', ' Policy'],
    intro: 'Which cookies and similar technologies myterrabook.com uses, why we use them, and how you can switch the optional ones on or off.',
    lead: 'We use a small number of strictly necessary cookies to make search and checkout work. Analytics and marketing cookies are optional and only set after you agree — you can change your choice at any time below or from “Cookie settings” in the footer.',
    cookies: true,
    sections: [
      { id: 'what', num: '01', title: 'What cookies are', paragraphs: [
        'Cookies are small text files stored on your device when you visit a website. Similar technologies, such as local storage and pixels, work in the same way. We refer to all of them as “cookies” in this policy.',
      ] },
      { id: 'how', num: '02', title: 'How we use them', paragraphs: [
        'Cookies let us keep you signed in, remember your destination, dates and preferred currency between pages, protect checkout against fraud and — with your consent — measure how the site is used and how our campaigns perform.',
      ] },
      { id: 'cats', num: '03', title: 'Cookie categories', paragraphs: [
        'Necessary cookies cannot be switched off because the site does not work without them. Everything else is your choice:',
      ] },
      { id: 'change', num: 'note', title: '', callout: { icon: 'sparkles', title: 'Change your mind any time', body: 'Your choice is saved for 12 months. Open “Cookie settings” in the site footer to review or withdraw consent — withdrawing does not affect bookings you have already made.' } },
      { id: 'third', num: '04', title: 'Third-party cookies', paragraphs: [
        'Some cookies are set by partners: Rapyd during payment, Google for analytics and advertising, and Meta for social campaigns. These providers have their own privacy policies and may process data outside the EEA under appropriate safeguards.',
      ] },
      { id: 'browser', num: '05', title: 'Managing cookies in your browser', paragraphs: [
        'Besides our settings, you can block or delete cookies in your browser. Blocking necessary cookies may stop sign-in, search filters or checkout from working correctly.',
      ] },
    ],
    ask,
  },
  'cardholder-terms': {
    crumb: 'Cardholder terms',
    title: ['', 'Cardholder', ' Terms'],
    intro: 'Information for card payments on myterrabook.com — who takes your payment, how much is charged and when, and how refunds and disputes are handled.',
    lead: 'Card payments on myterrabook.com are taken by MyTerraBook ehf. on behalf of the host you book with and processed securely by our payment partner Rapyd. These terms complement our Terms of Service and Refund policy.',
    sections: [
      { id: 'merchant', num: '01', title: 'Merchant information', paragraphs: [
        'Merchant: MyTerraBook ehf., 260 Reykjanesbær, Iceland. Phone +354 787 0707, e-mail info@myterrabook.com, open Mon–Sun 08–22 (GMT). The merchant country is Iceland and the transaction currency is EUR.',
      ] },
      { id: 'methods', num: '02', title: 'Accepted payment methods', paragraphs: [
        'We accept Visa, Mastercard and American Express credit and debit cards, Apple Pay and Google Pay. Where available, Klarna lets you pay in three instalments, subject to Klarna’s own terms and approval.',
      ] },
      { id: 'when', num: '03', title: 'When you are charged', paragraphs: ['Nothing is charged when you send a booking request.'], list: [
        '20% prepayment — charged when the host approves your request.',
        'Balance — charged or paid at pick-up or check-in, as shown in your payment schedule.',
        'Security deposit — a temporary hold may be placed by the host at pick-up and released after return.',
        'Extras or damage — only charged with a written explanation and in line with the rental agreement.',
      ], callout: { icon: 'file', title: 'How the charge appears on your statement', body: 'Payments show as “MYTERRABOOK” followed by your booking reference. If you don’t recognise a charge, contact us before contacting your bank — we can usually resolve it the same day.' } },
      { id: 'security', num: '04', title: 'Security', paragraphs: [
        'All payments are encrypted and processed by Rapyd, a PCI DSS Level 1 certified provider. Card transactions may require 3-D Secure authentication from your bank. MyTerraBook never stores full card numbers or CVC codes.',
      ] },
      { id: 'disputes', num: '05', title: 'Refunds and disputes', paragraphs: [
        'Refunds are made to the card used for the original payment in accordance with our Refund policy, normally within 5–10 business days. If you dispute a charge, please contact us first; chargebacks raised without contacting us may delay the outcome.',
      ] },
      { id: 'delivery', num: '06', title: 'Delivery of service', paragraphs: [
        'The service — the vehicle rental or accommodation — is delivered by the host on the dates in your confirmation. Your voucher with booking reference, pick-up or check-in details and host contact is sent by e-mail and is always available in your account.',
      ] },
    ],
    ask,
  },
}
