import {
  forwardRef,
  useCallback,
  useEffect,
  useImperativeHandle,
  useLayoutEffect,
  useMemo,
  useRef,
  useState,
} from 'react'
import { createPortal } from 'react-dom'
import '../../styles/date-range-picker.css'
import { localDateKey } from '../../utils/bookingRestrictions'

const MON = [
  'January', 'February', 'March', 'April', 'May', 'June',
  'July', 'August', 'September', 'October', 'November', 'December',
]
const M3 = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec']

function startOfDay(value) {
  const d = new Date(value)
  d.setHours(0, 0, 0, 0)
  return d
}

function parseDate(value) {
  if (!value) return null
  if (value instanceof Date) return startOfDay(value)
  const d = new Date(String(value).slice(0, 10))
  if (Number.isNaN(d.getTime())) return null
  return startOfDay(d)
}

function sameDay(a, b) {
  return a && b && a.getTime() === b.getTime()
}

function fmtDisplay(d, useFullMonth = false) {
  const month = useFullMonth ? MON[d.getMonth()] : M3[d.getMonth()]
  return `${d.getDate()} ${month}`
}

function euro(n) {
  return `€${n.toLocaleString('en-IE')}`
}

const CALENDAR_ICON = (
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.9" strokeLinecap="round" strokeLinejoin="round">
    <rect x="3" y="4.5" width="18" height="16" rx="2.5" />
    <path d="M3 9h18M8 2.5v4M16 2.5v4" />
  </svg>
)

