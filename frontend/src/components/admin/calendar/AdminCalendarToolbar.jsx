import { ChevronLeft, ChevronRight } from 'lucide-react'

const VIEW_OPTIONS = [
  { value: 'resourceTimelineMonth', label: 'Month' },
  { value: 'resourceTimelineWeek', label: 'Week' },
  { value: 'resourceTimelineDay', label: 'Day' },
  { value: 'listWeek', label: 'Agenda' },
]

export default function AdminCalendarToolbar({
  currentView,
  onViewChange,
  onPrev,
  onNext,
  onToday,
  title,
}) {
  return (
    <div className="admin-calendar-card admin-calendar-toolbar">
      <div className="admin-calendar-toolbar__group">
        <button type="button" className="admin-btn ghost" onClick={onPrev} aria-label="Previous">
          <ChevronLeft size={16} />
        </button>
        <button type="button" className="admin-btn ghost" onClick={onToday}>Today</button>
        <button type="button" className="admin-btn ghost" onClick={onNext} aria-label="Next">
          <ChevronRight size={16} />
        </button>
        <strong style={{ marginLeft: '0.5rem', color: '#0f172a' }}>{title}</strong>
      </div>

      <div className="admin-calendar-toolbar__group">
        {VIEW_OPTIONS.map((opt) => (
          <button
            key={opt.value}
            type="button"
            className={`admin-btn ${currentView === opt.value ? 'primary' : 'ghost'}`}
            onClick={() => onViewChange(opt.value)}
          >
            {opt.label}
          </button>
        ))}
      </div>
    </div>
  )
}
