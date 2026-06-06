import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import SiteLogo from '../components/branding/SiteLogo'
import PageHead from '../components/seo/PageHead'
import { usePageContent } from '../context/SiteContentContext'
import usePageSeo from '../hooks/usePageSeo'
import '../styles/auth-pages.css'

export default function NewsletterUnsubscribePage() {
  const { page: copy } = usePageContent('newsletter-unsubscribe')
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: copy.title ?? 'Newsletter unsubscribe' },
  })
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''
  const [status, setStatus] = useState(token ? 'loading' : 'missing')
  const [message, setMessage] = useState('')

  useEffect(() => {
    if (!token) return undefined

    let cancelled = false

    api.post('/newsletter/unsubscribe', { token })
      .then(({ data }) => {
        if (cancelled) return
        setStatus('success')
        setMessage(data.message || 'You have been unsubscribed.')
      })
      .catch((err) => {
        if (cancelled) return
        setStatus('error')
        setMessage(err.response?.data?.message || 'This unsubscribe link is invalid or already used.')
      })

    return () => {
      cancelled = true
    }
  }, [token])

  return (
    <>
      <PageHead {...seo} />
      <div className="auth-page">
      <div className="wrap auth-shell">
        <div className="auth-intro">
          <SiteLogo variant="auth" className="logo-text" />
          <h1>{copy.title ?? 'Newsletter'}</h1>
          {status === 'loading' && <p>Unsubscribing…</p>}
          {status === 'missing' && <p>Missing unsubscribe link.</p>}
          {(status === 'success' || status === 'error') && <p>{message}</p>}
        </div>
        <p className="auth-switch">
          <Link to="/">{copy.backLabel ?? 'Back to homepage'}</Link>
        </p>
      </div>
    </div>
    </>
  )
}
