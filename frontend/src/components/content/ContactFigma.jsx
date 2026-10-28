import { useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import { usePageContent } from '../../context/SiteContentContext'
import Container from '../site/Container'
import StrokeIcon from './StrokeIcon'
import '../../styles/frames.css'

const TOPICS = ['New booking', 'Existing booking', 'Insurance & damage', 'Becoming a host', 'Other']

function filled(value, fallback) {
  return typeof value === 'string' && value.trim() !== '' ? value : fallback
}

function shown(section) {
  return section?.visible !== false
}

export default function ContactFigma() {
  const { page } = usePageContent('contact')
  const hero = page.hero || {}
  const formCopy = page.form || {}
  const social = page.social || {}
  const map = page.map || {}
  const methods = page.methods?.length ? page.methods : [
    { icon: 'phone', label: 'PHONE', value: '+354 787 0707', note: 'Mon–Sun 08–22 · English & Icelandic', action: 'Call now', href: 'tel:+3547870707' },
    { icon: 'mail', label: 'EMAIL', value: 'info@myterrabook.com', note: 'We reply within opening hours', action: 'Send email', href: 'mailto:info@myterrabook.com' },
    { icon: 'pin', label: 'ADDRESS', value: 'MyTerraBook ehf. 260 Reykjanesbær, Iceland', note: '10 minutes from Keflavík airport', action: 'Get directions', href: 'https://maps.google.com/?q=Reykjanesb%C3%A6r+Iceland' },
    { icon: 'clock', label: 'HOURS', value: 'Mon–Sun 08–22', note: 'Local support 24/7 during your trip', action: '', href: '' },
  ]
  const topics = formCopy.topics?.length ? formCopy.topics : TOPICS
  const [form, setForm] = useState({ name: '', email: '', topic: topics[0] || 'New booking', message: '' })
  const [status, setStatus] = useState('')
  const [error, setError] = useState('')
  const [loading, setLoading] = useState(false)

  const set = (key) => (event) => setForm((current) => ({ ...current, [key]: event.target.value }))

  const submit = async (event) => {
    event.preventDefault()
    setLoading(true)
    setError('')
    setStatus('')
    try {
      const { data } = await api.post('/contact', {
        name: form.name,
        email: form.email,
        message: `Topic: ${form.topic}\n\n${form.message}`,
      })
      setStatus(data.message || 'Thanks, we will get back to you shortly.')
      setForm({ name: '', email: '', topic: topics[0] || 'New booking', message: '' })
    } catch (err) {
      setError(err.response?.data?.message || 'Could not send message. Email info@myterrabook.com instead.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="tb-frame">
      {shown(hero) && <section className="tb-contact-hero">
        <Container>
          <nav className="tb-crumbs">
            <Link to="/">Home</Link>
            <StrokeIcon name="chevron" size={12} />
            <strong>{filled(hero.crumb, 'Contact')}</strong>
          </nav>
          <p className="tb-kicker">{filled(hero.eyebrow, 'CONTACT US')}</p>
          <h1>{filled(hero.headingBefore, 'Talk to a local. We’re here to ')}<em>{filled(hero.headingAccent, 'help')}</em>{filled(hero.headingAfter, '.')}</h1>
          <p className="lead">{filled(hero.lead, 'Questions about a booking, a vehicle or becoming a host? Our team in Reykjanesbær answers every day, Mon–Sun 08–22.')}</p>
        </Container>
      </section>}

      {shown(page.methods) && shown(formCopy) && <section className="tb-contact-main">
        <Container className="tb-contact-grid">
          <div>
            {methods.map((method) => {
              const inner = (
                <>
                  <i><StrokeIcon name={method.icon || 'mail'} size={22} /></i>
                  <div>
                    <small>{method.label}</small>
                    <strong>{method.value}</strong>
                    {method.note && <p>{method.note}</p>}
                  </div>
                  {method.action ? <em>{method.action} <StrokeIcon name="arrow" size={13} /></em> : null}
                </>
              )
              return method.href ? (
                <a key={method.label} className="tb-method" href={method.href} target={method.href.startsWith('http') ? '_blank' : undefined} rel="noreferrer">{inner}</a>
              ) : (
                <div key={method.label} className="tb-method">{inner}</div>
              )
            })}
            <div className="tb-social">
              <div>
                <small>{filled(social.eyebrow, 'FOLLOW THE ROAD')}</small>
                <p>{filled(social.text, 'Trip ideas, road conditions & hidden detours')}</p>
              </div>
              <div>
                {(social.links?.length ? social.links : [
                  { label: 'Instagram', href: 'https://www.instagram.com/myterrabook', icon: 'instagram' },
                  { label: 'Facebook', href: 'https://www.facebook.com/myterrabook', icon: 'facebook' },
                ]).map((link) => (
                  <a key={link.label} href={link.href} target="_blank" rel="noreferrer"><StrokeIcon name={link.icon || 'link'} size={14} /> {link.label}</a>
                ))}
              </div>
            </div>
          </div>

          <form className="tb-form" onSubmit={submit}>
            <p className="tb-kicker">{filled(formCopy.eyebrow, 'SEND US A MESSAGE')}</p>
            <h2>{filled(formCopy.heading, 'How can we help?')}</h2>
            <p className="sub">{filled(formCopy.lead, 'Fill in the form and we’ll get back to you by email.')}</p>
            <div className="tb-form-row">
              <div>
                <label htmlFor="contact-name">{filled(formCopy.nameLabel, 'Your name')} <i>*</i></label>
                <div className="field">
                  <StrokeIcon name="user" size={16} />
                  <input id="contact-name" required placeholder={filled(formCopy.namePlaceholder, 'e.g. Anna Jónsdóttir')} value={form.name} onChange={set('name')} />
                </div>
              </div>
              <div>
                <label htmlFor="contact-email">{filled(formCopy.emailLabel, 'Email')} <i>*</i></label>
                <div className="field">
                  <StrokeIcon name="mail" size={16} />
                  <input id="contact-email" type="email" required placeholder={filled(formCopy.emailPlaceholder, 'you@example.com')} value={form.email} onChange={set('email')} />
                </div>
              </div>
            </div>
            <label htmlFor="contact-topic">{filled(formCopy.topicLabel, 'Topic')}</label>
            <div className="field">
              <select id="contact-topic" value={form.topic} onChange={set('topic')}>
                <option value="">{filled(formCopy.topicPlaceholder, 'Choose a topic')}</option>
                {topics.map((topic) => <option key={topic}>{topic}</option>)}
              </select>
              <StrokeIcon name="chevronDown" size={14} />
            </div>
            <div className="tb-topics">
              {topics.map((topic) => (
                <button key={topic} type="button" className={form.topic === topic ? 'is-on' : ''} onClick={() => setForm((current) => ({ ...current, topic }))}>
                  {topic}
                </button>
              ))}
            </div>
            <label htmlFor="contact-message">{filled(formCopy.messageLabel, 'Message')} <i>*</i></label>
            <div className="field area">
              <textarea id="contact-message" required placeholder={filled(formCopy.messagePlaceholder, 'Tell us about your trip, your booking reference or your question…')} value={form.message} onChange={set('message')} />
            </div>
            <div className="tb-form-foot">
              <p>{filled(formCopy.privacy, 'By sending this message you agree to our')} <Link to="/privacy">Privacy Policy</Link>.</p>
              <button className="tb-btn" type="submit" disabled={loading}>{loading ? filled(formCopy.sending, 'Sending…') : filled(formCopy.submit, 'Send message')} <StrokeIcon name="arrow" size={18} /></button>
            </div>
            {status && <p className="ok">{status}</p>}
            {error && <p className="err">{error}</p>}
          </form>
        </Container>
      </section>}

      {shown(map) && <section className="tb-map">
        <Container>
          <div className="tb-map-wrap">
            {map.image ? (
              <img src={map.image} alt={filled(map.imageAlt, map.title || 'Map')} loading="lazy" />
            ) : (
              <iframe
                title={filled(map.title, 'MyTerraBook in Reykjanesbær')}
                src={filled(map.embed, 'https://maps.google.com/maps?q=Reykjanesb%C3%A6r%20Iceland&z=11&output=embed')}
                loading="lazy"
              />
            )}
            <div className="tb-map-card">
              <strong><span><StrokeIcon name="pin" size={17} /></span> {filled(map.title, 'MyTerraBook ehf.')}</strong>
              <p>{filled(map.address, '260 Reykjanesbær, Iceland')}</p>
              <a className="tb-btn sm" href={filled(map.href, 'https://maps.google.com/?q=Reykjanesb%C3%A6r+Iceland')} target="_blank" rel="noreferrer">{filled(map.button, 'Get directions')} <StrokeIcon name="arrow" size={14} /></a>
            </div>
          </div>
        </Container>
      </section>}
    </div>
  )
}
