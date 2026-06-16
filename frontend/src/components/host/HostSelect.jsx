import FieldSelect from '../ui/FieldSelect'

export default function HostSelect({
  value,
  onChange,
  options = [],
  placeholder = 'Select',
  disabled = false,
  ariaLabel,
  searchable = false,
  className = '',
  wrapClassName = '',
}) {
  return (
    <div className={`host-select-wrap ${wrapClassName}`.trim()}>
      <FieldSelect
        value={value}
        onChange={onChange}
        options={options}
        placeholder={placeholder}
        disabled={disabled}
        ariaLabel={ariaLabel}
        searchable={searchable}
        className={`host-select ${className}`.trim()}
      />
    </div>
  )
}
