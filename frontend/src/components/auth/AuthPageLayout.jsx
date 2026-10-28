import { useEffect } from 'react'
import { Link } from 'react-router-dom'
import FigmaIcon from '../site/FigmaIcon'
import '../../styles/auth-figma.css'

export default function AuthPageLayout({
  variant = 'login',
  children,
}) {
  useEffect(() => {
    document.body.classList.add('tb-auth-screen')
    return () => document.body.classList.remove('tb-auth-screen')
  }, [])

  return (
    <div className={`tb-split-auth is-${variant}`}>
      <aside className="tb-split-visual">
        <img src="/figma/login-hero.png" alt="" />
        <div className="tb-split-shade" />
        <img className="tb-split-logo" src="/figma/logo-auth.svg" alt="MyTerraBook" />
        <div className="tb-split-quote">
          <p>ROAD TRIPS · ICELAND</p>
          <h2>One booking for the <em>whole</em> trip.</h2>
          <span>Campervans, cars &amp; 4x4s and guesthouses from local hosts — booked together, managed in one place.</span>
          <div>
            <b><FigmaIcon name="star" size={13} />4.9/5 · 12,400+ reviews</b>
            <b><FigmaIcon name="shield" size={13} />Free cancellation 48h</b>
            <b><FigmaIcon name="headset" size={13} />Local support 24/7</b>
          </div>
        </div>
      </aside>
      <section className="tb-split-form">
        <div className="tb-split-top">
          <Link to="/"><FigmaIcon name="chevron-left" size={16} />Back to MyTerraBook</Link>
          <span>
            New to MyTerraBook? <Link to="/register">Create account</Link>
          </span>
        </div>
        <div className="tb-split-body">{children}</div>
        <div className="tb-split-bottom">
          <span>© 2026 MyTerraBook ehf.</span>
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
