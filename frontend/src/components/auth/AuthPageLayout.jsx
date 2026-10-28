import { Link } from 'react-router-dom'
import FigmaIcon from '../site/FigmaIcon'
import '../styles/auth-figma.css'

export default function AuthPageLayout({
  heroTitle,
  heroText,
  children,
  footer,
}) {
  return (
    <div className="tb-auth">
      <main className="tb-auth-card">
        <Link to="/" className="tb-auth-back">
          <FigmaIcon name="chevron" size={14} />
          Back to MyTerraBook
        </Link>
        {(heroTitle || heroText) && (
          <header className="tb-auth-intro">
            {heroTitle ? <p>{heroTitle}</p> : null}
            {heroText ? <span>{heroText}</span> : null}
          </header>
        )}
        {children}
        {footer ? <footer className="tb-auth-foot">{footer}</footer> : null}
      </main>
    </div>
  )
}
