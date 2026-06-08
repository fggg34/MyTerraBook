import { useEffect, useState } from 'react'
import { api } from '../api'

let cachedKey = undefined
let inflight = null

async function fetchMapsApiKey() {
  if (cachedKey !== undefined) return cachedKey
  if (inflight) return inflight

  inflight = api
    .get('/public-config')
    .then((res) => {
      cachedKey = res.data?.maps_api_key || ''
      return cachedKey
    })
    .catch(() => {
      cachedKey = ''
      return cachedKey
    })
    .finally(() => {
      inflight = null
    })

  return inflight
}

export function useMapsConfig() {
  const [mapsApiKey, setMapsApiKey] = useState(cachedKey ?? '')
  const [loading, setLoading] = useState(cachedKey === undefined)

  useEffect(() => {
    let active = true
    fetchMapsApiKey().then((key) => {
      if (!active) return
      setMapsApiKey(key)
      setLoading(false)
    })
    return () => {
      active = false
    }
  }, [])

  return { mapsApiKey, loading }
}
