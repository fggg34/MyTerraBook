import { useCallback, useEffect, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import SiteLogo from '../branding/SiteLogo'
import { getDashboardLabel, getPostLoginPath, normalizeUserRole, useAuth } from '../../context/AuthContext'
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
  { label: 'Campsite Map', href: '/campsite-map' },
  { label: 'About us', href: '/about' },
  { label: 'FAQs', href: '/faq' },
  { label: 'Contact', href: '/contact' },
]

function FooterSocialIcon({ label }) {
  const normalized = label?.toLowerCase() ?? ''
  if (normalized.includes('facebook')) {
    return (
      <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
        <path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.7c0-.9.3-1.5 1.5-1.5h1.5V4.6c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.2H8v3h2.6V21h2.9Z" />
      </svg>
    )
  }
  if (normalized.includes('instagram')) {
    return (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
        <rect x="3" y="3" width="18" height="18" rx="5" />
        <circle cx="12" cy="12" r="4" />
        <circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none" />
      </svg>
    )
  }
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
      <circle cx="12" cy="12" r="9" />
    </svg>
  )
}

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

function FooterAccountBlock({
  user,
  dashboardPath,
  dashboardLabel,
  accountLinks,
  hostCtaLabel,
  hostCtaHref,
  showHostCta,
  onLogout,
}) {
  const signInLink = accountLinks.find((link) => /sign in|log in/i.test(link.label)) ?? accountLinks[0]
  const registerLink =
    accountLinks.find((link) => /create account|register|sign up/i.test(link.label)) ??
    accountLinks.find((link) => link !== signInLink)
  const userInitial = user?.name?.charAt(0)?.toUpperCase() || '?'

  return (
    <div className="ftr-account">
      <p className="ftr-account-label">Account</p>

      {user ? (
        <>
          <div className="ftr-account-user">
            <span className="user-avatar" aria-hidden="true">
              {userInitial}
            </span>
            <div className="ftr-account-user-meta">
              <span className="ftr-account-user-name">{user.name}</span>
              <span className="ftr-account-user-role">{dashboardLabel}</span>
            </div>
          </div>
          <div className="ftr-account-actions">
            <FooterLink href={dashboardPath} className="ftr-account-btn ftr-account-btn--solid">
              {dashboardLabel}
            </FooterLink>
            <button type="button" className="ftr-account-btn ftr-account-btn--logout" onClick={onLogout}>
              Log out
            </button>
          </div>
        </>
      ) : (
        <div className="ftr-account-actions">
          {signInLink && (
            <FooterLink href={signInLink.href} className="ftr-account-btn">
              {signInLink.label}
            </FooterLink>
          )}
          {registerLink && (
            <FooterLink href={registerLink.href} className="ftr-account-btn ftr-account-btn--solid">
              {registerLink.label}
            </FooterLink>
          )}
        </div>
      )}

      {showHostCta && hostCtaLabel && (
        <FooterLink href={hostCtaHref || '/become-a-host'} className="ftr-account-host">
          <span className="ftr-account-host-icon" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="12" cy="8" r="4" />
              <path d="M5 20c0-3.5 3.1-6 7-6s7 2.5 7 6" />
            </svg>
          </span>
          <span className="ftr-account-host-copy">
            <strong>{hostCtaLabel}</strong>
            <span>List your van or guesthouse</span>
          </span>
          <svg className="ftr-account-host-arrow" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </FooterLink>
      )}
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
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const isMobile = useMediaQuery('(max-width: 768px)')
  const [openSections, setOpenSections] = useState({ book: true, company: false })

  const role = normalizeUserRole(user)
  const dashboardPath = user ? getPostLoginPath(user) : null
  const dashboardLabel = getDashboardLabel(role)
  const showHostCta = !user || (role !== 'host' && role !== 'admin')

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  const bookLinks = findColumnLinks(columns, ['book', 'menu']) ?? BOOK_LINKS
  const companyLinks = findColumnLinks(columns, ['company', 'pages', 'explore']) ?? COMPANY_LINKS
  const accountLinks = findColumnLinks(columns, ['account']) ?? ACCOUNT_LINKS
  const addressLine = address?.split('\n').filter(Boolean)[0]

  const toggleSection = useCallback((id) => {
    setOpenSections((current) => ({ ...current, [id]: !current[id] }))
  }, [])

  useEffect(() => {
    if (!isMobile) {
      setOpenSections({ book: true, company: true })
    } else {
      setOpenSections({ book: true, company: false })
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

  const mobileAccountBlock = (
    <FooterAccountBlock
      user={user}
      dashboardPath={dashboardPath}
      dashboardLabel={dashboardLabel}
      accountLinks={accountLinks}
      hostCtaLabel={hostCtaLabel}
      hostCtaHref={hostCtaHref}
      showHostCta={showHostCta}
      onLogout={handleLogout}
    />
  )

  const desktopAccountBlock = user ? (
    <>
      <ul className="ftr-host-links">
        <li>
          <FooterLink href={dashboardPath}>{dashboardLabel}</FooterLink>
        </li>
        <li>
          <button type="button" className="ftr-host-logout" onClick={handleLogout}>
            Log out
          </button>
        </li>
      </ul>
      {showHostCta && hostCtaLabel && (
        <FooterLink href={hostCtaHref || '/become-a-host'} className="ftr-host-cta">
          {hostCtaLabel}
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <path d="M5 12h14M13 6l6 6-6 6" />
          </svg>
        </FooterLink>
      )}
    </>
  ) : (
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
                        <FooterSocialIcon label={item.label} />
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
                {mobileAccountBlock}
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
                  {desktopAccountBlock}
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

            {!isMobile && <LangCurrencyMenu variant="footer" />}
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
