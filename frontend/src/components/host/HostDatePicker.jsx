import DatePicker from 'react-datepicker'
import { Calendar } from 'lucide-react'
import { formatDateOnly, parseDateTimeLocal } from '../../utils/format'

function startOfToday() {
  const d = new Date()
  d.setHours(0, 0, 0, 0)
  return d
}

export default function HostDatePicker({
  value,
  onChange,
  minDate,
  maxDate,
  placeholder = 'Select date',
  disabled = false,
}) {
  const selected = value ? parseDateTimeLocal(value) : null
  const effectiveMinDate = minDate ?? startOfToday()

  return (
    <div className="host-datepicker-wrap">
      <DatePicker
        selected={selected}
        onChange={(date) => onChange(date ? formatDateOnly(date) : '')}
        dateFormat="dd MMM yyyy"
        minDate={effectiveMinDate}
        maxDate={maxDate}
        disabled={disabled}
        placeholderText={placeholder}
        popperPlacement="bottom-start"
        calendarClassName="tb-datepicker-calendar"
        wrapperClassName="w-full"
        className="host-datepicker-input"
        autoComplete="off"
        popperClassName="tb-datepicker-popper"
        popperProps={{ strategy: 'fixed' }}
      />
      <Calendar className="host-datepicker-icon" aria-hidden />
    </div>
  )
}
