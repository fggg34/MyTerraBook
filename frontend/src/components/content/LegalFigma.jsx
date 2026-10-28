import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import Container from '../site/Container'
import StrokeIcon from './StrokeIcon'
import { LEGAL_PAGES, LEGAL_TABS } from './legalPages'
import '../../styles/frames.css'

const COOKIE_KEY = 'terrabook_cookie_consent'

function CookieTable() {
  const [analytics, setAnalytics] = useState(true)
  const [marketing, setMarketing] = useState(false)
  const [saved, setSaved] = useState('')

  useEffect(() => {
    try {
      const raw = localStorage.getItem('mtb_cookie_detail')
      if (!raw) return
      const parsed = JSON.parse(raw)
      setAnalytics(Boolean(parsed.analytics))
      setMarketing(Boolean(parsed.marketing))
    } catch {
      // keep the frame defaults
    }
  }, [])

  const write = (nextAnalytics, nextMarketing) => {
    const value = nextAnalytics || nextMarketing ? 'accepted' : 'rejected'
    try {
      localStorage.setItem('mtb_cookie_detail', JSON.stringify({ analytics: nextAnalytics, marketing: nextMarketing }))
      localStorage.setItem(COOKIE_KEY, value)
    } catch {
      // ignore
    }
    document.cookie = `${COOKIE_KEY}=${encodeURIComponent(value)};path=/;max-age=${60 * 60 * 24 * 365};SameSite=Lax`
    setAnalytics(nextAnalytics)
    setMarketing(nextMarketing)
    setSaved('Your cookie choice is saved on this device.')
  }

  return (
    <>
      <div className="tb-cookie-table">
        <header><span>CATEGORY</span><span>PURPOSE & EXAMPLES</span><span>STATUS</span></header>
        <div className="tb-cookie-row">
          <strong>Necessary <StrokeIcon name="lock" size={14} /></strong>
          <div><p>Keep you signed in, remember your search, dates and currency, secure checkout and prevent fraud.</p><small>session_id · csrf_token · mtb_currency</small></div>
          <label>Always on <span className="tb-switch"><input type="checkbox" checked disabled readOnly /><span /></span></label>
        </div>
        <div className="tb-cookie-row">
          <strong>Analytics</strong>
          <div><p>Help us understand which pages and filters are used so we can improve search and booking. Data is aggregated.</p><small>_ga · _gid (Google Analytics, 13 months)</small></div>
          <label>{analytics ? 'On' : 'Off'} <span className="tb-switch"><input type="checkbox" checked={analytics} onChange={(event) => setAnalytics(event.target.checked)} /><span /></span></label>
        </div>
        <div className="tb-cookie-row">
          <strong>Marketing</strong>
          <div><p>Measure ad campaigns and show relevant MyTerraBook offers on other sites and social media.</p><small>_fbp (Meta) · _gcl_au (Google Ads)</small></div>
          <label>{marketing ? 'On' : 'Off'} <span className="tb-switch"><input type="checkbox" checked={marketing} onChange={(event) => setMarketing(event.target.checked)} /><span /></span></label>
        </div>
      </div>
      <div className="tb-cookie-actions">
        <button type="button" className="tb-btn sm" onClick={() => write(analytics, marketing)}>Save preferences <StrokeIcon name="arrow" size={16} /></button>
        <button type="button" className="tb-btn ghost sm" onClick={() => write(true, true)}>Accept all <StrokeIcon name="arrow" size={16} /></button>
        <button type="button" className="tb-btn ghost sm" onClick={() => write(false, false)}>Reject non-essential <StrokeIcon name="arrow" size={16} /></button>
      </div>
      {saved && <p className="tb-saved">{saved}</p>}
    </>
  )
}

export default function LegalFigma({ slug }) {
  const page = LEGAL_PAGES[slug] || LEGAL_PAGES.terms
  const [active, setActive] = useState(page.sections[0]?.id || '')

  useEffect(() => {
    setActive(page.sections[0]?.id || '')
  }, [slug, page.sections])

  const jump = (id) => {
    setActive(id)
    document.getElementById(id)?.scrollIntoView({ behavior: 'smooth', block: 'start' })
  }

  return (
    <div className="tb-frame tb-legal">
      <section className="tb-legal-hero">
        <Container>
          <nav className="tb-crumbs">
            <Link to="/">Home</Link>
            <StrokeIcon name="chevron" size={12} />
            <span>Legal</span>
            <StrokeIcon name="chevron" size={12} />
            <strong>{page.crumb}</strong>
          </nav>
          <p className="tb-kicker">LEGAL</p>
          <h1>{page.title[0]}<em>{page.title[1]}</em>{page.title[2]}</h1>
          <div className="tb-legal-meta">
            <StrokeIcon name="calendar" size={16} />
            <b>Last updated 1 June 2026</b>
            <span>·</span>
            <span>MyTerraBook ehf.</span>
          </div>
          <p className="intro">{page.intro}</p>
        </Container>
      </section>
      <section className="tb-legal-body">
        <Container>
          <nav className="tb-legal-tabs">
            {LEGAL_TABS.map((tab) => (
              <Link key={tab.slug} to={`/${tab.slug}`} className={tab.slug === slug ? 'is-on' : ''}>{tab.label}</Link>
            ))}
          </nav>
          <div className="tb-legal-grid">
            <aside className="tb-toc">
              <p>ON THIS PAGE</p>
              {page.sections.filter((section) => section.title).map((section) => (
                <button key={section.id} type="button" className={active === section.id ? 'is-on' : ''} onClick={() => jump(section.id)}>
                  {section.num} {section.title}
                </button>
              ))}
              <div className="tools">
                <button type="button" onClick={() => window.print()}><StrokeIcon name="download" size={16} /> Download as PDF</button>
                <button type="button" onClick={() => window.print()}><StrokeIcon name="file" size={16} /> Print this page</button>
              </div>
            </aside>
            <article className="tb-legal-copy">
              <p className="lead">{page.lead}</p>
              {page.sections.map((section) => (
                <section key={section.id} id={section.id}>
                  {section.title && <h2><b>{section.num}</b> {section.title}</h2>}
                  {section.paragraphs?.map((paragraph) => <p key={paragraph}>{paragraph}</p>)}
                  {section.list && <ul>{section.list.map((item) => <li key={item}>{item}</li>)}</ul>}
                  {section.id === 'cats' && <CookieTable />}
                  {section.callout && (
                    <div className="tb-callout">
                      <i><StrokeIcon name={section.callout.icon} size={18} /></i>
                      <div>
                        <strong>{section.callout.title}</strong>
                        <p>{section.callout.body}</p>
                      </div>
                    </div>
                  )}
                </section>
              ))}
              <div className="tb-ask">
                <i><StrokeIcon name="mail" size={24} /></i>
                <div>
                  <h3>{page.ask.title}</h3>
                  <p>{page.ask.body}</p>
                </div>
                <a className="tb-btn sm" href="mailto:info@myterrabook.com">Email us</a>
              </div>
            </article>
          </div>
        </Container>
      </section>
    </div>
  )
}
