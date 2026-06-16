<?php

namespace Database\Seeders;

use App\Enums\BlogPostStatus;
use App\Models\BlogPost;
use Illuminate\Database\Seeder;

class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $posts = [
            [
                'slug' => 'driving-the-ring-road-in-7-days',
                'title' => 'Driving the Ring Road in 7 days',
                'kicker' => 'Itinerary',
                'excerpt' => 'A complete loop itinerary with the best stops, fuel points and where to sleep each night.',
                'body' => <<<'HTML'
<p>The Ring Road (Route 1) circles Iceland in roughly 1,332 km. Seven days is tight but absolutely doable if you focus on highlights, book campsites or guesthouses ahead of time, and accept that you will drive most days rather than linger everywhere.</p>
<p>This itinerary assumes a clockwise loop starting and ending in Reykjavík, with a campervan or car collected near Keflavík. Adjust pacing if you add highland detours, those need extra days and often a 4×4.</p>
<h2>Before you leave Reykjavík</h2>
<p>Stock up on groceries at a Bonus or Krónan supermarket. Fill your water bottles, download offline maps, and check <strong>road.is</strong> and <strong>vedur.is</strong> for the week ahead. Book your first two or three nights if you are travelling in July or August; shoulder season is more forgiving but still worth planning around Vík and Höfn.</p>
<ul>
<li><strong>Fuel strategy:</strong> Iceland has long gaps between stations. Never let the tank drop below half on the east and north coasts.</li>
<li><strong>Distances:</strong> Google Maps times are optimistic in wind, rain, or gravel sections. Add 15–25% to estimates.</li>
<li><strong>Insurance:</strong> Gravel and ash damage is common. MyTerraBook rentals include gravel protection so you are not negotiating surprise fees at the counter.</li>
</ul>
<h2>Day 1: Reykjavík → South Coast (Seljalandsfoss, Skógafoss, Vík)</h2>
<p>Leave early to beat coach crowds at the waterfalls. Seljalandsfoss allows a walk behind the cascade when conditions are safe; Skógafoss is powerful and photogenic from the base or the stairs. Continue past Dyrhólaey and Reynisfjara black-sand beach, stay well back from the surf; sneaker waves are deadly.</p>
<p><strong>Overnight:</strong> Vík or nearby campsite. Driving time from Reykjavík: roughly 2.5–3 hours without long stops.</p>
<h2>Day 2: Vík → Jökulsárlón → Höfn</h2>
<p>The drive east is one of the trip's best stretches: waterfalls, glacier views, and the vast sandur plains. Stop at Fjaðrárgljúfur canyon if time allows. Jökulsárlón glacier lagoon and Diamond Beach deserve at least an hour.</p>
<p><strong>Overnight:</strong> Höfn or a campsite in the southeast. Fuel in Kirkjubæjarklaustur if needed, options thin out further east.</p>
<h2>Day 3: Höfn → Eastfjords → Egilsstaðir</h2>
<p>A longer driving day through winding fjord roads. The Eastfjords feel quieter than the south; stop in Seyðisfjörður if you can spare the detour. Stock up on food and fuel in Egilsstaðir, it is the main hub for the east.</p>
<p><strong>Overnight:</strong> Egilsstaðir area campsite or guesthouse.</p>
<h2>Day 4: Egilsstaðir → Mývatn → Akureyri</h2>
<p>Detour to Dettifoss (east side access is easier for 2WD). Explore Mývatn: pseudo-craters, Hverir geothermal area, and Grjótagjá cave. Finish in Akureyri, Iceland's northern capital, for restaurants and a proper town atmosphere.</p>
<p><strong>Overnight:</strong> Akureyri, a guesthouse night here breaks up camping nicely.</p>
<h2>Day 5: Akureyri → Tröllaskagi → North West</h2>
<p>Drive the scenic peninsula or cut inland depending on weather. Optional stops include Goðafoss and the turf houses at Glaumbær. If the weather is rough, keep the day shorter and push further west tomorrow.</p>
<p><strong>Overnight:</strong> Blönduós, Sauðárkrókur, or a farm stay on the north coast.</p>
<h2>Day 6: North → West Iceland (Borgarnes)</h2>
<p>Longest transit day. Break it in Borgarnes with a visit to the Settlement Centre or a detour to Hraunfossar and Barnafoss. If you skipped the Snæfellsnes peninsula entirely, consider adding a day, it does not fit cleanly into seven.</p>
<p><strong>Overnight:</strong> Borgarnes or campsite on the Snæfellsnes route if you extend.</p>
<h2>Day 7: West → Reykjavík</h2>
<p>Allow time for a soak at Krauma or a final stop on the Reykjanes peninsula before returning your vehicle. Most rentals expect return with a full tank near Keflavík or Reykjavík, confirm pickup and drop-off when you book.</p>
<h2>Practical tips for a 7-day loop</h2>
<ul>
<li><strong>Pace yourself:</strong> Seven days means choosing highlights, not every side road.</li>
<li><strong>Mix accommodation:</strong> Many travellers alternate camper nights with guesthouse stays in Akureyri or Vík, MyTerraBook lets you book both in one account.</li>
<li><strong>Weather wins:</strong> If a pass or coastal road is closed, reroute via the interior hub towns rather than waiting indefinitely.</li>
<li><strong>Campsites:</strong> Arrive before 20:00 when possible; reception hours vary.</li>
</ul>
<p>A week on Route 1 gives you a real taste of the whole island. Add two or three days if you want Snæfellsnes, the Westfjords, or highland F-roads without rushing.</p>
HTML,
                'featured_image' => '/images/homepage/hero.jpg',
                'image_alt' => 'Campervan on the Ring Road',
                'read_time' => '12 min read',
                'is_featured' => true,
                'aurora' => false,
                'sort_order' => 0,
            ],
            [
                'slug' => 'golden-circle-in-a-day',
                'title' => 'Golden Circle in a day',
                'kicker' => 'Day trip',
                'excerpt' => 'Þingvellir, Geysir, and Gullfoss, the classic day loop from Reykjavík with timing tips and optional stops.',
                'body' => <<<'HTML'
<p>The Golden Circle is Iceland's most popular day route, and for good reason. Three iconic stops (Þingvellir National Park, the Geysir geothermal area, and Gullfoss waterfall) fit comfortably into a single day from Reykjavík, with time for lunch and one optional soak.</p>
<p>You do not need a 4×4 for the standard loop. Any MyTerraBook car, campervan, or compact will handle the paved roads year-round, though winter driving demands extra caution.</p>
<h2>Recommended order and timing</h2>
<p>Start early, by 8:00 or 9:00 from Reykjavík, to stay ahead of tour buses at Þingvellir. The full loop is roughly 230 km; with stops, plan on 7–9 hours.</p>
<ol>
<li><strong>Þingvellir National Park</strong>, 1.5 to 2 hours. Walk between the North American and Eurasian tectonic plates, visit the Alþingi assembly site, and take in Öxaráfoss if you have time.</li>
<li><strong>Geysir and Strokkur</strong>, 45 to 60 minutes. Strokkur erupts every 5–10 minutes. The gift shop and café area gets crowded; mid-morning is busiest.</li>
<li><strong>Gullfoss</strong>, 45 to 60 minutes. Two viewpoints connected by steps; the lower path can be slippery when wet or icy.</li>
</ol>
<h2>Þingvellir: what to know</h2>
<p>Parking is paid and split across zones, follow signs for P1 or P5 depending on which trails you want. The park is a UNESCO World Heritage site and still geologically active; stick to marked paths. Silfra fissure diving is world-famous but requires a separate booked tour; it is not a casual stop.</p>
<h2>Geysir geothermal area</h2>
<p>The Great Geysir itself is mostly dormant, but Strokkur is reliable. Stay upwind of the eruption, the water is boiling. The smell of sulphur is normal. Facilities include toilets and food; prices are tourist-level, so packing snacks saves money.</p>
<h2>Gullfoss waterfall</h2>
<p>Gullfoss means "golden falls", the spray creates rainbows on sunny days. Wind at the canyon rim can be fierce; hold onto hats and phones. In winter, paths may be partially closed; check conditions on site.</p>
<h2>Optional add-ons</h2>
<ul>
<li><strong>Secret Lagoon (Gamla Laugin)</strong> near Flúðir, a natural hot spring, less commercial than the Blue Lagoon. Book ahead in peak season; allow 90 minutes including changing time.</li>
<li><strong>Kerið crater</strong>, a small, vivid volcanic crater lake on the route back toward Reykjavík. Quick stop, small entry fee.</li>
<li><strong>Friðheimar tomato greenhouse</strong>, lunch inside a greenhouse famous for tomato soup. Reservations essential.</li>
</ul>
<h2>Golden Circle vs Blue Lagoon</h2>
<p>Many arrivals combine the Golden Circle with a Blue Lagoon visit near Keflavík on a separate day. If you land at KEF and collect your rental, do not try to cram Blue Lagoon, the full Golden Circle, and driving to your first overnight in one jet-lagged day. Split them.</p>
<h2>Seasonal notes</h2>
<p><strong>Summer:</strong> Long daylight, heavy traffic, full parking lots by midday. <strong>Winter:</strong> Short daylight, start at first light. Roads are usually cleared but ice patches persist; studded tyres help. <strong>Shoulder (May, September):</strong> Often the sweet spot for fewer crowds and decent weather.</p>
<p>The Golden Circle is an ideal first outing after picking up your vehicle. It teaches you Icelandic driving, weather, and crowd patterns before you commit to the full Ring Road.</p>
HTML,
                'featured_image' => '/images/homepage/stay-hofn.jpg',
                'image_alt' => 'Golden Circle landscape',
            ],
            [
                'slug' => 'do-you-need-a-4x4',
                'title' => 'Do you need a 4×4?',
                'kicker' => 'Gear',
                'excerpt' => 'When a camper or 2WD car is enough, and when F-roads mean you need more.',
                'body' => <<<'HTML'
<p>One of the most common questions we get in Reykjavík: "Do I really need a 4×4?" The honest answer depends entirely on where you plan to go, not how rugged you feel as a driver.</p>
<p>For the Ring Road, Golden Circle, and virtually all major attractions along Route 1, a standard 2WD car or campervan is enough in summer. You need a 4×4 when your itinerary includes F-roads, highland interior routes, or specific remote tracks that legally require it.</p>
<h2>What is an F-road?</h2>
<p>F-roads (fjallvegur) are mountain tracks in Iceland's interior and highlands. They are rough, unpaved, often involve river crossings, and are generally open only in summer, typically June through September, depending on snow and conditions. Signs show a white "F" prefix (e.g. F208, F35 Kjölur).</p>
<p>Driving an F-road in a 2WD vehicle is not just unwise, it violates rental agreements and can void insurance. Rivers that look shallow can swallow a car after rain.</p>
<h2>When 2WD is fine</h2>
<ul>
<li>The full Ring Road (Route 1) in summer and shoulder season</li>
<li>Golden Circle, Snæfellsnes peninsula (main roads), Reykjanes</li>
<li>South coast to Jökulsárlón, east fjords on Route 1</li>
<li>North Iceland: Akureyri, Mývatn, Goðafoss, all on paved or maintained routes</li>
<li>Most campervan trips with campsite stays along Route 1</li>
</ul>
<p>Gravel sections exist even on Route 1, especially in the east. That is normal. Drive slowly, increase following distance, and accept that stone chips happen. Gravel protection is included on MyTerraBook rentals for exactly this reason.</p>
<h2>When you should book a 4×4</h2>
<ul>
<li>Landmannalaugar and the highland interior</li>
<li>Askja caldera, Thórsmörk (without a dedicated highland bus tour)</li>
<li>Westfjords interior tracks (many communities on Route 1 are fine in 2WD; gravel F-roads are not)</li>
<li>Any route marked F on maps or closed to non-4×4 traffic</li>
</ul>
<p>If your dream trip is "highlands and hot springs off the beaten path," budget for a 4×4 and build extra days for weather delays. One closed river ford can end a day's plans.</p>
<h2>Winter considerations</h2>
<p>Winter does not automatically mean 4×4 for Route 1, many locals drive 2WD with studded tyres. But winter highland F-roads are closed entirely. A 4×4 with good tyres helps on icy coastal and mountain passes, yet the bigger factor is experience, speed, and checking road.is every morning.</p>
<h2>Campervan vs SUV</h2>
<p>Standard campervans are typically 2WD and perfect for Ring Road camping culture. 4×4 campers exist but cost more and consume more fuel. If you are not crossing rivers, that money might be better spent on extra guesthouse nights or a Camping Card.</p>
<h2>How to decide in practice</h2>
<p>Write your must-see list before choosing a vehicle. If every item sits on Route 1 or a paved spur, save the 4×4 upgrade. If Landmannalaugar or F-roads appear, book one, and read the specific F-road opening dates for your travel month on road.is.</p>
<p>Still unsure? Our Reykjavík team reviews routes daily. Tell us your dates and dream stops when you book, we will match you to the smallest vehicle that safely fits the trip.</p>
HTML,
                'featured_image' => '/images/homepage/why-photo.jpg',
                'image_alt' => '4×4 camper on gravel',
            ],
            [
                'slug' => 'chasing-the-northern-lights',
                'title' => 'Chasing the northern lights',
                'kicker' => 'Nature',
                'excerpt' => 'Season, forecasts, and how to plan aurora nights without losing sleep.',
                'body' => <<<'HTML'
<p>The aurora borealis is unpredictable, addictive, and worth every cold hour you spend looking up. Iceland sits just south of the Arctic Circle, close enough for strong displays when conditions align, accessible enough that you do not need a remote Arctic expedition to see them.</p>
<p>Chasing the northern lights is really chasing darkness, clear skies, and solar activity. Get those three together and even a modest display feels magical.</p>
<h2>When is aurora season?</h2>
<p>Technically the aurora occurs year-round, but you need dark skies to see it. In Iceland that means roughly <strong>September through April</strong>, with peak cultural season from October to March. Mid-summer (May–July) is too bright, the midnight sun washes out faint auroras.</p>
<p>Within winter, there is no single "best month." February and March offer longer evenings than deep December with slightly milder weather. New moon weeks help, but a full moon only washes out weak displays, strong auroras punch through anyway.</p>
<h2>Understanding forecasts</h2>
<p>Use multiple tools; no app guarantees a show.</p>
<ul>
<li><strong>vedur.is</strong>, Icelandic Met Office cloud cover maps. Clear skies matter more than a perfect KP index under thick clouds.</li>
<li><strong>NOAA / Aurora apps</strong>, KP index and solar wind data. KP 3+ can be visible in Iceland; KP 5+ often means vivid displays.</li>
<li><strong>Local alerts</strong>, guesthouses and campsites sometimes wake guests for strong activity.</li>
</ul>
<p>Forecast "high activity" with 100% cloud cover equals nothing visible. A KP 2 night with crystal-clear skies can still reward patience.</p>
<h2>Where to watch</h2>
<p>Get away from Reykjavík light pollution when possible, not because the city blocks all auroras, but because suburban glow dulls faint curtains. South coast, Snæfellsnes, Mývatn, and the east fjords offer dark skies with open horizons.</p>
<p>You do not need a specific landmark. Pull off safely at a designated viewpoint or campsite, turn off headlights, and let your eyes adjust for 10–15 minutes. Avoid ice-slick pullouts; park where you can leave quickly if weather turns.</p>
<h2>Photography vs experience</h2>
<p>Phone cameras improve every year, but long-exposure settings or a tripod still beat handheld shots. If you are not photographing, put the phone away for at least one display, watching through a screen steals the scale of movement across the sky.</p>
<p>Dress for standing still in wind: base layers, insulated jacket, hat, gloves, warm boots. You will feel colder than hiking because you are not moving.</p>
<h2>Planning a trip around auroras</h2>
<ul>
<li>Stay <strong>at least 4–5 nights</strong> in aurora season to multiply your chances.</li>
<li>Plan daytime activities separately, do not sacrifice Skógafoss because you stayed up until 3:00.</li>
<li>A campervan lets you sleep near dark-sky spots; a guesthouse gives warmth between checks. Many travellers mix both.</li>
<li>Book flexible cancellation where possible, weather, not willpower, decides success.</li>
</ul>
<h2>What aurora looks like to the eye</h2>
<p>Cameras often show greener, brighter images than your eyes perceive, especially on weak nights. Strong displays look clearly green, sometimes purple or red at the edges, with visible movement and "curtains" rippling across the sky. Weak activity can look like a greyish cloud until you stare long enough to notice slow shimmer.</p>
<h2>Common mistakes</h2>
<ul>
<li>Checking once at 22:00 and giving up, activity often peaks between 22:00 and 02:00 but can arrive anytime after dark.</li>
<li>Driving tired on icy roads chasing a clearing, sleep and try tomorrow.</li>
<li>Expecting neon-green skies every night, some trips get one great show; others get several faint ones.</li>
</ul>
<p>The northern lights reward patience and humility. Plan a brilliant Iceland trip first; treat the aurora as a possible bonus on top, and when it appears, you will remember why you came back.</p>
HTML,
                'featured_image' => '/images/homepage/cardcamper.jpg',
                'image_alt' => 'Northern lights over Iceland',
                'read_time' => '9 min read',
                'is_featured' => false,
                'aurora' => true,
                'sort_order' => 3,
            ],
            [
                'slug' => 'campervan-vs-guesthouse',
                'title' => 'Campervan vs guesthouse',
                'kicker' => 'Compare',
                'excerpt' => 'Flexibility vs comfort, how to mix vans and stays on one trip.',
                'body' => <<<'HTML'
<p>Iceland invites two very different styles of travel: the campervan loop with midnight sun and campsite coffee, or the guesthouse route with made beds, shared kitchens, and local hosts who know which road closed overnight. Neither is wrong, and increasingly, travellers choose both.</p>
<h2>The campervan case</h2>
<p>A camper gives you <strong>freedom and spontaneity</strong>. Your accommodation travels with you. When a waterfall detour runs long, you do not race to a hotel check-in. Summer camping culture is social, scenic, and often set against mountains or ocean.</p>
<ul>
<li><strong>Pros:</strong> Flexible schedule, cooking saves money, iconic Iceland road-trip feel, easy access to remote campsites.</li>
<li><strong>Cons:</strong> Weather exposure, bathroom and shower depend on campsites, setup/pack-down daily, limited space in wind.</li>
<li><strong>Best for:</strong> Ring Road loops, budget-conscious travellers, couples or small groups comfortable in compact spaces.</li>
</ul>
<h2>The guesthouse case</h2>
<p>Guesthouses, and farm stays, offer <strong>warmth and recovery</strong>. After a day of driving in horizontal rain, a dry room and proper shower matter. Hosts share advice you will not find in guidebooks: which gravel shortcut is currently brutal, where the sheep are loose, which café opened late.</p>
<ul>
<li><strong>Pros:</strong> Comfort, local connection, no campsite hunt after long drives, better sleep before early starts.</li>
<li><strong>Cons:</strong> Fixed locations, peak-season availability pressure, less spontaneity without advance booking.</li>
<li><strong>Best for:</strong> Winter travel, multi-generational groups, anyone prioritising rest over roaming every night.</li>
</ul>
<h2>Cost comparison (rough guide)</h2>
<p>Campervan rental plus Camping Card or nightly campsite fees often beats hotel chains but not always farm stays in shoulder season. Fuel and supermarket cooking tilt campers toward savings. Guesthouses bundle comfort, value depends on how you price sleep and time.</p>
<p>Hidden camper costs: propane, occasional premium campsites with full hookups, dining out when you are too tired to cook. Hidden guesthouse costs: eating at restaurants more often, longer drives between fixed points.</p>
<h2>The hybrid strategy</h2>
<p>Our favourite recommendation: <strong>mix both on one itinerary</strong>.</p>
<ul>
<li>Camper nights for south coast and east, maximum scenery, early starts at waterfalls.</li>
<li>Guesthouse in Akureyri or Mývatn, laundry, warm meal, recharge before the long westward drive.</li>
<li>Final night near Keflavík in a guesthouse before an early flight, no morning pack-down stress.</li>
</ul>
<p>MyTerraBook exists partly because this hybrid was awkward before, vans from one company, hotels from another, separate insurance paperwork. One account for cars, campervans, and guesthouses simplifies the loop.</p>
<h2>Seasonal guidance</h2>
<p><strong>Summer:</strong> Campers shine, long days, open campsites, milder mornings for cooking outside. <strong>Winter:</strong> Guesthouses win on safety and comfort; many campsites close or limit services. Some hardy winter campers still tour, but know what you are signing up for. <strong>Shoulder:</strong> Either works; book guesthouses for popular towns.</p>
<h2>Who should avoid what</h2>
<p>Skip a camper if mobility is limited, you need reliable en-suite bathrooms nightly, or your group exceeds comfortable sleeping capacity. Skip guesthouse-only if you dream of waking at a lakeside campsite with no fixed checkout, that freedom is the camper's gift.</p>
<p>Choose the tool that matches each segment of the journey. Iceland rewards flexibility, and sometimes the smartest flexibility is swapping van nights for a warm bed when the forecast turns ugly.</p>
HTML,
                'featured_image' => '/images/homepage/host-van.jpg',
                'image_alt' => 'Campervan and guesthouse comparison',
                'read_time' => '8 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 4,
            ],
            [
                'slug' => 'winter-driving-in-iceland',
                'title' => 'Winter driving in Iceland',
                'kicker' => 'Safety',
                'excerpt' => 'Studded tyres, wind, daylight hours, and how to use road.is before every drive.',
                'body' => <<<'HTML'
<p>Winter Iceland is extraordinary, low sun, aurora skies, fewer tourists at Gullfoss. It is also unforgiving if you treat driving like a summer holiday with shorter days. Respecting weather, daylight, and road conditions matters more than vehicle size.</p>
<h2>Check road.is every morning</h2>
<p>Before you eat breakfast, open <strong>road.is</strong> (English available). Green, yellow, red, and white segments show maintained winter conditions. Closures happen without drama, a pass can shut while you sleep. Vedur.is adds wind and storm warnings; combine both before committing to a long transit day.</p>
<p>Do not rely on yesterday's all-clear. Do not assume Route 1 is always open, it usually is, but storms close sections, especially in the north and east.</p>
<h2>Tyres and rental vehicles</h2>
<p>Studded tyres are standard on MyTerraBook winter rentals, they bite into ice in ways all-season tyres cannot. That does not mean you can drive at summer speeds. Studded grip helps traction; it does not cancel physics on black ice or in 80 km/h gusts.</p>
<p>4×4 is helpful but not a substitute for caution on Route 1. Highland F-roads are closed in winter regardless of vehicle.</p>
<h2>Daylight planning</h2>
<p>In December, Reykjavík sees roughly four hours of meaningful daylight. Plan driving in those windows. A "quick" 200 km leg that felt easy in July becomes a night drive in January, and night driving in snow is where mistakes multiply.</p>
<ul>
<li>Schedule one major stop per daylight block.</li>
<li>Save aurora hunting for after dinner, not as a substitute for unfinished daytime miles.</li>
<li>Carry a headlamp for campsite setup even if you plan guesthouses, delays happen.</li>
</ul>
<h2>Wind: the invisible hazard</h2>
<p>Crosswinds on bridges, open plains, and the south coast flip vans and push cars into oncoming lanes. When vedur.is warns of strong wind, consider postponing travel or choosing a sheltered route. Open camper doors into wind, a common mistake, can bend hinges or injure hands.</p>
<p>Hold the steering wheel firmly in gusty sections. Reduce speed before bridges, not on them.</p>
<h2>Visibility and storms</h2>
<p>Whiteout conditions arrive fast. If visibility drops to unsafe levels, pull into the nearest safe parking, petrol station, farm pull-off, town, and wait. Icelandic rescue teams are excellent; calling them because you pressed onward is avoidable.</p>
<p>Never stop in the travel lane. Reflective vests help if you must exit the vehicle.</p>
<h2>What to pack in the car</h2>
<ul>
<li>Ice scraper and de-icer (often supplied; confirm at pickup)</li>
<li>Extra food, water, and warm layers beyond what you wear</li>
<li>Power bank for phone, cold drains batteries</li>
<li>Offline maps, signal gaps are common</li>
<li>Headlamp and basic first-aid kit</li>
</ul>
<h2>Guesthouses vs campers in winter</h2>
<p>Many campsites reduce services or close entirely from October to April. Winter camping is possible for experienced travellers with proper equipment; most visitors are happier in guesthouses with included breakfast and dry boots by the door. Mixing a few guesthouse nights into a winter ring trip is sensible, not a compromise.</p>
<h2>Insurance and gravel</h2>
<p>Winter roads mean more ash, sand, and gravel spray. Gravel protection included with MyTerraBook covers the chips that accumulate on any long Icelandic trip, winter does not exempt you from slow gravel driving on Route 1's eastern stretches.</p>
<p>Winter rewards patience and short days well spent. Drive less, see deeply, and let the island set the pace when the forecast turns.</p>
HTML,
                'featured_image' => '/images/homepage/cardcar.jpg',
                'image_alt' => 'Winter driving in Iceland',
                'read_time' => '9 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 5,
            ],
            [
                'slug' => 'where-to-camp-legally-in-iceland',
                'title' => 'Where to camp legally in Iceland',
                'kicker' => 'Practical',
                'excerpt' => 'Campsite rules, overnight parking, and how the Camping Card works along Route 1.',
                'body' => <<<'HTML'
<p>Iceland's landscapes look wild and open, but camping is regulated. Wild camping with a tent or camper is restricted on most land, and disrespectful overnight parking has led to closures and angry locals. Understanding the rules keeps you legal, safe, and welcome on return visits.</p>
<h2>The core rule</h2>
<p>Since 2015, overnight stays in motorhomes, campervans, and rooftop tents are generally allowed <strong>only at designated campsites</strong>, unless you have explicit landowner permission. Plain cars sleeping in pullouts are also discouraged and often illegal when treated as camping.</p>
<p>This applies nationwide, not just near Reykjavík. "I saw someone else parked here" is not a legal defence.</p>
<h2>What about tents?</h2>
<p>Tent camping on uncultivated land was traditionally allowed for a single night on non-private, non-protected land with landowner permission when required. Rules tightened over the years and local bans proliferated after overtourism. In practice: <strong>use official campsites</strong> unless you have clear, current permission for a specific plot.</p>
<p>National parks and nature reserves have strict bans, Þingvellir, many waterfall areas, and popular coastal viewpoints included.</p>
<h2>Official campsites along Route 1</h2>
<p>The Ring Road has frequent campsites, often near towns: Vík, Kirkjubæjarklaustur, Höfn, Egilsstaðir, Mývatn, Akureyri area, Blönduós, and more. Facilities range from basic toilets and cold water to full kitchens, showers, and electrical hookups.</p>
<ul>
<li>Arrive before reception closes, often 20:00–22:00 in summer, earlier in shoulder season.</li>
<li>Pay on arrival or via campsite app where available.</li>
<li>Respect quiet hours; Icelanders camp for rest, not parties.</li>
</ul>
<h2>The Camping Card (Útivist)</h2>
<p>The Icelandic Camping Card bundles entry to participating campsites for a flat seasonal fee. It suits Ring Road travellers staying many nights. Not every site accepts it, verify the current list before relying on it in peak towns where private sites may opt out.</p>
<p>Do the maths: if you camp more than roughly six to eight paid nights, the card often pays off. MyTerraBook can advise current partners when you pick up your van.</p>
<h2>Campervan etiquette</h2>
<ul>
<li><strong>Waste:</strong> Use campsite disposal for grey water and toilet, never roadside drains or streams.</li>
<li><strong>Cooking:</strong> Propane only in ventilated outdoor setups; no indoor grilling.</li>
<li><strong>Paths:</strong> Stay on marked ground; moss damage takes decades to heal.</li>
<li><strong>Toilets:</strong> If your van lacks a toilet, plan stops at campsites, N1 stations, or public facilities, not nature.</li>
</ul>
<h2>When parking overnight without camping</h2>
<p>Sleeping in a car at a petrol station or pullout "just to rest" occupies a grey area and irritates communities where overtourism already strains infrastructure. If you are too tired to drive safely, choose a legal campsite or guesthouse, fatigue kills more trips than any waterfall skip.</p>
<h2>Highland and remote camping</h2>
<p>Highland F-roads lead to primitive sites with minimal services. Book where required, carry fuel and water, and leave no trace. Rivers rise after rain, never camp in dry riverbeds.</p>
<h2>Penalties and enforcement</h2>
<p>Fines for illegal camping and damage exist and increased as pressure on fragile sites grew. Enforcement is uneven but embarrassing encounters with landowners and rangers are common at famous viewpoints. Social media geotags accelerated damage, do not replicate old "secret spot" posts from 2014.</p>
<p>Camp legally, leave sites cleaner than you found them, and the next traveller, and the moss, both benefit.</p>
HTML,
                'featured_image' => '/images/homepage/cardcamper.jpg',
                'image_alt' => 'Camping in Iceland',
                'read_time' => '8 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 6,
            ],
            [
                'slug' => 'best-time-to-visit-iceland',
                'title' => 'Best time to visit Iceland',
                'kicker' => 'Planning',
                'excerpt' => 'Summer midnight sun, shoulder-season calm, or winter aurora, how to pick your window.',
                'body' => <<<'HTML'
<p>There is no single best month in Iceland, only the best month for <em>your</em> trip. Weather, daylight, prices, crowds, and what you want to see all shift dramatically between January and July. Here is how the seasons actually feel on the ground in Reykjavík and around Route 1.</p>
<h2>Summer (June–August)</h2>
<p><strong>Daylight:</strong> Nearly 24-hour light in June, energising and disorienting. You can hike at midnight; you might also forget to sleep.</p>
<p><strong>Weather:</strong> Warmest averages, but "warm" means 10–15°C with rain and wind still common. Do not pack only shorts.</p>
<p><strong>Crowds:</strong> Peak tourism, parking lots fill, Blue Lagoon and Golden Circle busses multiply. Book campsites and guesthouses early.</p>
<p><strong>Road access:</strong> All Route 1 open; highland F-roads open mid-summer depending on snow. Best window for Landmannalaugar if you have a 4×4.</p>
<p><strong>Ideal for:</strong> First-time Ring Road loops, campers, highland plans, families with school holidays.</p>
<h2>Shoulder seasons (May, September–October)</h2>
<p>Our team’s favourite compromise. May brings spring waterfalls and fewer buses; September offers autumn colour and aurora begins. October balances dark skies with manageable storms.</p>
<ul>
<li><strong>Pros:</strong> Lower prices than July, easier parking, still decent road access in September.</li>
<li><strong>Cons:</strong> May highlands may stay closed; October daylight shrinks fast; some campsites close mid-September.</li>
<li><strong>Ideal for:</strong> Photographers, couples avoiding crowds, aurora-curious travellers in late September onward.</li>
</ul>
<h2>Winter (November–March)</h2>
<p><strong>Daylight:</strong> Four to six hours in deepest winter, plan accordingly.</p>
<p><strong>Weather:</strong> Storms, ice, wind. Beauty and danger share the same road.</p>
<p><strong>Crowds:</strong> Fewer visitors except holiday weeks and New Year. Reykjavík city breaks popular.</p>
<p><strong>Road access:</strong> Route 1 mostly maintained but closures happen; F-roads closed. Golden Circle and south coast often accessible; always check road.is daily.</p>
<p><strong>Ideal for:</strong> Aurora hunters, ice-cave tours (with guides), travellers who accept flexible itineraries.</p>
<h2>Spring (April)</h2>
<p>Transition month, unpredictable weather, rising daylight, aurora still possible early April. Campsites reopen gradually. Good deals on rentals before summer surge; some highland roads still snow-bound.</p>
<h2>Matching season to goal</h2>
<ul>
<li><strong>Ring Road first visit:</strong> Late May–September.</li>
<li><strong>Northern lights primary:</strong> October–March, minimum five nights.</li>
<li><strong>Highlands and F-roads:</strong> July–August only, with 4×4.</li>
<li><strong>Lowest budget:</strong> Shoulder months; avoid Christmas/New Year spikes.</li>
<li><strong>Whale watching (north):</strong> Summer sailings from Húsavík and Akureyri.</li>
</ul>
<h2>Booking lead times</h2>
<p>July campervans and Vík guesthouses: book months ahead. November rentals: often available weeks out. If dates are fixed, reserve early; if flexible, shoulder season rewards spontaneity, except Christmas.</p>
<h2>Weather expectations vs reality</h2>
<p>Iceland does not do "gararanteed sunshine" any month. Summer storms can cancel flights; winter can gift still, golden hours. Layer clothing every season; check forecasts daily, not once.</p>
<p>Pick the season that matches your non-negotiables, then build the route backward from daylight, road access, and how much weather uncertainty you enjoy.</p>
HTML,
                'featured_image' => '/images/homepage/stay-hofn.jpg',
                'image_alt' => 'Iceland landscape by season',
                'read_time' => '9 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 7,
            ],
            [
                'slug' => 'fuel-and-food-stops-on-route-1',
                'title' => 'Fuel and food stops on Route 1',
                'kicker' => 'Practical',
                'excerpt' => 'Town-by-town essentials for groceries, petrol, and coffee along the Ring Road.',
                'body' => <<<'HTML'
<p>Route 1 is well served compared to true wilderness, but gaps are long enough to catch unprepared drivers. Running low on fuel east of Vík or assuming a supermarket every hour leads to stress. This guide maps the practical stops our team uses when we loop the island.</p>
<h2>Golden rule: fill early, fill often</h2>
<p>Never treat the next station as guaranteed. When the tank hits half on the south or east coast, stop. N1, Olís, and Orkan stations accept cards; some remote pumps need PIN credit cards, check with your bank before travel.</p>
<h2>Reykjavík to Vík (South)</h2>
<ul>
<li><strong>Selfoss:</strong> Bonus, Krónan, N1, last large supermarket before smaller towns.</li>
<li><strong>Hella / Hvolsvöllur:</strong> Fuel, basic groceries, N1 meals.</li>
<li><strong>Vík:</strong> Fuel, small grocery, restaurants, key overnight hub; stock up if continuing east.</li>
</ul>
<h2>Vík to Höfn (Southeast)</h2>
<p>The longest sparse stretch for many first-timers.</p>
<ul>
<li><strong>Kirkjubæjarklaustur:</strong> Critical fuel and food stop, do not skip if the gauge is low.</li>
<li><strong>Skaftafell area:</strong> Visitor centre café; limited fuel, plan in Kirkjubæjarklaustur.</li>
<li><strong>Höfn:</strong> Full services, langoustine fame, good overnight base.</li>
</ul>
<h2>Höfn to Egilsstaðir (East)</h2>
<ul>
<li><strong>Eastern fjords towns:</strong> Small shops in Djúpivogur, Seyðisfjörður (detour), etc., charming but limited hours.</li>
<li><strong>Egilsstaðir:</strong> Regional hub, supermarket, fuel, pharmacy. Restock here before heading north.</li>
</ul>
<h2>Egilsstaðir to Akureyri (Northeast)</h2>
<ul>
<li><strong>Reykjahlíð / Mývatn:</strong> Fuel and basic supplies near the lake.</li>
<li><strong>Akureyri:</strong> Full city services, treat it like a reset day: laundry, restaurants, groceries.</li>
</ul>
<h2>Akureyri to Reykjavík (North & West)</h2>
<ul>
<li><strong>Blönduós, Sauðárkrókur:</strong> Fuel and modest groceries on Skagafjörður.</li>
<li><strong>Borgarnes:</strong> Last major stop before the capital region, N1, supermarket, coffee.</li>
<li><strong>Reykjavík:</strong> Everything, but return your rental with a full tank near Keflavík if required by your agreement.</li>
</ul>
<h2>Supermarket tips</h2>
<p><strong>Bonus</strong> and <strong>Krónan</strong> are cheapest; <strong>Nettó</strong> and <strong>Hagkaup</strong> appear in larger towns. Hours shorten on Sundays and in winter. Buy bread, skyr, pasta, coffee, and snacks in Reykjavík or Selfoss before remote stretches.</p>
<p>Alcohol is only sold in Vínbúðin state shops with limited hours, plan in Reykjavík or Akureyri.</p>
<h2>Cost expectations</h2>
<p>Iceland is expensive. A sit-down lunch easily runs €20–30; petrol adds up on a full loop. Campers cooking pasta and skyr save significantly; guesthouse breakfasts help balance restaurant dinners.</p>
<h2>Coffee and toilets</h2>
<p>N1 and Olís stations are unofficial rest infrastructure, clean toilets, hot dogs, coffee. Use them generously; the next stop might be an hour away.</p>
<h2>Camper-specific</h2>
<p>Propane canisters available at larger towns and some campsites, ask at pickup which type your stove uses. Grey water disposal only at designated campsite points.</p>
<p>Mark the hubs on your map before departure: Selfoss, Vík, Kirkjubæjarklaustur, Höfn, Egilsstaðir, Akureyri, Borgarnes. Treat them like pit walls in a long race, regular, non-optional.</p>
HTML,
                'featured_image' => '/images/homepage/cardhouse.jpg',
                'image_alt' => 'Fuel and food stops on Route 1',
                'read_time' => '8 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 8,
            ],
            [
                'slug' => 'your-first-24-hours-after-keflavik',
                'title' => 'Your first 24 hours after Keflavík',
                'kicker' => 'Practical',
                'excerpt' => 'Pickup, SIM card, groceries, and where to sleep after landing at KEF.',
                'body' => <<<'HTML'
<p>You stepped off the plane at Keflavík, jet-lagged, excited, possibly facing sideways rain. The first 24 hours set the tone for your Iceland trip. Move slowly, handle essentials, and resist cramming the Golden Circle before you have slept.</p>
<h2>Hour 0–1: Airport to rental pickup</h2>
<p>KEF is compact. After customs and baggage, follow signs to your rental pickup. MyTerraBook partners meet near the airport or shuttle you to a nearby depot, confirm instructions in your booking email before landing.</p>
<ul>
<li>Inspect the vehicle with staff, note existing damage on the form.</li>
<li>Ask about tyres (summer vs studded), propane, camping kit, and emergency number.</li>
<li>Adjust mirrors and headlights before leaving the lot, you may drive in dusk immediately in winter.</li>
</ul>
<h2>Hour 1–2: SIM card and money</h2>
<p>Buy a local SIM at the airport (Síminn, Nova, Vodafone kiosks) or in Keflavík town if queues are long. Data matters more than voice, maps, road.is, aurora apps all need connectivity.</p>
<p>Cards work widely; carry a backup card. Some rural pumps need chip-and-PIN.</p>
<h2>Hour 2–4: First supplies</h2>
<p>Stop at a supermarket in Keflavík or Reykjavík before wilderness. Minimum shop list:</p>
<ul>
<li>Water and snacks for the drive</li>
<li>Coffee/tea and basic breakfast if camping tomorrow</li>
<li>Phone charger and car USB adapter</li>
<li>Rain layer within reach, not buried in the back</li>
</ul>
<p>Do not grocery shop in Reykjavík city centre at premium prices if you can hit a Bonus in Kópavogur or on the way into town.</p>
<h2>Blue Lagoon: yes or no on day one?</h2>
<p>The Blue Lagoon sits between the airport and Reykjavík, tempting after a red-eye. It works if you booked a timed slot and accept tired soaking. It fails when combined with Golden Circle and three hours driving on no sleep.</p>
<p>Alternative: save the lagoon for your departure day (many do last slot before KEF) or skip it for a quieter local pool in Reykjavík later.</p>
<h2>First night: where to sleep</h2>
<p>Three sensible patterns:</p>
<ul>
<li><strong>Reykjavík guesthouse</strong>, walkable dinner, sleep off jet lag, start Golden Circle fresh tomorrow.</li>
<li><strong>Near Keflavík</strong>, ideal if you land late and pickup is early next morning.</li>
<li><strong>Camper first night at a campsite</strong>, only if you arrive with daylight and energy; setting up a van in darkness and rain after a transatlantic flight is brutal.</li>
</ul>
<p>MyTerraBook lets you book a guesthouse for night one and collect your camper the same day or the next, ask our team if splitting pickup reduces stress.</p>
<h2>Driving after a long flight</h2>
<p>Icelandic roads demand attention, sheep, single-lane bridges, gravel transitions. If you are nodding off, check into accommodation and sleep. Fatigue accidents outrank aurora disappointment.</p>
<p>Winter arrivals may mean ice before you reach Reykjavík. Take the main roads, reduce speed, and use road.is before any evening drive.</p>
<h2>Reykjavík evening</h2>
<p>If energy allows, a short walk on Laugavegur, early dinner (fish soup, lamb, or hot dogs at Bæjarins beztu), and an early bed beats nightlife on day zero. Reserve restaurants if you land on a weekend in summer.</p>
<h2>Tomorrow morning</h2>
<p>Check weather, plan one modest activity, Golden Circle or Reykjanes, and avoid booking three waterfalls before lunch. Pick up the rest of your loop rhythm on day two when coffee and time zones align.</p>
<p>The first day is logistics, not heroics. Handle pickup, connectivity, food, and sleep, the Ring Road will still be there tomorrow.</p>
HTML,
                'featured_image' => '/images/homepage/hero.jpg',
                'image_alt' => 'Arriving at Keflavík airport',
                'read_time' => '8 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 9,
            ],
            [
                'slug' => 'lorem-ipsum-travel-tips',
                'title' => 'Lorem ipsum dolor sit amet',
                'kicker' => 'Placeholder',
                'excerpt' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.',
                'body' => <<<'HTML'
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
<h2>Consectetur adipiscing elit</h2>
<p>Curabitur pretium tincidunt lacus. Nulla gravida orci a odio. Nullam varius, turpis et commodo pharetra, est eros bibendum elit, nec luctus magna felis sollicitudin mauris. Integer in mauris eu nibh euismod gravida.</p>
<ul>
<li>Nulla facilisi morbi tempus iaculis urna</li>
<li>Id volutpat lacus laoreet non curabitur gravida</li>
<li>Enim sed faucibus turpis in eu mi bibendum</li>
</ul>
<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum tortor quam, feugiat vitae, ultricies eget, tempor sit amet, ante.</p>
HTML,
                'featured_image' => '/images/homepage/why-photo.jpg',
                'image_alt' => 'Lorem ipsum travel placeholder',
                'read_time' => '3 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 10,
            ],
            [
                'slug' => 'lorem-ipsum-road-guide',
                'title' => 'Consectetur adipiscing elit sed do',
                'kicker' => 'Guide',
                'excerpt' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.',
                'body' => <<<'HTML'
<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
<h2>Tempor incididunt ut labore</h2>
<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium.</p>
<p>Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit.</p>
<h2>Quis autem vel eum iure</h2>
<p>Neque porro quisquam est, qui dolorem ipsum quia dolor sit amet, consectetur, adipisci velit, sed quia non numquam eius modi tempora incidunt ut labore et dolore magnam aliquam quaerat voluptatem.</p>
HTML,
                'featured_image' => '/images/homepage/stay-hofn.jpg',
                'image_alt' => 'Lorem ipsum road guide placeholder',
                'read_time' => '4 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 11,
            ],
            [
                'slug' => 'lorem-ipsum-camping-notes',
                'title' => 'Sed do eiusmod tempor incididunt',
                'kicker' => 'Notes',
                'excerpt' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.',
                'body' => <<<'HTML'
<p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Quis ipsum suspendisse ultrices gravida. Risus commodo viverra maecenas accumsan lacus vel facilisis.</p>
<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<h2>Magna aliqua enim ad minim</h2>
<p>At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint occaecati cupiditate non provident.</p>
<ul>
<li>Similique sunt in culpa qui officia deserunt mollitia animi</li>
<li>Id est laborum et dolorum fuga et harum quidem rerum</li>
<li>Facilis est et expedita distinctio nam libero tempore</li>
</ul>
<p>Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat.</p>
HTML,
                'featured_image' => '/images/homepage/cardcamper.jpg',
                'image_alt' => 'Lorem ipsum camping placeholder',
                'read_time' => '5 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 12,
            ],
            [
                'slug' => 'lorem-ipsum-weekend-ideas',
                'title' => 'Ut labore et dolore magna aliqua',
                'kicker' => 'Weekend',
                'excerpt' => 'Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat duis aute irure dolor.',
                'body' => <<<'HTML'
<p>Quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
<h2>Voluptate velit esse cillum</h2>
<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
<p>Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.</p>
<h2>Excepteur sint occaecat</h2>
<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>
HTML,
                'featured_image' => '/images/homepage/host-van.jpg',
                'image_alt' => 'Lorem ipsum weekend placeholder',
                'read_time' => '2 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 13,
            ],
            [
                'slug' => 'lorem-ipsum-essential-checklist',
                'title' => 'Duis aute irure dolor in reprehenderit',
                'kicker' => 'Checklist',
                'excerpt' => 'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',
                'body' => <<<'HTML'
<p>Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>
<h2>Officia deserunt mollit anim</h2>
<p>Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p>
<ul>
<li>Duis aute irure dolor in reprehenderit in voluptate velit</li>
<li>Esse cillum dolore eu fugiat nulla pariatur</li>
<li>Excepteur sint occaecat cupidatat non proident</li>
<li>Sunt in culpa qui officia deserunt mollit anim id est laborum</li>
</ul>
<p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.</p>
HTML,
                'featured_image' => '/images/homepage/cardcar.jpg',
                'image_alt' => 'Lorem ipsum checklist placeholder',
                'read_time' => '3 min read',
                'is_featured' => false,
                'aurora' => false,
                'sort_order' => 14,
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::query()->updateOrCreate(
                ['slug' => $post['slug']],
                [
                    ...$post,
                    'status' => BlogPostStatus::Published,
                    'published_at' => now(),
                ],
            );
        }
    }
}
