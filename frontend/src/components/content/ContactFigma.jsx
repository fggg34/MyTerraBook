import { useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import Container from '../site/Container'
import StrokeIcon from './StrokeIcon'
import '../../styles/frames.css'

const TOPICS = ['New booking', 'Existing booking', 'Insurance & damage', 'Becoming a host', 'Other']

export default function ContactFigma() {
  const [form, setForm] = useState({ name: '', email: '', topic: 'New booking', message: '' })
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
      setForm({ name: '', email: '', topic: 'New booking', message: '' })
    } catch (err) {
      setError(err.response?.data?.message || 'Could not send message. Email info@myterrabook.com instead.')
    } finally {
      setLoading(false)
    }
  }

  return (
    <div className="tb-frame">
      <section className="tb-contact-hero">
        <Container>
          <nav className="tb-crumbs">
            <Link to="/">Home</Link>
            <StrokeIcon name="chevron" size={12} />
            <strong>Contact</strong>
          </nav>
          <p className="tb-kicker">CONTACT US</p>
          <h1>Talk to a local. We’re here to <em>help</em>.</h1>
          <p className="lead">Questions about a booking, a vehicle or becoming a host? Our team in Reykjanesbær answers every day, Mon–Sun 08–22.</p>
        </Container>
      </section>

      <section className="tb-contact-main">
        <Container className="tb-contact-grid">
          <div>
            <a className="tb-method" href="tel:+3547870707">
              <i><StrokeIcon name="phone" size={22} /></i>
              <div>
                <small>PHONE</small>
                <strong>+354 787 0707</strong>
                <p>Mon–Sun 08–22 · English & Icelandic</p>
              </div>
              <em>Call now <StrokeIcon name="arrow" size={13} /></em>
            </a>
            <a className="tb-method" href="mailto:info@myterrabook.com">
              <i><StrokeIcon name="mail" size={22} /></i>
              <div>
                <small>EMAIL</small>
                <strong>info@myterrabook.com</strong>
                <p>We reply within opening hours</p>
              </div>
              <em>Send email <StrokeIcon name="arrow" size={13} /></em>
            </a>
            <a className="tb-method" href="https://maps.google.com/?q=Reykjanesb%C3%A6r+Iceland" target="_blank" rel="noreferrer">
              <i><StrokeIcon name="pin" size={22} /></i>
              <div>
                <small>ADDRESS</small>
                <strong>MyTerraBook ehf. 260 Reykjanesbær, Iceland</strong>
                <p>10 minutes from Keflavík airport</p>
              </div>
              <em>Get directions <StrokeIcon name="arrow" size={13} /></em>
            </a>
            <div className="tb-method">
              <i><StrokeIcon name="clock" size={22} /></i>
              <div>
                <small>HOURS</small>
                <strong>Mon–Sun 08–22</strong>
                <p>Local support 24/7 during your trip</p>
              </div>
            </div>
            <div className="tb-social">
              <div>
                <small>FOLLOW THE ROAD</small>
                <p>Trip ideas, road conditions & hidden detours</p>
              </div>
              <div>
                <a href="https://www.instagram.com/myterrabook" target="_blank" rel="noreferrer"><StrokeIcon name="instagram" size={14} /> Instagram</a>
                <a href="https://www.facebook.com/myterrabook" target="_blank" rel="noreferrer"><StrokeIcon name="facebook" size={14} /> Facebook</a>
              </div>
            </div>
          </div>

          <form className="tb-form" onSubmit={submit}>
            <p className="tb-kicker">SEND US A MESSAGE</p>
            <h2>How can we help?</h2>
            <p className="sub">Fill in the form and we’ll get back to you by email.</p>
            <div className="tb-form-row">
              <div>
                <label htmlFor="contact-name">Your name <i>*</i></label>
                <div className="field">
                  <StrokeIcon name="user" size={16} />
                  <input id="contact-name" required placeholder="e.g. Anna Jónsdóttir" value={form.name} onChange={set('name')} />
                </div>
              </div>
              <div>
                <label htmlFor="contact-email">Email <i>*</i></label>
                <div className="field">
                  <StrokeIcon name="mail" size={16} />
                  <input id="contact-email" type="email" required placeholder="you@example.com" value={form.email} onChange={set('email')} />
                </div>
              </div>
            </div>
            <label htmlFor="contact-topic">Topic</label>
            <div className="field">
              <select id="contact-topic" value={form.topic} onChange={set('topic')}>
                {TOPICS.map((topic) => <option key={topic}>{topic}</option>)}
              </select>
              <StrokeIcon name="chevronDown" size={14} />
            </div>
            <div className="tb-topics">
              {TOPICS.map((topic) => (
                <button key={topic} type="button" className={form.topic === topic ? 'is-on' : ''} onClick={() => setForm((current) => ({ ...current, topic }))}>
                  {topic}
                </button>
              ))}
            </div>
            <label htmlFor="contact-message">Message <i>*</i></label>
            <div className="field area">
              <textarea id="contact-message" required placeholder="Tell us about your trip, your booking reference or your question…" value={form.message} onChange={set('message')} />
            </div>
            <div className="tb-form-foot">
              <p>By sending this message you agree to our <Link to="/privacy">Privacy Policy</Link>.</p>
              <button className="tb-btn" type="submit" disabled={loading}>{loading ? 'Sending…' : 'Send message'} <StrokeIcon name="arrow" size={18} /></button>
            </div>
            {status && <p className="ok">{status}</p>}
            {error && <p className="err">{error}</p>}
          </form>
        </Container>
      </section>

      <section className="tb-map">
        <Container>
          <div className="tb-map-wrap">
            <iframe
              title="MyTerraBook in Reykjanesbær"
              src="https://maps.google.com/maps?q=Reykjanesb%C3%A6r%20Iceland&z=11&output=embed"
              loading="lazy"
            />
            <div className="tb-map-card">
              <strong><span><StrokeIcon name="pin" size={17} /></span> MyTerraBook ehf.</strong>
              <p>260 Reykjanesbær, Iceland</p>
              <a className="tb-btn sm" href="https://maps.google.com/?q=Reykjanesb%C3%A6r+Iceland" target="_blank" rel="noreferrer">Get directions <StrokeIcon name="arrow" size={14} /></a>
            </div>
          </div>
        </Container>
      </section>
    </div>
  )
}
