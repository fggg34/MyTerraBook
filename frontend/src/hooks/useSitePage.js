import { useEffect, useState } from 'react'
import { api } from '../api'
import { getDefaultSitePage } from '../data/defaultSitePageData'

export default function useSitePage(slug) {
  const fallback = slug ? getDefaultSitePage(slug) : null
  const [page, setPage] = useState(fallback)
  const [loading, setLoading] = useState(!fallback)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!slug) return undefined

    let cancelled = false
    const defaultPage = getDefaultSitePage(slug)

    if (!defaultPage) {
      setLoading(true)
      setError(null)
      setPage(null)
    }

    api
      .get(`/site-pages/${slug}`)
      .then((res) => {
        if (!cancelled) setPage(res.data?.data ?? res.data)
      })
      .catch((err) => {
        if (!cancelled) {
          if (defaultPage) {
            setPage(defaultPage)
            setError(null)
          } else {
            setError(err.response?.status === 404 ? 'not_found' : 'error')
          }
        }
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
