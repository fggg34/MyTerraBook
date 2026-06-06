const IMG = {
  hero: '/images/homepage/hero.jpg',
  whyPhoto: '/images/homepage/why-photo.jpg',
  stayHofn: '/images/homepage/stay-hofn.jpg',
  hostVan: '/images/homepage/host-van.jpg',
  cardCamper: '/images/homepage/cardcamper.jpg',
  cardCar: '/images/homepage/cardcar.jpg',
  cardHouse: '/images/homepage/cardhouse.jpg',
}

const pick = (name, price, gearbox, seats, sleeps, bags, image = IMG.cardCamper) => ({
  name,
  image,
  price,
  per: 'day',
  badge: 'Extras included',
  specs: [
    { type: 'gearbox', label: gearbox },
    { type: 'seat', label: `Seats ${seats}` },
    { type: 'bed', label: `Sleeps ${sleeps}` },
    { type: 'bag', label: `${bags} Bag${bags === 1 ? '' : 's'}` },
  ],
})

export const defaultHomepageData = {
  topbar: {
    text: 'Become a Host and start earning money!',
    linkLabel: 'List your van or guesthouse',
    linkHref: '/become-a-host',
  },
  header: {
    navLinks: [
      { label: 'Campervan', href: '/campervans' },
      { label: 'Car', href: '/cars' },
      { label: 'Guesthouse', href: '/guesthouses' },
      { label: 'Good to Know', href: '/good-to-know' },
    ],
    ctaLabel: 'Become a host',
    ctaHref: '/become-a-host',
    langLabel: 'EN',
    currencyLabel: '€ EUR',
    signInLabel: 'Sign in',
    signInHref: '/login',
  },
  hero: {
    heading: "Book with the world's leading roadtrip provider!",
    subtitle:
      'Campervans, 4×4s and warm guesthouses — everything you need for the Ring Road, in one booking.',
    backgroundImage: IMG.hero,
    tabs: [
      { id: 'campervan', label: 'Campervan' },
      { id: 'cars', label: 'Cars' },
      { id: 'guesthouses', label: 'Guesthouses' },
    ],
    experienceLabel: 'Choose your perfect Icelandic experience',
    experiencePlaceholder: 'Choose an experience',
    datesLabel: 'Select dates',
    startDateLabel: 'Starting date',
    endDateLabel: 'Final date',
    travelersLabel: 'Add travelers',
    travelersValue: '1 traveler',
    searchLabel: 'Search Now',
    footerHint: 'Not sure where to start? Check out',
    footerLinkLabel: 'Things to do in Iceland',
    footerLinkHref: '#discover',
  },
  trustItems: [
    { icon: 'star', title: '4.9 / 5', subtitle: '12,400+ verified reviews', stars: 5 },
    { icon: 'check', title: 'Free cancellation', subtitle: 'Up to 48 hours before pickup' },
    { icon: 'shield', title: 'Fully insured', subtitle: 'Gravel & ash cover included' },
    { icon: 'phone', title: 'Local support, 24/7', subtitle: 'Real people in Reykjavík' },
  ],
  rentSection: {
    heading: 'Three ways to move through Iceland.',
    subtitle: 'From a two-berth van for the Ring Road to a warm bed at the end of it.',
    cards: [
      {
        image: IMG.cardCamper,
        alt: 'Campervan at dusk on a black-sand beach',
        listingCount: '184 listings · from €89/night',
        name: 'Campervans',
        tagline: 'Sleep where you drive.',
        href: '/campervans',
      },
      {
        image: IMG.cardCar,
        alt: 'SUV on a winding road below snowy mountains',
        listingCount: '96 listings · from €42/night',
        name: 'Cars & 4×4',
        tagline: 'Built for the ring road.',
        href: '/cars',
      },
      {
        image: IMG.cardHouse,
        alt: 'Lit guesthouse in the snow at dusk',
        listingCount: '73 listings · from €110/night',
        name: 'Guesthouses',
        tagline: 'Warm beds between drives.',
        href: '/guesthouses',
      },
    ],
  },
  whySection: {
    heading: 'One local platform for every way to explore Iceland.',
    subheading:
      "Campervans, cars, guesthouses — even a way to earn from your own. All checked by a team that actually drives these roads.",
    photo: IMG.whyPhoto,
    badge: { rating: '4.9', reviewBold: '12,400+ travellers', reviewRest: 'who booked with us' },
    featuresLeft: [
      {
        icon: 'campervan',
        title: 'Campervans',
        description: 'Sleep where you stop — kitted out for the Ring Road and beyond.',
        expandedText:
          'Heaters, gas hobs and proper bedding come as standard, and every van is winter-checked before pickup so you can chase the aurora without packing half a house.',
      },
      {
        icon: 'car',
        title: 'Cars & 4×4s',
        description: 'From city compacts to proper F-road 4×4s with gravel cover.',
        expandedText:
          "Need to reach the highlands? Our 4×4s clear the F-roads and river crossings the rental desks at the airport quietly tell you to avoid.",
      },
      {
        icon: 'house',
        title: 'Guesthouses',
        description: 'Warm, vetted beds spaced along your route across the island.',
        expandedText:
          "Hand-picked stays in Vík, Höfn, Akureyri and more — each within an easy day's drive of the last, so your itinerary plans itself.",
      },
    ],
    featuresRight: [
      {
        icon: 'host',
        title: 'Become a host',
        description: 'List your van or guesthouse and earn between trips, hassle-free.',
        expandedText:
          'We handle bookings, payments and insurance, and you keep the calendar. Most hosts cover their winter storage within the first season.',
      },
      {
        icon: 'shield',
        title: 'Fully insured',
        description: 'Gravel, ash and tyre protection bundled into one clear price.',
        expandedText:
          "Iceland's gravel and volcanic ash wreck more rentals than anything else — so that cover is included up front, not upsold at the counter.",
      },
      {
        icon: 'phone',
        title: 'Local support, 24/7',
        description: 'Real people in Reykjavík, any season, whenever the road turns.',
        expandedText:
          'Flat tyre at midnight, a road closed by weather, a route rethink — one call reaches a local who knows exactly where you are and what to do.',
      },
    ],
  },
  picksSection: {
    heading: 'Hand-picked for Iceland.',
    tabs: [
      { id: 'camper', label: 'Campervans', allLabel: 'See all campervans', allHref: '/campervans' },
      { id: 'car', label: 'Cars', allLabel: 'See all cars', allHref: '/cars' },
    ],
    items: {
      camper: [
        pick('Freyja Caratour', '€187', 'MANUAL', 2, 2, 3),
        pick('Saga Voyager XL', '€214', 'AUTO', 4, 4, 4),
        pick('Embla Roamer', '€163', 'MANUAL', 2, 2, 2),
        pick('Vatna Highland', '€242', 'AUTO', 5, 4, 5),
        pick('Bragi Compact', '€139', 'MANUAL', 2, 2, 2),
        pick('Dröfn Trail Van', '€198', 'AUTO', 3, 3, 3),
        pick('Katla Adventure', '€226', 'MANUAL', 4, 4, 4),
        pick('Sól Micro Camper', '€124', 'MANUAL', 2, 2, 1),
      ],
      car: [
        {
          name: 'Vík 4×4 Explorer',
          image: IMG.cardCar,
          price: '€96',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'AUTO' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: 'AWD' },
            { type: 'bag', label: '4 Bags' },
          ],
        },
        {
          name: 'Reykjavík Compact',
          image: IMG.cardCar,
          price: '€48',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'MANUAL' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: 'FWD' },
            { type: 'bag', label: '2 Bags' },
          ],
        },
        {
          name: 'Þingvellir Wagon',
          image: IMG.cardCar,
          price: '€74',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'AUTO' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: 'AWD' },
            { type: 'bag', label: '3 Bags' },
          ],
        },
        {
          name: 'Askja Trail SUV',
          image: IMG.cardCar,
          price: '€118',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'AUTO' },
            { type: 'seat', label: 'Seats 7' },
            { type: 'drive', label: '4×4' },
            { type: 'bag', label: '5 Bags' },
          ],
        },
        {
          name: 'Hekla City EV',
          image: IMG.cardCar,
          price: '€62',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'AUTO' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: 'FWD' },
            { type: 'bag', label: '3 Bags' },
          ],
        },
        {
          name: 'Skógar Pickup',
          image: IMG.cardCar,
          price: '€132',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'MANUAL' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: '4×4' },
            { type: 'bag', label: '4 Bags' },
          ],
        },
        {
          name: 'Dyrhólaey Sedan',
          image: IMG.cardCar,
          price: '€58',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'AUTO' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: 'FWD' },
            { type: 'bag', label: '3 Bags' },
          ],
        },
        {
          name: 'Krafla EV Plus',
          image: IMG.cardCar,
          price: '€71',
          per: 'day',
          badge: 'Extras included',
          specs: [
            { type: 'gearbox', label: 'AUTO' },
            { type: 'seat', label: 'Seats 5' },
            { type: 'drive', label: 'AWD' },
            { type: 'bag', label: '3 Bags' },
          ],
        },
      ],
    },
  },
  howSection: {
    heading: 'From airport to open road in three steps.',
    steps: [
      {
        num: '01',
        title: 'Find your match',
        description:
          'Tell us your dates and how you like to travel. We surface only the vans, 4×4s and stays that fit your route — every one checked by our Reykjavík team and priced with insurance built in.',
        image: IMG.stayHofn,
        imageAlt: 'Browsing vehicles for an Iceland trip',
        tags: ['Search', 'Compare', 'Instant quote'],
      },
      {
        num: '02',
        title: 'Collect near Keflavík',
        description:
          "Land, grab your keys minutes from the terminal, and get a hands-on walkthrough of the gear. Studded tyres, camping card and gravel cover are already sorted before you pull away.",
        image: IMG.whyPhoto,
        imageAlt: 'Picking up a campervan in Iceland',
        tags: ['Fast pickup', 'Walkthrough', 'Gear ready'],
      },
      {
        num: '03',
        title: 'Drive Iceland, fully backed',
        description:
          'Hit the Ring Road with unlimited mileage and 24/7 local support in your pocket. Anything comes up — weather, a flat, a route change — and a real person in Iceland picks up.',
        image: IMG.hero,
        imageAlt: 'Driving the Ring Road in Iceland',
        tags: ['Unlimited km', '24/7 support', 'Any season'],
      },
    ],
  },
  staySection: {
    heading: 'A warm bed at the end of the road.',
    subtitle:
      "Hand-picked guesthouses spaced a comfortable day's drive apart — so the only thing left to plan is where to watch the sky.",
    allLabel: 'Browse all guesthouses',
    allHref: '/guesthouses',
    cards: [
      {
        name: 'Höfn Harbour House',
        image: IMG.cardHouse,
        badge: 'Breakfast included',
        specs: ['Sleeps 4', '2 Rooms', 'Wi-Fi'],
        price: '€128',
      },
      {
        name: 'Mývatn Lodge',
        image: IMG.hostVan,
        badge: 'Breakfast included',
        specs: ['Sleeps 6', '3 Rooms', 'Wi-Fi'],
        price: '€164',
      },
      {
        name: 'Vík Cliffside Stay',
        image: IMG.stayHofn,
        badge: 'Breakfast included',
        specs: ['Sleeps 2', '1 Room', 'Wi-Fi'],
        price: '€112',
      },
    ],
  },
  blogSection: {
    heading: 'Good to Know',
    subtitle: 'Guides, routes & practical Iceland tips from our Reykjavík team',
    allLabel: 'View all',
    allHref: '/good-to-know',
    posts: [
      {
        slug: 'driving-the-ring-road-in-7-days',
        featured: true,
        title: 'Driving the Ring Road in 7 days',
        description:
          'A complete loop itinerary with the best stops, fuel points and where to sleep each night.',
        meta: '12 min read',
        metaExtra: 'Updated this week',
        image: IMG.hero,
        imageAlt: 'Campervan on the Ring Road',
      },
      {
        slug: 'golden-circle-in-a-day',
        kicker: 'Day trip',
        title: 'Golden Circle in a day',
        image: IMG.stayHofn,
        imageAlt: 'Golden Circle',
      },
      {
        slug: 'do-you-need-a-4x4',
        kicker: 'Gear',
        title: 'Do you need a 4×4?',
        image: IMG.whyPhoto,
        imageAlt: '4x4 camper on gravel',
      },
      {
        slug: 'chasing-the-northern-lights',
        kicker: 'Nature',
        title: 'Chasing the northern lights',
        aurora: true,
      },
      {
        slug: 'campervan-vs-guesthouse',
        kicker: 'Compare',
        title: 'Campervan vs guesthouse',
        image: IMG.hostVan,
        imageAlt: 'Guesthouse interior',
      },
    ],
  },
  hostCtaSection: {
    eyebrow: 'For hosts',
    heading: 'List your car or guesthouse.',
    lead: "Your van sits idle between trips and your spare room stays empty. Put them to work on the platform Iceland's travellers already trust.",
    earnAmount: '€1,900',
    earnNote: 'Top campervan hosts clear €3,200+. You keep 85% — we handle bookings, payments and insurance.',
    points: ['Free to list', 'You set the calendar', 'Insurance included'],
    primaryLabel: 'Become a host',
    primaryHref: '/become-a-host',
    secondaryLabel: 'See how hosting works',
    secondaryHref: '/become-a-host',
    houseImage: IMG.hostVan,
    vanImage: IMG.whyPhoto,
    chipAmount: '€2.4M+',
    chipLabel: 'paid to 1,800+ hosts last year',
  },
  reviewsSection: {
    eyebrow: 'Traveller stories',
    heading: 'Loved on the Ring Road.',
    rating: '4.9 / 5',
    ratingCount: '12,400+ travellers',
    reviews: [
      {
        quote:
          '"Picked up the camper ten minutes from Keflavík and were chasing waterfalls by lunch. Everything just worked."',
        name: 'Marta Lindqvist',
        fill: '#a9d4e6',
        rot: '-9deg',
        ty: '30px',
      },
      {
        quote:
          '"Gravel roads, a river crossing, even a June snow flurry — the 4×4 and the insurance took it all in stride."',
        name: 'Tom Okafor',
        fill: '#bcdcab',
        rot: '-5.5deg',
        ty: '16px',
      },
      {
        quote:
          '"Our guesthouse in Vík was cosier than the photos. After a long day on the Ring Road we slept like rocks."',
        name: 'Sofia Rossi',
        fill: '#f1d79a',
        rot: '-1.5deg',
        ty: '4px',
      },
      {
        quote:
          '"Booking the van, the car days and two stays in one place saved us an entire evening of admin."',
        name: 'Daniel Berg',
        fill: '#cdbbea',
        rot: '1.5deg',
        ty: '4px',
      },
      {
        quote:
          '"Flat tyre near Höfn at midnight. One call, a real person, sorted in twenty minutes. Unreal."',
        name: 'Aiko Tanaka',
        fill: '#a4ddcd',
        rot: '5.5deg',
        ty: '16px',
      },
      {
        quote:
          '"Cleanest van we\'ve ever rented, fair price, and not a single surprise fee at the counter."',
        name: 'Lucas Moreau',
        fill: '#f4c1a4',
        rot: '9deg',
        ty: '30px',
      },
    ],
  },
  faqSection: {
    eyebrow: 'Good to know',
    heading: 'Questions & answers.',
    lead: 'Still curious before you book? Our team in Reykjavík is one message away.',
    phone: '+354 519 1010',
    email: 'support@myterrabook.is',
    items: [
      {
        num: '01',
        question: 'What do I need to rent and drive in Iceland?',
        answer:
          "A full driving licence held for at least one year and a card in the main driver's name. Most vehicles ask that you're 20+, or 23+ for the larger 4×4s. Bring your licence and passport to pickup — that's it.",
        open: true,
      },
      {
        num: '02',
        question: 'Is gravel and ash damage covered?',
        answer:
          "Yes. Gravel protection and sand-and-ash cover are built into every booking, not sold separately at the counter — they're the two things that catch most travellers out on Iceland's roads.",
      },
      {
        num: '03',
        question: 'Where can I sleep in a campervan?',
        answer:
          "At any of Iceland's registered campsites, which line the Ring Road. Wild camping in a vehicle isn't permitted, so we send a campsite map with every van booking to make planning each night easy.",
      },
      {
        num: '04',
        question: 'What if a road closes or the weather turns?',
        answer:
          "Call our Reykjavík team any hour and we'll help you re-route, find a stay, or rethink the plan. Live road and weather alerts are also pinned inside your booking so nothing catches you off guard.",
      },
      {
        num: '05',
        question: 'How does pickup near Keflavík work?',
        answer:
          "Land, hop on the quick shuttle, and collect your keys minutes from the terminal. You'll get a hands-on walkthrough of the vehicle before you drive off, with studded tyres and gear already fitted for the season.",
      },
    ],
  },
  newsSection: {
    eyebrow: 'Field notes from Iceland',
    heading: 'Road conditions, seasonal deals & hidden detours.',
    headingAccent: 'hidden detours',
    lead: "Aurora forecasts, last-minute van openings and local tips you won't find on a map — once a month, from our Reykjavík team.",
    backgroundImage: IMG.hero,
    placeholder: 'you@somewhere-cold.is',
    successMessage: 'Takk! Check your inbox to confirm.',
  },
  footer: {
    tagline:
      "Iceland's locally-run platform for campervans, 4×4s, cars and guesthouses — one booking, one team in Reykjavík behind it.",
    address: 'MyTerraBook ehf.\nLaugavegur 178 · 105 Reykjavík · Iceland\nKennitala 591284-0119 · VSK 142819',
    columns: [
      {
        title: 'Menu',
        links: [
          { label: 'Campervan', href: '/campervans' },
          { label: 'Car', href: '/cars' },
          { label: 'Guesthouse', href: '/guesthouses' },
          { label: 'Good to Know', href: '/good-to-know' },
          { label: 'Become a host', href: '/become-a-host' },
        ],
      },
      {
        title: 'Pages',
        links: [
          { label: 'About us', href: '/about' },
          { label: 'FAQs', href: '/faq' },
          { label: 'Contact', href: '/contact' },
          { label: 'Sign in', href: '/login' },
          { label: 'Create account', href: '/register' },
        ],
      },
    ],
    copyright: '© 2026 MyTerraBook ehf. Made in Reykjavík.',
    locale: 'English (UK)',
    currency: 'EUR €',
    legal: [
      { label: 'Terms', href: '/terms' },
      { label: 'Privacy', href: '/privacy' },
      { label: 'Cookies', href: '/cookies' },
    ],
    social: [],
  },
}
