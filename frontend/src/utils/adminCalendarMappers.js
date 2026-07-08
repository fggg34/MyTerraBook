const STATUS_COLORS = {
  pending: { vehicle: '#f59e0b', guesthouse: '#d97706' },
  stand_by: { vehicle: '#8b5cf6', guesthouse: '#8b5cf6' },
  confirmed: { vehicle: '#2563eb', guesthouse: '#059669' },
  cancelled: { vehicle: '#9ca3af', guesthouse: '#9ca3af' },
  completed: { vehicle: '#6b7280', guesthouse: '#6b7280' },
  no_show: { vehicle: '#dc2626', guesthouse: '#dc2626' },
}

export function eventStatusColor(status, type = 'vehicle') {
  const palette = STATUS_COLORS[status] || STATUS_COLORS.confirmed
  return palette[type] || palette.vehicle
}

export function toFullCalendarResource(resource) {
  return {
    id: resource.id,
    title: resource.title,
    extendedProps: {
      type: resource.type,
      hostName: resource.hostName,
      city: resource.city,
      capacity: resource.capacity,
      ...resource.extendedProps,
    },
  }
}

export function toFullCalendarEvent(event) {
  const color = eventStatusColor(event.status, event.type)
  return {
    id: event.id,
    resourceId: event.resourceId,
    title: event.title,
    start: event.start,
    end: event.end,
    backgroundColor: color,
    borderColor: event.hasConflict ? '#dc2626' : color,
    extendedProps: {
      status: event.status,
      subStatus: event.subStatus,
      paymentStatus: event.paymentStatus,
      hasConflict: event.hasConflict,
      type: event.type,
      ...event.extendedProps,
    },
  }
}

export function parseEventId(eventId) {
  if (!eventId || typeof eventId !== 'string') return null
  const [type, id] = eventId.split(':')
  if (!type || !id) return null
  return { type, id: Number(id) }
}

export function buildFilterParams(searchParams) {
  return {
    listing_type: searchParams.get('type') || 'all',
    host_id: searchParams.get('host') || undefined,
    city: searchParams.get('city') || undefined,
    status: searchParams.get('status') || undefined,
    search: searchParams.get('q') || undefined,
  }
}
