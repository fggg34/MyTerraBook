import { useEffect, useRef, useState } from 'react'
import { api } from '../api'

function useDebouncedValue(value, delay = 250) {
  const [debounced, setDebounced] = useState(value)

  useEffect(() => {
    const timer = window.setTimeout(() => setDebounced(value), delay)
    return () => window.clearTimeout(timer)
  }, [value, delay])

  return debounced
}

export default function useSearchSuggestions({
  scope,
  query = '',
  role,
  pickupLocationId,
  limit = 8,
  enabled = true,
}) {
  const debouncedQuery = useDebouncedValue(query)
  const [suggestions, setSuggestions] = useState([])
  const [loading, setLoading] = useState(false)
  const requestId = useRef(0)

  useEffect(() => {
    if (!enabled || !scope) {
      setSuggestions([])
      setLoading(false)
      return undefined
    }

    const currentRequest = ++requestId.current
    setLoading(true)

    const params = { scope, limit }
    if (debouncedQuery.trim()) params.q = debouncedQuery.trim()
    if (scope === 'location' && role) params.role = role
    if (scope === 'location' && pickupLocationId) params.pickup_location_id = pickupLocationId

    api
      .get('/search/suggestions', { params })
      .then((res) => {
        if (currentRequest !== requestId.current) return
        setSuggestions(res.data?.data ?? [])
      })
      .catch(() => {
        if (currentRequest !== requestId.current) return
        setSuggestions([])
      })
      .finally(() => {
        if (currentRequest === requestId.current) setLoading(false)
      })

    return undefined
  }, [scope, debouncedQuery, role, pickupLocationId, limit, enabled])

  return { suggestions, loading }
}
