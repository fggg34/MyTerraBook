import { Car, Calendar, Home, LayoutDashboard, LogOut, Settings } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import { usePageContent } from '../../context/SiteContentContext'
import PageHead from '../seo/PageHead'
import usePageSeo from '../../hooks/usePageSeo'
import 'react-datepicker/dist/react-datepicker.css'
import '../../styles/host-panel.css'

const ICONS = {
  '/host': LayoutDashboard,
  '/host/guesthouses': Home,
  '/host/cars': Car,
  '/host/bookings': Calendar,
  '/host/settings': Settings,
}

const DEFAULT_NAV = [
  { to: '/host', label: 'Dashboard', end: true },
  { to: '/host/cars', label: 'Cars & vans' },
  { to: '/host/guesthouses', label: 'Guesthouses' },
  { to: '/host/bookings', label: 'Bookings' },
  { to: '/host/settings', label: 'Settings' },
]

export default function HostLayout() {
  const { page: copy } = usePageContent('host-panel')
  const navItems = (copy.navItems?.length ? copy.navItems : DEFAULT_NAV).map((item) => ({
    ...item,
    icon: ICONS[item.to] ?? LayoutDashboard,
    end: item.to === '/host',
  }))
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: copy.sidebarTitle ?? 'Host panel' },
  })

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="host-panel-page host-account mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div className="host-panel-topbar">
        <div className="host-panel-topbar__intro">
          <p className="text-sm font-semibold text-slate-500">{copy.eyebrow ?? 'Host panel'}</p>
          <h1 className="text-2xl font-bold text-brand-950">Welcome, {user?.name}</h1>
        </div>
        <button type="button" className="host-btn secondary host-panel-topbar__signout" onClick={handleLogout}>
          <LogOut size={16} />
          <span>{copy.signOutLabel ?? 'Sign out'}</span>
        </button>
      </div>
      <div className="host-shell">
        <aside className="host-sidebar">
          <h2>{copy.sidebarTitle ?? 'Manage'}</h2>
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
