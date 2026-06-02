import { formatCurrency } from '../../utils/format'

export function QuoteFeeLines({ feesLines = [], currency = 'EUR', className = 'text-slate-600' }) {
  if (!feesLines.length) return null

  return (
    <>
      {feesLines.map((line) => (
        <div
          key={`${line.kind}-${line.label}-${line.amount}`}
          className={`flex justify-between gap-3 ${className}`}
        >
          <span className="min-w-0 flex-1">{line.label}</span>
          <span className="shrink-0">{formatCurrency(line.amount, currency)}</span>
        </div>
      ))}
    </>
  )
}

export function QuotePricingBreakdown({ quote, compact = false }) {
  if (!quote) return null

  const textClass = compact ? 'text-xs text-slate-500' : 'text-sm text-slate-600'
  const feeClass = compact ? 'text-xs text-slate-500' : 'text-sm text-slate-600'

  return (
    <div className={compact ? 'space-y-0.5' : 'space-y-1'}>
      <div className={`flex justify-between ${textClass}`}>
        <span>Rental ({quote.rental_days} day{quote.rental_days !== 1 ? 's' : ''})</span>
        <span>{formatCurrency(quote.rental_subtotal, quote.currency)}</span>
      </div>

      {Number(quote.special_discount_amount) > 0 && (
        <>
          <div className={`flex justify-between text-slate-500 ${compact ? 'text-xs' : 'text-sm'}`}>
            <span>Before special prices</span>
            <span className="line-through">
              {formatCurrency(quote.rental_before_specials, quote.currency)}
            </span>
          </div>
          <div className={`flex justify-between text-emerald-600 ${compact ? 'text-xs' : 'text-sm'}`}>
            <span>Special price discount</span>
            <span>-{formatCurrency(quote.special_discount_amount, quote.currency)}</span>
          </div>
        </>
      )}

      {Number(quote.special_surcharge_amount) > 0 && (
        <div className={`flex justify-between text-amber-700 ${compact ? 'text-xs' : 'text-sm'}`}>
          <span>Special price surcharge</span>
          <span>+{formatCurrency(quote.special_surcharge_amount, quote.currency)}</span>
        </div>
      )}

      {(quote.special_prices_applied || []).map((line) => (
        <div
          key={`${line.name}-${line.amount}`}
          className={`flex justify-between gap-3 ${compact ? 'text-xs' : 'text-sm'} text-slate-500`}
        >
          <span className="min-w-0 flex-1">{line.name}</span>
          <span className="shrink-0">
            {line.direction === 'discount' ? '-' : '+'}
            {formatCurrency(line.amount, quote.currency)}
          </span>
        </div>
      ))}

      <QuoteFeeLines
        feesLines={quote.fees_lines}
        currency={quote.currency}
        className={feeClass}
      />

      {Number(quote.discount_amount) > 0 && (
        <div className={`flex justify-between text-emerald-600 ${compact ? 'text-xs' : 'text-sm'}`}>
          <span>Coupon discount</span>
          <span>-{formatCurrency(quote.discount_amount, quote.currency)}</span>
        </div>
      )}

      {Number(quote.tax_amount) > 0 && (
        <div className={`flex justify-between ${textClass}`}>
          <span>Tax</span>
          <span>{formatCurrency(quote.tax_amount, quote.currency)}</span>
        </div>
      )}
    </div>
  )
}
