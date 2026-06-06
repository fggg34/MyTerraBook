import { Link } from 'react-router-dom'
import SiteLogo from '../branding/SiteLogo'

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

export default function Footer({
  tagline,
  address,
  columns = [],
  copyright,
  locale,
  currency,
  legal = [],
  social = [],
}) {
  const bookLinks = findColumnLinks(columns, ['book', 'menu']) ?? BOOK_LINKS
  const companyLinks = findColumnLinks(columns, ['company', 'pages', 'explore']) ?? COMPANY_LINKS
  const addressLine = address?.split('\n').filter(Boolean)[0]

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

            <div className="ftr-col">
              <h4>Book</h4>
              <ul>
                {bookLinks.map((link) => (
                  <li key={link.label}>
                    <FooterLink href={link.href}>{link.label}</FooterLink>
                  </li>
                ))}
              </ul>
            </div>

            <div className="ftr-col">
              <h4>Company</h4>
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
            </div>

            <div className="ftr-host">
              <h4>Account</h4>
              <ul className="ftr-host-links">
                <li>
                  <FooterLink href="/login">Sign in</FooterLink>
                </li>
                <li>
                  <FooterLink href="/register">Create account</FooterLink>
                </li>
              </ul>
              <FooterLink href="/become-a-host" className="ftr-host-cta">
                Become a host
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                  <path d="M5 12h14M13 6l6 6-6 6" />
                </svg>
              </FooterLink>
            </div>
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

            <div className="ftr-selectors">
              <button className="selector" type="button">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <circle cx="12" cy="12" r="9" />
                  <path d="M3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18" />
                </svg>
                {locale}
              </button>
              <button className="selector" type="button">
                {currency}
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="m6 9 6 6 6-6" />
                </svg>
              </button>
            </div>
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
