import DateTimePicker from '../ui/DateTimePicker'

export default function HostDateTimePicker({
  value,
  onChange,
  minDate,
  maxDate,
  placeholder = 'Select date & time',
  disabled = false,
}) {
  return (
    <div className={disabled ? 'host-datepicker-wrap disabled' : 'host-datepicker-wrap'}>
      <DateTimePicker
        value={value}
        onChange={onChange}
        minDate={minDate}
        maxDate={maxDate}
        placeholder={placeholder}
        fixedPopper
      />
    </div>
  )
}
