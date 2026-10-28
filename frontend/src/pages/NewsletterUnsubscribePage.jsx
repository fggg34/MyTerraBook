import { useEffect, useState } from 'react'
import { Link, useSearchParams } from 'react-router-dom'
import { api } from '../api'
import StrokeIcon from '../components/content/StrokeIcon'
import PageHead from '../components/seo/PageHead'
import { usePageContent } from '../context/SiteContentContext'
import '../styles/frames.css'

const REASONS = [
  'I get too many emails',
  'The content isn’t relevant to my trips',
  'I’ve already finished my trip to Iceland',
  'I never signed up for this newsletter',
  'Other reason',
]

function filled(value, fallback) {
  return typeof value === 'string' && value.trim() !== '' ? value : fallback
}

export default function NewsletterUnsubscribePage() {
  const { page: copy } = usePageContent('newsletter-unsubscribe')
  const reasons = copy.reasons?.length ? copy.reasons : REASONS
  const [searchParams] = useSearchParams()
  const token = searchParams.get('token') ?? ''
  const email = searchParams.get('email') || 'anna.jonsdottir@gmail.com'
  const [status, setStatus] = useState(token ? 'loading' : 'ready')
  const [message, setMessage] = useState('')
  const [reason, setReason] = useState('')
  const [sent, setSent] = useState(false)

  useEffect(() => {
    if (!token) return undefined
    let cancelled = false
    api.post('/newsletter/unsubscribe', { token })
      .then(({ data }) => {
        if (cancelled) return
        setStatus('ready')
        setMessage(data.message || '')
      })
      .catch((err) => {
        if (cancelled) return
        setStatus('error')
        setMessage(err.response?.data?.message || 'This unsubscribe link is invalid or already used.')
      })
    return () => { cancelled = true }
  }, [token])

  return (
    <div className="tb-unsub">
      <PageHead title="Newsletter unsubscribe" robots="noindex" />
      <div className="tb-unsub-logo"><img src="/figma/logo.svg" alt="MyTerraBook" /></div>
      <div className="tb-unsub-card">
        <i><StrokeIcon name="mail" size={28} /></i>
        <p className="tb-kicker">{filled(copy.kicker, 'NEWSLETTER')}</p>
        <h1>{status === 'error' ? filled(copy.errorTitle, 'We couldn’t unsubscribe that address') : <>{filled(copy.headingBefore, 'You’ve been ')}<em>{filled(copy.headingAccent, 'unsubscribed')}</em></>}</h1>
        <p>
          {status === 'loading' && filled(copy.loadingMessage, 'Removing you from the list…')}
          {status === 'error' && (message || filled(copy.errorMessage, 'This unsubscribe link is invalid or already used.'))}
          {status === 'ready' && filled(copy.lead, `${email} won’t receive road conditions, seasonal deals & hidden detours any more. Booking confirmations and receipts still arrive as usual.`)}
        </p>
        {status !== 'error' && (
          <>
            <hr />
            <div style={{ textAlign: 'left' }}>
              <strong>{filled(copy.reasonTitle, 'Mind telling us why?')}</strong>
              <p style={{ margin: '6px 0 14px', color: '#546375' }}>{filled(copy.reasonLead, 'Optional — it helps us send better emails.')}</p>
            </div>
            <div className="tb-reasons">
              {reasons.map((item) => (
                <button key={item} type="button" className={reason === item ? 'is-on' : ''} onClick={() => setReason(item)}>
                  <i /> {item}
                </button>
              ))}
            </div>
            <div className="tb-unsub-actions">
              <button type="button" className="tb-btn" disabled={!reason || sent} onClick={async () => {
                if (!token) {
                  setStatus('error')
                  setMessage(filled(copy.missingMessage, 'Missing unsubscribe link.'))
                  return
                }
                try {
                  await api.post('/newsletter/unsubscribe', { token, reason })
                  setSent(true)
                } catch (err) {
                  setStatus('error')
                  setMessage(err.response?.data?.message || filled(copy.errorMessage, 'Could not save that reason.'))
                }
              }}>{sent ? filled(copy.thanks, 'Thanks') : filled(copy.submitLabel, 'Send feedback')}</button>
              <Link className="tb-btn ghost" to="/">{filled(copy.backLabel, 'Back to the site')}</Link>
            </div>
            <p className="foot">{sent ? 'Noted. You can change email preferences any time in Account → Settings.' : 'Changed your mind later? Manage email preferences any time in Account → Settings.'}</p>
          </>
        )}
        {status === 'error' && <p className="foot"><Link to="/">Back to the site</Link></p>}
      </div>
      <p className="tb-unsub-legal">© 2026 MyTerraBook ehf. · 260 Reykjanesbær, Iceland · info@myterrabook.com · <Link to="/privacy">Privacy</Link> · <Link to="/terms">Terms</Link></p>
    </div>
  )
}
