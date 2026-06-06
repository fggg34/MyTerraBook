import { useState } from 'react'
import { Link } from 'react-router-dom'
import SiteLogo from '../branding/SiteLogo'
import LangCurrencyMenu from './LangCurrencyMenu'

function NavLink({ href, children, onClick, className = '' }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link className={className || undefined} to={href} onClick={onClick}>
        {children}
      </Link>
    )
  }
  return (
    <a className={className || undefined} href={href || '#'} onClick={onClick}>
      {children}
    </a>
  )
}

function navLinkClass(href) {
  if (!href || href.startsWith('/')) return ''
  return 'nav-collapsible'
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

  const closeMobile = () => setMobileOpen(false)

  return (
    <header className="nav">
      <div className="wrap">
        <SiteLogo variant="header" className="logo" />

        <nav className="main" aria-label="Main">
          {navLinks.map((link) => (
            <NavLink key={link.label} href={link.href} className={navLinkClass(link.href)}>
              {link.label}
            </NavLink>
          ))}
          <span id="headerSearchSlot" />
        </nav>

        <div className="nav-right">
          <LangCurrencyMenu langLabel={langLabel} currencyLabel={currencyLabel} />
          {ctaLabel &&
            (ctaHref?.startsWith('/') ? (
              <Link className="host" to={ctaHref}>
                {ctaLabel}
              </Link>
            ) : (
              <button className="host" type="button" onClick={() => (window.location.href = ctaHref || '#host')}>
                {ctaLabel}
              </button>
            ))}
          {signInLabel &&
            (signInHref?.startsWith('/') ? (
              <Link className="signin" to={signInHref}>
                {signInLabel}
              </Link>
            ) : (
              <button className="signin" type="button" onClick={() => (window.location.href = signInHref || '/login')}>
                {signInLabel}
              </button>
            ))}
          <button
            className="hamburger"
            type="button"
            aria-label="Menu"
            aria-expanded={mobileOpen}
            onClick={() => setMobileOpen((v) => !v)}
          >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
              <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
          </button>
        </div>
      </div>

      <div className={`mobile-menu ${mobileOpen ? 'open' : ''}`} id="mobileMenu">
        {navLinks.map((link) => (
          <NavLink key={link.label} href={link.href} onClick={closeMobile}>
            {link.label}
          </NavLink>
        ))}
        {ctaLabel &&
          (ctaHref?.startsWith('/') ? (
            <Link to={ctaHref} onClick={closeMobile}>
              {ctaLabel}
            </Link>
          ) : (
            <a href={ctaHref || '#host'} onClick={closeMobile}>
              {ctaLabel}
            </a>
          ))}
      </div>
    </header>
  )
}
