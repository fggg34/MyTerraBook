import { useMemo } from 'react'
import { useSiteContent } from '../context/SiteContentContext'
import { buildSiteDataFromContent } from '../data/defaultSiteContentData'
import { getBootstrappedSiteContent } from '../utils/siteBootstrap'
import { readSiteContentCache } from '../utils/siteContentCache'

const bootstrappedPages = getBootstrappedSiteContent()
const cachedPages = readSiteContentCache()
const instantPages = bootstrappedPages ?? cachedPages

/**
 * Site chrome (header, footer, topbar) from bootstrap/cache first, then live CMS data.
 * Avoids empty/static defaults flashing before the API responds.
 */
export default function useSiteChromeData() {
  const { siteData, loading, useDefaults } = useSiteContent()

  return useMemo(() => {
    if (!loading) return siteData
    if (instantPages) {
      return buildSiteDataFromContent(instantPages, { useDefaults: false })
    }
    return {}
  }, [siteData, loading, useDefaults])
}
