import { useEffect, useState } from 'react'
import { api } from '../api'
import { getBootstrappedHomepage } from '../utils/siteBootstrap'
import { preloadSiteAssets, readHomepageCache, writeHomepageCache } from '../utils/siteContentCache'

const bootstrappedHomepage = getBootstrappedHomepage()
const cachedHomepage = readHomepageCache()
const initialHomepage = bootstrappedHomepage ?? cachedHomepage
const hasInstantHomepage = initialHomepage != null

export default function useHomepageData() {
  const [homepageData, setHomepageData] = useState(initialHomepage)
  const [loading, setLoading] = useState(!hasInstantHomepage)

  useEffect(() => {
    let cancelled = false

    api
      .get('/homepage')
      .then((res) => {
        if (cancelled) return
        const data = res.data || null
        setHomepageData(data)
        if (data) {
          writeHomepageCache(data)
          preloadSiteAssets(null, data)
        }
      })
      .catch(() => {
        if (!cancelled && !hasInstantHomepage) setHomepageData(null)
      })
      .finally(() => {
        if (!cancelled) setLoading(false)
      })

    return () => {
      cancelled = true
    }
  }, [])

  return {
    homepageData,
    homepageLoading: loading,
    hasInstantHomepage,
  }
}
