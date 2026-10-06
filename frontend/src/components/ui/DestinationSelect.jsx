import FieldSelect from './FieldSelect'
import useDestinations from '../../hooks/useDestinations'
import CountryFlag from './CountryFlag'

export default function DestinationSelect({ value, onChange, mainCategory, className = 'field field--primary', labelClassName = 'flabel', onOpen }) {
  const { destinations, isPending, isError, refetch } = useDestinations(mainCategory)
  const options = destinations.map(({ code, name }) => ({ value: code, label: name, icon: <CountryFlag code={code} decorative /> }))
  if (value && !options.some((option) => option.value === value) && /^[A-Z]{2}$/.test(value)) {
    options.unshift({ value, label: new Intl.DisplayNames(['en'], { type: 'region' }).of(value), icon: <CountryFlag code={value} decorative /> })
  }
  return (
    <div className={className}>
      <span className={labelClassName}>Destination</span>
      <FieldSelect
        value={value || ''}
        onChange={onChange}
        options={[
          { value: '', label: 'All destinations' },
          ...options,
        ]}
        placeholder={isPending ? 'Loading destinations…' : 'All destinations'}
        ariaLabel="Destination country"
        searchable
        onOpen={onOpen}
      />
      {isError && <button type="button" onClick={() => refetch()}>Retry destinations</button>}
    </div>
  )
}
