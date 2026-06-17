const ACTIONABLE_STATUSES = new Set(['draft', 'pending_review', 'rejected'])

export function isActionableListingStatus(status) {
  return ACTIONABLE_STATUSES.has(status || 'draft')
}

export default function ListingStatusBadge({ status }) {
  if (!isActionableListingStatus(status)) return null

  const label = (status || 'draft').replace(/_/g, ' ')
  return <span className={`host-status ${status || 'draft'}`}>{label}</span>
}
