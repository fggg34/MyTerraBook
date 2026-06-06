import { Link } from 'react-router-dom'

export default function TopBar({ text, linkLabel, linkHref }) {
  const isInternal = linkHref?.startsWith('/') && !linkHref.startsWith('//')
  const LinkTag = isInternal ? Link : 'a'
  const linkProps = isInternal ? { to: linkHref } : { href: linkHref || '#' }

  if (!text && !linkLabel) {
    return null
  }

  return (
    <div className="topbar">
      <div className="wrap">
        {text && <span>{text}</span>}
        {linkLabel && (
          <LinkTag className="bannerlink" {...linkProps}>
            {linkLabel}
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </LinkTag>
        )}
      </div>
    </div>
  )
}
