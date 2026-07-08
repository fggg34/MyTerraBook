import { X } from 'lucide-react'
import StatusBadge from '../../ui/StatusBadge'
import { formatCurrencyFromCents, formatDateTime } from '../../../utils/format'

function DetailRow({ label, value }) {
  if (!value) return null
  return (
    <div className="admin-calendar-detail-panel__row">
      <span style={{ color: '#64748b' }}>{label}</span>
      <span style={{ fontWeight: 600, textAlign: 'right' }}>{value}</span>
    </div>
  )
}

export default function AdminReservationDetailPanel({ event, loading, onClose }) {
  if (!event && !loading) return null

  const props = event?.extendedProps || {}
  const currency = props.currency || 'EUR'

  return (
    <>
      <button
        type="button"
        className="admin-calendar-detail-backdrop"
        aria-label="Close details"
        onClick={onClose}
      />
      <aside className="admin-calendar-detail-panel" aria-label="Reservation details">
        <div className="admin-calendar-detail-panel__header">
          <div>
            <p style={{ fontSize: '0.75rem', color: '#64748b', margin: 0 }}>
              {event?.type === 'guesthouse' ? 'Guesthouse booking' : 'Vehicle rental'}
            </p>
            <h2 style={{ margin: '0.25rem 0 0', fontSize: '1.125rem' }}>
              {props.reference || 'Reservation'}
            </h2>
          </div>
          <button type="button" className="admin-btn ghost" onClick={onClose} aria-label="Close">
            <X size={18} />
          </button>
        </div>

        <div className="admin-calendar-detail-panel__body">
          {loading && <p>Loading details...</p>}

          {!loading && event && (
            <>
              <div style={{ marginBottom: '1rem', display: 'flex', gap: '0.5rem', flexWrap: 'wrap' }}>
                <StatusBadge status={event.status} rentalStatus={event.subStatus} />
                {event.hasConflict && (
                  <span className="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">
                    Conflict
                  </span>
                )}
              </div>

              <section className="admin-calendar-detail-panel__section">
                <h3>Guest</h3>
                <DetailRow label="Name" value={props.guestName} />
                <DetailRow label="Email" value={props.guestEmail} />
                <DetailRow label="Phone" value={props.guestPhone} />
              </section>

              <section className="admin-calendar-detail-panel__section">
                <h3>Host & listing</h3>
                <DetailRow label="Host" value={props.hostName} />
                <DetailRow label="Listing" value={props.listingName} />
                <DetailRow label="City" value={props.city} />
              </section>

              <section className="admin-calendar-detail-panel__section">
                <h3>Dates</h3>
                <DetailRow label="Start" value={formatDateTime(event.start)} />
                <DetailRow label="End" value={formatDateTime(event.end)} />
                {props.nights != null && <DetailRow label="Nights" value={String(props.nights)} />}
              </section>

              <section className="admin-calendar-detail-panel__section">
                <h3>Payment</h3>
                <DetailRow label="Status" value={event.paymentStatus} />
                <DetailRow
                  label="Total"
                  value={formatCurrencyFromCents(props.totalCents ?? 0, currency)}
                />
              </section>

              {props.notes && (
                <section className="admin-calendar-detail-panel__section">
                  <h3>Notes</h3>
                  <p style={{ fontSize: '0.875rem', color: '#334155', margin: 0 }}>{props.notes}</p>
                </section>
              )}
            </>
          )}
        </div>
      </aside>
    </>
  )
}
