import { CURRENCIES } from '../../data/localePreferences'
import HostSelect from './HostSelect'

export default function HostCurrencySelect({
  value,
  onChange,
  ariaLabel = 'Pricing currency',
  className = '',
  wrapClassName = '',
}) {
  return (
    <HostSelect
      value={value || ''}
      onChange={onChange}
      options={CURRENCIES.map((item) => ({
        value: item.code,
        label: item.label,
        subtitle: item.name,
      }))}
      placeholder="Select currency"
      ariaLabel={ariaLabel}
      className={className}
      wrapClassName={wrapClassName}
    />
  )
}
