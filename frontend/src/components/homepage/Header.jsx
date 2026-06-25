import { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { Link, useNavigate } from 'react-router-dom'
import SiteLogo from '../branding/SiteLogo'
import UserAvatar from '../account/UserAvatar'
import { getDashboardLabel, getPostLoginPath, normalizeUserRole, useAuth } from '../../context/AuthContext'
import useScrollLock from '../../hooks/useScrollLock'
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
  signInLabel,
  signInHref,
}) {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const [mobileOpen, setMobileOpen] = useState(false)
  const [userMenuOpen, setUserMenuOpen] = useState(false)
  const userMenuRef = useRef(null)
  const headerRef = useRef(null)
  const hamburgerRef = useRef(null)
  const mobileMenuScrollRef = useRef(null)

  const role = normalizeUserRole(user)
  const dashboardPath = user ? getPostLoginPath(user) : null
  const dashboardLabel = getDashboardLabel(role)
  const showHostCta = !user || (role !== 'host' && role !== 'admin')
  const mobileNavLinks = navLinks

  const closeMobile = () => {
    setMobileOpen(false)
    hamburgerRef.current?.blur()
  }

  useEffect(() => {
    document.body.classList.toggle('mobile-nav-open', mobileOpen)
    return () => document.body.classList.remove('mobile-nav-open')
  }, [mobileOpen])

  useScrollLock(mobileOpen)

  useEffect(() => {
    if (!mobileOpen) return undefined
    const resetScroll = requestAnimationFrame(() => {
      mobileMenuScrollRef.current?.scrollTo(0, 0)
    })
    return () => cancelAnimationFrame(resetScroll)
  }, [mobileOpen])

  useEffect(() => {
    if (!mobileOpen) return undefined
    const onKeyDown = (event) => {
      if (event.key === 'Escape') setMobileOpen(false)
    }
    document.addEventListener('keydown', onKeyDown)
    return () => document.removeEventListener('keydown', onKeyDown)
  }, [mobileOpen])

  useEffect(() => {
    if (!userMenuOpen) return undefined
    const onPointerDown = (event) => {
      if (!userMenuRef.current?.contains(event.target)) {
        setUserMenuOpen(false)
      }
    }
    document.addEventListener('mousedown', onPointerDown)
    return () => document.removeEventListener('mousedown', onPointerDown)
  }, [userMenuOpen])

  const handleLogout = async () => {
    await logout()
    setUserMenuOpen(false)
    closeMobile()
    navigate('/')
  }

  const mobileMenuLayer = (
    <>
      <button
        type="button"
        className="mobile-menu-backdrop open"
        aria-label="Close menu"
        onClick={closeMobile}
      />

      <div
        className="mobile-menu open"
        id="mobileMenu"
        role="dialog"
        aria-modal="true"
        aria-label="Navigation menu"
      >
        <div className="mobile-menu-scroll" ref={mobileMenuScrollRef}>
          <div className="mobile-menu-main">
            {mobileNavLinks.map((link) => (
              <NavLink key={link.label} href={link.href} onClick={closeMobile} className="mobile-menu-link">
                {link.label}
              </NavLink>
            ))}
          </div>
        </div>

        <div className="mobile-menu-bottom">
          {user ? (
            <>
              <div className="mobile-menu-account-head">
                <UserAvatar user={user} />
                <div className="mobile-menu-account-meta">
                  <span className="mobile-menu-account-name">{user.name}</span>
                  <span className="mobile-menu-account-label">{dashboardLabel}</span>
                </div>
              </div>
              <div className="mobile-menu-bottom-actions">
                <Link
                  className="mobile-menu-action mobile-menu-action--solid"
                  to={dashboardPath}
                  onClick={closeMobile}
                >
                  {dashboardLabel}
                </Link>
                <button type="button" className="mobile-menu-action mobile-menu-action--logout" onClick={handleLogout}>
                  Log out
                </button>
              </div>
            </>
          ) : (
            <div className="mobile-menu-bottom-actions">
              {(signInHref?.startsWith('/') ? (
                <Link className="mobile-menu-action mobile-menu-action--signin" to={signInHref || '/login'} onClick={closeMobile}>
                  {signInLabel || 'Sign in'}
                </Link>
              ) : (
                <a className="mobile-menu-action mobile-menu-action--signin" href={signInHref || '/login'} onClick={closeMobile}>
                  {signInLabel || 'Sign in'}
                </a>
              ))}
              <Link className="mobile-menu-action mobile-menu-action--solid" to="/register" onClick={closeMobile}>
                Create account
              </Link>
            </div>
          )}

          {showHostCta && ctaLabel &&
            (ctaHref?.startsWith('/') ? (
              <Link className="mobile-menu-action mobile-menu-action--host mobile-menu-action--full" to={ctaHref} onClick={closeMobile}>
                {ctaLabel}
              </Link>
            ) : (
              <a className="mobile-menu-action mobile-menu-action--host mobile-menu-action--full" href={ctaHref || '#host'} onClick={closeMobile}>
                {ctaLabel}
              </a>
            ))}

          <div className="mobile-menu-currency">
            <LangCurrencyMenu variant="mobile" />
          </div>
        </div>
      </div>
    </>
  )

  return (
    <header ref={headerRef} className={`nav${mobileOpen ? ' menu-open' : ''}`}>
      <div className="wrap nav-chrome">
        <div className="nav-mobile-left">
          <button
            ref={hamburgerRef}
            className="hamburger"
            type="button"
            aria-label={mobileOpen ? 'Close menu' : 'Open menu'}
            aria-expanded={mobileOpen}
            aria-controls="mobileMenu"
            onClick={() => setMobileOpen((v) => !v)}
          >
            {mobileOpen ? (
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
                <path d="M6 6l12 12M18 6 6 18" />
              </svg>
            ) : (
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round">
                <path d="M4 7h16M4 12h16M4 17h16" />
              </svg>
            )}
          </button>
        </div>

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
          <LangCurrencyMenu />
          {showHostCta && ctaLabel &&
            (ctaHref?.startsWith('/') ? (
              <Link className="host" to={ctaHref}>
                {ctaLabel}
              </Link>
            ) : (
              <button className="host" type="button" onClick={() => (window.location.href = ctaHref || '#host')}>
                {ctaLabel}
              </button>
            ))}
          {user ? (
            <div className="user-menu-wrap" ref={userMenuRef}>
              <button
                type="button"
                className={`user-menu-btn${userMenuOpen ? ' open' : ''}`}
                aria-label={`${user.name} account menu`}
                aria-expanded={userMenuOpen}
                aria-haspopup="menu"
                onClick={() => setUserMenuOpen((open) => !open)}
              >
                <UserAvatar user={user} />
                <span className="user-name">{user.name}</span>
                <svg className="user-caret" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </button>
              <div className={`user-menu-panel${userMenuOpen ? ' show' : ''}`} role="menu">
                <Link
                  to={dashboardPath}
                  className="user-menu-item"
                  role="menuitem"
                  onClick={() => setUserMenuOpen(false)}
                >
                  {dashboardLabel}
                </Link>
                <button type="button" className="user-menu-item user-menu-logout" role="menuitem" onClick={handleLogout}>
                  Log out
                </button>
              </div>
            </div>
          ) : (
            <>
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
              {signInHref?.startsWith('/') ? (
                <Link
                  className="nav-mobile-account-btn"
                  to={signInHref || '/login'}
                  aria-label={signInLabel || 'Sign in'}
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                    <path d="M20 21a8 8 0 0 0-16 0" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </Link>
              ) : (
                <a
                  className="nav-mobile-account-btn"
                  href={signInHref || '/login'}
                  aria-label={signInLabel || 'Sign in'}
                >
                  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
                    <path d="M20 21a8 8 0 0 0-16 0" />
                    <circle cx="12" cy="7" r="4" />
                  </svg>
                </a>
              )}
            </>
          )}
        </div>
      </div>

      {mobileOpen && createPortal(mobileMenuLayer, document.documentElement)}
    </header>
  )
}
