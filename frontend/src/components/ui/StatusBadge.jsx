import { capitalize } from '../../utils/format'

const STATUS_MAP = {
  pending: { label: 'Pending', className: 'bg-amber-100 text-amber-800 ring-amber-200' },
  stand_by: { label: 'Stand By', className: 'bg-yellow-100 text-yellow-800 ring-yellow-200' },
  confirmed: { label: 'Confirmed', className: 'bg-blue-100 text-blue-800 ring-blue-200' },
  cancelled: { label: 'Cancelled', className: 'bg-red-100 text-red-800 ring-red-200' },
  upcoming: { label: 'Upcoming', className: 'bg-sky-100 text-sky-800 ring-sky-200' },
  started: { label: 'Active', className: 'bg-emerald-100 text-emerald-800 ring-emerald-200' },
  terminated: { label: 'Completed', className: 'bg-slate-200 text-slate-700 ring-slate-300' },
  no_show: { label: 'No Show', className: 'bg-orange-100 text-orange-800 ring-orange-200' },
}

export default function StatusBadge({ status, rentalStatus }) {
  const key = rentalStatus && ['started', 'upcoming', 'terminated', 'no_show'].includes(rentalStatus)
    ? rentalStatus
    : status
  const config = STATUS_MAP[key] || {
    label: capitalize(key),
    className: 'bg-slate-100 text-slate-700 ring-slate-200',
  }

  return (
    <span
      className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold ring-1 ring-inset ${config.className}`}
    >
      {config.label}
    </span>
  )
}
