import { useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import { usePageContent } from '../../context/SiteContentContext'
import useSectionReveal from '../../hooks/useSectionReveal'

function QuickIcon({ type, image }) {
  if (image) {
    return <img src={image} alt="" className="faq-quick-icon-img" aria-hidden="true" />
  }
  const icons = {
    chat: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4Z" />
      </svg>
    ),
    book: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
        <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2Z" />
      </svg>
    ),
    home: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
        <path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2Z" />
        <path d="M9 22V12h6v10" />
      </svg>
    ),
  }
  return icons[type] || icons.chat
}

function normalizeCategoryNums(nums) {
  if (Array.isArray(nums)) {
    return nums
  }

  if (typeof nums === 'string' && nums.trim()) {
    return nums.split(/[,\s]+/).map((value) => value.trim()).filter(Boolean)
  }

  return undefined
}

export default function FaqPageContent() {
  const { page } = usePageContent('faq')
  const hero = page.hero ?? {}
  const helpCard = page.helpCard ?? {}
  const emptyState = page.emptyState ?? {}
  const items = page.items || []
  const categories = [
    { id: 'all', label: 'All questions' },
    ...(page.categories ?? []).map((category) => ({
      ...category,
      nums: normalizeCategoryNums(category.nums),
    })),
  ]
  const quickLinks = (page.quickLinks ?? []).map((link, index) => ({
    ...link,
    icon: link.icon ?? ['chat', 'book', 'home'][index] ?? 'chat',
  }))
  const cta = page.cta ?? {}
  const phone = helpCard.phone ?? page.phone
  const email = helpCard.email ?? page.email
  const mainRef = useRef(null)

  const defaultOpen = items.findIndex((item) => item.open)
  const [openIndex, setOpenIndex] = useState(defaultOpen >= 0 ? defaultOpen : 0)
  const [activeCategory, setActiveCategory] = useState('all')

  useSectionReveal(mainRef, { revealDoneMs: 1400, threshold: 0.08 })

  const filteredItems = useMemo(() => {
    const category = categories.find((entry) => entry.id === activeCategory)

    return items.filter((item) => (
      activeCategory === 'all' ||
      !category?.nums ||
      category.nums.includes(item.num)
    ))
  }, [items, activeCategory])

  const handleToggle = (index) => {
    setOpenIndex((current) => (current === index ? -1 : index))
  }

  const handleCategoryChange = (categoryId) => {
    setActiveCategory(categoryId)
    setOpenIndex(0)
  }

  return (
    <div className="content-page faq-page">
      <section className="faq-hero">
        <div className="faq-hero-bg" aria-hidden="true">
          <div className="faq-hero-aurora" />
          <div className="faq-hero-grid-lines" />
        </div>
        <div className="wrap faq-hero-inner">
          <h1>{hero.title}</h1>
          {hero.lead && <p className="faq-hero-lead">{hero.lead}</p>}
        </div>
      </section>

      <section ref={mainRef} className="faq-main">
        <div className="wrap">
          <div className="faq-filters faq-rise" style={{ '--d': '0s' }}>
            {categories.map((category) => (
              <button
                key={category.id}
                type="button"
                className={`faq-filter ${activeCategory === category.id ? 'is-active' : ''}`}
                onClick={() => handleCategoryChange(category.id)}
              >
                {category.label}
              </button>
            ))}
          </div>

          <div className="faq-layout">
            <aside className="faq-sidebar faq-rise" style={{ '--d': '0.06s' }}>
              <div className="faq-help-card">
                {helpCard.tag && <span className="faq-help-tag">{helpCard.tag}</span>}
                {helpCard.title && <h2>{helpCard.title}</h2>}
                {helpCard.body && <p>{helpCard.body}</p>}

                <div className="faq-help-contacts">
                  {phone && (
                    <a className="faq-help-contact" href={`tel:${phone.replace(/\s/g, '')}`}>
                      <span className="faq-help-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                          <path d="M4 5.5C4 4.7 4.7 4 5.5 4h2.8c.6 0 1.2.4 1.4 1l1.2 3.2c.2.5 0 1.1-.4 1.5L8.8 11.2a13 13 0 0 0 5.6 5.6l1.5-1.7c.4-.4 1-.6 1.5-.4l3.2 1.2c.6.2 1 .8 1 1.4v2.8c0 .8-.7 1.5-1.5 1.5C10.3 21.6 4 15.3 4 5.5Z" />
                        </svg>
                      </span>
                      <span>
                        <strong>Call us</strong>
                        <span>{phone}</span>
                      </span>
                    </a>
                  )}
                  {email && (
                    <a className="faq-help-contact" href={`mailto:${email}`}>
                      <span className="faq-help-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                          <rect x="3" y="5" width="18" height="14" rx="2.5" />
                          <path d="m4 7 8 6 8-6" />
                        </svg>
                      </span>
                      <span>
                        <strong>Email</strong>
                        <span>{email}</span>
                      </span>
                    </a>
                  )}
                </div>

                {helpCard.buttonLabel && (
                  <Link to={helpCard.buttonHref ?? '/contact'} className="faq-help-btn">
                    {helpCard.buttonLabel}
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </Link>
                )}
              </div>

              <nav className="faq-quick-links" aria-label="Related pages">
                {quickLinks.map((link) => (
                  <Link key={link.href} to={link.href} className="faq-quick-link">
                    <span className="faq-quick-icon" aria-hidden="true">
                      <QuickIcon type={link.icon} image={link.iconImage} />
                    </span>
                    {link.label}
                    <svg className="faq-quick-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                      <path d="M5 12h14M13 6l6 6-6 6" />
                    </svg>
                  </Link>
                ))}
              </nav>
            </aside>

            <div className="faq-accordion-wrap">
              {filteredItems.length === 0 ? (
                <div className="faq-empty faq-rise" style={{ '--d': '0.1s' }}>
                  <h2>{emptyState.title ?? 'No matches found'}</h2>
                  <p>{emptyState.body ?? 'Browse all questions or contact our team for anything not listed here.'}</p>
                  <button type="button" className="faq-empty-btn" onClick={() => setActiveCategory('all')}>
                    {emptyState.buttonLabel ?? 'Show all questions'}
                  </button>
                </div>
              ) : (
                <div className="faq-accordion" role="list">
                  {filteredItems.map((item, index) => {
                    const isOpen = openIndex === index
                    return (
                      <article
                        key={item.num || item.question}
                        className={`faq-accordion-item ${isOpen ? 'is-open' : ''} faq-rise`}
                        style={{ '--d': `${0.08 + index * 0.05}s` }}
                        role="listitem"
                      >
                        <button
                          className="faq-accordion-trigger"
                          type="button"
                          aria-expanded={isOpen}
                          onClick={() => handleToggle(index)}
                        >
                          <span className="faq-accordion-num">{item.num}</span>
                          <span className="faq-accordion-question">{item.question}</span>
                          <span className="faq-accordion-icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                              <path d="M12 5v14M5 12h14" />
                            </svg>
                          </span>
                        </button>
                        <div className="faq-accordion-panel" hidden={!isOpen}>
                          <div className="faq-accordion-answer">{item.answer}</div>
                        </div>
                      </article>
                    )
                  })}
                </div>
              )}
            </div>
          </div>
        </div>
      </section>

      <section className="faq-cta">
        <div className="wrap">
          <div className="faq-cta-panel faq-rise" style={{ '--d': '0s' }}>
            <div className="faq-cta-copy">
              <h2>{cta.title ?? 'Still planning the trip?'}</h2>
              {cta.subtitle && <p>{cta.subtitle}</p>}
            </div>
            <div className="faq-cta-actions">
              <Link to={cta.primaryHref ?? '/good-to-know'} className="faq-cta-btn faq-cta-btn--light">
                {cta.primaryLabel ?? 'Good to Know'}
              </Link>
              <Link to={cta.secondaryHref ?? '/contact'} className="faq-cta-btn faq-cta-btn--outline">
                {cta.secondaryLabel ?? 'Contact us'}
              </Link>
            </div>
          </div>
        </div>
      </section>
    </div>
  )
}
