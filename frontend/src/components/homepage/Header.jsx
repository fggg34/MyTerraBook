import { useState } from 'react'
import { Link } from 'react-router-dom'

function NavLink({ href, children, onClick }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return (
      <Link to={href} onClick={onClick}>
        {children}
      </Link>
    )
  }
  return (
    <a href={href || '#'} onClick={onClick}>
      {children}
    </a>
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

  const closeMobile = () => setMobileOpen(false)

  return (
    <header className="nav">
      <div className="wrap">
        <Link to="/" className="logo">
          <span className="mark">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
              <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" />
              <path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18" />
            </svg>
          </span>
          <span>
            My<span className="terra">Terra</span>Book
          </span>
        </Link>

        <nav className="main" aria-label="Main">
          {navLinks.map((link) => (
            <NavLink key={link.label} href={link.href}>
              {link.label}
            </NavLink>
          ))}
        </nav>

        <div className="nav-right">
          <button className="lang-cur" type="button" aria-label="Language and currency">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
              <circle cx="12" cy="12" r="9" />
              <path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18" />
            </svg>
            <span>{langLabel}</span>
            <span className="lc-div" />
            <span>{currencyLabel}</span>
          </button>
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
