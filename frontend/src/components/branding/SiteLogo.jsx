import { Link } from 'react-router-dom'
import { useSiteContent } from '../../context/SiteContentContext'

function GlobeMark({ className = 'mark' }) {
  return (
    <span className={className}>
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden>
        <path d="M3 12a9 9 0 1 0 18 0 9 9 0 0 0-18 0Z" />
        <path d="M3 12h18M12 3c2.5 2.7 2.5 15.3 0 18M12 3c-2.5 2.7-2.5 15.3 0 18" />
      </svg>
    </span>
  )
}

function MountainMark({ className = 'mark' }) {
  return (
    <span className={className}>
      <svg viewBox="0 0 40 40" fill="none" aria-hidden>
        <path d="M6 30h28L22 8l-6 12-4-6-6 16z" fill="currentColor" opacity="0.9" />
      </svg>
    </span>
  )
}

export default function SiteLogo({ variant = 'header', className = '', asLink = true }) {
  const { branding } = useSiteContent()
  const mode = branding.logoMode ?? 'text'
  const prefix = branding.prefix ?? 'My'
  const accent = branding.accent ?? 'Terra'
  const suffix = branding.suffix ?? 'Book'
  const logoImage = branding.logoImage

  const text = (
    <>
      {mode === 'image' && logoImage ? (
        <img src={logoImage} alt={`${prefix}${accent}${suffix}`} className="site-logo__image" />
      ) : variant === 'auth' ? (
        <>
          <MountainMark />
          <span>{prefix}{accent}{suffix}</span>
        </>
      ) : (
        <>
          <GlobeMark />
          <span>
            {prefix}
            <span className="terra">{accent}</span>
            {suffix}
          </span>
        </>
      )}
    </>
  )

  if (!asLink) {
    return <span className={className}>{text}</span>
  }

  return (
    <Link to="/" className={className || 'logo'} aria-label="MyTerraBook home">
      {text}
    </Link>
  )
}
