import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'
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
  const [pickupLocations, setPickupLocations] = useState([])
  const [dropoffLocations, setDropoffLocations] = useState([])

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

  useEffect(() => {
    if (isGuesthouse) return undefined
    api
      .get('/search/suggestions', { params: { scope: 'location', role: 'pickup', limit: 50 } })
      .then((res) => {
        const locations = res.data?.data ?? []
        setPickupLocations(locations)
        setVehicleDraft((prev) => {
          if (prev.pickup_location_id) return prev
          const first = locations[0]
          if (!first) return prev
          return {
            ...prev,
            pickup_location_id: first.value,
            dropoff_location_id: prev.dropoff_location_id || first.value,
          }
        })
      })
      .catch(() => {})
    return undefined
  }, [isGuesthouse])

  useEffect(() => {
    if (isGuesthouse || !vehicleDraft.pickup_location_id) {
      setDropoffLocations([])
      return undefined
    }
    api
      .get('/search/suggestions', {
        params: {
          scope: 'location',
          role: 'dropoff',
          pickup_location_id: vehicleDraft.pickup_location_id,
          limit: 50,
        },
      })
      .then((res) => setDropoffLocations(res.data?.data ?? []))
      .catch(() => setDropoffLocations([]))
    return undefined
  }, [isGuesthouse, vehicleDraft.pickup_location_id])

  useEffect(() => {
    if (!dropoffLocations.length) return undefined
    setVehicleDraft((prev) => {
      if (!prev.dropoff_location_id) return prev
      const stillValid = dropoffLocations.some((loc) => loc.value === prev.dropoff_location_id)
      if (stillValid) return prev
      const fallback =
        dropoffLocations.find((loc) => loc.value === prev.pickup_location_id) || dropoffLocations[0]
      return { ...prev, dropoff_location_id: fallback?.value || '' }
    })
    return undefined
  }, [dropoffLocations])

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
