import useMediaQuery from '../../hooks/useMediaQuery'
import { Link } from 'react-router-dom'

export default function TopBar({ text, linkLabel, linkHref, mobileText, mobileLinkLabel }) {
  const isMobile = useMediaQuery('(max-width: 768px)')
  const displayText = (isMobile && mobileText) || text
  const displayLabel = (isMobile && mobileLinkLabel) || linkLabel
  const isInternal = linkHref?.startsWith('/') && !linkHref.startsWith('//')
  const LinkTag = isInternal ? Link : 'a'
  const linkProps = isInternal ? { to: linkHref } : { href: linkHref || '#' }

  if (!displayText && !displayLabel) {
    return null
  }

  return (
    <div className="topbar">
      <div className="wrap">
        {displayText && <span className="topbar-text">{displayText}</span>}
        {displayLabel && (
          <LinkTag className="bannerlink" {...linkProps}>
            <span className="bannerlink-text">{displayLabel}</span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
              <path d="M5 12h14M13 6l6 6-6 6" />
            </svg>
          </LinkTag>
        )}
      </div>
    </div>
  )
}
