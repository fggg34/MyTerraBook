import { useEffect, useRef, useState } from 'react'
import { createPortal } from 'react-dom'
import { Link, useLocation, useNavigate } from 'react-router-dom'
import { ArrowRight, ChevronDown, ChevronRight, Clock, Mail, Menu, Phone, Search, X } from 'lucide-react'
import SiteLogo from '../branding/SiteLogo'
import UserAvatar from '../account/UserAvatar'
import { getDashboardLabel, getPostLoginPath, normalizeUserRole, useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import useScrollLock from '../../hooks/useScrollLock'
import LangCurrencyMenu from './LangCurrencyMenu'
import useDestinations from '../../hooks/useDestinations'
import CountryFlag from '../ui/CountryFlag'
import { destinationLandingPath } from '../../utils/destination'
import { cmsText } from '../../data/searchFormCopy'

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

function isDestinationsLink(link) {
  return /destination/i.test(link?.label || '')
}

function isVehiclesLink(link) {
  return /^vehicles$/i.test(link?.label || '')
}

const VEHICLE_LINKS = [
  { label: 'Campervans', href: '/campervans' },
  { label: 'Cars & 4x4', href: '/cars' },
]

export default function Header({
  navLinks = [],
  ctaLabel,
  ctaHref,
  signInLabel,
  signInHref,
}) {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const location = useLocation()
  const { page: globalPage } = usePageContent('global')
  const [mobileOpen, setMobileOpen] = useState(false)
  const [userMenuOpen, setUserMenuOpen] = useState(false)
  const [destinationsOpen, setDestinationsOpen] = useState(false)
  const [vehiclesOpen, setVehiclesOpen] = useState(false)
  const [mobileDestinationsOpen, setMobileDestinationsOpen] = useState(false)
  const { destinations, isPending: destinationsLoading } = useDestinations('campervan')
  const userMenuRef = useRef(null)
  const destinationsRef = useRef(null)
  const vehiclesRef = useRef(null)
  const headerRef = useRef(null)
  const hamburgerRef = useRef(null)
  const mobileMenuScrollRef = useRef(null)

  const role = normalizeUserRole(user)
  const dashboardPath = user ? getPostLoginPath(user) : null
  const dashboardLabel = getDashboardLabel(role)
  const showHostCta = !user || (role !== 'host' && role !== 'admin')
  const desktopLinks = navLinks
  const hasDestinationsItem = desktopLinks.some(isDestinationsLink)
  const phone = cmsText(globalPage.header?.supportPhone, globalPage.faqSection?.phone || '')
  const email = cmsText(globalPage.header?.supportEmail, globalPage.faqSection?.email || '')
  const hours = cmsText(globalPage.header?.supportHours, '')

  const mobileNavLinks = (() => {
    if (!navLinks.length) {
      return [
        { label: 'Plan a trip', href: '/#search' },
        ...VEHICLE_LINKS,
        { label: 'Guesthouses', href: '/guesthouses' },
        { label: 'Destinations', href: '/destinations' },
        { label: 'Guides', href: '/good-to-know' },
      ]
    }
    const expanded = []
    navLinks.forEach((link) => {
      if (isVehiclesLink(link)) {
        expanded.push(...VEHICLE_LINKS)
        return
      }
      expanded.push(link)
    })
    return expanded
  })()

  const closeMobile = () => {
    setMobileOpen(false)
    setMobileDestinationsOpen(false)
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
    if (!destinationsOpen) return undefined
    const onPointerDown = (event) => {
      if (!destinationsRef.current?.contains(event.target)) setDestinationsOpen(false)
    }
    const onKeyDown = (event) => {
      if (event.key === 'Escape') setDestinationsOpen(false)
    }
    document.addEventListener('mousedown', onPointerDown)
    document.addEventListener('keydown', onKeyDown)
    return () => {
      document.removeEventListener('mousedown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [destinationsOpen])

  useEffect(() => {
    if (!vehiclesOpen) return undefined
    const onPointerDown = (event) => {
      if (!vehiclesRef.current?.contains(event.target)) setVehiclesOpen(false)
    }
    const onKeyDown = (event) => {
      if (event.key === 'Escape') setVehiclesOpen(false)
    }
    document.addEventListener('mousedown', onPointerDown)
    document.addEventListener('keydown', onKeyDown)
    return () => {
      document.removeEventListener('mousedown', onPointerDown)
      document.removeEventListener('keydown', onKeyDown)
    }
  }, [vehiclesOpen])

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

  const openSearch = () => {
    if (location.pathname === '/') {
      document.getElementById('search')?.scrollIntoView({ behavior: 'smooth', block: 'center' })
      return
    }
    navigate('/campervans')
  }

  const hostCta = showHostCta && ctaLabel ? (
    ctaHref?.startsWith('/') ? (
      <Link className="host" to={ctaHref} onClick={closeMobile}>
        {ctaLabel}
        <ArrowRight size={16} aria-hidden />
      </Link>
    ) : (
      <a className="host" href={ctaHref || '#host'} onClick={closeMobile}>
        {ctaLabel}
        <ArrowRight size={16} aria-hidden />
      </a>
    )
  ) : null

  const signInControl = !user && signInLabel ? (
    signInHref?.startsWith('/') ? (
      <Link className="signin" to={signInHref || '/login'}>
        {signInLabel}
      </Link>
    ) : (
      <a className="signin" href={signInHref || '/login'}>
        {signInLabel}
      </a>
    )
  ) : null

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
        <div className="mobile-menu-toolbar">
          <SiteLogo variant="header" className="logo logo--on-dark" />
          <button type="button" className="mobile-menu-close" aria-label="Close menu" onClick={closeMobile}>
            <X size={20} />
          </button>
        </div>
        <div className="mobile-menu-scroll" ref={mobileMenuScrollRef}>
          <div className="mobile-menu-main">
            {mobileNavLinks.map((link) => {
              if (isDestinationsLink(link)) {
                return (
                  <div className="mobile-destinations" key="destinations">
                    <button
                      type="button"
                      className="mobile-menu-link"
                      onClick={() => setMobileDestinationsOpen((open) => !open)}
                      aria-expanded={mobileDestinationsOpen}
                    >
                      {link.label}
                      <ChevronRight size={18} aria-hidden />
                    </button>
                    {mobileDestinationsOpen && (
                      <div className="mobile-destination-list" aria-label="Available destinations">
                        {destinationsLoading ? (
                          <span className="mobile-destination-status">Loading destinations...</span>
                        ) : destinations.map(({ code, name }) => (
                          <Link
                            key={code}
                            className="mobile-destination-link"
                            to={destinationLandingPath({ name })}
                            onClick={closeMobile}
                          >
                            <CountryFlag code={code} decorative />
                            {name}
                          </Link>
                        ))}
                      </div>
                    )}
                  </div>
                )
              }
              return (
                <NavLink key={link.label} href={link.href} onClick={closeMobile} className="mobile-menu-link">
                  {link.label}
                  <ChevronRight size={18} aria-hidden />
                </NavLink>
              )
            })}
          </div>
        </div>

        <div className="mobile-menu-bottom">
          <div className="mobile-menu-currency">
            <LangCurrencyMenu variant="mobile" />
          </div>

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
              {showHostCta && ctaLabel && (
                ctaHref?.startsWith('/') ? (
                  <Link className="mobile-menu-action mobile-menu-action--host mobile-menu-action--full" to={ctaHref} onClick={closeMobile}>
                    {ctaLabel}
                    <ArrowRight size={16} aria-hidden />
                  </Link>
                ) : (
                  <a className="mobile-menu-action mobile-menu-action--host mobile-menu-action--full" href={ctaHref || '#host'} onClick={closeMobile}>
                    {ctaLabel}
                    <ArrowRight size={16} aria-hidden />
                  </a>
                )
              )}
              {signInHref?.startsWith('/') ? (
                <Link className="mobile-menu-action mobile-menu-action--signin" to={signInHref || '/login'} onClick={closeMobile}>
                  {signInLabel || 'Sign in'}
                  <ArrowRight size={16} aria-hidden />
                </Link>
              ) : (
                <a className="mobile-menu-action mobile-menu-action--signin" href={signInHref || '/login'} onClick={closeMobile}>
                  {signInLabel || 'Sign in'}
                  <ArrowRight size={16} aria-hidden />
                </a>
              )}
            </div>
          )}

          {(phone || email || hours) && (
            <div className="mobile-menu-contact">
              {phone && (
                <a href={`tel:${phone.replace(/\s/g, '')}`}>
                  <Phone size={15} aria-hidden />
                  {phone}
                </a>
              )}
              {email && (
                <a href={`mailto:${email}`}>
                  <Mail size={15} aria-hidden />
                  {email}
                </a>
              )}
              {hours && (
                <span className="hours">
                  <Clock size={14} aria-hidden />
                  {hours}
                </span>
              )}
            </div>
          )}
        </div>
      </div>
    </>
  )

  return (
    <header ref={headerRef} className={`nav${mobileOpen ? ' menu-open' : ''}`}>
      <div className="wrap nav-chrome">
        <SiteLogo variant="header" className="logo" />

        <nav className="main" aria-label="Main">
          {desktopLinks.map((link) => {
            if (isVehiclesLink(link)) {
              return (
                <div className="vehicles-menu destinations-menu" ref={vehiclesRef} key="vehicles">
                  <button
                    type="button"
                    className={vehiclesOpen ? 'vehicles-menu-toggle open' : 'vehicles-menu-toggle'}
                    aria-haspopup="menu"
                    aria-expanded={vehiclesOpen}
                    onClick={() => setVehiclesOpen((open) => !open)}
                  >
                    {link.label}
                    <ChevronDown size={14} aria-hidden />
                  </button>
                  {vehiclesOpen && (
                    <div className="destinations-menu-panel vehicles-menu-panel" role="menu" aria-label={link.label}>
                      {VEHICLE_LINKS.map((item) => (
                        <Link
                          key={item.href}
                          className="destinations-menu-item"
                          to={item.href}
                          role="menuitem"
                          onClick={() => setVehiclesOpen(false)}
                        >
                          <span className="destinations-menu-country">
                            <strong>{item.label}</strong>
                          </span>
                        </Link>
                      ))}
                    </div>
                  )}
                </div>
              )
            }
            if (isDestinationsLink(link)) {
              return (
                <div className="destinations-menu" ref={destinationsRef} key="destinations">
                  <button
                    type="button"
                    className={destinationsOpen ? 'destinations-menu-toggle open' : 'destinations-menu-toggle'}
                    aria-haspopup="menu"
                    aria-expanded={destinationsOpen}
                    onClick={() => setDestinationsOpen((open) => !open)}
                  >
                    {link.label}
                    <ChevronDown size={14} aria-hidden />
                  </button>
                  {destinationsOpen && (
                    <div className="destinations-menu-panel" role="menu" aria-label={link.label}>
                      <div className="destinations-menu-list">
                        {destinationsLoading ? (
                          <span className="destinations-menu-status">Loading destinations...</span>
                        ) : destinations.length ? (
                          destinations.map(({ code, name }) => (
                            <Link
                              key={code}
                              className="destinations-menu-item"
                              to={destinationLandingPath({ name })}
                              role="menuitem"
                              onClick={() => setDestinationsOpen(false)}
                            >
                              <span className="destinations-menu-flag"><CountryFlag code={code} decorative /></span>
                              <span className="destinations-menu-country">
                                <strong>{name}</strong>
                              </span>
                            </Link>
                          ))
                        ) : (
                          <span className="destinations-menu-status">No destinations available yet.</span>
                        )}
                      </div>
                    </div>
                  )}
                </div>
              )
            }
            return (
              <NavLink key={link.label} href={link.href} className={navLinkClass(link.href)}>
                {link.label}
              </NavLink>
            )
          })}
          {!hasDestinationsItem && (
            <div className="destinations-menu" ref={destinationsRef}>
              <button
                type="button"
                className={destinationsOpen ? 'destinations-menu-toggle open' : 'destinations-menu-toggle'}
                aria-haspopup="menu"
                aria-expanded={destinationsOpen}
                onClick={() => setDestinationsOpen((open) => !open)}
              >
                Destinations
                <ChevronDown size={14} aria-hidden />
              </button>
            </div>
          )}
          <span id="headerSearchSlot" />
        </nav>

        <div className="nav-right">
          {hostCta}
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
                <ChevronDown className="user-caret" size={16} aria-hidden />
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
            signInControl
          )}
          <LangCurrencyMenu />
          <button
            type="button"
            className="nav-search"
            aria-label="Search"
            onClick={openSearch}
          >
            <Search size={22} />
          </button>
          <button
            ref={hamburgerRef}
            className="hamburger"
            type="button"
            aria-label={mobileOpen ? 'Close menu' : 'Open menu'}
            aria-expanded={mobileOpen}
            aria-controls="mobileMenu"
            onClick={() => setMobileOpen((v) => !v)}
          >
            {mobileOpen ? <X size={20} /> : <Menu size={20} />}
          </button>
        </div>
      </div>

      {mobileOpen && createPortal(mobileMenuLayer, document.documentElement)}
    </header>
  )
}
