import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { Link } from 'react-router-dom'
import SiteLogo from '../branding/SiteLogo'
import LangCurrencyMenu from './LangCurrencyMenu'
import useMediaQuery from '../../hooks/useMediaQuery'

function FooterLink({ href, children, className }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link to={href} className={className || undefined}>
        {children}
      </Link>
    )
  }
  return (
    <a href={href || '#'} className={className || undefined}>
      {children}
    </a>
  )
}

const BOOK_LINKS = [
  { label: 'Campervans', href: '/campervans' },
  { label: 'Cars', href: '/cars' },
  { label: 'Guesthouses', href: '/guesthouses' },
]

const COMPANY_LINKS = [
  { label: 'Good to Know', href: '/good-to-know' },
  { label: 'About us', href: '/about' },
  { label: 'FAQs', href: '/faq' },
  { label: 'Contact', href: '/contact' },
]

function findColumnLinks(columns, titles) {
  const col = columns.find((c) => titles.some((t) => c.title?.toLowerCase().includes(t)))
  return col?.links?.length ? col.links : null
}

const ACCOUNT_LINKS = [
  { label: 'Sign in', href: '/login' },
  { label: 'Create account', href: '/register' },
]

function FooterColumnToggle({ id, title, open, onToggle, children }) {
  return (
    <div className={`ftr-col ftr-col--${id}${open ? ' is-open' : ''}`}>
      <button
        type="button"
        className="ftr-col-toggle"
        aria-expanded={open}
        aria-controls={`ftr-panel-${id}`}
        id={`ftr-toggle-${id}`}
        onClick={onToggle}
      >
        <span>{title}</span>
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
          <path d="m6 9 6 6 6-6" />
        </svg>
      </button>
      <div className="ftr-col-panel" id={`ftr-panel-${id}`} role="region" aria-labelledby={`ftr-toggle-${id}`} aria-hidden={!open}>
        {children}
      </div>
    </div>
  )
}

export default function Footer({
  tagline,
  address,
  columns = [],
  copyright,
  legal = [],
  social = [],
  hostCtaLabel,
  hostCtaHref,
}) {
  const isMobile = useMediaQuery('(max-width: 768px)')
  const [openSections, setOpenSections] = useState({ book: true, company: false, account: false })

  const bookLinks = findColumnLinks(columns, ['book', 'menu']) ?? BOOK_LINKS
  const companyLinks = findColumnLinks(columns, ['company', 'pages', 'explore']) ?? COMPANY_LINKS
  const accountLinks = findColumnLinks(columns, ['account']) ?? ACCOUNT_LINKS
  const addressLine = address?.split('\n').filter(Boolean)[0]

  const toggleSection = useCallback((id) => {
    setOpenSections((current) => ({ ...current, [id]: !current[id] }))
  }, [])

  useEffect(() => {
    if (!isMobile) {
      setOpenSections({ book: true, company: true, account: true })
    } else {
      setOpenSections({ book: true, company: false, account: false })
    }
  }, [isMobile])

  const bookList = (
    <ul>
      {bookLinks.map((link) => (
        <li key={link.label}>
          <FooterLink href={link.href}>{link.label}</FooterLink>
        </li>
      ))}
    </ul>
  )

  const companyList = (
    <ul>
      {companyLinks.map((link) => (
        <li key={link.label}>
          <FooterLink href={link.href}>
            {link.label}
            {link.badge && <span className="new-pill">{link.badge}</span>}
          </FooterLink>
        </li>
      ))}
    </ul>
  )

  const accountBlock = (
    <>
      <ul className="ftr-host-links">
        {accountLinks.map((link) => (
          <li key={link.label}>
            <FooterLink href={link.href}>{link.label}</FooterLink>
          </li>
        ))}
      </ul>
      {hostCtaLabel && (
        <FooterLink href={hostCtaHref || '/become-a-host'} className="ftr-host-cta">
          {hostCtaLabel}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </FooterLink>
      )}
    </>
  )

  return (
    <footer className="ftr">
      <div className="wrap">
        <div className="ftr-card">
          <div className="ftr-grid">
            <div className="ftr-brand">
              <SiteLogo variant="footer" className="logo" />
              {tagline && <p className="ftr-tag">{tagline}</p>}
              {social.length > 0 && (
                <div className="ftr-social">
                  {social.map((item) => (
                    <FooterLink key={item.label} href={item.href}>
                      <span aria-label={item.label}>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                          <circle cx="12" cy="12" r="9" />
                        </svg>
                      </span>
                    </FooterLink>
                  ))}
                </div>
              )}
            </div>

            {isMobile ? (
              <>
                <FooterColumnToggle
                  id="book"
                  title="Book"
                  open={openSections.book}
                  onToggle={() => toggleSection('book')}
                >
                  {bookList}
                </FooterColumnToggle>
                <FooterColumnToggle
                  id="company"
                  title="Company"
                  open={openSections.company}
                  onToggle={() => toggleSection('company')}
                >
                  {companyList}
                </FooterColumnToggle>
                <div className={`ftr-host ftr-col--account${openSections.account ? ' is-open' : ''}`}>
                  <button
                    type="button"
                    className="ftr-col-toggle"
                    aria-expanded={openSections.account}
                    aria-controls="ftr-panel-account"
                    id="ftr-toggle-account"
                    onClick={() => toggleSection('account')}
                  >
                    <span>Account</span>
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                      <path d="m6 9 6 6 6-6" />
                    </svg>
                  </button>
                  <div className="ftr-col-panel" id="ftr-panel-account" role="region" aria-labelledby="ftr-toggle-account" aria-hidden={!openSections.account}>
                    {accountBlock}
                  </div>
                </div>
              </>
            ) : (
              <>
                <div className="ftr-col">
                  <h4>Book</h4>
                  {bookList}
                </div>
                <div className="ftr-col">
                  <h4>Company</h4>
                  {companyList}
                </div>
                <div className="ftr-host">
                  <h4>Account</h4>
                  {accountBlock}
                </div>
              </>
            )}
          </div>

          <div className="ftr-meta">
            <div className="ftr-pay">
              <span className="pay-label">We accept</span>
              <div className="pay-row">
                <span className="pay visa">VISA</span>
                <span className="pay mc">
                  <i className="c1" />
                  <i className="c2" />
                </span>
                <span className="pay amex">AMEX</span>
                <span className="pay apay">Pay</span>
                <span className="pay gpay">G Pay</span>
                <span className="pay klarna">Klarna.</span>
              </div>
            </div>

            <LangCurrencyMenu variant="footer" />
          </div>

          <div className="ftr-bot">
            <div className="ftr-bot-left">
              <span className="copy">{copyright}</span>
              {addressLine && <span className="ftr-addr-inline">{addressLine}</span>}
            </div>
            {legal.length > 0 && (
              <div className="ftr-legal">
                {legal.map((item, index) => (
                  <span key={item.label}>
                    {index > 0 && <span className="dot" />}
                    <FooterLink href={item.href}>{item.label}</FooterLink>
                  </span>
                ))}
              </div>
            )}
          </div>
        </div>
      </div>
    </footer>
  )
}
