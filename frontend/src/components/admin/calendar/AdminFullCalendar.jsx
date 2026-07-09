import { Component, useEffect, useMemo } from 'react'
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
  if (view === 'resourceTimelineWeek') return 96
  return 42
}

export default function AdminFullCalendar({
  resources = [],
  events = [],
  loading = false,
  currentView,
  onDatesSet,
  onEventClick,
  calendarRef,
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

  useEffect(() => {
    const api = calendarRef?.current?.getApi?.()
    if (!api) return undefined

    // FullCalendar often keeps a stale width after month ↔ week switches.
    const refresh = () => {
      try {
        api.updateSize()
      } catch {
        // Calendar may be mid-unmount during view changes.
      }
    }

    refresh()
    const t1 = window.setTimeout(refresh, 50)
    const t2 = window.setTimeout(refresh, 250)
    const t3 = window.setTimeout(refresh, 600)

    return () => {
      window.clearTimeout(t1)
      window.clearTimeout(t2)
      window.clearTimeout(t3)
    }
  }, [calendarRef, currentView, mappedResources.length, mappedEvents.length])

  return (
    <div className={`admin-calendar-card admin-calendar-mount admin-calendar-mount--${currentView}`}>
      {loading && (
        <div className="admin-calendar-skeleton" style={{ marginBottom: '0.75rem' }}>
          <div className="admin-calendar-skeleton__bar" />
        </div>
      )}
      <CalendarErrorBoundary resetKey={currentView}>
        <FullCalendar
          ref={calendarRef}
          plugins={[resourceTimelinePlugin, interactionPlugin, listPlugin]}
          initialView={currentView}
          headerToolbar={false}
          height="auto"
          contentHeight="auto"
          stickyHeaderDates
          schedulerLicenseKey="GPL-My-Project-Is-Open-Source"
          resources={mappedResources}
          events={mappedEvents}
          resourceAreaHeaderContent="Listings"
          resourceAreaWidth={isListView ? undefined : '220px'}
          slotMinWidth={slotMinWidth}
          eventMinWidth={8}
          nowIndicator
          editable={false}
          eventClick={onEventClick}
          datesSet={onDatesSet}
          views={{
            resourceTimelineMonth: {
              type: 'resourceTimeline',
              duration: { months: 1 },
              slotDuration: { days: 1 },
              slotLabelFormat: [{ day: 'numeric' }],
            },
            resourceTimelineWeek: {
              type: 'resourceTimeline',
              duration: { weeks: 1 },
              slotDuration: { days: 1 },
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
