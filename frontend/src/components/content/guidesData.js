const photos = [
  '/figma/about-camper.png',
  '/images/homepage/hero.jpg',
  '/figma/login-hero.png',
  '/images/homepage/why-photo.jpg',
  '/figma/about-car.png',
  '/images/homepage/stay-hofn.jpg',
  '/figma/about-stay.png',
]

export const GUIDE_ARTICLES = [
  {
    slug: 'how-to-plan-a-road-trip-in-7-days',
    category: 'Itinerary',
    title: 'How to plan a road trip in 7 days',
    italic: '7 days',
    titleBefore: 'How to plan a road trip in ',
    excerpt: 'A day-by-day route around the south and west — where to pick up your campervan, which detours are worth it, and where to sleep each night.',
    deck: 'A day-by-day route around Iceland’s south and west — where to pick up your campervan, which detours are worth the extra kilometres, and where to sleep each night.',
    read: '8 min read',
    date: '12 Oct 2026',
    image: photos[0],
    featured: true,
  },
  {
    slug: 'the-ring-road-in-10-days',
    category: 'Itinerary',
    title: 'The Ring Road in 10 days',
    excerpt: 'Iceland’s full loop at an easy pace, with campsites and guesthouses for every night.',
    read: '12 min read',
    date: '4 Oct 2026',
    image: photos[1],
  },
  {
    slug: 'northern-lights-from-the-road',
    category: 'Nature',
    title: 'Where to see the northern lights from the road',
    excerpt: 'The best dark-sky stops, the forecast to watch and how late to stay up.',
    read: '6 min read',
    date: '28 Sep 2026',
    image: photos[2],
  },
  {
    slug: 'golden-circle-from-reykjavik',
    category: 'Day trip',
    title: 'Golden Circle: a perfect day from Reykjavík',
    excerpt: 'Þingvellir, Geysir and Gullfoss — timing, parking and where to eat.',
    read: '7 min read',
    date: '18 Sep 2026',
    image: photos[3],
  },
  {
    slug: 'f-roads-explained',
    category: 'Driving',
    title: 'F-roads explained: when you need a 4×4',
    excerpt: 'Which roads open when, river crossings and what your insurance covers.',
    read: '9 min read',
    date: '2 Sep 2026',
    image: photos[4],
  },
  {
    slug: 'what-to-pack-for-a-campervan-trip',
    category: 'Packing',
    title: 'What to pack for a campervan trip',
    excerpt: 'Layers, kitchen basics and the small things that make van life easy.',
    read: '5 min read',
    date: '20 Aug 2026',
    image: photos[5],
  },
  {
    slug: 'driving-abroad-what-you-need',
    category: 'Driving',
    title: 'Driving abroad: what you need',
    excerpt: 'Licences, age rules, speed limits and the gravel-road basics.',
    read: '6 min read',
    date: '11 Aug 2026',
    image: photos[6],
  },
]

export const GUIDE_CATEGORIES = ['All', 'Itinerary', 'Nature', 'Day trip', 'Driving', 'Packing']

export const SEVEN_DAY = {
  days: [
    ['1', 'DAY 1', 'Keflavík → Reykjavík', 'Pick up your vehicle at the airport, stop at the Bridge Between Continents and settle into the capital.', '50 km · 45 min'],
    ['2', 'DAY 2', 'Golden Circle', 'Þingvellir National Park, the Geysir geothermal area and Gullfoss waterfall in one loop.', '230 km · 3 h 30'],
    ['3', 'DAY 3', 'Waterfalls of the south coast', 'Seljalandsfoss, Skógafoss and the black-sand beach at Reynisfjara before sleeping in Vík.', '180 km · 2 h 40'],
    ['4', 'DAY 4', 'Vík → Skaftafell', 'Cross the Eldhraun lava field and Fjaðrárgljúfur canyon to the edge of Vatnajökull.', '140 km · 2 h'],
    ['5', 'DAY 5', 'Glacier lagoon day', 'Jökulsárlón and Diamond Beach at sunrise, then a glacier walk from Skaftafell.', '120 km · 1 h 40'],
    ['6', 'DAY 6', 'Back west to Hella', 'Slow drive back with stops you missed — overnight in a guesthouse near Hella.', '250 km · 3 h 20'],
    ['7', 'DAY 7', 'Reykjanes & home', 'Lava fields, the Gunnuhver hot springs and a last soak before returning your vehicle.', '140 km · 2 h'],
  ],
}
