import { Clock, LogOut, Settings } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import PageHead from '../seo/PageHead'
import usePageSeo from '../../hooks/usePageSeo'
import '../../styles/host-panel.css'
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
      <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div className="mb-6 flex items-center justify-between gap-4">
          <div>
            <p className="text-sm font-semibold text-slate-500">{copy.eyebrow ?? 'My account'}</p>
            <h1 className="text-2xl font-bold text-brand-950">Welcome back, {firstName}</h1>
            <p className="mt-1 text-sm text-slate-500">
              {copy.heroText ?? 'Your cars, campervans and guesthouse stays, all in one place.'}
            </p>
          </div>
          <button type="button" className="host-btn secondary" onClick={handleLogout}>
            <LogOut size={16} className="mr-2 inline" />
            {copy.signOutLabel ?? 'Sign out'}
          </button>
        </div>
        <div className="host-shell">
          <aside className="host-sidebar">
            <h2>{copy.sidebarTitle ?? 'My account'}</h2>
            <nav className="host-nav">
              {navItems.map((item) => (
                <NavLink
                  key={item.to}
                  to={item.to}
                  end={item.end}
                  className={({ isActive }) => (isActive ? 'active' : '')}
                >
                  <item.icon size={18} />
                  {item.label}
                </NavLink>
              ))}
            </nav>
          </aside>
          <main className="host-main">
            <Outlet />
          </main>
        </div>
      </div>
    </>
  )
}
