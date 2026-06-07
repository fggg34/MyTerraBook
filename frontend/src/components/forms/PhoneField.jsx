import PhoneInput from 'react-phone-number-input'
import PhoneCountrySelect from './PhoneCountrySelect'
import RequiredMark from './RequiredMark'
import '../../styles/phone-input.css'

const VARIANT_CLASS = {
  auth: 'phone-field--auth',
  host: 'phone-field--host',
  client: 'phone-field--client',
  rtb: 'phone-field--rtb',
}

export default function PhoneField({
  id,
  label = 'Phone',
  value,
  onChange,
  required = false,
  variant = 'auth',
  hasError = false,
  placeholder,
  defaultCountry = 'IS',
  showLabel = true,
  requiredMarkClassName,
}) {
  const variantClass = VARIANT_CLASS[variant] || VARIANT_CLASS.auth

  return (
    <div className={`phone-field ${variantClass}${hasError ? ' phone-field--error' : ''}`}>
      {showLabel && (
        <label htmlFor={id}>
          {label}
          {required && (
            <>
              {' '}
              <RequiredMark className={requiredMarkClassName} />
            </>
          )}
        </label>
      )}
      <PhoneInput
        id={id}
        defaultCountry={defaultCountry}
        countrySelectComponent={PhoneCountrySelect}
        countryCallingCodeEditable={false}
        value={value || undefined}
        onChange={(next) => onChange(next || '')}
        placeholder={placeholder}
        numberInputProps={{
          required,
          autoComplete: 'tel-national',
        }}
      />
    </div>
  )
}
