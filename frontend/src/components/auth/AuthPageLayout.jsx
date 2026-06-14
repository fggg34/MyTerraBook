import SiteLogo from '../branding/SiteLogo'

const DEFAULT_FEATURES = [
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M3 9.5 12 3l9 6.5V20a1 1 0 0 1-1 1h-5v-7H9v7H4a1 1 0 0 1-1-1V9.5Z" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'Guesthouses & campervans',
    text: 'Book unique stays and road-trip ready vehicles across Iceland.',
  },
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M12 22s8-4.5 8-11.5a8 8 0 1 0-16 0C4 17.5 12 22 12 22Z" strokeLinecap="round" strokeLinejoin="round" />
        <circle cx="12" cy="10.5" r="2.5" />
      </svg>
    ),
    title: 'Local hosts you can trust',
    text: 'Every listing is vetted and supported by our Iceland-based team.',
  },
  {
    icon: (
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.75" aria-hidden>
        <path d="M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" strokeLinecap="round" strokeLinejoin="round" />
        <path d="M12 7v5l3 2" strokeLinecap="round" strokeLinejoin="round" />
      </svg>
    ),
    title: 'Flexible booking',
    text: 'Manage reservations, messages and trip details in one place.',
  },
]

const DEFAULT_HERO_IMAGES = {
  login: '/images/homepage/hero.jpg',
  register: '/images/homepage/hero.jpg',
  'host-register': '/images/homepage/host-van.jpg',
}

export default function AuthPageLayout({
  variant = 'login',
  heroImage,
  heroTitle,
  heroText,
  heroStat,
  features = DEFAULT_FEATURES,
  children,
  footer,
}) {
  const resolvedHeroImage = heroImage ?? DEFAULT_HERO_IMAGES[variant] ?? DEFAULT_HERO_IMAGES.login
  const title = heroTitle ?? (variant === 'register'
    ? 'Create your MyTerraBook account'
    : 'Sign in to MyTerraBook')
  const text = heroText ?? (variant === 'register'
    ? 'Join travelers who book guesthouses, campervans and cars across Iceland, all in one trusted marketplace.'
    : 'Pick up where you left off. Your bookings, saved listings and trip details are waiting.')

  return (
    <div className={`auth-layout auth-layout--${variant}`}>
      <aside className="auth-layout__hero" aria-hidden="true">
        <img className="auth-layout__hero-img" src={resolvedHeroImage} alt="" />
        <div className="auth-layout__hero-overlay" />
        <div className="auth-layout__hero-content">
          <h2 className="auth-layout__hero-title">{title}</h2>
          <p className="auth-layout__hero-text">{text}</p>
          {heroStat && (
            <div className="auth-layout__hero-stat">
              <span className="auth-layout__hero-stat-amt">{heroStat.amount}</span>
              <span className="auth-layout__hero-stat-suffix">{heroStat.suffix}</span>
            </div>
          )}
          <ul className="auth-layout__features">
            {features.map((feature) => (
              <li key={feature.title} className="auth-layout__feature">
                <span className="auth-layout__feature-icon">{feature.icon}</span>
                <span>
                  <strong>{feature.title}</strong>
                  <span>{feature.text}</span>
                </span>
              </li>
            ))}
          </ul>
        </div>
      </aside>

      <main className="auth-layout__main">
        <div className="auth-layout__panel">
          <header className="auth-layout__header">
            <SiteLogo variant="auth" className="auth-layout__logo" />
          </header>
          <div className="auth-layout__body">{children}</div>
          {footer && <footer className="auth-layout__footer">{footer}</footer>}
        </div>
      </main>
    </div>
  )
}
