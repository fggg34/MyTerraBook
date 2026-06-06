import { BarChart3, Car, ExternalLink, TrendingUp } from 'lucide-react'
import { useEffect, useState } from 'react'
import { api } from '../api'
import { usePageContent } from '../context/SiteContentContext'
import { PageLoader } from '../components/ui/LoadingSpinner'
import { formatCurrency } from '../utils/format'

function filamentPanelUrl() {
  const explicit = import.meta.env.VITE_FILAMENT_URL
  if (explicit) return explicit
  const base = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
  return base.replace(/\/api\/?$/, '') + '/admin'
}

export default function AdminDashboardPage() {
  const { page: copy } = usePageContent('admin-dashboard')
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    api
      .get('/admin/stats')
      .then((res) => setStats(res.data))
      .catch((err) => {
        setError(
          err.response?.data?.message ||
            'Could not load admin stats. Ensure you are logged in as admin.',
        )
      })
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <PageLoader message="Loading admin dashboard…" />

  if (error) {
    return (
      <div className="mx-auto max-w-lg px-4 py-16">
        <div className="rounded-xl border border-red-200 bg-red-50 p-6 text-center">
          <p className="text-red-700">{error}</p>
        </div>
      </div>
    )
  }

  const statsLabels = copy.statsLabels ?? {}
  const cards = [
    { label: statsLabels.orders ?? 'Total Orders', value: stats.total_orders ?? stats.total_bookings, icon: BarChart3 },
    { label: statsLabels.revenue ?? 'Revenue', value: formatCurrency(stats.revenue), icon: TrendingUp },
    { label: statsLabels.activeRentals ?? 'Active Rentals', value: stats.active_rentals, icon: Car },
  ]

  return (
    <div className="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
      <h1 className="section-title">{copy.title ?? 'Admin Dashboard'}</h1>
      <p className="section-subtitle">Overview of your rental business performance.</p>

      <div className="mt-8 grid gap-6 sm:grid-cols-3">
        {cards.map(({ label, value, icon: Icon }) => (
          <article key={label} className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
            <div className="flex items-center justify-between">
              <p className="text-sm font-medium text-slate-500">{label}</p>
              <div className="flex h-10 w-10 items-center justify-center rounded-lg bg-accent/10 text-accent">
                <Icon className="h-5 w-5" aria-hidden />
              </div>
            </div>
            <p className="mt-3 text-3xl font-bold text-brand-950">{value}</p>
          </article>
        ))}
      </div>

      <div className="mt-8 rounded-xl border border-slate-200 bg-white p-6 shadow-card">
        <h2 className="text-lg font-bold text-brand-950">Filament Admin Panel</h2>
        <p className="mt-2 text-sm text-slate-600">
          Full CRUD for cars, locations, pricing, coupons, and orders is available in the Filament panel.
        </p>
        <a
          href={filamentPanelUrl()}
          target="_blank"
          rel="noreferrer"
          className="btn-primary mt-4 inline-flex"
        >
          {copy.filamentLink ?? 'Open Filament Admin'}
          <ExternalLink className="h-4 w-4" aria-hidden />
        </a>
      </div>
    </div>
  )
}
