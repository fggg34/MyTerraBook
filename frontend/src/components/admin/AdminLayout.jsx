import { Calendar, ExternalLink, LayoutDashboard, LogOut } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import PageHead from '../seo/PageHead'
import usePageSeo from '../../hooks/usePageSeo'
import '../../styles/admin-panel.css'

const NAV_ITEMS = [
  { to: '/admin', label: 'Dashboard', icon: LayoutDashboard, end: true },
  { to: '/admin/calendar', label: 'Calendar', icon: Calendar },
]

function filamentPanelUrl() {
  const explicit = import.meta.env.VITE_FILAMENT_URL
  if (explicit) return explicit
  const base = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
  return base.replace(/\/api\/?$/, '') + '/admin'
}

export default function AdminLayout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()
  const seo = usePageSeo(null, {
    skipPageSeo: true,
    robots: 'noindex',
    source: { title: 'Admin panel' },
  })

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  return (
    <>
      <PageHead {...seo} />
      <div className="admin-panel-page mx-auto max-w-[1600px] px-4 py-8 sm:px-6 lg:px-8">
        <div className="admin-panel-topbar">
          <div>
            <p className="text-sm font-semibold text-slate-500">Admin panel</p>
            <h1 className="text-2xl font-bold text-brand-950">Welcome, {user?.name}</h1>
          </div>
          <div className="admin-panel-topbar__actions">
            <a
              href={filamentPanelUrl()}
              target="_blank"
              rel="noreferrer"
              className="admin-btn secondary"
            >
              <ExternalLink size={16} />
              Filament
            </a>
            <button type="button" className="admin-btn secondary" onClick={handleLogout}>
              <LogOut size={16} />
              Sign out
            </button>
          </div>
        </div>

        <div className="admin-shell">
          <aside className="admin-sidebar">
            <h2>Manage</h2>
            <nav className="admin-nav">
              {NAV_ITEMS.map((item) => (
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
          <main className="admin-main">
            <Outlet />
          </main>
        </div>
      </div>
    </>
  )
}
