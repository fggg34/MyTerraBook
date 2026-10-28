import { Car, Calendar, Home, LayoutDashboard, LogOut, MoreHorizontal, Plug, Settings } from 'lucide-react'
import { NavLink, Outlet, useLocation, useNavigate } from 'react-router-dom'
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
  '/host/integrations': Plug,
  '/host/settings': Settings,
}

const DEFAULT_NAV = [
  { to: '/host', label: 'Dashboard', end: true },
  { to: '/host/cars', label: 'Cars & vans' },
  { to: '/host/guesthouses', label: 'Guesthouses' },
  { to: '/host/bookings', label: 'Bookings' },
  { to: '/host/integrations', label: 'API connections' },
  { to: '/host/settings', label: 'Settings' },
]

function resolveNavItems(cmsItems, defaults) {
  if (!cmsItems?.length) return defaults
  const labelByTo = new Map(cmsItems.map((item) => [item.to, item.label]))
  return defaults.map((item) => ({
    ...item,
    label: labelByTo.get(item.to) ?? item.label,
  }))
}

export default function HostLayout() {
  const { page: copy } = usePageContent('host-panel')
  const { pathname } = useLocation()
  const sidebar = copy.sidebar || {}
  const topbar = copy.topbar || {}
  const navItems = resolveNavItems(copy.navItems, DEFAULT_NAV).map((item) => ({
    ...item,
    icon: ICONS[item.to] ?? LayoutDashboard,
    end: item.to === '/host',
  }))
  const pageTitle = {
    '/host': topbar.pageTitle || 'Dashboard',
    '/host/cars': copy.vehicles?.title || 'Cars & vans',
    '/host/guesthouses': copy.stays?.title || 'Guesthouses',
    '/host/bookings': copy.bookings?.title || 'Bookings',
    '/host/integrations': copy.api?.title || 'API connections',
    '/host/settings': copy.settings?.title || 'Settings',
  }[pathname] || topbar.pageTitle || 'Dashboard'
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: sidebar.hostTag ?? 'Host panel' },
  })

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="host-panel-page host-account">
      <div className="host-shell">
        <aside className="host-sidebar">
          <div className="host-rail-brand">
            <span className="host-rail-mark" aria-hidden="true">H</span>
            <div>
              <p className="host-rail-name">MyTerraBook</p>
              <p className="host-rail-sub">{sidebar.hostTag ?? 'Host'}</p>
            </div>
          </div>
          <p className="host-rail-menu">{sidebar.menuLabel ?? 'MENU'}</p>
          <nav className="host-nav">
            {navItems.map((item) => (
              <NavLink key={item.to} to={item.to} end={item.end} className={({ isActive }) => (isActive ? 'active' : '')}>
                <item.icon size={16} />
                {item.label}
              </NavLink>
            ))}
          </nav>
          <div className="host-rail-help">
            <strong>{sidebar.supportTitle ?? 'Host support'}</strong>
            <span>{sidebar.supportLine ?? '+354 787 0707 · info@myterrabook.com'}</span>
          </div>
          <button type="button" className="host-rail-signout" onClick={handleLogout}>
            <LogOut size={16} />
            <span>{copy.signOutLabel ?? 'Sign out'}</span>
          </button>
        </aside>
        <main className="host-main">
          <div className="host-panel-topbar">
            <div className="host-panel-topbar__intro">
              <p className="host-panel-kicker">{topbar.accountRole ?? 'Host account'}</p>
              <h1>{pageTitle}</h1>
              <p className="host-panel-user">{user?.name}</p>
            </div>
            <button type="button" className="host-btn secondary host-panel-topbar__signout" onClick={handleLogout}>
              <LogOut size={16} />
              <span>{copy.signOutLabel ?? 'Sign out'}</span>
            </button>
          </div>
          <Outlet />
        </main>
      </div>
      <nav className="host-tabbar" aria-label="Host">
        {['/host', '/host/cars', '/host/bookings'].map((to) => {
          const item = navItems.find((entry) => entry.to === to)
          if (!item) return null
          return (
            <NavLink key={item.to} to={item.to} end={item.end} className={({ isActive }) => (isActive ? 'active' : '')}>
              <item.icon size={18} />
              <span>{item.label}</span>
            </NavLink>
          )
        })}
        <NavLink to="/host/settings" className={({ isActive }) => (isActive ? 'active' : '')}>
          <MoreHorizontal size={18} />
          <span>More</span>
        </NavLink>
      </nav>
    </div>
    </>
  )
}
