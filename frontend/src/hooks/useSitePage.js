import { useEffect, useState } from 'react'
import { api } from '../api'
import { getDefaultSitePage } from '../data/defaultSitePageData'
import {
  getBootstrappedSitePage,
  getBootstrappedSitePages,
} from '../utils/siteBootstrap'
import {
  readSitePageCache,
  readSitePagesCache,
  writeSitePageCache,
} from '../utils/siteContentCache'

function getInstantSitePage(slug) {
  if (!slug) return null
  return getBootstrappedSitePage(slug) ?? readSitePageCache(slug) ?? getBootstrappedSitePages()?.[slug] ?? readSitePagesCache()?.[slug] ?? null
}

export default function useSitePage(slug) {
  const instantPage = getInstantSitePage(slug)
  const [page, setPage] = useState(instantPage)
  const [loading, setLoading] = useState(Boolean(slug) && !instantPage)
  const [error, setError] = useState(null)

  useEffect(() => {
    if (!slug) return undefined

    let cancelled = false
    const cachedPage = getInstantSitePage(slug)
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

export function hydrateSitePagesFromBootstrap(sitePages) {
  if (!sitePages || typeof sitePages !== 'object') return
  writeSitePagesCache(sitePages)
}
