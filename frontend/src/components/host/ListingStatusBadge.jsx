export default function ListingStatusBadge({ status }) {
  const label = (status || 'draft').replace(/_/g, ' ')
  return <span className={`host-status ${status || 'draft'}`}>{label}</span>
}
