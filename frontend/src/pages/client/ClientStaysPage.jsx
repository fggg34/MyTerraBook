import { useCallback, useEffect, useState } from 'react'
import { Link } from 'react-router-dom'
import { Home } from 'lucide-react'
import ClientStayCard from '../../components/client/ClientStayCard'
import { usePageContent } from '../../context/SiteContentContext'
import { useToast } from '../../context/ToastContext'
import { cancelMeGuestHouseBooking, getMeGuestHouseBookings } from '../../api/me'

export default function ClientStaysPage() {
  const { page: copy } = usePageContent('user-dashboard')
  const { page: panel } = usePageContent('client-panel')
  const stays = panel.stays || {}
  const actions = panel.actions || {}
  const { toast } = useToast()
  const [bookings, setBookings] = useState([])
  const [loading, setLoading] = useState(true)
  const [cancelLoading, setCancelLoading] = useState(false)
  const [error, setError] = useState(null)

  const load = useCallback(() => {
    setLoading(true)
    getMeGuestHouseBookings()
      .then((res) => setBookings(res.data.data || []))
      .catch((err) => setError(err.response?.data?.message || 'Could not load stays'))
      .finally(() => setLoading(false))
  }, [])

  useEffect(() => {
    load()
  }, [load])

  const handleCancel = async (booking) => {
    const confirmed = window.confirm(
      `Cancel your stay at ${booking.guest_house?.name || 'this property'}? This may be subject to the cancellation policy.`,
    )
    if (!confirmed) return

    setCancelLoading(true)
    try {
      await cancelMeGuestHouseBooking(booking.booking_reference, 'Cancelled by guest')
      toast('Booking cancelled', 'success')
      load()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not cancel booking', 'error')
    } finally {
      setCancelLoading(false)
    }
  }

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
        <h2>{stays.title || 'Stays'}</h2>
        <p>{stays.lead || 'Guesthouse nights you have booked.'}</p>
      </div>

      {!loading && bookings.length === 0 ? (
        <div className="client-empty">
          <div className="client-empty-icon">
            <Home size={28} />
          </div>
          <h3>{stays.emptyTitle || copy.emptyStays || 'No stays yet'}</h3>
          <p>{stays.emptyText || 'When you book a guesthouse, it will show up here.'}</p>
          <Link to="/guesthouses" className="client-btn primary">Browse guesthouses</Link>
        </div>
      ) : (
        <div className="client-cards">
          {bookings.map((booking) => (
            <ClientStayCard
              key={booking.id}
              booking={booking}
              onCancel={handleCancel}
              cancelLoading={cancelLoading}
              viewListingLabel={actions.viewListingLabel || copy.viewListingLabel || 'View listing'}
              cancelBookingLabel={actions.cancelBookingLabel || copy.cancelBookingLabel || 'Cancel booking'}
            />
          ))}
        </div>
      )}
    </div>
  )
}
