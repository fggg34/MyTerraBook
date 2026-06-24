import { useEffect, useState } from 'react'
import { api } from '../../api'
import { useToast } from '../../context/ToastContext'
import { formatCurrency } from '../../utils/format'

function formatDelta(cents, currency) {
  if (cents == null) return null
  const sign = cents >= 0 ? '+' : '−'
  return `${sign}${formatCurrency(Math.abs(cents) / 100, currency || 'EUR')}`
}

export default function BookingModificationSection({
  bookableKind,
  reference,
  customerEmail,
  orderId = null,
  isVehicle = true,
  pickupAt = null,
  dropoffAt = null,
  rentalOptionIds = [],
  className = '',
}) {
  const { toast } = useToast()
  const [requests, setRequests] = useState([])
  const [loading, setLoading] = useState(true)
  const [submitting, setSubmitting] = useState(false)
  const [previewing, setPreviewing] = useState(false)
  const [preview, setPreview] = useState(null)
  const [type, setType] = useState('modification')
  const [message, setMessage] = useState('')
  const [pickupDate, setPickupDate] = useState('')
  const [dropoffDate, setDropoffDate] = useState('')

  useEffect(() => {
    if (pickupAt) setPickupDate(pickupAt.slice(0, 10))
    if (dropoffAt) setDropoffDate(dropoffAt.slice(0, 10))
  }, [pickupAt, dropoffAt])

  const loadRequests = () => {
    if (!reference || !customerEmail) {
      setLoading(false)
      return
    }
    setLoading(true)
    api.get('/booking-change-requests', {
      params: {
        bookable_kind: bookableKind,
        reference,
        customer_email: customerEmail,
      },
    })
      .then((res) => setRequests(res.data.data || []))
      .catch(() => setRequests([]))
      .finally(() => setLoading(false))
  }

  useEffect(() => {
    loadRequests()
  }, [bookableKind, reference, customerEmail])

  const buildRequestedChanges = () => {
    if (type !== 'modification') return {}
    if (!isVehicle) {
      return {
        check_in: pickupDate || undefined,
        check_out: dropoffDate || undefined,
      }
    }
    return {
      pickup_at: pickupDate ? `${pickupDate}T${pickupAt?.slice(11, 16) || '10:00'}:00` : undefined,
      dropoff_at: dropoffDate ? `${dropoffDate}T${dropoffAt?.slice(11, 16) || '10:00'}:00` : undefined,
      rental_options: rentalOptionIds.length ? rentalOptionIds : undefined,
    }
  }

  const handlePreview = async () => {
    if (!orderId || type !== 'modification' || !isVehicle) return
    setPreviewing(true)
    try {
      const res = await api.post('/booking-change-requests/preview', {
        order_id: orderId,
        requested_changes: buildRequestedChanges(),
      })
      setPreview(res.data.data)
    } catch (err) {
      toast(err.response?.data?.message || 'Could not preview new total', 'error')
      setPreview(null)
    } finally {
      setPreviewing(false)
    }
  }

  const handleSubmit = async (e) => {
    e.preventDefault()
    if (!message.trim()) {
      toast('Please describe the change you need', 'error')
      return
    }
    setSubmitting(true)
    try {
      await api.post('/booking-change-requests', {
        bookable_kind: bookableKind,
        reference,
        customer_email: customerEmail,
        type,
        customer_message: message.trim(),
        requested_changes: type === 'modification' ? buildRequestedChanges() : undefined,
      })
      toast('Request sent — we will review and update your booking', 'success')
      setMessage('')
      setPreview(null)
      loadRequests()
    } catch (err) {
      toast(err.response?.data?.message || 'Could not send request', 'error')
    } finally {
      setSubmitting(false)
    }
  }

  if (!reference || !customerEmail) return null

  return (
    <section className={`booking-mod-section${className ? ` ${className}` : ''}`}>
      <div className="booking-mod-section__head">
        <h3>Modify or cancel this booking</h3>
        <p>Need to change dates, add or remove extras, or cancel? Send a request to the host team.</p>
      </div>

      <form className="booking-mod-form" onSubmit={handleSubmit}>
        <div className="booking-mod-type">
          <label className={`booking-mod-type__opt${type === 'modification' ? ' active' : ''}`}>
            <input type="radio" name="change-type" value="modification" checked={type === 'modification'} onChange={() => setType('modification')} />
            Request a modification
          </label>
          <label className={`booking-mod-type__opt${type === 'cancellation' ? ' active' : ''}`}>
            <input type="radio" name="change-type" value="cancellation" checked={type === 'cancellation'} onChange={() => setType('cancellation')} />
            Request cancellation
          </label>
        </div>

        {type === 'modification' && (
          <div className="booking-mod-dates">
            <div className="field">
              <label>{isVehicle ? 'New pick-up date' : 'New check-in'}</label>
              <input className="inp" type="date" value={pickupDate} onChange={(e) => setPickupDate(e.target.value)} />
            </div>
            <div className="field">
              <label>{isVehicle ? 'New drop-off date' : 'New check-out'}</label>
              <input className="inp" type="date" value={dropoffDate} onChange={(e) => setDropoffDate(e.target.value)} />
            </div>
            {isVehicle && orderId && (
              <button type="button" className="client-btn secondary" disabled={previewing} onClick={handlePreview}>
                {previewing ? 'Calculating…' : 'Preview new total'}
              </button>
            )}
            {preview && (
              <p className="booking-mod-preview">
                Estimated total: <strong>{preview.total_formatted}</strong>
                {preview.price_delta_cents !== 0 && (
                  <span> ({formatDelta(preview.price_delta_cents, preview.quote?.currency)})</span>
                )}
              </p>
            )}
          </div>
        )}

        <div className="field full">
          <label>
            {type === 'cancellation' ? 'Reason for cancellation' : 'What would you like to change?'}
            <span className="req"> *</span>
          </label>
          <textarea
            className="inp"
            rows={4}
            placeholder={type === 'cancellation'
              ? 'Please tell us why you need to cancel…'
              : 'e.g. extend rental by 2 days, add GPS, remove child seat…'}
            value={message}
            onChange={(e) => setMessage(e.target.value)}
            required
          />
        </div>

        <button type="submit" className="client-btn primary" disabled={submitting}>
          {submitting ? 'Sending…' : 'Send request to host'}
        </button>
      </form>

      {loading ? (
        <p className="booking-mod-history-empty">Loading requests…</p>
      ) : requests.length > 0 && (
        <div className="booking-mod-history">
          <h4>Your requests</h4>
          <ul>
            {requests.map((req) => (
              <li key={req.id} className={`booking-mod-history__item booking-mod-history__item--${req.status}`}>
                <div className="booking-mod-history__top">
                  <span className="booking-mod-history__type">{req.type === 'cancellation' ? 'Cancellation' : 'Modification'}</span>
                  <span className="booking-mod-history__status">{req.status}</span>
                </div>
                <p>{req.customer_message}</p>
                {req.price_delta_cents != null && req.status === 'pending' && (
                  <p className="booking-mod-history__delta">
                    Estimated price change: {formatDelta(req.price_delta_cents, req.pricing_after?.currency)}
                  </p>
                )}
                {req.admin_response && (
                  <p className="booking-mod-history__admin">Response: {req.admin_response}</p>
                )}
                <time>{new Date(req.created_at).toLocaleString()}</time>
              </li>
            ))}
          </ul>
        </div>
      )}
    </section>
  )
}
