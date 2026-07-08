import { useMemo, useRef } from 'react'
import FullCalendar from '@fullcalendar/react'
import resourceTimelinePlugin from '@fullcalendar/resource-timeline'
import interactionPlugin from '@fullcalendar/interaction'
import listPlugin from '@fullcalendar/list'
import { toFullCalendarEvent, toFullCalendarResource } from '../../../utils/adminCalendarMappers'

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

  return (
    <div className="admin-calendar-card admin-calendar-mount">
      {loading && (
        <div className="admin-calendar-skeleton" style={{ marginBottom: '0.75rem' }}>
          <div className="admin-calendar-skeleton__bar" />
        </div>
      )}
      <FullCalendar
        ref={calendarRef}
        plugins={[resourceTimelinePlugin, interactionPlugin, listPlugin]}
        initialView={currentView}
        headerToolbar={false}
        height="auto"
        schedulerLicenseKey="GPL-My-Project-Is-Open-Source"
        resources={mappedResources}
        events={mappedEvents}
        resourceAreaHeaderContent="Listings"
        resourceAreaWidth="220px"
        slotMinWidth={42}
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
          },
          resourceTimelineWeek: {
            type: 'resourceTimeline',
            duration: { weeks: 1 },
            slotDuration: { days: 1 },
          },
          resourceTimelineDay: {
            type: 'resourceTimeline',
            duration: { days: 1 },
            slotDuration: { hours: 6 },
          },
        }}
        eventClassNames={(arg) => (arg.event.extendedProps.hasConflict ? ['conflict-event'] : [])}
        eventContent={(arg) => (
          <div style={{ padding: '2px 4px', overflow: 'hidden', whiteSpace: 'nowrap', textOverflow: 'ellipsis' }}>
            {arg.event.title}
          </div>
        )}
      />
    </div>
  )
}
