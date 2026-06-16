import { useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { api, resolveCmsImage } from '../../api'
import { useSiteLayout } from '../../context/SiteLayoutContext'
import { usePageContent } from '../../context/SiteContentContext'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import useSectionReveal from '../../hooks/useSectionReveal'
import { formatRentListingStats } from '../../utils/formatRentListingStats'
import { mergeHomepageData } from '../../utils/mergeHomepageData'

const FALLBACK_IMAGES = {
  hero: '/images/homepage/why-photo.jpg',
  camper: '/images/homepage/cardcamper.jpg',
  car: '/images/homepage/cardcar.jpg',
  house: '/images/homepage/cardhouse.jpg',
}

function parseParagraphs(html) {
  if (!html) return []
  const matches = html.match(/<p[^>]*>([\s\S]*?)<\/p>/gi)
  if (!matches) return []
  return matches.map((block) =>
    block
      .replace(/<\/?p[^>]*>/gi, '')
      .replace(/<[^>]+>/g, '')
      .trim(),
  ).filter(Boolean)
}

function PillarIcon({ type, image }) {
  if (image) {
    return <img src={image} alt="" className="about-pillar-icon-img" aria-hidden="true" />
  }
  const icons = {
    shield: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 22s8-4.5 8-11V5l-8-3-8 3v6c0 6.5 8 11 8 11Z" />
        <path d="m8.5 11.5 2.5 2.5L16 8.5" />
      </svg>
    ),
    price: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
      </svg>
    ),
    route: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.7" strokeLinecap="round" strokeLinejoin="round">
        <circle cx="6" cy="19" r="3" />
        <path d="M9 19h8.5a3.5 3.5 0 1 0 0-7h-11a3.5 3.5 0 1 1 0-7H15" />
        <circle cx="18" cy="5" r="3" />
      </svg>
    ),
  }
  return icons[type] || icons.shield
}

