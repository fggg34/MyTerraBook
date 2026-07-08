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

  const filters = useMemo(() => {
    const fromUrl = buildFilterParams(searchParams)
    return {
      listing_type: fromUrl.listing_type,
      host_id: fromUrl.host_id,
      city: fromUrl.city,
      status: fromUrl.status,
      search: fromUrl.search,
    }
  }, [searchParams])

  const resourcesQuery = useAdminCalendarResources(filters)
  const eventsQuery = useAdminCalendarEvents(range, filters)
  const summaryQuery = useAdminCalendarSummary(range, filters)
  const alertsQuery = useAdminCalendarAlerts(filters)

  const parsedSelection = parseEventId(selectedEventId)
  const detailQuery = useAdminCalendarEventDetail(parsedSelection?.type, parsedSelection?.id)

  const handleFilterChange = useCallback((patch) => {
    const next = new URLSearchParams(searchParams)
    Object.entries(patch).forEach(([key, value]) => {
      const urlKey = key === 'listing_type' ? 'type'
        : key === 'host_id' ? 'host'
        : key === 'search' ? 'q'
        : key
      if (value === undefined || value === null || value === '') {
        next.delete(urlKey)
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
    api?.changeView(viewName)
  }, [])

  const handlePrev = () => calendarRef.current?.getApi?.()?.prev()
  const handleNext = () => calendarRef.current?.getApi?.()?.next()
  const handleToday = () => calendarRef.current?.getApi?.()?.today()

  const resources = resourcesQuery.data?.data || []
  const events = eventsQuery.data?.data || []
  const selectedFromList = events.find((e) => e.id === selectedEventId)
  const detailEvent = detailQuery.data || selectedFromList

  useEffect(() => {
    if (!embed) return undefined
    document.documentElement.classList.add('admin-calendar-embed-root')
    return () => document.documentElement.classList.remove('admin-calendar-embed-root')
  }, [embed])

  useEffect(() => {
    if (!embed) return undefined

    const updateSize = () => {
      calendarRef.current?.getApi?.()?.updateSize()
    }

    updateSize()
    const t1 = window.setTimeout(updateSize, 100)
    const t2 = window.setTimeout(updateSize, 500)
    window.addEventListener('resize', updateSize)

    return () => {
      window.clearTimeout(t1)
      window.clearTimeout(t2)
      window.removeEventListener('resize', updateSize)
    }
  }, [embed, resources.length, events.length, currentView])

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

      {!embed && (
        <>
          <AdminCalendarFilters filters={filters} onChange={handleFilterChange} />
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
        resources={resources}
        events={events}
        loading={resourcesQuery.isLoading || eventsQuery.isLoading}
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
