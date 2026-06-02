import DatePicker from 'react-datepicker'
import { Calendar } from 'lucide-react'
import { formatDateTimeLocal, parseDateTimeLocal } from '../../utils/format'
import 'react-datepicker/dist/react-datepicker.css'

export default function DateTimePicker({
  id,
  value,
  onChange,
  minDate,
  maxDate,
  filterDate,
  placeholder = 'Select date & time',
  hasError = false,
  required = false,
  fixedPopper = false,
}) {
  const selected = parseDateTimeLocal(value)

  return (
    <div className="relative">
      <DatePicker
        id={id}
        selected={selected}
        onChange={(date) => onChange(date ? formatDateTimeLocal(date) : '')}
        showTimeSelect
        timeFormat="HH:mm"
        timeIntervals={30}
        dateFormat="EEE, MMM d, yyyy · HH:mm"
        minDate={minDate}
        maxDate={maxDate}
        filterDate={filterDate}
        placeholderText={placeholder}
        required={required}
        popperPlacement="bottom-start"
        calendarClassName="tb-datepicker-calendar"
        wrapperClassName="w-full"
        className={`input-field tb-datepicker-input pr-10 ${hasError ? 'input-field-error' : ''}`}
        autoComplete="off"
        popperClassName="tb-datepicker-popper"
        popperProps={fixedPopper ? { strategy: 'fixed' } : undefined}
      />
      <Calendar
        className="pointer-events-none absolute right-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
        aria-hidden
      />
    </div>
  )
}
