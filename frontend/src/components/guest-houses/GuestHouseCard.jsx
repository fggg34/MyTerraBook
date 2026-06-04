import { MapPin, Star, Users } from 'lucide-react'
import { Link } from 'react-router-dom'
import { formatCurrencyFromCents, capitalize } from '../../utils/format'

export default function GuestHouseCard({ house, searchParams = '' }) {
  const href = `/guest-houses/${house.slug}${searchParams ? `?${searchParams}` : ''}`

  return (
    <Link
      to={href}
      className="group overflow-hidden rounded-xl border border-slate-200 bg-white shadow-card transition hover:border-accent/30 hover:shadow-lg"
    >
      <div className="aspect-[4/3] overflow-hidden bg-slate-100">
        <img
          src={house.thumbnail || 'https://placehold.co/800x600'}
          alt={house.name}
          className="h-full w-full object-cover transition duration-300 group-hover:scale-105"
        />
      </div>
      <div className="p-4">
        <div className="flex items-start justify-between gap-2">
          <div>
            <span className="inline-block rounded-full bg-slate-100 px-2 py-0.5 text-xs font-medium text-slate-600">
              {capitalize(house.type)}
            </span>
            <h3 className="mt-1 font-bold text-brand-950 group-hover:text-accent">{house.name}</h3>
          </div>
          {house.rating != null && (
            <span className="flex shrink-0 items-center gap-1 text-sm font-medium text-brand-950">
              <Star className="h-4 w-4 fill-amber-400 text-amber-400" aria-hidden />
              {house.rating}
            </span>
          )}
        </div>
        <p className="mt-1 flex items-center gap-1 text-sm text-slate-500">
          <MapPin className="h-3.5 w-3.5" aria-hidden />
          {house.city}
          {house.country ? `, ${house.country}` : ''}
        </p>
        <div className="mt-3 flex items-center justify-between">
          <p className="text-sm text-slate-600">
            <span className="font-bold text-brand-950">
              {house.base_price_per_night_formatted ||
                formatCurrencyFromCents(house.base_price_per_night_cents)}
            </span>
            <span className="text-slate-500"> / night</span>
          </p>
          <span className="flex items-center gap-1 text-xs text-slate-500">
            <Users className="h-3.5 w-3.5" aria-hidden />
            {house.max_guests}
          </span>
        </div>
      </div>
    </Link>
  )
}
