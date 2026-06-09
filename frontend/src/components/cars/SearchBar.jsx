import { MapPin, Search } from 'lucide-react'
import { useEffect, useMemo, useState } from 'react'
import { useBookingRules } from '../../hooks/useBookingRules'
import useLocationOptions, { toFieldSelectOptions, useAutoSelectLocation } from '../../hooks/useLocationOptions'
import {
  computeMaxDropoffDate,
  computeMinDropoffDate,
  dropoffFilterDate,
  ensureValidDropoff,
  minRentalHint,
} from '../../utils/bookingRules'
import DateTimePicker from '../ui/DateTimePicker'
import { parseDateTimeLocal } from '../../utils/format'

export default function SearchBar({ initialValues = {}, onSearch, variant = 'hero' }) {
  const [form, setForm] = useState({
    pickup_location_id: initialValues.pickup_location_id || '',
    dropoff_location_id: initialValues.dropoff_location_id || '',
    pickup_at: initialValues.pickup_at || '',
    dropoff_at: initialValues.dropoff_at || '',
  })

  const { options: pickupOptions, isEmpty: pickupEmpty, loading: pickupLoading } = useLocationOptions({
    role: 'pickup',
    limit: 50,
  })

  const { options: dropoffOptions } = useLocationOptions({
    role: 'dropoff',
    pickupLocationId: form.pickup_location_id,
    enabled: !!form.pickup_location_id,
    limit: 50,
  })

  useAutoSelectLocation({
    options: pickupOptions,
    value: form.pickup_location_id,
    onSelect: (id) => {
      setForm((prev) => ({
        ...prev,
        pickup_location_id: id,
        dropoff_location_id: prev.dropoff_location_id || id,
      }))
    },
  })

  useAutoSelectLocation({
    options: dropoffOptions,
    value: form.dropoff_location_id,
    pickupValueForDropoff: form.pickup_location_id,
    onSelect: (id) => {
      setForm((prev) => ({ ...prev, dropoff_location_id: id }))
    },
  })

  const pickupSelectOptions = useMemo(() => toFieldSelectOptions(pickupOptions), [pickupOptions])
  const dropoffSelectOptions = useMemo(() => toFieldSelectOptions(dropoffOptions), [dropoffOptions])

  useEffect(() => {
    setForm((prev) => ({ ...prev, ...initialValues }))
  }, [initialValues.pickup_location_id, initialValues.dropoff_location_id, initialValues.pickup_at, initialValues.dropoff_at])

  const handleSubmit = (e) => {
    e.preventDefault()
    onSearch?.(form)
  }

  const isHero = variant === 'hero'
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
    setForm((prev) => ({ ...prev, pickup_at, dropoff_at }))
  }

  useEffect(() => {
    if (rules.loading || !pickupDate || !form.dropoff_at) return
    const corrected = ensureValidDropoff(pickupDate, form.dropoff_at, minRentalDays)
    if (corrected !== form.dropoff_at) {
      setForm((prev) => ({ ...prev, dropoff_at: corrected }))
    }
  }, [rules.loading, minRentalDays, pickupDate?.getTime()])

  const shellClass = isHero
    ? 'rounded-2xl bg-white p-4 shadow-2xl sm:p-6'
    : 'rounded-xl border border-slate-200 bg-white p-4 shadow-card'

  return (
    <form onSubmit={handleSubmit} className={`${shellClass} space-y-4`}>
      {!pickupLoading && pickupEmpty && (
        <p className="text-sm text-amber-700" role="status">
          Pickup locations are being configured. Assign locations to vehicles in admin to enable search.
        </p>
      )}
      <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div>
          <label className="label-field flex items-center gap-1.5">
            <MapPin className="h-4 w-4 text-accent" aria-hidden />
            Pick-up
          </label>
          <select
            className="input-field"
            value={form.pickup_location_id}
            onChange={(e) => {
              const value = e.target.value
              const sameAsPickup = form.dropoff_location_id === form.pickup_location_id
              setForm({
                ...form,
                pickup_location_id: value,
                dropoff_location_id: sameAsPickup ? value : form.dropoff_location_id,
              })
            }}
            required
            disabled={pickupEmpty}
          >
            <option value="">Select location</option>
            {pickupSelectOptions.map((loc) => (
              <option key={loc.value} value={loc.value}>
                {loc.label}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="label-field flex items-center gap-1.5">
            <MapPin className="h-4 w-4 text-accent" aria-hidden />
            Drop-off
          </label>
          <select
            className="input-field"
            value={form.dropoff_location_id}
            onChange={(e) => setForm({ ...form, dropoff_location_id: e.target.value })}
            required
            disabled={!form.pickup_location_id || pickupEmpty}
          >
            <option value="">Select location</option>
            {dropoffSelectOptions.map((loc) => (
              <option key={loc.value} value={loc.value}>
                {loc.label}
              </option>
            ))}
          </select>
        </div>

        <div>
          <label className="label-field" htmlFor="search-pickup-at">
            Pick-up date &amp; time
          </label>
          <DateTimePicker
            id="search-pickup-at"
            value={form.pickup_at}
            onChange={handlePickupChange}
            minDate={minPickup}
            placeholder="When do you pick up?"
            required
            fixedPopper={isHero}
          />
        </div>

        <div>
          <label className="label-field" htmlFor="search-dropoff-at">
            Drop-off date &amp; time
          </label>
          <DateTimePicker
            id="search-dropoff-at"
            value={form.dropoff_at}
            onChange={(dropoff_at) => setForm({ ...form, dropoff_at })}
            minDate={minDropoffDate}
            maxDate={maxDropoffDate}
            filterDate={dropoffDateFilter}
            placeholder="When do you return?"
            required
            fixedPopper={isHero}
          />
          {rentalHint && (
            <p className="mt-1 text-xs text-slate-500">{rentalHint}</p>
          )}
        </div>
      </div>

      <div className="flex justify-stretch sm:justify-end">
        <button type="submit" className={`btn-primary w-full sm:w-auto sm:min-w-[12rem] ${isHero ? 'py-3.5' : ''}`}>
          <Search className="h-4 w-4" aria-hidden />
          Search Cars
        </button>
      </div>
    </form>
  )
}
