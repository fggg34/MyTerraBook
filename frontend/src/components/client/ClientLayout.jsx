import { Clock, LogOut, Settings } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import PageHead from '../seo/PageHead'
import usePageSeo from '../../hooks/usePageSeo'
import '../../styles/client-panel.css'

const ICONS = {
  '/dashboard': Clock,
  '/dashboard/settings': Settings,
}

const DEFAULT_NAV = [
  { to: '/dashboard', label: 'Trip history', end: true },
  { to: '/dashboard/settings', label: 'Settings' },
]

export default function ClientLayout() {
  const { page: copy } = usePageContent('client-panel')
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const navItems = (copy.navItems?.length ? copy.navItems : DEFAULT_NAV).map((item) => ({
    ...item,
    icon: ICONS[item.to] ?? Clock,
    end: item.to === '/dashboard',
  }))
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: copy.sidebarTitle ?? 'My account' },
  })

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  const firstName = user?.name?.split(' ')[0] || 'traveler'

  return (
    <>
      <PageHead {...seo} />
      <div className="client-panel">
        <header className="client-hero">
          <div className="client-hero-inner">
            <div>
              <p className="client-hero-eyebrow">{copy.eyebrow ?? 'My account'}</p>
              <h1 className="client-hero-title">Welcome back, {firstName}</h1>
              <p className="client-hero-sub">{copy.heroText ?? 'Your cars, campervans and guesthouse stays — all in one place.'}</p>
            </div>
            <button type="button" className="client-signout" onClick={handleLogout}>
              <LogOut size={16} />
              {copy.signOutLabel ?? 'Sign out'}
            </button>
          </div>
        </header>

        <nav className="client-nav-wrap" aria-label="Account navigation">
          <div className="client-nav-inner">
            {navItems.map((item) => (
              <NavLink
                key={item.to}
                to={item.to}
                end={item.end}
                className={({ isActive }) => `client-nav-link${isActive ? ' active' : ''}`}
              >
                <item.icon size={16} />
                {item.label}
              </NavLink>
            ))}
          </div>
        </nav>

        <main className="client-main">
          <Outlet />
        </main>
      </div>
    </>
  )
}
