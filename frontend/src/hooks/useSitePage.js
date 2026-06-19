import { useEffect, useState } from 'react'
import { api } from '../api'
import { getDefaultSitePage } from '../data/defaultSitePageData'
import { readSitePageCache, writeSitePageCache } from '../utils/siteContentCache'

export default function useSitePage(slug) {
  const [page, setPage] = useState(() => (slug ? readSitePageCache(slug) : null))
  const [loading, setLoading] = useState(() => Boolean(slug) && !readSitePageCache(slug))
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!slug) return undefined

    let cancelled = false
    const cachedPage = readSitePageCache(slug)
    const defaultPage = getDefaultSitePage(slug)

    if (cachedPage) {
      setPage(cachedPage)
      setLoading(false)
    } else {
      setLoading(true)
    }
    setError(null)

    api
      .get(`/site-pages/${slug}`)
      .then((res) => {
        if (cancelled) return
        const data = res.data?.data ?? res.data
        setPage(data)
        if (data) writeSitePageCache(slug, data)
      })
      .catch((err) => {
        if (cancelled) return
        if (cachedPage || defaultPage) {
          setPage(cachedPage ?? defaultPage)
          setError(null)
        } else {
          setError(err.response?.status === 404 ? 'not_found' : 'error')
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