const DateRangePicker = forwardRef(function DateRangePicker(
  {
    startDate,
    endDate,
    onChange,
    startLabel = 'Pick-up',
    endLabel = 'Drop-off',
    minNights = 1,
    maxNights = null,
    pricePerDay = null,
    blockedDates = [],
    variant = 'default',
    fixedPopper = false,
    className = '',
  },
  ref,
) {
  const wrapRef = useRef(null)
  const fieldRef = useRef(null)
  const popRef = useRef(null)
  const [open, setOpen] = useState(false)
  const [popStyle, setPopStyle] = useState(null)

  const today = useMemo(() => startOfDay(new Date()), [])
  const dStart = parseDate(startDate)
  const dEnd = parseDate(endDate)
  const blockedSet = useMemo(() => new Set(blockedDates), [blockedDates])

  const [view, setView] = useState(() => {
    const base = dStart || today
    return new Date(base.getFullYear(), base.getMonth(), 1)
  })

  const isCompact = variant.includes('compact')
  const showLabels = !isCompact

  const measurePopper = useCallback(() => {
    if (!fixedPopper || !fieldRef.current) return null
    const rect = fieldRef.current.getBoundingClientRect()
    return {
      position: 'fixed',
      top: rect.bottom + 10,
      left: rect.left,
      width: Math.max(rect.width, isCompact ? 255 : 340),
      zIndex: 1200,
    }
  }, [fixedPopper, isCompact])

  const updatePopperPosition = useCallback(() => {
    const next = measurePopper()
    if (next) setPopStyle(next)
  }, [measurePopper])

  const openCalendar = useCallback(() => {
    if (fixedPopper) {
      setPopStyle(measurePopper())
    }
    setOpen(true)
  }, [fixedPopper, measurePopper])

  const toggleCalendar = useCallback(() => {
    setOpen((prev) => {
      if (prev) return false
      if (fixedPopper) {
        setPopStyle(measurePopper())
      }
      return true
    })
  }, [fixedPopper, measurePopper])

  useImperativeHandle(ref, () => ({
    open: openCalendar,
    close: () => setOpen(false),
  }))

  useLayoutEffect(() => {
    if (!open || !fixedPopper) return undefined
    updatePopperPosition()
    return undefined
  }, [open, fixedPopper, updatePopperPosition])

  useEffect(() => {
    if (!open || !fixedPopper) return undefined
    const onScrollOrResize = () => updatePopperPosition()
    window.addEventListener('scroll', onScrollOrResize, true)
    window.addEventListener('resize', onScrollOrResize)
    return () => {
      window.removeEventListener('scroll', onScrollOrResize, true)
      window.removeEventListener('resize', onScrollOrResize)
    }
  }, [open, fixedPopper, updatePopperPosition])

  useEffect(() => {
    if (!open) return undefined
    const onPointerDown = (event) => {
      const inField = wrapRef.current?.contains(event.target)
      const inPop = popRef.current?.contains(event.target)
      if (!inField && !inPop) {
        setOpen(false)
      }
    }
    const timer = window.setTimeout(() => {
      document.addEventListener('mousedown', onPointerDown)
    }, 0)
    return () => {
      window.clearTimeout(timer)
      document.removeEventListener('mousedown', onPointerDown)
    }
  }, [open])

  const minEndDate = useMemo(() => {
    if (!dStart) return null
    const d = new Date(dStart)
    d.setDate(d.getDate() + (minNights > 1 ? minNights : 1))
    return d
  }, [dStart, minNights])

  const maxEndDate = useMemo(() => {
    if (!dStart || !maxNights) return null
    const d = new Date(dStart)
    d.setDate(d.getDate() + maxNights)
    return d
  }, [dStart, maxNights])

  const isDisabled = (date) => {
    if (date < today) return true
    if (blockedSet.has(localDateKey(date))) return true
    if (dStart && !dEnd) {
      if (date < dStart) return true
      if (minEndDate && date < minEndDate) return true
      if (maxEndDate && date > maxEndDate) return true
    }
    if (maxEndDate && dStart && dEnd && date > maxEndDate) return true
    return false
  }

  const emitChange = (start, end) => {
    onChange?.({ start, end })
  }

  const handleDayClick = (date) => {
    if (isDisabled(date)) return

    if (!dStart || (dStart && dEnd) || date < dStart) {
      emitChange(date, null)
      return
    }

    if (!sameDay(date, dStart)) {
      emitChange(dStart, date)
      setOpen(false)
    }
  }

  const handleClear = (event) => {
    event.stopPropagation()
    emitChange(null, null)
  }

  const nights =
    dStart && dEnd ? Math.max(1, Math.round((dEnd.getTime() - dStart.getTime()) / 86400000)) : 0

  const footnote = (() => {
    if (dStart && dEnd) {
      if (pricePerDay != null) {
        const total = nights * Number(pricePerDay)
        return (
          <>
            <b>
              {nights} night{nights > 1 ? 's' : ''}
            </b>{' '}
            <span>· {euro(total)} total</span>
          </>
        )
      }
      return (
        <b>
          {nights} night{nights > 1 ? 's' : ''}
        </b>
      )
    }
    if (dStart) return <span>Choose your {endLabel.toLowerCase()} date</span>
    return <span>Select your dates</span>
  })()

  const startDow = (new Date(view.getFullYear(), view.getMonth(), 1).getDay() + 6) % 7
  const daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate()
  const cells = []

  for (let i = 0; i < startDow; i++) {
    cells.push(<div key={`empty-${i}`} className="cal-cell empty" />)
  }

  for (let day = 1; day <= daysInMonth; day++) {
    const date = new Date(view.getFullYear(), view.getMonth(), day)
    const classes = ['cal-cell']
    if (date < today) classes.push('past')
    if (isDisabled(date) && date >= today) classes.push('disabled')
    if (sameDay(date, today)) classes.push('today')
    if (sameDay(date, dStart) || sameDay(date, dEnd)) classes.push('sel')
    if (dStart && dEnd) {
      if (sameDay(date, dStart)) classes.push('range-start')
      if (sameDay(date, dEnd)) classes.push('range-end')
      if (date > dStart && date < dEnd) classes.push('inrange')
    }

    const disabled = classes.includes('past') || classes.includes('disabled')

    cells.push(
      <button
        key={day}
        type="button"
        className={classes.join(' ')}
        disabled={disabled}
        onClick={(event) => {
          event.stopPropagation()
          handleDayClick(date)
        }}
      >
        {day}
      </button>,
    )
  }

  const prevDisabled = view.getFullYear() === today.getFullYear() && view.getMonth() === today.getMonth()

  const calendarPop = (
    <div
      ref={popRef}
      className={`date-range-picker cal-pop ${variant} ${open ? 'open' : ''} ${fixedPopper ? 'fixed' : ''}`.trim()}
      style={fixedPopper ? popStyle || undefined : undefined}
      role="dialog"
      aria-label={`${startLabel} to ${endLabel} calendar`}
      onClick={(event) => event.stopPropagation()}
      onMouseDown={(event) => event.stopPropagation()}
    >
      <div className="cal-head">
        <button
          className="cal-nav"
          type="button"
          aria-label="Previous month"
          disabled={prevDisabled}
          onClick={(event) => {
            event.stopPropagation()
            setView((prev) => new Date(prev.getFullYear(), prev.getMonth() - 1, 1))
          }}
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
            <path d="m15 18-6-6 6-6" />
          </svg>
        </button>
        <div className="cal-title">
          {MON[view.getMonth()]} {view.getFullYear()}
        </div>
        <button
          className="cal-nav"
          type="button"
          aria-label="Next month"
          onClick={(event) => {
            event.stopPropagation()
            setView((prev) => new Date(prev.getFullYear(), prev.getMonth() + 1, 1))
          }}
        >
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.4" strokeLinecap="round" strokeLinejoin="round">
            <path d="m9 18 6-6-6-6" />
          </svg>
        </button>
      </div>
      <div className="cal-dows">
        {['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su'].map((dow) => (
          <span key={dow}>{dow}</span>
        ))}
      </div>
      <div className="cal-grid">{cells}</div>
      <div className="cal-foot">
        <span className="cal-nights">{footnote}</span>
        <div className="cal-actions">
          <button className="cal-clear" type="button" onClick={handleClear}>
            Clear
          </button>
          <button className="cal-done" type="button" onClick={() => setOpen(false)}>
            Done
          </button>
        </div>
      </div>
    </div>
  )

  return (
    <div className={`date-range-picker ${variant} ${className}`.trim()} ref={wrapRef}>
      <button
        ref={fieldRef}
        type="button"
        className={`date-field ${open ? 'open' : ''}`}
        onClick={(event) => {
          event.stopPropagation()
          toggleCalendar()
        }}
        aria-expanded={open}
        aria-haspopup="dialog"
      >
        {CALENDAR_ICON}
        <div className="df-segs">
          <span className="df-seg">
            {showLabels && <span className="df-lab">{startLabel}</span>}
            <span className={`df-val ${dStart ? 'set' : ''}`}>
              {dStart ? fmtDisplay(dStart, isCompact) : 'Add date'}
            </span>
          </span>
          <span className="df-div" />
          <span className="df-seg right">
            {showLabels && <span className="df-lab">{endLabel}</span>}
            <span className={`df-val ${dEnd ? 'set' : ''}`}>
              {dEnd ? fmtDisplay(dEnd, isCompact) : 'Add date'}
            </span>
          </span>
        </div>
      </button>

      {fixedPopper
        ? open && popStyle && createPortal(calendarPop, document.body)
        : calendarPop}
    </div>
  )
})

export default DateRangePicker
export { parseDate as parseDateOnly, startOfDay }
