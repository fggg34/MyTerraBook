import { useState } from 'react'
import { Link } from 'react-router-dom'
import { api } from '../api'
import StrokeIcon from '../components/content/StrokeIcon'
import PageHead from '../components/seo/PageHead'
import { usePageContent } from '../context/SiteContentContext'
import '../styles/frames.css'

function filled(value, fallback) {
  return typeof value === 'string' && value.trim() !== '' ? value : fallback
}

export default function ComingSoonPage() {
  const { page } = usePageContent('coming-soon')
  const hero = page.hero || {}
  const foot = page.footer || {}
  const [email, setEmail] = useState('')
  const [note, setNote] = useState('')

  const submit = async (event) => {
    event.preventDefault()
    try {
      await api.post('/newsletter/subscribe', { email, source: 'coming-soon' })
      setNote(filled(hero.success, 'You’re on the list. We’ll write once, when we launch.'))
      setEmail('')
    } catch {
      setNote(filled(hero.error, 'Could not save that email just now.'))
    }
  }

  return (
    <div className="tb-soon" style={{ backgroundImage: `url('${filled(hero.image, '/figma/login-hero.png')}')` }}>
      <PageHead title={page.seo?.title || 'Coming soon'} description={page.seo?.description} robots="noindex" />
      <div className="tb-soon-top">
        <img src="/figma/logo-light.svg" alt="MyTerraBook" />
        <span className="tb-soon-pill"><i /> {filled(hero.pill, 'Launching 2026 · Iceland first')}</span>
      </div>
      <div className="tb-soon-mid">
        <p className="tb-kicker">{filled(hero.eyebrow, 'COMING SOON')}</p>
        <h1>{filled(hero.headingBefore, 'MyTerraBook is ')}<em>{filled(hero.headingAccent, 'almost ready')}</em>{hero.headingAfter || ''}</h1>
        <p>{filled(hero.lead, 'Finishing touches on the road-trip platform.')}</p>
        <p>{filled(hero.sub, 'Campervans, cars & 4x4s and guesthouses — one booking for the whole trip.')}</p>
        <form onSubmit={submit}>
          <StrokeIcon name="mail" size={18} />
          <input type="email" required placeholder={filled(hero.placeholder, 'Your email address')} value={email} onChange={(event) => setEmail(event.target.value)} aria-label="Email" />
          <button className="tb-btn gold sm" type="submit">{filled(hero.button, 'Notify me')}</button>
        </form>
        <p className="fine">{note || filled(hero.fine, 'One email when we launch. No spam — unsubscribe any time.')}</p>
        <Link className="tb-soon-admin" to="/login"><StrokeIcon name="lock" size={14} /> {filled(hero.adminLabel, 'Admins can preview the site')} <StrokeIcon name="arrow" size={14} /> {filled(hero.signIn, 'Sign in')}</Link>
      </div>
      <div className="tb-soon-bottom">
        <span>{filled(foot.copyright, '© 2026 MyTerraBook ehf.')}</span>
        <span>{filled(foot.contact, 'info@myterrabook.com · +354 787 0707')}</span>
        <div>
          <a href="https://www.instagram.com/myterrabook" aria-label="Instagram"><StrokeIcon name="instagram" size={16} /></a>
          <a href="https://www.facebook.com/myterrabook" aria-label="Facebook"><StrokeIcon name="facebook" size={16} /></a>
        </div>
      </div>
    </div>
  )
}
