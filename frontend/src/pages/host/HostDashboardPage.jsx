import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { getHostDashboard } from '../../api/host'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { formatCurrency } from '../../utils/format'

export default function HostDashboardPage() {
  const [stats, setStats] = useState(null)
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    getHostDashboard()
      .then((res) => setStats(res.data.data))
      .catch(() => setStats(null))
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <PageLoader message="Loading host dashboard…" />
  if (!stats) return <p>Could not load dashboard.</p>

  return (
    <div>
      <div className="host-cards">
        <div className="host-card">
          <span>Live guesthouses</span>
          <strong>{stats.guest_houses.live}</strong>
        </div>
        <div className="host-card">
          <span>Live vehicles</span>
          <strong>{stats.cars.live}</strong>
        </div>
        <div className="host-card">
          <span>Pending review</span>
          <strong>{stats.guest_houses.pending_review + stats.cars.pending_review}</strong>
        </div>
        <div className="host-card">
          <span>Pending bookings</span>
          <strong>{stats.bookings.pending_car_orders + stats.bookings.pending_guesthouse_bookings}</strong>
        </div>
        <div className="host-card">
          <span>Car revenue</span>
          <strong>{formatCurrency(stats.revenue_cents.car_orders / 100)}</strong>
        </div>
        <div className="host-card">
          <span>Stay revenue</span>
          <strong>{formatCurrency(stats.revenue_cents.guesthouse_bookings / 100)}</strong>
        </div>
      </div>
      <div className="host-actions">
        <Link to="/host/guesthouses/new" className="host-btn primary">Add guesthouse</Link>
        <Link to="/host/cars/new" className="host-btn secondary">Add vehicle</Link>
        <Link to="/host/bookings" className="host-btn secondary">View bookings</Link>
      </div>
    </div>
  )
}
