import { useMemo } from 'react'
import { useSiteContent } from '../context/SiteContentContext'
import useSiteChromeData from '../hooks/useSiteChromeData'
import useHomepageData from '../hooks/useHomepageData'
import PageHead from '../components/seo/PageHead'
import usePageSeo from '../hooks/usePageSeo'
import { mergeHomepageData } from '../utils/mergeHomepageData'
import HomePage from './HomePage'

export default function HomePageContainer() {
  const siteData = useSiteChromeData()
  const { loading: siteLoading, useDefaults, hasInstantContent } = useSiteContent()
  const { homepageData, homepageLoading, hasInstantHomepage } = useHomepageData()

  const seo = usePageSeo('home', {
    source: {
      heading: siteData?.hero?.heading,
      subtitle: siteData?.hero?.subtitle,
      backgroundImage: siteData?.hero?.backgroundImage,
    },
  })

  const contentReady =
    (hasInstantContent || !siteLoading) && (hasInstantHomepage || !homepageLoading)

  const pageData = useMemo(() => {
    if (!contentReady) {
      return mergeHomepageData({}, { useImageFallbacks: false })
    }

    return mergeHomepageData(
      { ...siteData, ...homepageData },
      { useImageFallbacks: useDefaults },
    )
  }, [siteData, homepageData, contentReady, useDefaults])

  return (
    <>
      <PageHead {...seo} />
      <HomePage pageData={pageData} contentReady={contentReady} />
    </>
  )
}
