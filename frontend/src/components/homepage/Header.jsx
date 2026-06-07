import { useEffect, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import SiteLogo from '../branding/SiteLogo'
import { getPostLoginPath, normalizeUserRole, useAuth } from '../../context/AuthContext'
import LangCurrencyMenu from './LangCurrencyMenu'

function getDashboardLabel(role) {
  if (role === 'host') return 'Host panel'
  if (role === 'admin') return 'Admin'
  return 'My account'
}

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

  const role = normalizeUserRole(user)
  const dashboardPath = user ? getPostLoginPath(user) : null
  const dashboardLabel = getDashboardLabel(role)
  const showHostCta = !user || (role !== 'host' && role !== 'admin')
  const userInitial = user?.name?.charAt(0)?.toUpperCase() || '?'

  const closeMobile = () => setMobileOpen(false)

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
                aria-expanded={userMenuOpen}
                aria-haspopup="menu"
                onClick={() => setUserMenuOpen((open) => !open)}
              >
                <span className="user-avatar" aria-hidden>
                  {userInitial}
                </span>
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
            signInLabel &&
            (signInHref?.startsWith('/') ? (
              <Link className="signin" to={signInHref}>
                {signInLabel}
              </Link>
            ) : (
              <button className="signin" type="button" onClick={() => (window.location.href = signInHref || '/login')}>
                {signInLabel}
              </button>
            ))
          )}
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
        {showHostCta && ctaLabel &&
          (ctaHref?.startsWith('/') ? (
            <Link to={ctaHref} onClick={closeMobile}>
              {ctaLabel}
            </Link>
          ) : (
            <a href={ctaHref || '#host'} onClick={closeMobile}>
              {ctaLabel}
            </a>
          ))}
        {user ? (
          <>
            <Link to={dashboardPath} onClick={closeMobile}>
              {dashboardLabel}
            </Link>
            <button type="button" className="user-menu-logout-mobile" onClick={handleLogout}>
              Log out
            </button>
          </>
        ) : (
          signInLabel &&
          (signInHref?.startsWith('/') ? (
            <Link to={signInHref} onClick={closeMobile}>
              {signInLabel}
            </Link>
          ) : (
            <a href={signInHref || '/login'} onClick={closeMobile}>
              {signInLabel}
            </a>
          ))
        )}
      </div>
    </header>
  )
}
