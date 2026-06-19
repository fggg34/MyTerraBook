import { useMemo } from 'react'
import { buildSiteDataFromContent } from '../data/defaultSiteContentData'
import { useSiteContent } from '../context/SiteContentContext'
import { getBootstrappedSiteContent } from '../utils/siteBootstrap'

const bootstrappedPages = getBootstrappedSiteContent()

/**
 * Site chrome (header, footer, topbar) from bootstrap first, then live CMS data.
 * Avoids empty/static defaults flashing before the API responds.
 */
export default function useSiteChromeData() {
  const { siteData, loading, useDefaults } = useSiteContent()

  return useMemo(() => {
    if (!loading) return siteData
    if (bootstrappedPages) {
      return buildSiteDataFromContent(bootstrappedPages, { useDefaults: false })
    }
    return {}
  }, [siteData, loading, useDefaults])
}
