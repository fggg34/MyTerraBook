import TimePicker from '../ui/TimePicker'

export default function HostTimePicker({
  value,
  onChange,
  placeholder = 'Select time',
  disabled = false,
  hasError = false,
  ariaLabel,
}) {
  return (
    <div className={disabled ? 'host-datepicker-wrap disabled' : 'host-datepicker-wrap'}>
      <TimePicker
        value={value}
        onChange={onChange}
        placeholder={placeholder}
        disabled={disabled}
        hasError={hasError}
        fixedPopper
        className="host-datepicker-input"
        ariaLabel={ariaLabel}
      />
    </div>
  )
}
