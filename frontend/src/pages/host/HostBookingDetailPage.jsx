import { useEffect, useState } from 'react'
import { Link, useParams } from 'react-router-dom'
import { ArrowLeft, Download, ExternalLink, Mail, Phone } from 'lucide-react'
import { api } from '../../api'
import {
  applyHostBookingChangeRequest,
  getHostCarBooking,
  getHostGuestHouseBooking,
  rejectHostBookingChangeRequest,
} from '../../api/host'
import StatusBadge from '../../components/ui/StatusBadge'
import { useToast } from '../../context/ToastContext'
import { formatCurrency, formatDate, formatDateTime } from '../../utils/format'

function DetailSection({ title, children }) {
  return (
    <section className="host-booking-detail__section">
      <h3>{title}</h3>
      {children}
    </section>
  )
}

function DetailGrid({ items }) {
  return (
    <dl className="host-booking-detail__grid">
      {items.filter((item) => item.value != null && item.value !== '').map((item) => (
        <div key={item.label} className="host-booking-detail__row">
          <dt>{item.label}</dt>
          <dd>{item.value}</dd>
        </div>
      ))}
    </dl>
  )
}

function ChangeRequestsList({
  requests,
  onApply,
  onReject,
  processingId,
}) {
  const [notes, setNotes] = useState({})

  if (!requests?.length) return null

  const updateNote = (id, value) => {
    setNotes((prev) => ({ ...prev, [id]: value }))
  }

  return (
    <DetailSection title="Modification requests">
      <p className="host-booking-detail__changes-lead">
        Review guest requests below. Approving will update the booking and recalculate totals where applicable.
      </p>
      <ul className="host-booking-detail__changes">
        {requests.map((req) => {
          const isPending = req.status === 'pending'
          const isProcessing = processingId === req.id

          return (
            <li key={req.id} className={`host-booking-detail__change host-booking-detail__change--${req.status}`}>
              <div className="host-booking-detail__change-top">
                <strong>{req.type === 'cancellation' ? 'Cancellation' : 'Modification'}</strong>
                <span>{req.status}</span>
              </div>
              <p>{req.customer_message}</p>
              {req.requested_changes && Object.keys(req.requested_changes).length > 0 && (
                <DetailGrid items={Object.entries(req.requested_changes)
                  .filter(([, value]) => value != null && value !== '')
                  .map(([key, value]) => ({
                    label: key.replace(/_/g, ' '),
                    value: String(value).slice(0, 10).includes('-') ? formatDateTime(String(value)) : String(value),
                  }))} />
              )}
              {req.price_delta_cents != null && (
                <p className="host-booking-detail__change-delta">
                  Estimated price change: {req.price_delta_cents >= 0 ? '+' : '−'}
                  {formatCurrency(Math.abs(req.price_delta_cents) / 100)}
                </p>
              )}
              {req.admin_response && <p className="host-booking-detail__change-admin">Your response: {req.admin_response}</p>}
              <time>{formatDateTime(req.created_at)}</time>

              {isPending && (
                <div className="host-booking-detail__change-actions">
                  <label className="host-booking-detail__change-note">
                    Message to guest (optional for approval, required for rejection)
                    <textarea
                      className="inp"
                      rows={2}
                      value={notes[req.id] || ''}
                      onChange={(e) => updateNote(req.id, e.target.value)}
                      placeholder="e.g. Approved — your new dates are confirmed."
                      disabled={isProcessing}
                    />
                  </label>
                  <div className="host-booking-detail__change-buttons">
                    <button
                      type="button"
                      className="host-btn primary"
                      disabled={isProcessing}
                      onClick={() => onApply(req.id, notes[req.id]?.trim() || undefined)}
                    >
                      {isProcessing ? 'Applying…' : req.type === 'cancellation' ? 'Approve cancellation' : 'Approve changes'}
                    </button>
                    <button
                      type="button"
                      className="host-btn danger"
                      disabled={isProcessing}
                      onClick={() => {
                        const reason = notes[req.id]?.trim()
                        if (!reason) {
                          onReject(req.id, null)
                          return
                        }
                        onReject(req.id, reason)
                      }}
                    >
                      Reject
                    </button>
                  </div>
                </div>
              )}
            </li>
          )
        })}
      </ul>
    </DetailSection>
  )
}

