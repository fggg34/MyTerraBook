import { Search } from 'lucide-react'

const STATUS_OPTIONS = [
  { value: '', label: 'All statuses' },
  { value: 'pending', label: 'Pending' },
  { value: 'stand_by', label: 'Stand by' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'cancelled', label: 'Cancelled' },
  { value: 'completed', label: 'Completed' },
  { value: 'no_show', label: 'No show' },
]

const TYPE_OPTIONS = [
  { value: 'all', label: 'All listings' },
  { value: 'vehicle', label: 'Vehicles only' },
  { value: 'guesthouse', label: 'Guesthouses only' },
]

function resourceLabel(resource) {
  const type = resource.type === 'guesthouse' ? 'Guesthouse' : 'Vehicle'
  const city = resource.city ? ` · ${resource.city}` : ''
  return `${resource.title}${city} (${type})`
}

export default function AdminCalendarFilters({
  filters,
  onChange,
  resources = [],
  resourcesLoading = false,
}) {
  const selectedIds = Array.isArray(filters.resource_ids) ? filters.resource_ids : []
  const listingType = filters.listing_type || 'all'

  const listingOptions = resources.filter((resource) => {
    if (listingType === 'vehicle') return resource.type === 'vehicle'
    if (listingType === 'guesthouse') return resource.type === 'guesthouse'
    return true
  })

  return (
    <div className="admin-calendar-card">
      <div className="admin-calendar-filters">
        <label>
          Listing type
          <select
            value={listingType}
            onChange={(e) => onChange({
              listing_type: e.target.value,
              // Clear specific listing picks when switching type buckets.
              resource_ids: undefined,
            })}
          >
            {TYPE_OPTIONS.map((opt) => (
              <option key={opt.value} value={opt.value}>{opt.label}</option>
            ))}
          </select>
        </label>

        <label className="admin-calendar-filters__listing">
          Vehicle / listing
          <select
            value={selectedIds[0] || ''}
            disabled={resourcesLoading}
            onChange={(e) => {
              const value = e.target.value
              onChange({ resource_ids: value ? [value] : undefined })
            }}
          >
            <option value="">
              {resourcesLoading
                ? 'Loading listings…'
                : `All visible listings (${listingOptions.length})`}
            </option>
            {listingOptions.map((resource) => (
              <option key={resource.id} value={resource.id}>
                {resourceLabel(resource)}
              </option>
            ))}
          </select>
        </label>

        <label>
          Status
          <select
            value={filters.status || ''}
            onChange={(e) => onChange({ status: e.target.value || undefined })}
          >
            {STATUS_OPTIONS.map((opt) => (
              <option key={opt.value || 'all'} value={opt.value}>{opt.label}</option>
            ))}
          </select>
        </label>

        <label>
          City
          <input
            type="text"
            value={filters.city || ''}
            onChange={(e) => onChange({ city: e.target.value || undefined })}
            placeholder="Reykjavik"
          />
        </label>

        <label>
          Host ID
          <input
            type="number"
            min="1"
            value={filters.host_id || ''}
            onChange={(e) => onChange({ host_id: e.target.value || undefined })}
            placeholder="Host user ID"
          />
        </label>

        <label style={{ gridColumn: '1 / -1' }}>
          Search guest or host
          <div style={{ position: 'relative' }}>
            <Search
              size={16}
              style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: '#94a3b8' }}
            />
            <input
              type="search"
              value={filters.search || ''}
              onChange={(e) => onChange({ search: e.target.value || undefined })}
              placeholder="Name, reference, email"
              style={{ paddingLeft: '2rem', width: '100%' }}
            />
          </div>
        </label>
      </div>
    </div>
  )
}
