import { Car, Calendar, Home, LayoutDashboard, LogOut, Settings } from 'lucide-react'
import { NavLink, Outlet, useNavigate } from 'react-router-dom'
import { useAuth } from '../../context/AuthContext'
import '../../styles/host-panel.css'

const navItems = [
  { to: '/host', label: 'Dashboard', icon: LayoutDashboard, end: true },
  { to: '/host/guesthouses', label: 'Guesthouses', icon: Home },
  { to: '/host/cars', label: 'Cars & vans', icon: Car },
  { to: '/host/bookings', label: 'Bookings', icon: Calendar },
  { to: '/host/settings', label: 'Settings', icon: Settings },
]

export default function HostLayout() {
  const { user, logout } = useAuth()
  const navigate = useNavigate()

  const handleLogout = async () => {
    await logout()
    navigate('/')
  }

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <div className="mb-6 flex items-center justify-between gap-4">
        <div>
          <p className="text-sm font-semibold text-slate-500">Host panel</p>
          <h1 className="text-2xl font-bold text-brand-950">Welcome, {user?.name}</h1>
        </div>
        <button type="button" className="host-btn secondary" onClick={handleLogout}>
          <LogOut size={16} className="mr-2 inline" />
          Sign out
        </button>
      </div>
      <div className="host-shell">
        <aside className="host-sidebar">
          <h2>Manage</h2>
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
  )
}
