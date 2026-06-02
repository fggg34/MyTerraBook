import { useState } from 'react'
import { Link } from 'react-router-dom'

function GlobeIcon() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
      <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="1.8" />
      <path d="M3 12h18M12 3c2.5 2.8 4 6 4 9s-1.5 6.2-4 9M12 3c-2.5 2.8-4 6-4 9s1.5 6.2 4 9" stroke="currentColor" strokeWidth="1.8" />
    </svg>
  )
}

export default function Header({
  navLinks = [],
  ctaLabel,
  ctaHref,
  langLabel,
  currencyLabel,
  signInLabel,
  signInHref,
}) {
  const [mobileOpen, setMobileOpen] = useState(false)

  const renderLink = (link) => {
    const isExternal = link.href?.startsWith('http') || link.href?.startsWith('#')
    if (isExternal) {
      return (
        <a key={link.label} href={link.href}>
          {link.label}
        </a>
      )
    }
    return (
      <Link key={link.label} to={link.href || '/'}>
        {link.label}
      </Link>
    )
  }

  return (
    <header className="hp-header">
      <div className="homepage-wrap hp-header-inner">
        <Link to="/" className="hp-logo">
          <span className="hp-logo-icon">
            <GlobeIcon />
          </span>
          <span>
            My<span className="green">Terra</span>Book
          </span>
        </Link>

        <nav className="hp-nav" aria-label="Main">
          {navLinks.map(renderLink)}
        </nav>

        <div className="hp-header-actions">
          <button type="button" className="hp-lang-btn" aria-label="Language and currency">
            <GlobeIcon />
            <span>{langLabel}</span>
            <span className="hp-lang-divider" />
            <span>{currencyLabel}</span>
          </button>
          {ctaLabel && (
            <a href={ctaHref || '#'} className="hp-btn-host">
              {ctaLabel}
            </a>
          )}
          {signInLabel && (
            signInHref?.startsWith('/') ? (
              <Link to={signInHref} className="hp-btn-signin">
                {signInLabel}
              </Link>
            ) : (
              <a href={signInHref || '/login'} className="hp-btn-signin">
                {signInLabel}
              </a>
            )
          )}
          <button
            type="button"
            className="hp-hamburger"
            aria-label="Open menu"
            aria-expanded={mobileOpen}
            onClick={() => setMobileOpen((v) => !v)}
          >
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none">
              <path d="M4 7h16M4 12h16M4 17h16" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </button>
        </div>
      </div>

      <div className={`hp-mobile-menu ${mobileOpen ? 'open' : ''}`}>
        {navLinks.map(renderLink)}
        {ctaLabel && (
          <a href={ctaHref || '#'} className="hp-btn-host" style={{ width: 'fit-content' }}>
            {ctaLabel}
          </a>
        )}
      </div>
    </header>
  )
}
