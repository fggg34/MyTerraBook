import { useMemo, useState } from 'react'
import { Link } from 'react-router-dom'
import Container from '../site/Container'
import { usePageContent } from '../../context/SiteContentContext'
import StrokeIcon from './StrokeIcon'
import '../../styles/frames.css'

const ITEMS = [
  {
    num: '01',
    cat: 'booking',
    label: 'BOOKING & PAYMENT',
    q: 'How does booking work on MyTerraBook?',
    a: 'Choose your campervan, car or guesthouse, pick your dates and send a booking request. Your host reviews the request — nothing is charged today. Once it is approved you pay a 20% prepayment, and the balance is due on pick-up or check-in. You will get a confirmation with all pre-trip details by email.',
    link: { label: 'View the booking process', href: '/good-to-know/how-to-plan-a-road-trip-in-7-days' },
  },
  {
    num: '02',
    cat: 'booking',
    label: 'BOOKING & PAYMENT',
    q: 'Which payment methods do you accept?',
    a: 'We accept Visa, Mastercard and American Express, plus Apple Pay and Google Pay. Card payments are processed securely by Rapyd. Prices are shown in EUR; USD, GBP and ISK are estimates. A 20% prepayment is taken once the host approves, and the balance is due at pick-up or check-in.',
  },
  {
    num: '03',
    cat: 'booking',
    label: 'BOOKING & PAYMENT',
    q: 'Can I cancel or change my booking?',
    a: 'You can cancel free of charge until 48 hours before pick-up or check-in unless the listing states a stricter policy. Late cancellations follow the host’s refund policy. To change dates, locations or extras, use “Request a modification” in your account — you will see the new total before anything changes.',
    link: { label: 'Read the refund policy', href: '/refund-policy' },
  },
  {
    num: '04',
    cat: 'insurance',
    label: 'INSURANCE & COVER',
    q: 'What insurance is included in the price?',
    a: 'Each vehicle comes with Basic cover. Premium and Platinum protection can be added at checkout. The exact cover, excess and what is excluded are shown on the listing and again before you pay.',
  },
  {
    num: '05',
    cat: 'insurance',
    label: 'INSURANCE & COVER',
    q: 'Am I covered for gravel, sand and ash damage?',
    a: 'Gravel, sand and ash are the most common causes of damage on Icelandic roads. Basic cover is included with every vehicle; Premium and Platinum add protection for that kind of damage. Check the protection on the listing before you confirm.',
  },
  {
    num: '06',
    cat: 'road',
    label: 'ON THE ROAD',
    q: 'Can I drive my rental on F-roads and highland routes?',
    a: 'F-roads are only permitted in vehicles the host has marked as 4×4 and F-road approved. Off-road driving is illegal in Iceland and is never covered by insurance. Check road.is before you leave the paved road.',
  },
  {
    num: '07',
    cat: 'road',
    label: 'ON THE ROAD',
    q: 'Where do I pick up and drop off my vehicle?',
    a: 'Pick-up and drop-off locations are shown on each listing. Airport pick-up is available at Keflavík, about ten minutes from our office in Reykjanesbær. The exact meeting point is in your confirmation email.',
  },
  {
    num: '08',
    cat: 'road',
    label: 'ON THE ROAD',
    q: 'Is mileage unlimited, and what about one-way trips?',
    a: 'Mileage is unlimited. One-way trips are available where the host offers them, and any one-way, location or cleaning fee is included in the total you see at checkout.',
  },
  {
    num: '09',
    cat: 'booking',
    label: 'BOOKING & PAYMENT',
    q: 'What happens if my host cancels?',
    a: 'If a host cancels an approved booking, you get a full refund of everything you paid. Our support team will also help you find a comparable vehicle or stay.',
    extra: true,
  },
  {
    num: '10',
    cat: 'insurance',
    label: 'INSURANCE & COVER',
    q: 'Is a security deposit taken at pick-up?',
    a: 'A temporary hold may be placed by the host at pick-up and released after the vehicle has been checked. The amount is shown in your booking before you travel.',
    extra: true,
  },
  {
    num: '11',
    cat: 'road',
    label: 'ON THE ROAD',
    q: 'Can I wild camp in a campervan?',
    a: 'No. Overnight parking outside registered campsites is not allowed for campervans in Iceland. Use the campsite map to plan each night before you drive.',
    link: { label: 'Open the campsite map', href: '/campsite-map' },
    extra: true,
  },
  {
    num: '12',
    cat: 'booking',
    label: 'BOOKING & PAYMENT',
    q: 'How are refunds paid?',
    a: 'Refunds go back to the original payment method within 5–10 business days of being approved, in EUR. Your bank’s exchange rate may make the amount slightly different if you paid in another currency.',
    extra: true,
  },
]

const CATS = [
  { id: 'all', label: 'All', icon: 'grid' },
  { id: 'booking', label: 'Booking & payment', icon: 'card' },
  { id: 'insurance', label: 'Insurance & cover', icon: 'shield' },
  { id: 'road', label: 'On the road', icon: 'route' },
]

