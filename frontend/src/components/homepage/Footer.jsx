import { Link } from 'react-router-dom'

function FooterLink({ href, children }) {
  if (href?.startsWith('/') && !href.startsWith('//')) {
    return <Link to={href}>{children}</Link>
  }
  return <a href={href || '#'}>{children}</a>
}

export default function Footer({
  tagline,
  address,
  columns = [],
  copyright,
  social = [],
  legal = [],
  locale,
  currency,
}) {
  return (
    <footer className="hp-footer">
      <div className="homepage-wrap">
        <div className="hp-footer-top">
          <div className="hp-footer-brand">
            <Link to="/" className="hp-logo" style={{ color: '#fff' }}>
              <span className="hp-logo-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                  <circle cx="12" cy="12" r="9" stroke="currentColor" strokeWidth="1.8" />
                </svg>
              </span>
              <span>
                My<span className="green">Terra</span>Book
              </span>
            </Link>
            {tagline && <p>{tagline}</p>}
            {address && <div className="hp-footer-address">{address}</div>}
          </div>

          {columns.map((column) => (
            <div className="hp-footer-col" key={column.title}>
              <h4>{column.title}</h4>
              {(column.links || []).map((link) => (
                <FooterLink key={link.label} href={link.href}>
                  {link.label}
                </FooterLink>
              ))}
            </div>
          ))}
        </div>

        <div className="hp-footer-bottom">
          <div>{copyright}</div>
          <div className="hp-footer-social">
            {social.map((item) => (
              <a key={item.label} href={item.href || '#'}>
                {item.label}
              </a>
            ))}
          </div>
          <div>
            {locale} · {currency}
          </div>
          <div className="hp-footer-legal">
            {legal.map((item) => (
              <a key={item.label} href={item.href || '#'}>
                {item.label}
              </a>
            ))}
          </div>
        </div>
      </div>
    </footer>
  )
}
