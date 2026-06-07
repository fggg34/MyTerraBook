import { useMemo } from 'react'
import { getCountryOptions } from '../../data/countries'

export default function CountrySelect({
  id,
  value,
  onChange,
  placeholder = 'Select country',
  className = '',
  includeOther = true,
  required = false,
}) {
  const options = useMemo(
    () => getCountryOptions({ includeOther }),
    [includeOther],
  )

  return (
    <select
      id={id}
      className={className}
      value={value}
      onChange={onChange}
      required={required}
    >
      <option value="">{placeholder}</option>
      {options.map(({ code, name }) => (
        <option key={code} value={name}>{name}</option>
      ))}
    </select>
  )
}