const CHIPS = ['Cancellation', 'Insurance', 'F-roads', 'Pick-up']

export default function FaqFigma() {
  const { page } = usePageContent('faq')
  const items = page.figmaItems?.length ? page.figmaItems : ITEMS
  const [query, setQuery] = useState('')
  const [cat, setCat] = useState('all')
  const [open, setOpen] = useState('01')
  const [more, setMore] = useState(false)

  const visible = useMemo(() => {
    const q = query.trim().toLowerCase()
    return items.filter((item) => {
      if (item.extra && !more && !q) return false
      if (cat !== 'all' && item.cat !== cat) return false
      if (!q) return true
      return `${item.q} ${item.a} ${item.label}`.toLowerCase().includes(q)
    })
  }, [items, query, cat, more])

  return (
    <div className="tb-frame tb-faq">
      <section className="tb-faq-hero">
        <Container>
          <nav className="tb-crumbs">
            <Link to="/">Home</Link>
            <StrokeIcon name="chevron" size={12} />
            <strong>FAQs</strong>
          </nav>
          <div className="tb-faq-copy">
            <p className="tb-kicker">HELP CENTRE</p>
            <h1>Questions? We have <em>answers</em>.</h1>
            <p>Everything about booking, paying, insurance and life on the road — from the team that drives these roads every day.</p>
          </div>
          <form className="tb-faq-search" onSubmit={(event) => event.preventDefault()}>
            <StrokeIcon name="search" size={20} />
            <input
              value={query}
              onChange={(event) => setQuery(event.target.value)}
              placeholder="Search questions"
              aria-label="Search questions"
            />
            <button type="submit" className="tb-btn navy sm">Search <StrokeIcon name="arrow" size={16} /></button>
          </form>
          <div className="tb-faq-chips">
            <span>Popular:</span>
            {CHIPS.map((chip) => (
              <button key={chip} type="button" onClick={() => setQuery(chip)}>{chip}</button>
            ))}
          </div>
        </Container>
      </section>

      <section className="tb-faq-body">
        <Container>
          <div className="tb-faq-tabs" role="tablist">
            {CATS.map((entry) => (
              <button
                key={entry.id}
                type="button"
                className={cat === entry.id ? 'is-on' : ''}
                onClick={() => setCat(entry.id)}
              >
                <StrokeIcon name={entry.icon} size={14} />
                {entry.label}
              </button>
            ))}
          </div>
          <div className="tb-faq-grid">
            <div>
              {visible.length === 0 ? (
                <p className="tb-faq-empty">No questions match that search. Try a category above, or contact the team.</p>
              ) : visible.map((item) => {
                const isOpen = open === item.num
                return (
                  <article key={item.num} className={isOpen ? 'is-open' : ''}>
                    <button type="button" aria-expanded={isOpen} onClick={() => setOpen(isOpen ? '' : item.num)}>
                      <b>{item.num}</b>
                      <span>
                        <small>{item.label}</small>
                        {item.q}
                      </span>
                      <i><StrokeIcon name={isOpen ? 'minus' : 'plus'} size={16} /></i>
                    </button>
                    {isOpen && (
                      <div>
                        <p>{item.a}</p>
                        {item.link && (
                          <Link to={item.link.href}>{item.link.label} <StrokeIcon name="arrow" size={14} /></Link>
                        )}
                      </div>
                    )}
                  </article>
                )
              })}
              <div className="tb-faq-more">
                <span>Didn’t find it? Try a category above or</span>
                <button type="button" className="tb-btn ghost sm" onClick={() => setMore(true)}>
                  Show more questions <StrokeIcon name="arrow" size={16} />
                </button>
              </div>
            </div>
            <aside>
              <div className="tb-faq-human">
                <span><StrokeIcon name="headset" size={24} /></span>
                <h2>Need a <em>human</em>?</h2>
                <p>Our local team in Reykjanesbær answers every day, Mon–Sun 08–22.</p>
                <a href="tel:+3547870707"><StrokeIcon name="phone" size={20} /><span><small>Call us</small>+354 787 0707</span></a>
                <a href="mailto:info@myterrabook.com"><StrokeIcon name="mail" size={20} /><span><small>Email us</small>info@myterrabook.com</span></a>
                <div><StrokeIcon name="clock" size={20} /><span><small>Opening hours</small>Mon–Sun 08–22</span></div>
                <Link to="/contact" className="tb-btn gold">Contact us <StrokeIcon name="arrow" size={18} /></Link>
              </div>
              <div className="tb-faq-guide">
                <span><StrokeIcon name="book" size={20} /></span>
                <h3>Still planning your route?</h3>
                <p>Read our guides on routes, driving and packing in Iceland.</p>
                <Link to="/good-to-know">Go to Good to Know <StrokeIcon name="arrow" size={14} /></Link>
              </div>
            </aside>
          </div>
        </Container>
      </section>
    </div>
  )
}
