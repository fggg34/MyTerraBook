import { useEffect, useState } from 'react'
import { api } from '../api'

function filamentPanelUrl() {
  const explicit = import.meta.env.VITE_FILAMENT_URL
  if (explicit) {
    return explicit
  }
  const base = import.meta.env.VITE_API_URL || 'http://127.0.0.1:8000/api'
  return base.replace(/\/api\/?$/, '') + '/admin'
}

export default function AdminDashboardPage() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    setLoading(true)
    setError(null)
    api
      .get('/admin/stats')
      .then((res) => {
        setStats(res.data)
        setError(null)
      })
      .catch((err) => {
        setStats(null)
        setError(
          err.response?.data?.message ||
            err.message ||
            'Could not load admin stats. Check that you are logged in as admin and the API is running.',
        )
      })
      .finally(() => setLoading(false))
  }, [])

  if (loading) {
    return <p className="muted">Loading admin stats…</p>
  }

  if (error) {
    return (
      <section className="card">
        <h2>Admin</h2>
        <p className="error-text">{error}</p>
      </section>
    )
  }

  if (!stats) {
    return <p>No stats returned.</p>
  }

  return (
    <section>
      <div className="card" style={{ marginBottom: '1.5rem' }}>
        <h2>Admin overview</h2>
        <p className="muted">
          Full CRUD for cars, locations, add-ons, pricing, coupons, and orders is in the{' '}
          <strong>Filament</strong> panel (same database):{' '}
          <a href={filamentPanelUrl()} target="_blank" rel="noreferrer">
            {filamentPanelUrl()}
          </a>{' '}
          — log in with your admin user (session login, not the API token). This page only shows API summary
          stats; with a Sanctum Bearer token you can also call <code>/api/admin/orders/export.csv</code> and other{' '}
          <code>/api/admin/…</code> endpoints.
        </p>
      </div>
      <div className="grid">
        <article className="card">
          <h3>Total orders</h3>
          <p>{stats.total_orders ?? stats.total_bookings}</p>
        </article>
        <article className="card">
          <h3>Revenue</h3>
          <p>${stats.revenue}</p>
        </article>
        <article className="card">
          <h3>Active rentals</h3>
          <p>{stats.active_rentals}</p>
        </article>
      </div>
    </section>
  )
}
