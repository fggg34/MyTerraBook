/** Listing page copy & structure per storefront post type (matches Listing.html). */

export const LISTING_TYPES = {
  campervan: {
    id: 'campervan',
    categoryNames: ['Van', 'SUV'],
    archiveRoute: '/campervans',
    archiveLabel: 'See all campervans',
    dateStartLabel: 'Pick-up',
    dateEndLabel: 'Drop-off',
    rateUnit: 'day',
    rateLabelDefault: 'Daily rate',
    bookCta: 'Request to Book',
    similarTitle: 'Similar rentals available on your dates',
    guestPhotosTitle: 'Guest photos',
    reviewsTitle: 'Guest reviews',
    bookingModalTitle: 'Booking this camper in 4 steps',
    bookingModalLead:
      "Every reservation on MyTerraBook is reviewed by the host before it's confirmed — here's exactly what happens after you hit request.",
    tabs: [
      { id: 'details', label: 'Details' },
      { id: 'amenities', label: 'Amenities' },
      { id: 'conditions', label: 'Rental conditions' },
      { id: 'sleeping', label: 'Sleeping arrangement' },
      { id: 'addons', label: 'Host add-ons' },
    ],
    trustPoints: [
      { html: '<b>Comprehensive insurance</b> and 24/7 roadside assistance' },
      { html: 'Affordable security deposit with our <b>Excess Insurance</b>' },
      { html: 'Pay in instalments via a trusted third-party provider' },
      { html: '<b>Flexible cancellation</b> in case your plans change' },
    ],
    bookingSteps: [
      { title: 'Choose your dates & send a request', text: "Pick your pick-up and drop-off dates and add any extras. Sending a request doesn't charge your card yet." },
      { title: 'The host confirms availability', text: "Your host reviews your trip and approves it — usually within an hour. You'll get a message and email the moment it's accepted.", tag: 'Avg. reply under 1 hour' },
      { title: 'Pay securely & get confirmed', text: 'Once approved, pay in full or in instalments via our trusted payment provider. Your insurance and deposit terms are locked in.', tag: 'Free cancellation up to 48h' },
      { title: 'Pick up & hit the Ring Road', text: 'Meet your host for a full walkthrough of every system, then you\'re off. Support is a call away the whole trip.' },
    ],
    faqLead: 'Everything you need to know before you book this camper. Still unsure? Your host usually replies within the hour.',
  },
  car: {
    id: 'car',
    categoryNames: ['Economy', 'Compact', 'Mid-size', 'Luxury', 'Electric', 'SUV'],
    archiveRoute: '/cars',
    archiveLabel: 'See all cars',
    dateStartLabel: 'Pick-up',
    dateEndLabel: 'Drop-off',
    rateUnit: 'day',
    rateLabelDefault: 'Daily rate',
    bookCta: 'Request to Book',
    similarTitle: 'Similar cars available on your dates',
    guestPhotosTitle: 'Guest photos',
    reviewsTitle: 'Guest reviews',
    bookingModalTitle: 'Booking this car in 4 steps',
    bookingModalLead:
      "Every reservation on MyTerraBook is reviewed before it's confirmed — here's what happens after you request.",
    tabs: [
      { id: 'details', label: 'Details' },
      { id: 'amenities', label: 'Features' },
      { id: 'conditions', label: 'Rental conditions' },
      { id: 'addons', label: 'Add-ons' },
    ],
    trustPoints: [
      { html: '<b>Collision damage waiver</b> included on every booking' },
      { html: '24/7 roadside assistance across Iceland' },
      { html: 'Pay in instalments via a trusted third-party provider' },
      { html: '<b>Free cancellation</b> up to 48 hours before pick-up' },
    ],
    bookingSteps: [
      { title: 'Choose your dates & send a request', text: "Select pick-up and drop-off times and locations. Your card isn't charged until the booking is confirmed." },
      { title: 'We confirm availability', text: 'Our team checks the vehicle for your dates and confirms — usually within an hour.' },
      { title: 'Pay securely', text: 'Complete payment online or in instalments. Insurance and deposit terms are locked in.' },
      { title: 'Collect your car', text: 'Pick up at your chosen location with a full handover of the vehicle and paperwork.' },
    ],
    faqLead: 'Everything you need to know before you rent this car. Questions? Our Reykjavík team is here to help.',
  },
  guesthouse: {
    id: 'guesthouse',
    categoryNames: [],
    archiveRoute: '/guesthouses',
    archiveLabel: 'See all guesthouses',
    dateStartLabel: 'Check-in',
    dateEndLabel: 'Check-out',
    rateUnit: 'night',
    rateLabelDefault: 'Nightly rate',
    bookCta: 'Request to Book',
    similarTitle: 'Similar stays on your dates',
    guestPhotosTitle: 'Guest photos',
    reviewsTitle: 'Guest reviews',
    bookingModalTitle: 'Booking this stay in 4 steps',
    bookingModalLead:
      "Every stay on MyTerraBook is confirmed by the host before you're charged — here's how it works.",
    tabs: [
      { id: 'details', label: 'Details' },
      { id: 'amenities', label: 'Amenities' },
      { id: 'conditions', label: 'House rules' },
      { id: 'sleeping', label: 'Rooms & beds' },
      { id: 'addons', label: 'Extras' },
    ],
    trustPoints: [
      { html: '<b>Verified hosts</b> along the Ring Road route' },
      { html: 'Secure online payment — no hidden fees at check-in' },
      { html: 'Flexible cancellation on most stays' },
      { html: 'Local support team based in <b>Reykjavík</b>' },
    ],
    bookingSteps: [
      { title: 'Choose your dates & send a request', text: 'Select check-in and check-out dates. Sending a request does not charge your card yet.' },
      { title: 'The host confirms', text: 'Your host reviews your stay and accepts — most replies within a few hours.' },
      { title: 'Pay securely', text: 'Pay in full or in instalments once your stay is approved.' },
      { title: 'Check in & enjoy', text: 'Arrive at the agreed time; your host will show you in and answer any questions.' },
    ],
    faqLead: 'Everything you need to know before you book this guesthouse. Message the host with any questions.',
  },
}

