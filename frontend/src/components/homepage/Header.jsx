import { useEffect, useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import Container from '../site/Container'
import FigmaIcon from '../site/FigmaIcon'
import { useAuth, getPostLoginPath } from '../../context/AuthContext'
import { useLocalePreferences } from '../../context/LocalePreferencesContext'
import { CURRENCIES } from '../../data/localePreferences'

function NavItem({ href, children, onClick }) {
  if (href?.startsWith('/')) {
    return <Link to={href} onClick={onClick}>{children}</Link>
  }
  return <a href={href || '#'} onClick={onClick}>{children}</a>
}

export default function Header({
  navLinks = [],
  ctaLabel,
  ctaHref = '/become-a-host',
  signInLabel = 'Sign in',
  signInHref = '/login',
  accountLabel = 'Account',
  currencyNote = 'Prices are shown in your currency. Bookings are charged in ISK.',
}) {
  const { user } = useAuth()
  const navigate = useNavigate()
  const { currency, setCurrency } = useLocalePreferences()
  const [currencyOpen, setCurrencyOpen] = useState(false)
  const [menuOpen, setMenuOpen] = useState(false)
  const currencyRef = useRef(null)

  useEffect(() => {
    const close = (event) => {
      if (currencyRef.current && !currencyRef.current.contains(event.target)) {
        setCurrencyOpen(false)
      }
    }
    document.addEventListener('mousedown', close)
    return () => document.removeEventListener('mousedown', close)
  }, [])

  useEffect(() => {
    document.body.classList.toggle('tb-menu-open', menuOpen)
    return () => document.body.classList.remove('tb-menu-open')
  }, [menuOpen])

  const accountHref = user ? getPostLoginPath(user) : signInHref
  const accountText = user ? accountLabel : signInLabel

  return (
    <header className="tb-header">
      <Container className="tb-header-bar">
        <Link className="tb-logo" to="/" aria-label="MyTerraBook home">
          <img src="/figma/logo.svg" alt="MyTerraBook" />
        </Link>
        <nav className="tb-nav" aria-label="Main">
          {navLinks.map((link) => (
            <NavItem key={link.label} href={link.href}>{link.label}</NavItem>
          ))}
        </nav>
        <div className="tb-header-spacer" />
        <div className="tb-currency" ref={currencyRef}>
          <button
            type="button"
            className="tb-currency-btn"
            aria-expanded={currencyOpen}
            aria-haspopup="dialog"
            onClick={() => setCurrencyOpen((open) => !open)}
          >
            <FigmaIcon name="globe" size={16} />
            <span>{currency?.code || 'EUR'}</span>
          </button>
          {currencyOpen && (
            <div className="tb-currency-panel" role="dialog" aria-label="Currency">
              <p className="tb-currency-kicker">Currency</p>
              {CURRENCIES.map((item) => {
                const selected = item.code === currency?.code
                return (
                  <button
                    key={item.code}
                    type="button"
                    className={selected ? 'tb-currency-opt is-on' : 'tb-currency-opt'}
                    onClick={() => {
                      setCurrency(item)
                      setCurrencyOpen(false)
                    }}
                  >
                    <span className={selected ? 'tb-currency-mark is-on' : 'tb-currency-mark'}>{item.symbol || item.code}</span>
                    <span>
                      <strong>{item.code}</strong>
                      <em>{item.name}</em>
                    </span>
                    {selected ? <FigmaIcon name="check" size={16} /> : <span className="tb-currency-check" />}
                  </button>
                )
              })}
              <p className="tb-currency-note">{currencyNote}</p>
            </div>
          )}
        </div>
        {ctaLabel ? (
          <NavItem className="tb-host-link" href={ctaHref}>
            <span className="tb-host-link">
              <FigmaIcon name="key" size={16} />
              {ctaLabel}
            </span>
          </NavItem>
        ) : null}
        <button type="button" className="tb-account" onClick={() => navigate(accountHref)}>
          <FigmaIcon name="user" size={16} />
          {accountText}
        </button>
        <button
          type="button"
          className="tb-menu-btn"
          aria-label={menuOpen ? 'Close menu' : 'Open menu'}
          aria-expanded={menuOpen}
          onClick={() => setMenuOpen((open) => !open)}
        >
          <span />
          <span />
          <span />
        </button>
      </Container>
      {menuOpen && (
        <div className="tb-drawer">
          <nav>
            {navLinks.map((link) => (
              <NavItem key={link.label} href={link.href} onClick={() => setMenuOpen(false)}>{link.label}</NavItem>
            ))}
            {ctaLabel ? <NavItem href={ctaHref} onClick={() => setMenuOpen(false)}>{ctaLabel}</NavItem> : null}
            <NavItem href={accountHref} onClick={() => setMenuOpen(false)}>{accountText}</NavItem>
          </nav>
        </div>
      )}
    </header>
  )
}
