import { Link } from 'react-router-dom'

export default function TopBar({ text, mobileText, linkLabel, mobileLinkLabel, linkHref }) {
  const isInternal = linkHref?.startsWith('/') && !linkHref.startsWith('//')
  const LinkTag = isInternal ? Link : 'a'
  const linkProps = isInternal ? { to: linkHref } : { href: linkHref || '#' }

  if (!text && !mobileText && !linkLabel) {
    return null
  }

  return (
    <div className="topbar">
      <div className="wrap">
        {text && <span className="topbar-text topbar-text--desktop">{text}</span>}
        {(mobileText || text) && (
          <span className="topbar-text topbar-text--mobile">{mobileText || text}</span>
        )}
        {linkLabel && (
          <LinkTag className="bannerlink" {...linkProps}>
            <span className="bannerlink-text bannerlink-text--desktop">{linkLabel}</span>
            {(mobileLinkLabel || linkLabel) && (
              <span className="bannerlink-text bannerlink-text--mobile">{mobileLinkLabel || linkLabel}</span>
            )}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </LinkTag>
        )}
      </div>
    </div>
  )
}
