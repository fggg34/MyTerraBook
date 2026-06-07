import { Calendar, Home, LogOut, Settings } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import PageHead from '../seo/PageHead'
import usePageSeo from '../../hooks/usePageSeo'
import '../../styles/host-panel.css'

const ICONS = {
  '/dashboard': Calendar,
  '/dashboard/stays': Home,
  '/dashboard/settings': Settings,
}

const DEFAULT_NAV = [
  { to: '/dashboard', label: 'My bookings', end: true },
  { to: '/dashboard/stays', label: 'My stays' },
  { to: '/dashboard/settings', label: 'Settings' },
]

export default function ClientLayout() {
  const { page: copy } = usePageContent('client-panel')
  const navItems = (copy.navItems?.length ? copy.navItems : DEFAULT_NAV).map((item) => ({
    ...item,
    icon: ICONS[item.to] ?? Calendar,
    end: item.to === '/dashboard',
  }))
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: copy.sidebarTitle ?? 'My account' },
  })

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div className="mb-6 flex items-center justify-between gap-4">
          <div>
            <p className="text-sm font-semibold text-slate-500">{copy.eyebrow ?? 'My account'}</p>
            <h1 className="text-2xl font-bold text-brand-950">Welcome, {user?.name}</h1>
          </div>
          <button type="button" className="host-btn secondary" onClick={handleLogout}>
            <LogOut size={16} className="mr-2 inline" />
            {copy.signOutLabel ?? 'Sign out'}
          </button>
        </div>
        <div className="host-shell">
          <aside className="host-sidebar">
            <h2>{copy.sidebarTitle ?? 'Account'}</h2>
            <nav className="host-nav">
              {navItems.map((item) => (
                <NavLink key={item.to} to={item.to} end={item.end} className={({ isActive }) => (isActive ? 'active' : '')}>
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
