import { useEffect, useMemo, useState } from 'react'
import { useSearchParams } from 'react-router-dom'
import { api } from '../../api'
import { useFormatPrice } from '../../hooks/useFormatPrice'
import { rangeIncludesBlockedDate } from '../../utils/bookingRestrictions'
import { carenPriceLabel, formatCarenPrice } from '../../utils/carenPricing'
import FoxieInsurancePlans, { defaultFoxieInsuranceId } from './FoxieInsurancePlans'
import { formatDateTimeAt, parseTimeParts } from '../../utils/format'
import DateRangePicker, { parseDateOnly } from '../ui/DateRangePicker'
import FieldSelect from '../ui/FieldSelect'

function locationOptions(locations) {
  return (locations || []).map((location) => ({
    value: String(location.id),
    label: location.name,
  }))
}

function tripTimes(car, startDate, endDate) {
  const pickupParts = parseTimeParts(car.pickup_time_from) || { hours: 9, minutes: 0 }
  const dropoffParts = parseTimeParts(car.dropoff_time_from) || { hours: 10, minutes: 0 }
  return {
    pickup_at: formatDateTimeAt(startDate, pickupParts.hours, pickupParts.minutes),
    dropoff_at: formatDateTimeAt(endDate, dropoffParts.hours, dropoffParts.minutes),
  }
}