function CarBookingDetail({ booking, onPdf, onApplyChange, onRejectChange, processingId }) {
  const reference = booking.reference

  return (
    <>
      <div className="host-booking-detail__head">
        <div>
          <p className="host-booking-detail__ref">{reference}</p>
          <h2>{booking.car?.name || 'Vehicle rental'}</h2>
          <div className="host-booking-detail__badges">
            <StatusBadge status={booking.order_status} />
            {booking.rental_status && <StatusBadge rentalStatus={booking.rental_status} />}
          </div>
        </div>
        <div className="host-booking-detail__actions">
          {booking.confirmation_url && (
            <a href={booking.confirmation_url} target="_blank" rel="noopener noreferrer" className="host-btn secondary">
              <ExternalLink size={14} />
              Guest confirmation
            </a>
          )}
          {booking.order_status === 'confirmed' && (
            <button type="button" className="host-btn secondary" onClick={() => onPdf(`/host/bookings/cars/${booking.id}/contract.pdf`, `contract-${reference}.pdf`)}>
              <Download size={14} />
              Contract PDF
            </button>
          )}
        </div>
      </div>

      <div className="host-booking-detail__layout">
        <DetailSection title="Trip">
          <DetailGrid items={[
            { label: 'Pick-up', value: formatDateTime(booking.pickup_at) },
            { label: 'Pick-up location', value: booking.pickup_location?.name },
            { label: 'Drop-off', value: formatDateTime(booking.dropoff_at) },
            { label: 'Drop-off location', value: booking.dropoff_location?.name },
            { label: 'Protection plan', value: booking.price_type?.name },
            { label: 'Booked on', value: formatDateTime(booking.created_at) },
          ]} />
        </DetailSection>

        <DetailSection title="Guest">
          <DetailGrid items={[
            { label: 'Name', value: booking.customer_name },
            { label: 'Email', value: booking.customer_email },
            { label: 'Phone', value: booking.customer_phone },
            { label: 'Country', value: booking.customer_country },
          ]} />
          {booking.customer_email && (
            <div className="host-booking-detail__contact">
              <a href={`mailto:${booking.customer_email}`} className="host-btn secondary">
                <Mail size={14} />
                Email guest
              </a>
              {booking.customer_phone && (
                <a href={`tel:${booking.customer_phone}`} className="host-btn secondary">
                  <Phone size={14} />
                  Call guest
                </a>
              )}
            </div>
          )}
        </DetailSection>

        <DetailSection title="Pricing">
          <DetailGrid items={[
            { label: 'Base rental', value: formatCurrency(booking.base_rental_cents / 100, booking.currency) },
            { label: 'Extras', value: formatCurrency(booking.extras_cents / 100, booking.currency) },
            { label: 'Fees', value: formatCurrency(booking.fees_cents / 100, booking.currency) },
            { label: 'Discount', value: booking.discount_cents ? `−${formatCurrency(booking.discount_cents / 100, booking.currency)}` : '—' },
            { label: 'Tax', value: formatCurrency(booking.tax_cents / 100, booking.currency) },
            { label: 'Total', value: booking.total_formatted },
          ]} />
          {booking.line_items?.length > 0 && (
            <ul className="host-booking-detail__lines">
              {booking.line_items.map((line, index) => (
                <li key={`${line.kind}-${index}`}>
                  <span>{line.label}{line.quantity ? ` × ${line.quantity}` : ''}</span>
                  <span>{line.amount_formatted} {booking.currency}</span>
                </li>
              ))}
            </ul>
          )}
        </DetailSection>

        {(booking.notes || booking.custom_field_values) && (
          <DetailSection title="Notes & extra details">
            {booking.notes && <p>{booking.notes}</p>}
            {booking.custom_field_values && Object.keys(booking.custom_field_values).length > 0 && (
              <DetailGrid items={Object.entries(booking.custom_field_values).map(([key, value]) => ({
                label: key.replace(/_/g, ' '),
                value: String(value),
              }))} />
            )}
          </DetailSection>
        )}

        <ChangeRequestsList
          requests={booking.change_requests}
          onApply={onApplyChange}
          onReject={onRejectChange}
          processingId={processingId}
        />
      </div>
    </>
  )
}

