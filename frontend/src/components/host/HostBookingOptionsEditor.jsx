import { useEffect, useMemo, useState } from 'react'
import { useHostCurrency } from '../../hooks/useHostCurrency'
import { calculateRentalOptionTotalCents } from '../../utils/rentalOptionPricing'
import { getProtectionPresentation } from '../../data/requestToBookConfig'

function buildChanges(priceTypeId, rentalOptionIds) {
  return {
    price_type_id: Number(priceTypeId) || undefined,
    rental_options: rentalOptionIds.map(Number),
  }
}

export default function HostBookingOptionsEditor({
  booking,
  onPreview,
  onSave,
  onChange,
  compact = false,
  saveLabel = 'Save changes',
  showActions = true,
}) {
  const [priceTypeId, setPriceTypeId] = useState(String(booking.price_type_id || ''))
  const [rentalOptionIds, setRentalOptionIds] = useState(booking.rental_option_ids || [])
  const [preview, setPreview] = useState(null)
  const [previewing, setPreviewing] = useState(false)
  const [saving, setSaving] = useState(false)

  useEffect(() => {
    setPriceTypeId(String(booking.price_type_id || ''))
    setRentalOptionIds(booking.rental_option_ids || [])
    setPreview(null)
  }, [booking.id, booking.price_type_id, booking.rental_option_ids])

  const rentalDays = booking.rental_days || 1
  const priceTypes = booking.available_price_types || []
  const addons = booking.available_rental_options || []
  const currency = useHostCurrency()

  const hasChanges = useMemo(() => {
    const currentOptions = [...(booking.rental_option_ids || [])].map(Number).sort()
    const nextOptions = [...rentalOptionIds].map(Number).sort()
    return String(booking.price_type_id) !== String(priceTypeId)
      || JSON.stringify(currentOptions) !== JSON.stringify(nextOptions)
  }, [booking, priceTypeId, rentalOptionIds])

  const emitChange = (nextPriceTypeId, nextRentalOptionIds) => {
    onChange?.(buildChanges(nextPriceTypeId, nextRentalOptionIds))
  }

  const toggleAddon = (id) => {
    const num = Number(id)
    setRentalOptionIds((prev) => {
      const next = prev.includes(num) ? prev.filter((x) => x !== num) : [...prev, num]
      emitChange(priceTypeId, next)
      return next
    })
    setPreview(null)
  }

  const runPreview = async () => {
    setPreviewing(true)
    try {
      const data = await onPreview(buildChanges(priceTypeId, rentalOptionIds))
      setPreview(data)
    } catch {
      setPreview(null)
    } finally {
      setPreviewing(false)
    }
  }

  const handleSave = async () => {
    setSaving(true)
    try {
      await onSave(buildChanges(priceTypeId, rentalOptionIds))
      setPreview(null)
    } finally {
      setSaving(false)
    }
  }

  if (!booking.can_modify_options) return null

  return (
    <section className={`host-booking-options${compact ? ' host-booking-options--compact' : ''}`}>
      {!compact && <h3>Protection & extras</h3>}
      {!compact && (
        <p className="host-booking-options__lead">
          Change the insurance plan or add-ons for this booking. Totals are recalculated automatically.
        </p>
      )}

      {priceTypes.length > 0 && (
        <div className="host-booking-options__block">
          <h4>Insurance plan</h4>
          <div className="host-booking-options__plans">
            {priceTypes.map((pt, idx) => {
              const selected = String(pt.id) === String(priceTypeId)
              const pres = getProtectionPresentation(pt, idx)
              return (
                <label key={pt.id} className={`host-booking-options__plan${selected ? ' is-selected' : ''}`}>
                  <input
                    type="radio"
                    name={`plan-${booking.id}`}
                    checked={selected}
                    onChange={() => {
                      const next = String(pt.id)
                      setPriceTypeId(next)
                      emitChange(next, rentalOptionIds)
                      setPreview(null)
                    }}
                  />
                  <span className="host-booking-options__plan-name">{pt.name}</span>
                  <span className="host-booking-options__plan-meta">
                    {pt.from_price_per_day_cents > 0
                      ? `${currency.formatCents(pt.from_price_per_day_cents)} / day`
                      : 'Included'}
                  </span>
                  <span className="host-booking-options__plan-dep">
                    {pt.attribute_value_per_day || pres.deposit}
                  </span>
                </label>
              )
            })}
          </div>
        </div>
      )}

      {addons.length > 0 && (
        <div className="host-booking-options__block">
          <h4>Extra options</h4>
          <ul className="host-booking-options__addons">
            {addons.map((opt) => {
              const selected = rentalOptionIds.includes(Number(opt.id))
              const totalCents = calculateRentalOptionTotalCents(opt.cost_cents, opt.is_daily_cost, rentalDays)
              return (
                <li key={opt.id}>
                  <label className={`host-booking-options__addon${selected ? ' is-selected' : ''}`}>
                    <input
                      type="checkbox"
                      checked={selected}
                      onChange={() => toggleAddon(opt.id)}
                      disabled={opt.is_mandatory}
                    />
                    <span>
                      <strong>{opt.name}</strong>
                      {opt.description && <small>{opt.description}</small>}
                    </span>
                    <span>{currency.formatCents(totalCents)}</span>
                  </label>
                </li>
              )
            })}
          </ul>
        </div>
      )}

      <div className="host-booking-options__actions">
        {showActions && (
          <>
            <button
              type="button"
              className="host-btn secondary"
              disabled={!hasChanges || previewing}
              onClick={runPreview}
            >
              {previewing ? 'Calculating…' : 'Preview new total'}
            </button>
            <button
              type="button"
              className="host-btn primary"
              disabled={!hasChanges || saving}
              onClick={handleSave}
            >
              {saving ? 'Saving…' : saveLabel}
            </button>
          </>
        )}
      </div>

      {preview && (
        <p className="host-booking-options__preview">
          New total: <strong>{preview.total_formatted}</strong>
          {preview.price_delta_cents !== 0 && (
            <span>
              {' '}              ({preview.price_delta_cents >= 0 ? '+' : '−'}
              {currency.formatCents(Math.abs(preview.price_delta_cents))})
            </span>
          )}
        </p>
      )}
    </section>
  )
}
