import { MapPin, Tag } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { api } from '../../api'
import { useBookingRules } from '../../hooks/useBookingRules'
import {
  computeMaxDropoffDate,
  computeMinDropoffDate,
  dropoffFilterDate,
  ensureValidDropoff,
  minRentalHint,
} from '../../utils/bookingRules'
import DateTimePicker from '../ui/DateTimePicker'
import { QuotePricingBreakdown } from './QuotePricingBreakdown'
import { formatCurrency, parseDateTimeLocal } from '../../utils/format'

export default function BookingForm({
  carId,
  priceTypes = [],
  initialValues = {},
  selectedPriceTypeId,
  onPriceTypeChange,
  quote,
  quoteLoading,
  onSubmit,
  onFormChange,
  submitLabel = 'Book Now',
  submitDisabled = false,
}) {
  const [locations, setLocations] = useState([])
  const [form, setForm] = useState({
    pickup_location_id: initialValues.pickup_location_id || '',
    dropoff_location_id: initialValues.dropoff_location_id || '',
    pickup_at: initialValues.pickup_at || '',
    dropoff_at: initialValues.dropoff_at || '',
    price_type_id: selectedPriceTypeId || initialValues.price_type_id || '',
  })

  useEffect(() => {
    api.get('/locations').then((res) => setLocations(res.data.data || []))
  }, [])

  useEffect(() => {
    if (selectedPriceTypeId) {
      setForm((prev) => {
        const next = { ...prev, price_type_id: selectedPriceTypeId }
        onFormChange?.({ ...next, car_id: carId })
        return next
      })
    }
  }, [selectedPriceTypeId, carId, onFormChange])

  const updateForm = (patch) => {
    setForm((prev) => {
      const next = { ...prev, ...patch }
      onFormChange?.({ ...next, car_id: carId })
      return next
    })
  }

  const handleSubmit = (e) => {
    e.preventDefault()
    onSubmit?.({ ...form, car_id: carId })
  }

  const pickupDate = useMemo(() => parseDateTimeLocal(form.pickup_at), [form.pickup_at])
  const dropoffDate = useMemo(() => parseDateTimeLocal(form.dropoff_at), [form.dropoff_at])
  const minPickup = useMemo(() => {
    const now = new Date()
    now.setMinutes(0, 0, 0)
    return now
  }, [])

  const rules = useBookingRules(pickupDate, dropoffDate)
  const minRentalDays = rules.min_rental_days || 1
  const rentalHint = minRentalHint(minRentalDays)

  const minDropoffDate = useMemo(() => {
    const base = pickupDate || minPickup
    if (minRentalDays > 1) return computeMinDropoffDate(base, minRentalDays)
    return base
  }, [pickupDate, minPickup, minRentalDays])

  const maxDropoffDate = useMemo(
    () => (pickupDate && rules.max_rental_days ? computeMaxDropoffDate(pickupDate, rules.max_rental_days) : null),
    [pickupDate, rules.max_rental_days],
  )

  const dropoffDateFilter = useMemo(
    () => (pickupDate ? dropoffFilterDate(pickupDate, minRentalDays) : undefined),
    [pickupDate, minRentalDays],
  )

  const handlePickupChange = (pickup_at) => {
    const nextPickup = parseDateTimeLocal(pickup_at)
    const dropoff_at = ensureValidDropoff(nextPickup, form.dropoff_at, minRentalDays)
    updateForm({ pickup_at, dropoff_at })
  }

  useEffect(() => {
    if (rules.loading || !pickupDate || !form.dropoff_at) return
    const corrected = ensureValidDropoff(pickupDate, form.dropoff_at, minRentalDays)
    if (corrected !== form.dropoff_at) {
      updateForm({ dropoff_at: corrected })
    }
  }, [rules.loading, minRentalDays, pickupDate?.getTime()])

  return (
    <div className="rounded-xl border border-slate-200 bg-white p-6 shadow-card">
      <h3 className="text-lg font-bold text-brand-950">Book this car</h3>
      <p className="mt-1 text-sm text-slate-500">Select dates and location to see your total</p>

      <form onSubmit={handleSubmit} className="mt-5 space-y-4">
        {priceTypes.length > 0 && (
          <div>
            <label className="label-field flex items-center gap-1.5">
              <Tag className="h-4 w-4 text-accent" aria-hidden />
              Rate plan
            </label>
            <select
              className="input-field"
              value={form.price_type_id}
              onChange={(e) => {
                updateForm({ price_type_id: e.target.value })
                onPriceTypeChange?.(e.target.value)
              }}
              required
            >
              {priceTypes.map((pt) => (
                <option key={pt.id} value={pt.id}>
                  {pt.name}, from {formatCurrency(pt.from_price_per_day)}/day
                </option>
              ))}
            </select>
          </div>
        )}

        <div>
          <label className="label-field flex items-center gap-1.5">
            <MapPin className="h-4 w-4 text-accent" aria-hidden />
            Pick-up location
          </label>
          <select
            className="input-field"
            value={form.pickup_location_id}
            onChange={(e) => updateForm({ pickup_location_id: e.target.value })}
            required
          >
            <option value="">Select</option>
            {locations.map((loc) => (
              <option key={loc.id} value={loc.id}>
                {loc.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="label-field flex items-center gap-1.5">
            <MapPin className="h-4 w-4 text-accent" aria-hidden />
            Drop-off location
          </label>
          <select
            className="input-field"
            value={form.dropoff_location_id}
            onChange={(e) => updateForm({ dropoff_location_id: e.target.value })}
            required
          >
            <option value="">Select</option>
            {locations.map((loc) => (
              <option key={loc.id} value={loc.id}>
                {loc.name}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="label-field" htmlFor="booking-pickup-at">
            Pick-up date &amp; time
          </label>
          <DateTimePicker
            id="booking-pickup-at"
            value={form.pickup_at}
            onChange={handlePickupChange}
            minDate={minPickup}
            placeholder="Pick-up date & time"
            required
          />
        </div>

        <div>
          <label className="label-field" htmlFor="booking-dropoff-at">
            Drop-off date &amp; time
          </label>
          <DateTimePicker
            id="booking-dropoff-at"
            value={form.dropoff_at}
            onChange={(dropoff_at) => updateForm({ dropoff_at })}
            minDate={minDropoffDate}
            maxDate={maxDropoffDate}
            filterDate={dropoffDateFilter}
            placeholder="Drop-off date & time"
            required
          />
          {rentalHint && (
            <p className="mt-1 text-xs text-slate-500">{rentalHint}</p>
          )}
        </div>

        <div className="rounded-lg bg-brand-50 p-4">
          {quoteLoading ? (
            <p className="text-sm text-slate-500">Calculating price…</p>
          ) : quote ? (
            <div className="space-y-1 text-sm">
              <QuotePricingBreakdown quote={quote} />
              <div className="flex justify-between border-t border-brand-200 pt-2 text-base font-bold text-brand-950">
                <span>Total</span>
                <span className="text-accent">{formatCurrency(quote.total, quote.currency)}</span>
              </div>
            </div>
          ) : (
            <p className="text-sm text-slate-500">Fill in all fields to see pricing</p>
          )}
        </div>

        <button type="submit" className="btn-primary w-full py-3" disabled={submitDisabled || !quote}>
          {submitLabel}
        </button>
      </form>
    </div>
  )
}

export function buildCheckoutParams(form) {
  const params = new URLSearchParams()
  Object.entries(form).forEach(([k, v]) => {
    if (v) params.set(k, String(v))
  })
  return params.toString()
}
