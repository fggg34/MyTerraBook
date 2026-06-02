import { Fuel, Gauge, Tag, Users } from 'lucide-react'
import { Link } from 'react-router-dom'
import { resolveStorageUrl } from '../../api'
import { QuoteFeeLines } from './QuotePricingBreakdown'
import { formatCurrency } from '../../utils/format'

export default function CarCard({ car, searchQuery = '', categoryName }) {
  const imageSrc = resolveStorageUrl(car.thumbnail_url || car.main_image_path)
  const price = car.base_daily_price ?? '0.00'
  const pricing = car.search_pricing
  const detailUrl = `/cars/${car.id}${searchQuery ? `?${searchQuery}` : ''}`
  const checkoutUrl = `/checkout?car_id=${car.id}${searchQuery ? `&${searchQuery}` : ''}`

  return (
    <article className="group flex flex-col overflow-hidden rounded-xl bg-white shadow-card transition-all duration-300 hover:-translate-y-1 hover:shadow-card-hover">
      <Link to={detailUrl} className="relative block aspect-[16/10] overflow-hidden bg-brand-100">
        {imageSrc ? (
          <img
            src={imageSrc}
            alt={car.name}
            className="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            loading="lazy"
          />
        ) : (
          <div className="flex h-full items-center justify-center bg-gradient-to-br from-brand-800 to-brand-950">
            <Gauge className="h-16 w-16 text-white/30" aria-hidden />
          </div>
        )}
        {categoryName && (
          <span className="absolute left-3 top-3 rounded-full bg-white/95 px-2.5 py-0.5 text-xs font-semibold text-brand-800 shadow-sm">
            {categoryName}
          </span>
        )}
        {pricing?.has_special_discount && (
          <span className="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-emerald-600 px-2.5 py-0.5 text-xs font-semibold text-white shadow-sm">
            <Tag className="h-3 w-3" aria-hidden />
            Special price
          </span>
        )}
      </Link>

      <div className="flex flex-1 flex-col p-5">
        <Link to={detailUrl}>
          <h3 className="text-lg font-bold text-brand-950 transition-colors group-hover:text-accent">
            {car.name}
          </h3>
        </Link>

        <div className="mt-3 flex flex-wrap gap-2 text-xs text-slate-600">
          <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 capitalize">
            <Gauge className="h-3.5 w-3.5" aria-hidden />
            {car.transmission}
          </span>
          <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1 capitalize">
            <Fuel className="h-3.5 w-3.5" aria-hidden />
            {car.fuel_type}
          </span>
          {car.units_available != null && (
            <span className="inline-flex items-center gap-1 rounded-full bg-slate-100 px-2.5 py-1">
              <Users className="h-3.5 w-3.5" aria-hidden />
              {car.units_available} unit{car.units_available !== 1 ? 's' : ''}
            </span>
          )}
        </div>

        {pricing ? (
          <div className="mt-4 space-y-1">
            <div className="flex flex-wrap items-baseline gap-x-2 gap-y-1">
              {Number(pricing.special_discount_amount) > 0 && (
                <span className="text-sm text-slate-400 line-through">
                  {formatCurrency(pricing.rental_before_specials, pricing.currency)}
                </span>
              )}
              <span className="text-2xl font-bold text-brand-950">
                {formatCurrency(pricing.rental_subtotal, pricing.currency)}
              </span>
              <span className="text-sm text-slate-500">
                rental · {pricing.rental_days} day{pricing.rental_days !== 1 ? 's' : ''}
              </span>
            </div>
            {Number(pricing.special_discount_amount) > 0 ? (
              <p className="text-sm font-medium text-emerald-600">
                Special discount: -{formatCurrency(pricing.special_discount_amount, pricing.currency)}
              </p>
            ) : (
              <p className="text-sm text-slate-500">No special price discount for these dates</p>
            )}
            {(pricing.special_prices_applied || []).map((line) => (
              <p key={`${line.name}-${line.amount}`} className="text-xs text-slate-500">
                {line.name}: {line.direction === 'discount' ? '-' : '+'}
                {formatCurrency(line.amount, pricing.currency)}
              </p>
            ))}
            <QuoteFeeLines
              feesLines={pricing.fees_lines}
              currency={pricing.currency}
              className="text-xs text-slate-600"
            />
            <p className="text-sm text-slate-600">
              Total from {formatCurrency(pricing.total, pricing.currency)}
            </p>
          </div>
        ) : (
          <div className="mt-4 flex items-baseline gap-1">
            <span className="text-2xl font-bold text-brand-950">
              {formatCurrency(price)}
            </span>
            <span className="text-sm text-slate-500">/ day</span>
          </div>
        )}

        <div className="mt-auto flex gap-2 pt-5">
          <Link to={detailUrl} className="btn-secondary flex-1 text-center">
            View Details
          </Link>
          <Link to={checkoutUrl} className="btn-primary flex-1 text-center">
            Rent Now
          </Link>
        </div>
      </div>
    </article>
  )
}
