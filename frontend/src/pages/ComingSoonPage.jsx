import { useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../api'
import StrokeIcon from '../components/content/StrokeIcon'
import PageHead from '../components/seo/PageHead'
import '../styles/frames.css'

export default function ComingSoonPage() {
  const [email, setEmail] = useState('')
  const [note, setNote] = useState('')

  const submit = async (event) => {
    event.preventDefault()
    try {
      await api.post('/newsletter/subscribe', { email, source: 'coming-soon' })
      setNote('You’re on the list. We’ll write once, when we launch.')
      setEmail('')
    } catch {
      setNote('Could not save that email just now.')
    }
  }

  return (
    <div className="tb-soon" style={{ backgroundImage: "url('/figma/login-hero.png')" }}>
      <PageHead title="Coming soon" robots="noindex" />
      <div className="tb-soon-top">
        <img src="/figma/logo-light.svg" alt="MyTerraBook" />
        <span className="tb-soon-pill"><i /> Launching 2026 · Iceland first</span>
      </div>
      <div className="tb-soon-mid">
        <p className="tb-kicker">COMING SOON</p>
        <h1>MyTerraBook is <em>almost ready</em></h1>
        <p>Finishing touches on the road-trip platform.</p>
        <p>Campervans, cars & 4x4s and guesthouses — one booking for the whole trip.</p>
        <form onSubmit={submit}>
          <StrokeIcon name="mail" size={18} />
          <input type="email" required placeholder="Your email address" value={email} onChange={(event) => setEmail(event.target.value)} aria-label="Email" />
          <button className="tb-btn gold sm" type="submit">Notify me</button>
        </form>
        <p className="fine">{note || 'One email when we launch. No spam — unsubscribe any time.'}</p>
        <Link className="tb-soon-admin" to="/login"><StrokeIcon name="lock" size={14} /> Admins can preview the site <StrokeIcon name="arrow" size={14} /> Sign in</Link>
      </div>
      <div className="tb-soon-bottom">
        <span>© 2026 MyTerraBook ehf.</span>
        <span>info@myterrabook.com · +354 787 0707</span>
        <div>
          <a href="https://www.instagram.com/myterrabook" aria-label="Instagram"><StrokeIcon name="instagram" size={16} /></a>
          <a href="https://www.facebook.com/myterrabook" aria-label="Facebook"><StrokeIcon name="facebook" size={16} /></a>
        </div>
      </div>
    </div>
  )
}
