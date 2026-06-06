import { useEffect, useMemo, useRef, useState } from 'react'
import { Calendar, Pencil } from 'lucide-react'
import { fmtDisplayDate, nightsBetween } from '../../utils/requestToBookUtils'

const DOWS = ['Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa', 'Su']

function startOfDay(d) {
  const x = new Date(d)
  x.setHours(0, 0, 0, 0)
  return x
}

export default function TripCalendarPicker({
  startDate,
  endDate,
  startLabel,
  endLabel,
  blockedDates = [],
  onChange,
  onRangeComplete,
}) {
  const [open, setOpen] = useState(null)
  const [sel, setSel] = useState({ a: startDate, b: endDate })
  const [viewMonth, setViewMonth] = useState(() => new Date())
  const wrapRef = useRef(null)
  const blockedSet = useMemo(() => new Set(blockedDates), [blockedDates])
  const today = useMemo(() => startOfDay(new Date()), [])

  useEffect(() => {
    setSel({ a: startDate, b: endDate })
  }, [startDate, endDate])

  useEffect(() => {
    const handler = (e) => {
      if (open && wrapRef.current && !wrapRef.current.contains(e.target)) setOpen(null)
    }
    document.addEventListener('click', handler)
    return () => document.removeEventListener('click', handler)
  }, [open])

  const openCal = (which) => {
    if (open === which) {
      setOpen(null)
      return
    }
    setOpen(which)
    const base = which === 'end' ? endDate || startDate : startDate
    setViewMonth(base ? new Date(base.getFullYear(), base.getMonth(), 1) : new Date())
    setSel({ a: startDate, b: endDate })
  }

  const y = viewMonth.getFullYear()
  const m = viewMonth.getMonth()
  const daysInMonth = new Date(y, m + 1, 0).getDate()
  const startDow = (new Date(y, m, 1).getDay() + 6) % 7

  const cells = []
  for (let i = 0; i < startDow; i++) cells.push({ empty: true, key: `e${i}` })
  for (let d = 1; d <= daysInMonth; d++) {
    const dt = new Date(y, m, d)
    const ts = dt.getTime()
    const dateStr = dt.toISOString().slice(0, 10)
    const past = dt < today
    const blocked = blockedSet.has(dateStr)
    let cls = 'cal-cell'
    if (past || blocked) cls += ' past'
    if (ts === today.getTime()) cls += ' today'
    const a = sel.a?.getTime()
    const b = sel.b?.getTime()
    if (a && ts === a) cls += ' sel range-start'
    if (b && ts === b) cls += ' sel range-end'
    if (a && b && ts > a && ts < b) cls += ' inrange'
    cells.push({ d, dt, ts, cls, disabled: past || blocked, key: ts })
  }

  const pick = (dt) => {
    const editingEnd = open === 'end'
    const editingStart = open === 'start' || !open

    if (editingStart && (!sel.a || (sel.a && sel.b))) {
      let newEnd = sel.b
      if (newEnd && newEnd.getTime() <= dt.getTime()) newEnd = null
      const next = { a: dt, b: newEnd }
      setSel(next)
      onChange(next.a, next.b)
      setOpen('end')
      if (baseForView(next.b)) setViewMonth(new Date(next.b.getFullYear(), next.b.getMonth(), 1))
      return
    }

    if (editingEnd || (sel.a && !sel.b)) {
      if (dt.getTime() > (sel.a?.getTime() || 0)) {
        const next = { a: sel.a, b: dt }
        setSel(next)
        onChange(next.a, next.b)
        return
      }
      const next = { a: dt, b: null }
      setSel(next)
      onChange(next.a, null)
      setOpen('end')
      return
    }

    if (dt.getTime() > sel.a.getTime()) {
      const next = { a: sel.a, b: dt }
      setSel(next)
      onChange(next.a, next.b)
    } else {
      const next = { a: dt, b: null }
      setSel(next)
      onChange(next.a, null)
      setOpen('end')
    }
  }

  function baseForView(d) {
    return d instanceof Date && !Number.isNaN(d.getTime())
  }

  const handleDone = () => {
    const a = sel.a || startDate
    const b = sel.b || endDate
    if (a && b) {
      onChange(a, b)
      onRangeComplete?.()
    }
    setOpen(null)
  }

  const nights = sel.a && sel.b ? nightsBetween(sel.a, sel.b) : nightsBetween(startDate, endDate)
  const activeStart = sel.a || startDate
  const activeEnd = sel.b || endDate

  return (
    <div className="datepick" ref={wrapRef}>
      <div className="daterow">
        <button type="button" className={`dcard${open === 'start' ? ' open' : ''}`} onClick={() => openCal('start')}>
          <span className="dic"><Calendar aria-hidden /></span>
          <span className="dmeta">
            <span className="dk">{startLabel}</span>
            <span className="dv">{startDate ? fmtDisplayDate(startDate) : 'Select date'}</span>
            <span className="ds">{startDate ? startDate.getFullYear() : ''}</span>
          </span>
          <span className="dedit"><Pencil aria-hidden /></span>
        </button>
        <button type="button" className={`dcard${open === 'end' ? ' open' : ''}`} onClick={() => openCal('end')}>
          <span className="dic"><Calendar aria-hidden /></span>
          <span className="dmeta">
            <span className="dk">{endLabel}</span>
            <span className="dv">{endDate ? fmtDisplayDate(endDate) : 'Select date'}</span>
            <span className="ds">{endDate ? endDate.getFullYear() : ''}</span>
          </span>
          <span className="dedit"><Pencil aria-hidden /></span>
        </button>
      </div>
      {open && (
        <div className="cal-pop open">
          <div className="cal-head">
            <button
              type="button"
              className="cal-nav"
              disabled={y === today.getFullYear() && m === today.getMonth()}
              onClick={() => setViewMonth(new Date(y, m - 1, 1))}
            >
              ‹
            </button>
            <div className="cal-title">{viewMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' })}</div>
            <button type="button" className="cal-nav" onClick={() => setViewMonth(new Date(y, m + 1, 1))}>
              ›
            </button>
          </div>
          <div className="cal-dows">{DOWS.map((d) => <span key={d}>{d}</span>)}</div>
          <div className="cal-grid">
            {cells.map((c) =>
              c.empty ? (
                <button key={c.key} type="button" className="cal-cell empty" tabIndex={-1} />
              ) : (
                <button
                  key={c.key}
                  type="button"
                  className={c.cls}
                  disabled={c.disabled}
                  onClick={() => !c.disabled && pick(c.dt)}
                >
                  {c.d}
                </button>
              ),
            )}
          </div>
          <div className="cal-foot">
            <span className="cal-nights">
              {activeStart && activeEnd ? (
                <>
                  <b>{nights} night{nights !== 1 ? 's' : ''}</b>
                  <span> · {fmtDisplayDate(activeStart)} → {fmtDisplayDate(activeEnd)}</span>
                </>
              ) : activeStart ? (
                <span>Now choose your {endLabel.toLowerCase()} date</span>
              ) : (
                <span>Select your {open === 'end' ? endLabel.toLowerCase() : startLabel.toLowerCase()} date</span>
              )}
            </span>
            <button type="button" className="cal-done" onClick={handleDone}>
              Done
            </button>
          </div>
        </div>
      )}
    </div>
  )
}
