import { useMemo, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import Container from '../components/site/Container'
import FigmaIcon from '../components/site/FigmaIcon'
import { usePicksListings, useStayListings } from '../hooks/useHomepageListings'

const ROUTES = {
  campervan: '/campervans',
  car: '/cars',
  guesthouse: '/guesthouses',
}

function shown(section) {
  return section && section.visible !== false
}

function scrollRow(ref, direction) {
  const node = ref.current
  if (!node) return
  const card = node.querySelector(':scope > *')
  const amount = (card?.getBoundingClientRect().width || 240) + 16
  node.scrollBy({ left: direction * amount, behavior: 'smooth' })
}

function Arrows({ onPrev, onNext }) {
  return (
    <div className="tb-arrow-pair">
      <button type="button" aria-label="Previous" onClick={onPrev}>
        <FigmaIcon name="arrow" size={18} />
      </button>
      <button type="button" aria-label="Next" onClick={onNext}>
        <FigmaIcon name="arrow" size={18} />
      </button>
    </div>
  )
}

function ListingCard({ item, stay = false }) {
  if (!item) return null
  return (
    <Link className={stay ? 'tb-card tb-stay-card' : 'tb-card'} to={item.href || '#'}>
      <img className="tb-card-photo" src={item.image || '/figma/cover.png'} alt={item.name || ''} />
      <div className="tb-card-body">
        <h3>{item.name}</h3>
        <p>
          {item.price ? <strong>{item.price}</strong> : null}
          {item.per ? ` / ${item.per}` : ''}
        </p>
      </div>
    </Link>
  )
}

export default function HomePage({ pageData = {} }) {
  const navigate = useNavigate()
  const cover = pageData.cover || {}
  const countries = pageData.countries || {}
  const roadSigns = pageData.roadSigns || {}
  const campers = pageData.popularCampervans || {}
  const seasons = pageData.seasons || {}
  const cars = pageData.popularCars || {}
  const how = pageData.howTicket || {}
  const stays = pageData.placesToStay || {}
  const guides = pageData.guides || {}
  const host = pageData.hostBanner || {}

  const { items } = usePicksListings()
  const { cards: stayCards } = useStayListings(8)
  const countryRef = useRef(null)
  const camperRef = useRef(null)
  const carRef = useRef(null)
  const stayRef = useRef(null)

  const tabs = cover.tabs?.length ? cover.tabs : []
  const [tab, setTab] = useState(tabs[0]?.id || 'campervan')
  const [panel, setPanel] = useState('')
  const [where, setWhere] = useState('')
  const [start, setStart] = useState('')
  const [end, setEnd] = useState('')
  const [adults, setAdults] = useState(2)
  const [countryTab, setCountryTab] = useState(countries.tabs?.[0] || 'All')
  const [stayTab, setStayTab] = useState(stays.tabs?.[0] || 'All')

  const countryCards = useMemo(() => {
    const cards = countries.cards || []
    if (!countryTab || countryTab === 'All') return cards
    return cards.filter((card) => card.region === countryTab)
  }, [countries.cards, countryTab])

  const stayList = useMemo(() => {
    const list = stayCards || []
    if (!stayTab || stayTab === 'All' || stayTab === 'Guesthouses') return list
    return list.filter((card) => String(card.badge || '').toLowerCase().includes(stayTab.toLowerCase().replace(/s$/, '')))
  }, [stayCards, stayTab])

  function search() {
    const params = new URLSearchParams()
    if (where) params.set('where', where)
    if (start) params.set('pickup_at', start)
    if (end) params.set('dropoff_at', end)
    if (adults) params.set('adults', String(adults))
    const query = params.toString()
    navigate(`${ROUTES[tab] || '/campervans'}${query ? `?${query}` : ''}`)
  }

  return (
    <main className="tb-home">
      {shown(cover) && (
        <section className="tb-cover" id="search">
          <img className="tb-cover-photo" src={cover.backgroundImage || '/figma/cover.png'} alt={cover.backgroundAlt || ''} />
          <div className="tb-cover-shade" />
          <div className="tb-cover-inner">
            <h1>
              {cover.heading}
              {cover.headingAccent ? <em>{cover.headingAccent}</em> : null}
            </h1>
            <div className="tb-search-tabs">
              {tabs.map((item) => (
                <button key={item.id} type="button" className={tab === item.id ? 'is-on' : ''} onClick={() => setTab(item.id)}>
                  {item.label}
                </button>
              ))}
            </div>
            <div className="tb-search-bar">
              <button type="button" className={where ? 'tb-search-field is-set' : 'tb-search-field'} onClick={() => setPanel(panel === 'where' ? '' : 'where')}>
                <small>{cover.whereLabel}</small>
                <strong>{where || cover.wherePlaceholder}</strong>
              </button>
              <button type="button" className={start ? 'tb-search-field is-set' : 'tb-search-field'} onClick={() => setPanel(panel === 'when' ? '' : 'when')}>
                <small>{cover.whenLabel}</small>
                <strong>{start && end ? `${start} – ${end}` : cover.whenPlaceholder}</strong>
              </button>
              <button type="button" className="tb-search-field is-set" onClick={() => setPanel(panel === 'who' ? '' : 'who')}>
                <small>{cover.whoLabel}</small>
                <strong>{adults} adults</strong>
              </button>
              <button type="button" className="tb-search-go" aria-label="Search" onClick={search}>
                <FigmaIcon name="search" size={20} />
              </button>
              {panel === 'where' && (
                <div className="tb-search-pop">
                  <label htmlFor="tb-where">{cover.whereLabel}</label>
                  <input id="tb-where" value={where} placeholder={cover.wherePlaceholder} onChange={(event) => setWhere(event.target.value)} />
                </div>
              )}
              {panel === 'when' && (
                <div className="tb-search-pop">
                  <label htmlFor="tb-start">Start</label>
                  <input id="tb-start" type="date" value={start} onChange={(event) => setStart(event.target.value)} />
                  <label htmlFor="tb-end">End</label>
                  <input id="tb-end" type="date" value={end} onChange={(event) => setEnd(event.target.value)} />
                </div>
              )}
              {panel === 'who' && (
                <div className="tb-search-pop">
                  <label htmlFor="tb-adults">{cover.whoLabel}</label>
                  <input id="tb-adults" type="number" min="1" max="16" value={adults} onChange={(event) => setAdults(Number(event.target.value) || 1)} />
                </div>
              )}
            </div>
          </div>
        </section>
      )}

      {shown(countries) && (
        <section className="tb-section">
          <Container>
            <div className="tb-section-head">
              <div>
                {countries.eyebrow ? <span className="tb-kicker">{countries.eyebrow}</span> : null}
                <h2>{countries.heading}</h2>
                {countries.subtitle ? <p className="tb-lead">{countries.subtitle}</p> : null}
              </div>
              <Arrows onPrev={() => scrollRow(countryRef, -1)} onNext={() => scrollRow(countryRef, 1)} />
            </div>
            <div className="tb-pills">
              {(countries.tabs || []).map((name) => (
                <button key={name} type="button" className={countryTab === name ? 'is-on' : ''} onClick={() => setCountryTab(name)}>{name}</button>
              ))}
            </div>
            <div className="tb-country-row" ref={countryRef}>
              {countryCards.map((card) => (
                <Link key={card.code || card.name} className="tb-country" to={card.href || '#'}>
                  <img src={card.image || '/figma/cover.png'} alt={card.imageAlt || card.name || ''} />
                  <span className="tb-country-shade" />
                  <span className="tb-country-top">
                    <span className="tb-country-code">{card.code}</span>
                    {card.live ? <span className="tb-live">Live</span> : null}
                  </span>
                  <span className="tb-country-info">
                    <h3>{card.name}</h3>
                    {card.live ? (
                      <span className="tb-country-stats">
                        <span>{card.campers}</span>
                        <span>{card.cars}</span>
                        <span>{card.stays}</span>
                      </span>
                    ) : null}
                    <p>{card.live ? card.listings : card.emptyCta || card.listings}</p>
                  </span>
                </Link>
              ))}
            </div>
          </Container>
        </section>
      )}

      {shown(roadSigns) && (
        <Container className="tb-signs" as="section">
          {(roadSigns.signs || []).map((sign) => (
            <span key={sign.label} style={{ display: 'contents' }}>
              <span className="tb-sign-line" />
              <span className="tb-sign"><b>{sign.label}</b><span>{sign.distance}</span></span>
            </span>
          ))}
          <span className="tb-sign-line" />
          <span className="tb-sign-end" />
        </Container>
      )}

      {shown(campers) && (
        <section className="tb-section">
          <Container>
            <div className="tb-section-head">
              <div>
                {campers.eyebrow ? <span className="tb-kicker">{campers.eyebrow}</span> : null}
                <h2>{campers.heading}</h2>
              </div>
              <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
                {campers.buttonLabel ? <Link className="tb-text-btn" to={campers.buttonHref || '/campervans'}>{campers.buttonLabel}</Link> : null}
                <Arrows onPrev={() => scrollRow(camperRef, -1)} onNext={() => scrollRow(camperRef, 1)} />
              </div>
            </div>
            <div className="tb-card-row" ref={camperRef}>
              {(items.camper || []).slice(0, 3).map((item) => <ListingCard key={item.id || item.name} item={item} />)}
            </div>
          </Container>
        </section>
      )}

      {shown(seasons) && (
        <section className="tb-seasons">
          <Container className="tb-seasons-row">
            <div className="tb-seasons-intro">
              {seasons.eyebrow ? <span className="tb-kicker">{seasons.eyebrow}</span> : null}
              <h2>{seasons.heading}</h2>
            </div>
            {(seasons.items || []).map((item) => (
              <article key={item.title} className="tb-season">
                <span className="tb-season-icon"><FigmaIcon name={item.icon} size={20} /></span>
                <div>
                  <strong>{item.title}<em>{item.months}</em></strong>
                  <span>{item.text}</span>
                </div>
              </article>
            ))}
          </Container>
        </section>
      )}

      {shown(cars) && (
        <section className="tb-section">
          <Container>
            <div className="tb-section-head">
              <div>
                {cars.eyebrow ? <span className="tb-kicker">{cars.eyebrow}</span> : null}
                <h2>{cars.heading}</h2>
              </div>
              <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
                {cars.buttonLabel ? <Link className="tb-text-btn" to={cars.buttonHref || '/cars'}>{cars.buttonLabel}</Link> : null}
                <Arrows onPrev={() => scrollRow(carRef, -1)} onNext={() => scrollRow(carRef, 1)} />
              </div>
            </div>
            <div className="tb-card-row" ref={carRef}>
              {(items.car || []).slice(0, 3).map((item) => <ListingCard key={item.id || item.name} item={item} />)}
            </div>
          </Container>
        </section>
      )}

      {shown(how) && (
        <section className="tb-section tb-how">
          <Container>
            <div className="tb-section-head">
              <div>
                {how.eyebrow ? <span className="tb-kicker">{how.eyebrow}</span> : null}
                <h2>{how.heading}</h2>
              </div>
            </div>
            <div className="tb-ticket">
              {(how.steps || []).map((step, index) => (
                <span key={step.num || step.title} style={{ display: 'contents' }}>
                  {index > 0 ? <span className="tb-perf" /> : null}
                  <article>
                    <div className="tb-ticket-top">
                      <span className="tb-step-badge">{String(index + 1).padStart(2, '0')}</span>
                      <span>{step.num}</span>
                    </div>
                    <h3>{step.title}</h3>
                    <p>{step.text}</p>
                  </article>
                </span>
              ))}
            </div>
          </Container>
        </section>
      )}

      {shown(stays) && (
        <section className="tb-section">
          <Container>
            <div className="tb-section-head">
              <div>
                {stays.eyebrow ? <span className="tb-kicker">{stays.eyebrow}</span> : null}
                <h2>{stays.heading}</h2>
                {stays.subtitle ? <p className="tb-lead">{stays.subtitle}</p> : null}
              </div>
              <div style={{ display: 'flex', gap: 16, alignItems: 'center' }}>
                {stays.buttonLabel ? <Link className="tb-text-btn" to={stays.buttonHref || '/guesthouses'}>{stays.buttonLabel}</Link> : null}
                <Arrows onPrev={() => scrollRow(stayRef, -1)} onNext={() => scrollRow(stayRef, 1)} />
              </div>
            </div>
            <div className="tb-pills">
              {(stays.tabs || []).map((name) => (
                <button key={name} type="button" className={stayTab === name ? 'is-on' : ''} onClick={() => setStayTab(name)}>{name}</button>
              ))}
            </div>
            <div className="tb-card-row" ref={stayRef}>
              {(stayList || []).slice(0, 4).map((item) => <ListingCard key={item.slug || item.id} item={item} stay />)}
            </div>
          </Container>
        </section>
      )}

      {shown(guides) && (
        <section className="tb-section">
          <Container>
            <div className="tb-section-head">
              <div>
                {guides.eyebrow ? <span className="tb-kicker">{guides.eyebrow}</span> : null}
                <h2>{guides.heading}</h2>
                {guides.subtitle ? <p className="tb-lead">{guides.subtitle}</p> : null}
              </div>
              {guides.buttonLabel ? <Link className="tb-text-btn" to={guides.buttonHref || '/good-to-know'}>{guides.buttonLabel}</Link> : null}
            </div>
            <div className="tb-guides">
              {(guides.items || []).map((item) => (
                <Link key={item.title} className="tb-guide" to={item.href || '/good-to-know'}>
                  <img src={item.image || '/figma/cover.png'} alt={item.imageAlt || item.title || ''} />
                  <div>
                    <span className="tb-guide-tag">{item.tag}</span>
                    <span>{item.minutes}</span>
                    <h3>{item.title}</h3>
                    <p>{item.text}</p>
                  </div>
                  <span className="tb-guide-go"><FigmaIcon name="arrow" size={18} /></span>
                </Link>
              ))}
            </div>
          </Container>
        </section>
      )}

      {shown(host) && (
        <section className="tb-host-wrap">
          <Container>
            <div className="tb-host">
              <div>
                {host.eyebrow ? <span className="tb-kicker">{host.eyebrow}</span> : null}
                <h2>{host.heading}</h2>
                {host.subtitle ? <p>{host.subtitle}</p> : null}
                {host.buttonLabel ? <Link className="tb-navy-btn" to={host.buttonHref || '/become-a-host'}>{host.buttonLabel}</Link> : null}
              </div>
              <img src={host.image || '/figma/cover.png'} alt={host.imageAlt || ''} />
            </div>
          </Container>
        </section>
      )}
    </main>
  )
}
