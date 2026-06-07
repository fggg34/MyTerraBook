import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Car } from 'lucide-react'
import ClientBookingCard from '../../components/client/ClientBookingCard'
import { PageLoader } from '../../components/ui/LoadingSpinner'
import { usePageContent } from '../../context/SiteContentContext'
import { getMeOrders } from '../../api/me'

export default function ClientBookingsPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    getMeOrders()
      .then((res) => setOrders(res.data.data || []))
      .catch((err) => setError(err.response?.data?.message || 'Could not load bookings'))
      .finally(() => setLoading(false))
  }, [])

  if (loading) return <PageLoader message="Loading your bookings…" />

  if (error) {
    return (
      <div className="client-empty">
        <p>{error}</p>
      </div>
    )
  }

  return (
    <div>
      <div className="client-page-head">
        <h2>{copy.title ?? 'My bookings'}</h2>
        <p>Car and campervan rentals you have booked through MyTerraBook.</p>
      </div>

      {orders.length === 0 ? (
        <div className="client-empty">
          <div className="client-empty-icon">
            <Car size={28} />
          </div>
          <h3>{copy.emptyBookings ?? 'No bookings yet'}</h3>
          <p>When you rent a car or campervan, it will show up here.</p>
          <Link to="/cars" className="client-btn primary">Browse vehicles</Link>
        </div>
      ) : (
        <div className="client-cards">
          {orders.map((order) => (
            <ClientBookingCard
              key={order.id}
              order={order}
              addToCalendarLabel={copy.addToCalendarLabel ?? 'Add to calendar'}
              viewListingLabel={copy.viewListingLabel ?? 'View vehicle'}
            />
          ))}
        </div>
      )}
    </div>
  )
}
