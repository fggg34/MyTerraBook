import { useMemo } from 'react'
import { useSiteContent } from '../context/SiteContentContext'
import useSiteChromeData from '../hooks/useSiteChromeData'
import useHomepageData from '../hooks/useHomepageData'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { getInstantHomepage, getInstantSiteContent } from '../utils/siteBootstrap'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import HomePage from './HomePage'

export default function HomePageContainer() {
  const siteData = useSiteChromeData()
  const { loading: siteLoading, useDefaults, hasInstantContent } = useSiteContent()
  const { homepageData, homepageLoading, hasInstantHomepage } = useHomepageData()

  const instantHomepage = getInstantHomepage()
  const instantSite = getInstantSiteContent()
  const resolvedHomepage = homepageData ?? instantHomepage
  const resolvedSite = siteData?.hero ? siteData : (instantHomepage ?? {})

  const seo = usePageSeo('home', {
    source: {
      heading: resolvedSite?.hero?.heading ?? resolvedHomepage?.hero?.heading,
      subtitle: resolvedSite?.hero?.subtitle ?? resolvedHomepage?.hero?.subtitle,
      backgroundImage: resolvedSite?.hero?.backgroundImage ?? resolvedHomepage?.hero?.backgroundImage,
    },
  })

  const contentReady =
    (hasInstantContent || hasInstantHomepage || Boolean(instantSite) || Boolean(instantHomepage) || !siteLoading)
    && (hasInstantHomepage || Boolean(instantHomepage) || Boolean(homepageData) || !homepageLoading)

  const pageData = useMemo(() => {
    if (!contentReady) {
      return mergeHomepageData({}, { useImageFallbacks: false })
    }

    return mergeHomepageData(
      { ...resolvedSite, ...resolvedHomepage, ...homepageData },
      { useImageFallbacks: useDefaults },
    )
  }, [resolvedSite, resolvedHomepage, homepageData, contentReady, useDefaults])

  return (
    <>
      <PageHead {...seo} />
      <HomePage pageData={pageData} contentReady={contentReady} />
    </>
  )
}
