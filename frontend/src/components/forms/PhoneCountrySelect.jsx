import { getCountryCallingCode } from 'react-phone-number-input'

function DefaultArrow() {
  return <div className="PhoneInputCountrySelectArrow" aria-hidden />
}

export default function PhoneCountrySelect({
  value,
  onChange,
  options,
  disabled,
  readOnly,
  iconComponent: Icon,
  arrowComponent: Arrow = DefaultArrow,
  className,
  ...rest
}) {
  const dialCode = value ? `+${getCountryCallingCode(value)}` : ''

  return (
    <div className="PhoneInputCountry">
      <select
        {...rest}
        className={`PhoneInputCountrySelect${className ? ` ${className}` : ''}`}
        disabled={disabled || readOnly}
        value={value || 'ZZ'}
        onChange={(event) => onChange(event.target.value === 'ZZ' ? undefined : event.target.value)}
      >
        {options.map((option) => (
          <option
            key={option.divider ? '|' : option.value || 'ZZ'}
            value={option.divider ? '|' : option.value || 'ZZ'}
            disabled={!!option.divider}
          >
            {option.label}
          </option>
        ))}
      </select>
      {value && Icon && <Icon aria-hidden country={value} />}
      {dialCode && <span className="phone-dial-code">{dialCode}</span>}
      <Arrow />
    </div>
  )
}
