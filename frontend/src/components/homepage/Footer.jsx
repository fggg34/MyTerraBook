import { Link } from 'react-router-dom'

function FooterLink({ href, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return <Link to={href}>{children}</Link>
  }
  return <a href={href || '#'}>{children}</a>
}

export default function Footer({ tagline, address, columns = [], copyright, locale, currency, legal = [] }) {
  const addressLines = address?.split('\n') || []

  return (
    <footer className="ftr">
      <div className="wrap">
        <div className="ftr-top">
          <div className="ftr-brand">
            <div className="logo">
              <span className="mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.2" strokeLinecap="round" strokeLinejoin="round">
                  <path d="M3 19h18" />
                  <path d="m4 17 5-9 4 7 3-4 4 6" />
                </svg>
              </span>
              My<span className="terra">Terra</span>Book
            </div>
            {tagline && <p className="ftr-tag">{tagline}</p>}
            {addressLines.length > 0 && (
              <div className="ftr-addr">
                <b>{addressLines[0]}</b>
                {addressLines.slice(1).map((line) => (
                  <span key={line}>
                    <br />
                    {line}
                  </span>
                ))}
              </div>
            )}
            <div className="ftr-social">
              <a href="#" aria-label="Instagram">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="3" y="3" width="18" height="18" rx="5" />
                  <circle cx="12" cy="12" r="4" />
                  <circle cx="17.5" cy="6.5" r="1" fill="currentColor" />
                </svg>
              </a>
              <a href="#" aria-label="TikTok">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M16.5 3c.3 2 1.6 3.5 3.5 3.8v2.4c-1.4 0-2.7-.4-3.8-1.1v6.3c0 3.5-2.8 6.3-6.3 6.3S3.6 17.9 3.6 14.4s2.8-6.3 6.3-6.3c.3 0 .6 0 .9.1v2.7a3.7 3.7 0 1 0 2.8 3.6V3h2.9Z" />
                </svg>
              </a>
              <a href="#" aria-label="YouTube">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round">
                  <rect x="2.5" y="5.5" width="19" height="13" rx="3" />
                  <path d="m10.5 9.5 5 2.5-5 2.5z" fill="currentColor" />
                </svg>
              </a>
              <a href="#" aria-label="Facebook">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M13.5 21v-7.5h2.5l.4-3h-2.9V8.7c0-.9.3-1.5 1.5-1.5h1.5V4.6c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.2H8v3h2.6V21h2.9Z" />
                </svg>
              </a>
              <a href="#" aria-label="X / Twitter">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M18 3h3l-7 8 8 10h-6l-5-6.3L5 21H2l7.5-8.6L2 3h6l4.5 5.9L18 3Zm-1 16h1.6L7.1 4.8H5.4L17 19Z" />
                </svg>
              </a>
              <a href="#" aria-label="LinkedIn">
                <svg viewBox="0 0 24 24" fill="currentColor">
                  <path d="M4.5 4.5a2 2 0 1 1 0 4 2 2 0 0 1 0-4ZM3 10h3v11H3zM9 10h3v1.5c.7-1.1 2-1.7 3.3-1.7 2.8 0 3.7 1.7 3.7 4.3V21h-3v-6c0-1.6-.5-2.4-1.7-2.4-1.3 0-2.3.9-2.3 2.6V21H9V10Z" />
                </svg>
              </a>
            </div>
          </div>

          {columns.map((column) => (
            <div className="ftr-col" key={column.title}>
              <h4>{column.title}</h4>
              <ul>
                {(column.links || []).map((link) => (
                  <li key={link.label}>
                    <FooterLink href={link.href}>
                      {link.label}
                      {link.badge && <span className="new-pill">{link.badge}</span>}
                    </FooterLink>
                  </li>
                ))}
              </ul>
            </div>
          ))}
        </div>

        <div className="ftr-mid">
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

        <div className="ftr-bot">
          <div className="ftr-bot-l">
            <span className="copy">{copyright}</span>
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
          <div className="ftr-legal">
            {legal.map((item, index) => (
              <span key={item.label}>
                {index > 0 && <span className="dot" />}
                <a href={item.href || '#'}>{item.label}</a>
              </span>
            ))}
          </div>
        </div>
      </div>
      <div className="ftr-stripe" aria-hidden="true" />
    </footer>
  )
}
