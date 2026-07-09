import { Component, useEffect, useMemo, useRef } from 'react'
import FullCalendar from '@fullcalendar/react'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import { toFullCalendarEvent, toFullCalendarResource } from '../../../utils/adminCalendarMappers'

class CalendarErrorBoundary extends Component {
  constructor(props) {
    super(props)
    this.state = { error: null }
  }

  static getDerivedStateFromError(error) {
    return { error }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.resetKey !== this.props.resetKey && this.state.error) {
      this.setState({ error: null })
    }
  }

  render() {
    if (this.state.error) {
      return (
        <div className="admin-calendar-embed-error">
          Could not render the calendar. {this.state.error.message}
        </div>
      )
    }

    return this.props.children
  }
}

function slotMinWidthForView(view) {
  if (view === 'resourceTimelineDay') return 72
  if (view === 'resourceTimelineWeek') return 110
  return 36
}

function heightForView(view, resourceCount) {
  // Fixed heights avoid FullCalendar resource-timeline auto-height bugs when
  // switching month ↔ week (stale scrollgrid / collapsed slots).
  if (view === 'listWeek') return 'auto'
  const rows = Math.max(resourceCount, 1)
  return Math.min(720, Math.max(420, 120 + rows * 48))
}

export default function AdminFullCalendar({
  resources = [],
  events = [],
  loading = false,
  currentView,
  onDatesSet,
  onEventClick,
  calendarRef,
  initialDate,
}) {
  const mappedResources = useMemo(
    () => resources.map(toFullCalendarResource),
    [resources],
  )
  const mappedEvents = useMemo(
    () => events.map(toFullCalendarEvent),
    [events],
  )
  const slotMinWidth = slotMinWidthForView(currentView)
  const isListView = currentView === 'listWeek'
  const calendarHeight = heightForView(currentView, mappedResources.length)

  // Keep a stable date across remounts so Month → Week → Month stays on the same period.
  const dateAnchorRef = useRef(initialDate || undefined)
  useEffect(() => {
    if (initialDate) {
      dateAnchorRef.current = initialDate
    }
  }, [initialDate])

  const handleDatesSet = (arg) => {
    dateAnchorRef.current = arg.startStr || arg.start?.toISOString?.() || dateAnchorRef.current
    onDatesSet?.(arg)
  }

  useEffect(() => {
    // After remount, give the new view one layout pass once the DOM is painted.
    const id = window.requestAnimationFrame(() => {
      try {
        calendarRef?.current?.getApi?.()?.updateSize()
      } catch {
        // no-op
      }
    })
    return () => window.cancelAnimationFrame(id)
  }, [calendarRef, currentView, mappedResources.length])

  return (
    <div className={`admin-calendar-card admin-calendar-mount admin-calendar-mount--${currentView}`}>
      {loading && (
        <div className="admin-calendar-skeleton" style={{ marginBottom: '0.75rem' }}>
          <div className="admin-calendar-skeleton__bar" />
        </div>
      )}
      <CalendarErrorBoundary resetKey={currentView}>
        {/*
          key={currentView} remounts FullCalendar on every view switch.
          Resource-timeline month↔week changeView() leaves a broken scrollgrid;
          a fresh instance is the reliable fix.
        */}
        <FullCalendar
          key={currentView}
          ref={calendarRef}
          plugins={[resourceTimelinePlugin, interactionPlugin, listPlugin]}
          initialView={currentView}
          initialDate={dateAnchorRef.current}
          headerToolbar={false}
          height={calendarHeight}
          stickyHeaderDates={!isListView}
          schedulerLicenseKey="GPL-My-Project-Is-Open-Source"
          resources={mappedResources}
          events={mappedEvents}
          resourceAreaHeaderContent="Listings"
          resourceAreaWidth={isListView ? undefined : 220}
          slotMinWidth={slotMinWidth}
          eventMinWidth={8}
          nowIndicator
          editable={false}
          eventClick={onEventClick}
          datesSet={handleDatesSet}
          views={{
            resourceTimelineMonth: {
              type: 'resourceTimeline',
              duration: { months: 1 },
              slotDuration: { days: 1 },
              slotLabelInterval: { days: 1 },
              slotLabelFormat: [{ day: 'numeric' }],
            },
            resourceTimelineWeek: {
              type: 'resourceTimeline',
              duration: { weeks: 1 },
              slotDuration: { days: 1 },
              slotLabelInterval: { days: 1 },
              slotLabelFormat: [
                { weekday: 'short', month: 'numeric', day: 'numeric' },
              ],
            },
            resourceTimelineDay: {
              type: 'resourceTimeline',
              duration: { days: 1 },
              slotDuration: { hours: 2 },
              slotLabelFormat: [{ hour: 'numeric', minute: '2-digit' }],
            },
            listWeek: {
              type: 'list',
              duration: { weeks: 1 },
            },
          }}
          eventClassNames={(arg) => (arg.event.extendedProps.hasConflict ? ['conflict-event'] : [])}
          eventContent={(arg) => (
            <div className="admin-calendar-event-label">
              {arg.event.title}
            </div>
          )}
        />
      </CalendarErrorBoundary>
    </div>
  )
}
