import { useMemo } from 'react'
import { getCountryOptions, resolveCountrySelectValue } from '../../data/countries'

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
  const selectValue = resolveCountrySelectValue(value, options)

  return (
    <select
      id={id}
      className={className}
      value={selectValue}
      onChange={onChange}
      required={required}
    >
      <option value="">{placeholder}</option>
      {options.map(({ code, name }) => (
        <option key={code} value={code}>{name}</option>
      ))}
    </select>
  )
}
