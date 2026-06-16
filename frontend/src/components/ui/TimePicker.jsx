import DatePicker from 'react-datepicker'
import { Clock } from 'lucide-react'
import {
  TIME_INTERVAL_MINUTES,
  formatTimeValue,
  parseTimeValue,
} from '../../utils/format'
import 'react-datepicker/dist/react-datepicker.css'

export default function TimePicker({
  id,
  value,
  onChange,
  placeholder = 'Select time',
  hasError = false,
  disabled = false,
  required = false,
  fixedPopper = false,
  className = 'input-field tb-datepicker-input pr-10',
  ariaLabel,
}) {
  const selected = parseTimeValue(value)

  return (
    <div className="relative">
      <DatePicker
        id={id}
        selected={selected}
        onChange={(date) => onChange(date ? formatTimeValue(date) : '')}
        showTimeSelect
        showTimeSelectOnly
        timeIntervals={TIME_INTERVAL_MINUTES}
        timeCaption="Time"
        timeFormat="HH:mm"
        dateFormat="HH:mm"
        placeholderText={placeholder}
        required={required}
        disabled={disabled}
        aria-label={ariaLabel}
        popperPlacement="bottom-start"
        calendarClassName="tb-timepicker-calendar"
        wrapperClassName="w-full"
        className={`${className} ${hasError ? 'input-field-error' : ''}`.trim()}
        autoComplete="off"
        popperClassName="tb-datepicker-popper"
        popperProps={fixedPopper ? { strategy: 'fixed' } : undefined}
      />
      <Clock
        className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
        aria-hidden
      />
    </div>
  )
}
