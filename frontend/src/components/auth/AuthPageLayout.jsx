import { useEffect } from 'react'
import { Link } from 'react-router-dom'
import { usePageContent } from '../../context/SiteContentContext'
import FigmaIcon from '../site/FigmaIcon'
import '../../styles/auth-figma.css'

function filled(value, fallback) {
  return typeof value === 'string' && value.trim() !== '' ? value : fallback
}

export default function AuthPageLayout({
  variant = 'login',
  children,
}) {
  const { page: loginPage } = usePageContent('auth-login')
  const { page: registerPage } = usePageContent('auth-register')
  const page = variant === 'register' ? { ...loginPage, ...registerPage } : loginPage
  const visual = (variant === 'register' ? registerPage.visual : loginPage.visual) || loginPage.visual || {}
  const pills = visual.pills?.length ? visual.pills : [
    { icon: 'star', text: '4.9/5 · 12,400+ reviews' },
    { icon: 'shield', text: 'Free cancellation 48h' },
    { icon: 'headset', text: 'Local support 24/7' },
  ]
  useEffect(() => {
    document.body.classList.add('tb-auth-screen')
    return () => document.body.classList.remove('tb-auth-screen')
  }, [])

  return (
    <div className={`tb-split-auth is-${variant}`}>
      <aside className="tb-split-visual">
        <img src={filled(visual.image, '/figma/login-hero.png')} alt="" />
        <div className="tb-split-shade" />
        <img className="tb-split-logo" src="/figma/logo-auth.svg" alt="MyTerraBook" />
        <div className="tb-split-quote">
          <p>{filled(visual.kicker, 'ROAD TRIPS · ICELAND')}</p>
          <h2>{filled(visual.headingBefore, 'One booking for the ')}<em>{filled(visual.headingAccent, 'whole')}</em>{filled(visual.headingAfter, ' trip.')}</h2>
          <span>{filled(visual.lead, 'Campervans, cars & 4x4s and guesthouses from local hosts — booked together, managed in one place.')}</span>
          <div>
            {pills.map((pill) => (
              <b key={pill.text}><FigmaIcon name={pill.icon || 'star'} size={13} />{pill.text}</b>
            ))}
          </div>
        </div>
      </aside>
      <section className="tb-split-form">
        <div className="tb-split-top">
          <Link to="/"><FigmaIcon name="chevron-left" size={16} />{filled(page.backLabel, 'Back to MyTerraBook')}</Link>
          <span>
            {filled(page.newPrompt, 'New to MyTerraBook?')} <Link to="/register">{filled(page.newLink, 'Create account')}</Link>
          </span>
        </div>
        <div className="tb-split-body">{children}</div>
        <div className="tb-split-bottom">
          <span>{filled(page.copyright, '© 2026 MyTerraBook ehf.')}</span>
          <span>
            <Link to="/terms">Terms</Link>
            <Link to="/privacy">Privacy</Link>
            <Link to="/contact">Help</Link>
          </span>
        </div>
      </section>
    </div>
  )
}
