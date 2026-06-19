import { useMemo } from 'react'
import { useSiteContent } from '../context/SiteContentContext'
import { buildSiteDataFromContent } from '../data/defaultSiteContentData'
import { getInstantHomepage, getInstantSiteContent } from '../utils/siteBootstrap'

/**
 * Site chrome (header, footer, topbar) from bootstrap/cache first, then live CMS data.
 * Avoids empty/static defaults flashing before the API responds.
 */
export default function useSiteChromeData() {
  const { siteData, loading, useDefaults } = useSiteContent()

  return useMemo(() => {
    if (!loading) return siteData

    const pages = getInstantSiteContent()
    if (pages) {
      return buildSiteDataFromContent(pages, { useDefaults: false })
    }

    return getInstantHomepage() ?? {}
  }, [siteData, loading, useDefaults])
}