export const DEFAULT_OWNER = {
  name: 'MyTerraBook Host',
  initial: 'M',
  tripsLabel: '12 stays hosted',
  reviewsLabel: '8 reviews',
  badge: null,
}

export const DEFAULT_RATING = {
  score: '5.0',
  label: 'Excellent',
  reviewCount: 7,
  reviewLinkLabel: '7 reviews',
}

export const MOCK_REVIEWS = [
  { name: 'Joey M.', initial: 'J', date: 'October 2025', score: '5.0', text: 'Brilliant host and exactly what we wanted for the Ring Road.', clamp: false },
  { name: 'Joselyn A.', initial: 'J', date: 'September 2025', score: '5.0', text: "An incredible experience — beautiful memories and the host replied within minutes when we had a question. We'd book again in a heartbeat.", clamp: true },
  { name: 'David D.', initial: 'D', date: 'June 2025', score: '5.0', text: 'Our trip was great and the host was really awesome — kind, accommodating and flexible when our flight landed late.', clamp: true },
]

export const MOCK_FAQS = [
  { q: 'What is included in the price?', a: 'Insurance, unlimited mileage (vehicles), and standard roadside cover are included unless stated otherwise. Optional extras are priced separately.' },
  { q: 'Can I cancel or change my booking?', a: 'Free cancellation up to 48 hours before pick-up or check-in on most listings. Contact us or your host to change dates after booking.' },
  { q: 'Where do I pick up the vehicle?', a: 'Pick-up is at Keflavík Airport (KEF) or Reykjavík by default. One-way drop-offs elsewhere in Iceland can be arranged.' },
]

export const GUESTHOUSE_MOCK = {
  id: 'demo-guesthouse',
  name: 'Warm guesthouse near Vík — sea views',
  description:
    'A cosy two-room guesthouse five minutes from Vík, with geothermal hot tub, fully equipped kitchen and fast Wi-Fi. Perfect between glacier lagoons and black-sand beaches.\n\nHosts live next door and meet every guest on arrival. Fresh linen, towels and basic breakfast items are included.',
  category: { name: 'Guesthouse' },
  main_image_path: null,
  details_image_paths: [],
  transmission: null,
  fuel_type: null,
  units_available: 1,
  price_types: [{ id: 1, name: 'Standard Rate', from_price_per_day: '89.00', from_price_per_day_cents: 8900 }],
  characteristics: [],
  rental_options: [],
}