export default function CarenPricePanel({
  car,
  typeConfig,
  openCalendarRef,
  bookingDatesRef,
  onRequestBook,
  onAvailabilityChange,
}) {
  const [query] = useSearchParams()
  const formatter = useFormatPrice()
  const pickups = car.pickup_locations || []
  const dropoffs = car.dropoff_locations || []
  const pickupChoices = useMemo(() => locationOptions(pickups), [car.pickup_locations])
  const dropoffChoices = useMemo(() => locationOptions(dropoffs), [car.dropoff_locations])
  const [pickupLocationId, setPickupLocationId] = useState(() => query.get('pickup_location_id') || '')
  const [dropoffLocationId, setDropoffLocationId] = useState(
    () => query.get('dropoff_location_id') || query.get('pickup_location_id') || '',
  )
  const [startDate, setStartDate] = useState(() => parseDateOnly(query.get('pickup_at')))
  const [endDate, setEndDate] = useState(() => parseDateOnly(query.get('dropoff_at')))
  const [blockedDates, setBlockedDates] = useState([])
  const [blockedDatesLoaded, setBlockedDatesLoaded] = useState(false)
  const [availabilityError, setAvailabilityError] = useState(false)
  const [result, setResult] = useState(null)
  const [loading, setLoading] = useState(false)
  const [attempt, setAttempt] = useState(0)
  const insurancePlans = car.external_provider === 'foxie' ? (car.insurance_plans || []) : []
  const [insuranceId, setInsuranceId] = useState(() => defaultFoxieInsuranceId(insurancePlans))

  useEffect(() => {
    setPickupLocationId((current) => {
      if (current && pickups.some((location) => String(location.id) === current)) return current
      return pickups.length === 1 ? String(pickups[0].id) : ''
    })
    setDropoffLocationId((current) => {
      if (current && dropoffs.some((location) => String(location.id) === current)) return current
      return dropoffs.length === 1 ? String(dropoffs[0].id) : ''
    })
  }, [car.id, pickups, dropoffs])

  useEffect(() => {
    let cancelled = false
    setBlockedDatesLoaded(false)
    const params = {}
    if (pickupLocationId) params.pickup_location_id = pickupLocationId
    if (dropoffLocationId) params.dropoff_location_id = dropoffLocationId
    api.get(`/cars/${car.id}/availability-calendar`, { params })
      .then((res) => {
        if (cancelled) return
        setBlockedDates(res.data?.blocked_dates ?? [])
        setAvailabilityError(false)
      })
      .catch(() => {
        if (cancelled) return
        setBlockedDates([])
        setAvailabilityError(true)
      })
      .finally(() => {
        if (!cancelled) setBlockedDatesLoaded(true)
      })
    return () => { cancelled = true }
  }, [car.id, pickupLocationId, dropoffLocationId])

  const foxie = car.external_provider === 'foxie'
  const datesUnavailable = useMemo(
    () => (foxie ? false : rangeIncludesBlockedDate(startDate, endDate, blockedDates)),
    [foxie, startDate, endDate, blockedDates],
  )
  const bookingBlocked = foxie ? false : (!blockedDatesLoaded || datesUnavailable)
  const times = startDate && endDate ? tripTimes(car, startDate, endDate) : { pickup_at: '', dropoff_at: '' }
  const tripReady = Boolean(
    pickupLocationId && dropoffLocationId && times.pickup_at && times.dropoff_at && times.dropoff_at > times.pickup_at,
  )
  const pricing = !datesUnavailable ? result?.pricing : null

  useEffect(() => {
    onAvailabilityChange?.({
      datesUnavailable: bookingBlocked,
      blockedDatesLoaded,
    })
    if (bookingDatesRef) {
      bookingDatesRef.current = {
        pickupDate: startDate,
        dropoffDate: endDate,
        pickupLocationId,
        dropoffLocationId,
        priced: Boolean(pricing),
        datesUnavailable: bookingBlocked,
        blockedDatesLoaded,
        foxieInsuranceId: insuranceId,
      }
    }
  }, [
    startDate, endDate, pickupLocationId, dropoffLocationId, insuranceId,
    pricing, bookingBlocked, blockedDatesLoaded, onAvailabilityChange, bookingDatesRef,
  ])

  useEffect(() => {
    setResult(null)
    if (!tripReady || datesUnavailable) {
      setLoading(false)
      return undefined
    }
    let cancelled = false
    setLoading(true)
    const timer = window.setTimeout(() => {
      const pricePath = car.external_provider === 'foxie' ? 'foxie-price' : 'caren-price'
      api.get(`/cars/${car.id}/${pricePath}`, {
        params: {
          pickup_location_id: pickupLocationId,
          dropoff_location_id: dropoffLocationId,
          pickup_at: times.pickup_at,
          dropoff_at: times.dropoff_at,
          ...(car.external_provider === 'foxie' && insuranceId ? { foxie_insurance_id: insuranceId } : {}),
        },
      })
        .then(({ data }) => { if (!cancelled) setResult(data) })
        .catch(() => { if (!cancelled) setResult({ status: 'error' }) })
        .finally(() => { if (!cancelled) setLoading(false) })
    }, 350)
    return () => { cancelled = true; window.clearTimeout(timer) }
  }, [
    car.id, car.external_provider, pickupLocationId, dropoffLocationId, insuranceId,
    times.pickup_at, times.dropoff_at, tripReady, datesUnavailable, attempt,
  ])

  const nights = startDate && endDate
    ? Math.max(1, Math.round((endDate.getTime() - startDate.getTime()) / 86400000))
    : 0
  const rateLabel = startDate && endDate
    ? `Rental · ${nights} ${nights === 1 ? 'day' : 'days'}`
    : (typeConfig?.rateLabelDefault || 'Daily rate')
  const priceText = pricing ? formatCarenPrice(pricing.rental_subtotal, pricing.currency, formatter) : ''
  const baseText = !pricing && car.base_daily_price
    ? formatCarenPrice(car.base_daily_price, car.base_daily_currency || 'ISK', formatter)
    : ''
  const canBook = Boolean(car.caren_booking_enabled && pricing && !bookingBlocked && !loading)

  const choosePickup = (value) => {
    setDropoffLocationId((current) => {
      const samePlace = !current || current === pickupLocationId
      if (samePlace && dropoffs.some((location) => String(location.id) === value)) return value
      return dropoffs.some((location) => String(location.id) === current) ? current : ''
    })
    setPickupLocationId(value)
  }

  return (
    <div className="listing-bcard">
      <div className="listing-loc-fields">
        <label className="field">
          <span className="flabel">Pick-up location</span>
          <FieldSelect
            value={pickupLocationId}
            onChange={choosePickup}
            options={pickupChoices}
            placeholder="Select location"
            ariaLabel="Pick-up location"
            disabled={pickupChoices.length === 0}
          />
        </label>
        <label className="field">
          <span className="flabel">Drop-off location</span>
          <FieldSelect
            value={dropoffLocationId}
            onChange={setDropoffLocationId}
            options={dropoffChoices}
            placeholder="Select location"
            ariaLabel="Drop-off location"
            disabled={dropoffChoices.length === 0}
          />
        </label>
      </div>
      <div className="date-wrap" id="dateWrap">
        <DateRangePicker
          ref={openCalendarRef}
          startLabel={typeConfig?.dateStartLabel || 'Pick-up'}
          endLabel={typeConfig?.dateEndLabel || 'Drop-off'}
          startDate={startDate}
          endDate={endDate}
          rateUnit={typeConfig?.rateUnit || 'day'}
          totalAmount={pricing ? Number(pricing.rental_subtotal) : null}
          totalLoading={loading}
          formatTotal={pricing ? () => priceText : undefined}
          fixedPopper
          blockedDates={foxie ? [] : blockedDates}
          onChange={({ start, end }) => {
            setStartDate(start)
            setEndDate(end)
          }}
        />
      </div>
      {insurancePlans.length > 0 ? (
        <FoxieInsurancePlans
          plans={insurancePlans}
          selectedId={insuranceId}
          onSelect={setInsuranceId}
          formatter={formatter}
        />
      ) : null}
      <div className="rate-row">
        <span className="rl">{rateLabel}</span>
        <span className="rr">
          {startDate && endDate ? (
            loading ? <b>…</b> : priceText ? <b>{priceText}</b> : <b>-</b>
          ) : (
            baseText ? <b>{baseText}</b> : <b>-</b>
          )}
        </span>
      </div>
      {pricing ? (
        <p className="listing-fees-note">
          {formatCarenPrice(pricing.daily_price, pricing.currency, formatter)} per day on average. Rental estimate only. Extras and additional charges may apply.
        </p>
      ) : baseText ? (
        <p className="listing-fees-note">From {baseText} per day. Select dates for the trip total.</p>
      ) : null}
      {datesUnavailable && blockedDatesLoaded ? (
        <p className="listing-unavailable-note">Selected dates are not available for booking.</p>
      ) : null}
      {!datesUnavailable && tripReady && !loading && result && !pricing ? (
        <p className="listing-unavailable-note">{carenPriceLabel(result.status)}</p>
      ) : null}
      {availabilityError ? (
        <p className="listing-unavailable-note">Availability could not be checked. The price check still confirms these dates.</p>
      ) : null}
      {!blockedDatesLoaded ? (
        <p className="listing-availability-loading">Checking availability…</p>
      ) : null}
      {result?.status === 'error' ? (
        <button type="button" className="book-link" onClick={() => setAttempt((value) => value + 1)}>
          Retry price check
        </button>
      ) : null}
      <button
        className="book-btn"
        id="listingBookBtn"
        type="button"
        disabled={!canBook}
        onClick={(event) => {
          event.stopPropagation()
          if (!canBook) return
          onRequestBook?.({ pickupDate: startDate, dropoffDate: endDate })
        }}
      >
        {typeConfig?.bookCta || 'Book Now'}
      </button>
      <button className="book-link" id="bookProcessLink" type="button">
        View the booking process
      </button>
      {!car.caren_booking_enabled ? (
        <p className="listing-fees-note">Online booking is not yet available for this vehicle.</p>
      ) : null}
      <div className="listing-bcard-div" />
      <div className="trust-points">
        {(typeConfig?.trustPoints || []).map((point) => (
          <div key={point.html} className="tp">
            <span className="tp-ic" aria-hidden>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round">
                <path d="m5 12 4 4 10-10" />
              </svg>
            </span>
            <span dangerouslySetInnerHTML={{ __html: point.html }} />
          </div>
        ))}
      </div>
    </div>
  )
}
