import { Calendar, Car, Download, Home, MapPin, Tent } from 'lucide-react'
import { Link } from 'react-router-dom'
import { api, resolveStorageUrl } from '../../api'
import {
  cancelMeGuestHouseBooking,
  getMeGuestHouseContractUrl,
  getMeOrderCalendarUrl,
  getMeOrderContractUrl,
} from '../../api/me'
import { useAuth } from '../../context/AuthContext'
import { useToast } from '../../context/ToastContext'
import BookingModificationSection from '../booking/BookingModificationSection'
import { getListingPath, TYPE_LABELS } from '../../utils/clientHistory'
import { formatCurrency, formatDate } from '../../utils/format'
import StatusBadge from '../ui/StatusBadge'

const TYPE_ICONS = {
  car: Car,
  campervan: Tent,
  guesthouse: Home,
}

const CANCELLABLE = ['pending', 'confirmed']

async function downloadBlob(url, filename) {
  const res = await api.get(url, { responseType: 'blob' })
  const blobUrl = URL.createObjectURL(res.data)
  const link = document.createElement('a')
  link.href = blobUrl
  link.download = filename
  link.click()
  URL.revokeObjectURL(blobUrl)
}

export default function ClientHistoryCard({
  item,
  onCancelled,
  viewListingLabel = 'View listing',
  addToCalendarLabel = 'Add to calendar',
  downloadPdfLabel = 'Download PDF',
  cancelBookingLabel = 'Cancel booking',
}) {
  const { toast } = useToast()
  const { user } = useAuth()
  const TypeIcon = TYPE_ICONS[item.type] || Car
  const listingPath = getListingPath(item)
  const thumbnail = item.thumbnail ? resolveStorageUrl(item.thumbnail) : null
  const canCancel = item.kind === 'guesthouse'
    && CANCELLABLE.includes(item.status)
    && !item.cancelled_at

  const canModify = CANCELLABLE.includes(item.status)
    && !item.cancelled_at
    && item.status !== 'cancelled'

  const handleCalendar = async () => {
    try {
      await downloadBlob(getMeOrderCalendarUrl(item.id), `booking-${item.reference}.ics`)
    } catch {
      toast('Could not download calendar file', 'error')
    }
  }

  const handlePdf = async () => {
    const url = item.kind === 'order'
      ? getMeOrderContractUrl(item.id)
      : getMeGuestHouseContractUrl(item.reference)

    const filename = item.kind === 'order'
      ? `contract-${item.reference}.pdf`
      : `booking-${item.reference}.pdf`

    try {
      await downloadBlob(url, filename)
    } catch {
      toast('Could not download document', 'error')
    }
  }

  const handleCancel = async () => {
    const confirmed = window.confirm(
      `Cancel your stay at ${item.title}? This may be subject to the cancellation policy.`,
    )
    if (!confirmed) return

    try {
      await cancelMeGuestHouseBooking(item.reference, 'Cancelled by guest')
      toast('Booking cancelled', 'success')
      onCancelled?.()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not cancel booking', 'error')
    }
  }

  const dateLabel = item.kind === 'guesthouse' && item.nights
    ? `${formatDate(item.starts_at)} – ${formatDate(item.ends_at)} · ${item.nights} night${item.nights === 1 ? '' : 's'}`
    : `${formatDate(item.starts_at)} – ${formatDate(item.ends_at)}`

  const priceLabel = item.total_formatted
    || formatCurrency(item.total, item.currency || 'EUR')

  return (
    <article className={`client-history-card client-history-card--${item.type}`}>
      <div className="client-history-card__accent" aria-hidden />
      <div className="client-history-card__media">
        {thumbnail ? (
          <img src={thumbnail} alt="" />
        ) : (
          <div className="client-history-card__media-placeholder">
            <TypeIcon size={36} strokeWidth={1.5} />
          </div>
        )}
        <span className={`client-type-badge client-type-badge--${item.type}`}>
          {TYPE_LABELS[item.type] || item.type}
        </span>
      </div>

      <div className="client-history-card__body">
        <div className="client-history-card__top">
          <div>
            <p className="client-history-card__ref">{item.reference}</p>
            <h3 className="client-history-card__title">{item.title}</h3>
          </div>
          <StatusBadge status={item.status} rentalStatus={item.rental_status} />
        </div>

        <div className="client-history-card__meta">
          {item.subtitle && (
            <div className="client-history-card__meta-row">
              <MapPin size={15} />
              <span>{item.subtitle}</span>
            </div>
          )}
          <div className="client-history-card__meta-row">
            <Calendar size={15} />
            <span>{dateLabel}</span>
          </div>
        </div>

        <div className="client-history-card__footer">
          <span className="client-history-card__price">{priceLabel}</span>
          <div className="client-history-card__actions">
            {listingPath && (
              <Link to={listingPath} className="client-btn secondary">
                {viewListingLabel}
              </Link>
            )}
            {item.downloads?.calendar && (
              <button type="button" className="client-btn secondary" onClick={handleCalendar}>
                <Download size={14} />
                {addToCalendarLabel}
              </button>
            )}
            {item.downloads?.contract && (
              <button type="button" className="client-btn secondary" onClick={handlePdf}>
                <Download size={14} />
                {downloadPdfLabel}
              </button>
            )}
            {canCancel && (
              <button type="button" className="client-btn danger" onClick={handleCancel}>
                {cancelBookingLabel}
              </button>
            )}
          </div>
        </div>

        {canModify && user?.email && (
          <BookingModificationSection
            className="client-history-card__mod"
            bookableKind={item.kind === 'order' ? 'order' : 'guesthouse'}
            reference={item.reference}
            customerEmail={user.email}
            orderId={item.kind === 'order' ? item.id : null}
            isVehicle={item.kind === 'order'}
            pickupAt={item.starts_at}
            dropoffAt={item.ends_at}
          />
        )}
      </div>
    </article>
  )
}