export default function AboutPageContent() {
  const { page } = usePageContent('about')
  const { siteData } = useSiteLayout()
  const priceFormatter = useFormatPrice()
  const [homepageData, setHomepageData] = useState(null)
  const storyRef = useRef(null)
  const statsRef = useRef(null)
  const valuesRef = useRef(null)
  const offerRef = useRef(null)

  useEffect(() => {
    api
      .get('/homepage')
      .then((res) => setHomepageData(res.data || null))
      .catch(() => setHomepageData(null))
  }, [])

  const rentSection = useMemo(
    () => mergeHomepageData({ ...siteData, ...homepageData }).rentSection ?? {},
    [siteData, homepageData],
  )

  const offerCards = useMemo(() => {
    const cards = rentSection.cards ?? []
    const fallbacks = [FALLBACK_IMAGES.camper, FALLBACK_IMAGES.car, FALLBACK_IMAGES.house]
    return cards.map((card, index) => ({
      href: card.href,
      image: resolveCmsImage(card.image, fallbacks[index % fallbacks.length]),
      label: card.name,
      tag: card.tagline,
      listingLabel: card.listingStats
        ? formatRentListingStats(card.listingStats, priceFormatter)
        : null,
    }))
  }, [rentSection.cards, priceFormatter])

  const hero = page.hero ?? {}
  const storySection = page.storySection ?? {}
  const valuesSection = page.valuesSection ?? {}
  const offeringsSection = page.offeringsSection ?? {}
  const stats = page.stats ?? []
  const pillars = page.pillars ?? []
  const cta = page.cta ?? {}
  const paragraphs = parseParagraphs(page.body)
  const chapterImages = [FALLBACK_IMAGES.camper, FALLBACK_IMAGES.car, FALLBACK_IMAGES.house]
  const heroImage = hero.image || FALLBACK_IMAGES.hero

  useSectionReveal(storyRef, { revealDoneMs: 1400, threshold: 0.1, watch: paragraphs.length > 0 })
  useSectionReveal(statsRef, { revealDoneMs: 1000, threshold: 0.15, watch: stats.length > 0 })
  useSectionReveal(valuesRef, { revealDoneMs: 1200, threshold: 0.12, watch: pillars.length > 0 })
  useSectionReveal(offerRef, { revealDoneMs: 1200, threshold: 0.12, watch: offerCards.length > 0 })

  useEffect(() => {
    const root = storyRef.current
    if (!root || !paragraphs.length) return undefined

    const chapters = root.querySelectorAll('.about-chapter')
    if (!chapters.length) return undefined

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches
    if (reduceMotion) {
      chapters.forEach((chapter) => chapter.classList.add('is-revealed'))
      return undefined
    }

    const reveal = (chapter) => {
      chapter.classList.add('is-revealed')
    }

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            reveal(entry.target)
            io.unobserve(entry.target)
          }
        })
      },
      { threshold: 0.22, rootMargin: '0px 0px -8% 0px' },
    )

    chapters.forEach((chapter) => {
      const rect = chapter.getBoundingClientRect()
      const vh = window.innerHeight || document.documentElement.clientHeight
      if (rect.top < vh * 0.9 && rect.bottom > 0) {
        reveal(chapter)
      } else {
        io.observe(chapter)
      }
    })

    return () => io.disconnect()
  }, [paragraphs.length])

  return (
    <div className="content-page about-page">
      <section className="about-hero">
        <div className="about-hero-bg" aria-hidden="true">
          <div className="about-hero-aurora" />
          <div className="about-hero-topo" />
        </div>
        <div className="wrap about-hero-grid">
          <div className="about-hero-copy">
            <h1>{hero.title}</h1>
            {hero.lead && <p className="about-lead">{hero.lead}</p>}
            {(hero.primaryLabel || hero.secondaryLabel) && (
              <div className="about-hero-actions">
                {hero.primaryLabel && (
                  <Link to={hero.primaryHref ?? '/contact'} className="about-btn about-btn--primary">
                    {hero.primaryLabel}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </Link>
                )}
                {hero.secondaryLabel && (
                  <Link to={hero.secondaryHref ?? '/become-a-host'} className="about-btn about-btn--ghost">
                    {hero.secondaryLabel}
                  </Link>
                )}
              </div>
            )}
          </div>
          <div className="about-hero-visual">
            <div className="about-hero-frame">
              <img src={heroImage} alt="Iceland landscape seen from the road" />
            </div>
            {(hero.pinTitle || hero.pinSubtitle) && (
              <div className="about-hero-pin">
                <span className="about-hero-pin-dot" aria-hidden="true" />
                <div>
                  {hero.pinTitle && <strong>{hero.pinTitle}</strong>}
                  {hero.pinSubtitle && <span>{hero.pinSubtitle}</span>}
                </div>
              </div>
            )}
          </div>
        </div>
      </section>

      <section ref={statsRef} className="about-stats" aria-label="Key figures">
        <div className="wrap">
          <div className="about-stats-grid">
            {stats.map((stat, index) => (
              <div
                className="about-stat about-rise"
                key={stat.label}
                style={{ '--d': `${0.04 + index * 0.07}s` }}
              >
                <div className="about-stat-value">{stat.value}</div>
                <div className="about-stat-label">{stat.label}</div>
                <div className="about-stat-sub">{stat.sub}</div>
              </div>
            ))}
          </div>
        </div>
      </section>

      {paragraphs.length > 0 && (
        <section ref={storyRef} className="about-story">
          <div className="wrap">
            {storySection.heading && (
              <header className="about-section-head about-rise" style={{ '--d': '0s' }}>
                <h2>{storySection.heading}</h2>
              </header>
            )}
            <div className="about-chapters">
              {paragraphs.map((text, index) => (
                <article
                  key={text.slice(0, 24)}
                  className={`about-chapter about-chapter--${index % 2 === 0 ? 'left' : 'right'}`}
                >
                  <div className="about-chapter-media">
                    <img
                      src={chapterImages[index % chapterImages.length]}
                      alt=""
                      loading="lazy"
                    />
                  </div>
                  <div className="about-chapter-body">
                    <p>{text}</p>
                  </div>
                </article>
              ))}
            </div>
          </div>
        </section>
      )}

      <section ref={valuesRef} className="about-values">
        <div className="wrap">
          <header className="about-section-head about-rise" style={{ '--d': '0s' }}>
            {valuesSection.tag && <span className="about-section-tag">{valuesSection.tag}</span>}
            {valuesSection.heading && <h2>{valuesSection.heading}</h2>}
          </header>
          <div className="about-pillars">
            {pillars.map((pillar, index) => (
              <div
                key={pillar.title}
                className="about-pillar about-rise"
                style={{ '--d': `${0.06 + index * 0.08}s` }}
              >
                <span className="about-pillar-icon">
                  <PillarIcon type={pillar.icon} image={pillar.iconImage} />
                </span>
                <h3>{pillar.title}</h3>
                <p>{pillar.text}</p>
              </div>
            ))}
          </div>
        </div>
      </section>

      <section ref={offerRef} className="about-offerings">
        <div className="wrap">
          <header className="about-section-head about-rise" style={{ '--d': '0s' }}>
            {offeringsSection.tag && <span className="about-section-tag">{offeringsSection.tag}</span>}
            {offeringsSection.heading && <h2>{offeringsSection.heading}</h2>}
          </header>
          <div className="about-offer-grid">
            {offerCards.map((item, index) => (
              <Link
                key={item.href || item.label}
                to={item.href}
                className="about-offer-card about-rise"
                style={{ '--d': `${0.06 + index * 0.08}s` }}
              >
                <div className="about-offer-media">
                  <img src={item.image} alt="" loading="lazy" />
                </div>
                <div className="about-offer-body">
                  {item.listingLabel && (
                    <span className="about-offer-listings">{item.listingLabel}</span>
                  )}
                  <h3>{item.label}</h3>
                  {item.tag && <span className="about-offer-tag">{item.tag}</span>}
                  <span className="about-offer-link">
                    Browse listings
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </span>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>

      <section className="about-cta">
        <div className="wrap">
          <div className="about-cta-panel">
            <div className="about-cta-copy">
              <h2>{cta.title ?? "Planning a trip? We're in Reykjavík when you need us."}</h2>
              {cta.subtitle && <p>{cta.subtitle}</p>}
            </div>
            <div className="about-cta-actions">
              <Link to={cta.primaryHref ?? '/contact'} className="about-btn about-btn--light">
                {cta.primaryLabel ?? 'Contact us'}
              </Link>
              <Link to={cta.secondaryHref ?? '/faq'} className="about-btn about-btn--outline-light">
                {cta.secondaryLabel ?? 'Read FAQs'}
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
