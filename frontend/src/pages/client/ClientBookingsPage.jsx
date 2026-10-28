import { useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Car } from 'lucide-react'
import ClientBookingCard from '../../components/client/ClientBookingCard'
import { usePageContent } from '../../context/SiteContentContext'
import { getMeOrders } from '../../api/me'

export default function ClientBookingsPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const { page: panel } = usePageContent('client-panel')
  const trips = panel.trips || {}
  const actions = panel.actions || {}
  const [orders, setOrders] = useState([])
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    getMeOrders()
      .then((res) => setOrders(res.data.data || []))
      .catch((err) => setError(err.response?.data?.message || 'Could not load bookings'))
      .finally(() => setLoading(false))
  }, [])

  if (error && !loading) {
    return (
      <div className="client-empty">
        <p>{error}</p>
      </div>
    )
  }

  return (
    <div>
      <div className="client-page-head">
        <h2>{trips.title || copy.title || 'My trips'}</h2>
        <p>{trips.lead || 'Campervans, cars and guesthouses you have booked.'}</p>
      </div>

      {!loading && orders.length === 0 ? (
        <div className="client-empty">
          <div className="client-empty-icon">
            <Car size={28} />
          </div>
          <h3>{trips.emptyTitle || copy.emptyBookings || 'No trips yet'}</h3>
          <p>{trips.emptyText || 'When you book a campervan, car or guesthouse, it will show up here.'}</p>
          <Link to={trips.browseHref || '/campervans'} className="client-btn primary">{trips.browseLabel || 'Find a ride'}</Link>
        </div>
      ) : (
        <div className="client-cards">
          {orders.map((order) => (
            <ClientBookingCard
              key={order.id}
              order={order}
              addToCalendarLabel={actions.addToCalendarLabel || copy.addToCalendarLabel || 'Add to calendar'}
              viewListingLabel={actions.viewListingLabel || copy.viewListingLabel || 'View listing'}
            />
          ))}
        </div>
      )}
    </div>
  )
}
