import { useCallback, useEffect, useMemo, useRef, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import AdminCalendarAlertsPanel from '../../components/admin/calendar/AdminCalendarAlertsPanel'
import AdminCalendarFilters from '../../components/admin/calendar/AdminCalendarFilters'
import AdminCalendarSummaryBar from '../../components/admin/calendar/AdminCalendarSummaryBar'
import AdminCalendarToolbar from '../../components/admin/calendar/AdminCalendarToolbar'
import AdminFullCalendar from '../../components/admin/calendar/AdminFullCalendar'
import AdminReservationDetailPanel from '../../components/admin/calendar/AdminReservationDetailPanel'
import {
  useAdminCalendarAlerts,
  useAdminCalendarEventDetail,
  useAdminCalendarEvents,
  useAdminCalendarResources,
  useAdminCalendarSummary,
} from '../../hooks/admin/useAdminCalendar'
import { buildFilterParams, parseEventId } from '../../utils/adminCalendarMappers'

const DEFAULT_VISIBLE_RESOURCES = 20

function toIsoDate(date) {
  if (!date) return null
  return new Date(date).toISOString()
}

function defaultRange() {
  const start = new Date()
  start.setDate(1)
  start.setHours(0, 0, 0, 0)
  const end = new Date(start)
  end.setMonth(end.getMonth() + 1)
  end.setHours(23, 59, 59, 999)
  return { start: start.toISOString(), end: end.toISOString() }
}

export default function AdminCalendarPage({ embed = false }) {
  const calendarRef = useRef(null)
  const [searchParams, setSearchParams] = useSearchParams()
  const [range, setRange] = useState(defaultRange)
  const [currentView, setCurrentView] = useState('resourceTimelineMonth')
  const [calendarTitle, setCalendarTitle] = useState('')
  const [selectedEventId, setSelectedEventId] = useState(null)

  const filters = useMemo(() => buildFilterParams(searchParams), [searchParams])

  // Catalog query ignores resource_ids so the select always lists all matching listings.
  const catalogFilters = useMemo(() => ({
    listing_type: filters.listing_type,
    host_id: filters.host_id,
    city: filters.city,
    search: filters.search,
  }), [filters.listing_type, filters.host_id, filters.city, filters.search])

  const catalogQuery = useAdminCalendarResources(catalogFilters, { perPage: 200 })
  const catalogResources = catalogQuery.data?.data || []

  const selectedResourceIds = filters.resource_ids || []
  const visibleResources = useMemo(() => {
    if (selectedResourceIds.length > 0) {
      const selected = new Set(selectedResourceIds)
      return catalogResources.filter((resource) => selected.has(resource.id))
    }
    return catalogResources.slice(0, DEFAULT_VISIBLE_RESOURCES)
  }, [catalogResources, selectedResourceIds])

  const queryFilters = useMemo(() => {
    const resourceIds = selectedResourceIds.length > 0
      ? selectedResourceIds
      : visibleResources.map((resource) => resource.id)

    return {
      listing_type: filters.listing_type,
      host_id: filters.host_id,
      city: filters.city,
      status: filters.status,
      search: filters.search,
      resource_ids: resourceIds.length > 0 ? resourceIds.join(',') : undefined,
    }
  }, [filters, selectedResourceIds, visibleResources])

  const eventsQuery = useAdminCalendarEvents(range, queryFilters)
  const summaryQuery = useAdminCalendarSummary(range, queryFilters)
  const alertsQuery = useAdminCalendarAlerts(queryFilters)

  const parsedSelection = parseEventId(selectedEventId)
  const detailQuery = useAdminCalendarEventDetail(parsedSelection?.type, parsedSelection?.id)

  const handleFilterChange = useCallback((patch) => {
    const next = new URLSearchParams(searchParams)
    Object.entries(patch).forEach(([key, value]) => {
      const urlKey = key === 'listing_type' ? 'type'
        : key === 'host_id' ? 'host'
        : key === 'search' ? 'q'
        : key === 'resource_ids' ? 'resource'
        : key

      if (value === undefined || value === null || value === '' || (Array.isArray(value) && value.length === 0)) {
        next.delete(urlKey)
      } else if (Array.isArray(value)) {
        next.set(urlKey, value.join(','))
      } else {
        next.set(urlKey, String(value))
      }
    })
    setSearchParams(next, { replace: true })
  }, [searchParams, setSearchParams])

  const handleDatesSet = useCallback((arg) => {
    setCalendarTitle(arg.view.title)
    setRange({
      start: toIsoDate(arg.start),
      end: toIsoDate(arg.end),
    })
  }, [])

  const handleEventClick = useCallback((info) => {
    info.jsEvent.preventDefault()
    setSelectedEventId(info.event.id)
  }, [])

  const handleViewChange = useCallback((viewName) => {
    setCurrentView(viewName)
    const api = calendarRef.current?.getApi?.()
    if (!api) return

    try {
      api.changeView(viewName)
    } catch {
      // Ignore transient FullCalendar errors during rapid tab switches.
    }

    // Force a layout pass after the view DOM settles.
    window.requestAnimationFrame(() => {
      try {
        api.updateSize()
      } catch {
        // no-op
      }
    })
    window.setTimeout(() => {
      try {
        api.updateSize()
      } catch {
        // no-op
      }
    }, 120)
  }, [])

  const handlePrev = () => calendarRef.current?.getApi?.()?.prev()
  const handleNext = () => calendarRef.current?.getApi?.()?.next()
  const handleToday = () => calendarRef.current?.getApi?.()?.today()

  const events = eventsQuery.data?.data || []
  const selectedFromList = events.find((e) => e.id === selectedEventId)
  const detailEvent = detailQuery.data || selectedFromList
  const totalListings = catalogQuery.data?.meta?.total ?? catalogResources.length
  const showingLimited = selectedResourceIds.length === 0 && totalListings > visibleResources.length

  useEffect(() => {
    if (!embed) return undefined
    document.documentElement.classList.add('admin-calendar-embed-root')
    return () => document.documentElement.classList.remove('admin-calendar-embed-root')
  }, [embed])

  useEffect(() => {
    const updateSize = () => {
      try {
        calendarRef.current?.getApi?.()?.updateSize()
      } catch {
        // no-op
      }
    }

    updateSize()
    const t1 = window.setTimeout(updateSize, 100)
    const t2 = window.setTimeout(updateSize, 400)
    window.addEventListener('resize', updateSize)

    return () => {
      window.clearTimeout(t1)
      window.clearTimeout(t2)
      window.removeEventListener('resize', updateSize)
    }
  }, [embed, visibleResources.length, events.length, currentView])

  return (
    <div className={`admin-calendar-page${embed ? ' admin-calendar-page--embed' : ''}`}>
      {!embed && (
        <div>
          <h2 className="section-title" style={{ marginBottom: '0.25rem' }}>Reservations calendar</h2>
          <p className="section-subtitle">
            Platform-wide timeline for vehicles and guesthouses. Click a booking for details.
          </p>
        </div>
      )}

      <AdminCalendarFilters
        filters={filters}
        onChange={handleFilterChange}
        resources={catalogResources}
        resourcesLoading={catalogQuery.isLoading}
      />

      {showingLimited && (
        <p className="admin-calendar-limit-note">
          Showing {visibleResources.length} of {totalListings} listings.
          Use the Vehicle / listing filter to focus on one vehicle.
        </p>
      )}

      {!embed && (
        <>
          <AdminCalendarSummaryBar summary={summaryQuery.data} loading={summaryQuery.isLoading} />
          <AdminCalendarAlertsPanel alerts={alertsQuery.data} loading={alertsQuery.isLoading} />
        </>
      )}

      <AdminCalendarToolbar
        currentView={currentView}
        onViewChange={handleViewChange}
        onPrev={handlePrev}
        onNext={handleNext}
        onToday={handleToday}
        title={calendarTitle}
      />

      <AdminFullCalendar
        calendarRef={calendarRef}
        resources={visibleResources}
        events={events}
        loading={catalogQuery.isLoading || eventsQuery.isLoading}
        currentView={currentView}
        onDatesSet={handleDatesSet}
        onEventClick={handleEventClick}
      />

      <AdminReservationDetailPanel
        event={detailEvent}
        loading={Boolean(selectedEventId) && detailQuery.isLoading && !selectedFromList}
        onClose={() => setSelectedEventId(null)}
      />
    </div>
  )
}
