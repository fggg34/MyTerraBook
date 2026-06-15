import {
  ArrowRight,
  CalendarCheck,
  Check,
  Gauge,
  Tag,
} from 'lucide-react'
import { formatCurrency, formatCurrencyFromCents } from '../../utils/format'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { useShopConfig } from '../../context/ShopConfigContext'
import { fmtDisplayDate } from '../../utils/requestToBookUtils'

export default function BookingSummarySidebar({
  config,
  item,
  itemImage,
  form,
  quote,
  quoteLoading,
  nights,
  locationName,
  bookingType,
  selectedPriceType,
  onCouponApply,
}) {
  const price = useFormatPrice()
  const { prepayPercent } = useShopConfig()
  const kick = config.summaryKick(item)
  const isVehicle = bookingType !== 'guesthouse'

  const rateCents = isVehicle
    ? selectedPriceType?.from_price_per_day_cents || item?.price_types?.[0]?.from_price_per_day_cents
    : item?.base_price_per_night

  const rateDisplay = isVehicle
    ? (selectedPriceType?.from_price_per_day_cents != null
      ? price.formatCents(selectedPriceType.from_price_per_day_cents)
      : selectedPriceType?.from_price_per_day
        ? price.format(Number.parseFloat(selectedPriceType.from_price_per_day))
        : null)
    : item?.base_price_per_night_formatted || (rateCents ? price.formatCents(rateCents) : null)

  const pickDetail = bookingType === 'guesthouse'
    ? item?.check_in_time || 'From 15:00'
    : `${form.pickupTime} · ${locationName(form.pickup_location_id)}`
  const dropDetail = bookingType === 'guesthouse'
    ? item?.check_out_time || 'By 11:00'
    : `${form.dropoffTime} · ${locationName(form.sameReturn ? form.pickup_location_id : form.dropoff_location_id)}`

  const totalAmount = () => {
    if (!quote) return null
    if (bookingType === 'guesthouse') {
      if (quote.total_amount != null) return quote.total_amount / 100
      return null
    }
    return Number(quote.total) || null
  }

  const totalDisplay = () => {
    if (quoteLoading) return '…'
    const amount = totalAmount()
    if (amount == null) return '-'
    if (bookingType === 'guesthouse') return quote.total_formatted || price.format(amount)
    return price.format(amount)
  }

  const prepayAmount = () => {
    const total = totalAmount()
    if (total == null) return null
    return Math.round(total * (prepayPercent / 100) * 100) / 100
  }

  const balanceAmount = () => {
    const total = totalAmount()
    const prepay = prepayAmount()
    if (total == null || prepay == null) return null
    return Math.round((total - prepay) * 100) / 100
  }

  const feeLines = quote?.fees_lines || []
  const locationFees = feeLines.filter((l) => l.kind === 'location_fee' || l.kind === 'one_way_fee')
  const oohFees = feeLines.filter((l) => /out.of.hours|ooh/i.test(l.label || l.kind || ''))
  const otherFees = feeLines.filter(
    (l) => !locationFees.includes(l) && !oohFees.includes(l),
  )

  const protectionCost = isVehicle && quote && selectedPriceType
    ? (() => {
        const baseCents = selectedPriceType.from_price_per_day_cents || 0
        const days = quote.rental_days || nights
        const standardCents = item?.price_types?.[0]?.from_price_per_day_cents || 0
        const extra = Math.max(0, (baseCents - standardCents) * days)
        return extra > 0 ? { name: selectedPriceType.name, amount: extra / 100, currency: quote.currency } : null
      })()
    : null

  const selectedAddons = isVehicle && item?.rental_options
    ? item.rental_options.filter((o) => form.rental_option_ids.includes(Number(o.id)))
    : []

  const promoApplied = quote && Number(quote.discount_amount) > 0
  const promoCodeLabel = form.coupon_code.trim().toUpperCase() || 'PROMO'

  return (
    <aside className="summary">
      <div className="scard">
        <div className="scard-top">
          <div className="sthumb">
            {itemImage ? <img src={itemImage} alt={item?.name || ''} /> : null}
          </div>
          <div className="sinfo">
            <div className="skick">{kick}</div>
            <h4>{item?.name}</h4>
            {rateDisplay && (
              <div className="srate">
                <b>{rateDisplay}</b> / {config.step1.rateUnit}
                {item?.rating && <> · ★ {item.rating}</>}
              </div>
            )}
          </div>
        </div>
        <div className="strip">
          <div className="leg">
            <div className="lk">{config.step1.dateStartLabel}</div>
            <div className="ld">{form.startDate ? fmtDisplayDate(form.startDate) : ','}</div>
            <div className="lt">{pickDetail}</div>
          </div>
          <span className="arrowic"><ArrowRight aria-hidden /></span>
          <div className="leg">
            <div className="lk">{config.step1.dateEndLabel}</div>
            <div className="ld">{form.endDate ? fmtDisplayDate(form.endDate) : ','}</div>
            <div className="lt">{dropDetail}</div>
          </div>
        </div>
        <div className="lines">
          {quote && isVehicle && (
            <>
              <div className="lrow">
                <span className="ll">
                  {rateDisplay} × {quote.rental_days || nights} {config.step1.rateUnit}{quote.rental_days !== 1 ? 's' : ''}
                </span>
                <span className="lv">{price.format(quote.rental_subtotal)}</span>
              </div>
              {locationFees.map((line) => (
                <div key={`loc-${line.label}`} className="lrow">
                  <span className="ll">Location service</span>
                  <span className="lv">{price.format(line.amount)}</span>
                </div>
              ))}
              {oohFees.map((line) => (
                <div key={`ooh-${line.label}`} className="lrow">
                  <span className="ll">Out-of-hours pick-up</span>
                  <span className="lv">{price.format(line.amount)}</span>
                </div>
              ))}
              {otherFees.map((line) => (
                <div key={`${line.kind}-${line.label}`} className="lrow">
                  <span className="ll">{line.label}</span>
                  <span className="lv">{price.format(line.amount)}</span>
                </div>
              ))}
              {protectionCost && (
                <div className="lrow">
                  <span className="ll">{protectionCost.name} protection ×{quote.rental_days || nights}</span>
                  <span className="lv">{price.format(protectionCost.amount)}</span>
                </div>
              )}
              {selectedAddons.map((opt) => {
                const unitCents = opt.cost_cents || 0
                const totalCents = opt.is_daily_cost ? unitCents * (quote.rental_days || nights) : unitCents
                return (
                  <div key={opt.id} className="lrow">
                    <span className="ll">{opt.name}</span>
                    <span className="lv">{formatCurrencyFromCents(totalCents, quote.currency)}</span>
                  </div>
                )
              })}
              {Number(quote.discount_amount) > 0 && (
                <div className="lrow discount">
                  <span className="ll">Promo · {form.coupon_code.trim().toUpperCase() || 'DISCOUNT'}</span>
                  <span className="lv">−{price.format(quote.discount_amount)}</span>
                </div>
              )}
              {Number(quote.tax_amount) > 0 && (
                <div className="lrow">
                  <span className="ll">Tax</span>
                  <span className="lv">{price.format(quote.tax_amount)}</span>
                </div>
              )}
            </>
          )}
          {quote && bookingType === 'guesthouse' && (
            <>
              <div className="lrow">
                <span className="ll">
                  {rateDisplay || '-'} × {nights} night{nights !== 1 ? 's' : ''}
                </span>
                <span className="lv">{formatCurrencyFromCents(quote.base_total, quote.currency)}</span>
              </div>
              {quote.cleaning_fee > 0 && (
                <div className="lrow">
                  <span className="ll">Cleaning fee</span>
                  <span className="lv">{formatCurrencyFromCents(quote.cleaning_fee, quote.currency)}</span>
                </div>
              )}
              {Number(quote.discount_amount) > 0 && (
                <div className="lrow discount">
                  <span className="ll">Promo · {form.coupon_code.trim().toUpperCase() || 'DISCOUNT'}</span>
                  <span className="lv">−{formatCurrencyFromCents(quote.discount_amount, quote.currency)}</span>
                </div>
              )}
              {quote.tax_amount > 0 && (
                <div className="lrow">
                  <span className="ll">Tax</span>
                  <span className="lv">{formatCurrencyFromCents(quote.tax_amount, quote.currency)}</span>
                </div>
              )}
            </>
          )}
          {!quote && !quoteLoading && (
            <div className="lrow muted"><span className="ll">Complete trip details for pricing</span></div>
          )}
        </div>
        <div className="total-row">
          <span className="tl">Total</span>
          <span className="tv">
            <small>{quote?.currency || 'EUR'}</small>
            {totalDisplay()}
          </span>
        </div>
        {quote && prepayAmount() != null && (
          <div className="prepay-block">
            <div className="prepay-title">Payment schedule</div>
            <div className="prepay-row">
              <span>Due on approval ({prepayPercent}%)</span>
              <b>{price.format(prepayAmount())}</b>
            </div>
            <div className="prepay-row">
              <span>{isVehicle ? 'Due on pick-up' : 'Due at check-in'}</span>
              <b>{price.format(balanceAmount())}</b>
            </div>
            <p className="prepay-note">
              A {prepayPercent}% prepayment secures your booking once the host approves and is non-refundable. The remaining balance is paid {isVehicle ? 'when you collect the vehicle' : 'at check-in'}.
            </p>
            {price.isConverted ? (
              <p className="prepay-note">Charged in {price.baseCurrency}; shown in {price.displayCurrency}.</p>
            ) : null}
          </div>
        )}
        <div className="scard-foot">
          <div className="ff">
            <span className="fl"><CalendarCheck aria-hidden />Cancellation</span>
            <span className="fv flex">Free until 48h before</span>
          </div>
          {isVehicle && (
            <div className="ff">
              <span className="fl"><Gauge aria-hidden />Mileage</span>
              <span className="fv">Unlimited · included</span>
            </div>
          )}
        </div>
      </div>
      <div className={`promo-card${promoApplied ? ' promo-card--applied' : ''}`}>
        <div className="promo-card-head">
          <span className="promo-card-icon" aria-hidden>
            <Tag />
          </span>
          <div className="promo-card-copy">
            <span className="promo-card-label">Promo code</span>
            <span className="promo-card-hint">
              {promoApplied ? `${promoCodeLabel} applied to your total` : 'Save on your trip with a valid code'}
            </span>
          </div>
        </div>
        <div className="promo-field">
          <input
            type="text"
            className="promo-input"
            placeholder={promoApplied ? `${promoCodeLabel} applied` : 'e.g. ICELAND10'}
            value={form.coupon_code}
            onChange={(e) => onCouponApply(e.target.value)}
            onKeyDown={(e) => {
              if (e.key === 'Enter') onCouponApply(form.coupon_code, true)
            }}
            aria-label="Promo code"
          />
          <button
            type="button"
            className="promo-apply"
            onClick={() => onCouponApply(form.coupon_code, true)}
          >
            Apply
          </button>
        </div>
        {promoApplied && (
          <p className="promo-saved">
            <Check aria-hidden />
            You&apos;re saving{' '}
            {bookingType === 'guesthouse'
              ? formatCurrencyFromCents(quote.discount_amount, quote.currency)
              : formatCurrency(quote.discount_amount, quote.currency)}
          </p>
        )}
      </div>
      <div className="reassure">
        {config.reassurance.map((r, i) => (
          <div key={i} className="ra">
            <span className="ric"><Check aria-hidden /></span>
            <span>
              {r.bold && <b>{r.bold}</b>}
              {r.text && (r.bold ? ` ${r.text}` : r.text)}
            </span>
          </div>
        ))}
      </div>
    </aside>
  )
}
