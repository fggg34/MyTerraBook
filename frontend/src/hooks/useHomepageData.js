import { useEffect, useRef, useState } from 'react'
import { api } from '../api'
import { getInstantHomepage } from '../utils/siteBootstrap'
import { preloadSiteAssets, writeHomepageCache } from '../utils/siteContentCache'

export default function useHomepageData() {
  const hadInstantHomepageRef = useRef(Boolean(getInstantHomepage()))
  const [homepageData, setHomepageData] = useState(() => getInstantHomepage())
  const [loading, setLoading] = useState(() => !hadInstantHomepageRef.current)

  useEffect(() => {
    let cancelled = false
    const instantHomepage = getInstantHomepage()

    if (instantHomepage) {
      setHomepageData(instantHomepage)
      setLoading(false)
    } else {
      setLoading(true)
    }

    api
      .get('/homepage')
      .then((res) => {
        if (cancelled) return
        const data = res.data || null
        setHomepageData(data)
        if (data) {
          writeHomepageCache(data)
          preloadSiteAssets(null, data, null, null)
        }
      })
      .catch(() => {
        if (!cancelled && !instantHomepage) setHomepageData(null)
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
    hasInstantHomepage: hadInstantHomepageRef.current,
  }
}
