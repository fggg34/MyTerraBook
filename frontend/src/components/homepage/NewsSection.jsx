import { useEffect, useState } from 'react'
import { api } from '../../api'
import { useToast } from '../../context/ToastContext'

export default function NewsSection({
  heading,
  headingAccent,
  lead,
  backgroundImage,
  placeholder,
  successMessage,
}) {
  const { toast } = useToast()
  const [email, setEmail] = useState('')
  const [submitted, setSubmitted] = useState(false)
  const [loading, setLoading] = useState(false)

  const handleSubmit = async (event) => {
    event.preventDefault()
    setLoading(true)
    try {
      await api.post('/newsletter/subscribe', { email, source: 'homepage' })
      setSubmitted(true)
      setEmail('')
    } catch (err) {
      toast(err.response?.data?.message || 'Could not subscribe right now', 'error')
    } finally {
      setLoading(false)
    }
  }

  useEffect(() => {
    if (!submitted) return undefined
    const timer = window.setTimeout(() => setSubmitted(false), 4500)
    return () => window.clearTimeout(timer)
  }, [submitted])

  const headingParts = headingAccent && heading?.includes(headingAccent)
    ? heading.split(headingAccent)
    : null

  return (
    <section className="news">
      <img
        className="news-bg"
        src={backgroundImage || '/images/homepage/hero.jpg'}
        alt="Iceland terrain, campervan beneath dramatic peaks"
      />
      <div className="news-aurora" aria-hidden="true" />
      <div className="wrap">
        <div className="news-copy">
          {heading && (
            <h2>
              {headingParts ? (
                <>
                  {headingParts[0]}
                  <span className="accent">{headingAccent}</span>
                  {headingParts[1]}
                </>
              ) : (
                heading
              )}
            </h2>
          )}
          {lead && <p className="news-lead">{lead}</p>}
        </div>

        <div className="news-action">
          <form className="news-form" id="newsForm" onSubmit={handleSubmit}>
            <div className="news-form-field">
              <svg className="mail" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="14" rx="2.5" />
                <path d="m4 7 8 6 8-6" />
              </svg>
              <input
                type="email"
                id="newsEmail"
                placeholder={placeholder}
                autoComplete="email"
                value={email}
                onChange={(e) => setEmail(e.target.value)}
                required
              />
            </div>
            <button type="submit" disabled={loading}>
              {loading ? 'Subscribing…' : 'Subscribe'}
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </button>
          </form>
          <div className="news-foot">
            <span className="news-default" style={{ display: submitted ? 'none' : 'flex' }}>
              <span>One email a month</span>
              <span className="sep" />
              <span>No spam</span>
              <span className="sep" />
              <span>Unsubscribe anytime</span>
            </span>
            <span className={`news-success ${submitted ? 'show' : ''}`} id="newsOK">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
                <path d="m5 13 4 4L19 7" />
              </svg>
              {successMessage}
            </span>
          </div>
        </div>
      </div>
    </section>
  )
}
