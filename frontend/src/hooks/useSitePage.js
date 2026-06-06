import { useEffect, useState } from 'react'
import { api } from '../api'

export default function useSitePage(slug) {
  const [page, setPage] = useState(null)
  const [loading, setLoading] = useState(true)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!slug) return undefined

    let cancelled = false
    setLoading(true)
    setError(null)

    api
      .get(`/site-pages/${slug}`)
      .then((res) => {
        if (!cancelled) setPage(res.data?.data ?? res.data)
      })
      .catch((err) => {
        if (!cancelled) setError(err.response?.status === 404 ? 'not_found' : 'error')
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [slug])

  return { page, loading, error }
}
