import { useMemo } from 'react'
import CountryFlag from '../ui/CountryFlag'
import FieldSelect from '../ui/FieldSelect'
import { getCountryOptions, resolveCountrySelectValue } from '../../data/countries'

export default function CountrySelect({
  id,
  value,
  onChange,
  placeholder = 'Select country',
  className = '',
  includeOther = true,
  required = false,
  disabled = false,
  'aria-invalid': ariaInvalid,
  'aria-label': ariaLabel,
  'data-field': dataField,
}) {
  const countries = useMemo(
    () => getCountryOptions({ includeOther }),
    [includeOther],
  )
  const options = useMemo(
    () => countries.map(({ code, name }) => ({
      value: code,
      label: name,
      icon: /^[A-Z]{2}$/.test(code) ? <CountryFlag code={code} decorative /> : null,
    })),
    [countries],
  )
  const selectValue = resolveCountrySelectValue(value, countries)
  const selectedName = countries.find((country) => country.code === selectValue)?.name
  const invalid = ariaInvalid === true || ariaInvalid === 'true' || className.includes('inp-error')

  return (
    <div className={`country-select${invalid ? ' is-invalid' : ''}`} data-field={dataField}>
      <FieldSelect
        id={id}
        value={selectValue}
        onChange={(next) => onChange?.({ target: { value: next } })}
        options={options}
        placeholder={placeholder}
        ariaLabel={ariaLabel || selectedName || placeholder}
        disabled={disabled}
        required={required}
        searchable
      />
    </div>
  )
}
