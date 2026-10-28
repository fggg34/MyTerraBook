import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { usePageContent } from '../../context/SiteContentContext'
import { cmsText } from '../../data/searchFormCopy'

const COOKIE_KEY = 'terrabook_cookie_consent'
const COOKIE_MAX_AGE = 60 * 60 * 24 * 365

function readConsent() {
  try {
    const stored = localStorage.getItem(COOKIE_KEY)
    if (stored === 'accepted' || stored === 'rejected') return stored
  } catch {
    // ignore
  }
  if (typeof document === 'undefined') return null
  const match = document.cookie.split('; ').find((row) => row.startsWith(`${COOKIE_KEY}=`))
  if (!match) return null
  const value = decodeURIComponent(match.split('=').slice(1).join('='))
  return value === 'accepted' || value === 'rejected' ? value : null
}

function writeConsent(value) {
  try {
    localStorage.setItem(COOKIE_KEY, value)
  } catch {
    // ignore
  }
  document.cookie = `${COOKIE_KEY}=${encodeURIComponent(value)};path=/;max-age=${COOKIE_MAX_AGE};SameSite=Lax`
}

export default function CookieBanner() {
  const { page } = usePageContent('global')
  const copy = page.cookieBanner || {}
  const [choice, setChoice] = useState('pending')

  useEffect(() => {
    setChoice(readConsent() || 'open')
  }, [])

  if (choice !== 'open') return null

  const message = cmsText(
    copy.message,
    'We use cookies for essential booking features and, if you allow, to measure visits. You can change this anytime.',
  )
  const acceptLabel = cmsText(copy.acceptLabel, 'Accept')
  const rejectLabel = cmsText(copy.rejectLabel, 'Reject')
  const policyLabel = cmsText(copy.policyLabel, 'Cookie policy')
  const policyHref = copy.policyHref || '/cookies'

  const choose = (value) => {
    writeConsent(value)
    setChoice(value)
  }

  return (
    <div className="cookie-banner" role="dialog" aria-label={cmsText(copy.title, 'Cookies')}>
      <p className="cookie-banner__text">
        {message}{' '}
        <Link to={policyHref}>{policyLabel}</Link>
      </p>
      <div className="cookie-banner__actions">
        <button type="button" className="site-btn site-btn--light site-btn--m" onClick={() => choose('rejected')}>
          <span>{rejectLabel}</span>
        </button>
        <button type="button" className="site-btn site-btn--accent site-btn--m" onClick={() => choose('accepted')}>
          <span>{acceptLabel}</span>
        </button>
      </div>
    </div>
  )
}
