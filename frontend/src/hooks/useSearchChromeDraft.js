import { useEffect, useMemo, useState } from 'react'
import useLocationOptions, { useAutoSelectLocation } from './useLocationOptions'
import { useBookingRules } from './useBookingRules'
import { ensureValidDropoff } from '../utils/bookingRules'
import { formatDateTimeLocal, parseDateTimeLocal } from '../utils/format'
import { parseDateOnly } from '../components/ui/DateRangePicker'

const PEOPLE_OPTIONS = Array.from({ length: 8 }, (_, i) => i + 1)

function toDateInputValue(iso) {
  if (!iso) return ''
  const d = iso instanceof Date ? iso : new Date(iso)
  if (Number.isNaN(d.getTime())) return ''
  const pad = (n) => String(n).padStart(2, '0')
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`
}

function dateTimeWithTime(date, time) {
  if (!date) return ''
  const [hours, minutes] = time.split(':').map(Number)
  const d = new Date(date)
  d.setHours(hours, minutes, 0, 0)
  return formatDateTimeLocal(d)
}

export default function useSearchChromeDraft({ vehicleType, query, updateSearch }) {
  const isGuesthouse = vehicleType === 'guesthouse'
  const vehicleMainCategory =
    vehicleType === 'campervan' ? 'campervan' : vehicleType === 'car' ? 'car' : ''

  const [vehicleDraft, setVehicleDraft] = useState({
    pickup_location_id: '',
    dropoff_location_id: '',
    pickup_at: '',
    dropoff_at: '',
  })
  const [guestDraft, setGuestDraft] = useState({
    city: '',
    check_in: '',
    check_out: '',
    guests: '2',
  })
  const [guestCityLabel, setGuestCityLabel] = useState('')

  const { options: pickupOptions, isEmpty: pickupEmpty } = useLocationOptions({
    role: 'pickup',
    mainCategory: vehicleMainCategory,
    enabled: !isGuesthouse,
    limit: 50,
  })

  const { options: dropoffOptions } = useLocationOptions({
    role: 'dropoff',
    pickupLocationId: vehicleDraft.pickup_location_id,
    mainCategory: vehicleMainCategory,
    enabled: !isGuesthouse && !!vehicleDraft.pickup_location_id,
    limit: 50,
  })

  useAutoSelectLocation({
    options: pickupOptions,
    value: vehicleDraft.pickup_location_id,
    onSelect: (id) => {
      setVehicleDraft((prev) => ({
        ...prev,
        pickup_location_id: id,
        dropoff_location_id: prev.dropoff_location_id || id,
      }))
    },
  })

  useAutoSelectLocation({
    options: dropoffOptions,
    value: vehicleDraft.dropoff_location_id,
    pickupValueForDropoff: vehicleDraft.pickup_location_id,
    onSelect: (id) => {
      setVehicleDraft((prev) => ({ ...prev, dropoff_location_id: id }))
    },
  })

  const pickupLocations = pickupOptions
  const dropoffLocations = dropoffOptions

  useEffect(() => {
    if (isGuesthouse) {
      setGuestDraft({
        city: query.city || '',
        check_in: query.check_in || '',
        check_out: query.check_out || '',
        guests: query.guests || '2',
      })
      setGuestCityLabel(query.city || '')
      return
    }

    setVehicleDraft({
      pickup_location_id: query.pickup_location_id || '',
      dropoff_location_id: query.dropoff_location_id || '',
      pickup_at: query.pickup_at || '',
      dropoff_at: query.dropoff_at || '',
    })
  }, [
    isGuesthouse,
    query.city,
    query.check_in,
    query.check_out,
    query.guests,
    query.pickup_location_id,
    query.dropoff_location_id,
    query.pickup_at,
    query.dropoff_at,
  ])

  const pickupDate = useMemo(
    () => parseDateTimeLocal(vehicleDraft.pickup_at),
    [vehicleDraft.pickup_at],
  )
  const dropoffDate = useMemo(
    () => parseDateTimeLocal(vehicleDraft.dropoff_at),
    [vehicleDraft.dropoff_at],
  )
  const rules = useBookingRules(pickupDate, dropoffDate)
  const minRentalDays = rules.min_rental_days || 1

  const handleVehicleDates = ({ start, end }) => {
    const pickup_at = dateTimeWithTime(start, '11:00')
    let dropoff_at = dateTimeWithTime(end, '10:00')
    if (pickup_at && dropoff_at) {
      dropoff_at = ensureValidDropoff(parseDateTimeLocal(pickup_at), dropoff_at, minRentalDays)
    }
    setVehicleDraft((prev) => ({ ...prev, pickup_at, dropoff_at }))
  }

  const handleGuestDates = ({ start, end }) => {
    setGuestDraft((prev) => ({
      ...prev,
      check_in: start ? toDateInputValue(start) : '',
      check_out: end ? toDateInputValue(end) : '',
    }))
  }

  const applyDraft = () => {
    if (isGuesthouse) {
      updateSearch({
        city: guestDraft.city.trim(),
        check_in: guestDraft.check_in,
        check_out: guestDraft.check_out,
        guests: guestDraft.guests,
      })
      return
    }

    updateSearch({
      pickup_location_id: vehicleDraft.pickup_location_id,
      dropoff_location_id: vehicleDraft.dropoff_location_id,
      pickup_at: vehicleDraft.pickup_at,
      dropoff_at: vehicleDraft.dropoff_at,
    })
  }

  const buildQueryParams = (draftQuery) => {
    const params = new URLSearchParams()
    Object.entries(draftQuery).forEach(([k, v]) => {
      if (v) params.set(k, v)
    })
    return params
  }

  return {
    isGuesthouse,
    vehicleDraft,
    setVehicleDraft,
    guestDraft,
    setGuestDraft,
    guestCityLabel,
    setGuestCityLabel,
    pickupLocations,
    dropoffLocations,
    pickupEmpty,
    handleVehicleDates,
    handleGuestDates,
    applyDraft,
    buildQueryParams,
    guestPeopleOptions: PEOPLE_OPTIONS,
    minRentalDays,
    maxRentalDays: rules.max_rental_days,
    vehicleStartDate: parseDateOnly(vehicleDraft.pickup_at),
    vehicleEndDate: parseDateOnly(vehicleDraft.dropoff_at),
    guestStartDate: parseDateOnly(guestDraft.check_in),
    guestEndDate: parseDateOnly(guestDraft.check_out),
  }
}
