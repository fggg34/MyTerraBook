import { Link } from 'react-router-dom'

function FooterLink({ href, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return <Link to={href}>{children}</Link>
  }
  return <a href={href || '#'}>{children}</a>
}

export default function Footer({ tagline, address, columns = [], copyright, locale, currency, legal = [], social = [] }) {
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
                <FooterLink href={item.href}>{item.label}</FooterLink>
              </span>
            ))}
          </div>
        </div>
      </div>
      <div className="ftr-stripe" aria-hidden="true" />
    </footer>
  )
}
