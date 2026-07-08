export default function AdminCalendarAlertsPanel({ alerts, loading }) {
  if (loading) {
    return (
      <div className="admin-calendar-card admin-calendar-skeleton">
        <div className="admin-calendar-skeleton__bar" />
      </div>
    )
  }

  if (!alerts) return null

  const pending = alerts.pending || []
  const conflicts = alerts.conflicts || []

  if (!pending.length && !conflicts.length) {
    return null
  }

  return (
    <div className="admin-calendar-alerts">
      {pending.length > 0 && (
        <div className="admin-calendar-alert-box">
          <h3>Pending approvals ({alerts.pendingCount ?? pending.length})</h3>
          <ul>
            {pending.slice(0, 5).map((item) => (
              <li key={item.id}>{item.title}</li>
            ))}
          </ul>
        </div>
      )}
      {conflicts.length > 0 && (
        <div className="admin-calendar-alert-box conflict">
          <h3>Booking conflicts ({alerts.conflictCount ?? conflicts.length})</h3>
          <ul>
            {conflicts.slice(0, 5).map((item, index) => (
              <li key={`${item.resourceId}-${index}`}>
                {item.resourceId} on {item.date}
              </li>
            ))}
          </ul>
        </div>
      )}
    </div>
  )
}
