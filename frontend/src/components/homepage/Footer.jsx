import { useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import Container from '../site/Container'
import FigmaIcon from '../site/FigmaIcon'
import { useToast } from '../../context/ToastContext'

function FooterLink({ href, children }) {
  if (href?.startsWith('/')) return <Link to={href}>{children}</Link>
  return <a href={href || '#'}>{children}</a>
}

export default function Footer({
  tagline,
  columns = [],
  copyright,
  paymentsLabel,
  social = [],
  newsletterHeading,
  newsletterLead,
  newsletterPlaceholder,
  newsletterButton,
}) {
  const { toast } = useToast()
  const [email, setEmail] = useState('')
  const [sending, setSending] = useState(false)

  async function subscribe(event) {
    event.preventDefault()
    if (!email || sending) return
    setSending(true)
    try {
      await api.post('/newsletter/subscribe', { email, source: 'footer' })
      setEmail('')
      toast('Subscribed.', 'success')
    } catch {
      toast('Could not subscribe.', 'error')
    } finally {
      setSending(false)
    }
  }

  return (
    <footer className="tb-footer">
      <Container>
        <div className="tb-footer-top">
          <div className="tb-footer-brand">
            <img src="/figma/logo-light.svg" alt="MyTerraBook" />
            {tagline ? <p>{tagline}</p> : null}
            <div className="tb-footer-social">
              {social.map((item) => (
                <a key={item.label || item.icon} href={item.href || '#'} aria-label={item.label || item.icon}>
                  <FigmaIcon name={item.icon || 'mail'} size={18} />
                </a>
              ))}
            </div>
          </div>
          {columns.map((column) => (
            <div key={column.key || column.title} className="tb-footer-col">
              <h4>{column.title}</h4>
              {(column.links || []).map((link) => (
                <FooterLink key={link.label} href={link.href}>{link.label}</FooterLink>
              ))}
            </div>
          ))}
        </div>
        <form className="tb-news" onSubmit={subscribe}>
          <div>
            {newsletterHeading ? <h3>{newsletterHeading}</h3> : null}
            {newsletterLead ? <p>{newsletterLead}</p> : null}
          </div>
          <label className="tb-news-field">
            <FigmaIcon name="mail" size={16} />
            <input
              type="email"
              required
              value={email}
              placeholder={newsletterPlaceholder || ''}
              onChange={(event) => setEmail(event.target.value)}
              aria-label={newsletterPlaceholder || 'Email'}
            />
          </label>
          <button type="submit" disabled={sending}>
            {newsletterButton}
            <FigmaIcon name="arrow" size={18} />
          </button>
        </form>
        <div className="tb-footer-bottom">
          <span>{copyright}</span>
          <span>{paymentsLabel}</span>
        </div>
      </Container>
    </footer>
  )
}
