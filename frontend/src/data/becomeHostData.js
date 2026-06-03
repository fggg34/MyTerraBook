const IMG = {
  cardHouse: '/images/homepage/cardhouse.jpg',
  heroVan: '/images/homepage/host-van.jpg',
  cardCamper: '/images/homepage/cardcamper.jpg',
  cardCar: '/images/homepage/cardcar.jpg',
  whyPhoto: '/images/homepage/why-photo.jpg',
  stayHofn: '/images/homepage/stay-hofn.jpg',
}

export const becomeHostImages = IMG

export const howTabs = [
  {
    title: 'List it',
    image: IMG.cardHouse,
    imageAlt: 'A guesthouse listed on MyTerraBook',
    caption: 'List your van or guesthouse in minutes, ',
    muted: "our team reviews every listing so travellers know it's the real thing.",
  },
  {
    title: 'Get discovered',
    image: IMG.heroVan,
    imageAlt: 'A campervan discovered through MyTerraBook',
    caption: 'Your listing reaches travellers planning Iceland, ',
    muted: 'featured across 30,000+ trip searches every month.',
  },
  {
    title: 'Welcome travellers',
    image: IMG.cardCamper,
    imageAlt: 'A traveller picking up a campervan',
    caption: 'Insurance, verification and 24/7 roadside support are built in, ',
    muted: 'you just hand over the keys.',
  },
  {
    title: 'Get paid',
    image: IMG.cardCar,
    imageAlt: 'A car earning income on MyTerraBook',
    caption: 'Payouts land within 24 hours of pickup, ',
    muted: 'you keep 85% of every booking, with no hidden fees.',
  },
]

export const faqItems = [
  {
    num: '01',
    question: 'How much can I actually earn?',
    answer:
      "It depends on what you list and when. Campervan hosts earn €1,900/month on average over the season, and the top earners clear €3,200+. We show you a personalised estimate based on real local demand before you publish.",
  },
  {
    num: '02',
    question: 'What does it cost to list?',
    answer:
      'Listing is completely free. We only take a 15% service fee on confirmed bookings, which covers payments, marketing, insurance and 24/7 support. No subscriptions, no upfront costs, no surprises.',
  },
  {
    num: '03',
    question: 'Is my vehicle or property insured?',
    answer:
      'Yes. Every booking is covered by comprehensive insurance including Iceland-specific gravel, sand and ash protection. Guests are identity-verified before pickup, and our team is on call around the clock if anything comes up.',
  },
  {
    num: '04',
    question: 'How and when do I get paid?',
    answer:
      "Payouts are sent to your bank account within 24 hours of each trip starting. You'll see every booking, payout and upcoming reservation in a single earnings dashboard, so you always know what's coming in.",
  },
  {
    num: '05',
    question: 'Do I have to accept every booking?',
    answer:
      "Never. You control your own calendar and can block any dates you need the vehicle or room for yourself. Turn on instant booking for convenience, or review each request by hand — it's entirely up to you.",
  },
]

const starPath = 'm12 2 2.9 6.3 6.9.8-5.1 4.7 1.4 6.8L12 17.6 5.9 20.6l1.4-6.8L2.2 9.1l6.9-.8L12 2Z'

export const reviewColumns = {
  up: [
    {
      name: 'Anna Sigurðar',
      role: 'Campervan host · Reykjavík',
      quote:
        '"My van used to sit in the driveway nine months a year. Now it pays for itself twice over and I\'ve met travellers from all over the world."',
      fill: '#a9d4e6',
    },
    {
      name: 'Eva Magnús',
      role: '4×4 host · Vík',
      quote:
        '"The insurance and gravel cover took away every worry I had about renting out my 4×4. I just approve dates and the payouts arrive."',
      fill: '#f1d79a',
    },
    {
      name: 'Katrín Helga',
      role: 'Cabin host · Egilsstaðir',
      quote:
        '"Travellers leave my cabin spotless because every guest is verified first. For the first time, hosting actually feels safe."',
      fill: '#cdbbea',
    },
  ],
  down: [
    {
      name: 'Jón Þór',
      role: 'Guesthouse host · Akureyri',
      quote:
        '"Listing the guesthouse took one evening. The booking team handles everything, and a real person picks up when I call. That\'s rare."',
      fill: '#bcdcab',
    },
    {
      name: 'Bjarni Ólafsson',
      role: 'Car host · Selfoss',
      quote:
        '"I added my second car within a week. Summer demand was far higher than I ever expected, and the dashboard made pricing easy."',
      fill: '#a4ddcd',
    },
    {
      name: 'Ólafur Garðar',
      role: 'Van host · Höfn',
      quote:
        '"Payouts land within a day and the dashboard shows me exactly what\'s booking. No guesswork, no chasing invoices."',
      fill: '#f4c1a4',
    },
  ],
}

export const starPathIcon = starPath

export const proofPairs = [
  {
    tall: { name: 'Anna Sigurðar', role: 'Campervan host · Reykjavík', image: IMG.whyPhoto },
    stack: [
      { type: 'stat', variant: 'clay', big: '€2.4M+', desc: 'Paid out to MyTerraBook hosts last year.' },
      { type: 'photo', name: 'Evan Brooks', role: 'Guesthouse host', image: IMG.stayHofn },
    ],
  },
  {
    tall: { name: 'JónÞór', role: '4×4 host · Akureyri', image: IMG.cardCar },
    stack: [
      { type: 'photo', name: 'Tom Reyes', role: 'Van host · Selfoss', image: IMG.heroVan },
      { type: 'stat', variant: 'moss', big: '1,800+', desc: 'Active hosts around the Ring Road.' },
    ],
  },
  {
    tall: { name: 'Eva Magnús', role: 'Cabin host · Vík', image: IMG.cardCamper },
    stack: [
      { type: 'stat', variant: 'ochre', big: '€1,900', desc: 'Avg. monthly host earnings.' },
      { type: 'photo', name: 'Rob Mendes', role: 'Car host · Höfn', image: IMG.cardCar },
    ],
  },
  {
    tall: { name: 'Aisha Grant', role: 'Guesthouse host · Egilsstaðir', image: IMG.stayHofn },
    stack: [
      { type: 'photo', name: 'Ryan Mitchell', role: '4×4 host · Ísafjörður', image: IMG.cardCamper },
      { type: 'stat', variant: 'espresso', big: '4.9★', desc: 'Average host rating from travellers.' },
    ],
  },
]
