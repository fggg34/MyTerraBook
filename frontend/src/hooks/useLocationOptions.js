import { useEffect, useMemo, useState } from 'react'
import { api } from '../api'

/**
 * Fetches car-aware pickup/dropoff options from /search/suggestions.
 */
export default function useLocationOptions({
  role = 'pickup',
  pickupLocationId = '',
  mainCategory = '',
  enabled = true,
  limit = 50,
} = {}) {
  const [options, setOptions] = useState([])
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (!enabled) {
      setOptions([])
      setLoading(false)
      return undefined
    }

    if (role === 'dropoff' && !pickupLocationId) {
      setOptions([])
      setLoading(false)
      return undefined
    }

    let cancelled = false
    setLoading(true)

    const params = { scope: 'location', role, limit }
    if (role === 'dropoff' && pickupLocationId) {
      params.pickup_location_id = pickupLocationId
    }
    if (mainCategory) {
      params.main_category = mainCategory
    }

    api
      .get('/search/suggestions', { params })
      .then((res) => {
        if (!cancelled) setOptions(res.data?.data ?? [])
      })
      .catch(() => {
        if (!cancelled) setOptions([])
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [role, pickupLocationId, mainCategory, enabled, limit])

  const isEmpty = !loading && options.length === 0

  return { options, loading, isEmpty }
}

export function toFieldSelectOptions(options) {
  return options.map((loc) => ({
    value: loc.value,
    label: loc.label,
    subtitle: loc.subtitle,
  }))
}

export function suggestionToLocation(option) {
  return {
    id: option.value,
    name: option.label,
    address: option.subtitle || null,
    pickup_fee_cents: 0,
  }
}

export function mergeLocationLists(...lists) {
  const map = new Map()
  lists.flat().forEach((loc) => {
    if (loc?.id != null) map.set(String(loc.id), loc)
  })
  return [...map.values()]
}

export function useAutoSelectLocation({ options, value, onSelect, pickupValueForDropoff }) {
  useEffect(() => {
    if (!options.length) return undefined

    const normalizedValue = value == null || value === '' ? '' : String(value)
    if (normalizedValue) {
      const stillValid = options.some((loc) => String(loc.value) === normalizedValue)
      if (stillValid) return undefined
      // Keep an explicit user/query selection until matching options arrive.
      return undefined
    }

    const pickupMatch = pickupValueForDropoff
      ? options.find((loc) => String(loc.value) === String(pickupValueForDropoff))
      : null
    const fallback = pickupMatch || options[0]
    if (fallback) onSelect(fallback.value)
    return undefined
  }, [options, value, onSelect, pickupValueForDropoff])
}

export function usePickupDropoffLocations({ enabled = true } = {}) {
  const {
    options: pickupOptions,
    loading: pickupLoading,
    isEmpty: pickupEmpty,
  } = useLocationOptions({ role: 'pickup', enabled, limit: 50 })

  const [pickupLocationId, setPickupLocationId] = useState('')
  const [dropoffLocationId, setDropoffLocationId] = useState('')

  const {
    options: dropoffOptions,
    loading: dropoffLoading,
    isEmpty: dropoffEmpty,
  } = useLocationOptions({
    role: 'dropoff',
    pickupLocationId,
    enabled: enabled && !!pickupLocationId,
    limit: 50,
  })

  useAutoSelectLocation({
    options: pickupOptions,
    value: pickupLocationId,
    onSelect: (id) => {
      setPickupLocationId(id)
      setDropoffLocationId((prev) => prev || id)
    },
  })

  useAutoSelectLocation({
    options: dropoffOptions,
    value: dropoffLocationId,
    pickupValueForDropoff: pickupLocationId,
    onSelect: setDropoffLocationId,
  })

  const fieldSelectPickup = useMemo(() => toFieldSelectOptions(pickupOptions), [pickupOptions])
  const fieldSelectDropoff = useMemo(() => toFieldSelectOptions(dropoffOptions), [dropoffOptions])

  return {
    pickupLocationId,
    setPickupLocationId,
    dropoffLocationId,
    setDropoffLocationId,
    pickupOptions,
    dropoffOptions,
    fieldSelectPickup,
    fieldSelectDropoff,
    pickupLoading,
    dropoffLoading,
    pickupEmpty,
    dropoffEmpty,
    locationsLoading: pickupLoading || dropoffLoading,
    locationsEmpty: pickupEmpty,
  }
}
