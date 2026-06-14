import { useMemo, useState } from 'react'
import { ChevronLeft, ChevronRight } from 'lucide-react'
import StatusBadge from '../ui/StatusBadge'
import { formatDate } from '../../utils/format'

const WEEKDAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
const MONTHS = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]

function toKey(date) {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

function addDays(date, days) {
  const next = new Date(date)
  next.setDate(next.getDate() + days)
  return next
}

function buildMonthGrid(year, month) {
  const first = new Date(year, month, 1)
  const startOffset = (first.getDay() + 6) % 7
  const gridStart = addDays(first, -startOffset)
  const cells = []

  for (let i = 0; i < 42; i += 1) {
    const date = addDays(gridStart, i)
    cells.push({
      date,
      key: toKey(date),
      inMonth: date.getMonth() === month,
      day: date.getDate(),
    })
  }

  return cells
}

export default function HostReservationsCalendar({
  carBookings = [],
  stayBookings = [],
  loading = false,
}) {
  const [tab, setTab] = useState('cars')
  const [monthOffset, setMonthOffset] = useState(0)
  const [selectedKey, setSelectedKey] = useState(null)

  const activeBookings = tab === 'cars' ? carBookings : stayBookings

  const dayMap = useMemo(() => {
    const map = new Map()
    activeBookings.forEach((booking) => {
      booking.days.forEach((key) => {
        const existing = map.get(key) || []
        existing.push(booking)
        map.set(key, existing)
      })
    })
    return map
  }, [activeBookings])

  const viewDate = useMemo(() => {
    const now = new Date()
    return new Date(now.getFullYear(), now.getMonth() + monthOffset, 1)
  }, [monthOffset])

  const cells = useMemo(
    () => buildMonthGrid(viewDate.getFullYear(), viewDate.getMonth()),
    [viewDate],
  )

  const todayKey = toKey(new Date())
  const selectedBookings = selectedKey ? (dayMap.get(selectedKey) || []) : []

  const upcoming = useMemo(() => {
    const today = new Date()
    today.setHours(0, 0, 0, 0)
    return [...activeBookings]
      .filter((booking) => booking.end >= today)
      .sort((a, b) => a.start - b.start)
      .slice(0, 4)
  }, [activeBookings])

  const monthBusyCount = useMemo(() => {
    let count = 0
    cells.forEach((cell) => {
      if (cell.inMonth && (dayMap.get(cell.key) || []).length > 0) count += 1
    })
    return count
  }, [cells, dayMap])

  return (
    <section className="host-calendar-panel">
      <div className="host-calendar-toolbar">
        <div className="host-calendar-toolbar-left">
          <h2>Reservations</h2>
          <div className="host-calendar-tabs" role="tablist" aria-label="Reservation type">
            <button
              type="button"
              role="tab"
              aria-selected={tab === 'cars'}
              className={`host-calendar-tab ${tab === 'cars' ? 'active' : ''}`}
              onClick={() => {
                setTab('cars')
                setSelectedKey(null)
              }}
            >
              Vehicles
              {!loading && <span className="host-calendar-tab-count">{carBookings.length}</span>}
            </button>
            <button
              type="button"
              role="tab"
              aria-selected={tab === 'stays'}
              className={`host-calendar-tab ${tab === 'stays' ? 'active' : ''}`}
              onClick={() => {
                setTab('stays')
                setSelectedKey(null)
              }}
            >
              Guesthouses
              {!loading && <span className="host-calendar-tab-count">{stayBookings.length}</span>}
            </button>
          </div>
        </div>

        <div className="host-calendar-toolbar-right">
          <button
            type="button"
            className="host-calendar-today"
            onClick={() => {
              setMonthOffset(0)
              setSelectedKey(todayKey)
            }}
          >
            Today
          </button>
          <div className="host-calendar-nav">
            <button
              type="button"
              className="host-calendar-nav-btn"
              aria-label="Previous month"
              onClick={() => setMonthOffset((value) => value - 1)}
            >
              <ChevronLeft size={16} />
            </button>
            <span className="host-calendar-month">
              {MONTHS[viewDate.getMonth()]} {viewDate.getFullYear()}
            </span>
            <button
              type="button"
              className="host-calendar-nav-btn"
              aria-label="Next month"
              onClick={() => setMonthOffset((value) => value + 1)}
            >
              <ChevronRight size={16} />
            </button>
          </div>
        </div>
      </div>

      <div className="host-calendar-body">
        <div className={`host-calendar-main ${loading ? 'is-loading' : ''}`}>
          {loading ? (
            <div className="host-calendar-skeleton" aria-hidden />
          ) : (
            <>
              <p className="host-calendar-meta">
                {monthBusyCount} booked day{monthBusyCount === 1 ? '' : 's'} this month
              </p>
              <div className={`host-calendar-grid ${tab}`}>
                <div className="host-calendar-weekdays">
                  {WEEKDAYS.map((day) => (
                    <span key={day}>{day}</span>
                  ))}
                </div>
                <div className="host-calendar-cells">
                  {cells.map((cell) => {
                    const count = (dayMap.get(cell.key) || []).length
                    const isSelected = selectedKey === cell.key
                    const isToday = cell.key === todayKey
                    return (
                      <button
                        key={cell.key}
                        type="button"
                        className={[
                          'host-calendar-cell',
                          !cell.inMonth ? 'out' : '',
                          count > 0 ? 'busy' : '',
                          isSelected ? 'selected' : '',
                          isToday ? 'today' : '',
                        ].filter(Boolean).join(' ')}
                        onClick={() => setSelectedKey(cell.key)}
                        title={count > 0 ? `${count} reservation${count === 1 ? '' : 's'}` : undefined}
                      >
                        {cell.day}
                        {count > 0 && <i className="host-calendar-dot" />}
                      </button>
                    )
                  })}
                </div>
              </div>
            </>
          )}
        </div>

        <aside className="host-calendar-side">
          <h3>{selectedKey ? formatDate(selectedKey) : 'Upcoming'}</h3>
          {loading ? (
            <div className="host-calendar-side-skeleton" aria-hidden />
          ) : (selectedKey ? selectedBookings : upcoming).length === 0 ? (
            <p className="host-calendar-empty">
              {selectedKey ? 'Nothing booked this day.' : 'No upcoming reservations.'}
            </p>
          ) : (
            <ul className="host-calendar-list">
              {(selectedKey ? selectedBookings : upcoming).map((booking) => (
                <li key={booking.id} className="host-calendar-item">
                  <div className="host-calendar-item-main">
                    <strong>{booking.label}</strong>
                    <span>
                      {formatDate(booking.startKey)} – {formatDate(booking.endKey)}
                    </span>
                  </div>
                  <StatusBadge status={booking.status} />
                </li>
              ))}
            </ul>
          )}
        </aside>
      </div>
    </section>
  )
}
