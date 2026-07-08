import { formatCurrencyFromCents } from '../../../utils/format'

export default function AdminCalendarSummaryBar({ summary, loading }) {
  if (loading) {
    return (
      <div className="admin-calendar-card admin-calendar-skeleton">
        <div className="admin-calendar-skeleton__bar" />
      </div>
    )
  }

  if (!summary) return null

  const items = [
    { label: 'Reservations', value: summary.totalReservations ?? 0 },
    { label: 'Revenue', value: formatCurrencyFromCents(summary.revenueCents ?? 0) },
    { label: 'Occupancy', value: `${summary.occupancyRate ?? 0}%` },
    { label: 'Pending', value: summary.pendingApprovals ?? 0 },
  ]

  return (
    <div className="admin-calendar-card">
      <div className="admin-calendar-summary">
        {items.map((item) => (
          <div key={item.label} className="admin-calendar-summary__item">
            <div className="admin-calendar-summary__label">{item.label}</div>
            <div className="admin-calendar-summary__value">{item.value}</div>
          </div>
        ))}
      </div>
    </div>
  )
}