function GuestHouseBookingDetail({ booking, onPdf, onApplyChange, onRejectChange, processingId }) {
  const reference = booking.booking_reference

  return (
    <>
      <div className="host-booking-detail__head">
        <div>
          <p className="host-booking-detail__ref">{reference}</p>
          <h2>{booking.guest_house?.name || 'Guesthouse stay'}</h2>
          <div className="host-booking-detail__badges">
            <StatusBadge status={booking.status} />
          </div>
        </div>
        <div className="host-booking-detail__actions">
          {booking.confirmation_url && (
            <a href={booking.confirmation_url} target="_blank" rel="noopener noreferrer" className="host-btn secondary">
              <ExternalLink size={14} />
              Guest confirmation
            </a>
          )}
          {['confirmed', 'completed'].includes(booking.status) && (
            <button type="button" className="host-btn secondary" onClick={() => onPdf(`/host/bookings/guest-houses/${booking.id}/contract.pdf`, `contract-${reference}.pdf`)}>
              <Download size={14} />
              Contract PDF
            </button>
          )}
        </div>
      </div>

      <div className="host-booking-detail__layout">
        <DetailSection title="Stay">
          <DetailGrid items={[
            { label: 'Check-in', value: formatDate(booking.check_in) },
            { label: 'Check-out', value: formatDate(booking.check_out) },
            { label: 'Nights', value: booking.nights },
            { label: 'Guests', value: booking.guests_count },
            { label: 'Property', value: booking.guest_house?.name },
            { label: 'Booked on', value: formatDateTime(booking.created_at) },
          ]} />
        </DetailSection>

        <DetailSection title="Guest">
          <DetailGrid items={[
            { label: 'Name', value: booking.guest_name },
            { label: 'Email', value: booking.guest_email },
            { label: 'Phone', value: booking.guest_phone },
          ]} />
          {booking.guest_email && (
            <div className="host-booking-detail__contact">
              <a href={`mailto:${booking.guest_email}`} className="host-btn secondary">
                <Mail size={14} />
                Email guest
              </a>
              {booking.guest_phone && (
                <a href={`tel:${booking.guest_phone}`} className="host-btn secondary">
                  <Phone size={14} />
                  Call guest
                </a>
              )}
            </div>
          )}
        </DetailSection>

        <DetailSection title="Pricing">
          <DetailGrid items={[
            { label: 'Accommodation', value: formatCurrency(booking.base_total / 100) },
            { label: 'Cleaning fee', value: formatCurrency(booking.cleaning_fee / 100) },
            { label: 'Security deposit', value: formatCurrency(booking.security_deposit / 100) },
            { label: 'Discount', value: booking.discount_amount ? `−${formatCurrency(booking.discount_amount / 100)}` : '—' },
            { label: 'Tax', value: formatCurrency(booking.tax_amount / 100) },
            { label: 'Total', value: booking.total_formatted },
          ]} />
        </DetailSection>

        {booking.special_requests && (
          <DetailSection title="Special requests">
            <p>{booking.special_requests}</p>
          </DetailSection>
        )}

        <ChangeRequestsList
          requests={booking.change_requests}
          onApply={onApplyChange}
          onReject={onRejectChange}
          processingId={processingId}
        />
      </div>
    </>
  )
}

export default function HostBookingDetailPage({ kind }) {
  const { id } = useParams()
  const { toast } = useToast()
  const [booking, setBooking] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)
  const [processingId, setProcessingId] = useState(null)

  const load = () => {
    setLoading(true)
    const request = kind === 'car'
      ? getHostCarBooking(id)
      : getHostGuestHouseBooking(id)

    request
      .then((res) => {
        setBooking(res.data.data)
        setError(null)
      })
      .catch((err) => {
        setBooking(null)
        setError(err.response?.data?.message || 'Could not load booking details.')
      })
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    load()
  }, [id, kind])

  const openPdf = async (url, filename) => {
    try {
      const res = await api.get(url, { responseType: 'blob' })
      const blobUrl = URL.createObjectURL(res.data)
      const link = document.createElement('a')
      link.href = blobUrl
      link.download = filename
      link.click()
      URL.revokeObjectURL(blobUrl)
    } catch {
      toast('Could not download contract', 'error')
    }
  }

  const handleApplyChange = async (changeId, note) => {
    setProcessingId(changeId)
    try {
      await applyHostBookingChangeRequest(changeId, note)
      toast('Request approved and booking updated', 'success')
      load()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not approve request', 'error')
    } finally {
      setProcessingId(null)
    }
  }

  const handleRejectChange = async (changeId, reason) => {
    if (!reason) {
      toast('Please add a message explaining why you are rejecting this request', 'error')
      return
    }
    setProcessingId(changeId)
    try {
      await rejectHostBookingChangeRequest(changeId, reason)
      toast('Request rejected', 'success')
      load()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not reject request', 'error')
    } finally {
      setProcessingId(null)
    }
  }

  const detailProps = {
    onPdf: openPdf,
    onApplyChange: handleApplyChange,
    onRejectChange: handleRejectChange,
    processingId,
  }

  const backTo = kind === 'car' ? '/host/bookings' : '/host/bookings'

  return (
    <div className="host-booking-detail">
      <Link to={backTo} className="host-booking-detail__back">
        <ArrowLeft size={16} />
        Back to bookings
      </Link>

      {loading && <p>Loading booking…</p>}
      {error && !loading && <p className="host-overview-error">{error}</p>}

      {!loading && booking && (
        kind === 'car'
          ? <CarBookingDetail booking={booking} {...detailProps} />
          : <GuestHouseBookingDetail booking={booking} {...detailProps} />
      )}
    </div>
  )
}
