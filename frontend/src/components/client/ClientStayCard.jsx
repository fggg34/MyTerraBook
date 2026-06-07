import { Calendar, MapPin } from 'lucide-react'
import { Link } from 'react-router-dom'
import { resolveStorageUrl } from '../../api'
import StatusBadge from '../ui/StatusBadge'
import { formatCurrencyFromCents, formatDate } from '../../utils/format'

const CANCELLABLE = ['pending', 'confirmed']

export default function ClientStayCard({
  booking,
  onCancel,
  cancelLoading = false,
  viewListingLabel = 'View property',
  cancelBookingLabel = 'Cancel booking',
}) {
  const house = booking.guest_house
  const listingPath = house?.slug ? `/guesthouses/${house.slug}` : house?.id ? `/guesthouses/${house.id}` : null
  const thumbnail = house?.thumbnail ? resolveStorageUrl(house.thumbnail) : null
  const canCancel = CANCELLABLE.includes(booking.status) && !booking.cancelled_at

  return (
    <article className="client-card">
      <div className="client-card-media">
        {thumbnail ? (
          <img src={thumbnail} alt="" />
        ) : (
          <div className="client-card-media-placeholder">
            <MapPin size={40} strokeWidth={1.5} />
          </div>
        )}
      </div>
      <div className="client-card-body">
        <div className="client-card-top">
          <div>
            <p className="client-card-ref">{booking.booking_reference}</p>
            <h3 className="client-card-title">{house?.name || 'Guesthouse stay'}</h3>
          </div>
          <StatusBadge status={booking.status} />
        </div>
        <div className="client-card-meta">
          {house?.city && (
            <div className="client-card-meta-row">
              <MapPin size={15} />
              <span>{house.city}</span>
            </div>
          )}
          <div className="client-card-meta-row">
            <Calendar size={15} />
            <span>
              {formatDate(booking.check_in)} – {formatDate(booking.check_out)}
              {booking.nights ? ` · ${booking.nights} night${booking.nights === 1 ? '' : 's'}` : ''}
            </span>
          </div>
        </div>
        <div className="client-card-footer">
          <span className="client-card-price">
            {booking.total_formatted || formatCurrencyFromCents(booking.total_amount_cents)}
          </span>
          <div className="client-card-actions">
            {listingPath && (
              <Link to={listingPath} className="client-btn secondary">
                {viewListingLabel}
              </Link>
            )}
            {canCancel && (
              <button
                type="button"
                className="client-btn danger"
                onClick={() => onCancel(booking)}
                disabled={cancelLoading}
              >
                {cancelBookingLabel}
              </button>
            )}
          </div>
        </div>
      </div>
    </article>
  )
}
