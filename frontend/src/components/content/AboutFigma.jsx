import { Link } from 'react-router-dom'
import Container from '../site/Container'
import FigmaIcon from '../site/FigmaIcon'
import { usePageContent } from '../../context/SiteContentContext'
import '../../styles/about-figma.css'

const DEFAULTS = {
  crumb: 'About us',
  eyebrow: 'ABOUT MYTERRABOOK',
  heroLead: 'MyTerraBook brings campervans, cars & 4×4s and guesthouses together in one place — so you can plan the drive, the wheels and the bed for the night in a single booking. Born in Reykjanesbær, built for every country.',
  primaryLabel: 'Find your ride',
  primaryHref: '/campervans',
  secondaryLabel: 'Become a host',
  secondaryHref: '/become-a-host',
  heroImage: '/figma/login-hero.png',
  stats: [
    { icon: 'star', value: '4.9', text: 'Average rating from 12,400+ reviews' },
    { icon: 'sliders', value: '350+', text: 'Campervans, cars & guesthouses listed' },
    { icon: 'headset', value: '24/7', text: 'Local support, every day of your trip' },
    { icon: 'calendar', value: '48h', text: 'Free cancellation up to 48h before pick-up' },
  ],
  storyEyebrow: 'OUR STORY',
  storyImage: '/figma/about-camper.png',
  story: [
    'MyTerraBook began in Reykjanesbær, ten minutes from Keflavík airport — the place almost every Iceland road trip begins. We watched travellers juggle three websites, three deposits and three sets of terms just to get a van, a car and a bed for the night.',
    'So we built one platform for all of it. Local rental companies and guesthouse owners list directly with us, and travellers book the whole trip in one go — with clear prices, real insurance and people on the ground who answer the phone.',
    'Today we work with local partners like Konvin, Green Light and Foxie, and we are building MyTerraBook for road trips in every country.',
  ],
  address: 'MyTerraBook ehf. · 260 Reykjanesbær, Iceland',
  valuesEyebrow: 'WHAT WE STAND FOR',
  valuesLead: 'The promises behind every van, car and guesthouse on MyTerraBook.',
  values: [
    { icon: 'mountain', title: 'Local first', text: 'Every listing comes from a local company or host who knows the roads, the weather and the hidden detours.' },
    { icon: 'tag', title: 'Honest prices', text: 'What you see is what you pay — rental rate, fees and taxes shown up front, with free cancellation until 48h before pick-up.' },
    { icon: 'shield', title: 'Covered on every road', text: 'Basic insurance is always included, with Premium and Platinum cover for gravel, sand & ash when you want peace of mind.' },
  ],
  productsEyebrow: 'ONE PLATFORM',
  productsLead: 'Pick one, or combine all three in a single booking.',
  productsButton: 'See all listings',
  productsHref: '/campervans',
  products: [
    { icon: 'tent', title: 'Campervans', text: 'Drive and sleep in one — from compact 2-seaters to 4×4 campers that are winter-ready.', meta: '15 listings · from €59.80/day', button: 'Browse campervans', href: '/campervans', image: '/figma/about-camper.png' },
    { icon: 'gauge', title: 'Cars & 4×4s', text: 'Small city cars for the Ring Road, rugged 4×4s for the highlands and F-roads.', meta: '18 listings · from €47.60/day', button: 'Browse cars', href: '/cars', image: '/figma/about-car.png' },
    { icon: 'heart', title: 'Guesthouses', text: 'Cosy rooms, cottages and farm stays — a warm bed at the end of every driving day.', meta: '9 listings · from €3.57/night', button: 'Browse guesthouses', href: '/guesthouses', image: '/figma/about-stay.png' },
  ],
  ctaEyebrow: 'READY WHEN YOU ARE',
  ctaLead: 'Search campervans, cars and guesthouses across Iceland — and soon, every country.',
  ctaPrimary: 'Plan your trip',
  ctaPrimaryHref: '/campervans',
  ctaSecondary: 'Become a host',
  ctaSecondaryHref: '/become-a-host',
}

