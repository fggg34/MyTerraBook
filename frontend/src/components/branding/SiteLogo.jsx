import { Link } from 'react-router-dom'
import { useMemo } from 'react'
import { useSiteContent } from '../../context/SiteContentContext'
import { mergeBranding } from '../../utils/siteBootstrap'

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

function LogoPlaceholder({ className = '' }) {
  return <span className={`site-logo__placeholder ${className}`.trim()} aria-hidden="true" />
}

export default function SiteLogo({ variant = 'header', className = '', asLink = true }) {
  const { branding, loading } = useSiteContent()
  const effectiveBranding = useMemo(() => mergeBranding(branding), [branding])
  const mode = effectiveBranding.logoMode
  const prefix = effectiveBranding.prefix ?? 'My'
  const accent = effectiveBranding.accent ?? 'Terra'
  const suffix = effectiveBranding.suffix ?? 'Book'
  const logoImage = effectiveBranding.logoImage
  const hasResolvedBranding = Boolean(mode || logoImage)

  let content

  if (mode === 'image') {
    if (logoImage) {
      content = <img src={logoImage} alt={`${prefix}${accent}${suffix}`} className="site-logo__image" />
    } else if (loading) {
      content = <LogoPlaceholder />
    } else {
      content = <LogoPlaceholder />
    }
  } else if (loading && !hasResolvedBranding) {
    content = <LogoPlaceholder />
  } else if (variant === 'auth') {
    content = (
      <>
        <MountainMark />
        <span>{prefix}{accent}{suffix}</span>
      </>
    )
  } else {
    content = (
      <>
        <GlobeMark />
        <span>
          {prefix}
          <span className="terra">{accent}</span>
          {suffix}
        </span>
      </>
    )
  }

  if (!asLink) {
    return <span className={className}>{content}</span>
  }

  return (
    <Link to="/" className={className || 'logo'} aria-label={`${prefix}${accent}${suffix} home`}>
      {content}
    </Link>
  )
}
