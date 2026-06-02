import { useEffect, useState } from 'react'
import { api } from '../api'
import { formatDateTimeLocal } from '../utils/format'

const DEFAULT_RULES = { min_rental_days: 1, max_rental_days: null, loading: false }

export function useBookingRules(pickupDate, dropoffDate) {
  const [rules, setRules] = useState(DEFAULT_RULES)

  useEffect(() => {
    let cancelled = false

    const pickup =
      pickupDate instanceof Date && !Number.isNaN(pickupDate.getTime())
        ? formatDateTimeLocal(pickupDate).slice(0, 10)
        : undefined
    const dropoff =
      dropoffDate instanceof Date && !Number.isNaN(dropoffDate.getTime())
        ? formatDateTimeLocal(dropoffDate).slice(0, 10)
        : pickup

    const params = {}
    if (pickup) params.pickup_date = pickup
    if (dropoff) params.dropoff_date = dropoff

    setRules((prev) => ({ ...prev, loading: true }))

    api
      .get('/booking-restrictions', { params })
      .then((res) => {
        if (!cancelled) {
          setRules({
            min_rental_days: res.data.min_rental_days ?? 1,
            max_rental_days: res.data.max_rental_days ?? null,
            loading: false,
          })
        }
      })
      .catch(() => {
        if (!cancelled) setRules(DEFAULT_RULES)
      })

    return () => {
      cancelled = true
    }
  }, [pickupDate?.getTime?.(), dropoffDate?.getTime?.()])

  return rules
}