function filled(value, fallback) {
  return typeof value === 'string' && value.trim() !== '' ? value : fallback
}

export default function AboutFigma() {
  const { page } = usePageContent('about')
  const hero = page.hero || {}
  const storySection = page.story || {}
  const valuesSection = page.values || {}
  const productsSection = page.products || {}
  const cta = page.cta || {}
  const copy = {
    ...DEFAULTS,
    crumb: filled(hero.crumb, DEFAULTS.crumb),
    eyebrow: filled(hero.eyebrow, DEFAULTS.eyebrow),
    headingBefore: filled(hero.headingBefore, 'One booking for the '),
    headingAccent: filled(hero.headingAccent, 'whole'),
    headingAfter: filled(hero.headingAfter, ' road trip.'),
    heroLead: filled(hero.lead, DEFAULTS.heroLead),
    primaryLabel: filled(hero.primaryLabel, DEFAULTS.primaryLabel),
    primaryHref: filled(hero.primaryHref, DEFAULTS.primaryHref),
    secondaryLabel: filled(hero.secondaryLabel, DEFAULTS.secondaryLabel),
    secondaryHref: filled(hero.secondaryHref, DEFAULTS.secondaryHref),
    heroImage: filled(hero.image, DEFAULTS.heroImage),
    storyEyebrow: filled(storySection.eyebrow, DEFAULTS.storyEyebrow),
    storyBefore: filled(storySection.headingBefore, 'We started where the '),
    storyAccent: filled(storySection.headingAccent, 'road'),
    storyAfter: filled(storySection.headingAfter, ' starts.'),
    storyImage: filled(storySection.image, DEFAULTS.storyImage),
    address: filled(storySection.address, DEFAULTS.address),
    valuesEyebrow: filled(valuesSection.eyebrow, DEFAULTS.valuesEyebrow),
    valuesBefore: filled(valuesSection.headingBefore, 'Three things we '),
    valuesAccent: filled(valuesSection.headingAccent, 'never'),
    valuesAfter: filled(valuesSection.headingAfter, ' compromise on.'),
    valuesLead: filled(valuesSection.lead, DEFAULTS.valuesLead),
    productsEyebrow: filled(productsSection.eyebrow, DEFAULTS.productsEyebrow),
    productsBefore: filled(productsSection.headingBefore, 'Everything your trip '),
    productsAccent: filled(productsSection.headingAccent, 'needs'),
    productsAfter: filled(productsSection.headingAfter, '.'),
    productsLead: filled(productsSection.lead, DEFAULTS.productsLead),
    productsButton: filled(productsSection.button, DEFAULTS.productsButton),
    productsHref: filled(productsSection.href, DEFAULTS.productsHref),
    ctaEyebrow: filled(cta.eyebrow, DEFAULTS.ctaEyebrow),
    ctaBefore: filled(cta.headingBefore, 'Your next road trip starts '),
    ctaAccent: filled(cta.headingAccent, 'here'),
    ctaAfter: filled(cta.headingAfter, '.'),
    ctaLead: filled(cta.lead, DEFAULTS.ctaLead),
    ctaPrimary: filled(cta.primaryLabel, DEFAULTS.ctaPrimary),
    ctaPrimaryHref: filled(cta.primaryHref, DEFAULTS.ctaPrimaryHref),
    ctaSecondary: filled(cta.secondaryLabel, DEFAULTS.ctaSecondary),
    ctaSecondaryHref: filled(cta.secondaryHref, DEFAULTS.ctaSecondaryHref),
  }
  const stats = page.stats?.some((item) => item.text) ? page.stats : DEFAULTS.stats
  const story = storySection.paragraphs?.some((item) => item.text)
    ? storySection.paragraphs.map((item) => item.text)
    : DEFAULTS.story
  const values = valuesSection.items?.length ? valuesSection.items : DEFAULTS.values
  const products = productsSection.items?.length ? productsSection.items : DEFAULTS.products

  return (
    <div className="tb-about">
      <section className="tb-about-hero">
        <Container>
          <nav className="tb-crumbs"><Link to="/">Home</Link><span>/</span><span>{copy.crumb}</span></nav>
          <div className="tb-about-hero-row">
            <div>
              <p className="tb-kicker">{copy.eyebrow}</p>
              <h1>{copy.headingBefore}<em>{copy.headingAccent}</em>{copy.headingAfter}</h1>
              <p>{copy.heroLead}</p>
              <div className="tb-about-actions">
                <Link to={copy.primaryHref} className="tb-btn navy">{copy.primaryLabel} <FigmaIcon name="arrow-white" size={16} /></Link>
                <Link to={copy.secondaryHref} className="tb-btn ghost">{copy.secondaryLabel} <FigmaIcon name="arrow" size={16} /></Link>
              </div>
            </div>
            <img src={copy.heroImage} alt="" />
          </div>
        </Container>
      </section>

      <section className="tb-about-stats">
        <Container>
          {stats.map((item) => (
            <article key={item.value}>
              <span><FigmaIcon name={item.icon} size={18} /></span>
              <strong>{item.value}</strong>
              <p>{item.text}</p>
            </article>
          ))}
        </Container>
      </section>

      <section className="tb-about-story">
        <Container>
          <img src={copy.storyImage} alt="" />
          <div>
            <p className="tb-kicker">{copy.storyEyebrow}</p>
            <h2>{copy.storyBefore}<em>{copy.storyAccent}</em>{copy.storyAfter}</h2>
            {story.map((paragraph) => <p key={paragraph}>{typeof paragraph === 'string' ? paragraph : paragraph.text}</p>)}
            <p className="tb-about-address"><FigmaIcon name="pin" size={18} />{copy.address}</p>
          </div>
        </Container>
      </section>

      <section className="tb-about-values">
        <Container>
          <header>
            <p className="tb-kicker">{copy.valuesEyebrow}</p>
            <h2>{copy.valuesBefore}<em>{copy.valuesAccent}</em>{copy.valuesAfter}</h2>
            <p>{copy.valuesLead}</p>
          </header>
          <div>
            {values.map((item) => (
              <article key={item.title}>
                <span><FigmaIcon name={item.icon === 'tag' ? 'star' : item.icon} size={24} /></span>
                <h3>{item.title}</h3>
                <p>{item.text}</p>
              </article>
            ))}
          </div>
        </Container>
      </section>

      <section className="tb-about-products">
        <Container>
          <header>
            <div>
              <p className="tb-kicker">{copy.productsEyebrow}</p>
              <h2>{copy.productsBefore}<em>{copy.productsAccent}</em>{copy.productsAfter}</h2>
              <p>{copy.productsLead}</p>
            </div>
            <Link to={copy.productsHref}>{copy.productsButton}</Link>
          </header>
          <div className="tb-about-cards">
            {products.map((item) => (
              <article key={item.title}>
                <img src={item.image} alt="" />
                <div>
                  <h3><span><FigmaIcon name={item.icon} size={20} /></span>{item.title}</h3>
                  <p>{item.text}</p>
                  <strong>{item.meta}</strong>
                  <Link to={item.href}>{item.button} <FigmaIcon name="arrow-white" size={14} /></Link>
                </div>
              </article>
            ))}
          </div>
        </Container>
      </section>

      <section className="tb-about-cta">
        <Container>
          <div>
            <div>
              <p>{copy.ctaEyebrow}</p>
              <h2>{copy.ctaBefore}<em>{copy.ctaAccent}</em>{copy.ctaAfter}</h2>
              <span>{copy.ctaLead}</span>
            </div>
            <div>
              <Link to={copy.ctaPrimaryHref} className="tb-btn gold">{copy.ctaPrimary} <FigmaIcon name="arrow" size={16} /></Link>
              <Link to={copy.ctaSecondaryHref} className="tb-btn white">{copy.ctaSecondary} <FigmaIcon name="arrow" size={16} /></Link>
            </div>
          </div>
        </Container>
      </section>
    </div>
  )
}
