import { Calendar, Car, Download } from 'lucide-react'
import { Link } from 'react-router-dom'
import { api } from '../../api'
import StatusBadge from '../ui/StatusBadge'
import { useToast } from '../../context/ToastContext'
import { getMeOrderCalendarUrl } from '../../api/me'
import { formatCurrency, formatDate } from '../../utils/format'

export default function ClientBookingCard({ order, addToCalendarLabel = 'Add to calendar', viewListingLabel = 'View vehicle' }) {
  const { toast } = useToast()
  const vehicleName = order.car?.name || 'Vehicle rental'
  const listingPath = order.car?.slug ? `/cars/${order.car.slug}` : order.car?.id ? `/cars/${order.car.id}` : null

  const downloadCalendar = async () => {
    try {
      const res = await api.get(getMeOrderCalendarUrl(order.id), { responseType: 'blob' })
      const blobUrl = URL.createObjectURL(res.data)
      const link = document.createElement('a')
      link.href = blobUrl
      link.download = `booking-${order.reference}.ics`
      link.click()
      URL.revokeObjectURL(blobUrl)
    } catch {
      toast('Could not download calendar file', 'error')
    }
  }

  return (
    <article className="client-card">
      <div className="client-card-media">
        <div className="client-card-media-placeholder">
          <Car size={40} strokeWidth={1.5} />
        </div>
      </div>
      <div className="client-card-body">
        <div className="client-card-top">
          <div>
            <p className="client-card-ref">{order.reference}</p>
            <h3 className="client-card-title">{vehicleName}</h3>
          </div>
          <StatusBadge status={order.order_status} rentalStatus={order.rental_status} />
        </div>
        <div className="client-card-meta">
          <div className="client-card-meta-row">
            <Calendar size={15} />
            <span>{formatDate(order.pickup_at)} – {formatDate(order.dropoff_at)}</span>
          </div>
        </div>
        <div className="client-card-footer">
          <span className="client-card-price">
            {formatCurrency(order.total, order.currency || 'EUR')}
          </span>
          <div className="client-card-actions">
            {listingPath && (
              <Link to={listingPath} className="client-btn secondary">
                {viewListingLabel}
              </Link>
            )}
            <button type="button" className="client-btn secondary" onClick={downloadCalendar}>
              <Download size={14} />
              {addToCalendarLabel}
            </button>
          </div>
        </div>
      </div>
    </article>
  )
}
